<?= $this->extend('merchant/layout/main') ?>

<?= $this->section('content') ?>

<!-- Leaflet CSS for Map Picker -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
    .profile-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 16px;
    }

    @media (max-width: 640px) {
        .profile-grid-2 {
            grid-template-columns: 1fr !important;
            gap: 12px;
        }

        .daily-row-card {
            flex-direction: column;
            align-items: stretch !important;
            gap: 10px;
        }

        .daily-row-card > div:last-child {
            max-width: 100% !important;
            width: 100%;
        }
    }
</style>

<div style="max-width: 800px; margin: 0 auto;">
    <div style="margin-bottom: 24px;">
        <h2 style="font-size: 1.35rem; font-weight: 800; letter-spacing: -0.5px;">Profil & Pengaturan Toko</h2>
        <p style="font-size: 0.85rem; color: var(--text-muted);">Kelola foto profil toko, jam operasional buka/tutup, dan alamat usaha Anda.</p>
    </div>

    <!-- Card 1: Foto Profil Toko -->
    <div class="card">
        <h3 style="font-size: 1.05rem; font-weight: 800; margin-bottom: 16px;">Foto Profil / Logo Toko</h3>
        <div style="display: flex; align-items: center; gap: 24px; flex-wrap: wrap;">
            <div style="width: 100px; height: 100px; border-radius: 18px; overflow: hidden; background: #f1f5f9; border: 1px solid var(--border-color); flex-shrink: 0;">
                <?php if (!empty($merchant['photo'])): ?>
                    <img src="<?= base_url($merchant['photo']) ?>" alt="Foto Toko" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 2.5rem;">
                        <i class="fa-solid fa-store"></i>
                    </div>
                <?php endif; ?>
            </div>

            <form action="<?= base_url('merchant/profile/update-photo') ?>" method="post" enctype="multipart/form-data" style="flex: 1; min-width: 250px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); margin-bottom: 8px;">Unggah foto toko baru dari komputer (JPG, PNG, WebP):</label>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <input type="file" name="photo" accept="image/*" required style="padding: 8px; border: 1px solid var(--border-color); border-radius: 10px; font-size: 0.85rem; flex: 1; min-width: 180px;">
                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-upload"></i> Unggah
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Card 2: Data Informasi & Jam Operasional -->
    <div class="card">
        <h3 style="font-size: 1.05rem; font-weight: 800; margin-bottom: 16px;">Informasi Toko & Jam Operasional</h3>

        <form action="<?= base_url('merchant/profile/update') ?>" method="post">
            <div class="profile-grid-2">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-size: 0.85rem; font-weight: 700;">Nama Usaha / Toko</label>
                    <input type="text" value="<?= esc($merchant['name']) ?>" disabled style="width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 10px; background: #f8fafc; color: #64748b; font-size: 0.9rem;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-size: 0.85rem; font-weight: 700;">Nomor HP Toko</label>
                    <input type="text" value="<?= esc($merchant['phone']) ?>" disabled style="width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 10px; background: #f8fafc; color: #64748b; font-size: 0.9rem;">
                </div>
            </div>

            <?php
            $opDaysRaw = $merchant['operating_days'] ?? '';
            $isDaily = false;
            $dailyData = [];
            $defaultOpen = $merchant['opening_time'] ?? '08:00';
            $defaultClose = $merchant['closing_time'] ?? '21:00';

            $decoded = json_decode($opDaysRaw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && isset($decoded['mode']) && $decoded['mode'] === 'daily') {
                $isDaily = true;
                $dailyData = $decoded['days'] ?? [];
            }

            $dayMap = [
                '1' => 'Senin',
                '2' => 'Selasa',
                '3' => 'Rabu',
                '4' => 'Kamis',
                '5' => 'Jumat',
                '6' => 'Sabtu',
                '7' => 'Minggu',
            ];

            $currentDays = (!$isDaily && !empty($opDaysRaw)) ? explode(',', $opDaysRaw) : ['1', '2', '3', '4', '5', '6', '7'];
            ?>

            <!-- Mode Switcher: Jam Sama vs Atur Beda Tiap Hari (Pilihan A) -->
            <div style="margin-bottom: 20px; background: #f8fafc; border: 1px solid var(--border-color); border-radius: 12px; padding: 16px;">
                <label style="display: block; font-size: 0.88rem; font-weight: 700; color: #1e293b; margin-bottom: 8px;">
                    <i class="fa-solid fa-clock-rotate-left" style="color: var(--primary); margin-right: 6px;"></i> Mode Pengaturan Jam Operasional
                </label>

                <div style="display: flex; gap: 8px; margin-bottom: 16px; background: #e2e8f0; padding: 4px; border-radius: 10px;">
                    <label id="tabModeSame" onclick="toggleScheduleMode('same')" style="flex: 1; text-align: center; padding: 8px 12px; border-radius: 8px; cursor: pointer; font-size: 0.85rem; font-weight: 700; transition: all 0.2s; background: <?= !$isDaily ? 'var(--primary, #0ea5e9)' : 'transparent' ?>; color: <?= !$isDaily ? '#ffffff' : '#64748b' ?>;">
                        <input type="radio" name="schedule_mode" value="same" <?= !$isDaily ? 'checked' : '' ?> style="display: none;">
                        <i class="fa-solid fa-clock" style="margin-right: 6px;"></i> Jam Sama Tiap Hari
                    </label>
                    <label id="tabModeDaily" onclick="toggleScheduleMode('daily')" style="flex: 1; text-align: center; padding: 8px 12px; border-radius: 8px; cursor: pointer; font-size: 0.85rem; font-weight: 700; transition: all 0.2s; background: <?= $isDaily ? 'var(--primary, #0ea5e9)' : 'transparent' ?>; color: <?= $isDaily ? '#ffffff' : '#64748b' ?>;">
                        <input type="radio" name="schedule_mode" value="daily" <?= $isDaily ? 'checked' : '' ?> style="display: none;">
                        <i class="fa-solid fa-calendar-week" style="margin-right: 6px;"></i> Atur Beda Tiap Hari (Pilihan A)
                    </label>
                </div>

                <!-- CONTAINER 1: MODE JAM SAMA TIAP HARI -->
                <div id="sectionModeSame" style="display: <?= !$isDaily ? 'block' : 'none' ?>;">
                    <div class="profile-grid-2">
                        <div>
                            <label style="display: block; margin-bottom: 6px; font-size: 0.85rem; font-weight: 700;">Jam Buka Operasional</label>
                            <input type="time" name="opening_time" value="<?= esc($defaultOpen) ?>" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 10px; outline: none; font-size: 0.9rem;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 6px; font-size: 0.85rem; font-weight: 700;">Jam Tutup Operasional</label>
                            <input type="time" name="closing_time" value="<?= esc($defaultClose) ?>" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 10px; outline: none; font-size: 0.9rem;">
                        </div>
                    </div>

                    <div style="margin-bottom: 8px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; gap: 8px;">
                            <label style="font-size: 0.85rem; font-weight: 700; color: #334155; margin: 0;">Hari Buka Toko</label>
                            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                <button type="button" onclick="selectOperatingDays(['1','2','3','4','5','6','7'])" style="font-size: 0.75rem; padding: 4px 8px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; cursor: pointer; font-weight: 600; color: #334155;">Setiap Hari</button>
                                <button type="button" onclick="selectOperatingDays(['1','2','3','4','5','6'])" style="font-size: 0.75rem; padding: 4px 8px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; cursor: pointer; font-weight: 600; color: #334155;">Senin - Sabtu</button>
                                <button type="button" onclick="selectOperatingDays(['1','2','3','4','5'])" style="font-size: 0.75rem; padding: 4px 8px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; cursor: pointer; font-weight: 600; color: #334155;">Senin - Jumat</button>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(90px, 1fr)); gap: 8px;">
                            <?php foreach ($dayMap as $num => $dayName):
                                $isChecked = in_array((string)$num, $currentDays);
                            ?>
                                <label class="day-chip-label" style="display: flex; align-items: center; justify-content: center; gap: 6px; padding: 10px 8px; border: 1.5px solid <?= $isChecked ? 'var(--primary)' : '#e2e8f0' ?>; background: <?= $isChecked ? '#eff6ff' : '#ffffff' ?>; border-radius: 10px; cursor: pointer; font-size: 0.85rem; font-weight: <?= $isChecked ? '700' : '500' ?>; color: <?= $isChecked ? 'var(--primary)' : '#64748b' ?>; user-select: none; transition: all 0.2s ease;">
                                    <input type="checkbox" name="operating_days[]" value="<?= $num ?>" <?= $isChecked ? 'checked' : '' ?> style="accent-color: var(--primary); cursor: pointer;" onchange="handleDayToggle(this)">
                                    <span><?= $dayName ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- CONTAINER 2: MODE ATUR BEDA TIAP HARI (PILIHAN A) -->
                <div id="sectionModeDaily" style="display: <?= $isDaily ? 'block' : 'none' ?>;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                        <span style="font-size: 0.82rem; color: var(--text-muted);">Atur jam buka & tutup spesifik untuk setiap hari (Senin s/d Minggu)</span>
                        <button type="button" onclick="copyMondayHours()" style="font-size: 0.75rem; padding: 5px 10px; border-radius: 6px; border: 1px solid #93c5fd; background: #eff6ff; color: #1d4ed8; cursor: pointer; font-weight: 700;">
                            <i class="fa-solid fa-copy"></i> Salin Jam Senin ke Semua Hari
                        </button>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <?php foreach ($dayMap as $num => $dayName):
                            $cfg = $dailyData[(string)$num] ?? [];
                            $isOpen = isset($cfg['is_open']) ? (bool)$cfg['is_open'] : true;
                            $openTime = $cfg['open'] ?? '08:00';
                            $closeTime = $cfg['close'] ?? '21:00';
                        ?>
                            <div class="daily-row-card" id="dailyRow_<?= $num ?>" style="display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 10px 14px; background: <?= $isOpen ? '#ffffff' : '#f8fafc' ?>; border: 1.5px solid <?= $isOpen ? '#bfdbfe' : '#e2e8f0' ?>; border-radius: 10px; flex-wrap: wrap; transition: all 0.2s;">
                                <div style="display: flex; align-items: center; gap: 10px; min-width: 130px;">
                                    <input type="checkbox" name="daily_active[<?= $num ?>]" value="1" <?= $isOpen ? 'checked' : '' ?> id="dailyActive_<?= $num ?>" onchange="handleDailyToggle('<?= $num ?>')" style="accent-color: var(--primary); width: 18px; height: 18px; cursor: pointer;">
                                    <label for="dailyActive_<?= $num ?>" style="font-weight: 700; font-size: 0.9rem; cursor: pointer; color: <?= $isOpen ? '#1e293b' : '#94a3b8' ?>;" id="dailyLabel_<?= $num ?>">
                                        <?= $dayName ?>
                                    </label>
                                    <span id="dailyBadge_<?= $num ?>" style="font-size: 0.75rem; font-weight: 700; padding: 2px 6px; border-radius: 4px; background: <?= $isOpen ? '#dcfce7' : '#fee2e2' ?>; color: <?= $isOpen ? '#166534' : '#991b1b' ?>;">
                                        <?= $isOpen ? 'Buka' : 'Libur' ?>
                                    </span>
                                </div>

                                <div id="dailyTimeInputs_<?= $num ?>" style="display: flex; align-items: center; gap: 8px; flex: 1; max-width: 320px; <?= !$isOpen ? 'opacity: 0.4; pointer-events: none;' : '' ?>">
                                    <input type="time" name="daily_open[<?= $num ?>]" id="dailyOpen_<?= $num ?>" value="<?= esc($openTime) ?>" style="flex: 1; padding: 6px 10px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.85rem; outline: none; background: #fff;">
                                    <span style="font-weight: 700; color: #94a3b8;">-</span>
                                    <input type="time" name="daily_close[<?= $num ?>]" id="dailyClose_<?= $num ?>" value="<?= esc($closeTime) ?>" style="flex: 1; padding: 6px 10px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.85rem; outline: none; background: #fff;">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; margin-bottom: 6px; font-size: 0.85rem; font-weight: 700;">Alamat Lengkap Toko</label>
                <textarea name="address" rows="3" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 10px; outline: none; font-size: 0.9rem;"><?= esc($merchant['address'] ?? '') ?></textarea>
            </div>

            <!-- Titik Koordinat GPS & Peta Lokasi Toko -->
            <div style="margin-bottom: 24px; background: #f8fafc; border: 1.5px solid var(--border-color); border-radius: 14px; padding: 18px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                    <div>
                        <label style="display: block; font-size: 0.95rem; font-weight: 800; color: #1e293b; margin-bottom: 2px;">
                            <i class="fa-solid fa-location-dot" style="color: #ea580c; margin-right: 6px;"></i> Titik Koordinat Lokasi Toko (GPS)
                        </label>
                        <span style="font-size: 0.8rem; color: var(--text-muted);">Tentukan titik lokasi toko/warung di peta agar pelanggan dan driver mudah menemukan resto Anda.</span>
                    </div>
                    <button type="button" onclick="getCurrentGpsLocation()" class="btn-primary" style="background: #0284c7; padding: 7px 14px; font-size: 0.8rem; box-shadow: none;">
                        <i class="fa-solid fa-crosshairs"></i> Gunakan GPS Lokasi Saya
                    </button>
                </div>

                <!-- Map Container -->
                <div id="merchantMap" style="height: 320px; width: 100%; border-radius: 12px; border: 1px solid var(--border-color); margin-bottom: 12px; z-index: 1;"></div>

                <div style="display: flex; align-items: center; font-size: 0.78rem; color: #0369a1; margin-bottom: 14px; background: #e0f2fe; padding: 8px 12px; border-radius: 8px; border: 1px solid #bae6fd;">
                    <i class="fa-solid fa-circle-info" style="margin-right: 8px; font-size: 0.9rem;"></i>
                    <span><b>Petunjuk:</b> Klik di mana saja pada peta atau geser pin bulat oranye tepat di lokasi toko Anda.</span>
                </div>

                <!-- Input Koordinat (Latitude & Longitude) -->
                <div class="profile-grid-2" style="margin-bottom: 0;">
                    <div>
                        <label style="display: block; margin-bottom: 6px; font-size: 0.82rem; font-weight: 700;">Latitude (Lintang)</label>
                        <input type="text" name="latitude" id="inputLat" value="<?= esc((!empty($merchant['latitude']) && (float)$merchant['latitude'] != 0) ? $merchant['latitude'] : '-3.42460000') ?>" placeholder="-3.42460000" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 10px; font-size: 0.88rem; outline: none; background: #fff;" onchange="updateMarkerFromInputs()">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 6px; font-size: 0.82rem; font-weight: 700;">Longitude (Bujur)</label>
                        <input type="text" name="longitude" id="inputLng" value="<?= esc((!empty($merchant['longitude']) && (float)$merchant['longitude'] != 0) ? $merchant['longitude'] : '119.34140000') ?>" placeholder="119.34140000" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 10px; font-size: 0.88rem; outline: none; background: #fff;" onchange="updateMarkerFromInputs()">
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end;">
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleScheduleMode(mode) {
        const isDaily = mode === 'daily';
        document.getElementById('sectionModeSame').style.display = !isDaily ? 'block' : 'none';
        document.getElementById('sectionModeDaily').style.display = isDaily ? 'block' : 'none';

        const tabSame = document.getElementById('tabModeSame');
        const tabDaily = document.getElementById('tabModeDaily');

        if (isDaily) {
            tabDaily.style.background = 'var(--primary, #0ea5e9)';
            tabDaily.style.color = '#ffffff';
            tabSame.style.background = 'transparent';
            tabSame.style.color = '#64748b';
            tabDaily.querySelector('input').checked = true;
        } else {
            tabSame.style.background = 'var(--primary, #0ea5e9)';
            tabSame.style.color = '#ffffff';
            tabDaily.style.background = 'transparent';
            tabDaily.style.color = '#64748b';
            tabSame.querySelector('input').checked = true;
        }
    }

    function handleDayToggle(cb) {
        const parent = cb.closest('.day-chip-label');
        if (cb.checked) {
            parent.style.borderColor = 'var(--primary)';
            parent.style.background = '#eff6ff';
            parent.style.color = 'var(--primary)';
            parent.style.fontWeight = '700';
        } else {
            parent.style.borderColor = '#e2e8f0';
            parent.style.background = '#ffffff';
            parent.style.color = '#64748b';
            parent.style.fontWeight = '500';
        }
    }

    function selectOperatingDays(daysList) {
        const checkboxes = document.querySelectorAll('input[name="operating_days[]"]');
        checkboxes.forEach(cb => {
            cb.checked = daysList.includes(cb.value);
            handleDayToggle(cb);
        });
    }

    function handleDailyToggle(num) {
        const cb = document.getElementById('dailyActive_' + num);
        const row = document.getElementById('dailyRow_' + num);
        const inputs = document.getElementById('dailyTimeInputs_' + num);
        const badge = document.getElementById('dailyBadge_' + num);
        const label = document.getElementById('dailyLabel_' + num);

        if (cb.checked) {
            row.style.background = '#ffffff';
            row.style.borderColor = '#bfdbfe';
            inputs.style.opacity = '1';
            inputs.style.pointerEvents = 'auto';
            badge.innerText = 'Buka';
            badge.style.background = '#dcfce7';
            badge.style.color = '#166534';
            label.style.color = '#1e293b';
        } else {
            row.style.background = '#f8fafc';
            row.style.borderColor = '#e2e8f0';
            inputs.style.opacity = '0.4';
            inputs.style.pointerEvents = 'none';
            badge.innerText = 'Libur';
            badge.style.background = '#fee2e2';
            badge.style.color = '#991b1b';
            label.style.color = '#94a3b8';
        }
    }

    function copyMondayHours() {
        const monOpen = document.getElementById('dailyOpen_1').value;
        const monClose = document.getElementById('dailyClose_1').value;

        for (let i = 2; i <= 7; i++) {
            const oInput = document.getElementById('dailyOpen_' + i);
            const cInput = document.getElementById('dailyClose_' + i);
            if (oInput) oInput.value = monOpen;
            if (cInput) cInput.value = monClose;
        }
        alert('Jam buka (' + monOpen + ') dan tutup (' + monClose + ') Senin berhasil disalin ke hari Selasa s/d Minggu!');
    }
