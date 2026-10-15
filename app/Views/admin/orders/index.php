<?= $this->extend('admin/layout/main') ?>

<?= $this->section('content') ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
    <div>
        <h1 class="page-title animate-fade" style="margin-bottom: 4px;">Riwayat Pesanan</h1>
        <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">Pantau dan kelola seluruh transaksi pesanan transportasi dan pesan-antar makanan/toko.</p>
    </div>
    <?php
    $exportParams = [];
    if (!empty($startDate)) $exportParams['start_date'] = $startDate;
    if (!empty($endDate)) $exportParams['end_date'] = $endDate;
    $exportUrl = base_url('admin/orders/exportCsv/' . (($tab ?? 'ride') === 'food' ? 'maifood' : 'mairide')) . (!empty($exportParams) ? '?' . http_build_query($exportParams) : '');

    $urlParamsRide = ['tab' => 'ride'];
    if (!empty($startDate)) $urlParamsRide['start_date'] = $startDate;
    if (!empty($endDate)) $urlParamsRide['end_date'] = $endDate;

    $urlParamsFood = ['tab' => 'food'];
    if (!empty($startDate)) $urlParamsFood['start_date'] = $startDate;
    if (!empty($endDate)) $urlParamsFood['end_date'] = $endDate;
    ?>
    <a href="<?= $exportUrl ?>" style="padding: 10px 18px; background: #10b981; color: white; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);">
        <i class="fa-solid fa-file-csv"></i> Export Laporan (<?= ($tab ?? 'ride') === 'food' ? 'MaiFood & Mart' : 'MaiRide & Kirim' ?>)
    </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div style="background: #dcfce7; border: 1px solid #86efac; color: #166534; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-circle-check" style="font-size: 1.1rem;"></i>
        <span><?= session()->getFlashdata('success') ?></span>
    </div>
<?php endif; ?>

<!-- Navigasi Tab -->
<div style="display: flex; gap: 12px; margin-bottom: 24px; border-bottom: 2px solid #e2e8f0; background: #ffffff; padding: 6px 16px 0; border-radius: 12px 12px 0 0; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
    <a href="<?= base_url('admin/orders?' . http_build_query($urlParamsRide)) ?>" style="padding: 14px 22px; font-weight: 700; font-size: 0.95rem; text-decoration: none; border-bottom: 3px solid <?= ($tab ?? 'ride') === 'ride' ? 'var(--primary, #10b981)' : 'transparent' ?>; color: <?= ($tab ?? 'ride') === 'ride' ? 'var(--primary, #10b981)' : '#64748b' ?>; display: inline-flex; align-items: center; gap: 10px; margin-bottom: -2px; transition: all 0.2s;">
        <i class="fa-solid fa-motorcycle" style="font-size: 1.1rem;"></i> 
        <span>MaiRide & Pengantaran</span>
        <span style="background: <?= ($tab ?? 'ride') === 'ride' ? '#d1fae5' : '#f1f5f9' ?>; color: <?= ($tab ?? 'ride') === 'ride' ? '#065f46' : '#64748b' ?>; font-size: 0.75rem; padding: 2px 8px; border-radius: 999px; font-weight: 800;">
            <?= number_format($totalRide ?? 0) ?>
        </span>
    </a>
    
    <a href="<?= base_url('admin/orders?' . http_build_query($urlParamsFood)) ?>" style="padding: 14px 22px; font-weight: 700; font-size: 0.95rem; text-decoration: none; border-bottom: 3px solid <?= ($tab ?? 'ride') === 'food' ? '#ea580c' : 'transparent' ?>; color: <?= ($tab ?? 'ride') === 'food' ? '#ea580c' : '#64748b' ?>; display: inline-flex; align-items: center; gap: 10px; margin-bottom: -2px; transition: all 0.2s;">
        <i class="fa-solid fa-utensils" style="font-size: 1.1rem;"></i> 
        <span>MaiFood & MaiMart</span>
        <span style="background: <?= ($tab ?? 'ride') === 'food' ? '#ffedd5' : '#f1f5f9' ?>; color: <?= ($tab ?? 'ride') === 'food' ? '#9a3412' : '#64748b' ?>; font-size: 0.75rem; padding: 2px 8px; border-radius: 999px; font-weight: 800;">
            <?= number_format($totalFood ?? 0) ?>
        </span>
    </a>
