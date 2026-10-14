<?= $this->extend('admin/layout/main') ?>

<?= $this->section('content') ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h1 class="page-title animate-fade" style="margin-bottom: 0;">Ulasan & Rating Pengemudi</h1>
</div>

<div class="card-table animate-fade">
    <!-- Toolbar Pencarian & Filter -->
    <div class="table-toolbar">
        <form method="get" action="<?= base_url('admin/reviews') ?>" class="toolbar-form">
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" name="q" value="<?= esc($q ?? '') ?>" placeholder="Cari nama driver, pelanggan, ulasan..." class="search-input">
                <?php if (!empty($q)): ?>
                    <a href="<?= base_url('admin/reviews') ?>" class="clear-search" title="Hapus pencarian"><i class="fa-solid fa-xmark"></i></a>
                <?php endif; ?>
            </div>

            <div class="filter-box">
                <select name="rating" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Rating</option>
                    <option value="5" <?= ($rating ?? '') === '5' ? 'selected' : '' ?>>⭐⭐⭐⭐⭐ (5 Bintang)</option>
                    <option value="4" <?= ($rating ?? '') === '4' ? 'selected' : '' ?>>⭐⭐⭐⭐ (4 Bintang)</option>
                    <option value="3" <?= ($rating ?? '') === '3' ? 'selected' : '' ?>>⭐⭐⭐ (3 Bintang)</option>
                    <option value="2" <?= ($rating ?? '') === '2' ? 'selected' : '' ?>>⭐⭐ (2 Bintang)</option>
                    <option value="1" <?= ($rating ?? '') === '1' ? 'selected' : '' ?>>⭐ (1 Bintang)</option>
                </select>
            </div>

            <button type="submit" class="btn-search">
                <i class="fa-solid fa-filter"></i> Filter
            </button>

            <?php if (!empty($q) || !empty($rating)): ?>
                <a href="<?= base_url('admin/reviews') ?>" class="btn-reset" title="Reset filter">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </a>
            <?php endif; ?>
        </form>

        <div class="toolbar-stats">
            Total: <b><?= number_format($totalCount ?? 0) ?></b> Ulasan
        </div>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Pengemudi (Driver)</th>
                    <th>Pelanggan (User)</th>
                    <th>Rating Bintang</th>
                    <th>Komentar Ulasan</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($reviews)): ?>
                    <?php foreach($reviews as $rv): ?>
                    <tr>
                        <td>
                            <strong><?= esc($rv['driver_name'] ?? 'Driver Tidak Diketahui') ?></strong><br>
                            <span style="font-size: 0.875rem; color: var(--text-muted);">Pesanan #<?= $rv['id'] ?></span>
                        </td>
                        <td>
                            <strong><?= esc($rv['user_name']) ?></strong>
                        </td>
                        <td>
                            <div style="color: #fbbf24; font-size: 1rem;">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <?php if($i <= $rv['rating']): ?>
                                        <i class="fa-solid fa-star"></i>
                                    <?php else: ?>
                                        <i class="fa-regular fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                            <span style="font-size: 0.875rem; font-weight: 600; color: var(--text-main);"><?= $rv['rating'] ?>.0 / 5.0</span>
                        </td>
                        <td>
                            <span style="font-style: italic; color: var(--text-muted);">
                                "<?= esc($rv['review_comment'] ?? 'Tidak ada komentar') ?>"
                            </span>
                        </td>
                        <td><?= date('d M Y', strtotime($rv['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 30px;">
                            <i class="fa-solid fa-magnifying-glass" style="font-size: 1.5rem; opacity: 0.3; margin-bottom: 8px; display: block;"></i>
                            Tidak ada ulasan yang cocok dengan kriteria pencarian.
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
