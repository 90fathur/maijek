<header class="topbar">
    <button class="toggle-btn" id="sidebarToggle">
        <i class="fa-solid fa-bars"></i>
    </button>

    <a href="<?= base_url('admin/profile') ?>" style="text-decoration: none; color: inherit;">
        <div class="user-profile">
            <div class="info" style="text-align: right;">
                <span class="name"><?= esc(session()->get('admin_username')) ?></span>
                <span class="role" style="text-transform: uppercase;"><?= esc(session()->get('admin_role') ?? 'Superadmin') ?></span>
            </div>
            <img src="https://ui-avatars.com/api/?name=<?= urlencode(session()->get('admin_username')) ?>&background=10b981&color=fff" alt="Admin Profile">
        </div>
    </a>
</header>
