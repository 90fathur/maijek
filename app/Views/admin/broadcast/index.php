<?= $this->extend('admin/layout/main') ?>

<?= $this->section('content') ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h1 class="page-title animate-fade" style="margin-bottom: 0;">Broadcast Pesan (Push Notification)</h1>
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

<div class="stats-grid">
    <!-- Form Kirim -->
    <div class="card-table animate-fade" style="padding: 32px;">
        <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 24px;"><i class="fa-solid fa-paper-plane" style="color: var(--primary); margin-right: 8px;"></i> Tulis Pesan Baru</h2>

        <form action="<?= base_url('admin/broadcast/send') ?>" method="post">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 500;">Judul Notifikasi</label>
                <input type="text" name="title" placeholder="Misal: Promo Akhir Tahun!" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none;" required>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 500;">Pesan Utama</label>
                <textarea name="message" rows="4" placeholder="Ketik isi pesan di sini..." style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none;" required></textarea>
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 500;">Kirim Kepada</label>
                <select name="target" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none;" required>
                    <option value="all">Semua (Pengemudi & Pelanggan)</option>
                    <option value="users">Hanya Pelanggan</option>
                    <option value="drivers">Hanya Pengemudi</option>
                </select>
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 500;">Tipe Notifikasi (Ikon di Aplikasi)</label>
                <select name="type" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none;" required>
                    <option value="promo">🎉 Promo (Ikon Diskon)</option>
                    <option value="system">🛡️ Sistem (Ikon Keamanan/Info)</option>
                    <option value="transaction">💰 Transaksi (Ikon Dompet)</option>
                </select>
            </div>

            <button type="submit" style="width: 100%; padding: 14px; background: var(--primary); color: white; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: 0.2s;">
                Kirim Sekarang
            </button>
        </form>
    </div>

    <!-- Riwayat Broadcast -->
    <div class="card-table animate-fade">
        <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 24px; padding: 24px 24px 0 24px;"><i class="fa-solid fa-clock-rotate-left" style="color: var(--primary); margin-right: 8px;"></i> Riwayat Broadcast</h2>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Pesan</th>
                        <th>Target</th>
                        <th>Tipe</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($notifications)): ?>
                        <?php foreach ($notifications as $notif): ?>
                            <tr>
                                <td>
                                    <strong><?= esc($notif['title']) ?></strong><br>
                                    <span style="font-size: 0.875rem; color: var(--text-muted);"><?= esc($notif['message']) ?></span>
                                </td>
                                <td>
                                    <?php
                                    if ($notif['target'] == 'all') echo '<span class="badge" style="background:#f3f4f6; color:#4b5563;">Semua</span>';
                                    elseif ($notif['target'] == 'users') echo '<span class="badge badge-success">Pelanggan</span>';
                                    else echo '<span class="badge badge-warning">Pengemudi</span>';
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if (isset($notif['type'])) {
                                        if ($notif['type'] == 'promo') echo '<span class="badge badge-success">Promo</span>';
                                        elseif ($notif['type'] == 'transaction') echo '<span class="badge badge-warning">Transaksi</span>';
                                        else echo '<span class="badge" style="background:#f3f4f6; color:#4b5563;">Sistem</span>';
                                    } else {
                                        echo '<span class="badge" style="background:#f3f4f6; color:#4b5563;">Sistem</span>';
                                    }
                                    ?>
                                </td>
                                <td><?= date('d M Y H:i', strtotime($notif['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align: center; color: var(--text-muted); padding: 20px;">Belum ada riwayat pesan</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>