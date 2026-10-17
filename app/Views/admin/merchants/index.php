<?= $this->extend('admin/layout/main') ?>

<?= $this->section('content') ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h1 class="page-title animate-fade" style="margin-bottom: 0;">Manajemen Mitra UMKM</h1>
    <button onclick="document.getElementById('addMerchantModal').style.display='flex'" class="btn-primary animate-fade" style="animation-delay: 0.1s;">
        <i class="fa-solid fa-plus" style="margin-right: 8px;"></i> Tambah UMKM
    </button>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div style="background: rgba(16, 185, 129, 0.1); color: var(--primary); padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.875rem;">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.875rem;">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<div class="card-table animate-fade">
    <!-- Toolbar Pencarian & Filter -->
    <div class="table-toolbar">
        <form method="get" action="<?= base_url('admin/merchants') ?>" class="toolbar-form">
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" name="q" value="<?= esc($q ?? '') ?>" placeholder="Cari nama toko, alamat, no. HP..." class="search-input">
                <?php if (!empty($q)): ?>
                    <a href="<?= base_url('admin/merchants') ?>" class="clear-search" title="Hapus pencarian"><i class="fa-solid fa-xmark"></i></a>
                <?php endif; ?>
            </div>

            <div class="filter-box">
                <select name="merchant_type" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Layanan</option>
                    <option value="food" <?= ($merchant_type ?? '') === 'food' ? 'selected' : '' ?>>MaiFood (Makanan)</option>
                    <option value="mart" <?= ($merchant_type ?? '') === 'mart' ? 'selected' : '' ?>>MaiMart (Toko/Sembako)</option>
                </select>
            </div>

            <div class="filter-box">
                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="approved" <?= ($status ?? '') === 'approved' ? 'selected' : '' ?>>Aktif / Disetujui</option>
                    <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : '' ?>>Menunggu Verifikasi</option>
                    <option value="rejected" <?= ($status ?? '') === 'rejected' ? 'selected' : '' ?>>Ditolak</option>
                </select>
            </div>

            <button type="submit" class="btn-search">
                <i class="fa-solid fa-filter"></i> Filter
            </button>

            <?php if (!empty($q) || !empty($merchant_type) || !empty($status)): ?>
                <a href="<?= base_url('admin/merchants') ?>" class="btn-reset" title="Reset filter">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </a>
            <?php endif; ?>
        </form>

        <div class="toolbar-stats">
            Total: <b><?= number_format($totalCount ?? 0) ?></b> Mitra UMKM
        </div>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Merchant</th>
                    <th>No. HP</th>
                    <th>Alamat</th>
                    <th>Status Usaha</th>
                    <th>Status Verifikasi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($merchants)): ?>
                    <?php foreach ($merchants as $merchant): ?>
                        <tr>
                            <td>#UMKM-<?= str_pad($merchant['id'], 4, '0', STR_PAD_LEFT) ?></td>
                            <td style="display: flex; align-items: center; gap: 12px;">
                                <div style="position: relative; width: 44px; height: 44px; flex-shrink: 0;">
                                    <?php if (!empty($merchant['photo'])): ?>
                                        <img src="<?= base_url($merchant['photo']) ?>" alt="Foto" style="width: 44px; height: 44px; border-radius: 8px; object-fit: cover; border: 1px solid var(--border-color);">
                                    <?php else: ?>
                                        <div style="width: 44px; height: 44px; border-radius: 8px; background: #e5e7eb; display: flex; align-items: center; justify-content: center; color: #9ca3af;">
                                            <i class="fa-solid fa-store"></i>
                                        </div>
                                    <?php endif; ?>
                                    <!-- Tombol Ganti Foto -->
                                    <form action="<?= base_url('admin/merchants/updatePhoto/' . $merchant['id']) ?>" method="post" enctype="multipart/form-data" id="photoForm_<?= $merchant['id'] ?>" style="display: none;">
                                        <input type="file" name="photo" accept="image/*" onchange="document.getElementById('photoForm_<?= $merchant['id'] ?>').submit();">
                                    </form>
                                    <button type="button" onclick="document.querySelector('#photoForm_<?= $merchant['id'] ?> input').click();" title="Ganti Foto Toko" style="position: absolute; bottom: -4px; right: -4px; width: 20px; height: 20px; border-radius: 50%; background: var(--primary, #0ea5e9); color: white; border: 2px solid white; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 9px; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">
                                        <i class="fa-solid fa-camera"></i>
                                    </button>
                                </div>
                                <div style="display: flex; flex-direction: column;">
                                    <span style="font-weight: 600;"><?= esc($merchant['name']) ?></span>
                                    <div style="display: flex; gap: 4px; margin-top: 3px; align-items: center; flex-wrap: wrap;">
                                        <?php if (($merchant['merchant_type'] ?? 'food') === 'mart'): ?>
                                            <span style="font-size: 0.7rem; font-weight: 700; background: #FEF3C7; color: #D97706; border: 1px solid #FDE68A; padding: 1px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px;">
                                                <i class="fa-solid fa-basket-shopping" style="font-size: 0.65rem;"></i> MaiMart
                                            </span>
                                        <?php else: ?>
                                            <span style="font-size: 0.7rem; font-weight: 700; background: #DCFCE7; color: #15803D; border: 1px solid #BBF7D0; padding: 1px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px;">
                                                <i class="fa-solid fa-utensils" style="font-size: 0.65rem;"></i> MaiFood
                                            </span>
                                        <?php endif; ?>
                                        <span style="color: var(--text-muted); font-size: 0.75rem;"><?= esc($merchant['category'] ?? 'Umum') ?></span>

                                        <button type="button" onclick="openMerchantMenusModal(<?= $merchant['id'] ?>, '<?= esc($merchant['name'], 'js') ?>')" style="padding: 1px 6px; font-size: 0.7rem; font-weight: 600; border-radius: 4px; background: #EEF2FF; color: #4F46E5; border: 1px solid #C7D2FE; cursor: pointer; display: inline-flex; align-items: center; gap: 3px;" title="Lihat & Kelola Menu Toko Ini">
                                            <i class="fa-solid fa-bowl-food" style="font-size: 0.65rem;"></i> <?= (int)($merchant['total_menus'] ?? 0) ?> Menu
                                        </button>
                                    </div>
                                </div>
                            </td>
                            <td><?= esc($merchant['phone']) ?></td>
                            <td style="max-width: 250px; font-size: 0.8125rem;"><?= esc($merchant['address']) ?></td>
                            <td>
                                <form action="<?= base_url('admin/merchants/toggleOpen/' . $merchant['id']) ?>" method="post" style="margin: 0; display: inline-block;" onsubmit="return confirm('Ubah status operasional toko <?= esc($merchant['name'], 'js') ?> menjadi <?= $merchant['is_open'] == 1 ? 'TUTUP' : 'BUKA' ?>?');">
                                    <?= csrf_field() ?>
                                    <?php if ($merchant['is_open'] == 1): ?>
                                        <button type="submit" style="padding: 4px 10px; background: rgba(16, 185, 129, 0.15); color: #059669; border: 1px solid rgba(16, 185, 129, 0.35); border-radius: 6px; font-weight: 700; font-size: 0.75rem; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: all 0.15s;" onmouseover="this.style.background='rgba(16, 185, 129, 0.25)'" onmouseout="this.style.background='rgba(16, 185, 129, 0.15)'" title="Toko sedang Buka. Klik untuk Menutup.">
                                            <i class="fa-solid fa-store" style="font-size: 0.7rem;"></i> Buka
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" style="padding: 4px 10px; background: rgba(239, 68, 68, 0.15); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.35); border-radius: 6px; font-weight: 700; font-size: 0.75rem; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: all 0.15s;" onmouseover="this.style.background='rgba(239, 68, 68, 0.25)'" onmouseout="this.style.background='rgba(239, 68, 68, 0.15)'" title="Toko sedang Tutup. Klik untuk Membuka.">
                                            <i class="fa-solid fa-door-closed" style="font-size: 0.7rem;"></i> Tutup
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </td>
                            <td>
                                <?php if ($merchant['verification_status'] === 'approved'): ?>
                                    <span class="badge badge-success">Disetujui</span>
                                <?php elseif ($merchant['verification_status'] === 'rejected'): ?>
                                    <span class="badge badge-danger">Ditolak</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                                    <button type="button" onclick="openEditMerchantModal(<?= htmlspecialchars(json_encode($merchant), ENT_QUOTES, 'UTF-8') ?>)" style="padding: 6px 10px; background: #0284c7; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.82rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; transition: background 0.2s;" onmouseover="this.style.background='#0369a1'" onmouseout="this.style.background='#0284c7'" title="Edit Data & Kategori UMKM">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </button>

                                    <button type="button" onclick="openMerchantMenusModal(<?= $merchant['id'] ?>, '<?= esc($merchant['name'], 'js') ?>')" style="padding: 6px 10px; background: #4f46e5; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.82rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; transition: background 0.2s;" onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'" title="Kelola & Hapus Menu Toko">
                                        <i class="fa-solid fa-utensils"></i> Menu (<?= (int)($merchant['total_menus'] ?? 0) ?>)
                                    </button>

                                    <form action="<?= base_url('admin/merchants/changeStatus/' . $merchant['id']) ?>" method="post" style="display: flex; gap: 8px; margin: 0;">
                                        <select name="status" onchange="this.form.submit()" style="padding: 6px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 0.82rem; outline: none; cursor: pointer; background: var(--bg-body); color: var(--text-color);">
                                            <option value="" disabled selected>Status</option>
                                            <option value="approved">Aktif</option>
                                            <option value="suspended">Suspend</option>
                                        </select>
                                    </form>

                                    <form action="<?= base_url('admin/merchants/resetPassword/' . $merchant['id']) ?>" method="post" onsubmit="return confirm('Yakin ingin mereset password mitra ini?');" style="display: flex; gap: 4px; margin: 0;">
                                        <input type="text" name="new_password" placeholder="Pass Baru" required style="width: 80px; padding: 5px 6px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 0.8rem; outline: none; background: var(--bg-body); color: var(--text-color);">
                                        <button type="submit" style="padding: 5px 8px; background: #f59e0b; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.8rem; transition: background 0.2s;">
                                            Reset
                                        </button>
                                    </form>

                                    <!-- Tombol Hapus Merchant -->
                                    <form action="<?= base_url('admin/merchants/delete/' . $merchant['id']) ?>" method="post" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin menghapus mitra UMKM &quot;<?= esc($merchant['name'], 'js') ?>&quot; beserta seluruh menu-menunya secara permanen?\n\nSemua data menu dan berkas terkait akan dihapus secara permanen!');" style="margin: 0;">
                                        <button type="submit" style="padding: 6px 10px; background: #ef4444; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.82rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; transition: background 0.2s;" onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'" title="Hapus Mitra UMKM Ini">
                                            <i class="fa-solid fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 30px;">
                            <i class="fa-solid fa-magnifying-glass" style="font-size: 1.5rem; opacity: 0.3; margin-bottom: 8px; display: block;"></i>
                            Tidak ada data mitra UMKM yang cocok dengan kriteria pencarian.
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

