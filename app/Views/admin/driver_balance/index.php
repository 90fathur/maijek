<?= $this->extend('admin/layout/main') ?>

<?= $this->section('content') ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
    <div>
        <h1 class="page-title animate-fade" style="margin-bottom: 4px;">Minimal Saldo & Penarikan Driver</h1>
        <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">Kelola batas minimal saldo pengemudi untuk menarik orderan dan batas penarikan dana (withdraw).</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="<?= base_url('admin/driver-balance/resetNegative') ?>" class="btn-secondary" style="padding: 9px 16px; font-size: 0.875rem; border-radius: 8px; text-decoration: none; display: flex; align-items: center; gap: 6px; background: #fff1f2; color: #e11d48; border: 1px solid #fecdd3;" onclick="return confirm('Netralkan semua saldo pengemudi yang minus menjadi Rp 0?');" title="Netralkan saldo negatif ke Rp 0">
            <i class="fa-solid fa-arrows-rotate"></i> Reset Saldo Minus ke Rp 0
        </a>
        <a href="<?= base_url('admin/withdrawals') ?>" class="btn-secondary" style="padding: 9px 16px; font-size: 0.875rem; border-radius: 8px; text-decoration: none; display: flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-money-bill-transfer"></i> Permintaan Tarik Saldo
        </a>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div style="background: rgba(16, 185, 129, 0.12); border-left: 4px solid #10B981; color: #047857; padding: 14px 18px; border-radius: 8px; margin-bottom: 24px; font-size: 0.875rem; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-circle-check" style="font-size: 1.1rem;"></i>
        <span><?= session()->getFlashdata('success') ?></span>
    </div>
<?php endif; ?>

<style>
    /* Hilangkan panah spinner bawaan browser agar input luas & rapi */
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    input[type=number] {
        -moz-appearance: textfield;
    }
    .stat-card-custom {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .stat-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
    }
    .form-group-custom {
        margin-bottom: 22px;
    }
    .form-group-custom label {
        display: block;
        margin-bottom: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-color);
    }
    .form-control-custom {
        width: 100%;
        padding: 12px 14px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        outline: none;
        background: var(--bg-card);
        font-size: 0.875rem;
        color: var(--text-color);
        transition: border-color 0.2s;
    }
    .form-control-custom:focus {
        border-color: var(--primary);
    }
    .form-hint {
        color: var(--text-muted);
        display: block;
        margin-top: 6px;
        font-size: 0.8rem;
        line-height: 1.4;
    }
</style>

<!-- Grid Statistik Driver -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="stat-card-custom">
        <div class="stat-icon-wrapper" style="background: rgba(59, 130, 246, 0.12); color: #3B82F6;">
            <i class="fa-solid fa-users"></i>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 500;">Total Driver Aktif</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--text-color); margin-top: 2px;"><?= number_format($stats['total_drivers']) ?></div>
        </div>
    </div>

    <div class="stat-card-custom">
        <div class="stat-icon-wrapper" style="background: rgba(16, 185, 129, 0.12); color: #10B981;">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 500;">Saldo Memenuhi Syarat</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #10B981; margin-top: 2px;"><?= number_format($stats['safe_drivers']) ?></div>
        </div>
    </div>

    <div class="stat-card-custom">
        <div class="stat-icon-wrapper" style="background: rgba(245, 158, 11, 0.12); color: #F59E0B;">
            <i class="fa-solid fa-circle-exclamation"></i>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 500;">Saldo Rp 0 / Kosong</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #F59E0B; margin-top: 2px;"><?= number_format($stats['zero_balance_drivers']) ?></div>
        </div>
    </div>

    <div class="stat-card-custom">
        <div class="stat-icon-wrapper" style="background: <?= (($driver_zero_balance_enabled['setting_value'] ?? '0') == '1') ? 'rgba(16, 185, 129, 0.15)' : 'rgba(239, 68, 68, 0.15)' ?>; color: <?= (($driver_zero_balance_enabled['setting_value'] ?? '0') == '1') ? '#10B981' : '#EF4444' ?>;">
            <i class="fa-solid <?= (($driver_zero_balance_enabled['setting_value'] ?? '0') == '1') ? 'fa-unlock-keyhole' : 'fa-lock' ?>"></i>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 500;">Mode Saldo Rp 0</div>
            <div style="font-size: 1.1rem; font-weight: 700; color: <?= (($driver_zero_balance_enabled['setting_value'] ?? '0') == '1') ? '#10B981' : '#EF4444' ?>; margin-top: 4px;">
                <?= (($driver_zero_balance_enabled['setting_value'] ?? '0') == '1') ? 'AKTIF (BEBAS)' : 'NONAKTIF (DIBATASI)' ?>
            </div>
        </div>
    </div>