</div>

<div class="card-table animate-fade">
    <!-- Toolbar Pencarian & Filter -->
    <div class="table-toolbar" style="flex-direction: column; align-items: stretch; gap: 12px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; width: 100%;">
            <form method="get" action="<?= base_url('admin/orders') ?>" class="toolbar-form" id="orderFilterForm" style="display: flex; align-items: center; flex-wrap: wrap; gap: 8px;">
                <input type="hidden" name="tab" value="<?= esc($tab ?? 'ride') ?>">
                
                <div class="search-box" style="min-width: 220px;">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" name="q" value="<?= esc($q ?? '') ?>" placeholder="<?= ($tab ?? 'ride') === 'food' ? 'Cari ID, resto, pelanggan, kurir...' : 'Cari ID, pelanggan, kurir, alamat...' ?>" class="search-input">
                    <?php if (!empty($q)): ?>
                        <a href="<?= base_url('admin/orders?tab=' . esc($tab ?? 'ride') . (!empty($startDate) ? '&start_date=' . $startDate : '') . (!empty($endDate) ? '&end_date=' . $endDate : '')) ?>" class="clear-search" title="Hapus pencarian"><i class="fa-solid fa-xmark"></i></a>
                    <?php endif; ?>
                </div>

                <!-- Input Rentang Tanggal -->
                <div style="display: flex; align-items: center; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 5px 10px; gap: 6px;">
                    <i class="fa-regular fa-calendar-days" style="color: #64748b; font-size: 0.85rem;"></i>
                    <input type="date" name="start_date" id="orderStartDate" value="<?= esc($startDate ?? '') ?>" style="border: none; outline: none; background: transparent; font-size: 0.8rem; color: #1e293b;" title="Dari Tanggal">
                    <span style="color: #94a3b8; font-size: 0.75rem; font-weight: 700;">s/d</span>
                    <input type="date" name="end_date" id="orderEndDate" value="<?= esc($endDate ?? '') ?>" style="border: none; outline: none; background: transparent; font-size: 0.8rem; color: #1e293b;" title="Sampai Tanggal">
                </div>

                <div class="filter-box">
                    <select name="status" class="filter-select" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <?php if (($tab ?? 'ride') === 'food'): ?>
                            <option value="completed" <?= ($status ?? '') === 'completed' ? 'selected' : '' ?>>Selesai</option>
                            <option value="delivering" <?= ($status ?? '') === 'delivering' ? 'selected' : '' ?>>Sedang Diantar</option>
                            <option value="driver_assigned" <?= ($status ?? '') === 'driver_assigned' ? 'selected' : '' ?>>Kurir Menuju Resto</option>
                            <option value="driver_at_merchant" <?= ($status ?? '') === 'driver_at_merchant' ? 'selected' : '' ?>>Kurir di Resto</option>
                            <option value="ready" <?= ($status ?? '') === 'ready' ? 'selected' : '' ?>>Pesanan Siap</option>
                            <option value="processing" <?= ($status ?? '') === 'processing' ? 'selected' : '' ?>>Dimasak Resto</option>
                            <option value="accepted_by_merchant" <?= ($status ?? '') === 'accepted_by_merchant' ? 'selected' : '' ?>>Diterima Resto</option>
                            <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : '' ?>>Menunggu Resto</option>
                            <option value="cancelled" <?= ($status ?? '') === 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
                        <?php else: ?>
                            <option value="completed" <?= ($status ?? '') === 'completed' ? 'selected' : '' ?>>Selesai</option>
                            <option value="in_progress" <?= ($status ?? '') === 'in_progress' ? 'selected' : '' ?>>Dalam Perjalanan</option>
                            <option value="arrived" <?= ($status ?? '') === 'arrived' ? 'selected' : '' ?>>Driver Sampai</option>
                            <option value="accepted" <?= ($status ?? '') === 'accepted' ? 'selected' : '' ?>>Diterima Driver</option>
                            <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : '' ?>>Mencari Driver</option>
                            <option value="cancelled" <?= ($status ?? '') === 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
                        <?php endif; ?>
                    </select>
                </div>

                <button type="submit" class="btn-search" style="padding: 7px 14px;">
                    <i class="fa-solid fa-filter"></i> Terapkan
                </button>

                <?php if (!empty($q) || !empty($status) || !empty($startDate) || !empty($endDate)): ?>
                    <a href="<?= base_url('admin/orders?tab=' . esc($tab ?? 'ride')) ?>" class="btn-reset" title="Reset filter" style="padding: 7px 12px;">
                        <i class="fa-solid fa-rotate-left"></i> Reset
                    </a>
                <?php endif; ?>
            </form>

            <div class="toolbar-stats" style="font-size: 0.82rem; color: #64748b;">
                Ditemukan: <b style="color: var(--primary); font-size: 0.95rem;"><?= number_format($totalCount ?? 0) ?></b> pesanan
            </div>
        </div>

        <!-- Tombol Preset Cepat Filter Tanggal -->
        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap; background: #f8fafc; padding: 6px 10px; border-radius: 8px; border: 1px solid #f1f5f9;">
            <span style="font-size: 0.74rem; font-weight: 700; color: #64748b; margin-right: 4px;">
                <i class="fa-solid fa-bolt" style="color: #eab308;"></i> Filter Cepat Tanggal:
            </span>
            <button type="button" onclick="setDatePreset('today')" style="background: <?= ($startDate === date('Y-m-d') && $endDate === date('Y-m-d')) ? 'var(--primary, #10b981)' : '#ffffff' ?>; color: <?= ($startDate === date('Y-m-d') && $endDate === date('Y-m-d')) ? '#ffffff' : '#334155' ?>; border: 1px solid #cbd5e1; padding: 3px 9px; border-radius: 6px; font-size: 0.73rem; font-weight: 600; cursor: pointer;">Hari Ini</button>
            <button type="button" onclick="setDatePreset('yesterday')" style="background: <?= ($startDate === date('Y-m-d', strtotime('-1 day')) && $endDate === date('Y-m-d', strtotime('-1 day')) ? 'var(--primary, #10b981)' : '#ffffff') ?>; color: <?= ($startDate === date('Y-m-d', strtotime('-1 day')) && $endDate === date('Y-m-d', strtotime('-1 day')) ? '#ffffff' : '#334155') ?>; border: 1px solid #cbd5e1; padding: 3px 9px; border-radius: 6px; font-size: 0.73rem; font-weight: 600; cursor: pointer;">Kemarin</button>
            <button type="button" onclick="setDatePreset('7days')" style="background: #ffffff; color: #334155; border: 1px solid #cbd5e1; padding: 3px 9px; border-radius: 6px; font-size: 0.73rem; font-weight: 600; cursor: pointer;">7 Hari Terakhir</button>
            <button type="button" onclick="setDatePreset('this_month')" style="background: #ffffff; color: #334155; border: 1px solid #cbd5e1; padding: 3px 9px; border-radius: 6px; font-size: 0.73rem; font-weight: 600; cursor: pointer;">Bulan Ini</button>
            <button type="button" onclick="setDatePreset('all')" style="background: <?= (empty($startDate) && empty($endDate)) ? '#e2e8f0' : '#ffffff' ?>; color: #334155; border: 1px solid #cbd5e1; padding: 3px 9px; border-radius: 6px; font-size: 0.73rem; font-weight: 600; cursor: pointer;">Semua Tanggal</button>

            <?php if (!empty($startDate) || !empty($endDate)): ?>
                <span style="margin-left: auto; font-size: 0.72rem; color: #0284c7; background: #e0f2fe; padding: 2px 8px; border-radius: 4px; font-weight: 600;">
                    <i class="fa-regular fa-calendar-check"></i> Filter aktif: <?= esc($startDate ?: 'Awal') ?> s/d <?= esc($endDate ?: 'Sekarang') ?>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <div class="table-responsive">
        <?php if (($tab ?? 'ride') === 'food'): ?>
            <!-- ================= TABEL MAIFOOD & MAIMART ================= -->
            <table>
                <thead>
                    <tr>
                        <th style="min-width: 100px;">ID & Layanan</th>
                        <th style="min-width: 150px;">Pelanggan</th>
                        <th style="min-width: 220px;">Toko / Resto & Menu</th>
                        <th style="min-width: 160px;">Kurir</th>
                        <th style="min-width: 130px;">Total & Bayar</th>
                        <th style="min-width: 180px;">Alamat Antar</th>
                        <th style="min-width: 110px;">Waktu</th>
                        <th style="min-width: 100px;">Status</th>
                        <th style="min-width: 90px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>
                                    <strong style="display:block; font-size: 0.95rem; color: #0f172a;">#F-<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></strong>
                                    <?php if (($order['order_type'] ?? 'food') === 'mart'): ?>
                                        <span class="badge" style="background: #f0fdf4; color: #16a34a; border: 1px solid #dcfce7; font-size: 0.7rem; padding: 2px 6px;">
                                            <i class="fa-solid fa-store"></i> MaiMart
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; font-size: 0.7rem; padding: 2px 6px;">
                                            <i class="fa-solid fa-utensils"></i> MaiFood
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="width: 28px; height: 28px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; color: #475569; flex-shrink: 0;">
                                            <i class="fa-solid fa-user"></i>
                                        </div>
                                        <div>
                                            <strong style="color: #1e293b;"><?= esc($order['user_name'] ?? ('User #' . $order['user_id'])) ?></strong>
                                            <?php if (!empty($order['user_phone'])): ?>
                                                <div style="font-size: 0.75rem; color: var(--text-muted);"><?= esc($order['user_phone']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong style="color: #c2410c; display: flex; align-items: center; gap: 4px;">
                                            <i class="fa-solid fa-store" style="font-size: 0.8rem;"></i> <?= esc($order['merchant_name'] ?? 'Mitra Resto/Toko') ?>
                                        </strong>
                                        <?php if (!empty($order['items_summary']) && $order['items_summary'] !== '-'): ?>
                                            <div style="font-size: 0.78rem; color: #334155; margin-top: 4px; line-height: 1.35; background: #f8fafc; padding: 4px 8px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                                <i class="fa-solid fa-bowl-food" style="color: #f97316; font-size: 0.7rem;"></i> <?= esc($order['items_summary']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($order['status'] === 'cancelled'): ?>
                                        <?php if (!empty($order['driver_name'])): ?>
                                            <div style="color: #64748b; font-size: 0.82rem; text-decoration: line-through;">
                                                <i class="fa-solid fa-motorcycle"></i> <?= esc($order['driver_name']) ?>
                                            </div>
                                            <span style="font-size: 0.72rem; color: #dc2626; font-weight: 600; display: inline-flex; align-items: center; gap: 3px; margin-top: 2px;">
                                                <i class="fa-solid fa-ban"></i> Batal
                                            </span>
                                        <?php else: ?>
                                            <span style="font-size: 0.73rem; color: #dc2626; font-weight: 600; background: #fee2e2; border: 1px solid #fecaca; padding: 2px 7px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                                                <i class="fa-solid fa-ban"></i> Tidak Ada Kurir (Batal)
                                            </span>
                                        <?php endif; ?>
                                    <?php elseif (!empty($order['driver_name'])): ?>
                                        <strong style="color: #047857; display: flex; align-items: center; gap: 4px;">
                                            <i class="fa-solid fa-motorcycle" style="font-size: 0.8rem;"></i> <?= esc($order['driver_name']) ?>
                                        </strong>
                                        <?php if (!empty($order['driver_plate'])): ?>
                                            <span style="font-size: 0.72rem; background: #e2e8f0; padding: 1px 5px; border-radius: 4px; font-weight: 700; color: #334155;"><?= esc($order['driver_plate']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($order['driver_phone'])): ?>
                                            <div style="font-size: 0.75rem; color: var(--text-muted);"><?= esc($order['driver_phone']) ?></div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge" style="background: #fef3c7; color: #92400e; font-size: 0.75rem; padding: 3px 6px;">
                                            <i class="fa-regular fa-clock"></i> Mencari Driver
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-size: 0.95rem; font-weight: 800; color: var(--primary);">
                                        Rp <?= number_format($order['total_price'], 0, ',', '.') ?>
                                    </div>
                                    <div style="margin-top: 3px;">
                                        <?php if (($order['payment_method'] ?? 'cash') === 'maipay'): ?>
                                            <span class="badge" style="background: #eff6ff; color: #1d4ed8; font-size: 0.7rem; padding: 1px 6px;">MaiPay</span>
                                        <?php else: ?>
                                            <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 0.7rem; padding: 1px 6px;">Tunai</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 0.8rem; color: #334155; line-height: 1.35; max-width: 220px;">
                                        <i class="fa-solid fa-location-dot" style="color: #ef4444; font-size: 0.75rem;"></i> <?= esc($order['delivery_address'] ?? '-') ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 0.8rem; color: #334155;">
                                        <?= date('d M Y', strtotime($order['created_at'])) ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">
                                        <?= date('H:i', strtotime($order['created_at'])) ?> WITA
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $st = strtolower($order['status'] ?? 'pending');
                                    $statusBadgeMapFood = [
                                        'completed'            => ['label' => 'Selesai', 'bg' => '#dcfce7', 'color' => '#166534', 'border' => '#86efac'],
                                        'delivered'            => ['label' => 'Selesai', 'bg' => '#dcfce7', 'color' => '#166534', 'border' => '#86efac'],
                                        'cancelled'            => ['label' => 'Batal', 'bg' => '#fee2e2', 'color' => '#dc2626', 'border' => '#fca5a5'],
                                        'delivering'           => ['label' => 'Sedang Diantar', 'bg' => '#e0f2fe', 'color' => '#0369a1', 'border' => '#bae6fd'],
                                        'in_progress'          => ['label' => 'Sedang Diantar', 'bg' => '#e0f2fe', 'color' => '#0369a1', 'border' => '#bae6fd'],
                                        'driver_at_merchant'   => ['label' => 'Kurir di Resto', 'bg' => '#ede9fe', 'color' => '#6d28d9', 'border' => '#ddd6fe'],
                                        'driver_assigned'      => ['label' => 'Kurir Menuju Resto', 'bg' => '#eff6ff', 'color' => '#1d4ed8', 'border' => '#bfdbfe'],
                                        'accepted_by_driver'   => ['label' => 'Kurir Menuju Resto', 'bg' => '#eff6ff', 'color' => '#1d4ed8', 'border' => '#bfdbfe'],
                                        'ready'                => ['label' => 'Pesanan Siap', 'bg' => '#ccfbf1', 'color' => '#0f766e', 'border' => '#99f6e4'],
                                        'processing'           => ['label' => 'Dimasak Resto', 'bg' => '#ffedd5', 'color' => '#c2410c', 'border' => '#fed7aa'],
                                        'accepted_by_merchant' => ['label' => 'Diterima Resto', 'bg' => '#fef3c7', 'color' => '#b45309', 'border' => '#fde68a'],
                                        'pending'              => ['label' => 'Menunggu Resto', 'bg' => '#fff7ed', 'color' => '#c2410c', 'border' => '#ffedd5'],
                                    ];
                                    $stFood = $statusBadgeMapFood[$st] ?? ['label' => ucfirst($st), 'bg' => '#f1f5f9', 'color' => '#475569', 'border' => '#cbd5e1'];
                                    ?>
                                    <span class="badge" style="background: <?= $stFood['bg'] ?>; color: <?= $stFood['color'] ?>; border: 1px solid <?= $stFood['border'] ?>; font-size: 0.74rem; padding: 3px 8px; font-weight: 700; border-radius: 6px;">
                                        <?= esc($stFood['label']) ?>
                                    </span>
                                    <?php if ($st === 'cancelled' && !empty($order['cancel_reason'])): ?>
                                        <div style="font-size: 0.72rem; color: #b91c1c; margin-top: 4px; background: #fef2f2; padding: 3px 6px; border-radius: 4px; border: 1px solid #fee2e2; line-height: 1.3;">
                                            <i class="fa-solid fa-circle-exclamation" style="color: #ef4444;"></i> <?= esc($order['cancel_reason']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center; white-space: nowrap;">
                                    <div style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                                        <button type="button" onclick="openOrderDetail('food', <?= $order['id'] ?>)" style="padding: 5px 10px; background: #0284c7; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 1px 2px rgba(2, 132, 199, 0.2);" title="Lihat Rincian Lengkap">
                                            <i class="fa-solid fa-eye"></i> Detail
                                        </button>
                                        <?php if (in_array($order['status'], ['pending', 'processing', 'ready', 'accepted_by_driver', 'in_progress'])): ?>
                                            <form action="<?= base_url('admin/orders/cancel/maifood/' . $order['id']) ?>" method="post" onsubmit="return confirm('Yakin ingin membatalkan pesanan MaiFood ini secara paksa?');" style="margin: 0;">
                                                <?= csrf_field() ?>
                                                <button type="submit" style="padding: 5px 9px; background: #ef4444; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;" title="Batalkan Pesanan Paksa">
                                                    <i class="fa-solid fa-ban"></i> Batal
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 40px 10px;">
                                <i class="fa-solid fa-utensils" style="font-size: 2rem; opacity: 0.25; margin-bottom: 8px; display: block;"></i>
                                Belum ada riwayat pesanan MaiFood atau MaiMart yang sesuai.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

        <?php else: ?>
            <!-- ================= TABEL MAIRIDE & PENGANTARAN ================= -->
            <table>
                <thead>
                    <tr>
                        <th style="min-width: 100px;">ID & Layanan</th>
                        <th style="min-width: 150px;">Pelanggan</th>
                        <th style="min-width: 160px;">Pengemudi</th>
                        <th style="min-width: 130px;">Jarak & Harga</th>
                        <th style="min-width: 220px;">Rute / Barang</th>
                        <th style="min-width: 110px;">Waktu</th>
                        <th style="min-width: 110px;">Durasi (Menit)</th>
                        <th style="min-width: 100px;">Status</th>
                        <th style="min-width: 90px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>
                                    <strong style="display:block; font-size: 0.95rem; color: #0f172a;">#ORD-<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></strong>
                                    <?php
                                    $vType = strtolower($order['service_type'] ?? 'motor');
                                    if (in_array($vType, ['car', 'mobil'])):
                                    ?>
                                        <span class="badge" style="background: #eff6ff; color: #2563eb; border: 1px solid #dbeafe; font-size: 0.7rem; padding: 2px 6px;">
                                            <i class="fa-solid fa-car"></i> MaiCar
                                        </span>
                                    <?php elseif (str_contains($vType, 'send')): ?>
                                        <span class="badge" style="background: #faf5ff; color: #9333ea; border: 1px solid #f3e8ff; font-size: 0.7rem; padding: 2px 6px;">
                                            <i class="fa-solid fa-box"></i> MaiSend
                                        </span>
                                    <?php elseif (str_contains($vType, 'titip')): ?>
                                        <span class="badge" style="background: #fffbeb; color: #d97706; border: 1px solid #fef3c7; font-size: 0.7rem; padding: 2px 6px;">
                                            <i class="fa-solid fa-bag-shopping"></i> MaiTitip
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; font-size: 0.7rem; padding: 2px 6px;">
                                            <i class="fa-solid fa-motorcycle"></i> MaiRide
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="width: 28px; height: 28px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; color: #475569; flex-shrink: 0;">
                                            <i class="fa-solid fa-user"></i>
                                        </div>
                                        <div>
                                            <strong style="color: #1e293b;"><?= esc($order['user_name'] ?? ('User #' . $order['user_id'])) ?></strong>
                                            <?php if (!empty($order['user_phone'])): ?>
                                                <div style="font-size: 0.75rem; color: var(--text-muted);"><?= esc($order['user_phone']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($order['status'] === 'cancelled'): ?>
                                        <?php if (!empty($order['driver_name'])): ?>
                                            <div style="color: #64748b; font-size: 0.82rem; text-decoration: line-through;">
                                                <i class="fa-solid fa-motorcycle"></i> <?= esc($order['driver_name']) ?>
                                            </div>
                                            <span style="font-size: 0.72rem; color: #dc2626; font-weight: 600; display: inline-flex; align-items: center; gap: 3px; margin-top: 2px;">
                                                <i class="fa-solid fa-ban"></i> Batal
                                            </span>
                                        <?php else: ?>
                                            <span style="font-size: 0.73rem; color: #dc2626; font-weight: 600; background: #fee2e2; border: 1px solid #fecaca; padding: 2px 7px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                                                <i class="fa-solid fa-ban"></i> Tidak Ada Driver (Batal)
                                            </span>
                                        <?php endif; ?>
                                    <?php elseif (!empty($order['driver_name'])): ?>
                                        <strong style="color: #047857; display: flex; align-items: center; gap: 4px;">
                                            <i class="fa-solid fa-motorcycle" style="font-size: 0.8rem;"></i> <?= esc($order['driver_name']) ?>
                                        </strong>
                                        <?php if (!empty($order['driver_plate'])): ?>
                                            <span style="font-size: 0.72rem; background: #e2e8f0; padding: 1px 5px; border-radius: 4px; font-weight: 700; color: #334155;"><?= esc($order['driver_plate']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($order['driver_phone'])): ?>
                                            <div style="font-size: 0.75rem; color: var(--text-muted);"><?= esc($order['driver_phone']) ?></div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge" style="background: #fef3c7; color: #92400e; font-size: 0.75rem; padding: 3px 6px;">
                                            <i class="fa-regular fa-clock"></i> Mencari Driver
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-size: 0.95rem; font-weight: 800; color: var(--primary);">
                                        Rp <?= number_format($order['price'], 0, ',', '.') ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                                        <?= $order['distance'] ?> KM
                                    </div>
                                    <div style="margin-top: 2px;">
                                        <?php if (($order['payment_method'] ?? 'cash') === 'maipay'): ?>
                                            <span class="badge" style="background: #eff6ff; color: #1d4ed8; font-size: 0.7rem; padding: 1px 6px;">MaiPay</span>
                                        <?php else: ?>
                                            <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 0.7rem; padding: 1px 6px;">Tunai</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 0.78rem; color: #334155; line-height: 1.35; max-width: 240px;">
                                        <?php if (!empty($order['pickup_address']) && !empty($order['dropoff_address'])): ?>
                                            <div><span style="color: #10b981; font-weight: 600;">Jemput:</span> <?= esc($order['pickup_address']) ?></div>
                                            <div style="margin-top: 2px;"><span style="color: #ef4444; font-weight: 600;">Antar:</span> <?= esc($order['dropoff_address']) ?></div>
                                        <?php elseif (!empty($order['item_description'])): ?>
                                            <div><span style="color: #8b5cf6; font-weight: 600;">Barang:</span> <?= esc($order['item_description']) ?></div>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted);">-</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 0.8rem; color: #334155;">
                                        <?= date('d M Y', strtotime($order['created_at'])) ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">
                                        <?= date('H:i', strtotime($order['created_at'])) ?> WITA
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $pickup_duration = '-';
                                    $dropoff_duration = '-';

                                    if (!empty($order['accepted_at']) && !empty($order['in_progress_at'])) {
                                        $pickup_diff = strtotime($order['in_progress_at']) - strtotime($order['accepted_at']);
                                        $pickup_duration = round($pickup_diff / 60) . ' mnt';
                                    }

                                    if (!empty($order['in_progress_at']) && !empty($order['completed_at'])) {
                                        $dropoff_diff = strtotime($order['completed_at']) - strtotime($order['in_progress_at']);
                                        $dropoff_duration = round($dropoff_diff / 60) . ' mnt';
                                    }
                                    ?>
                                    <div style="font-size: 0.8rem;">
                                        <span style="color: var(--text-muted);">Jemput:</span> <strong><?= $pickup_duration ?></strong><br>
                                        <span style="color: var(--text-muted);">Antar:</span> <strong><?= $dropoff_duration ?></strong>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $st = strtolower($order['status'] ?? 'pending');
                                    $statusBadgeMapRide = [
                                        'completed'   => ['label' => 'Selesai', 'bg' => '#dcfce7', 'color' => '#166534', 'border' => '#86efac'],
                                        'cancelled'   => ['label' => 'Batal', 'bg' => '#fee2e2', 'color' => '#dc2626', 'border' => '#fca5a5'],
                                        'in_progress' => ['label' => 'Dalam Perjalanan', 'bg' => '#e0f2fe', 'color' => '#0369a1', 'border' => '#bae6fd'],
                                        'on_trip'     => ['label' => 'Dalam Perjalanan', 'bg' => '#e0f2fe', 'color' => '#0369a1', 'border' => '#bae6fd'],
                                        'arrived'     => ['label' => 'Driver Sampai', 'bg' => '#ede9fe', 'color' => '#6d28d9', 'border' => '#ddd6fe'],
                                        'accepted'    => ['label' => 'Diterima Driver', 'bg' => '#eff6ff', 'color' => '#1d4ed8', 'border' => '#bfdbfe'],
                                        'pending'     => ['label' => 'Mencari Driver', 'bg' => '#fff7ed', 'color' => '#c2410c', 'border' => '#ffedd5'],
                                    ];
                                    $stRide = $statusBadgeMapRide[$st] ?? ['label' => ucfirst($st), 'bg' => '#f1f5f9', 'color' => '#475569', 'border' => '#cbd5e1'];
                                    ?>
                                    <span class="badge" style="background: <?= $stRide['bg'] ?>; color: <?= $stRide['color'] ?>; border: 1px solid <?= $stRide['border'] ?>; font-size: 0.74rem; padding: 3px 8px; font-weight: 700; border-radius: 6px;">
                                        <?= esc($stRide['label']) ?>
                                    </span>
                                    <?php if ($st === 'cancelled' && !empty($order['cancel_reason'])): ?>
                                        <div style="font-size: 0.72rem; color: #b91c1c; margin-top: 4px; background: #fef2f2; padding: 3px 6px; border-radius: 4px; border: 1px solid #fee2e2; line-height: 1.3;">
                                            <i class="fa-solid fa-circle-exclamation" style="color: #ef4444;"></i> <?= esc($order['cancel_reason']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center; white-space: nowrap;">
                                    <div style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                                        <button type="button" onclick="openOrderDetail('ride', <?= $order['id'] ?>)" style="padding: 5px 10px; background: #0284c7; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 1px 2px rgba(2, 132, 199, 0.2);" title="Lihat Rincian Lengkap">
                                            <i class="fa-solid fa-eye"></i> Detail
                                        </button>
                                        <?php if (in_array($order['status'], ['pending', 'accepted', 'in_progress'])): ?>
                                            <form action="<?= base_url('admin/orders/cancel/mairide/' . $order['id']) ?>" method="post" onsubmit="return confirm('Yakin ingin membatalkan pesanan ini secara paksa?');" style="margin: 0;">
                                                <?= csrf_field() ?>
                                                <button type="submit" style="padding: 5px 9px; background: #ef4444; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;" title="Batalkan Pesanan Paksa">
                                                    <i class="fa-solid fa-ban"></i> Batal
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 40px 10px;">
                                <i class="fa-solid fa-magnifying-glass" style="font-size: 2rem; opacity: 0.25; margin-bottom: 8px; display: block;"></i>
                                Tidak ada pesanan yang cocok dengan kriteria pencarian.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Footer Paginasi -->
    <?php if (!empty($pager)): ?>
        <div class="pagination-footer">
            <div class="pagination-info">
                <?php if ($totalCount > 0): ?>
                    Menampilkan <b><?= ($pager->getCurrentPage() - 1) * $perPage + 1 ?></b> - <b><?= min($pager->getCurrentPage() * $perPage, $totalCount) ?></b> dari <b><?= number_format($totalCount) ?></b> data
                <?php else: ?>
                    Menampilkan 0 data
                <?php endif; ?>
            </div>
            <?= $pager->links('default', 'admin_pager') ?>
        </div>
    <?php endif; ?>
</div>

<?= $this->include('admin/orders/modal_detail') ?>

<script>
function setDatePreset(preset) {
    const startInput = document.getElementById('orderStartDate');
    const endInput = document.getElementById('orderEndDate');
    const form = document.getElementById('orderFilterForm');
    if (!startInput || !endInput || !form) return;

    const today = new Date();
    const formatDate = (d) => {
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    if (preset === 'today') {
        const dStr = formatDate(today);
        startInput.value = dStr;
        endInput.value = dStr;
    } else if (preset === 'yesterday') {
        const y = new Date();
        y.setDate(today.getDate() - 1);
        const yStr = formatDate(y);
        startInput.value = yStr;
        endInput.value = yStr;
    } else if (preset === '7days') {
        const past = new Date();
        past.setDate(today.getDate() - 6);
        startInput.value = formatDate(past);
        endInput.value = formatDate(today);
    } else if (preset === 'this_month') {
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        startInput.value = formatDate(firstDay);
        endInput.value = formatDate(lastDay);
    } else if (preset === 'all') {
        startInput.value = '';
        endInput.value = '';
    }
    form.submit();
}
</script>
<?= $this->endSection() ?>