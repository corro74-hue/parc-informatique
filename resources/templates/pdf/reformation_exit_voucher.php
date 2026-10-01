<?php
/**
 * Template PDF — Bon de sortie de réforme
 *
 * @var \App\Models\Reformation $reformation
 * @var \App\Models\ReformationItem[] $items
 * @var string $generatedAt
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bon de sortie <?= htmlspecialchars($reformation->reference) ?></title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1f2937;
            margin: 0;
            padding: 30px 40px;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #dc2626;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header .republic {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #6b7280;
            margin-bottom: 8px;
        }
        .header h1 {
            font-size: 22px;
            color: #dc2626;
            margin: 5px 0;
            text-transform: uppercase;
        }
        .header h2 {
            font-size: 14px;
            color: #374151;
            margin: 5px 0;
            font-weight: normal;
        }
        .header .ref {
            display: inline-block;
            background: #dc2626;
            color: #fff;
            padding: 4px 12px;
            border-radius: 3px;
            font-family: monospace;
            font-size: 12px;
            margin-top: 8px;
        }
        .info-block {
            background: #fef2f2;
            border-left: 4px solid #dc2626;
            padding: 10px 15px;
            margin-bottom: 20px;
        }
        .info-block table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-block td {
            padding: 3px 8px 3px 0;
            vertical-align: top;
        }
        .info-block .label {
            font-weight: bold;
            color: #4b5563;
            width: 130px;
        }
        h3 {
            font-size: 13px;
            color: #dc2626;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 4px;
            margin: 20px 0 10px 0;
            text-transform: uppercase;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table.data th {
            background: #dc2626;
            color: #fff;
            padding: 6px 8px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            border: 1px solid #dc2626;
        }
        table.data td {
            padding: 6px 8px;
            border: 1px solid #d1d5db;
            font-size: 10px;
        }
        table.data tr:nth-child(even) td {
            background: #fef2f2;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .notice {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            border-radius: 4px;
            padding: 10px 15px;
            margin: 15px 0;
            font-size: 10px;
            color: #78350f;
        }
        .signatures {
            margin-top: 50px;
            page-break-inside: avoid;
        }
        .signatures table {
            width: 100%;
            border-collapse: collapse;
        }
        .signatures td {
            width: 50%;
            text-align: center;
            padding-top: 60px;
            vertical-align: bottom;
        }
        .signatures .line {
            border-top: 1px solid #374151;
            padding-top: 5px;
            font-size: 10px;
            color: #4b5563;
        }
        .footer {
            position: fixed;
            bottom: 20px;
            left: 40px;
            right: 40px;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
        }
    </style>
</head>
<body>

    <!-- En-tête -->
    <div class="header">
        <div class="republic">République Algérienne Démocratique et Populaire</div>
        <h1>Bon de Sortie</h1>
        <h2>Sortie d'équipements du parc informatique</h2>
        <div class="ref"><?= htmlspecialchars($reformation->reference) ?></div>
    </div>

    <!-- Informations -->
    <div class="info-block">
        <table>
            <tr>
                <td class="label">Titre :</td>
                <td><?= htmlspecialchars($reformation->title) ?></td>
            </tr>
            <tr>
                <td class="label">Motif :</td>
                <td><?= htmlspecialchars($reformation->getReasonLabel()) ?></td>
            </tr>
            <tr>
                <td class="label">Réf. PV :</td>
                <td><?= htmlspecialchars($reformation->reference) ?></td>
            </tr>
            <?php if ($reformation->commissionReference): ?>
            <tr>
                <td class="label">Réf. commission :</td>
                <td><?= htmlspecialchars($reformation->commissionReference) ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td class="label">Date d'émission :</td>
                <td><?= htmlspecialchars($generatedAt) ?></td>
            </tr>
        </table>
    </div>

    <!-- Liste des équipements à sortir -->
    <h3>Équipements autorisés à sortir</h3>
    <table class="data">
        <thead>
            <tr>
                <th style="width:30px;">#</th>
                <th style="width:120px;">N° Inventaire</th>
                <th>Désignation</th>
                <th style="width:110px;">Catégorie</th>
                <th style="width:50px;" class="text-center">Qté</th>
                <th style="width:130px;">Observations</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; foreach ($items as $item): ?>
                <tr>
                    <td class="text-center"><?= $i++ ?></td>
                    <td><code><?= htmlspecialchars($item->equipmentInventoryNumber ?? '—') ?></code></td>
                    <td><?= htmlspecialchars($item->equipmentDesignation ?? '—') ?></td>
                    <td><?= htmlspecialchars($item->equipmentCategoryName ?? '—') ?></td>
                    <td class="text-center"><?= (int) $item->quantity ?></td>
                    <td><?= htmlspecialchars($item->conditionNotes ?? '—') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Avis -->
    <div class="notice">
        <strong>⚠️ Important :</strong>
        Le présent bon de sortie autorise la sortie définitive des équipements listés ci-dessus
        du parc informatique de l'établissement. Toute sortie doit être accompagnée du présent document
        signé par les autorités compétentes.
    </div>

    <!-- Signatures -->
    <div class="signatures">
        <table>
            <tr>
                <td>
                    <div class="line">Le Responsable du parc informatique</div>
                </td>
                <td>
                    <div class="line">Le Directeur / Le Secrétaire Général</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        Document généré automatiquement le <?= htmlspecialchars($generatedAt) ?>
        — <?= htmlspecialchars($reformation->reference) ?>
    </div>

</body>
</html>