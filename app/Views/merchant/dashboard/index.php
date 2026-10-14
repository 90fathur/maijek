<?= $this->extend('merchant/layout/main') ?>

<?= $this->section('content') ?>

<?php
$isMart = !empty($isMart) ? $isMart : ((session()->get('merchant_type') === 'mart') || (($merchant['merchant_type'] ?? '') === 'mart'));
?>

<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 28px;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        border: 1px solid var(--border-color);
        padding: 22px;
        display: flex;
        align-items: center;
        gap: 16px;
        transition: all 0.2s;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
    }

    .stat-icon {
        width: 54px;
        height: 54px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .stat-icon.orange {
        background: #ffedd5;
        color: #ea580c;
    }

    .stat-icon.green {
        background: #dcfce7;
        color: #16a34a;
    }

    .stat-icon.blue {
        background: #dbeafe;
        color: #2563eb;
    }

    .stat-icon.purple {
        background: #f3e8ff;
        color: #9333ea;
    }

    .stat-label {
        font-size: 0.8rem;
        color: var(--text-muted);
        font-weight: 600;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--text-main);
        letter-spacing: -0.5px;
    }

    .hero-banner {
        background: linear-gradient(135deg, #ea580c, #c2410c);
        color: white;
        border-radius: 20px;
        padding: 28px 32px;
        margin-bottom: 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(234, 88, 12, 0.25);
    }

    .hero-banner::after {
        content: '\f2e7';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        right: -20px;
        bottom: -30px;
        font-size: 160px;
        opacity: 0.08;
        pointer-events: none;
    }

    .hero-title {
        font-size: 1.5rem;
        font-weight: 800;
        margin-bottom: 6px;
    }

    .hero-desc {
        font-size: 0.95rem;
        opacity: 0.9;
        max-width: 550px;
    }

    .table-card {
        background: white;
        border-radius: 16px;
        border: 1px solid var(--border-color);
        padding: 24px;
    }

    .table-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .table-title {
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--text-main);
    }

    .order-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .order-table th {
        padding: 12px 16px;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-muted);
        border-bottom: 1px solid var(--border-color);
        font-weight: 700;
    }

    .order-table td {
        padding: 16px;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.875rem;
        vertical-align: middle;
    }

    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .badge-status.pending {
        background: #fef3c7;
        color: #b45309;
    }

    .badge-status.processing {
        background: #ffedd5;
        color: #c2410c;
    }

    .badge-status.ready {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .badge-status.completed {
        background: #dcfce7;
        color: #15803d;
    }

    .badge-status.cancelled {
        background: #fee2e2;
        color: #b91c1c;
    }

    /* RESPONSIVE MEDIA QUERIES */
    @media (max-width: 768px) {
        .hero-banner {
            flex-direction: column;
            align-items: flex-start;
            padding: 22px 18px;
            gap: 16px;
        }

        .hero-banner a.btn-primary {
            width: 100%;
            justify-content: center;
            padding: 12px 18px !important;
            font-size: 0.95rem !important;
        }

        .hero-title {
            font-size: 1.25rem;
        }

        .hero-desc {
            font-size: 0.85rem;
        }

        .table-card {
            padding: 16px;
            border-radius: 14px;
        }
    }

    @media (max-width: 640px) {
        .stats-grid {
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }

        .stat-card {
            padding: 14px 12px;
            gap: 10px;
            border-radius: 14px;
        }

        .stat-icon {
            width: 42px;
            height: 42px;
            font-size: 1.2rem;
            border-radius: 10px;
        }

        .stat-label {
            font-size: 0.68rem;
        }

        .stat-value {
            font-size: 1.15rem;
        }
    }

    @media (max-width: 380px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Hero Banner -->
<div class="hero-banner">
    <div>
        <h2 class="hero-title">Halo, <?= esc($merchant['name']) ?>! 👋</h2>
        <p class="hero-desc">Layar kasir <?= $isMart ? 'MaiMart' : 'MaiFood' ?> siap memantau pesanan masuk secara langsung. Pastikan komputer Anda tetap menyala dan terhubung dengan speaker kasir.</p>
    </div>
    <a href="<?= base_url('merchant/orders/live') ?>" class="btn-primary" style="background: white; color: var(--primary); padding: 14px 24px; font-size: 1rem; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
        <i class="fa-solid fa-cash-register"></i> Buka Layar Kasir Live
    </a>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fa-solid fa-coins"></i>
        </div>
        <div>
            <div class="stat-label">Pendapatan Hari Ini</div>
            <div class="stat-value">Rp <?= number_format($incomeToday, 0, ',', '.') ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon orange">
            <i class="fa-solid fa-receipt"></i>
        </div>
        <div>
            <div class="stat-label">Pesanan Hari Ini</div>
            <div class="stat-value"><?= $ordersToday ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fa-solid fa-fire"></i>
        </div>
        <div>
            <div class="stat-label">Pesanan Aktif</div>
            <div class="stat-value"><?= $activeOrders ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon purple">
            <i class="fa-solid <?= $isMart ? 'fa-boxes-stacked' : 'fa-bowl-food' ?>"></i>
        </div>
        <div>
            <div class="stat-label"><?= $isMart ? 'Total Produk' : 'Total Menu' ?></div>
            <div class="stat-value"><?= $menuCount ?></div>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="table-card">
    <div class="table-header">
        <div class="table-title">Pesanan Terakhir</div>
        <a href="<?= base_url('merchant/orders/live') ?>" style="color: var(--primary); font-weight: 700; text-decoration: none; font-size: 0.875rem;">
            Lihat Layar Live <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>

    <div style="overflow-x: auto;">
        <table class="order-table">
            <thead>
                <tr>
                    <th>No. Order</th>
                    <th>Pemesan</th>
                    <th>Menu Makanan</th>
                    <th>Belanja Makanan (Resto)</th>
                    <th>Metode</th>
                    <th>Status</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recentOrders)): ?>
                    <?php foreach ($recentOrders as $ro): ?>
                        <?php $foodBelanja = !empty($ro['food_total']) ? $ro['food_total'] : ($ro['total_price'] - ($ro['delivery_fee'] ?? 0)); ?>
                        <tr>
                            <td style="font-weight: 700; color: var(--primary);">#<?= $ro['id'] ?></td>
                            <td>
                                <div style="font-weight: 600;"><?= esc($ro['user_name'] ?? 'Pelanggan') ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);"><?= esc($ro['user_phone'] ?? '-') ?></div>
                            </td>
                            <td>
                                <?php if (!empty($ro['items'])): ?>
                                    <?php foreach ($ro['items'] as $item): ?>
                                        <div style="font-size: 0.8rem;"><?= $item['quantity'] ?>x <?= esc($item['menu_name'] ?? 'Menu') ?></div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-weight: 800; color: #047857;">Rp <?= number_format($foodBelanja, 0, ',', '.') ?></div>
                                <div style="font-size: 0.7rem; color: #64748b;">(Pelanggan: Rp <?= number_format($ro['total_price'], 0, ',', '.') ?>)</div>
                            </td>
                            <td>
                                <span style="text-transform: uppercase; font-size: 0.75rem; font-weight: 700;">
                                    <?= ($ro['payment_method'] == 'cash') ? '💵 Tunai' : '💳 Mai-Pay' ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $sClass = 'pending';
                                $sText = 'Menunggu';
                                if ($ro['status'] == 'processing') {
                                    $sClass = 'processing';
                                    $sText = $isMart ? 'Disiapkan' : 'Dimasak';
                                } else if ($ro['status'] == 'ready') {
                                    $sClass = 'ready';
                                    $sText = 'Mencari Kurir';
                                } else if (in_array($ro['status'], ['driver_assigned', 'accepted'])) {
                                    $sClass = 'ready';
                                    $sText = $isMart ? 'Kurir Menuju Toko' : 'Kurir Menuju Resto';
                                } else if ($ro['status'] == 'driver_at_merchant') {
                                    $sClass = 'ready';
                                    $sText = $isMart ? 'Kurir di Toko' : 'Kurir di Resto';
                                } else if (in_array($ro['status'], ['delivering', 'in_progress'])) {
                                    $sClass = 'ready';
                                    $sText = 'Sedang Diantar';
                                } else if (in_array($ro['status'], ['completed', 'delivered'])) {
                                    $sClass = 'completed';
                                    $sText = 'Selesai';
                                } else if ($ro['status'] == 'cancelled') {
                                    $sClass = 'cancelled';
                                    $sText = 'Dibatalkan';
                                }
                                ?>
                                <span class="badge-status <?= $sClass ?>"><?= $sText ?></span>
                            </td>
                            <td style="color: var(--text-muted); font-size: 0.8rem;">
                                <?= date('H:i, d M', strtotime($ro['created_at'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 30px;">Belum ada pesanan masuk.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>