<?= $this->extend('admin/layout/main') ?>

<?= $this->section('content') ?>

<!-- Header Title & Export Button -->
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <div>
        <h1 class="page-title animate-fade" style="margin: 0;">Laporan Keuangan & Akuntansi</h1>
        <span style="font-size: 0.82rem; color: var(--text-muted);">
            Periode: <strong style="color: var(--primary);"><?= esc($period_label) ?></strong>
        </span>
    </div>

    <div>
        <a href="<?= base_url('admin/finance/exportCsv?' . http_build_query(['filter' => $filter, 'start_date' => $start_date, 'end_date' => $end_date])) ?>" 
           class="btn" 
           style="background: #10b981; color: white; padding: 10px 18px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2); transition: background 0.15s ease;"
           onmouseover="this.style.background='#059669'"
           onmouseout="this.style.background='#10b981'"
           title="Unduh seluruh data transaksi pada periode ini ke file Excel / CSV">
            <i class="fa-solid fa-file-excel" style="font-size: 1.1rem;"></i> Unduh Laporan Excel / CSV
        </a>
    </div>
</div>

<!-- Card Filter Rentang Tanggal -->
<div class="card-table animate-fade" style="margin-bottom: 24px; padding: 16px 20px; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
    <div style="display: flex; flex-direction: column; gap: 14px;">
        
        <!-- Baris 1: Tombol Cepat (Quick Filters) -->
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span style="font-size: 0.82rem; font-weight: 600; color: #475569; margin-right: 4px;">
                <i class="fa-solid fa-clock-rotate-left"></i> Filter Cepat:
            </span>

            <?php
            $quickFilters = [
                'all'        => 'Semua Waktu',
                'today'      => 'Hari Ini',
                'yesterday'  => 'Kemarin',
                '7days'      => '7 Hari Terakhir',
                'this_month' => 'Bulan Ini',
                'last_month' => 'Bulan Lalu',
            ];
            foreach ($quickFilters as $key => $label):
                $isActive = ($filter === $key);
            ?>
                <a href="<?= base_url('admin/finance?filter=' . $key) ?>" 
                   style="padding: 5px 12px; border-radius: 20px; font-size: 0.78rem; font-weight: 600; text-decoration: none; transition: all 0.15s ease; <?= $isActive ? 'background: var(--primary); color: #ffffff; box-shadow: 0 2px 4px rgba(0, 166, 90, 0.25);' : 'background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;' ?>">
                    <?= $label ?>
                </a>
            <?php endforeach; ?>
        </div>

        <hr style="border: none; border-top: 1px solid #f1f5f9; margin: 0;">

        <!-- Baris 2: Input Tanggal Kustom -->
        <form method="GET" action="<?= base_url('admin/finance') ?>" style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <input type="hidden" name="filter" value="custom">

            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="font-size: 0.82rem; font-weight: 500; color: #64748b;">Dari:</span>
                <input type="date" name="start_date" value="<?= esc($start_date) ?>" required style="padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.82rem; color: #1e293b; outline: none;">
            </div>

            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="font-size: 0.82rem; font-weight: 500; color: #64748b;">Sampai:</span>
                <input type="date" name="end_date" value="<?= esc($end_date) ?>" required style="padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.82rem; color: #1e293b; outline: none;">
            </div>

            <button type="submit" class="btn btn-primary" style="padding: 6px 14px; font-size: 0.82rem; font-weight: 600; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-filter"></i> Terapkan Tanggal
            </button>

            <?php if ($filter !== 'all'): ?>
                <a href="<?= base_url('admin/finance') ?>" style="font-size: 0.82rem; color: #64748b; text-decoration: none; padding: 6px 10px; border: 1px solid #e2e8f0; border-radius: 6px; background: #f8fafc; display: inline-flex; align-items: center; gap: 4px;" title="Kembalikan ke semua waktu">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </a>
            <?php endif; ?>
        </form>

    </div>
</div>

