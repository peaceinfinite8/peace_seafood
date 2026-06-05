<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 50%, #0f172a 100%); min-height: 100vh;">
    <div style="max-width: 600px; margin: 40px auto; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%); padding: 30px; text-align: center;">
            <h1 style="color: white; margin: 0; font-size: 24px; font-weight: bold;">🔐 Reset Password</h1>
        </div>
        
        <!-- Content -->
        <div style="padding: 40px 30px;">
            <p style="font-size: 16px; color: #334155; line-height: 1.6; margin-top: 0;">
                Halo <strong><?= htmlspecialchars($name ?? 'User') ?></strong>,
            </p>
            
            <p style="font-size: 16px; color: #334155; line-height: 1.6;">
                Kami menerima permintaan untuk mereset password akun Anda. Klik tombol di bawah untuk membuat password baru:
            </p>
            
            <!-- Reset Button -->
            <div style="text-align: center; margin: 35px 0;">
                <a href="<?= $resetLink ?>" 
                   style="display: inline-block; padding: 16px 40px; background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; box-shadow: 0 4px 6px rgba(14, 165, 233, 0.3);">
                    Reset Password Saya
                </a>
            </div>
            
            <!-- Alternative Link -->
            <div style="background: #f1f5f9; border-left: 4px solid #0ea5e9; padding: 15px; border-radius: 4px; margin: 25px 0;">
                <p style="margin: 0; font-size: 14px; color: #475569;">
                    <strong>Atau copy link berikut:</strong><br>
                    <a href="<?= $resetLink ?>" style="color: #0ea5e9; word-break: break-all;">
                        <?= $resetLink ?>
                    </a>
                </p>
            </div>
            
            <!-- Security Notice -->
            <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; border-radius: 4px; margin: 25px 0;">
                <p style="margin: 0; font-size: 14px; color: #78350f;">
                    <strong>⚠️ Perhatian Keamanan:</strong><br>
                    • Link ini akan kadaluarsa dalam <strong>1 jam</strong><br>
                    • Link hanya bisa digunakan <strong>sekali</strong><br>
                    • Jika Anda tidak meminta reset password, abaikan email ini
                </p>
            </div>
            
            <!-- Info -->
            <p style="font-size: 14px; color: #64748b; line-height: 1.6; margin-bottom: 0;">
                <strong>Detail Permintaan:</strong><br>
                Waktu: <?= date('d M Y H:i') ?> WIB<br>
                IP Address: <?= $_SERVER['REMOTE_ADDR'] ?? 'Unknown' ?><br>
                Browser: <?= substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 50) ?>...
            </p>
        </div>
        
        <!-- Footer -->
        <div style="background: #f8fafc; padding: 20px 30px; border-top: 1px solid #e2e8f0; text-align: center;">
            <p style="margin: 0; font-size: 12px; color: #94a3b8;">
                Email ini dikirim dari <strong>Peace Seafood WMS</strong><br>
                Jika Anda mengalami kesulitan, hubungi administrator sistem.
            </p>
        </div>
    </div>
</body>
</html>
