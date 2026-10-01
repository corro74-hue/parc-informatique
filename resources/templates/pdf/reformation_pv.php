<?php
/**
 * Template PDF — Procès-Verbal de réforme
 *
 * @var \App\Models\Reformation $reformation
 * @var \App\Models\ReformationItem[] $items
 * @var \App\Models\ReformationDecision[] $decisions
 * @var string $generatedAt
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>PV de réforme <?= htmlspecialchars($reformation->reference) ?></title>
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
            border-bottom: 3px double #1e40af;
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
            font-size: 20px;
            color: #1e40af;
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
            background: #1e40af;
            color: #fff;
            padding: 4px 12px;
            border-radius: 3px;
            font-family: monospace;
            font-size: 12px;
            margin-top: 8px;
        }
        .info-block {
            background: #f3f4f6;
            border-left: 4px solid #1e40af;
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
            color: #1e40af;
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
            background: #1e40af;
            color: #fff;
            padding: 6px 8px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            border: 1px solid #1e40af;
        }
        table.data td {
            padding: 6px 8px;
            border: 1px solid #d1d5db;
            font-size: 10px;
        }
        table.data tr:nth-child(even) td {
            background: #f9fafb;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row {
            background: #dbeafe !important;
            font-weight: bold;
        }
        .decision-box {
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 4px;
            padding: 10px 15px;
            margin-bottom: 10px;
        }
        .decision-box .date {
            font-size: 10px;
            color: #6b7280;
        }
        .decision-box .notes {
            margin: 8px 0 0 0;
            font-style: italic;
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
        .signatures {
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .signatures table {
            width: 100%;
            border-collapse: collapse;
        }
        .signatures td {
            width: 33%;
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
    </style>
</head>
<body>

    <!-- En-tête -->
    <div class="header">
        <div class="republic">République Algérienne Démocratique et Populaire</div>
        <h1>Procès-Verbal de Réforme</h1>
        <h2>Commission de réforme du parc informatique</h2>
        <div class="ref"><?= htmlspecialchars($reformation->reference) ?></div>
    </div>

    <!-- Informations générales -->
    <div class="info-block">
        <table>
            <tr>
                <td class="label">Titre :</td>
                <td><?= htmlspecialchars($reformation->title) ?></td>
                <td class="label">Date du PV :</td>
                <td><?= htmlspecialchars($generatedAt) ?></td>
            </tr>
            <tr>
                <td class="label">Motif :</td>
                <td><?= htmlspecialchars($reformation->getReasonLabel()) ?></td>
                <td class="label">Réf. commission :</td>
                <td><?= htmlspecialchars($reformation->commissionReference ?? '—') ?></td>
            </tr>
            <?php if ($reformation->meetingDate): ?>
            <tr>
                <td class="label">Date de réunion :</td>
                <td colspan="3"><?= htmlspecialchars(date('d/m/Y', strtotime($reformation->meetingDate))) ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($reformation->reasonDetails): ?>
            <tr>
                <td class="label">Détails du motif :</td>
                <td colspan="3"><?= nl2br(htmlspecialchars($reformation->reasonDetails)) ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Liste des équipements -->
    <h3>Liste des équipements concernés</h3>
    <table class="data">
        <thead>
            <tr>
                <th style="width:30px;">#</th>
                <th style="width:110px;">N° Inventaire</th>
                <th>Désignation</th>
                <th style="width:100px;">Catégorie</th>
                <th style="width:50px;" class="text-center">Qté</th>
                <th style="width:90px;" class="text-right">Valeur unit.</th>
                <th style="width:90px;" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; $grandTotal = 0; foreach ($items as $item): ?>
                <?php
                $lineTotal = $item->estimatedValue * $item->quantity;
                $grandTotal += $lineTotal;
                ?>
                <tr>
                    <td class="text-center"><?= $i++ ?></td>
                    <td><code><?= htmlspecialchars($item->equipmentInventoryNumber ?? '—') ?></code></td>
                    <td><?= htmlspecialchars($item->equipmentDesignation ?? '—') ?></td>
                    <td><?= htmlspecialchars($item->equipmentCategoryName ?? '—') ?></td>
                    <td class="text-center"><?= (int) $item->quantity ?></td>
                    <td class="text-right"><?= number_format($item->estimatedValue, 2, ',', ' ') ?> DA</td>
                    <td class="text-right"><?= number_format($lineTotal, 2, ',', ' ') ?> DA</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="6" class="text-right">VALEUR TOTALE ESTIMÉE :</td>
                <td class="text-right"><?= number_format($grandTotal, 2, ',', ' ') ?> DA</td>
            </tr>
        </tfoot>
    </table>

    <!-- Décisions de la commission -->
    <?php if (!empty($decisions)): ?>
        <h3>Décisions de la commission</h3>
        <?php foreach ($decisions as $d): ?>
            <div class="decision-box">
                <strong><?= htmlspecialchars($d->getDecisionLabel()) ?></strong>
                <span class="date">— <?= htmlspecialchars(date('d/m/Y', strtotime($d->decisionDate))) ?></span>
                <?php if ($d->notes): ?>
                    <p class="notes">« <?= nl2br(htmlspecialchars($d->notes)) ?> »</p>
                <?php endif; ?>
                <?php $members = $d->getCommissionMembersList(); ?>
                <?php if (!empty($members)): ?>
                    <div style="margin-top:6px; font-size:10px; color:#4b5563;">
                        <strong>Membres :</strong> <?= htmlspecialchars(implode(', ', $members)) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Signatures -->
    <div class="signatures">
        <table>
            <tr>
                <td>
                    <div class="line">Le Président de la commission</div>
                </td>
                <td>
                    <div class="line">Le Responsable du parc</div>
                </td>
                <td>
                    <div class="line">Le Directeur</div>
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