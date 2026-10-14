<?= $this->extend('admin/layout/main') ?>

<?= $this->section('content') ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h1 class="page-title animate-fade" style="margin-bottom: 0;">Hak Akses (Role)</h1>
    <button onclick="document.getElementById('addModal').style.display='flex'" style="padding: 10px 16px; background: var(--primary); color: white; border: none; border-radius: 8px; cursor: pointer;">
        <i class="fa-solid fa-plus"></i> Tambah Akun
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
                    <th>Username</th>
                    <th>Role (Hak Akses)</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($admins as $admin): ?>
                <tr>
                    <td><strong><?= esc($admin['username']) ?></strong></td>
                    <td>
                        <?php if($admin['role'] == 'superadmin'): ?>
                            <span class="badge badge-success">Super Admin</span>
                        <?php elseif($admin['role'] == 'cs'): ?>
                            <span class="badge badge-warning">Customer Service (CS)</span>
                        <?php elseif($admin['role'] == 'finance'): ?>
                            <span class="badge" style="background:#3b82f6; color:white;">Finance (Keuangan)</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if(session()->get('admin_id') != $admin['id']): ?>
                        <form action="<?= base_url('admin/admins/delete/' . $admin['id']) ?>" method="post" onsubmit="return confirm('Hapus akun ini?');">
                            <button type="submit" style="padding: 6px 12px; background: #ef4444; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.75rem;">
                                Hapus
                            </button>
                        </form>
                        <?php else: ?>
                            <span style="font-size: 0.875rem; color: var(--text-muted);">Akun Anda</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Admin -->
<div id="addModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: var(--bg-card); padding: 24px; border-radius: 12px; width: 100%; max-width: 400px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <h3 style="margin-top: 0; margin-bottom: 20px;">Tambah Karyawan (Role)</h3>
        <form action="<?= base_url('admin/admins/create') ?>" method="post">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Username</label>
                <input type="text" name="username" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Password</label>
                <input type="password" name="password" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px;">Hak Akses</label>
                <select name="role" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
                    <option value="cs">Customer Service (Pesanan, Bantuan, Ulasan)</option>
                    <option value="finance">Finance (Tarik Saldo Keuangan)</option>
                    <option value="superadmin">Super Admin (Akses Penuh)</option>
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
