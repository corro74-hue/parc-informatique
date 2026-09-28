<?php
declare(strict_types=1);

namespace App\Services\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

final class MailService
{
    private PHPMailer $mailer;
    private bool $enabled;

    public function __construct()
    {
        $this->enabled = filter_var($_ENV['MAIL_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $this->mailer = new PHPMailer(true);

        // Configuration SMTP
        $this->mailer->isSMTP();
        $this->mailer->Host       = (string) ($_ENV['MAIL_HOST'] ?? 'smtp.gmail.com');
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = (string) ($_ENV['MAIL_USERNAME'] ?? '');
        $this->mailer->Password   = (string) ($_ENV['MAIL_PASSWORD'] ?? '');
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port       = (int) ($_ENV['MAIL_PORT'] ?? 587);
        $this->mailer->CharSet    = 'UTF-8';

        // Expéditeur par défaut
        $fromAddress = (string) ($_ENV['MAIL_FROM_ADDRESS'] ?? '');
        $fromName    = (string) ($_ENV['MAIL_FROM_NAME'] ?? 'Parc Info');
        $this->mailer->setFrom($fromAddress, $fromName);
    }

    /**
     * Envoie un email.
     *
     * @param string $to       Destinataire (email)
     * @param string $subject  Sujet
     * @param string $htmlBody Corps de l'email en HTML
     * @param string|null $altBody Version texte brut (optionnel)
     * @return bool True si envoyé, false sinon
     */
    public function send(string $to, string $subject, string $htmlBody, ?string $altBody = null): bool
    {
        // Si l'envoi d'email est désactivé, on log et on retourne true
        if (!$this->enabled) {
            $this->log('Email désactivé (MAIL_ENABLED=false). Destinataire : ' . $to);
            return true;
        }

        try {
            // Réinitialise les destinataires (si plusieurs envois)
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            // Destinataire
            $this->mailer->addAddress($to);

            // Contenu
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $htmlBody;
            $this->mailer->AltBody = $altBody ?? strip_tags($htmlBody);

            // Envoi
            $this->mailer->send();

            $this->log('Email envoyé à ' . $to . ' — Sujet : ' . $subject);
            return true;

        } catch (PHPMailerException $e) {
            $this->log('ERREUR envoi email à ' . $to . ' : ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Envoie un email à partir d'un template de vue.
     *
     * @param string $to
     * @param string $subject
     * @param string $viewName  Nom du template (ex: 'emails.welcome')
     * @param array  $data      Variables à passer au template
     * @return bool
     */
    public function sendTemplate(string $to, string $subject, string $viewName, array $data = []): bool
    {
        $html = $this->renderView($viewName, $data);
        return $this->send($to, $subject, $html);
    }

    /**
     * Rend une vue PHP en HTML.
     */
    private function renderView(string $viewName, array $data): string
    {
        $viewPath = dirname(__DIR__, 3) . '/resources/views/' . str_replace('.', '/', $viewName) . '.php';

        if (!is_file($viewPath)) {
            throw new \RuntimeException('Template email introuvable : ' . $viewName);
        }

        return (function () use ($viewPath, $data) {
            extract($data, EXTR_SKIP);
            ob_start();
            require $viewPath;
            return ob_get_clean();
        })();
    }

    /**
     * Log dans storage/logs/mail.log
     */
    private function log(string $message, string $level = 'info'): void
    {
        $logDir = dirname(__DIR__, 3) . '/storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/mail.log';
        $line = '[' . date('Y-m-d H:i:s') . '] [' . strtoupper($level) . '] ' . $message . PHP_EOL;

        @file_put_contents($logFile, $line, FILE_APPEND);
    }

    /**
     * Teste la connexion SMTP.
     */
    public function testConnection(): array
    {
        try {
            $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
            $this->mailer->smtpConnect();
            $this->mailer->smtpClose();

            return [
                'success' => true,
                'message' => 'Connexion SMTP réussie.',
            ];
        } catch (PHPMailerException $e) {
            return [
                'success' => false,
                'message' => 'Erreur SMTP : ' . $e->getMessage(),
            ];
        }
    }
}