<!-- Modal Tambah UMKM -->
<div id="addMerchantModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: var(--bg-card); padding: 32px; border-radius: 16px; width: 90%; max-width: 550px; box-shadow: var(--shadow-lg); border: 1px solid var(--border-color); position: relative; max-height: 90vh; overflow-y: auto;" class="animate-fade">
        <button type="button" onclick="document.getElementById('addMerchantModal').style.display='none'" style="position: absolute; top: 16px; right: 16px; background: none; border: none; font-size: 1.5rem; color: var(--text-muted); cursor: pointer;">&times;</button>
        <h2 style="margin-top: 0; margin-bottom: 20px; font-size: 1.25rem; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-plus-circle" style="color: var(--primary);"></i> Tambah Mitra UMKM Baru
        </h2>

        <form action="<?= base_url('admin/merchants/store') ?>" method="post" enctype="multipart/form-data">
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-size: 0.875rem; font-weight: 500;">Nama Usaha / Toko</label>
                <input type="text" name="name" required placeholder="Contoh: Apotek Sehat, Warung Makan..." style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-body); color: var(--text-color); outline: none;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-size: 0.875rem; font-weight: 500;">Jenis Kemitraan</label>
                    <select name="merchant_type" id="add_merchant_type" onchange="onAddTypeChange()" style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-body); color: var(--text-color); outline: none;">
                        <option value="food">MaiFood (Makanan/Kuliner)</option>
                        <option value="mart">MaiMart (Toko/Apotek/Sembako)</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-size: 0.875rem; font-weight: 500;">No. Handphone (Awali 08)</label>
                    <input type="number" name="phone" required placeholder="08..." style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-body); color: var(--text-color); outline: none;">
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; font-size: 0.875rem; font-weight: 500;">
                    <span>Kategori Usaha / Toko</span>
                    <span style="font-size: 0.72rem; color: var(--text-muted);">Ketik bebas atau klik rekomendasi</span>
                </label>
                <input type="text" name="category" id="add_category" list="allCategoryOptions" placeholder="Contoh: Apotek & Kesehatan, Makanan, Sembako..." style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-body); color: var(--text-color); outline: none;">
                <div style="margin-top: 6px; display: flex; flex-wrap: wrap; gap: 6px;" id="addQuickCatButtons"></div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-size: 0.875rem; font-weight: 500;">Foto Profil / Logo Toko (Opsional)</label>
                <input type="file" name="photo" accept="image/*" style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-body); color: var(--text-color); outline: none;">
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-size: 0.875rem; font-weight: 500;">Password Akun UMKM</label>
                <input type="text" name="password" required placeholder="Minimal 6 karakter" style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-body); color: var(--text-color); outline: none;">
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; margin-bottom: 6px; font-size: 0.875rem; font-weight: 500;">Alamat Lengkap Usaha</label>
                <textarea name="address" required rows="3" placeholder="Jl. Contoh No. 1, Kota..." style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-body); color: var(--text-color); outline: none;"></textarea>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="document.getElementById('addMerchantModal').style.display='none'" style="padding: 10px 20px; border-radius: 8px; background: var(--bg-body); color: var(--text-color); border: 1px solid var(--border-color); cursor: pointer;">Batal</button>
                <button type="submit" class="btn-primary" style="padding: 10px 22px; border-radius: 8px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-check"></i> Simpan UMKM
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Data & Kategori UMKM (Admin Feature) -->
<div id="editMerchantModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: var(--bg-card); padding: 32px; border-radius: 16px; width: 90%; max-width: 550px; box-shadow: var(--shadow-lg); border: 1px solid var(--border-color); position: relative; max-height: 90vh; overflow-y: auto;" class="animate-fade">
        <button type="button" onclick="document.getElementById('editMerchantModal').style.display='none'" style="position: absolute; top: 16px; right: 16px; background: none; border: none; font-size: 1.5rem; color: var(--text-muted); cursor: pointer;">&times;</button>
        <h2 style="margin-top: 0; margin-bottom: 4px; font-size: 1.25rem; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-pen-to-square" style="color: var(--primary);"></i> Edit Data & Kategori UMKM
        </h2>
        <p id="editMerchantSubtitle" style="margin-top: 0; margin-bottom: 18px; color: var(--text-muted); font-size: 0.85rem;"></p>

        <form id="editMerchantForm" action="" method="post" enctype="multipart/form-data">
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-size: 0.875rem; font-weight: 500;">Nama Usaha / Toko</label>
                <input type="text" name="name" id="edit_name" required style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-body); color: var(--text-color); outline: none;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-size: 0.875rem; font-weight: 500;">Layanan Kemitraan</label>
                    <select name="merchant_type" id="edit_merchant_type" onchange="onEditTypeChange()" style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-body); color: var(--text-color); outline: none;">
                        <option value="food">MaiFood (Makanan/Kuliner)</option>
                        <option value="mart">MaiMart (Toko/Apotek/Sembako)</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-size: 0.875rem; font-weight: 500;">Status Buka/Tutup</label>
                    <select name="is_open" id="edit_is_open" style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-body); color: var(--text-color); outline: none;">
                        <option value="1">Buka</option>
                        <option value="0">Tutup</option>
                    </select>
                </div>
            </div>

            <!-- Kategori UMKM (Bisa Dipilih & Bisa Diedit Bebas) -->
            <div style="margin-bottom: 16px;">
                <label style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; font-size: 0.875rem; font-weight: 500;">
                    <span>Kategori Usaha / Toko</span>
                    <span style="font-size: 0.72rem; color: var(--text-muted);">Bisa ketik bebas atau klik rekomendasi</span>
                </label>
                <input type="text" name="category" id="edit_category" list="allCategoryOptions" placeholder="Contoh: Apotek & Kesehatan, Makanan, Sembako..." style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-body); color: var(--text-color); outline: none;">
                
                <!-- Quick Category Buttons -->
                <div style="margin-top: 6px; display: flex; flex-wrap: wrap; gap: 6px;" id="editQuickCatButtons"></div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-size: 0.875rem; font-weight: 500;">No. Handphone</label>
                    <input type="text" name="phone" id="edit_phone" required style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-body); color: var(--text-color); outline: none;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-size: 0.875rem; font-weight: 500;">Status Verifikasi</label>
                    <select name="verification_status" id="edit_verification_status" style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-body); color: var(--text-color); outline: none;">
                        <option value="approved">Disetujui (Approved)</option>
                        <option value="pending">Menunggu Verifikasi</option>
                        <option value="rejected">Ditolak</option>
                        <option value="suspended">Disuspend</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-size: 0.875rem; font-weight: 500;">Ganti Foto Logo Toko (Opsional)</label>
                <input type="file" name="photo" accept="image/*" style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-body); color: var(--text-color); outline: none;">
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; margin-bottom: 6px; font-size: 0.875rem; font-weight: 500;">Alamat Lengkap</label>
                <textarea name="address" id="edit_address" rows="3" style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-body); color: var(--text-color); outline: none;"></textarea>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="document.getElementById('editMerchantModal').style.display='none'" style="padding: 10px 20px; border-radius: 8px; background: var(--bg-body); color: var(--text-color); border: 1px solid var(--border-color); cursor: pointer;">Batal</button>
                <button type="submit" class="btn-primary" style="padding: 10px 22px; border-radius: 8px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Datalist Rekomendasi Kategori -->
