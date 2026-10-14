<?= $this->extend('admin/layout/main') ?>

<?= $this->section('content') ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h1 class="page-title animate-fade" style="margin-bottom: 0;">Zona Operasional (Geofence)</h1>
    <button onclick="document.getElementById('addModal').style.display='flex'" style="padding: 10px 16px; background: var(--primary); color: white; border: none; border-radius: 8px; cursor: pointer;">
        <i class="fa-solid fa-draw-polygon"></i> Buat Zona Baru
    </button>
</div>

<?php if(session()->getFlashdata('success')): ?>
    <div style="background: rgba(16, 185, 129, 0.1); color: var(--primary); padding: 12px; border-radius: 8px; margin-bottom: 20px;">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
    <div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<div class="card-table animate-fade">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Nama Zona Wilayah</th>
                    <th>Status</th>
                    <th>Format Peta</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($zones)): ?>
                    <?php foreach($zones as $z): ?>
                    <tr>
                        <td><strong><?= esc($z['name']) ?></strong></td>
                        <td>
                            <?php if($z['status'] == 'active'): ?>
                                <span class="badge badge-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span style="font-family: monospace; background: var(--bg-body); padding: 4px 8px; border-radius: 4px; font-size: 0.75rem;">GeoJSON Polygon</span>
                        </td>
                        <td>
                            <form action="<?= base_url('admin/zones/delete/' . $z['id']) ?>" method="post" onsubmit="return confirm('Hapus zona ini? Pelanggan mungkin bisa memesan dari luar kota.');">
                                <button type="submit" style="padding: 6px 12px; background: #ef4444; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.75rem;">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align: center; color: var(--text-muted); padding: 20px;">Anda belum mengatur pembatasan wilayah (Bebas di mana saja).</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="addModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: var(--bg-card); padding: 24px; border-radius: 12px; width: 100%; max-width: 500px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <h3 style="margin-top: 0; margin-bottom: 20px;">Tambah Wilayah Geofence</h3>
        <form action="<?= base_url('admin/zones/create') ?>" method="post">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Nama Kota / Wilayah</label>
                <input type="text" name="name" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Data Koordinat (GeoJSON Polygon Array)</label>
                <textarea name="coordinates" rows="6" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; font-family: monospace; font-size: 0.75rem;" placeholder="[
  [-6.200000, 106.816666],
  [-6.210000, 106.816666],
  ...
]"></textarea>
                <small style="color: var(--text-muted);">*Anda bisa menggambar batas wilayah dan mendapatkan kodenya dari situs <strong>geojson.io</strong></small>
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px;">Status</label>
                <select name="status" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
                    <option value="active">Aktif (Terapkan Pembatasan)</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="document.getElementById('addModal').style.display='none'" style="padding: 10px 15px; background: #e5e7eb; color: #374151; border: none; border-radius: 6px; cursor: pointer;">Batal</button>
                <button type="submit" style="padding: 10px 15px; background: var(--primary); color: white; border: none; border-radius: 6px; cursor: pointer;">Simpan Zona</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