</div>

<form action="<?= base_url('admin/driver-balance/update') ?>" method="post" class="animate-fade">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: start;">
        
        <!-- CARD 1: Minimal Saldo untuk Menarik Order (Narik Orderan) -->
        <div class="card-table" style="padding: 28px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px; flex-wrap: wrap; gap: 10px;">
                <h2 style="font-size: 1.15rem; font-weight: 600; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-motorcycle" style="color: #3B82F6;"></i> Batas Saldo Menarik Order (Narik)
                </h2>
                <span style="font-size: 0.75rem; background: #EFF6FF; color: #1D4ED8; padding: 4px 10px; border-radius: 9999px; font-weight: 600; border: 1px solid #BFDBFE;">
                    Tarik Order
                </span>
            </div>

            <!-- Fitur Saldo Rp 0 Toggle -->
            <div class="form-group-custom">
                <label>Status Fitur Saldo Driver Rp 0 (Bebas Narik)</label>
                <select name="driver_zero_balance_enabled" class="form-control-custom">
                    <option value="1" <?= (($driver_zero_balance_enabled['setting_value'] ?? '0') == '1') ? 'selected' : '' ?>>
                        ✅ AKTIF - Driver Saldo Rp 0 Tetap Bisa Menarik Order (Bebas Saldo)
                    </option>
                    <option value="0" <?= (($driver_zero_balance_enabled['setting_value'] ?? '0') == '0') ? 'selected' : '' ?>>
                        ⛔ NONAKTIF - Wajib Memiliki Saldo Minimal (Kembali ke Awal)
                    </option>
                </select>
                <small class="form-hint">
                    Bila <b>AKTIF</b>, pengemudi dengan saldo Mai-Pay Rp 0 bebas mengambil order kapanpun dan saldo <b>tidak akan terpotong sampai minus</b> saat menyelesaikan pesanan tunai. Bila <b>NONAKTIF</b>, driver wajib memiliki saldo minimal sesuai nominal di bawah untuk menerima pesanan tunai reguler.
                </small>
            </div>

            <!-- Batas Minimal Saldo untuk Narik -->
            <div class="form-group-custom">
                <label>Batas Minimal Saldo Driver untuk Terima Order (Rp)</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 12px; font-size: 0.875rem; color: var(--text-muted); font-weight: 600;">Rp</span>
                    <input type="number" name="driver_min_balance" value="<?= esc($driver_min_balance['setting_value'] ?? 10000) ?>" class="form-control-custom" style="padding-left: 42px;" placeholder="10000" required>
                </div>
                <small class="form-hint">
                    Batas saldo minimal yang wajib dimiliki driver saat Fitur Saldo Rp 0 dinonaktifkan (Standar: <b>Rp 10.000</b>). Driver dengan saldo di bawah ini tidak dapat menerima pesanan tunai non-promo.
                </small>
            </div>

            <!-- Pengecualian Promo Cashback -->
            <div class="form-group-custom" style="margin-bottom: 0;">
                <label>Pengecualian Promo Cashback (Bebas Saldo Rp 0 Saat Promo)</label>
                <select name="driver_promo_zero_balance_bypass" class="form-control-custom">
                    <option value="1" <?= (($driver_promo_zero_balance_bypass['setting_value'] ?? '1') == '1') ? 'selected' : '' ?>>
                        ⭐ AKTIF (Direkomendasikan) - Saldo Rp 0 Tetap Boleh Tarik jika Order Ada Cashback Promo
                    </option>
                    <option value="0" <?= (($driver_promo_zero_balance_bypass['setting_value'] ?? '1') == '0') ? 'selected' : '' ?>>
                        ❌ NONAKTIF - Wajib Saldo Minimal Tanpa Pengecualian Promo
                    </option>
                </select>
                <small class="form-hint">
                    Jika aktif, saat ada pesanan dengan subsidi promo (contoh: subsidi komisi promo menghasilkan cashback <b>+Rp 120</b> ke Maypay driver), driver dengan saldo Rp 0 <b>tetap bisa menarik order</b> karena saldo bertambah, bukan berkurang.
                </small>
            </div>
        </div>

        <!-- CARD 2: Minimal Penarikan Saldo Driver (Pencairan Dana / Withdraw) -->
        <div class="card-table" style="padding: 28px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px; flex-wrap: wrap; gap: 10px;">
                <h2 style="font-size: 1.15rem; font-weight: 600; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-money-bill-transfer" style="color: #10B981;"></i> Batas Penarikan Dana Driver (Withdraw)
                </h2>
                <span style="font-size: 0.75rem; background: #ECFDF5; color: #059669; padding: 4px 10px; border-radius: 9999px; font-weight: 600; border: 1px solid #A7F3D0;">
                    Pencairan Rekening
                </span>
            </div>

            <!-- Minimal Tarik Dana -->
            <div class="form-group-custom">
                <label>Minimal Nominal Penarikan Saldo Driver (Rp)</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 12px; font-size: 0.875rem; color: var(--text-muted); font-weight: 600;">Rp</span>
                    <input type="number" name="driver_min_withdraw" value="<?= esc($driver_min_withdraw['setting_value'] ?? 50000) ?>" class="form-control-custom" style="padding-left: 42px;" placeholder="50000" required>
                </div>
                <small class="form-hint">
                    Batas minimal saldo yang dapat dicairkan driver ke rekening bank / e-wallet dalam satu kali permintaan penarikan (Standar: <b>Rp 50.000</b>).
                </small>
            </div>

            <!-- Panduan & Rangkuman Aturan -->
            <div style="background: rgba(243, 244, 246, 0.6); border: 1px solid var(--border-color); border-radius: 10px; padding: 16px; margin-top: 20px;">
                <div style="font-size: 0.85rem; font-weight: 600; color: var(--text-color); margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-circle-info" style="color: #3B82F6;"></i> Ringkasan Aturan Bagi Driver
                </div>
                <ul style="margin: 0; padding-left: 20px; font-size: 0.8rem; color: var(--text-muted); line-height: 1.6;">
                    <li>Pesanan non-tunai (Mai-Pay) <b>tidak memotong saldo</b> melainkan langsung menambah penghasilan driver.</li>
                    <li>Pesanan tunai reguler memotong komisi aplikasi dari saldo Maypay driver.</li>
                    <li>Pesanan promo ber-subsidi mengkreditkan selisih komisi sebagai <b>cashback otomatis</b> ke saldo Maypay driver saat selesai.</li>
                </ul>
            </div>
        </div>

    </div>

    <!-- Tombol Simpan -->
    <div style="margin-top: 24px; text-align: right;">
        <button type="submit" class="btn-primary" style="padding: 12px 28px; font-size: 0.95rem; font-weight: 600; border-radius: 8px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
            <i class="fa-solid fa-floppy-disk"></i> Simpan Pengaturan Saldo Driver
        </button>
    </div>
</form>

<?= $this->endSection() ?>
