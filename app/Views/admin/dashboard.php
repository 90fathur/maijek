<?= $this->extend('admin/layout/main') ?>

<?= $this->section('content') ?>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <div>
        <h1 class="page-title animate-fade" style="margin: 0;">Dashboard Ringkasan</h1>
        <span style="font-size: 0.8rem; color: var(--text-muted);">Pantau operasional dan pesanan masuk secara langsung</span>
    </div>

    <!-- Live Auto-Refresh & Audio Toolbar -->
    <div style="display: inline-flex; align-items: center; gap: 10px; background: #ffffff; padding: 6px 14px; border-radius: 30px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <!-- Live Status Indicator -->
        <div id="livePulseIndicator" style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.78rem; font-weight: 700; color: #059669;">
            <span class="pulse-dot" style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
            <span>Live (<span id="autoRefreshCounter">30</span>s)</span>
        </div>

        <span style="width: 1px; height: 16px; background: #e2e8f0;"></span>

        <!-- Toggle Auto-Refresh Button -->
        <button type="button" id="btnToggleAutoRefresh" onclick="toggleAutoRefresh()" style="border: none; background: none; font-size: 0.78rem; font-weight: 600; color: #475569; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;" title="Nyalakan / Jeda Pembaruan Otomatis">
            <i class="fa-solid fa-pause" id="iconToggleAutoRefresh"></i> <span id="labelToggleAutoRefresh">Jeda</span>
        </button>

        <span style="width: 1px; height: 16px; background: #e2e8f0;"></span>

        <!-- Sound Notification Toggle -->
        <button type="button" id="btnToggleSound" onclick="toggleSoundNotification()" style="border: none; background: none; font-size: 0.78rem; font-weight: 600; color: #2563eb; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;" title="Nyalakan / Matikan Bunyi Bel Pesanan">
            <i class="fa-solid fa-volume-high" id="iconToggleSound"></i> <span id="labelToggleSound">Suara ON</span>
        </button>

        <!-- Test Audio Button -->
        <button type="button" onclick="testSoundNotification()" style="padding: 2px 8px; background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; border-radius: 12px; font-size: 0.7rem; font-weight: 700; cursor: pointer;" title="Uji Coba Bunyi Bel">
            Tes
        </button>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
        <div class="stat-info">
            <h3>Total Pelanggan</h3>
            <p id="statTotalUsers"><?= $total_users ?? 0 ?></p>
        </div>
    </div>

    <div class="stat-card" style="cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onclick="document.getElementById('driverMonitoringSection').scrollIntoView({ behavior: 'smooth' })" title="Klik untuk lihat siapa pengemudi yang aktif & online">
        <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;"><i class="fa-solid fa-helmet-safety"></i></div>
        <div class="stat-info">
            <h3>Total Pengemudi</h3>
            <p id="statTotalDrivers"><?= $total_drivers ?? 0 ?></p>
            <div id="statOnlineDriversText" style="font-size: 0.75rem; color: #10b981; margin-top: 5px; font-weight: 600;">
                <i class="fa-solid fa-circle-check" style="margin-right: 4px;"></i> Online: <?= $online_motor ?? 0 ?> Motor | <?= $online_mobil ?? 0 ?> Mobil
            </div>
            <div style="font-size: 0.72rem; color: #0284c7; margin-top: 3px; font-weight: 600; display: flex; align-items: center; gap: 4px;">
                <span>Lihat Pengemudi Aktif</span> <i class="fa-solid fa-arrow-down" style="font-size: 0.65rem;"></i>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;"><i class="fa-solid fa-route"></i></div>
        <div class="stat-info">
            <h3>Total Pesanan</h3>
            <p id="statTotalOrders"><?= $total_orders ?? 0 ?></p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;"><i class="fa-solid fa-rupiah-sign"></i></div>
        <div class="stat-info">
            <h3 style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 5px;">Pend. Kotor (Total Transaksi)</h3>
            <p style="font-size: 1.25rem;">Rp <?= number_format((float)($total_gross_revenue ?? 0), 0, ',', '.') ?></p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;"><i class="fa-solid fa-wallet"></i></div>
        <div class="stat-info">
            <h3 style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 5px;">Pend. Bersih (Potongan Maijek)</h3>
            <p style="font-size: 1.25rem; color: var(--primary);">Rp <?= number_format((float)($total_net_revenue ?? 0), 0, ',', '.') ?></p>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(440px, 1fr)); gap: 24px;">
    <!-- Grafik Tren Modern & Rapi -->
    <?php
    $rawCounts = $chart_counts_raw ?? (json_decode($chart_counts, true) ?: []);
    $total7Hari = array_sum($rawCounts);
    $avg7Hari = !empty($rawCounts) ? number_format($total7Hari / count($rawCounts), 1, ',', '.') : '0';
    $puncak7Hari = !empty($rawCounts) ? max($rawCounts) : 0;
    ?>
    <div class="card-table animate-fade" style="display: flex; flex-direction: column;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 16px 20px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(16, 185, 129, 0.12); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <div>
                    <h2 style="margin: 0; font-size: 1.05rem;">Tren Pesanan</h2>
                    <span style="font-size: 0.75rem; color: var(--text-muted);">Statistik volume 7 hari terakhir</span>
                </div>
            </div>

            <!-- Type Switcher Pill: Area vs Batang -->
            <div style="display: inline-flex; background: #f1f5f9; padding: 3px; border-radius: 8px; gap: 3px; border: 1px solid #e2e8f0;">
                <button type="button" id="btnChartTypeLine" onclick="switchChartType('line')" class="chart-type-btn active" title="Tampilan Kurva Area">
                    <i class="fa-solid fa-chart-area" style="font-size: 0.75rem;"></i> Area
                </button>
                <button type="button" id="btnChartTypeBar" onclick="switchChartType('bar')" class="chart-type-btn" title="Tampilan Diagram Batang">
                    <i class="fa-solid fa-chart-simple" style="font-size: 0.75rem;"></i> Batang
                </button>
            </div>
        </div>

        <!-- Mini KPI Row Ringkasan -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; padding: 0 20px 14px;">
            <div style="background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 10px; padding: 9px 12px; display: flex; flex-direction: column;">
                <span style="font-size: 0.68rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px;">Total 7 Hari</span>
                <div style="display: flex; align-items: baseline; gap: 4px; margin-top: 3px;">
                    <strong style="font-size: 1.15rem; color: #0f172a; font-weight: 800;"><?= $total7Hari ?></strong>
                    <span style="font-size: 0.72rem; color: #94a3b8;">Pesanan</span>
                </div>
            </div>
            <div style="background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 10px; padding: 9px 12px; display: flex; flex-direction: column;">
                <span style="font-size: 0.68rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px;">Rata-Rata</span>
                <div style="display: flex; align-items: baseline; gap: 4px; margin-top: 3px;">
                    <strong style="font-size: 1.15rem; color: #10b981; font-weight: 800;"><?= $avg7Hari ?></strong>
                    <span style="font-size: 0.72rem; color: #94a3b8;">/ hari</span>
                </div>
            </div>
            <div style="background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 10px; padding: 9px 12px; display: flex; flex-direction: column;">
                <span style="font-size: 0.68rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px;">Puncak Harian</span>
                <div style="display: flex; align-items: baseline; gap: 4px; margin-top: 3px;">
                    <strong style="font-size: 1.15rem; color: #0284c7; font-weight: 800;"><?= $puncak7Hari ?></strong>
                    <span style="font-size: 0.72rem; color: #94a3b8;">Pesanan</span>
                </div>
            </div>
        </div>

        <div style="padding: 0 20px 20px; position: relative; height: 260px; width: 100%; flex: 1;">
            <canvas id="orderChart"></canvas>
        </div>
    </div>

    <!-- Pesanan Terbaru -->
    <div class="card-table animate-fade" style="display: flex; flex-direction: column;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(0, 166, 90, 0.12); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.15rem;">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <div>
                    <h2 style="margin: 0; font-size: 1.05rem; font-weight: 700;">Pesanan Terbaru</h2>
                    <span style="font-size: 0.75rem; color: var(--text-muted);">
                        <?php if (!empty($order_date)): ?>
                            Filter Tanggal: <strong style="color: var(--primary);"><?= date('d M Y', strtotime($order_date)) ?></strong> (<?= count($recent_orders) ?> pesanan)
                        <?php else: ?>
                            Aktivitas transaksi pemesanan terkini
                        <?php endif; ?>
                    </span>
                </div>
            </div>

            <!-- Toolbar Tanggal & Tombol Lihat Semua -->
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <form method="get" action="<?= base_url('admin') ?>" style="display: flex; align-items: center; gap: 6px; margin: 0;">
                    <div style="display: flex; align-items: center; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0 8px;">
                        <i class="fa-regular fa-calendar-days" style="color: #64748b; font-size: 0.8rem; margin-right: 6px;"></i>
                        <input type="date" id="dashboardOrderDate" name="order_date" value="<?= esc($order_date ?? '') ?>" style="padding: 6px 4px; border: none; background: transparent; font-size: 0.8rem; outline: none; color: #1e293b;" onchange="this.form.submit()" title="Pilih Tanggal Pesanan">
                    </div>
                    <?php if (!empty($order_date)): ?>
                        <a href="<?= base_url('admin') ?>" style="padding: 6px 10px; background: #fee2e2; color: #dc2626; border-radius: 8px; text-decoration: none; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;" title="Reset Filter Tanggal">
                            <i class="fa-solid fa-xmark"></i> Reset
                        </a>
                    <?php endif; ?>
                </form>

                <a href="<?= base_url('admin/orders' . (!empty($order_date) ? '?start_date=' . $order_date . '&end_date=' . $order_date : '')) ?>" class="btn-primary" style="font-size: 0.8rem; font-weight: 700; padding: 7px 14px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(0, 166, 90, 0.25);">
                    <i class="fa-solid fa-list-check"></i>
                    <span>Lihat Semua Pesanan</span>
                    <i class="fa-solid fa-arrow-right" style="font-size: 0.7rem;"></i>
                </a>
            </div>
        </div>

        <div id="recentOrdersListContainer" style="padding: 12px 16px; max-height: 480px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; flex: 1;">
            <?php if (!empty($recent_orders)): ?>
                <?php foreach ($recent_orders as $order): ?>
                    <?php
                    $st = strtolower($order['status'] ?? 'pending');
                    $isFood = ($order['type'] === 'food');

                    $statusBadgeMap = [
                        'completed'            => ['label' => 'Selesai', 'bg' => '#dcfce7', 'color' => '#166534', 'border' => '#86efac'],
                        'delivered'            => ['label' => 'Selesai', 'bg' => '#dcfce7', 'color' => '#166534', 'border' => '#86efac'],
                        'cancelled'            => ['label' => 'Batal', 'bg' => '#fee2e2', 'color' => '#dc2626', 'border' => '#fca5a5'],
                        'delivering'           => ['label' => 'Sedang Diantar', 'bg' => '#e0f2fe', 'color' => '#0369a1', 'border' => '#bae6fd'],
                        'in_progress'          => ['label' => $isFood ? 'Sedang Diantar' : 'Dalam Perjalanan', 'bg' => '#e0f2fe', 'color' => '#0369a1', 'border' => '#bae6fd'],
                        'on_trip'              => ['label' => 'Dalam Perjalanan', 'bg' => '#e0f2fe', 'color' => '#0369a1', 'border' => '#bae6fd'],
                        'driver_at_merchant'   => ['label' => 'Kurir di Resto', 'bg' => '#ede9fe', 'color' => '#6d28d9', 'border' => '#ddd6fe'],
                        'driver_assigned'      => ['label' => 'Kurir Menuju Resto', 'bg' => '#eff6ff', 'color' => '#1d4ed8', 'border' => '#bfdbfe'],
                        'ready'                => ['label' => 'Pesanan Siap', 'bg' => '#ccfbf1', 'color' => '#0f766e', 'border' => '#99f6e4'],
                        'processing'           => ['label' => 'Dimasak / Disiapkan', 'bg' => '#ffedd5', 'color' => '#c2410c', 'border' => '#fed7aa'],
                        'accepted_by_merchant' => ['label' => 'Diterima Resto', 'bg' => '#fef3c7', 'color' => '#b45309', 'border' => '#fde68a'],
                        'accepted'             => ['label' => $isFood ? 'Kurir Menuju Resto' : 'Diterima Driver', 'bg' => '#eff6ff', 'color' => '#1d4ed8', 'border' => '#bfdbfe'],
                        'arrived'              => ['label' => 'Driver Tiba di Lokasi', 'bg' => '#ede9fe', 'color' => '#6d28d9', 'border' => '#ddd6fe'],
                        'pending'              => ['label' => $isFood ? 'Menunggu Resto' : 'Mencari Driver', 'bg' => '#fef3c7', 'color' => '#92400e', 'border' => '#fde68a'],
                    ];
                    $stConfig = $statusBadgeMap[$st] ?? ['label' => ucfirst($st), 'bg' => '#f1f5f9', 'color' => '#475569', 'border' => '#cbd5e1'];
                    ?>
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); transition: transform 0.15s ease, box-shadow 0.15s ease;" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'" onmouseout="this.style.boxShadow='0 1px 3px rgba(0,0,0,0.04)'">

                        <!-- Header Baris: ID, Badge Layanan, Waktu, Status, Harga -->
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; padding-bottom: 8px; border-bottom: 1px dashed #e2e8f0;">
                            <div>
                                <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                    <span style="font-weight: 800; font-size: 0.95rem; color: #0f172a;"><?= esc($order['display_id']) ?></span>

                                    <?php if ($order['service_label'] === 'MaiFood'): ?>
                                        <span class="badge" style="background: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; font-size: 0.72rem; padding: 2px 7px; border-radius: 6px;">
                                            <i class="fa-solid fa-utensils"></i> MaiFood
                                        </span>
                                    <?php elseif ($order['service_label'] === 'MaiMart'): ?>
                                        <span class="badge" style="background: #f0fdf4; color: #16a34a; border: 1px solid #dcfce7; font-size: 0.72rem; padding: 2px 7px; border-radius: 6px;">
                                            <i class="fa-solid fa-store"></i> MaiMart
                                        </span>
                                    <?php elseif ($order['service_label'] === 'MaiCar'): ?>
                                        <span class="badge" style="background: #eff6ff; color: #2563eb; border: 1px solid #dbeafe; font-size: 0.72rem; padding: 2px 7px; border-radius: 6px;">
                                            <i class="fa-solid fa-car"></i> MaiCar
                                        </span>
                                    <?php elseif ($order['service_label'] === 'MaiSend'): ?>
                                        <span class="badge" style="background: #faf5ff; color: #9333ea; border: 1px solid #f3e8ff; font-size: 0.72rem; padding: 2px 7px; border-radius: 6px;">
                                            <i class="fa-solid fa-box"></i> MaiSend
                                        </span>
                                    <?php elseif ($order['service_label'] === 'MaiTitip'): ?>
                                        <span class="badge" style="background: #fffbeb; color: #d97706; border: 1px solid #fef3c7; font-size: 0.72rem; padding: 2px 7px; border-radius: 6px;">
                                            <i class="fa-solid fa-bag-shopping"></i> MaiTitip
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; font-size: 0.72rem; padding: 2px 7px; border-radius: 6px;">
                                            <i class="fa-solid fa-motorcycle"></i> MaiRide
                                        </span>
                                    <?php endif; ?>

                                    <span style="font-size: 0.75rem; color: #94a3b8;">
                                        <i class="fa-regular fa-clock" style="font-size: 0.7rem;"></i> <?= date('d M, H:i', strtotime($order['created_at'])) ?>
                                    </span>
                                </div>
                            </div>

                            <div style="text-align: right;">
                                <span class="badge" style="background: <?= $stConfig['bg'] ?>; color: <?= $stConfig['color'] ?>; border: 1px solid <?= $stConfig['border'] ?>; font-size: 0.72rem; padding: 3px 8px; font-weight: 700; border-radius: 6px;">
                                    <?= esc($stConfig['label']) ?>
                                </span>

                                <div style="font-weight: 800; font-size: 0.95rem; color: var(--primary); margin-top: 3px;">
                                    Rp <?= number_format($order['price'], 0, ',', '.') ?>
                                </div>
                                <button type="button" onclick="openOrderDetail('<?= $order['type'] ?>', <?= (int)($order['raw_id'] ?? preg_replace('/[^0-9]/', '', $order['id'])) ?>)" style="padding: 3px 8px; background: #0284c7; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.72rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; margin-top: 4px; box-shadow: 0 1px 2px rgba(2, 132, 199, 0.2);" title="Lihat Rincian Lengkap">
                                    <i class="fa-solid fa-eye"></i> Detail
                                </button>
                            </div>
                        </div>

                        <!-- Baris Rincian: Pemesan | Resto/Food/Item | Kurir -->
                        <div style="display: grid; grid-template-columns: 1fr; gap: 6px; font-size: 0.8rem; background: #f8fafc; padding: 8px 10px; border-radius: 8px; border: 1px solid #f1f5f9;">

                            <!-- 1. Pemesan (Customer) -->
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="width: 22px; height: 22px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: #475569; flex-shrink: 0;">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                                <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <span style="color: #64748b; font-size: 0.75rem;">Pemesan:</span>
                                    <strong style="color: #1e293b; margin-left: 2px;"><?= esc($order['user_name']) ?></strong>
                                    <?php if (!empty($order['user_phone'])): ?>
                                        <span style="color: #94a3b8; font-size: 0.72rem;">(<?= esc($order['user_phone']) ?>)</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- 2. Food/Resto/Toko & Menu yang Dipesan ATAU Rute Layanan -->
                            <?php if (!empty($order['merchant_name'])): ?>
                                <div style="display: flex; align-items: flex-start; gap: 8px;">
                                    <div style="width: 22px; height: 22px; border-radius: 50%; background: #ffedd5; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: #ea580c; flex-shrink: 0; margin-top: 2px;">
                                        <i class="fa-solid fa-store"></i>
                                    </div>
                                    <div style="min-width: 0; flex: 1;">
                                        <div style="display: flex; align-items: center; gap: 4px; flex-wrap: wrap;">
                                            <span style="color: #64748b; font-size: 0.75rem;">Toko / Resto:</span>
                                            <strong style="color: #c2410c;"><?= esc($order['merchant_name']) ?></strong>
                                        </div>
                                        <?php if (!empty($order['items_summary'])): ?>
                                            <div style="color: #334155; font-size: 0.75rem; margin-top: 3px; line-height: 1.3; background: #ffffff; padding: 4px 8px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                                <span style="color: #ea580c; font-weight: 600;"><i class="fa-solid fa-bowl-food" style="font-size: 0.7rem;"></i> Menu:</span> <?= esc($order['items_summary']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php elseif (!empty($order['items_summary'])): ?>
                                <div style="display: flex; align-items: flex-start; gap: 8px;">
                                    <div style="width: 22px; height: 22px; border-radius: 50%; background: #e0f2fe; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: #0284c7; flex-shrink: 0; margin-top: 2px;">
                                        <i class="fa-solid fa-location-dot"></i>
                                    </div>
                                    <div style="color: #334155; font-size: 0.75rem; min-width: 0; flex: 1; line-height: 1.35;">
                                        <span style="color: #64748b; font-weight: 500;">Rute / Detail:</span> <?= esc($order['items_summary']) ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- 3. Kurir / Driver -->
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="width: 22px; height: 22px; border-radius: 50%; background: <?= !empty($order['driver_name']) ? '#d1fae5' : ($st === 'cancelled' ? '#fee2e2' : '#fef3c7') ?>; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: <?= !empty($order['driver_name']) ? '#059669' : ($st === 'cancelled' ? '#dc2626' : '#d97706') ?>; flex-shrink: 0;">
                                    <i class="fa-solid <?= $st === 'cancelled' && empty($order['driver_name']) ? 'fa-ban' : 'fa-motorcycle' ?>"></i>
                                </div>
                                <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1;">
                                    <span style="color: #64748b; font-size: 0.75rem;">Kurir:</span>
                                    <?php if (!empty($order['driver_name'])): ?>
                                        <strong style="color: #047857; margin-left: 2px;"><?= esc($order['driver_name']) ?></strong>
                                        <?php if (!empty($order['driver_plate'])): ?>
                                            <span style="background: #e2e8f0; color: #334155; font-size: 0.68rem; font-weight: 700; padding: 1px 5px; border-radius: 4px; margin-left: 3px;"><?= esc($order['driver_plate']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($order['driver_phone'])): ?>
                                            <span style="color: #94a3b8; font-size: 0.72rem;">(<?= esc($order['driver_phone']) ?>)</span>
                                        <?php endif; ?>
                                        <?php if ($st === 'cancelled'): ?>
                                            <span style="color: #dc2626; font-size: 0.72rem; font-weight: 600; margin-left: 4px;">(Dibatalkan)</span>
                                        <?php endif; ?>
                                    <?php elseif ($st === 'cancelled'): ?>
                                        <span style="color: #dc2626; font-weight: 600; font-size: 0.75rem; margin-left: 2px;">
                                            <i class="fa-solid fa-ban" style="font-size: 0.7rem;"></i> Tidak Ada Kurir (Pesanan Dibatalkan)
                                        </span>
                                    <?php elseif ($st === 'completed'): ?>
                                        <span style="color: #64748b; font-size: 0.75rem; margin-left: 2px;">
                                            Selesai tanpa kurir
                                        </span>
                                    <?php elseif ($isFood && $st === 'pending'): ?>
                                        <span style="color: #d97706; font-style: italic; font-weight: 500; font-size: 0.75rem; margin-left: 2px;">
                                            <i class="fa-regular fa-clock" style="font-size: 0.7rem;"></i> Menunggu Konfirmasi Toko/Resto
                                        </span>
                                    <?php else: ?>
                                        <span style="color: #d97706; font-style: italic; font-weight: 500; font-size: 0.75rem; margin-left: 2px;">
                                            <i class="fa-solid fa-spinner fa-spin" style="font-size: 0.7rem;"></i> Belum Ada Kurir (Sedang Mencari...)
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- 4. Alasan Batal (Jika ada) -->
                            <?php if ($st === 'cancelled' && !empty($order['cancel_reason'])): ?>
                                <div style="margin-top: 4px; padding: 4px 8px; background: #fef2f2; border: 1px solid #fee2e2; border-radius: 6px; color: #b91c1c; font-size: 0.72rem; display: flex; align-items: center; gap: 6px;">
                                    <i class="fa-solid fa-circle-exclamation" style="color: #ef4444;"></i>
                                    <span><strong>Alasan Batal:</strong> <?= esc($order['cancel_reason']) ?></span>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; color: var(--text-muted); padding: 30px 10px;">
                    <i class="fa-regular fa-folder-open" style="font-size: 2rem; color: #cbd5e1; display: block; margin-bottom: 8px;"></i>
                    Belum ada pesanan masuk saat ini.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ================= DAFTAR PENGEMUDI AKTIF & ONLINE (MONITOR LANGSUNG) ================= -->
