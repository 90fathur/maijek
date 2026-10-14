<?= $this->extend('admin/layout/main') ?>

<?= $this->section('content') ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
    <div>
        <h1 class="page-title animate-fade" style="margin-bottom: 4px;">Riwayat Pesanan</h1>
        <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">Pantau dan kelola seluruh transaksi pesanan transportasi dan pesan-antar makanan/toko.</p>
    </div>
    <a href="<?= base_url('admin/orders/exportCsv/' . (($tab ?? 'ride') === 'food' ? 'maifood' : 'mairide')) ?>" style="padding: 10px 18px; background: #10b981; color: white; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);">
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
    <a href="<?= base_url('admin/orders?tab=ride') ?>" style="padding: 14px 22px; font-weight: 700; font-size: 0.95rem; text-decoration: none; border-bottom: 3px solid <?= ($tab ?? 'ride') === 'ride' ? 'var(--primary, #10b981)' : 'transparent' ?>; color: <?= ($tab ?? 'ride') === 'ride' ? 'var(--primary, #10b981)' : '#64748b' ?>; display: inline-flex; align-items: center; gap: 10px; margin-bottom: -2px; transition: all 0.2s;">
        <i class="fa-solid fa-motorcycle" style="font-size: 1.1rem;"></i> 
        <span>MaiRide & Pengantaran</span>
        <span style="background: <?= ($tab ?? 'ride') === 'ride' ? '#d1fae5' : '#f1f5f9' ?>; color: <?= ($tab ?? 'ride') === 'ride' ? '#065f46' : '#64748b' ?>; font-size: 0.75rem; padding: 2px 8px; border-radius: 999px; font-weight: 800;">
            <?= number_format($totalRide ?? 0) ?>
        </span>
    </a>
    
    <a href="<?= base_url('admin/orders?tab=food') ?>" style="padding: 14px 22px; font-weight: 700; font-size: 0.95rem; text-decoration: none; border-bottom: 3px solid <?= ($tab ?? 'ride') === 'food' ? '#ea580c' : 'transparent' ?>; color: <?= ($tab ?? 'ride') === 'food' ? '#ea580c' : '#64748b' ?>; display: inline-flex; align-items: center; gap: 10px; margin-bottom: -2px; transition: all 0.2s;">
        <i class="fa-solid fa-utensils" style="font-size: 1.1rem;"></i> 
        <span>MaiFood & MaiMart</span>
        <span style="background: <?= ($tab ?? 'ride') === 'food' ? '#ffedd5' : '#f1f5f9' ?>; color: <?= ($tab ?? 'ride') === 'food' ? '#9a3412' : '#64748b' ?>; font-size: 0.75rem; padding: 2px 8px; border-radius: 999px; font-weight: 800;">
            <?= number_format($totalFood ?? 0) ?>
        </span>
    </a>
</div>

<div class="card-table animate-fade">
    <!-- Toolbar Pencarian & Filter -->
    <div class="table-toolbar">
        <form method="get" action="<?= base_url('admin/orders') ?>" class="toolbar-form">
            <input type="hidden" name="tab" value="<?= esc($tab ?? 'ride') ?>">
            
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" name="q" value="<?= esc($q ?? '') ?>" placeholder="<?= ($tab ?? 'ride') === 'food' ? 'Cari ID pesanan, nama resto/toko, pemesan, kurir, menu...' : 'Cari ID pesanan, nama pemesan, kurir, alamat jemput/tujuan...' ?>" class="search-input">
                <?php if (!empty($q)): ?>
                    <a href="<?= base_url('admin/orders?tab=' . esc($tab ?? 'ride')) ?>" class="clear-search" title="Hapus pencarian"><i class="fa-solid fa-xmark"></i></a>
                <?php endif; ?>
            </div>

            <div class="filter-box">
                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <?php if (($tab ?? 'ride') === 'food'): ?>
                        <option value="completed" <?= ($status ?? '') === 'completed' ? 'selected' : '' ?>>Selesai</option>
                        <option value="in_progress" <?= ($status ?? '') === 'in_progress' ? 'selected' : '' ?>>Sedang Diantar</option>
                        <option value="processing" <?= ($status ?? '') === 'processing' ? 'selected' : '' ?>>Sedang Dimasak / Disiapkan</option>
                        <option value="ready" <?= ($status ?? '') === 'ready' ? 'selected' : '' ?>>Siap Diambil Kurir</option>
                        <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : '' ?>>Menunggu Konfirmasi</option>
                        <option value="cancelled" <?= ($status ?? '') === 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
                    <?php else: ?>
                        <option value="completed" <?= ($status ?? '') === 'completed' ? 'selected' : '' ?>>Selesai</option>
                        <option value="in_progress" <?= ($status ?? '') === 'in_progress' ? 'selected' : '' ?>>Dalam Perjalanan</option>
                        <option value="accepted" <?= ($status ?? '') === 'accepted' ? 'selected' : '' ?>>Diterima Driver</option>
                        <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : '' ?>>Menunggu Driver</option>
                        <option value="cancelled" <?= ($status ?? '') === 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
                    <?php endif; ?>
                </select>
            </div>

            <button type="submit" class="btn-search">
                <i class="fa-solid fa-filter"></i> Filter
            </button>

            <?php if (!empty($q) || !empty($status)): ?>
                <a href="<?= base_url('admin/orders?tab=' . esc($tab ?? 'ride')) ?>" class="btn-reset" title="Reset filter">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </a>
            <?php endif; ?>
        </form>

        <div class="toolbar-stats">
            Total: <b><?= number_format($totalCount ?? 0) ?></b> Pesanan <?= ($tab ?? 'ride') === 'food' ? 'Food & Mart' : 'Ride & Kirim' ?>
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
                                    <?php if (!empty($order['driver_name'])): ?>
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
                                    <?php if ($order['status'] == 'completed'): ?>
                                        <span class="badge badge-success">Selesai</span>
                                    <?php elseif ($order['status'] == 'cancelled'): ?>
                                        <span class="badge badge-danger">Batal</span>
                                        <?php if (!empty($order['cancel_reason'])): ?>
                                            <div style="font-size: 0.72rem; color: #ef4444; margin-top: 3px; font-style: italic;">
                                                "<?= esc($order['cancel_reason']) ?>"
                                            </div>
                                        <?php endif; ?>
                                    <?php elseif (in_array($order['status'], ['in_progress', 'accepted_by_driver'])): ?>
                                        <span class="badge" style="background: #e0f2fe; color: #0284c7;">Diantar</span>
                                    <?php elseif (in_array($order['status'], ['processing', 'ready'])): ?>
                                        <span class="badge badge-warning">Dimasak</span>
                                    <?php else: ?>
                                        <span class="badge" style="background: #fef3c7; color: #92400e;">Menunggu</span>
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
                                    <?php if (!empty($order['driver_name'])): ?>
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
                                    <?php if ($order['status'] == 'completed'): ?>
                                        <span class="badge badge-success">Selesai</span>
                                    <?php elseif ($order['status'] == 'pending'): ?>
                                        <span class="badge badge-warning">Menunggu</span>
                                    <?php elseif ($order['status'] == 'cancelled'): ?>
                                        <span class="badge badge-danger">Dibatalkan</span>
                                        <?php if (!empty($order['cancel_reason'])): ?>
                                            <div style="font-size: 0.72rem; color: #ef4444; margin-top: 3px; font-style: italic;">
                                                "<?= esc($order['cancel_reason']) ?>"
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">Jalan</span>
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
<?= $this->endSection() ?>