<!-- Kartu Ringkasan Keuangan Sesuai Filter -->
<div class="stats-grid animate-fade">
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;"><i class="fa-solid fa-check-circle"></i></div>
        <div class="stat-info">
            <h3 style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 5px;">Pesanan Sukses</h3>
            <p style="font-size: 1.25rem; font-weight: bold;"><?= number_format($total_success, 0, ',', '.') ?></p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;"><i class="fa-solid fa-receipt"></i></div>
        <div class="stat-info">
            <h3 style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 5px;">Total Omset Resmi (GMV)</h3>
            <p style="font-size: 1.25rem; font-weight: bold; color: #1e293b;">Rp <?= number_format($total_gross, 0, ',', '.') ?></p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(14, 165, 233, 0.1); color: #0284c7;"><i class="fa-solid fa-coins"></i></div>
        <div class="stat-info">
            <h3 style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 5px;">Komisi Kotor Platform</h3>
            <p style="font-size: 1.25rem; font-weight: bold; color: #0284c7;">Rp <?= number_format($total_commission, 0, ',', '.') ?></p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(244, 63, 94, 0.1); color: #e11d48;"><i class="fa-solid fa-tag"></i></div>
        <div class="stat-info">
            <h3 style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 5px;">Subsidi Diskon Promo</h3>
            <p style="font-size: 1.25rem; font-weight: bold; color: #e11d48;">- Rp <?= number_format($total_promo_subsidy, 0, ',', '.') ?></p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #059669;"><i class="fa-solid fa-wallet"></i></div>
        <div class="stat-info">
            <h3 style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 5px;">Laba Bersih Platform (Net)</h3>
            <p style="font-size: 1.25rem; font-weight: bold; color: <?= $total_net_profit >= 0 ? '#059669' : '#dc2626' ?>;">
                Rp <?= number_format($total_net_profit, 0, ',', '.') ?>
            </p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;"><i class="fa-solid fa-ban"></i></div>
        <div class="stat-info">
            <h3 style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 5px;">Potensi Hilang (Batal)</h3>
            <p style="font-size: 1.15rem; font-weight: bold; color: #ef4444;"><?= number_format($total_cancelled, 0, ',', '.') ?> Pesanan</p>
            <span style="font-size: 0.72rem; color: #94a3b8;">Komisi hilang: Rp <?= number_format($total_lost_profit, 0, ',', '.') ?></span>
        </div>
    </div>
</div>

