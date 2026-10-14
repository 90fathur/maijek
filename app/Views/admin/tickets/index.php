<?= $this->extend('admin/layout/main') ?>

<?= $this->section('content') ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h1 class="page-title animate-fade" style="margin-bottom: 0;">Tiket Bantuan (Helpdesk)</h1>
</div>

<div class="card-table animate-fade">
    <!-- Toolbar Pencarian & Filter -->
    <div class="table-toolbar">
        <form method="get" action="<?= base_url('admin/tickets') ?>" class="toolbar-form">
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" name="q" value="<?= esc($q ?? '') ?>" placeholder="Cari ID tiket, subjek, nama..." class="search-input">
                <?php if (!empty($q)): ?>
                    <a href="<?= base_url('admin/tickets') ?>" class="clear-search" title="Hapus pencarian"><i class="fa-solid fa-xmark"></i></a>
                <?php endif; ?>
            </div>

            <div class="filter-box">
                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="open" <?= ($status ?? '') === 'open' ? 'selected' : '' ?>>Terbuka (Open)</option>
                    <option value="closed" <?= ($status ?? '') === 'closed' ? 'selected' : '' ?>>Selesai (Closed)</option>
                </select>
            </div>

            <button type="submit" class="btn-search">
                <i class="fa-solid fa-filter"></i> Filter
            </button>

            <?php if (!empty($q) || !empty($status)): ?>
                <a href="<?= base_url('admin/tickets') ?>" class="btn-reset" title="Reset filter">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </a>
            <?php endif; ?>
        </form>

        <div class="toolbar-stats">
            Total: <b><?= number_format($totalCount ?? 0) ?></b> Tiket
        </div>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID Tiket</th>
                    <th>Subjek / Kendala</th>
                    <th>Pengirim</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($tickets)): ?>
                    <?php foreach($tickets as $t): ?>
                    <tr>
                        <td>#<?= esc($t['id']) ?></td>
                        <td><strong><?= esc($t['subject']) ?></strong></td>
                        <td>
                            <?php if($t['user_id']): ?>
                                <span style="color:var(--primary);"><i class="fa-solid fa-user"></i> <?= esc($t['user_name']) ?></span>
                            <?php elseif($t['driver_id']): ?>
                                <span style="color:#f59e0b;"><i class="fa-solid fa-motorcycle"></i> <?= esc($t['driver_name']) ?></span>
                            <?php else: ?>
                                Sistem
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($t['status'] == 'open'): ?>
                                <span class="badge badge-warning">Terbuka (Open)</span>
                            <?php else: ?>
                                <span class="badge" style="background:#e5e7eb; color:#6b7280;">Selesai (Closed)</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d M Y', strtotime($t['created_at'])) ?></td>
                        <td>
                            <a href="<?= base_url('admin/tickets/show/' . $t['id']) ?>" style="padding: 6px 12px; background: var(--primary); color: white; border-radius: 6px; text-decoration: none; font-size: 0.75rem;">
                                <i class="fa-solid fa-reply"></i> Balas
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px;">
                        <i class="fa-solid fa-magnifying-glass" style="font-size: 1.5rem; opacity: 0.3; margin-bottom: 8px; display: block;"></i>
                        Tidak ada tiket yang cocok dengan kriteria pencarian.
                    </td></tr>
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
