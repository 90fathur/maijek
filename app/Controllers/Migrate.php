<?php

namespace App\Controllers;

use App\Database\Migrations\CreateTransactionsTable;

class Migrate extends BaseController
{
    public function index()
    {
        try {
            // Kita bypass migration runner CodeIgniter karena tabel 'migrations' di production berantakan
            // Langsung eksekusi up() dari CreateTransactionsTable
            require_once APPPATH . 'Database/Migrations/2026-08-25-142409_CreateTransactionsTable.php';

            $migration = new CreateTransactionsTable(\Config\Database::forge());
            $migration->up();

            echo "Migrations run successfully. Tabel transactions berhasil dibuat/diperbarui.";
        } catch (\Throwable $e) {
            // Jika error karena tabel sudah ada, abaikan saja (berarti sudah sukses)
            if (strpos($e->getMessage(), 'already exists') !== false) {
                echo "Migrations run successfully. Tabel transactions sudah ada.";
            } else {
                echo "Migration failed: " . $e->getMessage();
            }
        }
    }
}
