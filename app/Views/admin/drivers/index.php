<?= $this->extend('admin/layout/main') ?>

<?= $this->section('content') ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h1 class="page-title animate-fade" style="margin-bottom: 0;">Manajemen Pengemudi</h1>
    <button onclick="document.getElementById('addDriverModal').style.display='flex'" class="btn-primary animate-fade" style="animation-delay: 0.1s;">
        <i class="fa-solid fa-plus" style="margin-right: 8px;"></i> Tambah Pengemudi
    </button>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div style="background: rgba(16, 185, 129, 0.1); color: var(--primary); padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.875rem;">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<div class="card-table animate-fade">
    <!-- Toolbar Pencarian & Filter -->
    <div class="table-toolbar">
        <form method="get" action="<?= base_url('admin/drivers') ?>" class="toolbar-form">
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" name="q" value="<?= esc($q ?? '') ?>" placeholder="Cari nama, plat, tipe, no. HP..." class="search-input">
                <?php if (!empty($q)): ?>
                    <a href="<?= base_url('admin/drivers') ?>" class="clear-search" title="Hapus pencarian"><i class="fa-solid fa-xmark"></i></a>
                <?php endif; ?>
            </div>

            <div class="filter-box">
                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Aktif</option>
                    <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
                    <option value="suspended" <?= ($status ?? '') === 'suspended' ? 'selected' : '' ?>>Disuspend</option>
                </select>
            </div>

            <div class="filter-box">
                <select name="vehicle_type" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Kendaraan</option>
                    <option value="motor" <?= ($vehicle_type ?? '') === 'motor' ? 'selected' : '' ?>>Motor (MaiRide)</option>
                    <option value="mobil" <?= ($vehicle_type ?? '') === 'mobil' ? 'selected' : '' ?>>Mobil (MaiCar)</option>
                </select>
            </div>

            <button type="submit" class="btn-search">
                <i class="fa-solid fa-filter"></i> Filter
            </button>

            <?php if (!empty($q) || !empty($status) || !empty($vehicle_type)): ?>
                <a href="<?= base_url('admin/drivers') ?>" class="btn-reset" title="Reset filter">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </a>
            <?php endif; ?>
        </form>

        <div class="toolbar-stats">
            Total: <b><?= number_format($totalCount ?? 0) ?></b> Pengemudi
        </div>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Pengemudi</th>
                    <th>No. HP</th>
                    <th>Kendaraan</th>
                    <th>Level & Poin</th>
                    <th>Saldo & Koin</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($drivers)): ?>
                    <?php foreach ($drivers as $driver): ?>
                        <tr>
                            <td>#DRV-<?= str_pad($driver['id'], 4, '0', STR_PAD_LEFT) ?></td>
                            <td style="display: flex; align-items: center; gap: 12px;">
                                <?php if (!empty($driver['photo'])): ?>
                                    <img src="<?= base_url($driver['photo']) ?>" alt="Foto" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary);">
                                <?php else: ?>
                                    <div style="width: 40px; height: 40px; border-radius: 50%; background: #e5e7eb; display: flex; align-items: center; justify-content: center; color: #9ca3af;">
                                        <i class="fa-solid fa-user"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <span style="display: block; font-weight: 500;"><?= esc($driver['name']) ?></span>
                                    <?php
                                    $isOnline = false;
                                    if ($driver['is_online'] == 1 && $driver['status'] == 'active' && !empty($driver['last_heartbeat_at'])) {
                                        $heartbeatTime = strtotime($driver['last_heartbeat_at']);
                                        if (time() - $heartbeatTime <= 300) {
                                            $isOnline = true;
                                        }
                                    }
                                    ?>
                                    <?php if ($isOnline): ?>
                                        <span style="display: inline-block; padding: 2px 6px; background: #dcfce7; color: #166534; font-size: 0.7rem; border-radius: 4px; font-weight: 600; margin-top: 4px;"><i class="fa-solid fa-circle" style="font-size: 0.5rem; margin-right: 3px;"></i> Online</span>
                                    <?php else: ?>
                                        <span style="display: inline-block; padding: 2px 6px; background: #f3f4f6; color: #6b7280; font-size: 0.7rem; border-radius: 4px; font-weight: 600; margin-top: 4px;"><i class="fa-solid fa-circle" style="font-size: 0.5rem; margin-right: 3px;"></i> Offline</span>
                                    <?php endif; ?>

                                    <?php if (!empty($driver['qris_image'])): ?>
                                        <br>
                                        <a href="<?= base_url($driver['qris_image']) ?>" target="_blank" style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 6px; background: #ccfbf1; color: #0f766e; font-size: 0.7rem; border-radius: 4px; font-weight: 600; text-decoration: none; margin-top: 4px;">
                                            <i class="fa-solid fa-qrcode"></i> <?= esc($driver['qris_merchant_name'] ?: 'Lihat QRIS') ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><?= esc($driver['phone']) ?></td>
                            <td>
                                <div style="font-size: 0.875rem;">
                                    <span style="display: inline-block; background: #0f172a; color: #fff; font-family: monospace; font-weight: bold; font-size: 0.8rem; padding: 2px 7px; border-radius: 4px; letter-spacing: 0.5px; margin-bottom: 3px;">
                                        <?= esc(strtoupper($driver['vehicle_plate'] ?: '-')) ?>
                                    </span><br>
                                    <strong style="color: var(--text-main); font-size: 0.85rem;"><?= esc($driver['vehicle_brand'] ?: ($driver['vehicle_type'] == 'mobil' ? 'Toyota Avanza' : 'Honda Beat')) ?></strong><br>
                                    <span style="color: var(--text-muted); font-size: 0.8rem;">
                                        <i class="fa-solid <?= $driver['vehicle_type'] == 'mobil' ? 'fa-car' : 'fa-motorcycle' ?>"></i> 
                                        <?= ucfirst($driver['vehicle_type']) ?> &bull; <?= (int)(!empty($driver['vehicle_capacity']) ? $driver['vehicle_capacity'] : ($driver['vehicle_type'] == 'mobil' ? 4 : 1)) ?> Kursi
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 0.875rem;">
                                    <?php
                                    $levelColors = [
                                        'legend' => 'linear-gradient(135deg, #FFD700, #FDB931)', // Emas
                                        'master' => 'linear-gradient(135deg, #9ca3af, #d1d5db)', // Perak
                                        'senior' => 'linear-gradient(135deg, #cd7f32, #b87333)', // Perunggu
                                        'junior' => '#3b82f6' // Biru
                                    ];
                                    $lvl = strtolower($driver['driver_level'] ?? 'junior');
                                    $bg = $levelColors[$lvl] ?? '#3b82f6';
                                    ?>
                                    <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; color: white; font-weight: bold; font-size: 0.75rem; background: <?= $bg ?>; text-transform: uppercase; margin-bottom: 4px;">
                                        <?= ucfirst($driver['driver_level'] ?? 'Junior') ?>
                                    </span><br>
                                    <span style="color: var(--text-muted);">
                                        <i class="fa-solid fa-star" style="color: #fbbf24;"></i> <?= number_format($driver['driver_points'] ?? 0) ?> Pts
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div style="margin-bottom: 2px;">
                                    <strong style="color: #047857; font-size: 0.95rem;">Rp <?= number_format($driver['balance'] ?? 0, 0, ',', '.') ?></strong>
                                </div>
                                <div style="color: #f59e0b; font-size: 0.825rem; margin-bottom: 6px;">
                                    <i class="fa-solid fa-coins"></i> <?= number_format($driver['coins'] ?? 0) ?> Koin
                                </div>
                                <button type="button" onclick="openManageBalanceModal(<?= htmlspecialchars(json_encode($driver), ENT_QUOTES, 'UTF-8') ?>)" style="padding: 4px 8px; background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; border-radius: 6px; cursor: pointer; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s;">
                                    <i class="fa-solid fa-wallet"></i> Kelola Saldo
                                </button>
                            </td>
                            <td>
                                <?php if ($driver['status'] == 'active'): ?>
                                    <span class="badge badge-success">Aktif</span>
                                <?php elseif ($driver['status'] == 'suspended'): ?>
                                    <span class="badge badge-danger">Disuspend</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Tidak Aktif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 8px; min-width: 200px;">
                                    <!-- Tombol Edit Kendaraan (Admin Only) -->
                                    <button type="button" onclick="openEditVehicleModal(<?= htmlspecialchars(json_encode($driver), ENT_QUOTES, 'UTF-8') ?>)" style="width: 100%; padding: 7px; background: #0284c7; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.85rem; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 6px; transition: background 0.2s;">
                                        <i class="fa-solid fa-car-side"></i> Edit Kendaraan
                                    </button>

                                    <!-- Tombol Kelola Saldo MaiPay (Tambah / Kurang) -->
                                    <button type="button" onclick="openManageBalanceModal(<?= htmlspecialchars(json_encode($driver), ENT_QUOTES, 'UTF-8') ?>)" style="width: 100%; padding: 7px; background: #059669; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.85rem; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 6px; transition: background 0.2s;">
                                        <i class="fa-solid fa-money-bill-transfer"></i> Kelola Saldo MaiPay
                                    </button>

                                    <!-- Form Ubah Status -->
                                    <form action="<?= base_url('admin/drivers/changeStatus/' . $driver['id']) ?>" method="post" style="display: flex; gap: 8px; width: 100%;">
                                        <select name="status" style="flex: 1; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 0.875rem; outline: none; min-width: 0;">
                                            <option value="active" <?= $driver['status'] == 'active' ? 'selected' : '' ?>>Aktif</option>
                                            <option value="inactive" <?= $driver['status'] == 'inactive' ? 'selected' : '' ?>>Tidak Aktif</option>
                                            <option value="suspended" <?= $driver['status'] == 'suspended' ? 'selected' : '' ?>>Suspend</option>
                                        </select>
                                        <button type="submit" style="width: 80px; padding: 6px; background: var(--primary); color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.875rem; transition: background 0.2s; white-space: nowrap;">
                                            Simpan
                                        </button>
                                    </form>

                                    <!-- Form Reset Password -->
                                    <form action="<?= base_url('admin/drivers/resetPassword/' . $driver['id']) ?>" method="post" onsubmit="return confirm('Yakin ingin mereset password driver ini?');" style="display: flex; gap: 8px; width: 100%;">
                                        <input type="text" name="new_password" placeholder="Pass Baru" required style="flex: 1; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 0.875rem; outline: none; min-width: 0;">
                                        <button type="submit" style="width: 80px; padding: 6px; background: #f59e0b; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.875rem; transition: background 0.2s; white-space: nowrap;">
                                            Reset
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 30px;">
                            <i class="fa-solid fa-magnifying-glass" style="font-size: 1.5rem; opacity: 0.3; margin-bottom: 8px; display: block;"></i>
                            Tidak ada data pengemudi yang cocok dengan kriteria pencarian.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
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
            <div class="pagination-nav-wrapper">
                <?= $pager->links('default', 'admin_pager') ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Edit Kendaraan Driver (Admin Exclusive) -->
