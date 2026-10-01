<?php
declare(strict_types=1);

namespace App\Services\Reform;

use App\Models\Reformation;
use App\Models\ReformationDecision;
use App\Models\ReformationItem;
use Dompdf\Dompdf;
use Dompdf\Options;
use RuntimeException;

final class ReformationPdfService
{
    private const STORAGE_SUBDIR = 'documents/reformations';

    public function __construct(
        private readonly string $projectRoot = '',
    ) {}

    /**
     * Génère le PV de réforme et retourne le chemin relatif
     * (à stocker dans reformations.pv_path).
     */
    public function generatePv(Reformation $r, array $items, array $decisions): string
    {
        $html = $this->render('reformation_pv', [
            'reformation'  => $r,
            'items'        => $items,
            'decisions'    => $decisions,
            'generatedAt'  => date('d/m/Y H:i'),
        ]);

        $relativePath = self::STORAGE_SUBDIR . '/pv/' . $r->reference . '.pdf';
        $this->writePdf($html, $relativePath);

        return $relativePath;
    }

    /**
     * Génère le bon de sortie et retourne le chemin relatif.
     */
    public function generateExitVoucher(Reformation $r, array $items): string
    {
        $html = $this->render('reformation_exit_voucher', [
            'reformation' => $r,
            'items'       => $items,
            'generatedAt' => date('d/m/Y H:i'),
        ]);

        $relativePath = self::STORAGE_SUBDIR . '/bons-sortie/' . $r->reference . '.pdf';
        $this->writePdf($html, $relativePath);

        return $relativePath;
    }

    // ============================================================
    // HELPERS PRIVÉS
    // ============================================================

    private function render(string $template, array $data): string
    {
        $path = $this->templatesDir() . DIRECTORY_SEPARATOR . $template . '.php';
        if (!is_file($path)) {
            throw new RuntimeException("Template PDF introuvable : {$path}");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $path;
        return (string) ob_get_clean();
    }

    /**
     * Version ROBUSTE : lance une exception claire si quelque chose échoue.
     */
    private function writePdf(string $html, string $relativePath): void
    {
        // 1. Configuration Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // 2. Calcul du chemin absolu (Windows-friendly)
        $fullPath = $this->storageDir() . DIRECTORY_SEPARATOR
                  . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
        $dir = dirname($fullPath);

        // 3. Vérification / création du dossier
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0775, true) && !is_dir($dir)) {
                throw new RuntimeException("Impossible de créer le dossier : {$dir}");
            }
        }

        if (!is_writable($dir)) {
            throw new RuntimeException("Le dossier n'est pas accessible en écriture : {$dir}");
        }

        // 4. Vérification que Dompdf produit du contenu
        $content = $dompdf->output();
        if ($content === '' || $content === false || $content === null) {
            throw new RuntimeException("Dompdf a produit un contenu vide.");
        }

        // 5. Écriture avec détection d'erreur
        $bytes = @file_put_contents($fullPath, $content);
        if ($bytes === false) {
            $lastError = error_get_last();
            $errMsg = $lastError['message'] ?? 'Erreur inconnue';
            throw new RuntimeException("Échec d'écriture du PDF dans : {$fullPath} — {$errMsg}");
        }

        // 6. Vérification finale
        if (!is_file($fullPath)) {
            throw new RuntimeException("Le fichier n'existe pas après écriture : {$fullPath}");
        }
        if (filesize($fullPath) === 0) {
            throw new RuntimeException("Le fichier PDF est vide : {$fullPath}");
        }
    }

    private function projectRoot(): string
    {
        return $this->projectRoot !== ''
            ? $this->projectRoot
            : dirname(__DIR__, 3);
    }

    private function storageDir(): string
    {
        return $this->projectRoot() . DIRECTORY_SEPARATOR . 'storage';
    }

    private function templatesDir(): string
    {
        return $this->projectRoot() . DIRECTORY_SEPARATOR . 'resources'
             . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'pdf';
    }
}