</script>

<!-- Leaflet JS for Map Picker -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    let merchantMap = null;
    let merchantMarker = null;

    document.addEventListener('DOMContentLoaded', () => {
        initMerchantMap();
    });

    function initMerchantMap() {
        let defaultLat = parseFloat(document.getElementById('inputLat').value) || -3.4246;
        let defaultLng = parseFloat(document.getElementById('inputLng').value) || 119.3414;

        merchantMap = L.map('merchantMap').setView([defaultLat, defaultLng], 15);

        // Google Maps Tiles (Akurat, cepat, lengkap dengan nama jalan lokal)
        L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
            attribution: '&copy; Google Maps',
            maxZoom: 20
        }).addTo(merchantMap);

        // Custom Restaurant Pin Icon
        const storeIcon = L.divIcon({
            className: 'custom-map-pin',
            html: `
                <div style="background: #ea580c; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.1rem; box-shadow: 0 4px 12px rgba(234, 88, 12, 0.45); border: 3px solid white;">
                    <i class="fa-solid fa-utensils"></i>
                </div>
            `,
            iconSize: [38, 38],
            iconAnchor: [19, 19]
        });

        merchantMarker = L.marker([defaultLat, defaultLng], {
            draggable: true,
            icon: storeIcon
        }).addTo(merchantMap);

        merchantMarker.bindPopup("<b><?= esc($merchant['name']) ?></b><br>Geser pin ini tepat ke lokasi toko Anda.").openPopup();

        // Saat pin digeser
        merchantMarker.on('dragend', function(e) {
            const pos = merchantMarker.getLatLng();
            setCoords(pos.lat, pos.lng);
        });

        // Saat peta diklik
        merchantMap.on('click', function(e) {
            merchantMarker.setLatLng(e.latlng);
            setCoords(e.latlng.lat, e.latlng.lng);
        });

        // Pastikan ukuran peta pas setelah render
        setTimeout(() => {
            merchantMap.invalidateSize();
        }, 400);
    }

    function setCoords(lat, lng) {
        document.getElementById('inputLat').value = lat.toFixed(8);
        document.getElementById('inputLng').value = lng.toFixed(8);
    }

    function updateMarkerFromInputs() {
        const lat = parseFloat(document.getElementById('inputLat').value);
        const lng = parseFloat(document.getElementById('inputLng').value);
        if (!isNaN(lat) && !isNaN(lng) && merchantMap && merchantMarker) {
            merchantMarker.setLatLng([lat, lng]);
            merchantMap.panTo([lat, lng]);
        }
    }

    function getCurrentGpsLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    setCoords(lat, lng);
                    if (merchantMap && merchantMarker) {
                        merchantMarker.setLatLng([lat, lng]);
                        merchantMap.setView([lat, lng], 17);
                        merchantMarker.bindPopup("<b>Lokasi GPS Anda Ditemukan!</b>").openPopup();
                    }
                },
                (err) => {
                    alert("Gagal mendeteksi lokasi GPS: " + err.message + ". Pastikan izin lokasi browser Anda aktif.");
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000
                }
            );
        } else {
            alert("Browser Anda tidak mendukung deteksi lokasi otomatis.");
        }
    }
</script>

<?= $this->endSection() ?>