<?php
$fiveMinLimit = strtotime('-5 minutes');
$todayDate = date('Y-m-d');
$activeDriversList = $active_drivers ?? [];

$realtimeOnlineCount = 0;
$motorCount = 0;
$mobilCount = 0;

foreach ($activeDriversList as $drv) {
    $vType = strtolower($drv['vehicle_type'] ?? 'motor');
    if (in_array($vType, ['mobil', 'car'])) {
        $mobilCount++;
    } else {
        $motorCount++;
    }

    $isHbOnline = ($drv['is_online'] == 1 && !empty($drv['last_heartbeat_at']) && strtotime($drv['last_heartbeat_at']) >= $fiveMinLimit);
    if ($isHbOnline) {
        $realtimeOnlineCount++;
    }
}
?>

<div class="card-table animate-fade" id="driverMonitoringSection" style="margin-top: 24px;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; padding: 18px 24px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 42px; height: 42px; border-radius: 12px; background: rgba(16, 185, 129, 0.12); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                <i class="fa-solid fa-motorcycle"></i>
            </div>
            <div>
                <h2 style="margin: 0; font-size: 1.15rem; color: var(--text-color); display: flex; align-items: center; gap: 8px;">
                    Daftar Pengemudi Aktif & Status Online
                    <span style="display: inline-flex; align-items: center; gap: 5px; padding: 2px 9px; border-radius: 12px; background: #dcfce7; color: #166534; font-size: 0.72rem; font-weight: 700;">
                        <span class="live-indicator-dot"></span>
                        Live Real-Time
                    </span>
                </h2>
                <p style="margin: 3px 0 0; font-size: 0.8rem; color: var(--text-muted);">
                    Data pengemudi terverifikasi aktif, riwayat sinyal lokasi GPS terakhir, dan ketersediaan menerima pesanan.
                </p>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <a href="<?= base_url('admin/map') ?>" style="padding: 7px 14px; font-size: 0.8rem; font-weight: 600; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; background: #0284c7; color: white; transition: background 0.2s;" onmouseover="this.style.background='#0369a1'" onmouseout="this.style.background='#0284c7'">
                <i class="fa-solid fa-map-location-dot"></i> Buka Peta Driver Live
            </a>
            <a href="<?= base_url('admin/drivers') ?>" style="padding: 7px 14px; font-size: 0.8rem; font-weight: 600; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; background: var(--bg-body); color: var(--text-color); border: 1px solid var(--border-color);">
                <i class="fa-solid fa-users-gear"></i> Kelola Mitra Driver
            </a>
        </div>
    </div>

    <!-- Filter Buttons Bar -->
    <div style="padding: 12px 24px; background: var(--bg-body); border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; gap: 8px; flex-wrap: wrap;" id="driverFilterNav">
            <button type="button" onclick="setDriverFilter('all')" class="driver-filter-chip active" id="filter-all">
                Semua Aktif (<?= count($activeDriversList) ?>)
            </button>
            <button type="button" onclick="setDriverFilter('online')" class="driver-filter-chip" id="filter-online">
                <span style="width: 6px; height: 6px; border-radius: 50%; background: #16a34a; display: inline-block;"></span>
                Online Real-Time (<?= $realtimeOnlineCount ?>)
            </button>
            <button type="button" onclick="setDriverFilter('motor')" class="driver-filter-chip" id="filter-motor">
                🛵 Motor (<?= $motorCount ?>)
            </button>
            <button type="button" onclick="setDriverFilter('mobil')" class="driver-filter-chip" id="filter-mobil">
                🚗 Mobil (<?= $mobilCount ?>)
            </button>
        </div>
        <div style="font-size: 0.8rem; color: var(--text-muted);">
            Menampilkan: <b id="driverShowingCount"><?= count($activeDriversList) ?></b> Pengemudi
        </div>
    </div>

    <!-- Driver Table -->
    <div class="table-responsive">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;" id="activeDriversTable">
            <thead>
                <tr style="border-bottom: 2px solid var(--border-color); text-align: left; background: rgba(0,0,0,0.01);">
                    <th style="padding: 12px 18px; width: 60px;">ID</th>
                    <th style="padding: 12px 18px;">Pengemudi</th>
                    <th style="padding: 12px 18px;">Layanan / Kendaraan</th>
                    <th style="padding: 12px 18px;">Status Saat Ini</th>
                    <th style="padding: 12px 18px;">Sinyal GPS Terakhir</th>
                    <th style="padding: 12px 18px;">Saldo Dompet</th>
                    <th style="padding: 12px 18px; text-align: right;">Aksi Cepat</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($activeDriversList)): ?>
                    <?php foreach ($activeDriversList as $driver): ?>
                        <?php
                        $hbTime = !empty($driver['last_heartbeat_at']) ? strtotime($driver['last_heartbeat_at']) : null;
                        $isLive = ($driver['is_online'] == 1 && $hbTime !== null && $hbTime >= $fiveMinLimit);
                        $isToday = ($hbTime !== null && date('Y-m-d', $hbTime) === $todayDate);
                        $vType = strtolower($driver['vehicle_type'] ?? 'motor');
                        $isCar = in_array($vType, ['mobil', 'car']);

                        // Formatter waktu GPS
                        $timeDesc = 'Belum pernah aktif';
                        if ($hbTime !== null) {
                            $diffSec = time() - $hbTime;
                            if ($diffSec < 120) {
                                $timeDesc = '<span style="color: #15803d; font-weight: 700;">Baru saja (' . date('H:i', $hbTime) . ' WITA)</span>';
                            } elseif ($diffSec < 3600) {
                                $mins = floor($diffSec / 60);
                                $timeDesc = '<span style="color: #0369a1; font-weight: 600;">' . $mins . ' menit lalu (' . date('H:i', $hbTime) . ')</span>';
                            } elseif ($isToday) {
                                $timeDesc = 'Hari ini, ' . date('H:i', $hbTime) . ' WITA';
                            } else {
                                $timeDesc = date('d M Y, H:i', $hbTime);
                            }
                        }

                        // WhatsApp link
                        $cleanPhone = preg_replace('/[^0-9]/', '', (string)$driver['phone']);
                        if (str_starts_with($cleanPhone, '0')) {
                            $cleanPhone = '62' . substr($cleanPhone, 1);
                        }
                        ?>
                        <tr class="driver-row"
                            data-is-online="<?= $isLive ? '1' : '0' ?>"
                            data-vehicle-type="<?= $isCar ? 'mobil' : 'motor' ?>"
                            style="border-bottom: 1px solid var(--border-color); transition: background 0.15s;"
                            onmouseover="this.style.background='rgba(0,0,0,0.02)'"
                            onmouseout="this.style.background='transparent'">

                            <td style="padding: 12px 18px; font-weight: 700; color: var(--text-muted);">
                                #<?= esc($driver['id']) ?>
                            </td>

                            <td style="padding: 12px 18px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 38px; height: 38px; border-radius: 50%; background: <?= $isCar ? '#e0e7ff' : '#fef3c7' ?>; color: <?= $isCar ? '#4338ca' : '#b45309' ?>; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.95rem; flex-shrink: 0;">
                                        <?= strtoupper(substr(trim($driver['name']), 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: var(--text-color); font-size: 0.9rem;">
                                            <?= esc($driver['name']) ?>
                                        </div>
                                        <a href="https://wa.me/<?= $cleanPhone ?>" target="_blank" style="font-size: 0.76rem; color: var(--text-muted); text-decoration: none; display: inline-flex; align-items: center; gap: 4px; margin-top: 2px;" title="Chat WhatsApp">
                                            <i class="fa-brands fa-whatsapp" style="color: #25d366;"></i> <?= esc($driver['phone']) ?>
                                        </a>
                                    </div>
                                </div>
                            </td>

                            <td style="padding: 12px 18px;">
                                <?php if ($isCar): ?>
                                    <span style="font-size: 0.72rem; font-weight: 700; background: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe; padding: 2px 7px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="fa-solid fa-car"></i> MaiCar (Mobil)
                                    </span>
                                <?php else: ?>
                                    <span style="font-size: 0.72rem; font-weight: 700; background: #fef3c7; color: #92400e; border: 1px solid #fde68a; padding: 2px 7px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="fa-solid fa-motorcycle"></i> MaiRide / Food
                                    </span>
                                <?php endif; ?>
                                <div style="font-size: 0.78rem; font-weight: 600; color: var(--text-color); margin-top: 4px;">
                                    <?= esc($driver['vehicle_plate'] ?: '-') ?>
                                </div>
                                <?php if (!empty($driver['vehicle_brand'])): ?>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);"><?= esc($driver['vehicle_brand']) ?></div>
                                <?php endif; ?>
                            </td>

                            <td style="padding: 12px 18px;">
                                <?php if ($isLive): ?>
                                    <span style="padding: 3px 10px; border-radius: 20px; font-size: 0.74rem; font-weight: 700; background: #dcfce7; color: #15803d; display: inline-flex; align-items: center; gap: 5px; border: 1px solid #bbf7d0;">
                                        <span style="width: 7px; height: 7px; border-radius: 50%; background: #16a34a; box-shadow: 0 0 6px #16a34a;"></span>
                                        Online Real-Time
                                    </span>
                                <?php elseif ($driver['is_online'] == 1 && $isToday): ?>
                                    <span style="padding: 3px 10px; border-radius: 20px; font-size: 0.74rem; font-weight: 600; background: #fef9c3; color: #854d0e; display: inline-flex; align-items: center; gap: 5px; border: 1px solid #fef08a;">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #ca8a04;"></span>
                                        Siaga (Aktif Hari Ini)
                                    </span>
                                <?php else: ?>
                                    <span style="padding: 3px 10px; border-radius: 20px; font-size: 0.74rem; font-weight: 500; background: #f3f4f6; color: #6b7280; display: inline-flex; align-items: center; gap: 5px; border: 1px solid #e5e7eb;">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #9ca3af;"></span>
                                        Offline
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td style="padding: 12px 18px; font-size: 0.8rem; color: var(--text-muted);">
                                <?= $timeDesc ?>
                            </td>

                            <td style="padding: 12px 18px; font-weight: 700; color: <?= ((float)($driver['balance'] ?? 0) < 10000) ? '#dc2626' : '#15803d' ?>;">
                                Rp <?= number_format(max(0, (float)($driver['balance'] ?? 0)), 0, ',', '.') ?>
                            </td>

                            <td style="padding: 12px 18px; text-align: right;">
                                <div style="display: inline-flex; gap: 6px; align-items: center;">
                                    <a href="https://wa.me/<?= $cleanPhone ?>" target="_blank" style="padding: 6px 10px; border-radius: 6px; background: #25d366; color: white; text-decoration: none; font-size: 0.78rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;" title="Hubungi Driver via WhatsApp">
                                        <i class="fa-brands fa-whatsapp"></i> WA
                                    </a>
                                    <a href="<?= base_url('admin/map') ?>" style="padding: 6px 10px; border-radius: 6px; background: #0284c7; color: white; text-decoration: none; font-size: 0.78rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;" title="Pantau Lokasi GPS di Peta">
                                        <i class="fa-solid fa-location-dot"></i> Peta
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 30px; color: var(--text-muted);">
                            Belum ada pengemudi berstatus aktif yang terdaftar.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    @keyframes livePulse {
        0% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(22, 163, 74, 0.7);
        }

        70% {
            transform: scale(1);
            box-shadow: 0 0 0 6px rgba(22, 163, 74, 0);
        }

        100% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(22, 163, 74, 0);
        }
    }

    .live-indicator-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #16a34a;
        display: inline-block;
        animation: livePulse 1.8s infinite;
    }

    .driver-filter-chip {
        padding: 6px 13px;
        border-radius: 20px;
        border: 1px solid var(--border-color);
        background: var(--bg-card);
        color: var(--text-color);
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .driver-filter-chip:hover {
        border-color: var(--primary);
        color: var(--primary);
    }

    .driver-filter-chip.active {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .chart-type-btn {
        border: none;
        background: transparent;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .chart-type-btn:hover {
        color: #0f172a;
    }

    .chart-type-btn.active {
        background: #ffffff;
        color: #059669;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }
</style>

<script>
    let orderChart = null;
    let currentChartType = 'line';
    const chartLabels = <?= $chart_dates ?>;
    const chartCounts = <?= $chart_counts ?>;

    function initOrderChart(type) {
        const canvas = document.getElementById('orderChart');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');

        const maxVal = Math.max(...chartCounts, 0);
        // Memberi ruang atas agar saat jumlah 0-1 tidak mentok plafon grafik
        const suggestedMaxVal = Math.max(5, maxVal + 2);

        let dataset = {};

        if (type === 'bar') {
            dataset = {
                label: 'Jumlah Pesanan',
                data: chartCounts,
                backgroundColor: function(context) {
                    const chart = context.chart;
                    const {
                        ctx,
                        chartArea
                    } = chart;
                    if (!chartArea) return '#10b981';
                    const grad = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                    grad.addColorStop(0, '#10b981');
                    grad.addColorStop(1, '#059669');
                    return grad;
                },
                hoverBackgroundColor: '#047857',
                borderRadius: 8,
                borderSkipped: false,
                maxBarThickness: 38,
                barPercentage: 0.55,
                categoryPercentage: 0.7
            };
        } else {
            // 'line' (Sleek Area Chart dengan gradient halus)
            dataset = {
                label: 'Jumlah Pesanan',
                data: chartCounts,
                borderColor: '#10b981',
                borderWidth: 2.5,
                backgroundColor: function(context) {
                    const chart = context.chart;
                    const {
                        ctx,
                        chartArea
                    } = chart;
                    if (!chartArea) return 'rgba(16, 185, 129, 0.12)';
                    const grad = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                    grad.addColorStop(0, 'rgba(16, 185, 129, 0.28)');
                    grad.addColorStop(0.65, 'rgba(16, 185, 129, 0.06)');
                    grad.addColorStop(1, 'rgba(16, 185, 129, 0.0)');
                    return grad;
                },
                tension: 0.32,
                fill: true,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#10b981',
                pointBorderWidth: 2.5,
                pointRadius: 4.5,
                pointHoverRadius: 7,
                pointHoverBorderWidth: 3,
                pointHoverBackgroundColor: '#10b981',
                pointHoverBorderColor: '#ffffff'
            };
        }

        orderChart = new Chart(ctx, {
            type: type,
            data: {
                labels: chartLabels,
                datasets: [dataset]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMin: 0,
                        suggestedMax: suggestedMaxVal,
                        grace: '10%',
                        ticks: {
                            stepSize: 1,
                            precision: 0,
                            color: '#94a3b8',
                            font: {
                                family: "'Plus Jakarta Sans', system-ui, -apple-system, sans-serif",
                                size: 11,
                                weight: '500'
                            },
                            padding: 8
                        },
                        grid: {
                            color: '#f1f5f9',
                            borderDash: [4, 4],
                            drawTicks: false
                        },
                        border: {
                            display: false
                        }
                    },
                    x: {
                        ticks: {
                            color: '#64748b',
                            font: {
                                family: "'Plus Jakarta Sans', system-ui, -apple-system, sans-serif",
                                size: 11,
                                weight: '600'
                            },
                            padding: 8
                        },
                        grid: {
                            display: false,
                            drawTicks: false
                        },
                        border: {
                            color: '#e2e8f0'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#f8fafc',
                        titleFont: {
                            family: "'Plus Jakarta Sans', system-ui, -apple-system, sans-serif",
                            size: 12,
                            weight: '700'
                        },
                        bodyColor: '#e2e8f0',
                        bodyFont: {
                            family: "'Plus Jakarta Sans', system-ui, -apple-system, sans-serif",
                            size: 12
                        },
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                const val = context.parsed.y;
                                return ' ' + val + ' Transaksi Pesanan';
                            }
                        }
                    }
                }
            }
        });
    }

    function switchChartType(type) {
        if (!orderChart || currentChartType === type) return;
        currentChartType = type;

        const btnLine = document.getElementById('btnChartTypeLine');
        const btnBar = document.getElementById('btnChartTypeBar');
        if (btnLine) btnLine.classList.toggle('active', type === 'line');
        if (btnBar) btnBar.classList.toggle('active', type === 'bar');

        orderChart.destroy();
        initOrderChart(type);
    }

    document.addEventListener('DOMContentLoaded', function() {
        initOrderChart('line');
    });

    function setDriverFilter(type) {
        // Update active class on buttons
        const buttons = document.querySelectorAll('.driver-filter-chip');
        buttons.forEach(b => b.classList.remove('active'));
        const activeBtn = document.getElementById('filter-' + type);
        if (activeBtn) activeBtn.classList.add('active');

        // Filter table rows
        const rows = document.querySelectorAll('#activeDriversTable .driver-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const isOnline = row.getAttribute('data-is-online') === '1';
            const vType = row.getAttribute('data-vehicle-type');

            let show = false;
            if (type === 'all') {
                show = true;
            } else if (type === 'online') {
                show = isOnline;
            } else if (type === 'motor') {
                show = (vType === 'motor');
            } else if (type === 'mobil') {
                show = (vType === 'mobil');
            }

            if (show) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const countEl = document.getElementById('driverShowingCount');
        if (countEl) countEl.innerText = visibleCount;
    }

    // === REALTIME DASHBOARD AUTO-REFRESH & AUDIO CHIME ===
    let autoRefreshEnabled = true;
    let soundEnabled = localStorage.getItem('maijek_admin_sound_enabled') !== '0';
    let refreshIntervalSeconds = 25;
    let refreshCountdown = refreshIntervalSeconds;
    let lastKnownOrderKey = '<?= !empty($recent_orders) ? ($recent_orders[0]['type'] . '_' . ($recent_orders[0]['raw_id'] ?? $recent_orders[0]['id']) . '_' . $recent_orders[0]['status']) : '' ?>';
    let audioCtx = null;
    let isFirstCheck = true;

    function updateSoundButtonUI() {
        const btn = document.getElementById('btnToggleSound');
        const icon = document.getElementById('iconToggleSound');
        const label = document.getElementById('labelToggleSound');
        if (!btn || !icon || !label) return;

        if (soundEnabled) {
            btn.style.color = '#2563eb';
            icon.className = 'fa-solid fa-volume-high';
            label.innerText = 'Suara ON';
        } else {
            btn.style.color = '#94a3b8';
            icon.className = 'fa-solid fa-volume-xmark';
            label.innerText = 'Suara OFF';
        }
    }

    function toggleSoundNotification() {
        soundEnabled = !soundEnabled;
        localStorage.setItem('maijek_admin_sound_enabled', soundEnabled ? '1' : '0');
        updateSoundButtonUI();
        if (soundEnabled) {
            testSoundNotification();
        }
    }

    function toggleAutoRefresh() {
        autoRefreshEnabled = !autoRefreshEnabled;
        const btn = document.getElementById('btnToggleAutoRefresh');
        const icon = document.getElementById('iconToggleAutoRefresh');
        const label = document.getElementById('labelToggleAutoRefresh');
        const indicator = document.getElementById('livePulseIndicator');

        if (autoRefreshEnabled) {
            icon.className = 'fa-solid fa-pause';
            label.innerText = 'Jeda';
            indicator.innerHTML = '<span class="pulse-dot" style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block;"></span> <span>Live (<span id="autoRefreshCounter">' + refreshCountdown + '</span>s)</span>';
            indicator.style.color = '#059669';
        } else {
            icon.className = 'fa-solid fa-play';
            label.innerText = 'Lanjut';
            indicator.innerHTML = '<span style="width: 8px; height: 8px; border-radius: 50%; background: #94a3b8; display: inline-block;"></span> <span>Dijeda</span>';
            indicator.style.color = '#64748b';
        }
    }

    // Web Audio API Context
    function getAudioContext() {
        if (!audioCtx) {
            const AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (AudioContextClass) {
                audioCtx = new AudioContextClass();
            }
        }
        if (audioCtx && audioCtx.state === 'suspended') {
            audioCtx.resume();
        }
        return audioCtx;
    }

    // Melodic chime ("Ting-Ting!")
    function playOrderChime() {
        if (!soundEnabled) return;
        try {
            const ctx = getAudioContext();
            if (!ctx) return;
            const now = ctx.currentTime;

            // Tone 1: F#5 (740 Hz)
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(740, now);
            gain1.gain.setValueAtTime(0.3, now);
            gain1.gain.exponentialRampToValueAtTime(0.0001, now + 0.35);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(now);
            osc1.stop(now + 0.35);

            // Tone 2: B5 (987.77 Hz)
            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(987.77, now + 0.12);
            gain2.gain.setValueAtTime(0.38, now + 0.12);
            gain2.gain.exponentialRampToValueAtTime(0.0001, now + 0.75);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(now + 0.12);
            osc2.stop(now + 0.75);
        } catch (e) {
            console.warn('Audio chime playback issue:', e);
        }
    }

    function testSoundNotification() {
        getAudioContext();
        playOrderChime();
        showLiveToast({
            display_id: '#F-TEST',
            service_label: 'MaiFood (Uji Coba)',
            price_fmt: 'Rp 25.000',
            type: 'food',
            raw_id: 0
        });
    }

    function showLiveToast(order) {
        const toast = document.getElementById('liveOrderToast');
        const desc = document.getElementById('toastOrderDesc');
        const detailBtn = document.getElementById('toastDetailBtn');
        if (!toast) return;

        desc.innerText = `${order.display_id} • ${order.service_label} • ${order.price_fmt}`;
        detailBtn.onclick = function() {
            closeLiveToast();
            if (order.raw_id > 0) {
                openOrderDetail(order.type, order.raw_id);
            }
        };

        toast.style.display = 'block';
        if (window.liveToastTimer) clearTimeout(window.liveToastTimer);
        window.liveToastTimer = setTimeout(() => {
            closeLiveToast();
        }, 9000);
    }

    function closeLiveToast() {
        const toast = document.getElementById('liveOrderToast');
        if (toast) toast.style.display = 'none';
    }

    function fetchLiveUpdates() {
        const dateInput = document.getElementById('dashboardOrderDate');
        const orderDateVal = dateInput ? dateInput.value : '';
        const url = '<?= base_url('admin/dashboard/liveUpdates') ?>' + (orderDateVal ? '?order_date=' + encodeURIComponent(orderDateVal) : '');

        fetch(url)
            .then(res => res.json())
            .then(res => {
                if (res.status !== 'success') return;

                // 1. Update stats
                if (res.summary) {
                    const uEl = document.getElementById('statTotalUsers');
                    const dEl = document.getElementById('statTotalDrivers');
                    const oEl = document.getElementById('statOnlineDriversText');
                    const ordEl = document.getElementById('statTotalOrders');

                    if (uEl) uEl.innerText = Number(res.summary.total_users).toLocaleString('id-ID');
                    if (dEl) dEl.innerText = Number(res.summary.total_drivers).toLocaleString('id-ID');
                    if (ordEl) ordEl.innerText = Number(res.summary.total_orders).toLocaleString('id-ID');
                    if (oEl) {
                        oEl.innerHTML = `<i class="fa-solid fa-circle-check" style="margin-right: 4px;"></i> Online: ${res.summary.online_motor || 0} Motor | ${res.summary.online_mobil || 0} Mobil`;
                    }
                }

                // 2. Check for NEW orders
                if (res.latest_order_key && res.latest_order_key !== lastKnownOrderKey) {
                    if (!isFirstCheck) {
                        playOrderChime();
                        if (res.latest_order) {
                            showLiveToast(res.latest_order);
                        }
                    }
                    lastKnownOrderKey = res.latest_order_key;
                }
                isFirstCheck = false;

                // 3. Render updated recent orders
                if (res.recent_orders) {
                    renderRecentOrders(res.recent_orders);
                }
            })
            .catch(err => {
                console.warn('Dashboard live update error:', err);
            });
    }

    function renderRecentOrders(orders) {
        const container = document.getElementById('recentOrdersListContainer');
        if (!container) return;

        if (!orders || orders.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; color: var(--text-muted); padding: 35px 15px; background: #f8fafc; border-radius: 12px; border: 1px dashed #cbd5e1;">
                    <i class="fa-regular fa-folder-open" style="font-size: 2.2rem; color: #94a3b8; display: block; margin-bottom: 8px;"></i>
                    <strong style="color: #475569; display: block;">Tidak Ada Pesanan Ditemukan</strong>
                    <span style="font-size: 0.8rem; color: #64748b;">Belum ada aktivitas pesanan pada tanggal ini.</span>
                </div>`;
            return;
        }

        const statusMap = {
            'completed': { label: 'Selesai', style: 'background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;' },
            'cancelled': { label: 'Batal', style: 'background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;' },
            'delivering': { label: 'Diantar Kurir', style: 'background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;' },
            'in_progress': { label: 'Sedang Jalan', style: 'background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;' },
            'on_trip': { label: 'Di Perjalanan', style: 'background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;' },
            'driver_at_merchant': { label: 'Kurir di Resto', style: 'background: #fef3c7; color: #b45309; border: 1px solid #fde68a;' },
            'driver_assigned': { label: 'Kurir Menuju Resto', style: 'background: #fef3c7; color: #b45309; border: 1px solid #fde68a;' },
            'ready': { label: 'Siap Diantar', style: 'background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe;' },
            'processing': { label: 'Resto Menyiapkan', style: 'background: #fef3c7; color: #b45309; border: 1px solid #fde68a;' },
            'accepted_by_merchant': { label: 'Diterima Resto', style: 'background: #fef3c7; color: #b45309; border: 1px solid #fde68a;' },
            'accepted': { label: 'Diterima Driver', style: 'background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe;' },
            'arrived': { label: 'Kurir Sampai', style: 'background: #dbeafe; color: #1d4ed8; border: 1px solid #bfdbfe;' },
            'pending': { label: 'Mencari Kurir', style: 'background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5;' }
        };

        let html = '';
        orders.forEach(o => {
            let srvBadgeHtml = '';
            if (o.service_label === 'MaiFood') {
                srvBadgeHtml = `<span class="badge" style="background: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; font-size: 0.72rem; padding: 2px 7px; border-radius: 6px;"><i class="fa-solid fa-utensils"></i> MaiFood</span>`;
            } else if (o.service_label === 'MaiMart') {
                srvBadgeHtml = `<span class="badge" style="background: #f0fdf4; color: #16a34a; border: 1px solid #dcfce7; font-size: 0.72rem; padding: 2px 7px; border-radius: 6px;"><i class="fa-solid fa-store"></i> MaiMart</span>`;
            } else if (o.service_label === 'MaiCar') {
                srvBadgeHtml = `<span class="badge" style="background: #eff6ff; color: #2563eb; border: 1px solid #dbeafe; font-size: 0.72rem; padding: 2px 7px; border-radius: 6px;"><i class="fa-solid fa-car"></i> MaiCar</span>`;
            } else if (o.service_label === 'MaiSend') {
                srvBadgeHtml = `<span class="badge" style="background: #faf5ff; color: #9333ea; border: 1px solid #f3e8ff; font-size: 0.72rem; padding: 2px 7px; border-radius: 6px;"><i class="fa-solid fa-box"></i> MaiSend</span>`;
            } else if (o.service_label === 'MaiTitip') {
                srvBadgeHtml = `<span class="badge" style="background: #fffbeb; color: #d97706; border: 1px solid #fef3c7; font-size: 0.72rem; padding: 2px 7px; border-radius: 6px;"><i class="fa-solid fa-bag-shopping"></i> MaiTitip</span>`;
            } else {
                srvBadgeHtml = `<span class="badge" style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; font-size: 0.72rem; padding: 2px 7px; border-radius: 6px;"><i class="fa-solid fa-motorcycle"></i> MaiRide</span>`;
            }

            const isCancelled = (o.status === 'cancelled');
            const stObj = statusMap[o.status] || { label: o.status || 'Pending', style: 'background: #fef3c7; color: #92400e; border: 1px solid #fde68a;' };
            const stBadgeHtml = `<span class="badge" style="${stObj.style} font-size: 0.72rem; padding: 3px 8px; font-weight: 700; border-radius: 6px;">${escapeHtml(stObj.label)}</span>`;

            let driverHtml = '';
            let driverIconBg = '#fef3c7';
            let driverIconColor = '#d97706';

            if (isCancelled) {
                driverIconBg = '#fee2e2';
                driverIconColor = '#dc2626';
                driverHtml = `<span style="color: #dc2626; font-weight: 600; font-size: 0.75rem; margin-left: 2px;"><i class="fa-solid fa-ban" style="font-size: 0.7rem;"></i> Tidak Ada Kurir (Pesanan Dibatalkan)</span>`;
            } else if (o.driver_name) {
                driverIconBg = '#d1fae5';
                driverIconColor = '#059669';
                driverHtml = `
                    <strong style="color: #047857; margin-left: 2px;">${escapeHtml(o.driver_name)}</strong>
                    ${o.driver_plate ? `<span style="background: #e2e8f0; color: #334155; font-size: 0.68rem; font-weight: 700; padding: 1px 5px; border-radius: 4px; margin-left: 3px;">${escapeHtml(o.driver_plate)}</span>` : ''}
                    ${o.driver_phone ? `<span style="color: #94a3b8; font-size: 0.72rem;">(${escapeHtml(o.driver_phone)})</span>` : ''}
                `;
            } else {
                driverHtml = `<span style="color: #d97706; font-style: italic; font-weight: 500; font-size: 0.75rem; margin-left: 2px;"><i class="fa-regular fa-clock" style="font-size: 0.7rem;"></i> Belum Ada Kurir (Mencari...)</span>`;
            }

            let cancelReasonHtml = '';
            if (isCancelled && o.cancel_reason) {
                cancelReasonHtml = `
                    <div style="background: #fef2f2; border: 1px solid #fee2e2; border-radius: 6px; padding: 4px 8px; font-size: 0.72rem; color: #991b1b; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-circle-exclamation" style="font-size: 0.75rem; color: #ef4444; flex-shrink: 0;"></i>
                        <div>
                            <strong>Alasan Batal:</strong> ${escapeHtml(o.cancel_reason)}
                        </div>
                    </div>
                `;
            }

            let middleHtml = '';
            if (o.merchant_name) {
                middleHtml = `
                    <div style="display: flex; align-items: flex-start; gap: 8px;">
                        <div style="width: 22px; height: 22px; border-radius: 50%; background: #ffedd5; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: #ea580c; flex-shrink: 0; margin-top: 2px;">
                            <i class="fa-solid fa-store"></i>
                        </div>
                        <div style="min-width: 0; flex: 1;">
                            <div style="display: flex; align-items: center; gap: 4px; flex-wrap: wrap;">
                                <span style="color: #64748b; font-size: 0.75rem;">Toko / Resto:</span>
                                <strong style="color: #c2410c;">${escapeHtml(o.merchant_name)}</strong>
                            </div>
                            ${o.items_summary ? `<div style="color: #334155; font-size: 0.75rem; margin-top: 3px; line-height: 1.3; background: #ffffff; padding: 4px 8px; border-radius: 6px; border: 1px solid #e2e8f0;"><span style="color: #ea580c; font-weight: 600;"><i class="fa-solid fa-bowl-food" style="font-size: 0.7rem;"></i> Menu:</span> ${escapeHtml(o.items_summary)}</div>` : ''}
                        </div>
                    </div>
                `;
            } else if (o.items_summary) {
                middleHtml = `
                    <div style="display: flex; align-items: flex-start; gap: 8px;">
                        <div style="width: 22px; height: 22px; border-radius: 50%; background: #e0f2fe; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: #0284c7; flex-shrink: 0; margin-top: 2px;">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div style="color: #334155; font-size: 0.75rem; min-width: 0; flex: 1; line-height: 1.35;">
                            <span style="color: #64748b; font-weight: 500;">Rute / Detail:</span> ${escapeHtml(o.items_summary)}
                        </div>
                    </div>
                `;
            }

            html += `
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); transition: transform 0.15s ease, box-shadow 0.15s ease;" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'" onmouseout="this.style.boxShadow='0 1px 3px rgba(0,0,0,0.04)'">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; padding-bottom: 8px; border-bottom: 1px dashed #e2e8f0;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                <span style="font-weight: 800; font-size: 0.95rem; color: #0f172a;">${escapeHtml(o.display_id)}</span>
                                ${srvBadgeHtml}
                                <span style="font-size: 0.75rem; color: #94a3b8;">
                                    <i class="fa-regular fa-clock" style="font-size: 0.7rem;"></i> ${escapeHtml(o.created_at_fmt || '')}
                                </span>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            ${stBadgeHtml}
                            <div style="font-weight: 800; font-size: 0.95rem; color: var(--primary); margin-top: 3px;">
                                ${escapeHtml(o.price_fmt || '')}
                            </div>
                            <button type="button" onclick="openOrderDetail('${o.type}', ${o.raw_id})" style="padding: 3px 8px; background: #0284c7; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.72rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; margin-top: 4px; box-shadow: 0 1px 2px rgba(2, 132, 199, 0.2);" title="Lihat Rincian Lengkap">
                                <i class="fa-solid fa-eye"></i> Detail
                            </button>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr; gap: 6px; font-size: 0.8rem; background: #f8fafc; padding: 8px 10px; border-radius: 8px; border: 1px solid #f1f5f9;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="width: 22px; height: 22px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: #475569; flex-shrink: 0;">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <span style="color: #64748b; font-size: 0.75rem;">Pemesan:</span>
                                <strong style="color: #1e293b; margin-left: 2px;">${escapeHtml(o.user_name || 'Pelanggan')}</strong>
                                ${o.user_phone ? `<span style="color: #94a3b8; font-size: 0.72rem;">(${escapeHtml(o.user_phone)})</span>` : ''}
                            </div>
                        </div>
                        ${middleHtml}
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="width: 22px; height: 22px; border-radius: 50%; background: ${driverIconBg}; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: ${driverIconColor}; flex-shrink: 0;">
                                <i class="fa-solid fa-motorcycle"></i>
                            </div>
                            <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <span style="color: #64748b; font-size: 0.75rem;">Kurir:</span>
                                ${driverHtml}
                            </div>
                        </div>
                        ${cancelReasonHtml}
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // 1-second countdown ticker
    setInterval(function() {
        if (!autoRefreshEnabled) return;
        refreshCountdown--;
        const counterEl = document.getElementById('autoRefreshCounter');
        if (counterEl) counterEl.innerText = refreshCountdown;

        if (refreshCountdown <= 0) {
            refreshCountdown = refreshIntervalSeconds;
            fetchLiveUpdates();
        }
    }, 1000);

    // Initial sound state check on load
    document.addEventListener('DOMContentLoaded', function() {
        updateSoundButtonUI();
    });
</script>

<!-- Floating Live Toast Alert for New Orders -->
<div id="liveOrderToast" style="display: none; position: fixed; top: 24px; right: 24px; z-index: 99999; background: #0f172a; color: #ffffff; padding: 14px 18px; border-radius: 14px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2); border-left: 5px solid #10b981; max-width: 380px; animation: toastSlideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);">
    <div style="display: flex; align-items: flex-start; gap: 12px;">
        <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(16, 185, 129, 0.2); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0;">
            <i class="fa-solid fa-bell fa-shake"></i>
        </div>
        <div style="flex: 1;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <strong style="font-size: 0.9rem; color: #ffffff;">Pesanan Baru Masuk!</strong>
                <button type="button" onclick="closeLiveToast()" style="background: none; border: none; color: #94a3b8; font-size: 1.2rem; cursor: pointer; line-height: 1; padding: 0 4px;" title="Tutup">&times;</button>
            </div>
            <div id="toastOrderDesc" style="font-size: 0.8rem; color: #cbd5e1; margin-top: 3px;">#F-0000 • Layanan • Rp 0</div>
            <div style="display: flex; gap: 8px; margin-top: 10px;">
                <button type="button" id="toastDetailBtn" style="padding: 4px 12px; background: #10b981; color: white; border: none; border-radius: 6px; font-size: 0.75rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                    <i class="fa-solid fa-eye"></i> Lihat Detail
                </button>
                <button type="button" onclick="closeLiveToast()" style="padding: 4px 10px; background: #334155; color: #cbd5e1; border: none; border-radius: 6px; font-size: 0.75rem; font-weight: 600; cursor: pointer;">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes pulseGlow {
        0% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        }

        70% {
            transform: scale(1);
            box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
        }

        100% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
        }
    }

    .pulse-dot {
        animation: pulseGlow 1.8s infinite;
    }

    @keyframes toastSlideIn {
        from {
            transform: translateX(120%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
</style>

<?= $this->include('admin/orders/modal_detail') ?>
<?= $this->endSection() ?>