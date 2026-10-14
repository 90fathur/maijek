<?= $this->extend('merchant/layout/main') ?>

<?= $this->section('content') ?>

<?php
$isMart = !empty($isMart) ? $isMart : ((session()->get('merchant_type') === 'mart') || (($merchant['merchant_type'] ?? '') === 'mart'));
$pageHeading = $isMart ? 'Daftar Produk Toko' : 'Daftar Menu Restoran';
$pageDesc = $isMart ? 'Kelola produk belanja, satuan, harga, foto, dan ketersediaan stok.' : 'Kelola menu makanan, satuan porsi, harga, foto, dan ketersediaan stok.';
$addBtnText = $isMart ? 'Tambah Produk Baru' : 'Tambah Menu Baru';
$emptyIcon = $isMart ? 'fa-boxes-stacked' : 'fa-bowl-food';
$emptyTitle = $isMart ? 'Belum ada produk toko' : 'Belum ada menu makanan';
$emptyDesc = $isMart ? 'Klik tombol "Tambah Produk Baru" di atas untuk menambahkan produk pertama Anda.' : 'Klik tombol "Tambah Menu Baru" di atas untuk menambahkan menu pertama Anda.';
$labelName = $isMart ? 'Nama Produk / Barang' : 'Nama Menu Makanan';
$placeholderName = $isMart ? 'Contoh: Minyak Goreng 2L, Beras 5kg' : 'Contoh: Nasi Goreng Spesial';
$placeholderDesc = $isMart ? 'Jelaskan berat, ukuran, merek, atau kemasan...' : 'Jelaskan porsi, topping, atau rasa makanan...';
$labelPhoto = $isMart ? 'Foto Produk (Opsional)' : 'Foto Menu (Opsional)';
$saveBtnText = $isMart ? 'Simpan Produk' : 'Simpan Menu';

// Satuan & Kategori terpisah untuk Mart vs Food
$foodUnits = ['porsi', 'paket', 'bungkus', 'kotak', 'mangkok', 'piring', 'gelas', 'cup', 'botol', 'potong', 'tusuk', 'loyang', 'pcs'];
$martUnits = ['pcs', 'kg', 'gram', 'liter', 'ml', 'pack', 'ikat', 'sachet', 'bungkus', 'botol', 'kaleng', 'dus', 'strip', 'butir', 'roll', 'renteng'];
$currentUnits = $isMart ? $martUnits : $foodUnits;

$foodCategories = ['Makanan', 'Minuman', 'Camilan / Snack', 'Paket Hemat', 'Lainnya'];
$martCategories = ['Sembako', 'Sayur & Buah', 'Daging & Ikan', 'Bumbu Dapur', 'Makanan Ringan / Snack', 'Minuman Kemasan', 'Kebutuhan Rumah Tangga', 'Kebutuhan Bayi', 'Kesehatan & Obat', 'Alat Tulis & Kantor', 'Lainnya'];
$currentCategories = $isMart ? $martCategories : $foodCategories;
?>