<datalist id="allCategoryOptions">
    <option value="Apotek & Kesehatan">
    <option value="Apotek">
    <option value="Makanan">
    <option value="Minuman">
    <option value="Sembako">
    <option value="Sayur & Buah">
    <option value="Daging & Ayam">
    <option value="Kebutuhan Bayi">
    <option value="Pet Shop">
    <option value="Rumah Tangga">
    <option value="Kopi & Cafe">
    <option value="Cemilan">
    <option value="Aneka Nasi">
    <option value="Fast Food">
    <option value="Lainnya">
</datalist>

<!-- Modal Kelola & Daftar Menu Merchant -->
<div id="merchantMenusModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: var(--bg-card); padding: 26px; border-radius: 16px; width: 92%; max-width: 840px; box-shadow: var(--shadow-lg); border: 1px solid var(--border-color); position: relative; max-height: 88vh; display: flex; flex-direction: column;" class="animate-fade">
        
        <!-- Modal Header -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid var(--border-color); flex-wrap: wrap; gap: 10px;">
            <div>
                <h2 style="margin: 0; font-size: 1.25rem; display: flex; align-items: center; gap: 8px; color: var(--text-color);">
                    <i class="fa-solid fa-utensils" style="color: #6366f1;"></i> Kelola Menu & Produk UMKM
                </h2>
                <p id="menuModalSubtitle" style="margin: 4px 0 0 0; color: var(--text-muted); font-size: 0.85rem;">
                    Memuat daftar menu...
                </p>
            </div>
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <!-- Tombol Tambah Menu Baru -->
                <button type="button" id="btnAddMenuModal" onclick="openAddMenuModal()" style="padding: 7px 14px; background: #10b981; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 0.82rem; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; transition: background 0.2s;" onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'" title="Tambah Menu / Produk Baru untuk Mitra Ini">
                    <i class="fa-solid fa-plus"></i> Tambah Menu
                </button>

                <!-- Form Hapus Semua Menu -->
                <form id="deleteAllMenusForm" action="" method="post" onsubmit="return confirm('PERINGATAN KERAS: Apakah Anda yakin ingin menghapus SEMUA menu milik merchant ini secara permanen?\n\nTindakan ini tidak dapat dibatalkan!');" style="margin: 0; display: none;">
                    <button type="submit" style="padding: 7px 12px; background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; border-radius: 8px; cursor: pointer; font-size: 0.82rem; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                        <i class="fa-solid fa-trash-can"></i> Hapus Semua
                    </button>
                </form>
                <button type="button" onclick="document.getElementById('merchantMenusModal').style.display='none'" style="background: none; border: none; font-size: 1.6rem; color: var(--text-muted); cursor: pointer; line-height: 1; padding: 0 4px;">&times;</button>
            </div>
        </div>

        <!-- Modal Body (Scrollable Table) -->
        <div id="menusModalContent" style="overflow-y: auto; flex: 1; min-height: 240px; padding-right: 4px;">
            <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                <i class="fa-solid fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 12px; color: var(--primary);"></i>
                <p style="margin: 0;">Sedang mengambil data menu...</p>
            </div>
        </div>

        <!-- Modal Footer -->
        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 14px; border-top: 1px solid var(--border-color); margin-top: 12px;">
            <span style="font-size: 0.78rem; color: var(--text-muted); display: flex; align-items: center; gap: 4px;">
                <i class="fa-solid fa-circle-info" style="color: #0284c7;"></i> Klik pada <b>Nama Menu</b>, <b>Foto</b>, atau tombol <b>Edit</b> untuk mengubah menu.
            </span>
            <button type="button" onclick="document.getElementById('merchantMenusModal').style.display='none'" style="padding: 7px 18px; border-radius: 8px; background: var(--bg-body); color: var(--text-color); border: 1px solid var(--border-color); cursor: pointer; font-size: 0.85rem;">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- Modal Edit Menu Spesifik (Admin) -->
