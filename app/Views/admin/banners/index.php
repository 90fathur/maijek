<?= $this->extend('admin/layout/main') ?>

<?= $this->section('content') ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h1 class="page-title animate-fade" style="margin-bottom: 0;">Manajemen Banner (CMS)</h1>
    <button onclick="document.getElementById('addModal').style.display='flex'" style="padding: 10px 16px; background: var(--primary); color: white; border: none; border-radius: 8px; cursor: pointer;">
        <i class="fa-solid fa-cloud-arrow-up"></i> Unggah Banner
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

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;" class="animate-fade">
    <?php if(!empty($banners)): ?>
        <?php foreach($banners as $b): ?>
            <div class="card" style="padding: 0; overflow: hidden;">
                <img src="<?= base_url('uploads/banners/' . $b['image_path']) ?>" alt="<?= esc($b['title']) ?>" style="width: 100%; height: 160px; object-fit: cover;">
                <div style="padding: 16px;">
                    <h3 style="margin-top: 0; margin-bottom: 8px; font-size: 1.1rem;"><?= esc($b['title']) ?></h3>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <?php if($b['status'] == 'active'): ?>
                            <span class="badge badge-success">Aktif</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Nonaktif</span>
                        <?php endif; ?>
                        
                        <form action="<?= base_url('admin/banners/delete/' . $b['id']) ?>" method="post" onsubmit="return confirm('Hapus banner ini dari aplikasi?');">
                            <button type="submit" style="padding: 6px 12px; background: #ef4444; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.75rem;">
                                <i class="fa-solid fa-trash"></i> Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 40px; background: white; border-radius: 12px;">
            Belum ada banner promosi. Aplikasi mobile akan terlihat kosong.
        </div>
    <?php endif; ?>
</div>

<div id="addModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: var(--bg-card); padding: 24px; border-radius: 12px; width: 100%; max-width: 400px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <h3 style="margin-top: 0; margin-bottom: 20px;">Unggah Banner Baru</h3>
        <form action="<?= base_url('admin/banners/create') ?>" method="post" enctype="multipart/form-data">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Judul Promo / Banner</label>
                <input type="text" name="title" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">File Gambar (JPG/PNG)</label>
                <input type="file" name="image" accept="image/*" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px;">Status Penayangan</label>
                <select name="status" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
                    <option value="active">Langsung Tayang (Aktif)</option>
                    <option value="inactive">Sembunyikan Dulu (Nonaktif)</option>
                </select>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="document.getElementById('addModal').style.display='none'" style="padding: 10px 15px; background: #e5e7eb; color: #374151; border: none; border-radius: 6px; cursor: pointer;">Batal</button>
                <button type="submit" style="padding: 10px 15px; background: var(--primary); color: white; border: none; border-radius: 6px; cursor: pointer;">Unggah</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
