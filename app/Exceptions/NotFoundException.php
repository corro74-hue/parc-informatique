<?php
declare(strict_types=1);

namespace App\Exceptions;

class NotFoundException extends \RuntimeException
{
    public function __construct(string $message = 'Ressource introuvable', int $code = 404)
    {
        parent::__construct($message, $code);
    }
}