<div id="editVehicleModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: var(--bg-card); padding: 32px; border-radius: 16px; width: 90%; max-width: 500px; box-shadow: var(--shadow-lg); border: 1px solid var(--border-color); position: relative;" class="animate-fade">
        <button type="button" onclick="document.getElementById('editVehicleModal').style.display='none'" style="position: absolute; top: 16px; right: 16px; background: none; border: none; font-size: 1.5rem; color: var(--text-muted); cursor: pointer;">&times;</button>
        <h2 style="margin-top: 0; margin-bottom: 6px; font-size: 1.25rem;">Edit Kendaraan Pengemudi</h2>
        <p id="editVehicleDriverName" style="margin-top: 0; margin-bottom: 20px; color: var(--text-muted); font-size: 0.875rem; font-weight: 500;"></p>

        <form id="editVehicleForm" action="" method="post">
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 600;">Jenis Kendaraan</label>
                <select name="vehicle_type" id="ev_vehicle_type" required onchange="onEditVehicleTypeChange(this.value)" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-body); color: var(--text-color); outline: none;">
                    <option value="motor">Motor (MaiRide)</option>
                    <option value="mobil">Mobil (MaiCar)</option>
                </select>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 600;">Plat Nomor Kendaraan</label>
                <input type="text" name="vehicle_plate" id="ev_vehicle_plate" required placeholder="Contoh: DC 2488 FC" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-body); color: var(--text-color); outline: none; text-transform: uppercase;">
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 600;">Merk & Model Kendaraan</label>
                <input type="text" name="vehicle_brand" id="ev_vehicle_brand" required placeholder="Contoh: Toyota Avanza Silver" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-body); color: var(--text-color); outline: none;">
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 600;">Kapasitas Kursi Penumpang</label>
                <input type="number" name="vehicle_capacity" id="ev_vehicle_capacity" required min="1" max="10" placeholder="Contoh: 4" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-body); color: var(--text-color); outline: none;">
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="document.getElementById('editVehicleModal').style.display='none'" style="padding: 12px 24px; border-radius: 8px; background: var(--bg-body); color: var(--text-color); border: 1px solid var(--border-color); cursor: pointer;">Batal</button>
                <button type="submit" class="btn-primary" style="padding: 12px 24px; border-radius: 8px; border: none; cursor: pointer;">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Pengemudi -->