<!-- Tabel Rincian Transaksi -->
<div class="card-table animate-fade" style="margin-top: 2rem;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
        <div>
            <h2 style="margin: 0; font-size: 1.05rem;">Rincian Transaksi & Akuntansi Promo</h2>
            <span style="font-size: 0.75rem; color: var(--text-muted);">
                Total: <?= number_format($total_trans, 0, ',', '.') ?> transaksi ditemukan
            </span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>ID / Layanan</th>
                    <th>Status</th>
                    <th style="text-align: right;">Tarif Resmi (GMV)</th>
                    <th style="text-align: right;">Subsidi Promo</th>
                    <th style="text-align: right;">Komisi Platform</th>
                    <th style="text-align: right;">Laba Bersih Platform</th>
                    <th style="text-align: center;">Detail</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($transactions)): ?>
                    <?php foreach ($transactions as $t): ?>
                        <tr>
                            <td style="white-space: nowrap; font-size: 0.82rem; color: #64748b;">
                                <i class="fa-regular fa-clock" style="font-size: 0.75rem;"></i> <?= date('d M Y, H:i', strtotime($t['created_at'])) ?>
                            </td>
                            <td>
                                <strong style="display:block; color: #1e293b;">#<?= esc($t['id']) ?></strong>
                                <span class="badge" style="background: <?= $t['type'] === 'Transport/Kurir' ? '#eff6ff' : '#fff7ed' ?>; color: <?= $t['type'] === 'Transport/Kurir' ? '#2563eb' : '#ea580c' ?>; font-size: 0.7rem; padding: 1px 6px; border-radius: 4px;">
                                    <?= esc($t['type']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($t['status'] == 'completed'): ?>
                                    <span class="badge badge-success" style="font-size: 0.72rem; padding: 2px 7px;">Sukses</span>
                                <?php elseif ($t['status'] == 'cancelled'): ?>
                                    <span class="badge badge-danger" style="font-size: 0.72rem; padding: 2px 7px;">Batal</span>
                                <?php else: ?>
                                    <span class="badge badge-warning" style="font-size: 0.72rem; padding: 2px 7px;"><?= esc($t['status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right; font-weight: 600; color: #1e293b;">
                                Rp <?= number_format($t['gross'], 0, ',', '.') ?>
                            </td>
                            <td style="text-align: right; color: <?= $t['discount'] > 0 ? '#e11d48' : 'var(--text-muted)' ?>; font-weight: <?= $t['discount'] > 0 ? '600' : 'normal' ?>;">
                                <?= $t['discount'] > 0 ? '- Rp ' . number_format($t['discount'], 0, ',', '.') : '-' ?>
                            </td>
                            <td style="text-align: right; color: #0284c7; font-weight: 600;">
                                Rp <?= number_format($t['commission'], 0, ',', '.') ?>
                            </td>
                            <td style="text-align: right; font-weight: 700; color: <?= $t['status'] == 'completed' ? ($t['net'] >= 0 ? '#059669' : '#dc2626') : 'var(--text-muted)' ?>;">
                                Rp <?= number_format($t['net'], 0, ',', '.') ?>
                            </td>
                            <td style="text-align: center;">
                                <?php 
                                    $rawId = (int)preg_replace('/[^0-9]/', '', $t['id']);
                                    $orderType = ($t['type'] === 'Transport/Kurir') ? 'ride' : 'food';
                                ?>
                                <button type="button" 
                                        onclick="openOrderDetail('<?= $orderType ?>', <?= $rawId ?>)"
                                        style="padding: 4px 8px; background: #0284c7; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.72rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 1px 2px rgba(2, 132, 199, 0.2);" 
                                        title="Lihat Rincian Pesanan">
                                    <i class="fa-solid fa-eye"></i> Detail
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 3rem;">
                            <i class="fa-regular fa-folder-open" style="font-size: 2.2rem; color: #cbd5e1; display: block; margin-bottom: 8px;"></i>
                            Tidak ada transaksi pada periode <strong><?= esc($period_label) ?></strong>.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 15px; display: flex; justify-content: center;" class="pagination-wrapper">
        <?= $pager ?>
    </div>
</div>

<style>
    .pagination-wrapper nav ul {
        display: flex;
        padding-left: 0;
        list-style: none;
        border-radius: 0.5rem;
        gap: 5px;
        margin: 0;
    }

    .pagination-wrapper nav ul li {
        display: inline-block;
    }

    .pagination-wrapper nav ul li>a,
    .pagination-wrapper nav ul li>span {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        padding: 0 12px;
        color: var(--primary, #10b981);
        background-color: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 500;
        box-sizing: border-box;
    }

    .pagination-wrapper nav ul li>a>span {
        border: none;
        padding: 0;
        margin: 0;
        background: transparent;
        color: inherit;
        height: auto;
        min-width: auto;
    }

    .pagination-wrapper nav ul li.active>a,
    .pagination-wrapper nav ul li.active>span {
        z-index: 3;
        color: #fff;
        background-color: var(--primary, #10b981);
        border-color: var(--primary, #10b981);
    }

    .pagination-wrapper nav ul li>a:hover {
        background-color: #f8fafc;
        border-color: #cbd5e1;
    }

    .pagination-wrapper nav ul li.disabled>a,
    .pagination-wrapper nav ul li.disabled>span {
        color: #94a3b8;
        pointer-events: none;
        background-color: #f1f5f9;
        border-color: #e2e8f0;
    }
</style>

<?= $this->include('admin/orders/modal_detail') ?>
<?= $this->endSection() ?>