<div id="editMenuModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 1100; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: var(--bg-card); padding: 26px; border-radius: 16px; width: 92%; max-width: 560px; box-shadow: var(--shadow-lg); border: 1px solid var(--border-color); position: relative; max-height: 90vh; overflow-y: auto;" class="animate-fade">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid var(--border-color);">
            <div>
                <h2 style="margin: 0; font-size: 1.15rem; display: flex; align-items: center; gap: 8px; color: var(--text-color);">
                    <i class="fa-solid fa-pen-to-square" style="color: #0284c7;"></i> Edit Menu / Produk
                </h2>
                <p id="editMenuSubtitle" style="margin: 3px 0 0 0; color: var(--text-muted); font-size: 0.8rem;"></p>
            </div>
            <button type="button" onclick="closeEditMenuModal()" style="background: none; border: none; font-size: 1.6rem; color: var(--text-muted); cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <form id="editMenuForm" onsubmit="submitEditMenuForm(event)" enctype="multipart/form-data">
            <input type="hidden" id="edit_menu_id" name="menu_id">

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 5px; color: var(--text-color);">Nama Menu / Produk <span style="color: #ef4444;">*</span></label>
                <input type="text" id="edit_menu_name" name="name" required style="width: 100%; padding: 9px 12px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-body); color: var(--text-color); font-size: 0.875rem; box-sizing: border-box;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 5px; color: var(--text-color);">Kategori Menu</label>
                    <input type="text" id="edit_menu_category" name="category" list="allCategoryOptions" placeholder="Cth: Makanan, Minuman" style="width: 100%; padding: 9px 12px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-body); color: var(--text-color); font-size: 0.875rem; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 5px; color: var(--text-color);">Satuan (Unit)</label>
                    <input type="text" id="edit_menu_unit" name="unit" placeholder="porsi, pcs, cup, botol" style="width: 100%; padding: 9px 12px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-body); color: var(--text-color); font-size: 0.875rem; box-sizing: border-box;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 5px; color: var(--text-color);">Harga Normal (Rp) <span style="color: #ef4444;">*</span></label>
                    <input type="number" id="edit_menu_price" name="price" required min="0" step="100" style="width: 100%; padding: 9px 12px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-body); color: var(--text-color); font-size: 0.875rem; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 5px; color: var(--text-color);">Harga Diskon / Promo (Rp)</label>
                    <input type="number" id="edit_menu_discount_price" name="discount_price" min="0" step="100" placeholder="Kosongkan jika tidak promo" style="width: 100%; padding: 9px 12px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-body); color: var(--text-color); font-size: 0.875rem; box-sizing: border-box;">
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 5px; color: var(--text-color);">Status Ketersediaan</label>
                <select id="edit_menu_is_available" name="is_available" style="width: 100%; padding: 9px 12px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-body); color: var(--text-color); font-size: 0.875rem; box-sizing: border-box; cursor: pointer;">
                    <option value="1">🟢 Tersedia (Bisa Dipesan Pembeli)</option>
                    <option value="0">🔴 Habis / Kosong (Stok Habis)</option>
                </select>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 5px; color: var(--text-color);">Deskripsi Menu</label>
                <textarea id="edit_menu_description" name="description" rows="2" placeholder="Keterangan rasa, topping, porsi..." style="width: 100%; padding: 9px 12px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-body); color: var(--text-color); font-size: 0.875rem; box-sizing: border-box; resize: vertical;"></textarea>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 5px; color: var(--text-color);">Foto Menu</label>
                <div style="display: flex; gap: 12px; align-items: center;">
                    <div id="edit_menu_photo_preview" style="width: 58px; height: 58px; border-radius: 8px; border: 1px solid var(--border-color); background: #e5e7eb; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                        <i class="fa-solid fa-bowl-food" style="color: #9ca3af; font-size: 1.3rem;"></i>
                    </div>
                    <div style="flex: 1;">
                        <input type="file" id="edit_menu_photo_input" name="photo" accept="image/*" onchange="previewMenuPhoto(this, 'edit_menu_photo_preview')" style="width: 100%; font-size: 0.8125rem;">
                        <small style="color: var(--text-muted); font-size: 0.72rem; display: block; margin-top: 3px;">Format: JPG, PNG, WebP (Maks 3MB). Biarkan kosong jika tidak mengganti foto.</small>
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 8px; padding-top: 14px; border-top: 1px solid var(--border-color);">
                <button type="button" onclick="closeEditMenuModal()" style="padding: 8px 18px; border-radius: 8px; background: var(--bg-body); color: var(--text-color); border: 1px solid var(--border-color); cursor: pointer; font-size: 0.875rem;">
                    Batal
                </button>
                <button type="submit" id="btnSubmitEditMenu" style="padding: 8px 22px; border-radius: 8px; background: #0284c7; color: white; border: none; font-weight: 600; cursor: pointer; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 6px; transition: background 0.2s;" onmouseover="this.style.background='#0369a1'" onmouseout="this.style.background='#0284c7'">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Menu Baru (Admin) -->
