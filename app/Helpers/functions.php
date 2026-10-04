<?php
declare(strict_types=1);

// ============================================
// ENVIRONNEMENT
// ============================================
if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? $default;
    }
}

// ============================================
// CONFIGURATION
// ============================================
if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        static $configs = [];
        [$file, $path] = array_pad(explode('.', $key, 2), 2, null);

        if (!isset($configs[$file])) {
            $filePath = dirname(__DIR__, 2) . "/config/$file.php";
            $configs[$file] = is_file($filePath) ? require $filePath : [];
        }

        if ($path === null) {
            return $configs[$file];
        }

        $value = $configs[$file];
        foreach (explode('.', $path) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }
        return $value;
    }
}

// ============================================
// CHEMINS
// ============================================
if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        return dirname(__DIR__, 2) . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '/\\') : '');
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        return base_path('storage' . ($path ? '/' . ltrim($path, '/\\') : ''));
    }
}

if (!function_exists('public_path')) {
    function public_path(string $path = ''): string
    {
        return base_path('public' . ($path ? '/' . ltrim($path, '/\\') : ''));
    }
}

// ============================================
// URLs
// ============================================
if (!function_exists('url')) {
    /**
     * Génère une URL complète pour l'application.
     */
    function url(string $path = ''): string
    {
        $appUrl = (string) env('APP_URL', '');
        if ($appUrl !== '' && preg_match('#^https?://#', $appUrl)) {
            $base = rtrim($appUrl, '/');
            return $base . ($path !== '' ? '/' . ltrim($path, '/') : '');
        }

        $base = defined('BASE_PATH') ? BASE_PATH : '';
        return $base . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

// ============================================
// ÉCHAPPEMENT (protection XSS)
// ============================================
if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

// ============================================
// ANCIENNES VALEURS DE FORMULAIRE
// ============================================
if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        return $_SESSION['_old'][$key] ?? $default;
    }
}

// ============================================
// CSRF
// ============================================
if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('verify_csrf')) {
    function verify_csrf(?string $token): bool
    {
        return !empty($token)
            && !empty($_SESSION['_csrf_token'])
            && hash_equals($_SESSION['_csrf_token'], $token);
    }
}

// ============================================
// MESSAGES FLASH
// ============================================
if (!function_exists('flash')) {
    function flash(string $key, mixed $value = null): mixed
    {
        if ($value !== null) {
            $_SESSION['_flash'][$key] = $value;
            return null;
        }
        $val = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $val;
    }
}

// ============================================
// RACCOURCI DE REDIRECTION
// ============================================
if (!function_exists('redirect')) {
    function redirect(string $url, int $status = 302): \App\Core\Response
    {
        return \App\Core\Response::redirect($url, $status);
    }
}

// ============================================
// DEBUG (dump & die)
// ============================================
if (!function_exists('dd')) {
    function dd(mixed ...$vars): never
    {
        echo '<pre style="background:#1e1e1e;color:#eee;padding:15px;font-family:monospace;border-radius:6px;">';
        foreach ($vars as $v) {
            var_dump($v);
            echo "\n" . str_repeat('-', 80) . "\n";
        }
        echo '</pre>';
        exit(1);
    }
}

// ============================================
// AUTHENTIFICATION — HELPERS
// ============================================
if (!function_exists('auth_id')) {
    /**
     * Retourne l'ID de l'utilisateur connecté, ou null.
     */
    function auth_id(): ?int
    {
        if (!empty($_SESSION['user_id'])) {
            return (int) $_SESSION['user_id'];
        }
        if (!empty($_SESSION['auth_user_id'])) {
            return (int) $_SESSION['auth_user_id'];
        }
        return null;
    }
}

if (!function_exists('is_logged_in')) {
    /**
     * Indique si un utilisateur est connecté.
     */
    function is_logged_in(): bool
    {
        return auth_id() !== null;
    }
}

if (!function_exists('auth_user')) {
    /**
     * Retourne l'utilisateur connecté (instance User) ou null.
     * Utilise un cache statique pour éviter les requêtes répétées.
     */
    function auth_user(): ?\App\Models\User
    {
        static $user = null;
        static $loaded = false;

        if ($loaded) {
            return $user;
        }
        $loaded = true;

        $id = auth_id();
        if ($id === null) {
            return null;
        }

        try {
            $repo = new \App\Repositories\MySql\UserRepository();
            $user = $repo->findById($id);
        } catch (\Throwable $e) {
            $user = null;
        }

        return $user;
    }
}

// ============================================
// PERMISSIONS & RÔLES (RBAC)
// ============================================
if (!function_exists('can')) {
    function can(string $permission): bool
    {
        static $auth = null;
        if ($auth === null) {
            $auth = new \App\Services\Auth\AuthService();
        }
        return $auth->can($permission);
    }
}

