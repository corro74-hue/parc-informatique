<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

final class AboutController extends Controller
{
    /**
     * Page "À propos" de l'application.
     */
    public function index(Request $request): Response
    {
        $appInfo = [
            'name'        => config('app.name', 'Gestion Parc Informatique'),
            'version'     => '0.6.0',
            'release'     => '2026-10-07',
            'environment' => config('app.env', 'local'),
            'timezone'    => config('app.timezone', 'Africa/Algiers'),
            'locale'      => config('app.locale', 'fr'),

            'author'      => [
                'name'      => 'OUAHAB FAYCAL',
                'role'      => 'Développeur Full-Stack',
                'github'    => 'https://github.com/corro74-hue',
                'github_id' => 'corro74-hue',
                'city'      => 'Alger, Algérie',
            ],

            'technologies' => [
                ['name' => 'PHP',              'version' => '8.3.28',  'icon' => 'bi-filetype-php',  'color' => '#777BB4'],
                ['name' => 'MySQL',            'version' => '8.4.7',   'icon' => 'bi-database',      'color' => '#4479A1'],
                ['name' => 'Apache',           'version' => '2.4.65',  'icon' => 'bi-server',        'color' => '#D22128'],
                ['name' => 'Bootstrap',        'version' => '5.3.2',   'icon' => 'bi-bootstrap',     'color' => '#7952B3'],
                ['name' => 'Chart.js',         'version' => '4.4.0',   'icon' => 'bi-bar-chart',     'color' => '#FF6384'],
                ['name' => 'Dompdf',           'version' => '3.0',     'icon' => 'bi-file-pdf',      'color' => '#DC2626'],
                ['name' => 'Composer',         'version' => '2.10.3',  'icon' => 'bi-box',           'color' => '#885630'],
                ['name' => 'PhpSpreadsheet',   'version' => '2.4.8',   'icon' => 'bi-file-excel',    'color' => '#217346'],
            ],

            'stats' => [
                'modules'     => 7,
                'controllers' => count(glob(dirname(__DIR__, 2) . '/app/Controllers/*.php')),
                'views'       => count(glob(dirname(__DIR__, 2) . '/resources/views/**/*.php', GLOB_BRACE)),
                'lines'       => 0, // Sera calculé
            ],

            'modules' => [
                ['icon' => 'bi-box-seam',        'name' => 'Inventaire',    'desc' => 'Gestion complète du parc informatique'],
                ['icon' => 'bi-people',          'name' => 'Affectations',  'desc' => 'Attribution des équipements aux employés'],
                ['icon' => 'bi-tools',           'name' => 'Maintenance',   'desc' => 'Suivi des interventions et réparations'],
                ['icon' => 'bi-recycle',         'name' => 'Réformes',      'desc' => 'Workflow de réforme des équipements'],
                ['icon' => 'bi-clipboard-check', 'name' => 'Campagnes',     'desc' => 'Vérification physique du parc'],
                ['icon' => 'bi-file-earmark',    'name' => 'Documents (GED)','desc' => 'Gestion électronique des documents'],
                ['icon' => 'bi-bar-chart',       'name' => 'Rapports',      'desc' => 'Statistiques et graphiques avancés'],
            ],
        ];

        return $this->view('about.index', [
            'title'   => 'À propos',
            'appInfo' => $appInfo,
        ]);
    }
}