<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Gestion des jetons CSRF.
 *
 * Un seul jeton par session, stocké dans $_SESSION['_csrf_token'].
 * Utilisé partout : formulaires HTML (csrf_field) et requêtes AJAX (csrf_token).
 */
final class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    /**
     * Génère (ou retourne) le jeton de la session courante.
     */
    public static function token(): string
    {
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Vérifie qu'un jeton fourni correspond à celui de la session.
     */
    public static function verify(?string $token): bool
    {
        if ($token === null || $token === '') {
            return false;
        }

        if (empty($_SESSION[self::SESSION_KEY])) {
            return false;
        }

        return hash_equals($_SESSION[self::SESSION_KEY], $token);
    }

    /**
     * Retourne un champ <input hidden> prêt à insérer dans un formulaire.
     */
    public static function field(): string
    {
        $token = self::token();

        return '<input type="hidden" name="_token" value="'
            . htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
            . '">';
    }

    /**
     * Régénère le jeton (à appeler après login/logout par exemple).
     */
    public static function regenerate(): void
    {
        $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
    }
}