<div id="addMenuModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 1100; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: var(--bg-card); padding: 26px; border-radius: 16px; width: 92%; max-width: 560px; box-shadow: var(--shadow-lg); border: 1px solid var(--border-color); position: relative; max-height: 90vh; overflow-y: auto;" class="animate-fade">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid var(--border-color);">
            <div>
                <h2 style="margin: 0; font-size: 1.15rem; display: flex; align-items: center; gap: 8px; color: var(--text-color);">
                    <i class="fa-solid fa-plus-circle" style="color: #10b981;"></i> Tambah Menu Baru
                </h2>
                <p id="addMenuSubtitle" style="margin: 3px 0 0 0; color: var(--text-muted); font-size: 0.8rem;"></p>
            </div>
            <button type="button" onclick="closeAddMenuModal()" style="background: none; border: none; font-size: 1.6rem; color: var(--text-muted); cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <form id="addMenuForm" onsubmit="submitAddMenuForm(event)" enctype="multipart/form-data">
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 5px; color: var(--text-color);">Nama Menu / Produk <span style="color: #ef4444;">*</span></label>
                <input type="text" id="add_menu_name" name="name" required placeholder="Cth: Ayam Geprek Sambal Matah" style="width: 100%; padding: 9px 12px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-body); color: var(--text-color); font-size: 0.875rem; box-sizing: border-box;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 5px; color: var(--text-color);">Kategori Menu</label>
                    <input type="text" id="add_menu_category" name="category" list="allCategoryOptions" placeholder="Cth: Makanan, Minuman" style="width: 100%; padding: 9px 12px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-body); color: var(--text-color); font-size: 0.875rem; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 5px; color: var(--text-color);">Satuan (Unit)</label>
                    <input type="text" id="add_menu_unit" name="unit" placeholder="porsi, pcs, cup" value="porsi" style="width: 100%; padding: 9px 12px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-body); color: var(--text-color); font-size: 0.875rem; box-sizing: border-box;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 5px; color: var(--text-color);">Harga Normal (Rp) <span style="color: #ef4444;">*</span></label>
                    <input type="number" id="add_menu_price" name="price" required min="0" step="100" placeholder="0" style="width: 100%; padding: 9px 12px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-body); color: var(--text-color); font-size: 0.875rem; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 5px; color: var(--text-color);">Harga Diskon / Promo (Rp)</label>
                    <input type="number" id="add_menu_discount_price" name="discount_price" min="0" step="100" placeholder="Kosongkan jika tidak promo" style="width: 100%; padding: 9px 12px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-body); color: var(--text-color); font-size: 0.875rem; box-sizing: border-box;">
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 5px; color: var(--text-color);">Status Ketersediaan</label>
                <select id="add_menu_is_available" name="is_available" style="width: 100%; padding: 9px 12px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-body); color: var(--text-color); font-size: 0.875rem; box-sizing: border-box; cursor: pointer;">
                    <option value="1" selected>🟢 Tersedia (Bisa Dipesan Pembeli)</option>
                    <option value="0">🔴 Habis / Kosong (Stok Habis)</option>
                </select>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 5px; color: var(--text-color);">Deskripsi Menu</label>
                <textarea id="add_menu_description" name="description" rows="2" placeholder="Keterangan rasa, topping, porsi..." style="width: 100%; padding: 9px 12px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-body); color: var(--text-color); font-size: 0.875rem; box-sizing: border-box; resize: vertical;"></textarea>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 5px; color: var(--text-color);">Foto Menu (Opsional)</label>
                <div style="display: flex; gap: 12px; align-items: center;">
                    <div id="add_menu_photo_preview" style="width: 58px; height: 58px; border-radius: 8px; border: 1px solid var(--border-color); background: #e5e7eb; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                        <i class="fa-solid fa-bowl-food" style="color: #9ca3af; font-size: 1.3rem;"></i>
                    </div>
                    <div style="flex: 1;">
                        <input type="file" id="add_menu_photo_input" name="photo" accept="image/*" onchange="previewMenuPhoto(this, 'add_menu_photo_preview')" style="width: 100%; font-size: 0.8125rem;">
                        <small style="color: var(--text-muted); font-size: 0.72rem; display: block; margin-top: 3px;">Format: JPG, PNG, WebP (Maks 3MB).</small>
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 8px; padding-top: 14px; border-top: 1px solid var(--border-color);">
                <button type="button" onclick="closeAddMenuModal()" style="padding: 8px 18px; border-radius: 8px; background: var(--bg-body); color: var(--text-color); border: 1px solid var(--border-color); cursor: pointer; font-size: 0.875rem;">
                    Batal
                </button>
                <button type="submit" id="btnSubmitAddMenu" style="padding: 8px 22px; border-radius: 8px; background: #10b981; color: white; border: none; font-weight: 600; cursor: pointer; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 6px; transition: background 0.2s;" onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'">
                    <i class="fa-solid fa-plus"></i> Tambah Menu
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Floating Notification Toast -->
<div id="adminMenuToast" style="display: none; position: fixed; top: 24px; right: 24px; z-index: 9999; padding: 12px 20px; border-radius: 10px; color: white; font-size: 0.875rem; font-weight: 600; box-shadow: 0 10px 25px rgba(0,0,0,0.15); display: none; align-items: center; gap: 10px; transition: all 0.3s ease;">
    <i id="adminMenuToastIcon" class="fa-solid fa-check-circle" style="font-size: 1.1rem;"></i>
    <span id="adminMenuToastMsg">Berhasil</span>
