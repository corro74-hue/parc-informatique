<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

final class SettingsController
{
    /**
     * Page principale des paramètres
     */
    public function index(Request $request): Response
    {
        $pdo = Database::getInstance();

        // Récupérer tous les paramètres
        $stmt = $pdo->query("SELECT * FROM settings ORDER BY `group`, `key`");
        $settings = $stmt->fetchAll();

        // Regrouper par catégorie
        $grouped = [];
        foreach ($settings as $s) {
            $group = $s['group'] ?? 'general';
            $grouped[$group][] = $s;
        }

        return $this->view('settings/index', [
            'title'    => 'Paramètres',
            'settings' => $grouped,
        ]);
    }

    /**
     * Enregistrer les modifications
     */
    public function save(Request $request): Response
    {
        $pdo = Database::getInstance();

        try {
            $data = $request->body['settings'] ?? [];

            if (empty($data)) {
                flash('error', 'Aucun paramètre à enregistrer.');
                return Response::redirect(url('settings'));
            }

            // Types connus (pour info)
            $types = [];
            foreach ($pdo->query("SELECT `key`, `type` FROM `settings`")->fetchAll() as $row) {
                $types[$row['key']] = $row['type'];
            }

            $stmt = $pdo->prepare("UPDATE settings SET `value` = ?, updated_at = NOW() WHERE `key` = ?");

            $count = 0;
            foreach ($data as $key => $value) {
                // Ne pas permettre la modification des clés sensibles via ce formulaire
                if (in_array($key, [
                    'maintenance_mode',
                    'maintenance_reason',
                    'maintenance_end_at',
                    'maintenance_activated_by',
                    'maintenance_activated_at',
                ], true)) {
                    continue;
                }

                $type  = $types[$key] ?? 'string';
                $value = (string)$value;

                // Normaliser les booléens
                if ($type === 'bool') {
                    $value = ($value === '1' || $value === 'true' || $value === 'on') ? '1' : '0';
                }

                $stmt->execute([trim($value), $key]);
                $count++;
            }

            // Log de l'action
            if (function_exists('logAction')) {
                logAction('UPDATE_SETTINGS', "Mise à jour de $count paramètre(s)");
            }

            flash('success', "Paramètres enregistrés avec succès ($count modifiés).");
        } catch (\Throwable $e) {
            flash('error', 'Erreur : ' . $e->getMessage());
        }

        return Response::redirect(url('settings'));
    }

    /**
     * Test d'envoi d'email (SMTP)
     */
    public function testMail(Request $request): Response
    {
        $email = trim((string)($request->body['test_email'] ?? ''));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Adresse email invalide.');
            return Response::redirect(url('settings'));
        }

        try {
            if (!function_exists('envoyerMail')) {
                throw new \RuntimeException("La fonction 'envoyerMail' n'existe pas.");
            }

            $html = '<p>Ceci est un <strong>email de test</strong> envoyé depuis la page Paramètres.</p>'
                  . '<p>Si vous recevez ce message, votre configuration SMTP est correcte. ✅</p>';

            $result = envoyerMail($email, 'Test SMTP - Parc Info', $html);

            if ($result) {
                flash('success', "Email de test envoyé à $email avec succès !");
            } else {
                flash('error', "L'envoi de l'email a échoué. Vérifiez les paramètres SMTP.");
            }
        } catch (\Throwable $e) {
            flash('error', "Erreur SMTP : " . $e->getMessage());
        }

