<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;

final class TodoController extends Controller
{
    private const TODO_FILE = 'data/todo.json';

    /**
     * Page principale du TODO avec filtres.
     */
    public function index(Request $request): Response
    {
        $todo = $this->loadTodo();

        $filters = [
            'search'  => trim((string) $request->get('search', '')),
            'status'  => (string) $request->get('status', 'all'),
            'type'    => (string) $request->get('type', 'all'),
            'module'  => (string) $request->get('module', 'all'),
            'assigned'=> (string) $request->get('assigned', 'all'), // 🆕 Filtre par utilisateur
        ];

        $stats   = $this->collectProjectStats();
        $todo    = $this->computeProgress($todo);
        $todo    = $this->applyFilters($todo, $filters);
        $users   = $this->loadActiveUsers();

        return $this->view('todo.index', [
            'title'   => 'Tableau de bord du projet',
            'todo'    => $todo,
            'stats'   => $stats,
            'filters' => $filters,
            'users'   => $users,
        ]);
    }

    /**
     * Basculer l'état d'une tâche (AJAX).
     */
    public function toggle(Request $request): Response
    {
        $taskId   = trim((string) $request->input('task_id', ''));
        $moduleId = trim((string) $request->input('module_id', ''));

        if ($taskId === '' || $moduleId === '') {
            return Response::json(['success' => false, 'message' => 'Paramètres manquants.'], 400);
        }

        try {
            $todo  = $this->loadTodo();
            $found = false;
            $newState = false;

            foreach ($todo['modules'] as &$module) {
                if ($module['id'] !== $moduleId) continue;
                foreach ($module['tasks'] as &$task) {
                    if ($task['id'] === $taskId) {
                        $task['done'] = !$task['done'];
                        $newState = $task['done'];
                        $found = true;
                        break 2;
                    }
                }
            }
            unset($module, $task);

            if (!$found) {
                return Response::json(['success' => false, 'message' => 'Tâche introuvable.'], 404);
            }

            $todo['last_update'] = date('Y-m-d');
            $this->saveTodo($todo);

            return Response::json([
                'success' => true,
                'done'    => $newState,
                'message' => $newState ? 'Tâche validée ✅' : 'Tâche décochée',
            ]);
        } catch (\Throwable $e) {
            return Response::json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * 🆕 Mettre à jour les métadonnées d'une tâche (deadline + assignation).
     */
    public function updateTask(Request $request): Response
    {
        $taskId   = trim((string) $request->input('task_id', ''));
        $moduleId = trim((string) $request->input('module_id', ''));
        $deadline = trim((string) $request->input('deadline', ''));
        $assigned = trim((string) $request->input('assigned_to', ''));

        if ($taskId === '' || $moduleId === '') {
            return Response::json(['success' => false, 'message' => 'Paramètres manquants.'], 400);
        }

        try {
            $todo  = $this->loadTodo();
            $found = false;

            foreach ($todo['modules'] as &$module) {
                if ($module['id'] !== $moduleId) continue;
                foreach ($module['tasks'] as &$task) {
                    if ($task['id'] === $taskId) {
                        // Deadline
                        if ($deadline === '') {
                            unset($task['deadline']);
                        } else {
                            $task['deadline'] = $deadline;
                        }
                        // Assignation
                        if ($assigned === '' || $assigned === '0') {
                            unset($task['assigned_to']);
                        } else {
                            $task['assigned_to'] = (int) $assigned;
                        }
                        $found = true;
                        break 2;
                    }
                }
            }
            unset($module, $task);

            if (!$found) {
                return Response::json(['success' => false, 'message' => 'Tâche introuvable.'], 404);
            }

            $todo['last_update'] = date('Y-m-d');
            $this->saveTodo($todo);

            return Response::json([
                'success' => true,
                'message' => 'Tâche mise à jour ✅',
            ]);
        } catch (\Throwable $e) {
            return Response::json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remettre toutes les tâches à zéro (reset).
     */
    public function reset(Request $request): Response
    {
        try {
            $todo = $this->loadTodo();
            foreach ($todo['modules'] as &$module) {
                foreach ($module['tasks'] as &$task) {
                    $task['done'] = false;
                }
            }
            unset($module, $task);
            $todo['last_update'] = date('Y-m-d');
            $this->saveTodo($todo);

            flash('success', 'Toutes les tâches ont été réinitialisées.');
        } catch (\Throwable $e) {
            flash('error', 'Erreur : ' . $e->getMessage());
        }

        return Response::redirect(url('todo'));
    }

    // ============================================
    // HELPERS PRIVÉS
    // ============================================

    private function todoPath(): string
    {
        return dirname(__DIR__, 2) . '/' . self::TODO_FILE;
    }

    private function loadTodo(): array
    {
        $path = $this->todoPath();
        if (!is_file($path)) {
            throw new \RuntimeException('Fichier TODO introuvable : ' . $path);
        }
        $json = file_get_contents($path);
        $data = json_decode($json, true);
        if (!is_array($data) || !isset($data['modules'])) {
            throw new \RuntimeException('Format du fichier TODO invalide.');
        }
        return $data;
    }

    private function saveTodo(array $todo): void
    {
        $path = $this->todoPath();
        $json = json_encode($todo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (file_put_contents($path, $json) === false) {
            throw new \RuntimeException('Impossible d\'écrire dans le fichier TODO.');
        }
    }

    /**
     * 🆕 Charge les utilisateurs actifs (pour la liste d'assignation).
     */
    private function loadActiveUsers(): array
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query("
                SELECT id, username, first_name, last_name
                FROM users
                WHERE is_active = 1
                ORDER BY first_name, last_name
            ");
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function applyFilters(array $todo, array $filters): array
    {
        if ($filters['search'] === ''
            && $filters['status'] === 'all'
            && $filters['type'] === 'all'
            && $filters['module'] === 'all'
            && $filters['assigned'] === 'all') {
            return $todo;
        }

        $searchLower = mb_strtolower($filters['search']);
        $filteredModules = [];
        $totalFiltered = 0;
        $doneFiltered = 0;

        foreach ($todo['modules'] as $module) {
            if ($filters['module'] !== 'all' && $module['id'] !== $filters['module']) {
                continue;
            }

            $filteredTasks = [];

            foreach ($module['tasks'] as $task) {
                if ($searchLower !== '') {
                    if (!str_contains(mb_strtolower($task['label']), $searchLower)) continue;
                }

                $isDone = !empty($task['done']);
                if ($filters['status'] === 'done' && !$isDone) continue;
                if ($filters['status'] === 'pending' && $isDone) continue;

                $taskType = $task['type'] ?? 'feature';
                if ($filters['type'] !== 'all' && $taskType !== $filters['type']) continue;

                // 🆕 Filtre par assignation
                if ($filters['assigned'] !== 'all') {
                    $assignedTo = (string)($task['assigned_to'] ?? '');
                    if ($assignedTo !== $filters['assigned']) continue;
                }

                $filteredTasks[] = $task;
                $totalFiltered++;
                if ($isDone) $doneFiltered++;
            }

            if (!empty($filteredTasks)) {
                $module['tasks'] = $filteredTasks;
                $module['filtered_total'] = count($filteredTasks);
                $module['filtered_done'] = count(array_filter($filteredTasks, fn($t) => !empty($t['done'])));
                $filteredModules[] = $module;
            }
        }

        $todo['modules'] = $filteredModules;
        $todo['filtered_total'] = $totalFiltered;
        $todo['filtered_done'] = $doneFiltered;
        $todo['is_filtered'] = true;

        return $todo;
    }

    private function computeProgress(array $todo): array
    {
        $totalAll = 0;
        $doneAll  = 0;

        foreach ($todo['modules'] as &$module) {
            $total = count($module['tasks']);
            $done  = 0;
            foreach ($module['tasks'] as $task) {
                if (!empty($task['done'])) $done++;
            }
            $module['total']       = $total;
            $module['done']        = $done;
            $module['percentage']  = $total > 0 ? (int) round(($done / $total) * 100) : 0;
            $totalAll += $total;
            $doneAll  += $done;
        }
        unset($module);

        $todo['total']      = $totalAll;
        $todo['done']       = $doneAll;
        $todo['percentage'] = $totalAll > 0 ? (int) round(($doneAll / $totalAll) * 100) : 0;
        $todo['is_filtered'] = false;

        return $todo;
    }

    private function collectProjectStats(): array
    {
        $root = dirname(__DIR__, 2);

        $stats = [
            'controllers' => $this->countFiles("$root/app/Controllers", '*.php', true),
            'models'      => $this->countFiles("$root/app/Models", '*.php'),
            'services'    => $this->countFiles("$root/app/Services", '*.php', true),
            'views'       => $this->countFiles("$root/resources/views", '*.php', true),
            'migrations'  => $this->countFiles("$root/database", '*.sql'),
        ];

        $routesFile = "$root/routes/web.php";
        if (is_file($routesFile)) {
            $content = file_get_contents($routesFile);
            $stats['routes'] = preg_match_all('/\$router->(get|post|put|delete)/', $content);
        } else {
            $stats['routes'] = 0;
        }

        try {
            $pdo = Database::getInstance();
            $stats['db_equipment']   = (int) $pdo->query("SELECT COUNT(*) FROM equipment WHERE deleted_at IS NULL")->fetchColumn();
            $stats['db_users']       = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn();
            $stats['db_documents']   = (int) $pdo->query("SELECT COUNT(*) FROM documents")->fetchColumn();
            $stats['db_campaigns']   = (int) $pdo->query("SELECT COUNT(*) FROM inventory_campaigns")->fetchColumn();
            $stats['db_maintenance'] = (int) $pdo->query("SELECT COUNT(*) FROM maintenance")->fetchColumn();
            $stats['db_employees']   = (int) $pdo->query("SELECT COUNT(*) FROM employees WHERE is_active = 1")->fetchColumn();
        } catch (\Throwable $e) {
            $stats['db_equipment']   = 0;
            $stats['db_users']       = 0;
            $stats['db_documents']   = 0;
            $stats['db_campaigns']   = 0;
            $stats['db_maintenance'] = 0;
            $stats['db_employees']   = 0;
        }

        $stats['project_size'] = $this->humanSize($this->dirSize("$root/app") + $this->dirSize("$root/resources"));
        $stats['storage_size'] = $this->humanSize($this->dirSize("$root/storage"));

        return $stats;
    }

    private function countFiles(string $dir, string $pattern, bool $recursive = false): int
    {
        if (!is_dir($dir)) return 0;
        $count = 0;
        $iterator = $recursive
            ? new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS))
            : new \DirectoryIterator($dir);
        foreach ($iterator as $file) {
            if ($file->isFile() && fnmatch($pattern, $file->getFilename())) $count++;
        }
        return $count;
    }

    private function dirSize(string $dir): int
    {
        if (!is_dir($dir)) return 0;
        $size = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            if ($file->isFile()) $size += $file->getSize();
        }
        return $size;
    }

    private function humanSize(int $bytes): string
    {
        if ($bytes < 1024) return $bytes . ' o';
        if ($bytes < 1048576) return number_format($bytes / 1024, 1, ',', ' ') . ' Ko';
        return number_format($bytes / 1048576, 2, ',', ' ') . ' Mo';
    }
}