</div>

<script>
var foodCategories = ['Makanan', 'Minuman', 'Cemilan', 'Aneka Nasi', 'Kopi & Cafe', 'Fast Food', 'Umum'];
var martCategories = ['Apotek & Kesehatan', 'Apotek', 'Sayur & Buah', 'Daging & Ayam', 'Sembako', 'Kebutuhan Bayi', 'Pet Shop', 'Rumah Tangga', 'Lainnya'];

var currentActiveMerchantId = null;
var currentActiveMerchantName = '';

function showToast(message, type) {
    var toast = document.getElementById('adminMenuToast');
    var icon = document.getElementById('adminMenuToastIcon');
    var msg = document.getElementById('adminMenuToastMsg');

    msg.innerText = message;
    if (type === 'error') {
        toast.style.background = '#ef4444';
        icon.className = 'fa-solid fa-circle-exclamation';
    } else {
        toast.style.background = '#10b981';
        icon.className = 'fa-solid fa-check-circle';
    }

    toast.style.display = 'flex';
    toast.style.opacity = '1';
    toast.style.transform = 'translateY(0)';

    setTimeout(function() {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-10px)';
        setTimeout(function() { toast.style.display = 'none'; }, 300);
    }, 3500);
}

function renderQuickButtons(type, inputId, containerId) {
    var container = document.getElementById(containerId);
    if (!container) return;
    var cats = (type === 'mart') ? martCategories : foodCategories;
    var html = '';
    cats.forEach(function(c) {
        html += '<button type="button" onclick="document.getElementById(\'' + inputId + '\').value=\'' + c + '\'" style="font-size: 0.72rem; padding: 3px 9px; border-radius: 12px; border: 1px solid var(--border-color); background: var(--bg-body); color: var(--text-color); cursor: pointer; transition: all 0.15s;" onmouseover="this.style.borderColor=\'var(--primary)\'" onmouseout="this.style.borderColor=\'var(--border-color)\'">' + c + '</button>';
    });
    container.innerHTML = html;
}

function onAddTypeChange() {
    var type = document.getElementById('add_merchant_type').value;
    renderQuickButtons(type, 'add_category', 'addQuickCatButtons');
}

function onEditTypeChange() {
    var type = document.getElementById('edit_merchant_type').value;
    renderQuickButtons(type, 'edit_category', 'editQuickCatButtons');
}

function openEditMerchantModal(merchant) {
    document.getElementById('editMerchantForm').action = '<?= base_url('admin/merchants/update/') ?>/' + merchant.id;
    document.getElementById('editMerchantSubtitle').innerText = '#UMKM-' + String(merchant.id).padStart(4, '0') + ' — ' + (merchant.name || '');
    document.getElementById('edit_name').value = merchant.name || '';
    document.getElementById('edit_merchant_type').value = merchant.merchant_type || 'food';
    document.getElementById('edit_is_open').value = merchant.is_open ?? 1;
    document.getElementById('edit_category').value = merchant.category || '';
    document.getElementById('edit_phone').value = merchant.phone || '';
    document.getElementById('edit_verification_status').value = merchant.verification_status || 'approved';
    document.getElementById('edit_address').value = merchant.address || '';
    
    renderQuickButtons(merchant.merchant_type || 'food', 'edit_category', 'editQuickCatButtons');
    document.getElementById('editMerchantModal').style.display = 'flex';
}

function openMerchantMenusModal(merchantId, merchantName) {
    currentActiveMerchantId = merchantId;
    currentActiveMerchantName = merchantName;

    var modal = document.getElementById('merchantMenusModal');
    var subtitle = document.getElementById('menuModalSubtitle');
    var deleteAllForm = document.getElementById('deleteAllMenusForm');

    modal.style.display = 'flex';
    subtitle.innerText = merchantName + ' (#UMKM-' + String(merchantId).padStart(4, '0') + ')';
    deleteAllForm.action = '<?= base_url('admin/merchants/deleteAllMenus/') ?>/' + merchantId;
    deleteAllForm.style.display = 'none';

    refreshMenusModal();
}

