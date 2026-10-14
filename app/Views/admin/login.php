<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Maijek Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--bg-body) 0%, #d1fae5 100%);
        }
        .login-card {
            background: var(--bg-card);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .login-card i.logo {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 16px;
        }
        .login-card h2 {
            margin-bottom: 8px;
            color: var(--text-main);
        }
        .login-card p {
            color: var(--text-muted);
            margin-bottom: 32px;
            font-size: 0.875rem;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-main);
        }
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
            outline: none;
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }
        .btn-primary {
            width: 100%;
            padding: 12px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 12px;
        }
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        .alert {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.875rem;
        }
    </style>
</head>
<body class="animate-fade">
    <div class="login-card">
        <i class="fa-solid fa-motorcycle logo"></i>
        <h2>Maijek Admin</h2>
        <p>Silakan masuk ke panel kendali Anda</p>

        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <form action="<?= base_url('admin/login') ?>" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required placeholder="Masukkan username">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required placeholder="Masukkan password">
            </div>
            <div class="form-group" style="background: rgba(16, 185, 129, 0.05); padding: 16px; border-radius: 8px; border: 1px dashed var(--primary);">
                <label style="color: var(--primary-dark);"><i class="fa-solid fa-shield-halved"></i> Keamanan (Captcha)</label>
                <p style="margin-bottom: 12px; color: var(--text-main); font-weight: 500; font-size: 1rem;">
                    <?= $captcha_question ?? 'Berapa hasil dari 5 + 5?' ?>
                </p>
                <input type="number" name="captcha" class="form-control" required placeholder="Jawaban angka...">
            </div>
            <button type="submit" class="btn-primary">Masuk</button>
        </form>
    </div>
</body>
</html>