<div id="addDriverModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: var(--bg-card); padding: 32px; border-radius: 16px; width: 90%; max-width: 500px; box-shadow: var(--shadow-lg); border: 1px solid var(--border-color); position: relative;" class="animate-fade">
        <button type="button" onclick="document.getElementById('addDriverModal').style.display='none'" style="position: absolute; top: 16px; right: 16px; background: none; border: none; font-size: 1.5rem; color: var(--text-muted); cursor: pointer;">&times;</button>
        <h2 style="margin-top: 0; margin-bottom: 24px; font-size: 1.25rem;">Tambah Pengemudi Baru</h2>

        <form action="<?= base_url('admin/drivers/store') ?>" method="post">
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem;">Nama Lengkap</label>
                <input type="text" name="name" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-body); color: var(--text-color); outline: none;">
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem;">No. Handphone (Awali dengan 08)</label>
                <input type="number" name="phone" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-body); color: var(--text-color); outline: none;">
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem;">Password</label>
                <input type="text" name="password" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-body); color: var(--text-color); outline: none;">
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem;">Tipe Kendaraan</label>
                <select name="vehicle_type" id="add_vehicle_type" required onchange="onAddVehicleTypeChange(this.value)" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-body); color: var(--text-color); outline: none;">
                    <option value="motor">Motor (MaiRide)</option>
                    <option value="mobil">Mobil (MaiCar)</option>
                </select>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem;">Plat Nomor Kendaraan</label>
                <input type="text" name="vehicle_plate" required placeholder="Contoh: DC 2488 FC" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-body); color: var(--text-color); outline: none; text-transform: uppercase;">
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem;">Merk & Model Kendaraan</label>
                <input type="text" name="vehicle_brand" id="add_vehicle_brand" placeholder="Contoh: Honda Beat / Toyota Avanza" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-body); color: var(--text-color); outline: none;">
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem;">Kapasitas Penumpang (Kursi)</label>
                <input type="number" name="vehicle_capacity" id="add_vehicle_capacity" value="1" min="1" max="10" placeholder="Contoh: 1 (Motor) atau 4 (Mobil)" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-body); color: var(--text-color); outline: none;">
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="document.getElementById('addDriverModal').style.display='none'" style="padding: 12px 24px; border-radius: 8px; background: var(--bg-body); color: var(--text-color); border: 1px solid var(--border-color); cursor: pointer;">Batal</button>
                <button type="submit" class="btn-primary" style="padding: 12px 24px; border-radius: 8px; border: none; cursor: pointer;">Simpan Pengemudi</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Kelola Saldo Driver (Tambah & Kurang Saldo MaiPay) -->