function refreshMenusModal() {
    if (!currentActiveMerchantId) return;

    var subtitle = document.getElementById('menuModalSubtitle');
    var content = document.getElementById('menusModalContent');
    var deleteAllForm = document.getElementById('deleteAllMenusForm');

    content.innerHTML = '<div style="text-align: center; padding: 40px; color: var(--text-muted);"><i class="fa-solid fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 12px; color: var(--primary);"></i><p style="margin: 0;">Sedang mengambil data menu...</p></div>';

    fetch('<?= base_url('admin/merchants/menus/') ?>/' + currentActiveMerchantId)
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.status !== 200) {
                content.innerHTML = '<div style="text-align: center; padding: 40px; color: #ef4444;"><i class="fa-solid fa-circle-exclamation" style="font-size: 2rem; margin-bottom: 10px;"></i><p>' + (data.message || 'Gagal memuat menu') + '</p></div>';
                return;
            }

            var menus = data.menus || [];
            subtitle.innerText = currentActiveMerchantName + ' (#UMKM-' + String(currentActiveMerchantId).padStart(4, '0') + ') — ' + menus.length + ' Menu';

            if (menus.length === 0) {
                content.innerHTML = '<div style="text-align: center; padding: 40px; color: var(--text-muted);"><i class="fa-solid fa-utensils" style="font-size: 2rem; opacity: 0.3; margin-bottom: 10px; display: block;"></i><p style="margin: 0; font-weight: 500;">Belum ada menu yang terdaftar untuk mitra UMKM ini.</p><p style="font-size: 0.8rem; margin-top: 6px;">Klik tombol <b>"+ Tambah Menu"</b> di atas untuk menambahkan menu baru.</p></div>';
                deleteAllForm.style.display = 'none';
                return;
            }

            deleteAllForm.style.display = 'inline-block';

            var html = '<table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">';
            html += '<thead><tr style="border-bottom: 2px solid var(--border-color); text-align: left;">';
            html += '<th style="padding: 10px 8px; width: 50px;">Foto</th>';
            html += '<th style="padding: 10px 8px;">Nama Menu</th>';
            html += '<th style="padding: 10px 8px;">Kategori</th>';
            html += '<th style="padding: 10px 8px;">Harga</th>';
            html += '<th style="padding: 10px 8px;">Status</th>';
            html += '<th style="padding: 10px 8px; text-align: right; width: 150px;">Aksi</th>';
            html += '</tr></thead><tbody>';

            menus.forEach(function(m) {
                var menuJson = JSON.stringify(m).replace(/"/g, '&quot;');
                var photoHtml = m.photo 
                    ? '<img src="<?= base_url() ?>/' + m.photo + '" alt="" style="width: 44px; height: 44px; border-radius: 8px; object-fit: cover; border: 1px solid var(--border-color); cursor: pointer;" onclick="openEditMenuModal(' + menuJson + ')" title="Klik untuk edit menu">'
                    : '<div onclick="openEditMenuModal(' + menuJson + ')" style="width: 44px; height: 44px; border-radius: 8px; background: #e5e7eb; display: flex; align-items: center; justify-content: center; color: #9ca3af; font-size: 0.9rem; cursor: pointer;" title="Klik untuk edit menu"><i class="fa-solid fa-bowl-food"></i></div>';
                
                var priceFormatted = 'Rp ' + Number(m.price || 0).toLocaleString('id-ID');
                if (m.discount_price && Number(m.discount_price) > 0 && Number(m.discount_price) < Number(m.price)) {
                    priceFormatted = '<span style="text-decoration: line-through; color: var(--text-muted); font-size: 0.75rem; display: block;">Rp ' + Number(m.price).toLocaleString('id-ID') + '</span><span style="color: #10b981; font-weight: 700;">Rp ' + Number(m.discount_price).toLocaleString('id-ID') + '</span>';
                }

                var statusBadge = (m.is_available == 1) 
                    ? '<span style="font-size: 0.72rem; padding: 2px 7px; border-radius: 4px; background: #dcfce7; color: #166534; font-weight: 600;">Tersedia</span>'
                    : '<span style="font-size: 0.72rem; padding: 2px 7px; border-radius: 4px; background: #fee2e2; color: #991b1b; font-weight: 600;">Habis</span>';

                html += '<tr style="border-bottom: 1px solid var(--border-color); transition: background 0.15s;" onmouseover="this.style.background=\'rgba(0,0,0,0.02)\'" onmouseout="this.style.background=\'transparent\'">';
                html += '<td style="padding: 10px 8px;">' + photoHtml + '</td>';
                html += '<td style="padding: 10px 8px;">';
                html += '<a href="javascript:void(0)" onclick="openEditMenuModal(' + menuJson + ')" style="font-weight: 600; color: var(--text-color); text-decoration: none;" onmouseover="this.style.color=\'var(--primary)\'" onmouseout="this.style.color=\'var(--text-color)\'" title="Klik untuk edit menu">' + escapeHtml(m.name) + '</a>';
                if (m.unit) {
                    html += ' <span style="font-size: 0.72rem; padding: 1px 5px; border-radius: 4px; background: rgba(0,0,0,0.05); color: var(--text-muted);">/' + escapeHtml(m.unit) + '</span>';
                }
                if (m.description) {
                    html += '<div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px; max-width: 260px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">' + escapeHtml(m.description) + '</div>';
                }
                html += '</td>';
                html += '<td style="padding: 10px 8px;"><span style="font-size: 0.75rem; padding: 2px 6px; border-radius: 4px; background: #e0f2fe; color: #0369a1; font-weight: 600;">' + escapeHtml(m.category || 'Umum') + '</span></td>';
                html += '<td style="padding: 10px 8px; font-weight: 600;">' + priceFormatted + '</td>';
                html += '<td style="padding: 10px 8px;">' + statusBadge + '</td>';
                html += '<td style="padding: 10px 8px; text-align: right;">';
                html += '<div style="display: inline-flex; gap: 6px; align-items: center;">';
                html += '<button type="button" onclick="openEditMenuModal(' + menuJson + ')" style="padding: 5px 10px; background: #0284c7; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.78rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; transition: background 0.2s;" onmouseover="this.style.background=\'#0369a1\'" onmouseout="this.style.background=\'#0284c7\'" title="Edit Menu Ini"><i class="fa-solid fa-pen-to-square"></i> Edit</button>';
                html += '<button type="button" onclick="deleteMenuAjax(' + m.id + ', \'' + escapeHtml(m.name) + '\')" style="padding: 5px 10px; background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; border-radius: 6px; cursor: pointer; font-size: 0.78rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; transition: background 0.2s;" onmouseover="this.style.background=\'#fecaca\'" onmouseout="this.style.background=\'#fee2e2\'" title="Hapus Menu Ini"><i class="fa-solid fa-trash"></i> Hapus</button>';
                html += '</div>';
                html += '</td>';
                html += '</tr>';
            });

            html += '</tbody></table>';
            content.innerHTML = html;
        })
        .catch(function(err) {
            content.innerHTML = '<div style="text-align: center; padding: 40px; color: #ef4444;"><p>Gagal menghubungi server untuk memuat menu.</p></div>';
        });
}