if (!function_exists('has_role')) {
    function has_role(string $slug): bool
    {
        static $auth = null;
        if ($auth === null) {
            $auth = new \App\Services\Auth\AuthService();
        }
        return $auth->hasRole($slug);
    }
}

// ============================================
// JOURNAL D'AUDIT — Enregistrement d'actions
// ============================================
if (!function_exists('logAction')) {
    /**
     * Enregistre une action dans le journal d'audit.
     * La fonction s'adapte automatiquement à la structure de la table.
     *
     * @param string $action  Code de l'action (ex: 'UPDATE_SETTINGS')
     * @param string $details Détails supplémentaires
     */
    function logAction(string $action, string $details = ''): void
    {
        try {
            $pdo = \App\Core\Database::getInstance();

            $userId   = auth_id();
            $username = $_SESSION['username'] ?? ($_SESSION['full_name'] ?? 'Anonyme');
            $ip       = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $ua       = $_SERVER['HTTP_USER_AGENT'] ?? '';

            // Vérifier les colonnes disponibles dans audit_logs
            static $columns = null;
            if ($columns === null) {
                try {
                    $columns = $pdo->query("SHOW COLUMNS FROM `audit_logs`")->fetchAll(PDO::FETCH_COLUMN);
                } catch (\Throwable $e) {
                    $columns = [];
                }
            }

            if (empty($columns)) {
                error_log("logAction : table audit_logs introuvable");
                return;
            }

            // Construire la requête selon les colonnes disponibles
            $fields = ['action' => $action];
            if (in_array('user_id', $columns, true))      $fields['user_id']     = $userId;
            if (in_array('username', $columns, true))     $fields['username']    = $username;
            if (in_array('entity_type', $columns, true))  $fields['entity_type'] = 'system';
            if (in_array('details', $columns, true))      $fields['details']     = $details;
            if (in_array('description', $columns, true))  $fields['description'] = $details;
            if (in_array('ip', $columns, true))           $fields['ip']          = $ip;
            if (in_array('ip_address', $columns, true))   $fields['ip_address']  = $ip;
            if (in_array('user_agent', $columns, true))   $fields['user_agent']  = $ua;
            if (in_array('created_at', $columns, true))   $fields['created_at']  = date('Y-m-d H:i:s');

            $cols = implode('`, `', array_keys($fields));
            $vals = implode(', ', array_fill(0, count($fields), '?'));

            $stmt = $pdo->prepare("INSERT INTO `audit_logs` (`$cols`) VALUES ($vals)");
            $stmt->execute(array_values($fields));

        } catch (\Throwable $e) {
            error_log("logAction ERREUR : " . $e->getMessage());
        }
    }
}

// ============================================
// ENVOI D'EMAIL (PHPMailer + settings BDD)
// ============================================
if (!function_exists('envoyerMail')) {
    /**
     * Envoie un email via PHPMailer en utilisant la configuration SMTP
     * stockée dans la table `settings`.
     *
     * @param string $to      Destinataire
     * @param string $subject Sujet
     * @param string $body    Corps HTML
     * @return bool
     */
    function envoyerMail(string $to, string $subject, string $body): bool
    {
        try {
            $pdo = \App\Core\Database::getInstance();

            // Charger tous les settings en une requête
            $settings = $pdo->query("SELECT `key`, `value` FROM `settings`")
                            ->fetchAll(PDO::FETCH_KEY_PAIR);

            $host      = $settings['smtp_host']       ?? '';
            $port      = (int)($settings['smtp_port'] ?? 587);
            $user      = $settings['smtp_user']       ?? '';
            $pass      = $settings['smtp_pass']       ?? '';
            $fromEmail = $settings['smtp_from_email'] ?? ($settings['smtp_from'] ?? $user);
            $fromName  = $settings['smtp_from_name']  ?? 'Parc Info';
            $secure    = $settings['smtp_secure']     ?? 'tls';

            if (empty($host) || empty($user) || empty($pass)) {
                error_log("envoyerMail : configuration SMTP incomplète");
                return false;
            }

            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

            // SMTP
            $mail->isSMTP();
            $mail->Host       = $host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $user;
            $mail->Password   = $pass;
            $mail->SMTPSecure = $secure === 'ssl'
                ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
                : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $port;
            $mail->CharSet    = 'UTF-8';

            // Expéditeur / destinataire
            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($to);

            // Contenu
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);

            $mail->send();
            return true;

        } catch (\PHPMailer\PHPMailer\Exception $e) {
            error_log("envoyerMail ERREUR PHPMailer : " . ($mail->ErrorInfo ?? $e->getMessage()));
            return false;
        } catch (\Throwable $e) {
            error_log("envoyerMail EXCEPTION : " . $e->getMessage());
            return false;
        }
    }
}