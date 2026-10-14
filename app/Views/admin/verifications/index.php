<?= $this->extend('admin/layout/main') ?>

<?= $this->section('content') ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h1 class="page-title animate-fade" style="margin-bottom: 0;">Verifikasi Dokumen (KYC)</h1>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div style="background: rgba(16, 185, 129, 0.1); color: var(--primary); padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.875rem;">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<div class="card-table animate-fade">
    <!-- Toolbar Pencarian & Filter -->
    <div class="table-toolbar">
        <form method="get" action="<?= base_url('admin/verifications') ?>" class="toolbar-form">
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" name="q" value="<?= esc($q ?? '') ?>" placeholder="Cari nama, plat nomor, no. HP..." class="search-input">
                <?php if (!empty($q)): ?>
                    <a href="<?= base_url('admin/verifications') ?>" class="clear-search" title="Hapus pencarian"><i class="fa-solid fa-xmark"></i></a>
                <?php endif; ?>
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

            <?php if (!empty($q) || !empty($status)): ?>
                <a href="<?= base_url('admin/verifications') ?>" class="btn-reset" title="Reset filter">
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
                    <th>Nama & Plat</th>
                    <th>Status</th>
                    <th>Dokumen KTP</th>
                    <th>Dokumen SIM</th>
                    <th>Dokumen STNK</th>
                    <th>Kendaraan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($drivers)): ?>
                    <?php foreach ($drivers as $driver): ?>
                        <tr>
                            <td>
                                <strong><?= esc($driver['name']) ?></strong><br>
                                <span style="font-size: 0.875rem; color: var(--text-muted);"><?= esc($driver['vehicle_plate']) ?> (<?= esc($driver['vehicle_type']) ?>)</span>
                            </td>
                            <td>
                                <?php if ($driver['verification_status'] == 'pending'): ?>
                                    <span class="badge badge-warning">Menunggu</span>
                                <?php elseif ($driver['verification_status'] == 'rejected'): ?>
                                    <span class="badge badge-danger">Ditolak</span>
                                <?php elseif ($driver['verification_status'] == 'approved'): ?>
                                    <span class="badge badge-success">Disetujui</span>
                                <?php else: ?>
                                    <span class="badge" style="background:#f3f4f6; color:#4b5563;">Belum Upload</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($driver['ktp_image']): ?>
                                    <a href="<?= base_url($driver['ktp_image']) ?>" target="_blank" style="color: var(--primary); font-size: 0.875rem;"><i class="fa-solid fa-image"></i> Lihat KTP</a>
                                <?php else: ?>
                                    <span style="font-size: 0.875rem; color: var(--text-muted);">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($driver['sim_image']): ?>
                                    <a href="<?= base_url($driver['sim_image']) ?>" target="_blank" style="color: var(--primary); font-size: 0.875rem;"><i class="fa-solid fa-image"></i> Lihat SIM</a>
                                <?php else: ?>
                                    <span style="font-size: 0.875rem; color: var(--text-muted);">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($driver['stnk_image']): ?>
                                    <a href="<?= base_url($driver['stnk_image']) ?>" target="_blank" style="color: var(--primary); font-size: 0.875rem;"><i class="fa-solid fa-image"></i> Lihat STNK</a>
                                <?php else: ?>
                                    <span style="font-size: 0.875rem; color: var(--text-muted);">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($driver['vehicle_image']): ?>
                                    <a href="<?= base_url($driver['vehicle_image']) ?>" target="_blank" style="color: var(--primary); font-size: 0.875rem;"><i class="fa-solid fa-image"></i> Kendaraan</a>
                                <?php else: ?>
                                    <span style="font-size: 0.875rem; color: var(--text-muted);">-</span>
                                <?php endif; ?>
                            </td>
                            <td style="display: flex; gap: 8px;">
                                <form action="<?= base_url('admin/verifications/verify/' . $driver['id'] . '/approved') ?>" method="post" onsubmit="return confirm('Setujui dokumen ini?');">
                                    <button type="submit" style="padding: 6px 12px; background: var(--primary); color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.75rem;">
                                        <i class="fa-solid fa-check"></i> Setujui
                                    </button>
                                </form>
                                <form action="<?= base_url('admin/verifications/verify/' . $driver['id'] . '/rejected') ?>" method="post" onsubmit="return confirm('Tolak dokumen ini?');">
                                    <button type="submit" style="padding: 6px 12px; background: #ef4444; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.75rem;">
                                        <i class="fa-solid fa-xmark"></i> Tolak
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 30px;">
                            <i class="fa-solid fa-magnifying-glass" style="font-size: 1.5rem; opacity: 0.3; margin-bottom: 8px; display: block;"></i>
                            Tidak ada antrean verifikasi pengemudi yang cocok dengan kriteria.
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