<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue sur <?= e($appName ?? 'Parc Info') ?></title>
</head>
<body style="margin: 0; padding: 0; background: #f5f7fa; font-family: -apple-system, 'Segoe UI', Roboto, Arial, sans-serif;">

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background: #f5f7fa; padding: 30px 15px;">
        <tr>
            <td align="center">

                <!-- Carte principale -->
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width: 600px; background: #ffffff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); overflow: hidden;">

                    <!-- En-tête -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 35px 30px; text-align: center;">
                            <div style="font-size: 48px; margin-bottom: 10px;">🖥️</div>
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 700;">
                                <?= e($appName ?? 'Parc Info') ?>
                            </h1>
                            <p style="color: rgba(255,255,255,0.85); margin: 8px 0 0; font-size: 14px;">
                                Gestion de Parc Informatique
                            </p>
                        </td>
                    </tr>

                    <!-- Corps -->
                    <tr>
                        <td style="padding: 35px 30px;">

                            <h2 style="color: #1e293b; margin: 0 0 15px; font-size: 20px; font-weight: 700;">
                                Bienvenue, <?= e($firstName ?? 'Utilisateur') ?> ! 👋
                            </h2>

                            <p style="color: #475569; font-size: 15px; line-height: 1.6; margin: 0 0 20px;">
                                Votre compte a été créé avec succès sur l'application
                                <strong><?= e($appName ?? 'Parc Info') ?></strong>.
                                Voici vos informations de connexion :
                            </p>

                            <!-- Bloc identifiants -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background: #f8fafc; border-left: 4px solid #667eea; border-radius: 8px; margin: 20px 0;">
                                <tr>
                                    <td style="padding: 20px;">

                                        <p style="margin: 0 0 12px; color: #64748b; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                            Vos identifiants
                                        </p>

                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td style="padding: 6px 0; color: #64748b; font-size: 13px; width: 130px;">
                                                    Nom d'utilisateur :
                                                </td>
                                                <td style="padding: 6px 0; color: #1e293b; font-size: 14px; font-weight: 600;">
                                                    <?= e($username ?? '') ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 6px 0; color: #64748b; font-size: 13px;">
                                                    Mot de passe :
                                                </td>
                                                <td style="padding: 6px 0;">
                                                    <code style="background: #fef3c7; color: #92400e; padding: 4px 10px; border-radius: 6px; font-size: 14px; font-weight: 700; font-family: ui-monospace, monospace; letter-spacing: 1px;">
                                                        <?= e($password ?? '') ?>
                                                    </code>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 6px 0; color: #64748b; font-size: 13px;">
                                                    Application :
                                                </td>
                                                <td style="padding: 6px 0; color: #1e293b; font-size: 14px;">
                                                    <a href="<?= e($appUrl ?? '#') ?>" style="color: #667eea; text-decoration: none; font-weight: 600;">
                                                        <?= e($appUrl ?? '') ?>
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>

                                    </td>
                                </tr>
                            </table>

                            <!-- Avertissement -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 8px; margin: 20px 0;">
                                <tr>
                                    <td style="padding: 15px 20px;">
                                        <p style="margin: 0; color: #92400e; font-size: 13px; line-height: 1.5;">
                                            ⚠️ <strong>Important :</strong> Lors de votre première connexion, vous devrez
                                            <strong>changer ce mot de passe</strong> pour des raisons de sécurité.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Bouton -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin: 25px auto 0;">
                                <tr>
                                    <td align="center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px;">
                                        <a href="<?= e($loginUrl ?? '#') ?>"
                                           style="display: inline-block; padding: 14px 40px; color: #ffffff; text-decoration: none; font-size: 15px; font-weight: 600;">
                                            🔐 Se connecter
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="color: #94a3b8; font-size: 12px; text-align: center; margin: 25px 0 0; line-height: 1.6;">
                                Si vous n'êtes pas à l'origine de cette inscription, contactez l'administrateur.
                            </p>

                        </td>
                    </tr>

                    <!-- Pied de page -->
                    <tr>
                        <td style="background: #f8fafc; padding: 20px 30px; text-align: center; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0; color: #94a3b8; font-size: 12px;">
                                Cet email a été envoyé automatiquement par
                                <strong style="color: #667eea;"><?= e($appName ?? 'Parc Info') ?></strong>.
                            </p>
                            <p style="margin: 5px 0 0; color: #cbd5e1; font-size: 11px;">
                                © <?= date('Y') ?> <?= e($appName ?? 'Parc Info') ?> — Tous droits réservés
                            </p>
                        </td>
                    </tr>

                </table>

                <!-- Info bas de page -->
                <p style="color: #94a3b8; font-size: 11px; text-align: center; margin: 15px 0 0;">
                    Cet email contient des informations confidentielles. Merci de ne pas le partager.
                </p>

            </td>
        </tr>
    </table>

</body>
</html>