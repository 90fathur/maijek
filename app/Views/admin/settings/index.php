<?= $this->extend('admin/layout/main') ?>

<?= $this->section('content') ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h1 class="page-title animate-fade" style="margin-bottom: 0;">Pengaturan Sistem</h1>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div style="background: rgba(16, 185, 129, 0.1); color: var(--primary); padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.875rem;">
        <?= session()->getFlashdata('success') ?>
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
</style>

<form action="<?= base_url('admin/settings/update') ?>" method="post" class="animate-fade">
    <div class="stats-grid">
        <!-- Tarif Panel Mai-Ride -->
        <div class="card-table" style="padding: 28px;">
            <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 20px;"><i class="fa-solid fa-coins" style="color: var(--primary); margin-right: 8px;"></i> Tarif & Biaya Mai-Ride</h2>

            <!-- Mai-Ride Motor -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 600; color: var(--primary);"><i class="fa-solid fa-motorcycle" style="margin-right: 4px;"></i> Mai-Ride (Motor)</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 8px;">
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-size: 0.75rem; color: var(--text-muted);">Tarif Dasar (Rp)</label>
                        <input type="number" name="base_fare" value="<?= esc($base_fare['setting_value']) ?>" style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none; font-size: 0.875rem;" placeholder="10000" required>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-size: 0.75rem; color: var(--text-muted);">Batas Jarak Dasar</label>
                        <div style="display: flex; align-items: center; background: var(--bg-body); border: 1px solid var(--border-color); border-radius: 8px; padding-right: 10px;">
                            <input type="number" name="base_fare_distance" value="<?= esc($base_fare_distance['setting_value'] ?? 3) ?>" style="width: 100%; padding: 10px 12px; border: none; background: transparent; outline:none; font-size: 0.875rem;" placeholder="3" required>
                            <span style="color: var(--text-muted); font-size: 0.75rem; font-weight: 600;">KM</span>
                        </div>
                    </div>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 4px; font-size: 0.75rem; color: var(--text-muted);">Tarif per KM Berikutnya (Rp/KM)</label>
                    <div style="display: flex; align-items: center; background: var(--bg-body); border: 1px solid var(--border-color); border-radius: 8px; padding-right: 10px;">
                        <input type="number" name="per_km_fare" value="<?= esc($per_km_fare['setting_value']) ?>" style="width: 100%; padding: 10px 12px; border: none; background: transparent; outline:none; font-size: 0.875rem;" placeholder="3000" required>
                        <span style="color: var(--text-muted); font-size: 0.75rem; font-weight: 600;">/ KM</span>
                    </div>
                </div>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 20px 0;">

            <!-- Mai-Ride Mobil -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 600; color: var(--primary);"><i class="fa-solid fa-car" style="margin-right: 4px;"></i> Mai-Ride (Mobil)</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 8px;">
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-size: 0.75rem; color: var(--text-muted);">Tarif Dasar (Rp)</label>
                        <input type="number" name="ride_mobil_base_fare" value="<?= esc($ride_mobil_base_fare['setting_value'] ?? 15000) ?>" style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none; font-size: 0.875rem;" placeholder="15000" required>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-size: 0.75rem; color: var(--text-muted);">Batas Jarak Dasar</label>
                        <div style="display: flex; align-items: center; background: var(--bg-body); border: 1px solid var(--border-color); border-radius: 8px; padding-right: 10px;">
                            <input type="number" name="ride_mobil_base_fare_distance" value="<?= esc($ride_mobil_base_fare_distance['setting_value'] ?? 3) ?>" style="width: 100%; padding: 10px 12px; border: none; background: transparent; outline:none; font-size: 0.875rem;" placeholder="3" required>
                            <span style="color: var(--text-muted); font-size: 0.75rem; font-weight: 600;">KM</span>
                        </div>
                    </div>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 4px; font-size: 0.75rem; color: var(--text-muted);">Tarif per KM Berikutnya (Rp/KM)</label>
                    <div style="display: flex; align-items: center; background: var(--bg-body); border: 1px solid var(--border-color); border-radius: 8px; padding-right: 10px;">
                        <input type="number" name="ride_mobil_per_km_fare" value="<?= esc($ride_mobil_per_km_fare['setting_value'] ?? 4000) ?>" style="width: 100%; padding: 10px 12px; border: none; background: transparent; outline:none; font-size: 0.875rem;" placeholder="4000" required>
                        <span style="color: var(--text-muted); font-size: 0.75rem; font-weight: 600;">/ KM</span>
                    </div>
                </div>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 20px 0;">

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 500;">Biaya Aplikasi (Platform Fee)</label>
                <div style="display: flex; gap: 10px;">
                    <select name="platform_fee_type" style="width: 45%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none; font-size: 0.875rem;">
                        <option value="fixed" <?= ($platform_fee_type['setting_value'] ?? 'fixed') == 'fixed' ? 'selected' : '' ?>>Rupiah (Rp)</option>
                        <option value="percent" <?= ($platform_fee_type['setting_value'] ?? 'fixed') == 'percent' ? 'selected' : '' ?>>Persentase (%)</option>
                    </select>
                    <input type="number" name="platform_fee" value="<?= esc($platform_fee['setting_value']) ?>" style="flex: 1; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none; font-size: 0.875rem;" placeholder="Misal: 1000 atau 10" required>
                </div>
                <small style="color: var(--text-muted); display:block; margin-top: 6px;">Potongan biaya aplikasi untuk setiap pesanan ride.</small>
            </div>
        </div>

        <!-- Tarif Mai-Food Panel -->
        <div class="card-table" style="padding: 28px;">
            <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 20px;"><i class="fa-solid fa-burger" style="color: var(--primary); margin-right: 8px;"></i> Tarif & Biaya Mai-Food</h2>

            <!-- Mai-Food Motor -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 600; color: var(--primary);"><i class="fa-solid fa-motorcycle" style="margin-right: 4px;"></i> Tarif Mai-Food (Kurir Motor - Reguler)</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 8px;">
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-size: 0.75rem; color: var(--text-muted);">Tarif Dasar (Rp)</label>
                        <input type="number" name="food_base_fare" value="<?= esc($food_base_fare['setting_value'] ?? 5000) ?>" style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none; font-size: 0.875rem;" placeholder="5000" required>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-size: 0.75rem; color: var(--text-muted);">Batas Jarak Dasar</label>
                        <div style="display: flex; align-items: center; background: var(--bg-body); border: 1px solid var(--border-color); border-radius: 8px; padding-right: 10px;">
                            <input type="number" name="food_base_fare_distance" value="<?= esc($food_base_fare_distance['setting_value'] ?? 3) ?>" style="width: 100%; padding: 10px 12px; border: none; background: transparent; outline:none; font-size: 0.875rem;" placeholder="3" required>
                            <span style="color: var(--text-muted); font-size: 0.75rem; font-weight: 600;">KM</span>
                        </div>
                    </div>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 4px; font-size: 0.75rem; color: var(--text-muted);">Tarif per KM Berikutnya (Rp/KM)</label>
                    <div style="display: flex; align-items: center; background: var(--bg-body); border: 1px solid var(--border-color); border-radius: 8px; padding-right: 10px;">
                        <input type="number" name="food_per_km_fare" value="<?= esc($food_per_km_fare['setting_value'] ?? 2000) ?>" style="width: 100%; padding: 10px 12px; border: none; background: transparent; outline:none; font-size: 0.875rem;" placeholder="2000" required>
                        <span style="color: var(--text-muted); font-size: 0.75rem; font-weight: 600;">/ KM</span>
                    </div>
                </div>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 20px 0;">

            <!-- Mai-Food Mobil -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 600; color: var(--primary);"><i class="fa-solid fa-car" style="margin-right: 4px;"></i> Tarif Mai-Food (Kurir Mobil - Porsi Besar / Katering)</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 8px;">
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-size: 0.75rem; color: var(--text-muted);">Tarif Dasar (Rp)</label>
                        <input type="number" name="food_car_base_fare" value="<?= esc($food_car_base_fare['setting_value'] ?? 12000) ?>" style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none; font-size: 0.875rem;" placeholder="12000" required>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-size: 0.75rem; color: var(--text-muted);">Batas Jarak Dasar</label>
                        <div style="display: flex; align-items: center; background: var(--bg-body); border: 1px solid var(--border-color); border-radius: 8px; padding-right: 10px;">
                            <input type="number" name="food_car_base_fare_distance" value="<?= esc($food_car_base_fare_distance['setting_value'] ?? 3) ?>" style="width: 100%; padding: 10px 12px; border: none; background: transparent; outline:none; font-size: 0.875rem;" placeholder="3" required>
                            <span style="color: var(--text-muted); font-size: 0.75rem; font-weight: 600;">KM</span>
                        </div>
                    </div>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 4px; font-size: 0.75rem; color: var(--text-muted);">Tarif per KM Berikutnya (Rp/KM)</label>
                    <div style="display: flex; align-items: center; background: var(--bg-body); border: 1px solid var(--border-color); border-radius: 8px; padding-right: 10px;">
                        <input type="number" name="food_car_per_km_fare" value="<?= esc($food_car_per_km_fare['setting_value'] ?? 4000) ?>" style="width: 100%; padding: 10px 12px; border: none; background: transparent; outline:none; font-size: 0.875rem;" placeholder="4000" required>
                        <span style="color: var(--text-muted); font-size: 0.75rem; font-weight: 600;">/ KM</span>
                    </div>
                </div>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 20px 0;">

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 500;">Biaya Aplikasi (Komisi) Mai-Food</label>
                <div style="display: flex; gap: 10px;">
                    <select name="food_platform_fee_type" style="width: 45%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none; font-size: 0.875rem;">
                        <option value="fixed" <?= ($food_platform_fee_type['setting_value'] ?? 'percent') == 'fixed' ? 'selected' : '' ?>>Rupiah (Rp)</option>
                        <option value="percent" <?= ($food_platform_fee_type['setting_value'] ?? 'percent') == 'percent' ? 'selected' : '' ?>>Persentase (%)</option>
                    </select>
                    <input type="number" name="food_platform_fee" value="<?= esc($food_platform_fee['setting_value'] ?? 15) ?>" style="flex: 1; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none; font-size: 0.875rem;" placeholder="Misal: 15" required>
                </div>
                <small style="color: var(--text-muted); display:block; margin-top: 6px;">Komisi Mai-Food HANYA memotong dari total ongkos kirim (Delivery Fee).</small>
            </div>
        </div>

        <!-- Tarif Mai-Send & Titip Panel -->
        <div class="card-table" style="padding: 28px;">
            <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 20px;"><i class="fa-solid fa-box" style="color: var(--primary); margin-right: 8px;"></i> Tarif Kurir & Jastip</h2>

            <!-- Mai-Send (Motor) -->
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-size: 0.875rem; font-weight: 600; color: var(--primary);"><i class="fa-solid fa-box" style="margin-right: 4px;"></i> Mai-Send (Motor)</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 6px;">
                    <div>
                        <label style="display: block; margin-bottom: 2px; font-size: 0.75rem; color: var(--text-muted);">Dasar (Rp)</label>
                        <input type="number" name="send_motor_base_fare" value="<?= esc($send_motor_base_fare['setting_value'] ?? 8000) ?>" style="width: 100%; padding: 8px 10px; border: 1px solid var(--border-color); border-radius: 6px; outline:none; font-size: 0.875rem;" required>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 2px; font-size: 0.75rem; color: var(--text-muted);">Jarak Dasar</label>
                        <div style="display: flex; align-items: center; background: var(--bg-body); border: 1px solid var(--border-color); border-radius: 6px; padding-right: 8px;">
                            <input type="number" name="send_motor_base_distance" value="<?= esc($send_motor_base_distance['setting_value'] ?? 3) ?>" style="width: 100%; padding: 8px 10px; border: none; background: transparent; outline:none; font-size: 0.875rem;" required>
                            <span style="color: var(--text-muted); font-size: 0.75rem; font-weight: 600;">KM</span>
                        </div>
                    </div>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 2px; font-size: 0.75rem; color: var(--text-muted);">Per KM (Rp)</label>
                    <div style="display: flex; align-items: center; background: var(--bg-body); border: 1px solid var(--border-color); border-radius: 6px; padding-right: 8px;">
                        <input type="number" name="send_motor_per_km" value="<?= esc($send_motor_per_km['setting_value'] ?? 2000) ?>" style="width: 100%; padding: 8px 10px; border: none; background: transparent; outline:none; font-size: 0.875rem;" required>
                        <span style="color: var(--text-muted); font-size: 0.75rem; font-weight: 600;">/ KM</span>
                    </div>
                </div>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 14px 0;">

            <!-- Mai-Send (Mobil) -->
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-size: 0.875rem; font-weight: 600; color: var(--primary);"><i class="fa-solid fa-truck" style="margin-right: 4px;"></i> Mai-Send (Mobil)</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 6px;">
                    <div>
                        <label style="display: block; margin-bottom: 2px; font-size: 0.75rem; color: var(--text-muted);">Dasar (Rp)</label>
                        <input type="number" name="send_mobil_base_fare" value="<?= esc($send_mobil_base_fare['setting_value'] ?? 20000) ?>" style="width: 100%; padding: 8px 10px; border: 1px solid var(--border-color); border-radius: 6px; outline:none; font-size: 0.875rem;" required>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 2px; font-size: 0.75rem; color: var(--text-muted);">Jarak Dasar</label>
                        <div style="display: flex; align-items: center; background: var(--bg-body); border: 1px solid var(--border-color); border-radius: 6px; padding-right: 8px;">
                            <input type="number" name="send_mobil_base_distance" value="<?= esc($send_mobil_base_distance['setting_value'] ?? 3) ?>" style="width: 100%; padding: 8px 10px; border: none; background: transparent; outline:none; font-size: 0.875rem;" required>
                            <span style="color: var(--text-muted); font-size: 0.75rem; font-weight: 600;">KM</span>
                        </div>
                    </div>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 2px; font-size: 0.75rem; color: var(--text-muted);">Per KM (Rp)</label>
                    <div style="display: flex; align-items: center; background: var(--bg-body); border: 1px solid var(--border-color); border-radius: 6px; padding-right: 8px;">
                        <input type="number" name="send_mobil_per_km" value="<?= esc($send_mobil_per_km['setting_value'] ?? 4000) ?>" style="width: 100%; padding: 8px 10px; border: none; background: transparent; outline:none; font-size: 0.875rem;" required>
                        <span style="color: var(--text-muted); font-size: 0.75rem; font-weight: 600;">/ KM</span>
                    </div>
                </div>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 14px 0;">

            <!-- Mai-Titip (Motor) -->
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-size: 0.875rem; font-weight: 600; color: var(--primary);"><i class="fa-solid fa-basket-shopping" style="margin-right: 4px;"></i> Mai-Titip Motor (Jastip)</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 6px;">
                    <div>
                        <label style="display: block; margin-bottom: 2px; font-size: 0.75rem; color: var(--text-muted);">Dasar (Rp)</label>
                        <input type="number" name="titip_motor_base_fare" value="<?= esc($titip_motor_base_fare['setting_value'] ?? 10000) ?>" style="width: 100%; padding: 8px 10px; border: 1px solid var(--border-color); border-radius: 6px; outline:none; font-size: 0.875rem;" required>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 2px; font-size: 0.75rem; color: var(--text-muted);">Jarak Dasar</label>
                        <div style="display: flex; align-items: center; background: var(--bg-body); border: 1px solid var(--border-color); border-radius: 6px; padding-right: 8px;">
                            <input type="number" name="titip_motor_base_distance" value="<?= esc($titip_motor_base_distance['setting_value'] ?? 3) ?>" style="width: 100%; padding: 8px 10px; border: none; background: transparent; outline:none; font-size: 0.875rem;" required>
                            <span style="color: var(--text-muted); font-size: 0.75rem; font-weight: 600;">KM</span>
                        </div>
                    </div>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 2px; font-size: 0.75rem; color: var(--text-muted);">Per KM (Rp)</label>
                    <div style="display: flex; align-items: center; background: var(--bg-body); border: 1px solid var(--border-color); border-radius: 6px; padding-right: 8px;">
                        <input type="number" name="titip_motor_per_km" value="<?= esc($titip_motor_per_km['setting_value'] ?? 2000) ?>" style="width: 100%; padding: 8px 10px; border: none; background: transparent; outline:none; font-size: 0.875rem;" required>
                        <span style="color: var(--text-muted); font-size: 0.75rem; font-weight: 600;">/ KM</span>
                    </div>
                </div>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 14px 0;">

            <!-- Mai-Titip (Mobil) -->
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-size: 0.875rem; font-weight: 600; color: var(--primary);"><i class="fa-solid fa-car-side" style="margin-right: 4px;"></i> Mai-Titip Mobil (Jastip)</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 6px;">
                    <div>
                        <label style="display: block; margin-bottom: 2px; font-size: 0.75rem; color: var(--text-muted);">Dasar (Rp)</label>
                        <input type="number" name="titip_mobil_base_fare" value="<?= esc($titip_mobil_base_fare['setting_value'] ?? 25000) ?>" style="width: 100%; padding: 8px 10px; border: 1px solid var(--border-color); border-radius: 6px; outline:none; font-size: 0.875rem;" required>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 2px; font-size: 0.75rem; color: var(--text-muted);">Jarak Dasar</label>
                        <div style="display: flex; align-items: center; background: var(--bg-body); border: 1px solid var(--border-color); border-radius: 6px; padding-right: 8px;">
                            <input type="number" name="titip_mobil_base_distance" value="<?= esc($titip_mobil_base_distance['setting_value'] ?? 3) ?>" style="width: 100%; padding: 8px 10px; border: none; background: transparent; outline:none; font-size: 0.875rem;" required>
                            <span style="color: var(--text-muted); font-size: 0.75rem; font-weight: 600;">KM</span>
                        </div>
                    </div>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 2px; font-size: 0.75rem; color: var(--text-muted);">Per KM (Rp)</label>
                    <div style="display: flex; align-items: center; background: var(--bg-body); border: 1px solid var(--border-color); border-radius: 6px; padding-right: 8px;">
                        <input type="number" name="titip_mobil_per_km" value="<?= esc($titip_mobil_per_km['setting_value'] ?? 5000) ?>" style="width: 100%; padding: 8px 10px; border: none; background: transparent; outline:none; font-size: 0.875rem;" required>
                        <span style="color: var(--text-muted); font-size: 0.75rem; font-weight: 600;">/ KM</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="stats-grid" style="margin-top: 24px;">
        <div class="card-table" style="padding: 32px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
                <h2 style="font-size: 1.25rem; font-weight: 600; margin: 0;">
                    <i class="fa-solid fa-wallet" style="color: #3B82F6; margin-right: 8px;"></i> Kebijakan Saldo Driver (Tarik / Terima Order)
                </h2>
                <span style="font-size: 0.75rem; background: #EFF6FF; color: #1D4ED8; padding: 4px 10px; border-radius: 9999px; font-weight: 600; border: 1px solid #BFDBFE;">
                    Fitur Dinamis Saldo Driver
                </span>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 600;">Status Fitur Saldo Driver Rp 0</label>
                <select name="driver_zero_balance_enabled" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none; background: var(--bg-card); font-size: 0.875rem;">
                    <option value="1" <?= (($driver_zero_balance_enabled['setting_value'] ?? '0') == '1') ? 'selected' : '' ?>>
                        ✅ AKTIF - Driver Saldo Rp 0 Tetap Bisa Menarik Order (Bebas Saldo)
                    </option>
                    <option value="0" <?= (($driver_zero_balance_enabled['setting_value'] ?? '0') == '0') ? 'selected' : '' ?>>
                        ⛔ NONAKTIF - Driver Wajib Memiliki Saldo Minimal (Kembali ke Awal)
                    </option>
                </select>
                <small style="color: var(--text-muted); display:block; margin-top: 8px;">
                    Jika <b>AKTIF</b>, pengemudi dengan saldo Mai-Pay Rp 0 (atau tanpa saldo) bebas menarik pesanan kapanpun tanpa dicegat saldo. Jika <b>NONAKTIF</b>, driver wajib memiliki saldo minimal sesuai nominal di bawah.
                </small>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 600;">Batas Minimal Saldo Driver (Rp)</label>
                <input type="number" name="driver_min_balance" value="<?= esc($driver_min_balance['setting_value'] ?? 10000) ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none;" placeholder="Misal: 10000" required>
                <small style="color: var(--text-muted); display:block; margin-top: 8px;">
                    Batas saldo minimal yang harus ada di dompet driver jika Fitur Saldo Rp 0 dinonaktifkan (Standar: <b>Rp 10.000</b>). Driver dengan saldo di bawah ini tidak dapat menarik order non-promo.
                </small>
            </div>

            <div style="margin-bottom: 10px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 600;">Pengecualian Promo Cashback (Bebas Saldo Rp 0 Saat Ada Promo)</label>
                <select name="driver_promo_zero_balance_bypass" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none; background: var(--bg-card); font-size: 0.875rem;">
                    <option value="1" <?= (($driver_promo_zero_balance_bypass['setting_value'] ?? '1') == '1') ? 'selected' : '' ?>>
                        ⭐ AKTIF (Direkomendasikan) - Saldo Rp 0 Tetap Bisa Menarik jika Pesanan Ada Cashback Promo
                    </option>
                    <option value="0" <?= (($driver_promo_zero_balance_bypass['setting_value'] ?? '1') == '0') ? 'selected' : '' ?>>
                        ❌ NONAKTIF - Wajib Saldo Minimal Rp 10.000 Tanpa Pengecualian Promo
                    </option>
                </select>
                <small style="color: var(--text-muted); display:block; margin-top: 8px;">
                    Saat promo aktif dan subsidi aplikasi lebih besar dari komisi (contoh: driver menerima cashback <b>+Rp 120</b> ke dompet Maypay), driver dengan saldo Rp 0 <b>tetap bisa menarik order</b> karena saldo bertambah, bukan berkurang.
                </small>
            </div>
        </div>
    </div>

    <div class="stats-grid" style="margin-top: 24px;">
        <div class="card-table" style="padding: 32px;">
            <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 24px;"><i class="fa-solid fa-gift" style="color: var(--primary); margin-right: 8px;"></i> Reward & Penalti Driver</h2>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 500;">Penalti Lewati/Tolak Pesanan (%)</label>
                <input type="number" name="driver_skip_penalty" value="<?= esc($driver_skip_penalty['setting_value'] ?? 2) ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none;" placeholder="Misal: 2" required>
                <small style="color: var(--text-muted); display:block; margin-top: 8px;">Persentase poin performa yang dipotong setiap kali driver menolak/melewati pesanan.</small>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 500;">Target Reward Harian (Jumlah Order)</label>
                <input type="number" name="driver_reward_threshold" value="<?= esc($driver_reward_threshold['setting_value'] ?? 10) ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none;" placeholder="Misal: 10" required>
                <small style="color: var(--text-muted); display:block; margin-top: 8px;">Jumlah pesanan yang harus diselesaikan dalam sehari untuk mendapatkan bonus.</small>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 500;">Bonus Saldo Reward Harian - Rp</label>
                <input type="number" name="driver_reward_amount" value="<?= esc($driver_reward_amount['setting_value'] ?? 10000) ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none;" placeholder="Misal: 10000" required>
                <small style="color: var(--text-muted); display:block; margin-top: 8px;">Nominal saldo Mai-Pay yang diberikan otomatis saat target harian tercapai.</small>
            </div>
        </div>
    </div>

    <!-- Radius Radar Pencarian Driver Dinamis -->
    <div class="stats-grid" style="margin-top: 24px;">
        <div class="card-table" style="padding: 32px; border-left: 4px solid var(--primary);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h2 style="font-size: 1.25rem; font-weight: 700; margin: 0; color: var(--text-main);">
                        <i class="fa-solid fa-radar" style="color: var(--primary); margin-right: 8px;"></i> Radius Radar Pencarian Driver (KM)
                    </h2>
                    <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 4px; margin-bottom: 0;">
                        Tentukan jarak jangkauan maksimal (radius) pencarian driver terdekat saat ada pesanan masuk. Driver di luar radius ini tidak akan menerima notifikasi pesanan.
                    </p>
                </div>
                <span style="background: #e0f2fe; color: #0284c7; padding: 4px 12px; border-radius: 99px; font-size: 0.75rem; font-weight: 700;">
                    <i class="fa-solid fa-bolt"></i> Realtime & Dinamis
                </span>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
                <!-- Mai-Ride -->
                <div style="background: var(--bg-body); padding: 16px; border-radius: 12px; border: 1px solid var(--border-color);">
                    <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">
                        <i class="fa-solid fa-motorcycle" style="color: #2563eb;"></i> Mai-Ride (Motor & Mobil)
                    </label>
                    <div style="display: flex; align-items: center; background: white; border: 1.5px solid var(--border-color); border-radius: 8px; padding-right: 12px;">
                        <input type="number" step="0.5" min="1" max="100" name="search_radius_ride" value="<?= esc($search_radius_ride['setting_value'] ?? 5) ?>" style="width: 100%; padding: 10px 12px; border: none; background: transparent; outline:none; font-size: 1rem; font-weight: 700; color: var(--primary);" required>
                        <span style="color: var(--text-muted); font-size: 0.8rem; font-weight: 700;">KM</span>
                    </div>
                    <small style="color: var(--text-muted); font-size: 0.72rem; display: block; margin-top: 6px;">Radius jemput penumpang. Standar: 5 KM.</small>
                </div>

                <!-- Mai-Send -->
                <div style="background: var(--bg-body); padding: 16px; border-radius: 12px; border: 1px solid var(--border-color);">
                    <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">
                        <i class="fa-solid fa-box" style="color: #ea580c;"></i> Mai-Send (Kirim Paket)
                    </label>
                    <div style="display: flex; align-items: center; background: white; border: 1.5px solid var(--border-color); border-radius: 8px; padding-right: 12px;">
                        <input type="number" step="0.5" min="1" max="100" name="search_radius_send" value="<?= esc($search_radius_send['setting_value'] ?? 5) ?>" style="width: 100%; padding: 10px 12px; border: none; background: transparent; outline:none; font-size: 1rem; font-weight: 700; color: var(--primary);" required>
                        <span style="color: var(--text-muted); font-size: 0.8rem; font-weight: 700;">KM</span>
                    </div>
                    <small style="color: var(--text-muted); font-size: 0.72rem; display: block; margin-top: 6px;">Radius jemput kurir barang. Standar: 5 KM.</small>
                </div>

                <!-- Mai-Titip -->
                <div style="background: var(--bg-body); padding: 16px; border-radius: 12px; border: 1px solid var(--border-color);">
                    <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">
                        <i class="fa-solid fa-basket-shopping" style="color: #9333ea;"></i> Mai-Titip (Jastip Belanja)
                    </label>
                    <div style="display: flex; align-items: center; background: white; border: 1.5px solid var(--border-color); border-radius: 8px; padding-right: 12px;">
                        <input type="number" step="0.5" min="1" max="100" name="search_radius_titip" value="<?= esc($search_radius_titip['setting_value'] ?? 5) ?>" style="width: 100%; padding: 10px 12px; border: none; background: transparent; outline:none; font-size: 1rem; font-weight: 700; color: var(--primary);" required>
                        <span style="color: var(--text-muted); font-size: 0.8rem; font-weight: 700;">KM</span>
                    </div>
                    <small style="color: var(--text-muted); font-size: 0.72rem; display: block; margin-top: 6px;">Radius kurir jastip. Standar: 5 KM.</small>
                </div>

                <!-- Mai-Food -->
                <div style="background: var(--bg-body); padding: 16px; border-radius: 12px; border: 1px solid var(--border-color);">
                    <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">
                        <i class="fa-solid fa-bowl-food" style="color: #16a34a;"></i> Mai-Food (Resto / Kuliner)
                    </label>
                    <div style="display: flex; align-items: center; background: white; border: 1.5px solid var(--border-color); border-radius: 8px; padding-right: 12px;">
                        <input type="number" step="0.5" min="1" max="100" name="search_radius_food" value="<?= esc($search_radius_food['setting_value'] ?? 15) ?>" style="width: 100%; padding: 10px 12px; border: none; background: transparent; outline:none; font-size: 1rem; font-weight: 700; color: var(--primary);" required>
                        <span style="color: var(--text-muted); font-size: 0.8rem; font-weight: 700;">KM</span>
                    </div>
                    <small style="color: var(--text-muted); font-size: 0.72rem; display: block; margin-top: 6px;">Radius kurir dari lokasi restoran. Standar: 15 KM.</small>
                </div>

                <!-- Mai-Mart -->
                <div style="background: var(--bg-body); padding: 16px; border-radius: 12px; border: 1px solid var(--border-color);">
                    <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">
                        <i class="fa-solid fa-store" style="color: #0d9488;"></i> Mai-Mart (Toko / Sembako)
                    </label>
                    <div style="display: flex; align-items: center; background: white; border: 1.5px solid var(--border-color); border-radius: 8px; padding-right: 12px;">
                        <input type="number" step="0.5" min="1" max="100" name="search_radius_mart" value="<?= esc($search_radius_mart['setting_value'] ?? 15) ?>" style="width: 100%; padding: 10px 12px; border: none; background: transparent; outline:none; font-size: 1rem; font-weight: 700; color: var(--primary);" required>
                        <span style="color: var(--text-muted); font-size: 0.8rem; font-weight: 700;">KM</span>
                    </div>
                    <small style="color: var(--text-muted); font-size: 0.72rem; display: block; margin-top: 6px;">Radius kurir dari lokasi toko/mart. Standar: 15 KM.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="stats-grid" style="margin-top: 24px;">
        <div class="card-table" style="padding: 32px;">
            <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 24px;"><i class="fa-solid fa-coins" style="color: #F59E0B; margin-right: 8px;"></i> Cashback Koin (Loyalty Program)</h2>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 500;">Target Kelipatan Order Penumpang</label>
                <input type="number" name="user_coin_threshold" value="<?= esc($user_coin_threshold['setting_value'] ?? 5) ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none;" placeholder="Misal: 5" required>
                <small style="color: var(--text-muted); display:block; margin-top: 8px;">Pelanggan akan mendapat Koin setiap kelipatan order ini tercapai.</small>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 500;">Nominal Reward Koin Penumpang</label>
                <input type="number" name="user_coin_reward" value="<?= esc($user_coin_reward['setting_value'] ?? 100) ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none;" placeholder="Misal: 100" required>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 24px 0;">

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 500;">Target Kelipatan Order Driver</label>
                <input type="number" name="driver_coin_threshold" value="<?= esc($driver_coin_threshold['setting_value'] ?? 10) ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none;" placeholder="Misal: 10" required>
                <small style="color: var(--text-muted); display:block; margin-top: 8px;">Kurir akan mendapat Koin setiap kelipatan order ini tercapai.</small>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 500;">Nominal Reward Koin Driver</label>
                <input type="number" name="driver_coin_reward" value="<?= esc($driver_coin_reward['setting_value'] ?? 200) ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none;" placeholder="Misal: 200" required>
            </div>
        </div>
    </div>

    <div class="stats-grid" style="margin-top: 24px;">
        <div class="card-table" style="padding: 32px;">
            <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 24px;"><i class="fa-solid fa-tags" style="color: #10B981; margin-right: 8px;"></i> Promo Dinamis (Otomatis)</h2>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 500;">Status Promo Dinamis</label>
                <select name="promo_dynamic_status" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none;" required>
                    <option value="inactive" <?= (isset($promo_dynamic_status['setting_value']) && $promo_dynamic_status['setting_value'] == 'inactive') ? 'selected' : '' ?>>Tidak Aktif</option>
                    <option value="active" <?= (isset($promo_dynamic_status['setting_value']) && $promo_dynamic_status['setting_value'] == 'active') ? 'selected' : '' ?>>Aktif</option>
                </select>
                <small style="color: var(--text-muted); display:block; margin-top: 8px;">Jika aktif, potongan akan otomatis terpasang pada hari yang dipilih.</small>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 500;">Hari Berlaku</label>
                <select name="promo_dynamic_day" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none;" required>
                    <option value="Monday" <?= (isset($promo_dynamic_day['setting_value']) && $promo_dynamic_day['setting_value'] == 'Monday') ? 'selected' : '' ?>>Senin</option>
                    <option value="Tuesday" <?= (isset($promo_dynamic_day['setting_value']) && $promo_dynamic_day['setting_value'] == 'Tuesday') ? 'selected' : '' ?>>Selasa</option>
                    <option value="Wednesday" <?= (isset($promo_dynamic_day['setting_value']) && $promo_dynamic_day['setting_value'] == 'Wednesday') ? 'selected' : '' ?>>Rabu</option>
                    <option value="Thursday" <?= (isset($promo_dynamic_day['setting_value']) && $promo_dynamic_day['setting_value'] == 'Thursday') ? 'selected' : '' ?>>Kamis</option>
                    <option value="Friday" <?= (isset($promo_dynamic_day['setting_value']) && $promo_dynamic_day['setting_value'] == 'Friday') ? 'selected' : '' ?>>Jumat</option>
                    <option value="Saturday" <?= (isset($promo_dynamic_day['setting_value']) && $promo_dynamic_day['setting_value'] == 'Saturday') ? 'selected' : '' ?>>Sabtu</option>
                    <option value="Sunday" <?= (isset($promo_dynamic_day['setting_value']) && $promo_dynamic_day['setting_value'] == 'Sunday') ? 'selected' : '' ?>>Minggu</option>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 500;">Tipe Potongan</label>
                <select name="promo_dynamic_type" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none;" required>
                    <option value="fixed" <?= (isset($promo_dynamic_type['setting_value']) && $promo_dynamic_type['setting_value'] == 'fixed') ? 'selected' : '' ?>>Nominal Rupiah (Rp)</option>
                    <option value="percent" <?= (isset($promo_dynamic_type['setting_value']) && $promo_dynamic_type['setting_value'] == 'percent') ? 'selected' : '' ?>>Persentase (%)</option>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 500;">Jumlah Potongan</label>
                <input type="number" name="promo_dynamic_amount" value="<?= esc($promo_dynamic_amount['setting_value'] ?? 0) ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none;" placeholder="Misal: 5000" required>
            </div>
        </div>
    </div>

    <div class="stats-grid" style="margin-top: 24px;">
        <div class="card-table" style="padding: 32px;">
            <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 24px;"><i class="fa-brands fa-whatsapp" style="color: #25D366; margin-right: 8px;"></i> Integrasi API OTP (WhatsApp)</h2>
            <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 20px;">
                Isi token API untuk pengiriman OTP. Sistem menggunakan skema fallback otomatis: <strong>Cloudchat &rarr; Bablast &rarr; Whapify &rarr; Fonnte</strong>.
            </p>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 500;">Cloudchat API Key (Gateway Utama)</label>
                <input type="text" name="cloudchat_api_key" value="<?= esc($cloudchat_api_key['setting_value'] ?? '') ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none;" placeholder="sk_...">
                <small style="color: var(--text-muted);">Endpoint Cloudchat: <code>https://app.cloudchat.id/api/public/v1/messages</code></small>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 500;">Bablast API Key (WABA Cadangan 1)</label>
                <input type="text" name="bablast_api_key" value="<?= esc($bablast_api_key['setting_value'] ?? '') ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none;" placeholder="Masukkan Bablast Token">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 500;">Whapify API Secret (Unofficial 1)</label>
                <input type="text" name="whapify_api_secret" value="<?= esc($whapify_api_secret['setting_value'] ?? '') ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none;" placeholder="Masukkan Whapify Secret">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 500;">Whapify Account ID</label>
                <input type="text" name="whapify_account_id" value="<?= esc($whapify_account_id['setting_value'] ?? '') ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none;" placeholder="Masukkan Whapify Account ID">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 500;">Fonnte API Key (Unofficial 2)</label>
                <input type="text" name="fonnte_api_key" value="<?= esc($fonnte_api_key['setting_value'] ?? '') ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none;" placeholder="Masukkan Fonnte Token">
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 32px 0;">

            <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 24px;"><i class="fa-solid fa-shield-halved" style="color: var(--primary); margin-right: 8px;"></i> Keamanan Akun</h2>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 500;">Ubah Password Admin</label>
                <input type="password" name="new_password" placeholder="Biarkan kosong jika tidak ingin mengubah" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none;">
                <small style="color: var(--text-muted);">Password akan di-enkripsi menggunakan Bcrypt.</small>
            </div>

            <!-- Konten Dinamis Aplikasi -->
            <h2 style="font-size: 1.25rem; font-weight: 600; margin-top: 40px; margin-bottom: 24px;"><i class="fa-solid fa-mobile-screen" style="color: var(--primary); margin-right: 8px;"></i> Konten Aplikasi Supir</h2>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 500;">Bantuan & Laporan</label>
                <textarea name="driver_help_center" rows="4" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none;" placeholder="Tulis info kontak bantuan di sini..."><?= esc($driver_help_center['setting_value'] ?? '') ?></textarea>
                <small style="color: var(--text-muted);">Teks ini akan muncul di menu "Bantuan & Laporan" aplikasi supir.</small>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 500;">Tentang Maijek Driver</label>
                <textarea name="driver_about" rows="4" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none;" placeholder="Tulis versi atau deskripsi aplikasi di sini..."><?= esc($driver_about['setting_value'] ?? '') ?></textarea>
                <small style="color: var(--text-muted);">Teks ini akan muncul di menu "Tentang Maijek Driver".</small>
            </div>

            <div style="margin-top: 40px;">
                <button type="submit" style="width: 100%; padding: 16px; background: var(--primary); color: white; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: 0.2s;">
                    Simpan Semua Perubahan
                </button>
            </div>
        </div>
    </div>
</form>
<?= $this->endSection() ?>