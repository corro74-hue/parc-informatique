<?php
declare(strict_types=1);

namespace App\Services\Security;

final class PasswordGeneratorService
{
    /**
     * Caractères autorisés (sans ambigus : l, I, 1, O, 0)
     */
    private const LOWERCASE = 'abcdefghijkmnopqrstuvwxyz';        // sans 'l'
    private const UPPERCASE = 'ABCDEFGHJKLMNPQRSTUVWXYZ';          // sans 'I', 'O'
    private const DIGITS    = '23456789';                          // sans '0', '1'
    private const SPECIAL   = '!@#$%^&*-_=+';

    /**
     * Génère un mot de passe fort et aléatoire.
     *
     * @param int  $length           Longueur (16 par défaut)
     * @param bool $requireUppercase Inclure des majuscules
     * @param bool $requireLowercase Inclure des minuscules
     * @param bool $requireDigit     Inclure des chiffres
     * @param bool $requireSpecial   Inclure des caractères spéciaux
     * @return string
     */
    public function generate(
        int $length = 16,
        bool $requireUppercase = true,
        bool $requireLowercase = true,
        bool $requireDigit = true,
        bool $requireSpecial = true
    ): string {
        // Sécurité : longueur minimale
        $length = max(12, $length);

        // Construit le pool de caractères
        $pool = '';
        $guaranteedChars = [];

        if ($requireLowercase) {
            $pool .= self::LOWERCASE;
            $guaranteedChars[] = self::LOWERCASE[random_int(0, strlen(self::LOWERCASE) - 1)];
        }
        if ($requireUppercase) {
            $pool .= self::UPPERCASE;
            $guaranteedChars[] = self::UPPERCASE[random_int(0, strlen(self::UPPERCASE) - 1)];
        }
        if ($requireDigit) {
            $pool .= self::DIGITS;
            $guaranteedChars[] = self::DIGITS[random_int(0, strlen(self::DIGITS) - 1)];
        }
        if ($requireSpecial) {
            $pool .= self::SPECIAL;
            $guaranteedChars[] = self::SPECIAL[random_int(0, strlen(self::SPECIAL) - 1)];
        }

        if ($pool === '') {
            throw new \RuntimeException('Au moins un type de caractère doit être activé.');
        }

        // Génère les caractères restants
        $password = $guaranteedChars;
        $remaining = $length - count($guaranteedChars);

        for ($i = 0; $i < $remaining; $i++) {
            $password[] = $pool[random_int(0, strlen($pool) - 1)];
        }

        // Mélange le mot de passe (Fisher-Yates via shuffle)
        shuffle($password);

        return implode('', $password);
    }

    /**
     * Génère un mot de passe temporaire pour reset admin.
     * Format plus court et lisible (12 caractères).
     */
    public function generateTemporary(): string
    {
        return $this->generate(12);
    }
}