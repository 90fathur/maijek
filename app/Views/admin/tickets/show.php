<?= $this->extend('admin/layout/main') ?>

<?= $this->section('content') ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <div>
        <a href="<?= base_url('admin/tickets') ?>" style="color: var(--text-muted); text-decoration: none; font-size: 0.875rem;"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
        <h1 class="page-title animate-fade" style="margin-bottom: 0; margin-top: 10px;">Tiket #<?= esc($ticket['id']) ?> - <?= esc($ticket['subject']) ?></h1>
    </div>
    <?php if($ticket['status'] == 'open'): ?>
        <span class="badge badge-warning">Status: OPEN</span>
    <?php else: ?>
        <span class="badge" style="background:#e5e7eb; color:#6b7280;">Status: CLOSED</span>
    <?php endif; ?>
</div>

<?php if(session()->getFlashdata('success')): ?>
    <div style="background: rgba(16, 185, 129, 0.1); color: var(--primary); padding: 12px; border-radius: 8px; margin-bottom: 20px;">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<div class="card animate-fade" style="margin-bottom: 20px; max-height: 500px; overflow-y: auto; display: flex; flex-direction: column; gap: 16px;">
    <?php if(empty($replies)): ?>
        <div style="text-align: center; color: var(--text-muted);">Belum ada percakapan.</div>
    <?php else: ?>
        <?php foreach($replies as $r): ?>
            <?php $isAdmin = ($r['sender'] == 'admin'); ?>
            <div style="display: flex; flex-direction: column; align-items: <?= $isAdmin ? 'flex-end' : 'flex-start' ?>;">
                <span style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 4px;"><?= $isAdmin ? 'Admin (Anda)' : 'Pelanggan/Pengemudi' ?> - <?= date('d M Y H:i', strtotime($r['created_at'])) ?></span>
                <div style="padding: 12px 16px; border-radius: 12px; background: <?= $isAdmin ? 'var(--primary)' : '#f3f4f6' ?>; color: <?= $isAdmin ? 'white' : 'var(--text-main)' ?>; max-width: 70%;">
                    <?= nl2br(esc($r['message'])) ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php if($ticket['status'] == 'open'): ?>
<div class="card animate-fade">
    <form action="<?= base_url('admin/tickets/reply/' . $ticket['id']) ?>" method="post">
        <textarea name="message" rows="4" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; margin-bottom: 12px; outline:none;" placeholder="Tulis balasan Anda ke pelanggan/pengemudi di sini..."></textarea>
        
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <label style="display: flex; align-items: center; gap: 8px; font-size: 0.875rem; color: var(--text-muted);">
                <input type="checkbox" name="close_ticket" value="1"> Sekaligus tutup tiket ini (Selesai)
            </label>
            <button type="submit" style="padding: 10px 20px; background: var(--primary); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                <i class="fa-solid fa-paper-plane"></i> Kirim Balasan
            </button>
        </div>
    </form>
</div>
<?php else: ?>
    <div style="text-align: center; color: var(--text-muted); padding: 20px; background: var(--bg-body); border-radius: 8px;">
        Tiket ini sudah ditutup dan tidak dapat menerima balasan baru.
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
