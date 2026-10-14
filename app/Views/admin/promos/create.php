<?= $this->extend('admin/layout/main') ?>

<?= $this->section('content') ?>
<div style="margin-bottom: 24px;">
    <h1 class="page-title animate-fade" style="margin-bottom: 8px;">Buat Promo Baru</h1>
    <a href="<?= base_url('admin/promos') ?>" style="color: var(--primary); text-decoration: none; font-size: 0.875rem;"><i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Promo</a>
</div>

<form action="<?= base_url('admin/promos/store') ?>" method="post" class="card-table animate-fade" style="max-width: 600px; padding: 32px;">
    <div style="margin-bottom: 20px;">
        <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 500;">Kode Voucher</label>
        <input type="text" name="code" placeholder="Misal: DISKON50" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none; text-transform: uppercase;" required>
    </div>
    
    <div style="margin-bottom: 20px; display: flex; gap: 20px;">
        <div style="flex: 1;">
            <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 500;">Tipe Potongan</label>
            <select name="discount_type" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none;" required>
                <option value="fixed">Nominal Rupiah (Rp)</option>
                <option value="percent">Persentase (%)</option>
            </select>
        </div>
        <div style="flex: 1;">
            <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 500;">Jumlah Potongan</label>
            <input type="number" name="discount_amount" placeholder="Misal: 5000 atau 20" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none;" required>
        </div>
    </div>
    
    <div style="margin-bottom: 24px;">
        <label style="display: block; margin-bottom: 8px; font-size: 0.875rem; font-weight: 500;">Berlaku Sampai (Tanggal & Waktu)</label>
        <input type="datetime-local" name="valid_until" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; outline:none;" required>
    </div>

    <button type="submit" style="width: 100%; padding: 14px; background: var(--primary); color: white; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: 0.2s;">
        Simpan Promo
    </button>
</form>
<?= $this->endSection() ?>
