<?= $this->extend('admin/layout/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Chat Layanan Pelanggan</h3>
            </div>
            <div class="card-body">
                <table id="example1" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Room ID</th>
                            <th>Pengguna</th>
                            <th>Waktu Chat Terakhir</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rooms as $room): ?>
                            <tr>
                                <td><?= esc($room['room_id']) ?></td>
                                <td><span class="badge badge-info"><?= strtoupper($room['sender_type']) ?></span></td>
                                <td><?= esc($room['last_chat_time']) ?></td>
                                <td>
                                    <a href="<?= base_url('admin/cs-chat/' . $room['room_id']) ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-comments"></i> Balas
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>