# SQL untuk Update Database Online

Buka phpMyAdmin online Anda, pilih database `sql_maijek_polman_id`, lalu jalankan *query* berikut di tab **SQL**:

```sql
-- Tambahkan kolom foto profil (jika belum ada)
ALTER TABLE drivers ADD COLUMN photo VARCHAR(255) NULL;

-- Tambahkan kolom dokumen KYC (jika belum ada)
ALTER TABLE drivers ADD COLUMN sim_image VARCHAR(255) NULL;
ALTER TABLE drivers ADD COLUMN stnk_image VARCHAR(255) NULL;
ALTER TABLE drivers ADD COLUMN vehicle_image VARCHAR(255) NULL;
```

*(Jika saat menjalankan perintah di atas muncul pesan "Duplicate column name", abaikan saja peringatan tersebut untuk kolom itu, artinya kolomnya sudah ada).*
