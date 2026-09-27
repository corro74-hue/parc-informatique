<?php
declare(strict_types=1);

namespace App\Services\Auth;

use PragmaRX\Google2FA\Google2FA;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\MySql\UserRepository;

final class TwoFactorService
{
    private Google2FA $google2fa;
    private UserRepositoryInterface $users;

    public function __construct(?UserRepositoryInterface $users = null)
    {
        $this->google2fa = new Google2FA();
        $this->users     = $users ?? new UserRepository();
    }

    /**
     * Génère un nouveau secret 2FA pour un utilisateur.
     * Retourne le secret (base32) à afficher sous forme de QR code.
     */
    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey(32);
    }

    /**
     * Génère l'URL du QR Code pour Google Authenticator.
     *
     * @param string $company Nom de l'application (ex: "Parc Info")
     * @param string $email   Email de l'utilisateur
     * @param string $secret  Secret généré
     */
    public function getQrCodeUrl(string $company, string $email, string $secret): string
    {
        return $this->google2fa->getQRCodeUrl($company, $email, $secret);
    }

    /**
     * Vérifie un code TOTP à 6 chiffres.
     *
     * @param string $secret Le secret stocké en BDD
     * @param string $code   Le code saisi par l'utilisateur
     * @param int    $window Nombre de fenêtres de tolérance (1 = ±30 secondes)
     */
    public function verifyCode(string $secret, string $code, int $window = 1): bool
    {
        // Nettoie le code (supprime espaces, tirets, etc.)
        $code = preg_replace('/[^0-9]/', '', $code);

        if (strlen($code) !== 6) {
            return false;
        }

        return (bool) $this->google2fa->verifyKey($secret, $code, $window);
    }

    /**
     * Génère des codes de secours (backup codes).
     * Format : XXXX-XXXX (8 caractères alphanumériques, tiret au milieu).
     *
     * @param int $count Nombre de codes à générer
     * @return array<int, string>
     */
    public function generateBackupCodes(int $count = 8): array
    {
        $codes = [];
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        for ($i = 0; $i < $count; $i++) {
            $part1 = '';
            $part2 = '';

            for ($j = 0; $j < 4; $j++) {
                $part1 .= $chars[random_int(0, strlen($chars) - 1)];
                $part2 .= $chars[random_int(0, strlen($chars) - 1)];
            }

            $codes[] = $part1 . '-' . $part2;
        }

        return $codes;
    }

    /**
     * Active la 2FA pour un utilisateur.
     * Stocke le secret et les codes de secours (hashés).
     *
     * @return array{secret: string, backup_codes: array<int, string>}
     */
    public function enable(int $userId, string $secret): array
    {
        // Génère 8 codes de secours
        $backupCodes = $this->generateBackupCodes(8);

        // Hache chaque code de secours (pour stockage sécurisé)
        $hashedCodes = array_map(
            fn($code) => password_hash($code, PASSWORD_ARGON2ID),
            $backupCodes
        );

        // Active la 2FA dans la BDD
        $this->users->enableTwoFactor($userId, $secret, $hashedCodes);

        // Retourne les codes en clair (à afficher UNE SEULE FOIS à l'utilisateur)
        return [
            'secret'       => $secret,
            'backup_codes' => $backupCodes,
        ];
    }

    /**
     * Désactive la 2FA pour un utilisateur.
     */
    public function disable(int $userId): void
    {
        $this->users->disableTwoFactor($userId);
    }

    /**
     * Vérifie un code de secours (backup code).
     * Si valide, le supprime de la liste (à usage unique).
     */
    public function verifyBackupCode(int $userId, string $code): bool
    {
        $user = $this->users->findById($userId);
        if (!$user) {
            return false;
        }

        // Récupère les codes de secours hashés depuis la BDD
        $hashedCodes = $this->users->getBackupCodes($userId);
        if (empty($hashedCodes)) {
            return false;
        }

        // Normalise le code saisi (majuscules, tiret au milieu)
        $code = strtoupper(trim($code));

        // Cherche une correspondance
        $matchedIndex = null;
        foreach ($hashedCodes as $index => $hash) {
            if (password_verify($code, $hash)) {
                $matchedIndex = $index;
                break;
            }
        }

        if ($matchedIndex === null) {
            return false;
        }

        // Supprime le code utilisé (usage unique)
        $this->users->removeBackupCode($userId, $matchedIndex);

        return true;
    }
}