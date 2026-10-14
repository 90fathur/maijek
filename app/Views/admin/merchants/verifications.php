<?= $this->extend('admin/layout/main') ?>

<?= $this->section('content') ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <div>
        <h1 class="page-title animate-fade" style="margin-bottom: 4px;">Verifikasi Mitra UMKM</h1>
        <span style="font-size: 0.875rem; color: var(--text-muted);">Persetujuan pendaftaran mitra baru untuk layanan MaiFood & MaiMart</span>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div style="background: rgba(16, 185, 129, 0.1); color: var(--primary); padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.875rem;">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<div class="card-table animate-fade">
    <!-- Toolbar Pencarian & Filter -->
    <div class="table-toolbar">
        <form method="get" action="<?= base_url('admin/merchant-verifications') ?>" class="toolbar-form">
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" name="q" value="<?= esc($q ?? '') ?>" placeholder="Cari nama toko, no. HP, alamat..." class="search-input">
                <?php if (!empty($q)): ?>
                    <a href="<?= base_url('admin/merchant-verifications') ?>" class="clear-search" title="Hapus pencarian"><i class="fa-solid fa-xmark"></i></a>
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
                    <option value="">Semua Status Antrean</option>
                    <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : '' ?>>Menunggu Verifikasi</option>
                    <option value="unverified" <?= ($status ?? '') === 'unverified' ? 'selected' : '' ?>>Belum Upload Dokumen</option>
                    <option value="rejected" <?= ($status ?? '') === 'rejected' ? 'selected' : '' ?>>Ditolak</option>
                    <option value="approved" <?= ($status ?? '') === 'approved' ? 'selected' : '' ?>>Sudah Disetujui</option>
                </select>
            </div>

            <button type="submit" class="btn-search">
                <i class="fa-solid fa-filter"></i> Filter
            </button>

            <?php if (!empty($q) || !empty($merchant_type) || !empty($status)): ?>
                <a href="<?= base_url('admin/merchant-verifications') ?>" class="btn-reset" title="Reset filter">
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
                    <th>Nama & Info Mitra</th>
                    <th>Layanan</th>
                    <th>Status</th>
                    <th>KTP Pemilik</th>
                    <th>Foto Tempat Usaha</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($merchants)): ?>
                    <?php foreach ($merchants as $merchant): ?>
                        <tr>
                            <td>
                                <strong><?= esc($merchant['name']) ?></strong><br>
                                <span style="font-size: 0.875rem; color: var(--text-muted);"><i class="fa-solid fa-phone"></i> <?= esc($merchant['phone']) ?></span><br>
                                <span style="font-size: 0.875rem; color: var(--text-muted);"><i class="fa-solid fa-location-dot"></i> <?= esc($merchant['address']) ?></span>
                            </td>
                            <td>
                                <?php if (($merchant['merchant_type'] ?? 'food') === 'mart'): ?>
                                    <div style="display: inline-flex; flex-direction: column; gap: 4px; align-items: flex-start;">
                                        <span style="background: rgba(147, 51, 234, 0.12); color: #9333ea; font-size: 0.75rem; padding: 3px 8px; border-radius: 6px; font-weight: 600; border: 1px solid rgba(147, 51, 234, 0.25);">
                                            <i class="fa-solid fa-cart-shopping"></i> MaiMart
                                        </span>
                                        <?php if (!empty($merchant['category'])): ?>
                                            <span style="background: rgba(59, 130, 246, 0.1); color: #2563eb; font-size: 0.7rem; padding: 2px 6px; border-radius: 4px; font-weight: 500;">
                                                <?= esc($merchant['category']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span style="background: rgba(234, 88, 12, 0.12); color: #ea580c; font-size: 0.75rem; padding: 3px 8px; border-radius: 6px; font-weight: 600; border: 1px solid rgba(234, 88, 12, 0.25);">
                                        <i class="fa-solid fa-utensils"></i> MaiFood
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($merchant['verification_status'] == 'pending'): ?>
                                    <span class="badge badge-warning">Menunggu</span>
                                <?php elseif ($merchant['verification_status'] == 'rejected'): ?>
                                    <span class="badge badge-danger">Ditolak</span>
                                <?php elseif ($merchant['verification_status'] == 'approved'): ?>
                                    <span class="badge badge-success">Disetujui</span>
                                <?php else: ?>
                                    <span class="badge" style="background:#f3f4f6; color:#4b5563;">Belum Upload</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($merchant['ktp_image']): ?>
                                    <a href="<?= base_url($merchant['ktp_image']) ?>" target="_blank" style="color: var(--primary); font-size: 0.875rem; font-weight: 500;"><i class="fa-solid fa-id-card"></i> Lihat KTP</a>
                                <?php else: ?>
                                    <span style="font-size: 0.875rem; color: var(--text-muted);">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($merchant['business_image']): ?>
                                    <a href="<?= base_url($merchant['business_image']) ?>" target="_blank" style="color: var(--primary); font-size: 0.875rem; font-weight: 500;"><i class="fa-solid fa-store"></i> Lihat Foto Usaha</a>
                                <?php else: ?>
                                    <span style="font-size: 0.875rem; color: var(--text-muted);">-</span>
                                <?php endif; ?>
                            </td>
                            <td style="display: flex; gap: 8px;">
                                <form action="<?= base_url('admin/merchant-verifications/verify/' . $merchant['id'] . '/approved') ?>" method="post" onsubmit="return confirm('Setujui verifikasi mitra ini? Toko akan langsung aktif.');">
                                    <button type="submit" style="padding: 6px 12px; background: var(--primary); color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.75rem; font-weight: 500;">
                                        <i class="fa-solid fa-check"></i> Setujui
                                    </button>
                                </form>
                                <form action="<?= base_url('admin/merchant-verifications/verify/' . $merchant['id'] . '/rejected') ?>" method="post" onsubmit="return confirm('Tolak pendaftaran mitra ini?');">
                                    <button type="submit" style="padding: 6px 12px; background: #ef4444; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.75rem; font-weight: 500;">
                                        <i class="fa-solid fa-xmark"></i> Tolak
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px;">
                            <i class="fa-solid fa-magnifying-glass" style="font-size: 1.5rem; opacity: 0.3; margin-bottom: 8px; display: block;"></i>
                            Tidak ada antrean verifikasi Mitra UMKM yang cocok dengan kriteria.
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
<?= $this->endSection() ?>