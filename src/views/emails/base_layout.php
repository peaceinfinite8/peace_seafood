<?php
// Base email layout — expects `$content` to contain HTML-safe inner content
$mail_logo = $mail_logo ?? ($_ENV['MAIL_LOGO'] ?? null);
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo htmlspecialchars($subject ?? 'Peace Seafood'); ?></title>
</head>

<body style="margin:0;padding:0;font-family:Inter, 'Segoe UI', Roboto, Arial, sans-serif;background: linear-gradient(135deg,#0ea5e9 0%,#0284c7 50%,#0f172a 100%);-webkit-font-smoothing:antialiased;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center" style="padding:40px 16px;">
                <table width="600" cellpadding="0" cellspacing="0" role="presentation" style="max-width:600px;background:#ffffff;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td style="padding:20px 28px 10px;border-bottom:1px solid rgba(15,23,42,0.06);">
                            <div style="display:flex;align-items:center;gap:12px;">
                                <?php if ($mail_logo): ?>
                                    <img src="<?php echo htmlspecialchars($mail_logo); ?>" alt="Peace Seafood" style="height:36px;object-fit:contain;" />
                                <?php else: ?>
                                    <div style="font-weight:700;color:#0f172a;font-size:18px">Peace Seafood WMS</div>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            <?php echo $content ?? ''; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 28px;background:#f8fafc;color:#334155;font-size:13px;border-top:1px solid rgba(15,23,42,0.04);">
                            <div style="max-width:520px;margin:0 auto;text-align:center;">
                                <div style="margin-bottom:6px;color:#334155">Peace Seafood WMS</div>
                                <div style="color:#64748b;font-size:12px;line-height:16px">Jika Anda tidak meminta email ini, abaikan saja.</div>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>