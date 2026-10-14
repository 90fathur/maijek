<?= $this->extend('admin/layout/main') ?>

<?= $this->section('content') ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h1 class="page-title animate-fade" style="margin-bottom: 0;">Kategori Kendaraan Spesifik</h1>
    <button onclick="document.getElementById('addModal').style.display='flex'" style="padding: 10px 16px; background: var(--primary); color: white; border: none; border-radius: 8px; cursor: pointer;">
        <i class="fa-solid fa-plus"></i> Tambah Kategori
    </button>
</div>

<?php if(session()->getFlashdata('success')): ?>
    <div style="background: rgba(16, 185, 129, 0.1); color: var(--primary); padding: 12px; border-radius: 8px; margin-bottom: 20px;">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<div class="card-table animate-fade">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Nama Kategori (Class)</th>
                    <th>Tipe Dasar</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($classes)): ?>
                    <?php foreach($classes as $c): ?>
                    <tr>
                        <td><strong><?= esc($c['name']) ?></strong></td>
                        <td>
                            <?php if($c['type'] == 'motor'): ?>
                                <i class="fa-solid fa-motorcycle" style="color: var(--text-muted);"></i> Motor
                            <?php else: ?>
                                <i class="fa-solid fa-car" style="color: var(--text-muted);"></i> Mobil
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($c['status'] == 'active'): ?>
                                <span class="badge badge-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form action="<?= base_url('admin/vehicleclasses/delete/' . $c['id']) ?>" method="post" onsubmit="return confirm('Hapus kategori ini?');">
                                <button type="submit" style="padding: 6px 12px; background: #ef4444; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.75rem;">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align: center; color: var(--text-muted); padding: 20px;">Belum ada kategori kendaraan. Silakan tambahkan.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="addModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: var(--bg-card); padding: 24px; border-radius: 12px; width: 100%; max-width: 400px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <h3 style="margin-top: 0; margin-bottom: 20px;">Tambah Kategori Kendaraan</h3>
        <form action="<?= base_url('admin/vehicleclasses/create') ?>" method="post">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Nama Kategori (Misal: Maijek XL, Maijek Premium)</label>
                <input type="text" name="name" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Tipe Dasar</label>
                <select name="type" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
                    <option value="motor">Sepeda Motor</option>
                    <option value="mobil">Mobil</option>
                </select>
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px;">Status</label>
                <select name="status" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="document.getElementById('addModal').style.display='none'" style="padding: 10px 15px; background: #e5e7eb; color: #374151; border: none; border-radius: 6px; cursor: pointer;">Batal</button>
                <button type="submit" style="padding: 10px 15px; background: var(--primary); color: white; border: none; border-radius: 6px; cursor: pointer;">Simpan</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
