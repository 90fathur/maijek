<?= $this->extend('admin/layout/main') ?>

<?= $this->section('content') ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h1 class="page-title animate-fade" style="margin-bottom: 0;">Profil Saya & Keamanan</h1>
</div>

<?php if(session()->getFlashdata('success')): ?>
    <div style="background: rgba(16, 185, 129, 0.1); color: var(--primary); padding: 12px; border-radius: 8px; margin-bottom: 20px;">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
    <div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
    <div class="card animate-fade" style="text-align: center;">
        <img src="https://ui-avatars.com/api/?name=<?= urlencode(session()->get('admin_username')) ?>&background=10b981&color=fff&size=128" alt="Profile" style="border-radius: 50%; border: 4px solid var(--border-color); margin-bottom: 16px;">
        <h2><?= esc(session()->get('admin_username')) ?></h2>
        <p style="color: var(--text-muted); font-size: 0.875rem; text-transform: uppercase; margin-bottom: 20px;">
            <i class="fa-solid fa-shield-halved"></i> Role: <?= esc(session()->get('admin_role') ?? 'Superadmin') ?>
        </p>
    </div>

    <div class="card animate-fade">
        <h3 style="margin-top: 0; margin-bottom: 20px;">Ubah Kata Sandi (Password)</h3>
        <form action="<?= base_url('admin/profile/update') ?>" method="post">
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Kata Sandi Lama</label>
                <input type="password" name="old_password" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px;">
            </div>
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Kata Sandi Baru</label>
                <input type="password" name="new_password" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px;">
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Konfirmasi Kata Sandi Baru</label>
                <input type="password" name="confirm_password" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px;">
            </div>

            <button type="submit" style="padding: 12px 24px; background: var(--primary); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                Simpan Perubahan
            </button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
