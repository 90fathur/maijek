<?= $this->extend('admin/layout/main') ?>

<?= $this->section('content') ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h1 class="page-title animate-fade" style="margin-bottom: 0;">Manajemen Pelanggan</h1>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div style="background: rgba(16, 185, 129, 0.1); color: var(--primary); padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.875rem;">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<div class="card-table animate-fade">
    <!-- Toolbar Pencarian & Filter -->
    <div class="table-toolbar">
        <form method="get" action="<?= base_url('admin/users') ?>" class="toolbar-form">
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" name="q" value="<?= esc($q ?? '') ?>" placeholder="Cari nama, no. HP, email, ID..." class="search-input">
                <?php if (!empty($q)): ?>
                    <a href="<?= base_url('admin/users') ?>" class="clear-search" title="Hapus pencarian"><i class="fa-solid fa-xmark"></i></a>
                <?php endif; ?>
            </div>

            <div class="filter-box">
                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Aktif</option>
                    <option value="suspended" <?= ($status ?? '') === 'suspended' ? 'selected' : '' ?>>Disuspend</option>
                </select>
            </div>

            <button type="submit" class="btn-search">
                <i class="fa-solid fa-filter"></i> Filter
            </button>

            <?php if (!empty($q) || !empty($status)): ?>
                <a href="<?= base_url('admin/users') ?>" class="btn-reset" title="Reset filter">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </a>
            <?php endif; ?>
        </form>

        <div class="toolbar-stats">
            Total: <b><?= number_format($totalCount ?? 0) ?></b> Pelanggan
        </div>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Pelanggan</th>
                    <th>No. HP</th>
                    <th>Email</th>
                    <th>Saldo & Koin</th>
                    <th>Status</th>
                    <th>Tgl Mendaftar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>#USR-<?= str_pad($user['id'], 4, '0', STR_PAD_LEFT) ?></td>
                            <td><?= esc($user['name']) ?></td>
                            <td><?= esc($user['phone']) ?></td>
                            <td><?= esc($user['email']) ?: '-' ?></td>
                            <td>
                                <strong>Rp <?= number_format($user['balance'] ?? 0, 0, ',', '.') ?></strong><br>
                                <span style="color: #f59e0b; font-size: 0.875rem;"><i class="fa-solid fa-coins"></i> <?= number_format($user['coins'] ?? 0) ?> Koin</span>
                            </td>
                            <td>
                                <?php if (($user['status'] ?? 'active') === 'suspended'): ?>
                                    <span class="badge badge-danger" style="background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); padding: 4px 8px; border-radius: 6px; font-weight: bold; font-size: 0.75rem;">Disuspend</span>
                                <?php else: ?>
                                    <span class="badge badge-success" style="background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); padding: 4px 8px; border-radius: 6px; font-weight: bold; font-size: 0.75rem;">Aktif</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d M Y H:i', strtotime($user['created_at'])) ?></td>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 8px; min-width: 250px;">
                                    <!-- Baris 1: Status & Ubah Saldo -->
                                    <div style="display: flex; gap: 6px; width: 100%;">
                                        <!-- Form Ubah Status -->
                                        <form action="<?= base_url('admin/users/changeStatus/' . $user['id']) ?>" method="post" style="display: flex; gap: 4px; flex: 1;">
                                            <select name="status" style="width: 100%; padding: 6px 8px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 0.8125rem; outline: none; background: var(--bg-card); color: var(--text-color);">
                                                <option value="active" <?= ($user['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Aktif</option>
                                                <option value="suspended" <?= ($user['status'] ?? 'active') === 'suspended' ? 'selected' : '' ?>>Suspend</option>
                                            </select>
                                            <button type="submit" style="padding: 6px 10px; background: #6366f1; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.8125rem; white-space: nowrap;">
                                                Ubah
                                            </button>
                                        </form>

                                        <!-- Form Ubah Saldo -->
                                        <form action="<?= base_url('admin/users/setBalance/' . $user['id']) ?>" method="post" onsubmit="return confirm('Perbarui saldo penumpang ini?');" style="display: flex; gap: 4px; flex: 1;">
                                            <input type="number" name="balance" value="<?= (int)($user['balance'] ?? 0) ?>" placeholder="Saldo Rp" required style="width: 100%; padding: 6px 8px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 0.8125rem; outline: none; background: var(--bg-card); color: var(--text-color); min-width: 0;">
                                            <button type="submit" style="padding: 6px 10px; background: #10b981; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.8125rem; white-space: nowrap;">
                                                Set
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Baris 2: Reset Device, Reset Saldo, & Reset Pass -->
                                    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                        <!-- Form Reset Device -->
                                        <form action="<?= base_url('admin/users/resetDeviceId/' . $user['id']) ?>" method="post" onsubmit="return confirm('Yakin ingin mereset batasan perangkat untuk penumpang ini?');">
                                            <button type="submit" style="padding: 6px 10px; background: #f59e0b; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.8125rem; white-space: nowrap;">
                                                Reset Device
                                            </button>
                                        </form>

                                        <!-- Form Reset Saldo -->
                                        <?php if (($user['balance'] ?? 0) > 0): ?>
                                            <form action="<?= base_url('admin/users/resetBalance/' . $user['id']) ?>" method="post" onsubmit="return confirm('Yakin ingin mereset saldo penumpang ini menjadi Rp 0?');">
                                                <button type="submit" style="padding: 6px 10px; background: #dc2626; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.8125rem; white-space: nowrap;">
                                                    Reset Saldo
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <!-- Form Reset Password -->
                                        <form action="<?= base_url('admin/users/resetPassword/' . $user['id']) ?>" method="post" onsubmit="return confirm('Yakin ingin mereset password penumpang ini?');" style="display: flex; gap: 4px;">
                                            <input type="text" name="new_password" placeholder="Pass Baru" required style="width: 80px; padding: 6px 8px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 0.8125rem; outline: none; background: var(--bg-card); color: var(--text-color);">
                                            <button type="submit" style="padding: 6px 10px; background: #ef4444; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.8125rem; white-space: nowrap;">
                                                Pass
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 30px;">
                            <i class="fa-solid fa-magnifying-glass" style="font-size: 1.5rem; opacity: 0.3; margin-bottom: 8px; display: block;"></i>
                            Tidak ada data pelanggan yang cocok dengan kriteria pencarian.
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