<div id="manageBalanceModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.55); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: var(--bg-card); padding: 28px; border-radius: 16px; width: 92%; max-width: 520px; box-shadow: var(--shadow-lg); border: 1px solid var(--border-color); position: relative; max-height: 90vh; overflow-y: auto;" class="animate-fade">
        <button type="button" onclick="closeManageBalanceModal()" style="position: absolute; top: 16px; right: 16px; background: none; border: none; font-size: 1.5rem; color: var(--text-muted); cursor: pointer; line-height: 1;">&times;</button>
        
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
            <div style="width: 38px; height: 38px; border-radius: 10px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 1.15rem;">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div>
                <h2 style="margin: 0; font-size: 1.25rem; font-weight: 700;">Kelola Saldo MaiPay Pengemudi</h2>
                <p id="mb_driver_info" style="margin: 2px 0 0; color: var(--text-muted); font-size: 0.85rem; font-weight: 500;"></p>
            </div>
        </div>

        <!-- Kartu Ringkasan Saldo Saat Ini & Estimasi Saldo Baru -->
        <div style="background: var(--bg-body); border: 1px solid var(--border-color); border-radius: 12px; padding: 14px 16px; margin: 18px 0; display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div>
                <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 4px;">Saldo Saat Ini</span>
                <span id="mb_current_balance_text" style="font-size: 1.15rem; font-weight: 800; color: var(--text-color);">Rp 0</span>
            </div>
            <div style="border-left: 1px solid var(--border-color); padding-left: 14px;">
                <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 4px;">Estimasi Saldo Baru</span>
                <span id="mb_preview_balance_text" style="font-size: 1.15rem; font-weight: 800; color: #059669;">Rp 0</span>
            </div>
        </div>

        <form id="manageBalanceForm" action="" method="post" onsubmit="return validateManageBalanceForm();">
            <!-- Pilihan Tindakan: Tambah (+) vs Kurang (-) -->
            <div style="margin-bottom: 18px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 600;">Jenis Penyesuaian Saldo</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <label id="mb_btn_add" onclick="setBalanceAction('add')" style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 12px; border-radius: 10px; border: 2px solid #10b981; background: #ecfdf5; color: #047857; font-weight: 700; cursor: pointer; transition: all 0.2s;">
                        <input type="radio" name="action" id="mb_radio_add" value="add" checked style="display: none;">
                        <i class="fa-solid fa-circle-plus" style="font-size: 1.1rem;"></i> Tambah Saldo
                    </label>
                    <label id="mb_btn_deduct" onclick="setBalanceAction('deduct')" style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 12px; border-radius: 10px; border: 2px solid var(--border-color); background: var(--bg-body); color: var(--text-muted); font-weight: 700; cursor: pointer; transition: all 0.2s;">
                        <input type="radio" name="action" id="mb_radio_deduct" value="deduct" style="display: none;">
                        <i class="fa-solid fa-circle-minus" style="font-size: 1.1rem;"></i> Kurangi Saldo
                    </label>
                </div>
            </div>

            <!-- Input Nominal Saldo -->
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 600;">
                    Nominal Penyesuaian (Rp) <span style="color: #ef4444;">*</span>
                </label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-weight: 700; color: var(--text-muted); font-size: 1rem;">Rp</span>
                    <input type="number" name="amount" id="mb_amount_input" required min="1" placeholder="Contoh: 50000" oninput="updateBalancePreview()" style="width: 100%; padding: 12px 14px 12px 44px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-body); color: var(--text-color); font-size: 1.05rem; font-weight: 700; outline: none;">
                </div>
                <!-- Quick Preset Chips -->
                <div style="display: flex; gap: 6px; margin-top: 8px; flex-wrap: wrap;">
                    <button type="button" onclick="setPresetAmount(10000)" style="padding: 5px 10px; border-radius: 6px; border: 1px solid var(--border-color); background: var(--bg-body); color: var(--text-color); font-size: 0.75rem; font-weight: 600; cursor: pointer;">+10.000</button>
                    <button type="button" onclick="setPresetAmount(20000)" style="padding: 5px 10px; border-radius: 6px; border: 1px solid var(--border-color); background: var(--bg-body); color: var(--text-color); font-size: 0.75rem; font-weight: 600; cursor: pointer;">+20.000</button>
                    <button type="button" onclick="setPresetAmount(50000)" style="padding: 5px 10px; border-radius: 6px; border: 1px solid var(--border-color); background: var(--bg-body); color: var(--text-color); font-size: 0.75rem; font-weight: 600; cursor: pointer;">+50.000</button>
                    <button type="button" onclick="setPresetAmount(100000)" style="padding: 5px 10px; border-radius: 6px; border: 1px solid var(--border-color); background: var(--bg-body); color: var(--text-color); font-size: 0.75rem; font-weight: 600; cursor: pointer;">+100.000</button>
                    <button type="button" onclick="setPresetAmount(200000)" style="padding: 5px 10px; border-radius: 6px; border: 1px solid var(--border-color); background: var(--bg-body); color: var(--text-color); font-size: 0.75rem; font-weight: 600; cursor: pointer;">+200.000</button>
                    <button type="button" onclick="setPresetAmount(500000)" style="padding: 5px 10px; border-radius: 6px; border: 1px solid var(--border-color); background: var(--bg-body); color: var(--text-color); font-size: 0.75rem; font-weight: 600; cursor: pointer;">+500.000</button>
                </div>
            </div>

            <!-- Keterangan / Alasan -->
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 600;">Keterangan / Alasan (Opsional)</label>
                <input type="text" name="note" id="mb_note_input" placeholder="Contoh: Top Up Manual / Insentif / Koreksi Selisih" style="width: 100%; padding: 11px 14px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-body); color: var(--text-color); font-size: 0.875rem; outline: none;">
            </div>

            <!-- Toggle Notifikasi Push FCM -->
            <div style="margin-bottom: 24px; background: var(--bg-body); padding: 12px 14px; border-radius: 8px; border: 1px solid var(--border-color);">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin: 0;">
                    <input type="checkbox" name="send_notif" value="1" checked style="width: 18px; height: 18px; accent-color: #10b981; cursor: pointer;">
                    <div>
                        <span style="font-size: 0.875rem; font-weight: 600; display: block; color: var(--text-color);">Kirim Notifikasi Realtime ke HP Pengemudi</span>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Pemberitahuan perubahan saldo akan langsung terkirim via FCM ke aplikasi driver</span>
                    </div>
                </label>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="closeManageBalanceModal()" style="padding: 12px 20px; border-radius: 8px; background: var(--bg-body); color: var(--text-color); border: 1px solid var(--border-color); cursor: pointer; font-weight: 600;">Batal</button>
                <button type="submit" id="mb_submit_btn" style="padding: 12px 24px; border-radius: 8px; border: none; cursor: pointer; font-weight: 700; background: #059669; color: white; display: flex; align-items: center; gap: 8px; font-size: 0.95rem; box-shadow: 0 2px 6px rgba(5, 150, 105, 0.3);">
                    <i class="fa-solid fa-check"></i> <span id="mb_submit_btn_text">Tambah Saldo MaiPay</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
