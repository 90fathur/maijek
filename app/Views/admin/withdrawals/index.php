<?= $this->extend('admin/layout/main') ?>

<?= $this->section('content') ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h1 class="page-title animate-fade" style="margin-bottom: 0;">Pencairan Saldo (Driver & Penumpang)</h1>
    <a href="<?= base_url('admin/withdrawals/export') ?>" style="padding: 10px 16px; background: var(--primary); color: white; border-radius: 8px; text-decoration: none; font-size: 0.875rem;">
        <i class="fa-solid fa-file-csv"></i> Export CSV
    </a>
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
        <form method="get" action="<?= base_url('admin/withdrawals') ?>" class="toolbar-form">
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" name="q" value="<?= esc($q ?? '') ?>" placeholder="Cari nama, rekening, bank, no. HP..." class="search-input">
                <?php if (!empty($q)): ?>
                    <a href="<?= base_url('admin/withdrawals') ?>" class="clear-search" title="Hapus pencarian"><i class="fa-solid fa-xmark"></i></a>
                <?php endif; ?>
            </div>

            <div class="filter-box">
                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : '' ?>>Menunggu Transfer</option>
                    <option value="approved" <?= ($status ?? '') === 'approved' ? 'selected' : '' ?>>Selesai / Ditransfer</option>
                    <option value="rejected" <?= ($status ?? '') === 'rejected' ? 'selected' : '' ?>>Ditolak</option>
                </select>
            </div>

            <button type="submit" class="btn-search">
                <i class="fa-solid fa-filter"></i> Filter
            </button>

            <?php if (!empty($q) || !empty($status)): ?>
                <a href="<?= base_url('admin/withdrawals') ?>" class="btn-reset" title="Reset filter">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </a>
            <?php endif; ?>
        </form>

        <div class="toolbar-stats">
            Total: <b><?= number_format($totalCount ?? 0) ?></b> Permintaan
        </div>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Tipe</th>
                    <th>Nama Pemohon</th>
                    <th>Jumlah Tarik Dana</th>
                    <th>Informasi Rekening / E-Wallet</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($withdrawals)): ?>
                    <?php foreach ($withdrawals as $wd): ?>
                        <tr>
                            <td>
                                <?php if ($wd['role_label'] == 'Penumpang'): ?>
                                    <span class="badge" style="background: #e0f2fe; color: #0284c7; font-weight: 700;">
                                        <i class="fa-solid fa-user"></i> Penumpang
                                    </span>
                                <?php else: ?>
                                    <span class="badge" style="background: #dcfce7; color: #16a34a; font-weight: 700;">
                                        <i class="fa-solid fa-motorcycle"></i> Pengemudi
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= esc($wd['applicant_name']) ?></strong><br>
                                <span style="font-size: 0.8rem; color: var(--text-muted);">
                                    <?= esc($wd['applicant_phone']) ?> • <?= date('d M Y H:i', strtotime($wd['created_at'])) ?>
                                </span>
                            </td>
                            <td>
                                <strong style="color: var(--primary); font-size: 1rem;">Rp <?= number_format($wd['amount'], 0, ',', '.') ?></strong>
                            </td>
                            <td>
                                <strong style="color: var(--text-main);"><?= esc($wd['bank_name']) ?></strong><br>
                                <span style="font-size: 0.875rem; color: var(--text-muted);">
                                    <?= esc($wd['account_number']) ?> a/n <strong><?= esc($wd['account_name']) ?></strong>
                                </span>
                            </td>
                            <td>
                                <?php if ($wd['status'] == 'pending'): ?>
                                    <span class="badge badge-warning">Menunggu Transfer</span>
                                <?php elseif ($wd['status'] == 'approved'): ?>
                                    <span class="badge badge-success">Selesai / Ditransfer</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Ditolak</span>
                                <?php endif; ?>
                            </td>
                            <td style="display: flex; gap: 8px;">
                                <?php if ($wd['status'] == 'pending'): ?>
                                    <form action="<?= base_url('admin/withdrawals/process/' . $wd['id'] . '/approved') ?>" method="post" onsubmit="return confirm('Apakah Anda sudah mentransfer uang Rp <?= number_format($wd['amount'], 0, ',', '.') ?> ke rekening ini?');">
                                        <button type="submit" style="padding: 6px 12px; background: var(--primary); color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.75rem; font-weight: 600;">
                                            <i class="fa-solid fa-check"></i> Setujui
                                        </button>
                                    </form>
                                    <form action="<?= base_url('admin/withdrawals/process/' . $wd['id'] . '/rejected') ?>" method="post" onsubmit="return confirm('Tolak penarikan ini dan kembalikan saldo ke pemohon?');">
                                        <button type="submit" style="padding: 6px 12px; background: #ef4444; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.75rem; font-weight: 600;">
                                            <i class="fa-solid fa-xmark"></i> Tolak
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span style="font-size: 0.8rem; color: var(--text-muted);">- Selesai -</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px;">
                            <i class="fa-solid fa-magnifying-glass" style="font-size: 1.5rem; opacity: 0.3; margin-bottom: 8px; display: block;"></i>
                            Tidak ada permintaan pencairan dana yang cocok dengan kriteria pencarian.
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