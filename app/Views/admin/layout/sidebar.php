<div class="sidebar">
    <div class="sidebar-header">
        <i class="fa-solid fa-motorcycle"></i> <span>Maijek Admin</span>
    </div>
    <ul class="sidebar-menu">
        <li>
            <a href="<?= base_url('admin/dashboard') ?>" class="<?= uri_string() == 'admin/dashboard' ? 'active' : '' ?>">
                <i class="fa-solid fa-chart-pie"></i> <span>Dashboard</span>
            </a>
        </li>

        <li class="menu-item-has-children">
            <a href="javascript:void(0)" class="menu-toggle">
                <i class="fa-solid fa-users"></i> <span>Manajemen Pengguna</span>
                <i class="fa-solid fa-chevron-down arrow"></i>
            </a>
            <ul class="sub-menu">
                <li><a href="<?= base_url('admin/users') ?>" class="<?= strpos(uri_string(), 'admin/users') === 0 ? 'active' : '' ?>">Pelanggan</a></li>
                <li><a href="<?= base_url('admin/drivers') ?>" class="<?= strpos(uri_string(), 'admin/drivers') === 0 ? 'active' : '' ?>">Pengemudi</a></li>
                <li><a href="<?= base_url('admin/driver-balance') ?>" class="<?= strpos(uri_string(), 'admin/driver-balance') === 0 ? 'active' : '' ?>">Minimal Saldo Driver</a></li>
                <li><a href="<?= base_url('admin/merchants') ?>" class="<?= strpos(uri_string(), 'admin/merchants') === 0 && strpos(uri_string(), 'admin/merchant-verifications') === false ? 'active' : '' ?>">Mitra UMKM</a></li>
                <li><a href="<?= base_url('admin/verifications') ?>" class="<?= strpos(uri_string(), 'admin/verifications') === 0 ? 'active' : '' ?>">Verifikasi Driver</a></li>
                <li><a href="<?= base_url('admin/merchant-verifications') ?>" class="<?= strpos(uri_string(), 'admin/merchant-verifications') === 0 ? 'active' : '' ?>">Verifikasi UMKM</a></li>
            </ul>
        </li>

        <li class="menu-item-has-children">
            <a href="javascript:void(0)" class="menu-toggle">
                <i class="fa-solid fa-wallet"></i> <span>Transaksi & Keuangan</span>
                <i class="fa-solid fa-chevron-down arrow"></i>
            </a>
            <ul class="sub-menu">
                <li><a href="<?= base_url('admin/orders') ?>" class="<?= strpos(uri_string(), 'admin/orders') === 0 ? 'active' : '' ?>">Riwayat Pesanan</a></li>
                <li><a href="<?= base_url('admin/finance') ?>" class="<?= strpos(uri_string(), 'admin/finance') === 0 ? 'active' : '' ?>">Laporan Keuangan</a></li>
                <li><a href="<?= base_url('admin/withdrawals') ?>" class="<?= strpos(uri_string(), 'admin/withdrawals') === 0 ? 'active' : '' ?>">Tarik Saldo</a></li>
                <li><a href="<?= base_url('admin/driver-balance') ?>" class="<?= strpos(uri_string(), 'admin/driver-balance') === 0 ? 'active' : '' ?>">Minimal Saldo Driver</a></li>
            </ul>
        </li>

        <li class="menu-item-has-children">
            <a href="javascript:void(0)" class="menu-toggle">
                <i class="fa-solid fa-map-location-dot"></i> <span>Operasional</span>
                <i class="fa-solid fa-chevron-down arrow"></i>
            </a>
            <ul class="sub-menu">
                <li><a href="<?= base_url('admin/map') ?>" class="<?= strpos(uri_string(), 'admin/map') === 0 ? 'active' : '' ?>">Live Map</a></li>
                <?php if (session()->get('admin_role') == 'superadmin'): ?>
                    <li><a href="<?= base_url('admin/zones') ?>" class="<?= strpos(uri_string(), 'admin/zones') === 0 ? 'active' : '' ?>">Geofencing (Zona)</a></li>
                    <li><a href="<?= base_url('admin/vehicleclasses') ?>" class="<?= strpos(uri_string(), 'admin/vehicleclasses') === 0 ? 'active' : '' ?>">Kategori Kendaraan</a></li>
                <?php endif; ?>
            </ul>
        </li>

        <li class="menu-item-has-children">
            <a href="javascript:void(0)" class="menu-toggle">
                <i class="fa-solid fa-bullhorn"></i> <span>Pemasaran & Promo</span>
                <i class="fa-solid fa-chevron-down arrow"></i>
            </a>
            <ul class="sub-menu">
                <li><a href="<?= base_url('admin/promos') ?>" class="<?= strpos(uri_string(), 'admin/promos') === 0 ? 'active' : '' ?>">Kode Promo</a></li>
                <li><a href="<?= base_url('admin/banners') ?>" class="<?= strpos(uri_string(), 'admin/banners') === 0 ? 'active' : '' ?>">Banner Aplikasi</a></li>
                <li><a href="<?= base_url('admin/broadcast') ?>" class="<?= strpos(uri_string(), 'admin/broadcast') === 0 ? 'active' : '' ?>">Kirim Broadcast</a></li>
            </ul>
        </li>

        <li class="menu-item-has-children">
            <a href="javascript:void(0)" class="menu-toggle">
                <i class="fa-solid fa-headset"></i> <span>Layanan Bantuan</span>
                <i class="fa-solid fa-chevron-down arrow"></i>
            </a>
            <ul class="sub-menu">
                <li><a href="<?= base_url('admin/tickets') ?>" class="<?= strpos(uri_string(), 'admin/tickets') === 0 ? 'active' : '' ?>">Tiket Keluhan</a></li>
                <li><a href="<?= base_url('admin/cs-chat') ?>" class="<?= strpos(uri_string(), 'admin/cs-chat') === 0 ? 'active' : '' ?>">Live Chat CS</a></li>
                <li><a href="<?= base_url('admin/reviews') ?>" class="<?= strpos(uri_string(), 'admin/reviews') === 0 ? 'active' : '' ?>">Ulasan & Rating</a></li>
            </ul>
        </li>

        <?php if (session()->get('admin_role') == 'superadmin'): ?>
            <li class="menu-item-has-children">
                <a href="javascript:void(0)" class="menu-toggle">
                    <i class="fa-solid fa-gear"></i> <span>Pengaturan Inti</span>
                    <i class="fa-solid fa-chevron-down arrow"></i>
                </a>
                <ul class="sub-menu">
                    <li><a href="<?= base_url('admin/admins') ?>" class="<?= strpos(uri_string(), 'admin/admins') === 0 ? 'active' : '' ?>">Multi-Admin (Role)</a></li>
                    <li><a href="<?= base_url('admin/settings') ?>" class="<?= strpos(uri_string(), 'admin/settings') === 0 ? 'active' : '' ?>">Pengaturan Sistem</a></li>
                </ul>
            </li>
        <?php endif; ?>
    </ul>
    <div style="padding: 20px; border-top: 1px solid rgba(255,255,255,0.05);">
        <a href="<?= base_url('admin/logout') ?>" style="display: flex; align-items: center; color: #ef4444; text-decoration: none; padding: 12px 24px; transition: var(--transition);">
            <i class="fa-solid fa-arrow-right-from-bracket" style="margin-right: 12px; width: 24px;"></i> <span>Keluar</span>
        </a>
    </div>
</div>