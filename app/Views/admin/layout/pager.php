<?php
/**
 * Admin Panel Pagination Template
 * @var \CodeIgniter\Pager\PagerRenderer $pager
 */
$pager->setSurroundCount(2);
?>

<?php if ($pager->hasPrevious() || $pager->hasNext() || count($pager->links()) > 1) : ?>
<nav class="pagination-nav" aria-label="Navigasi Halaman">
    <ul class="pagination">
        <?php if ($pager->hasPrevious()) : ?>
            <li>
                <a href="<?= $pager->getFirst() ?>" class="page-link" title="Halaman Pertama" aria-label="Pertama">
                    <i class="fa-solid fa-angles-left"></i>
                </a>
            </li>
            <li>
                <a href="<?= $pager->getPrevious() ?>" class="page-link" title="Sebelumnya" aria-label="Sebelumnya">
                    <i class="fa-solid fa-angle-left"></i>
                </a>
            </li>
        <?php endif ?>

        <?php foreach ($pager->links() as $link) : ?>
            <li>
                <a href="<?= $link['uri'] ?>" class="page-link <?= $link['active'] ? 'active' : '' ?>">
                    <?= $link['title'] ?>
                </a>
            </li>
        <?php endforeach ?>

        <?php if ($pager->hasNext()) : ?>
            <li>
                <a href="<?= $pager->getNext() ?>" class="page-link" title="Berikutnya" aria-label="Berikutnya">
                    <i class="fa-solid fa-angle-right"></i>
                </a>
            </li>
            <li>
                <a href="<?= $pager->getLast() ?>" class="page-link" title="Halaman Terakhir" aria-label="Terakhir">
                    <i class="fa-solid fa-angles-right"></i>
                </a>
            </li>
        <?php endif ?>
    </ul>
</nav>
<?php endif ?>