        return Response::redirect(url('settings'));
    }

    /**
     * Activer / désactiver le mode maintenance
     */
    public function toggleMaintenance(Request $request): Response
    {
        $pdo = Database::getInstance();

        try {
            $active = (string)($request->body['active'] ?? '0');
            $value  = $active === '1' ? '1' : '0';

            $pdo->prepare("UPDATE `settings` SET `value` = ?, `updated_at` = NOW() WHERE `key` = 'maintenance_mode'")
                ->execute([$value]);

            // Mettre à jour la date d'activation si on active
            if ($value === '1') {
                $pdo->prepare("UPDATE `settings` SET `value` = ?, `updated_at` = NOW() WHERE `key` = 'maintenance_activated_at'")
                    ->execute([date('Y-m-d H:i:s')]);
                $pdo->prepare("UPDATE `settings` SET `value` = ?, `updated_at` = NOW() WHERE `key` = 'maintenance_activated_by'")
                    ->execute([(string)($_SESSION['user_id'] ?? '')]);
            }

            if (function_exists('logAction')) {
                logAction('TOGGLE_MAINTENANCE', "Mode maintenance : " . ($value === '1' ? 'ACTIVÉ' : 'DÉSACTIVÉ'));
            }

            flash('success', $value === '1'
                ? '⚠️ Mode maintenance ACTIVÉ. Les utilisateurs non-admin seront redirigés.'
                : '✅ Mode maintenance désactivé. L\'application est de nouveau accessible.');
        } catch (\Throwable $e) {
            flash('error', 'Erreur : ' . $e->getMessage());
        }

        return Response::redirect(url('settings'));
    }

    /**
     * Upload du logo de l'application
     */
    public function uploadLogo(Request $request): Response
    {
        try {
            $file = $request->files['logo'] ?? null;

            if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
                flash('error', 'Aucun fichier reçu ou erreur d\'upload.');
                return Response::redirect(url('settings'));
            }

            // Vérification de la taille (2 Mo max)
            $maxSize = 2 * 1024 * 1024;
            if ($file['size'] > $maxSize) {
                flash('error', 'Le fichier dépasse 2 Mo.');
                return Response::redirect(url('settings'));
            }

            // Vérification du type MIME réel
            $allowedMimes = ['image/png', 'image/jpeg', 'image/jpg', 'image/svg+xml'];
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);

            if (!in_array($mime, $allowedMimes, true)) {
                flash('error', 'Format non autorisé. Utilisez PNG, JPG ou SVG.');
                return Response::redirect(url('settings'));
            }

            // Déterminer l'extension
            $extMap = [
                'image/png'     => 'png',
                'image/jpeg'    => 'jpg',
                'image/jpg'     => 'jpg',
                'image/svg+xml' => 'svg',
            ];
            $ext = $extMap[$mime] ?? 'png';

            // Chemin de destination
            $destDir = dirname(__DIR__, 2) . '/public/assets/images';
            if (!is_dir($destDir)) {
                @mkdir($destDir, 0755, true);
            }

            $filename = 'logo-app.' . $ext;
            $destPath = $destDir . '/' . $filename;

            // Supprimer les anciens logos (si extension différente)
            foreach (['png', 'jpg', 'jpeg', 'svg'] as $oldExt) {
                $oldPath = $destDir . '/logo-app.' . $oldExt;
                if (file_exists($oldPath) && $oldPath !== $destPath) {
                    @unlink($oldPath);
                }
            }

            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                flash('error', 'Impossible d\'enregistrer le fichier.');
                return Response::redirect(url('settings'));
            }

            // Mettre à jour la BDD
            $relativePath = 'assets/images/' . $filename;
            $pdo = Database::getInstance();
            $pdo->prepare("UPDATE `settings` SET `value` = ?, `updated_at` = NOW() WHERE `key` = 'app.logo'")
                ->execute([$relativePath]);

            if (function_exists('logAction')) {
                logAction('UPLOAD_LOGO', "Logo mis à jour : $relativePath");
            }

            flash('success', '✅ Logo uploadé avec succès !');
        } catch (\Throwable $e) {
            flash('error', 'Erreur : ' . $e->getMessage());
        }

        return Response::redirect(url('settings'));
    }

    /**
     * Rendu d'une vue
     */
    private function view(string $view, array $data = []): Response
    {
        extract($data);
        $viewPath = dirname(__DIR__, 2) . '/resources/views/' . $view . '.php';

        if (!is_file($viewPath)) {
            return new Response("Vue introuvable : $view", 500);
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        // Rendre dans le layout principal
        $layoutPath = dirname(__DIR__, 2) . '/resources/views/layouts/app.php';
        if (is_file($layoutPath)) {
            ob_start();
            require $layoutPath;
            $content = ob_get_clean();
        }

        return new Response($content);
    }
}