function openEditMenuModal(menu) {
    document.getElementById('edit_menu_id').value = menu.id;
    document.getElementById('edit_menu_name').value = menu.name || '';
    document.getElementById('edit_menu_category').value = menu.category || '';
    document.getElementById('edit_menu_unit').value = menu.unit || 'pcs';
    document.getElementById('edit_menu_price').value = menu.price || 0;
    document.getElementById('edit_menu_discount_price').value = (menu.discount_price && Number(menu.discount_price) > 0) ? menu.discount_price : '';
    document.getElementById('edit_menu_is_available').value = (menu.is_available == 1 || menu.is_available === '1') ? '1' : '0';
    document.getElementById('edit_menu_description').value = menu.description || '';
    document.getElementById('edit_menu_photo_input').value = '';

    document.getElementById('editMenuSubtitle').innerText = currentActiveMerchantName + ' — ' + (menu.name || '');

    var previewBox = document.getElementById('edit_menu_photo_preview');
    if (menu.photo) {
        previewBox.innerHTML = '<img src="<?= base_url() ?>/' + menu.photo + '" style="width: 100%; height: 100%; object-fit: cover;">';
    } else {
        previewBox.innerHTML = '<i class="fa-solid fa-bowl-food" style="color: #9ca3af; font-size: 1.3rem;"></i>';
    }

    document.getElementById('editMenuModal').style.display = 'flex';
}

function closeEditMenuModal() {
    document.getElementById('editMenuModal').style.display = 'none';
}

function submitEditMenuForm(e) {
    e.preventDefault();
    var menuId = document.getElementById('edit_menu_id').value;
    var btn = document.getElementById('btnSubmitEditMenu');
    var form = document.getElementById('editMenuForm');
    var formData = new FormData(form);

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';

    fetch('<?= base_url('admin/merchants/updateMenu/') ?>/' + menuId, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan';

        if (data.status === 200) {
            closeEditMenuModal();
            showToast(data.message || 'Menu berhasil diperbarui!', 'success');
            refreshMenusModal();
        } else {
            alert('Gagal: ' + (data.message || 'Terjadi kesalahan saat menyimpan'));
        }
    })
    .catch(function(err) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan';
        alert('Terjadi kesalahan koneksi ke server.');
    });
}

function openAddMenuModal() {
    document.getElementById('addMenuForm').reset();
    document.getElementById('add_menu_photo_input').value = '';
    document.getElementById('add_menu_photo_preview').innerHTML = '<i class="fa-solid fa-bowl-food" style="color: #9ca3af; font-size: 1.3rem;"></i>';
    document.getElementById('addMenuSubtitle').innerText = currentActiveMerchantName;
    document.getElementById('addMenuModal').style.display = 'flex';
}

function closeAddMenuModal() {
    document.getElementById('addMenuModal').style.display = 'none';
}

function submitAddMenuForm(e) {
    e.preventDefault();
    if (!currentActiveMerchantId) return;

    var btn = document.getElementById('btnSubmitAddMenu');
    var form = document.getElementById('addMenuForm');
    var formData = new FormData(form);

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menambahkan...';

    fetch('<?= base_url('admin/merchants/addMenu/') ?>/' + currentActiveMerchantId, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-plus"></i> Tambah Menu';

        if (data.status === 200) {
            closeAddMenuModal();
            showToast(data.message || 'Menu baru berhasil ditambahkan!', 'success');
            refreshMenusModal();
        } else {
            alert('Gagal: ' + (data.message || 'Terjadi kesalahan saat menambah menu'));
        }
    })
    .catch(function(err) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-plus"></i> Tambah Menu';
        alert('Terjadi kesalahan koneksi ke server.');
    });
}

function deleteMenuAjax(menuId, menuName) {
    if (!confirm('Yakin ingin menghapus menu "' + menuName + '" ini secara permanen?')) {
        return;
    }

    fetch('<?= base_url('admin/merchants/deleteMenu/') ?>/' + menuId, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.status === 200) {
            showToast(data.message || 'Menu berhasil dihapus.', 'success');
            refreshMenusModal();
        } else {
            alert('Gagal: ' + (data.message || 'Terjadi kesalahan saat menghapus menu'));
        }
    })
    .catch(function(err) {
        alert('Terjadi kesalahan koneksi ke server.');
    });
}

function previewMenuPhoto(input, previewContainerId) {
    var container = document.getElementById(previewContainerId);
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            container.innerHTML = '<img src="' + e.target.result + '" style="width: 100%; height: 100%; object-fit: cover;">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

// Inisialisasi awal kategori di modal Tambah Merchant
document.addEventListener('DOMContentLoaded', function() {
    renderQuickButtons('food', 'add_category', 'addQuickCatButtons');
});
</script>

<?= $this->endSection() ?>