var currentManagingDriver = null;
var currentBalanceAction = 'add';

function formatRupiah(num) {
    return 'Rp ' + Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function openManageBalanceModal(driver) {
    currentManagingDriver = driver;
    currentBalanceAction = 'add';

    document.getElementById('mb_driver_info').textContent = (driver.name || '') + ' (' + (driver.phone || '') + ') • Plat: ' + (driver.vehicle_plate || '-').toUpperCase();
    document.getElementById('manageBalanceForm').action = '<?= base_url('admin/drivers/adjustBalance') ?>/' + driver.id;

    var bal = parseFloat(driver.balance) || 0;
    document.getElementById('mb_current_balance_text').textContent = formatRupiah(bal);
    document.getElementById('mb_amount_input').value = '';
    document.getElementById('mb_note_input').value = '';

    setBalanceAction('add');
    updateBalancePreview();

    document.getElementById('manageBalanceModal').style.display = 'flex';
    setTimeout(function() {
        document.getElementById('mb_amount_input').focus();
    }, 100);
}

function closeManageBalanceModal() {
    document.getElementById('manageBalanceModal').style.display = 'none';
}

function setBalanceAction(action) {
    currentBalanceAction = action;
    var radioAdd = document.getElementById('mb_radio_add');
    var radioDeduct = document.getElementById('mb_radio_deduct');
    var btnAdd = document.getElementById('mb_btn_add');
    var btnDeduct = document.getElementById('mb_btn_deduct');
    var submitBtn = document.getElementById('mb_submit_btn');
    var submitText = document.getElementById('mb_submit_btn_text');

    if (action === 'deduct') {
        radioDeduct.checked = true;
        radioAdd.checked = false;

        btnDeduct.style.borderColor = '#ef4444';
        btnDeduct.style.background = '#fef2f2';
        btnDeduct.style.color = '#b91c1c';

        btnAdd.style.borderColor = 'var(--border-color)';
        btnAdd.style.background = 'var(--bg-body)';
        btnAdd.style.color = 'var(--text-muted)';

        submitBtn.style.background = '#dc2626';
        submitBtn.style.boxShadow = '0 2px 6px rgba(220, 38, 38, 0.3)';
        submitText.textContent = 'Kurangi Saldo MaiPay';
    } else {
        radioAdd.checked = true;
        radioDeduct.checked = false;

        btnAdd.style.borderColor = '#10b981';
        btnAdd.style.background = '#ecfdf5';
        btnAdd.style.color = '#047857';

        btnDeduct.style.borderColor = 'var(--border-color)';
        btnDeduct.style.background = 'var(--bg-body)';
        btnDeduct.style.color = 'var(--text-muted)';

        submitBtn.style.background = '#059669';
        submitBtn.style.boxShadow = '0 2px 6px rgba(5, 150, 105, 0.3)';
        submitText.textContent = 'Tambah Saldo MaiPay';
    }

    updateBalancePreview();
}

function setPresetAmount(val) {
    document.getElementById('mb_amount_input').value = val;
    updateBalancePreview();
}

function updateBalancePreview() {
    if (!currentManagingDriver) return;
    var currentBal = parseFloat(currentManagingDriver.balance) || 0;
    var amount = parseFloat(document.getElementById('mb_amount_input').value) || 0;
    var previewEl = document.getElementById('mb_preview_balance_text');

    var newBal = currentBal;
    if (currentBalanceAction === 'deduct') {
        newBal = currentBal - amount;
        if (newBal < 0) {
            previewEl.textContent = formatRupiah(newBal) + ' (Saldo Minus!)';
            previewEl.style.color = '#dc2626';
        } else {
            previewEl.textContent = formatRupiah(newBal);
            previewEl.style.color = '#d97706';
        }
    } else {
        newBal = currentBal + amount;
        previewEl.textContent = formatRupiah(newBal);
        previewEl.style.color = '#059669';
    }
}

function validateManageBalanceForm() {
    if (!currentManagingDriver) return false;
    var currentBal = parseFloat(currentManagingDriver.balance) || 0;
    var amount = parseFloat(document.getElementById('mb_amount_input').value) || 0;

    if (amount <= 0) {
        alert('Mohon masukkan nominal saldo yang valid (lebih dari Rp 0)!');
        document.getElementById('mb_amount_input').focus();
        return false;
    }

    if (currentBalanceAction === 'deduct' && amount > currentBal) {
        var proceed = confirm('Peringatan: Nominal pemotongan (' + formatRupiah(amount) + ') melebihi saldo driver saat ini (' + formatRupiah(currentBal) + '). Pemotongan ini akan gagal jika saldo tidak mencukupi.\n\nApakah Anda tetap ingin melanjutkan?');
        return proceed;
    }

    var actionName = (currentBalanceAction === 'deduct') ? 'MENGURANGI' : 'MENAMBAH';
    return confirm('Apakah Anda yakin ingin ' + actionName + ' saldo pengemudi ' + (currentManagingDriver.name || '') + ' sebesar ' + formatRupiah(amount) + '?');
}

function openEditVehicleModal(driver) {
    document.getElementById('editVehicleDriverName').textContent = 'Pengemudi: ' + (driver.name || '') + ' (' + (driver.phone || '') + ')';
    document.getElementById('editVehicleForm').action = '<?= base_url('admin/drivers/updateVehicle') ?>/' + driver.id;
    
    document.getElementById('ev_vehicle_type').value = driver.vehicle_type || 'motor';
    document.getElementById('ev_vehicle_plate').value = (driver.vehicle_plate || '').toUpperCase();
    
    var isCar = (driver.vehicle_type === 'mobil');
    document.getElementById('ev_vehicle_brand').value = driver.vehicle_brand || (isCar ? 'Toyota Avanza' : 'Honda Beat');
    
    var cap = parseInt(driver.vehicle_capacity, 10);
    if (isNaN(cap) || cap <= 0) {
        cap = (isCar ? 4 : 1);
    }
    document.getElementById('ev_vehicle_capacity').value = cap;
    
    document.getElementById('editVehicleModal').style.display = 'flex';
}

function onEditVehicleTypeChange(type) {
    var cap = document.getElementById('ev_vehicle_capacity');
    var brand = document.getElementById('ev_vehicle_brand');
    if (type === 'mobil') {
        if (cap.value == '1' || cap.value == '') cap.value = '4';
        if (brand.value == 'Honda Beat' || brand.value == '') brand.value = 'Toyota Avanza';
    } else {
        if (cap.value == '4' || cap.value == '') cap.value = '1';
        if (brand.value == 'Toyota Avanza' || brand.value == '') brand.value = 'Honda Beat';
    }
}

function onAddVehicleTypeChange(type) {
    var cap = document.getElementById('add_vehicle_capacity');
    var brand = document.getElementById('add_vehicle_brand');
    if (type === 'mobil') {
        if (cap.value == '1' || cap.value == '') cap.value = '4';
        if (brand.value == 'Honda Beat' || brand.value == '') brand.value = 'Toyota Avanza';
    } else {
        if (cap.value == '4' || cap.value == '') cap.value = '1';
        if (brand.value == 'Toyota Avanza' || brand.value == '') brand.value = 'Honda Beat';
    }
}
</script>

<?= $this->endSection() ?>