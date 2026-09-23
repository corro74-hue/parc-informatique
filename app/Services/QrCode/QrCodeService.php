<?php
declare(strict_types=1);

namespace App\Services\QrCode;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\Font\OpenSans;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;

final class QrCodeService
{
    /**
     * Génère un QR code en base64 (data URI) pour l'afficher dans une balise <img>
     *
     * @param string $content  Le contenu du QR code (URL, texte, etc.)
     * @param int    $size     Taille en pixels (par défaut 300)
     * @param string $format   'png' ou 'svg'
     */
    public function generateBase64(string $content, int $size = 300, string $format = 'png'): string
    {
        $writer = $format === 'svg' ? new SvgWriter() : new PngWriter();

        $builder = new Builder();
        $builder->writer($writer);
        $builder->data($content);
        $builder->encoding(new Encoding('UTF-8'));
        $builder->errorCorrectionLevel(ErrorCorrectionLevel::High);
        $builder->size($size);
        $builder->margin(10);
        $builder->roundBlockSizeMode(RoundBlockSizeMode::Margin);

        $result = $builder->build();

        return $result->getDataUri();
    }

    /**
     * Génère un QR code avec un label sous le code
     */
    public function generateWithLabel(string $content, string $label, int $size = 300): string
    {
        $builder = new Builder();
        $builder->writer(new PngWriter());
        $builder->data($content);
        $builder->encoding(new Encoding('UTF-8'));
        $builder->errorCorrectionLevel(ErrorCorrectionLevel::High);
        $builder->size($size);
        $builder->margin(15);
        $builder->roundBlockSizeMode(RoundBlockSizeMode::Margin);
        $builder->labelText($label);
        $builder->labelFont(new OpenSans(14));
        $builder->labelAlignment(LabelAlignment::Center);

        $result = $builder->build();

        return $result->getDataUri();
    }

    /**
     * Génère un QR code pour un équipement (contient l'URL de sa fiche)
     */
    public function forEquipment(string $inventoryNumber, string $url, int $size = 300): string
    {
        return $this->generateBase64($url, $size);
    }
}