<style>
    .menu-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .menu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
    }

    .menu-card {
        background: white;
        border-radius: 16px;
        border: 1px solid var(--border-color);
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        display: flex;
        flex-direction: column;
        transition: all 0.2s;
    }

    .menu-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
    }

    .menu-img-wrapper {
        height: 160px;
        width: 100%;
        background: #f1f5f9;
        position: relative;
        overflow: hidden;
    }

    .menu-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .menu-no-img {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 2.5rem;
    }

    .menu-body {
        padding: 16px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .menu-name {
        font-size: 1rem;
        font-weight: 800;
        margin-bottom: 4px;
        color: var(--text-main);
    }

    .menu-desc {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-bottom: 12px;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .menu-price {
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--primary);
        margin-top: auto;
    }

    .menu-footer {
        padding: 12px 16px;
        border-top: 1px solid var(--border-color);
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* Switch toggle */
    .switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #cbd5e1;
        transition: .3s;
        border-radius: 24px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
    }

    input:checked+.slider {
        background-color: var(--success);
    }

    input:checked+.slider:before {
        transform: translateX(20px);
    }

    /* Modal */
    .modal-backdrop {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .modal-box {
        background: white;
        border-radius: 20px;
        width: 90%;
        max-width: 520px;
        padding: 28px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
        max-height: 90vh;
        overflow-y: auto;
    }

    /* RESPONSIVE MEDIA QUERIES */
    @media (max-width: 640px) {
        .menu-header {
            flex-direction: column;
            align-items: stretch;
            gap: 14px;
        }

        .menu-header .btn-primary {
            width: 100%;
            justify-content: center;
        }

        .menu-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .modal-box {
            padding: 20px 16px;
            width: 94%;
        }
    }
</style>

<div class="menu-header">
    <div>
        <h2 style="font-size: 1.35rem; font-weight: 800; letter-spacing: -0.5px;"><?= $pageHeading ?></h2>
        <p style="font-size: 0.85rem; color: var(--text-muted);"><?= $pageDesc ?></p>
    </div>
    <button onclick="openAddMenuModal()" class="btn-primary">
        <i class="fa-solid fa-plus"></i> <?= $addBtnText ?>
    </button>
</div>

<div class="menu-grid">
    <?php if (!empty($menus)): ?>
        <?php foreach ($menus as $m): 
            $itemUnit = !empty($m['unit']) ? $m['unit'] : ($isMart ? 'pcs' : 'porsi');
            $itemCategory = !empty($m['category']) ? $m['category'] : ($isMart ? 'Sembako' : 'Makanan');
        ?>
            <div class="menu-card" id="menu_card_<?= $m['id'] ?>">
                <div class="menu-img-wrapper">
                    <?php if (!empty($m['photo'])): ?>
                        <img src="<?= base_url($m['photo']) ?>" alt="<?= esc($m['name']) ?>" class="menu-img">
                    <?php else: ?>
                        <div class="menu-no-img"><i class="fa-solid <?= $isMart ? 'fa-box' : 'fa-utensils' ?>"></i></div>
                    <?php endif; ?>
                </div>
                <div class="menu-body">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px; margin-bottom: 6px;">
                        <span style="font-size: 0.72rem; font-weight: 700; color: #4f46e5; background: #eef2ff; padding: 2px 8px; border-radius: 6px; border: 1px solid #c7d2fe;">
                            <i class="fa-solid fa-tag" style="font-size: 0.65rem;"></i> <?= esc($itemCategory) ?>
                        </span>
                        <span style="font-size: 0.72rem; font-weight: 700; color: #047857; background: #ecfdf5; padding: 2px 8px; border-radius: 6px; border: 1px solid #a7f3d0;">
                            / <?= esc($itemUnit) ?>
                        </span>
                    </div>

                    <div class="menu-name"><?= esc($m['name']) ?></div>
                    <div class="menu-desc"><?= esc($m['description'] ?? 'Tidak ada deskripsi.') ?></div>

                    <?php if (!empty($m['discount_price']) && (float)$m['discount_price'] > 0 && (float)$m['discount_price'] < (float)$m['price']):
                        $pct = round((($m['price'] - $m['discount_price']) / $m['price']) * 100);
                    ?>
                        <div style="display: flex; align-items: center; gap: 6px; margin-top: auto;">
                            <span style="background: #fee2e2; color: #dc2626; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; font-weight: 800;">-<?= $pct ?>%</span>
                            <span style="font-size: 0.8rem; color: var(--text-muted); text-decoration: line-through;">Rp <?= number_format($m['price'], 0, ',', '.') ?></span>
                        </div>
                        <div class="menu-price" style="color: #10b981; margin-top: 2px;">
                            Rp <?= number_format($m['discount_price'], 0, ',', '.') ?>
                            <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">/ <?= esc($itemUnit) ?></span>
                        </div>
                    <?php else: ?>
                        <div class="menu-price">
                            Rp <?= number_format($m['price'], 0, ',', '.') ?>
                            <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">/ <?= esc($itemUnit) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="menu-footer">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <label class="switch">
                            <input type="checkbox" onchange="toggleMenuStock(<?= $m['id'] ?>)" <?= ($m['is_available'] == 1) ? 'checked' : '' ?> id="switch_<?= $m['id'] ?>">
                            <span class="slider"></span>
                        </label>
                        <span style="font-size: 0.75rem; font-weight: 700;" id="stock_text_<?= $m['id'] ?>">
                            <?= ($m['is_available'] == 1) ? 'Tersedia' : 'Habis' ?>
                        </span>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" onclick='editMenu(<?= json_encode($m) ?>)' class="btn-icon" style="width: 32px; height: 32px;" title="<?= $isMart ? 'Ubah Produk' : 'Ubah Menu' ?>">
                            <i class="fa-solid fa-pen-to-square" style="font-size: 0.8rem;"></i>
                        </button>
                        <a href="<?= base_url('merchant/menus/delete/' . $m['id']) ?>" onclick="return confirm('Hapus <?= $isMart ? 'produk' : 'menu' ?> ini?')" class="btn-icon" style="width: 32px; height: 32px; color: #ef4444;" title="<?= $isMart ? 'Hapus Produk' : 'Hapus Menu' ?>">
                            <i class="fa-solid fa-trash" style="font-size: 0.8rem;"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="grid-column: 1 / -1; background: white; padding: 40px; text-align: center; border-radius: 16px; border: 1px dashed var(--border-color); color: var(--text-muted);">
            <i class="fa-solid <?= $emptyIcon ?>" style="font-size: 3rem; margin-bottom: 12px; opacity: 0.4;"></i>
            <h3><?= $emptyTitle ?></h3>
            <p style="margin-top: 4px; font-size: 0.85rem;"><?= $emptyDesc ?></p>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Tambah / Ubah Menu -->
<div id="menuModal" class="modal-backdrop">
    <div class="modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 id="modalTitle" style="font-size: 1.15rem; font-weight: 800;"><?= $addBtnText ?></h3>
            <button type="button" onclick="closeMenuModal()" style="background: none; border: none; font-size: 1.25rem; color: var(--text-muted); cursor: pointer;">&times;</button>
        </div>

        <form id="menuForm" action="<?= base_url('merchant/menus/store') ?>" method="post" enctype="multipart/form-data">
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-size: 0.85rem; font-weight: 700;"><?= $labelName ?></label>
                <input type="text" name="name" id="menuName" required placeholder="<?= $placeholderName ?>" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 10px; outline: none; font-size: 0.9rem;">
            </div>

            <!-- Kategori & Satuan Terpisah -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-size: 0.85rem; font-weight: 700;">Kategori</label>
                    <select name="category" id="menuCategory" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 10px; outline: none; font-size: 0.9rem; background: white;">
                        <?php foreach ($currentCategories as $cat): ?>
                            <option value="<?= esc($cat) ?>"><?= esc($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-size: 0.85rem; font-weight: 700;">Satuan (Unit)</label>
                    <select name="unit" id="menuUnit" onchange="onUnitSelectChange(this.value)" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 10px; outline: none; font-size: 0.9rem; background: white;">
                        <?php foreach ($currentUnits as $u): ?>
                            <option value="<?= esc($u) ?>"><?= esc(ucfirst($u)) ?></option>
                        <?php endforeach; ?>
                        <option value="custom" style="font-weight: 700; color: #4f46e5;">✏️ + Buat Satuan Sendiri...</option>
                    </select>
                </div>
            </div>

            <!-- Custom Satuan Box -->
            <div id="customUnitContainer" style="display: none; margin-bottom: 16px; background: #f8fafc; padding: 12px; border-radius: 10px; border: 1px dashed #6366f1;">
                <label style="display: block; margin-bottom: 6px; font-size: 0.82rem; font-weight: 700; color: #4338ca;">
                    <i class="fa-solid fa-pen-nib"></i> Ketik Satuan Sendiri <?= $isMart ? '(Contoh: ikat, renteng, sak, rim, koli)' : '(Contoh: mangkok, piring, cup jumbo)' ?>
                </label>
                <input type="text" name="custom_unit" id="customUnitInput" placeholder="Masukkan nama satuan custom..." style="width: 100%; padding: 10px 14px; border: 1px solid #c7d2fe; border-radius: 8px; outline: none; font-size: 0.9rem; background: white;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-size: 0.85rem; font-weight: 700;">Harga Asli (Rp)</label>
                    <input type="number" name="price" id="menuPrice" required placeholder="Contoh: 25000" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 10px; outline: none; font-size: 0.9rem;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-size: 0.85rem; font-weight: 700; color: #10B981;">Harga Diskon (Opsional)</label>
                    <input type="number" name="discount_price" id="menuDiscountPrice" placeholder="Contoh: 18000" style="width: 100%; padding: 10px 14px; border: 1px solid #10B981; border-radius: 10px; outline: none; font-size: 0.9rem;">
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-size: 0.85rem; font-weight: 700;">Deskripsi (Opsional)</label>
                <textarea name="description" id="menuDescription" rows="2" placeholder="<?= $placeholderDesc ?>" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 10px; outline: none; font-size: 0.9rem;"></textarea>
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; margin-bottom: 6px; font-size: 0.85rem; font-weight: 700;"><?= $labelPhoto ?></label>
                <input type="file" name="photo" accept="image/*" style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 10px; font-size: 0.85rem;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeMenuModal()" style="padding: 10px 18px; border-radius: 10px; background: #f1f5f9; border: 1px solid var(--border-color); font-weight: 600; cursor: pointer;">Batal</button>
                <button type="submit" class="btn-primary" id="submitBtn"><?= $saveBtnText ?></button>
            </div>
        </form>
    </div>
</div>

<script>
    const presetUnits = <?= json_encode($currentUnits) ?>;

    function onUnitSelectChange(val) {
        const customDiv = document.getElementById('customUnitContainer');
        const customInput = document.getElementById('customUnitInput');
        if (val === 'custom') {
            customDiv.style.display = 'block';
            customInput.focus();
            customInput.required = true;
        } else {
            customDiv.style.display = 'none';
            customInput.required = false;
        }
    }

    function openAddMenuModal() {
        document.getElementById('modalTitle').innerText = '<?= $addBtnText ?>';
        document.getElementById('menuForm').action = '<?= base_url('merchant/menus/store') ?>';
        document.getElementById('menuName').value = '';
        document.getElementById('menuCategory').value = '<?= $currentCategories[0] ?>';
        document.getElementById('menuUnit').value = '<?= $currentUnits[0] ?>';
        document.getElementById('customUnitInput').value = '';
        document.getElementById('customUnitContainer').style.display = 'none';
        document.getElementById('customUnitInput').required = false;
        document.getElementById('menuPrice').value = '';
        document.getElementById('menuDiscountPrice').value = '';
        document.getElementById('menuDescription').value = '';
        document.getElementById('menuModal').style.display = 'flex';
    }

    function editMenu(menu) {
        document.getElementById('modalTitle').innerText = '<?= $isMart ? 'Ubah Produk Toko' : 'Ubah Menu Makanan' ?>';
        document.getElementById('menuForm').action = '<?= base_url('merchant/menus/update/') ?>/' + menu.id;
        document.getElementById('menuName').value = menu.name;
        
        // Set category
        if (menu.category) {
            const catSelect = document.getElementById('menuCategory');
            let foundCat = false;
            for (let i = 0; i < catSelect.options.length; i++) {
                if (catSelect.options[i].value.toLowerCase() === menu.category.toLowerCase()) {
                    catSelect.selectedIndex = i;
                    foundCat = true;
                    break;
                }
            }
            if (!foundCat) {
                // If custom category, append option
                const opt = document.createElement('option');
                opt.value = menu.category;
                opt.text = menu.category;
                catSelect.add(opt);
                catSelect.value = menu.category;
            }
        }

        // Set unit
        const rawUnit = (menu.unit || '<?= $currentUnits[0] ?>').trim();
        const lowerUnit = rawUnit.toLowerCase();
        const lowerPresets = presetUnits.map(u => u.toLowerCase());

        if (lowerPresets.includes(lowerUnit)) {
            document.getElementById('menuUnit').value = lowerUnit;
            document.getElementById('customUnitContainer').style.display = 'none';
            document.getElementById('customUnitInput').value = '';
            document.getElementById('customUnitInput').required = false;
        } else {
            document.getElementById('menuUnit').value = 'custom';
            document.getElementById('customUnitContainer').style.display = 'block';
            document.getElementById('customUnitInput').value = rawUnit;
            document.getElementById('customUnitInput').required = true;
        }

        document.getElementById('menuPrice').value = menu.price;
        document.getElementById('menuDiscountPrice').value = menu.discount_price || '';
        document.getElementById('menuDescription').value = menu.description || '';
        document.getElementById('menuModal').style.display = 'flex';
    }

    function closeMenuModal() {
        document.getElementById('menuModal').style.display = 'none';
    }

    function toggleMenuStock(menuId) {
        fetch(`<?= base_url('merchant/menus/toggle-availability/') ?>/${menuId}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 200) {
                    const label = document.getElementById('stock_text_' + menuId);
                    label.innerText = (res.is_available == 1) ? 'Tersedia' : 'Habis';
                }
            });
    }
</script>

<?= $this->endSection() ?>