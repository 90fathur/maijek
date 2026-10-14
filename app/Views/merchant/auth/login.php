<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Login Mitra Restoran | MaiFood' ?></title>
    <!-- Google Font & FontAwesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        :root {
            --primary: #ea580c;
            --primary-hover: #c2410c;
            --primary-glow: rgba(234, 88, 12, 0.25);
            --bg-body: #0f172a;
            --bg-card: #1e293b;
            --border-color: #334155;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: var(--bg-body);
            color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
            background-image: radial-gradient(rgba(234, 88, 12, 0.15) 1px, transparent 1px);
            background-size: 32px 32px;
        }

        .login-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            width: 100%;
            max-width: 440px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
        }

        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo-box {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #f97316, #ea580c);
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            box-shadow: 0 8px 24px var(--primary-glow);
            margin-bottom: 16px;
        }

        .login-title {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: white;
            margin-bottom: 6px;
        }

        .login-subtitle {
            font-size: 0.875rem;
            color: #94a3b8;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #cbd5e1;
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px 14px 44px;
            background: #0f172a;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            color: white;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.2s;
        }

        .form-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-glow);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #f97316, #ea580c);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 16px var(--primary-glow);
            margin-top: 8px;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #ea580c, #c2410c);
            transform: translateY(-1px);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid #ef4444;
            color: #fca5a5;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid #10b981;
            color: #6ee7b7;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .login-footer {
            margin-top: 24px;
            text-align: center;
            font-size: 0.8rem;
            color: #64748b;
        }

        @media (max-width: 480px) {
            body {
                padding: 14px;
            }

            .login-card {
                padding: 28px 20px;
                border-radius: 20px;
            }

            .login-title {
                font-size: 1.3rem;
            }

            .logo-box {
                width: 54px;
                height: 54px;
                font-size: 1.5rem;
                border-radius: 14px;
            }
        }
    </style>
</head>

<body>

    <div class="login-card">
        <div class="login-header">
            <div class="logo-box">
                <i class="fa-solid fa-utensils"></i>
            </div>
            <h1 class="login-title">MaiFood Merchant</h1>
            <p class="login-subtitle">Panel Khusus Kasir & Restoran Komputer</p>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?= session()->getFlashdata('error') ?></span>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <span><?= session()->getFlashdata('success') ?></span>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('merchant/login') ?>" method="post">
            <div class="form-group">
                <label class="form-label">Nomor Handphone Toko</label>
                <div class="input-group">
                    <i class="fa-solid fa-phone"></i>
                    <input type="text" name="phone" value="<?= old('phone') ?>" placeholder="Contoh: 08123456789" required autofocus class="form-input">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" placeholder="Masukkan password toko" required class="form-input">
                </div>
            </div>

            <button type="submit" class="btn-login">
                <i class="fa-solid fa-right-to-bracket" style="margin-right: 6px;"></i> Masuk ke Panel Kasir
            </button>
        </form>

        <div class="login-footer">
            Belum memiliki akun toko? Hubungi Customer Service Maijek untuk pendaftaran kemitraan.
        </div>
    </div>

</body>

</html>