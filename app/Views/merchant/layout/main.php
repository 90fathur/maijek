<?php
$mId = session()->get('merchant_id');
$mType = session()->get('merchant_type');
if (empty($mType) && $mId) {
    $db = \Config\Database::connect();
    $mRow = $db->table('merchants')->select('merchant_type')->where('id', $mId)->get()->getRowArray();
    $mType = $mRow['merchant_type'] ?? 'food';
    session()->set('merchant_type', $mType);
}
$isMart = ($mType === 'mart');
$brandName = $isMart ? 'MaiMart' : 'MaiFood';
$panelTitle = $isMart ? 'MaiMart Panel' : 'MaiFood Panel';
$portalTag = $isMart ? 'Mitra Toko & Swalayan' : 'Merchant Portal';
$categoryCatalog = $isMart ? 'Toko & Produk' : 'Restoran & Menu';
$menuManageLabel = $isMart ? 'Kelola Produk' : 'Kelola Menu';
$menuManageIcon = $isMart ? 'fa-boxes-stacked' : 'fa-book-open';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? ($isMart ? 'MaiMart Merchant Panel' : 'MaiFood Merchant Panel') ?></title>
    <!-- Google Font & FontAwesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        :root {
            --primary: #ea580c;
            --primary-hover: #c2410c;
            --primary-light: #ffedd5;
            --primary-glow: rgba(234, 88, 12, 0.25);
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --sidebar-bg: #0f172a;
            --sidebar-hover: #1e293b;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-main);
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* SIDEBAR */
        .merchant-sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            color: #94a3b8;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 50;
            transition: all 0.3s ease;
        }

        .sidebar-brand {
            padding: 24px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar-brand .brand-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #f97316, #ea580c);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
            box-shadow: 0 4px 12px var(--primary-glow);
        }

        .sidebar-brand .brand-text {
            color: white;
            font-size: 1.15rem;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .sidebar-brand .brand-tag {
            font-size: 0.7rem;
            color: #fdba74;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .sidebar-menu {
            padding: 20px 12px;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 6px;
            overflow-y: auto;
        }

        .menu-category {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #475569;
            padding: 12px 12px 6px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 10px;
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.2s;
            position: relative;
        }

        .menu-item:hover {
            background: var(--sidebar-hover);
            color: white;
        }

        .menu-item.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 14px var(--primary-glow);
        }

        .menu-item .badge {
            margin-left: auto;
            background: var(--danger);
            color: white;
            font-size: 0.75rem;
            padding: 2px 8px;
            border-radius: 99px;
            font-weight: 700;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.6;
            }
        }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(0, 0, 0, 0.2);
        }

        .merchant-profile-card {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .merchant-avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            object-fit: cover;
            background: #334155;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* MAIN CONTENT */
        .merchant-main {
            margin-left: 260px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        /* TOPBAR */
        .merchant-topbar {
            height: 70px;
            background: white;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 40;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .topbar-title {
            font-size: 1.15rem;
            font-weight: 800;
            letter-spacing: -0.3px;
        }

        .store-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 99px;
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }

        .store-status-pill.open {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .store-status-pill.closed {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .store-status-pill.open .status-dot {
            background: #22c55e;
            box-shadow: 0 0 8px #22c55e;
        }

        .store-status-pill.closed .status-dot {
            background: #ef4444;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            background: white;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-icon:hover {
            color: var(--primary);
            border-color: var(--primary);
            background: var(--primary-light);
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.875rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: 0 2px 8px var(--primary-glow);
        }

        .btn-primary:hover {
            background: var(--primary-hover);
        }

        /* PAGE BODY */
        .page-content {
            padding: 28px;
            flex: 1;
        }

        /* CARDS */
        .card {
            background: white;
            border-radius: 16px;
            border: 1px solid var(--border-color);
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
            margin-bottom: 24px;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 600;
            font-size: 0.875rem;
            border: 1px solid #bbf7d0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 600;
            font-size: 0.875rem;
            border: 1px solid #fecaca;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* RESPONSIVE HAMBURGER & BACKDROP */
        .btn-hamburger {
            display: none;
            background: white;
            color: var(--text-main);
            border: 1px solid var(--border-color);
        }

        .btn-sidebar-close {
            display: none;
            background: transparent;
            border: none;
            color: #94a3b8;
            font-size: 1.25rem;
            cursor: pointer;
            padding: 6px 10px;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .btn-sidebar-close:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(4px);
            z-index: 45;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .sidebar-backdrop.active {
            display: block;
            opacity: 1;
        }

        .status-text-short {
            display: none;
        }

        /* RESPONSIVE MEDIA QUERIES */
        @media (max-width: 992px) {
            .merchant-sidebar {
                transform: translateX(-100%);
                z-index: 100;
                width: 275px;
                box-shadow: 0 0 35px rgba(0, 0, 0, 0.5);
            }

            .merchant-sidebar.active {
                transform: translateX(0);
            }

            .btn-hamburger {
                display: flex;
            }

            .btn-sidebar-close {
                display: block;
            }

            .merchant-main {
                margin-left: 0;
                width: 100%;
            }

            .merchant-topbar {
                padding: 0 16px;
                height: 64px;
            }

            .page-content {
                padding: 20px 14px;
            }
        }

        @media (max-width: 640px) {
            .topbar-left {
                gap: 8px;
            }

            .topbar-title {
                display: none;
            }

            .store-status-pill {
                padding: 6px 10px;
                font-size: 0.72rem;
            }

            .status-text-full {
                display: none;
            }

            .status-text-short {
                display: inline;
            }

            .topbar-actions {
                gap: 6px;
            }

            .btn-primary.btn-cashier {
                padding: 7px 10px !important;
                font-size: 0.75rem;
            }

            .btn-primary.btn-cashier span {
                display: none;
            }

            .btn-icon {
                width: 34px;
                height: 34px;
                font-size: 0.82rem;
            }

            .card {
                padding: 16px;
                border-radius: 14px;
            }
        }
    </style>
</head>

<body>

    <!-- Audio Element for Live Order Chime -->
    <audio id="orderAlarmAudio" preload="auto">
        <source src="data:audio/wav;base64,UklGRl9vT19XQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YU..." type="audio/wav">
    </audio>

    <!-- Mobile Sidebar Backdrop Overlay -->
    <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <aside class="merchant-sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <i class="fa-solid <?= $isMart ? 'fa-cart-shopping' : 'fa-utensils' ?>"></i>
            </div>
            <div style="flex: 1; min-width: 0;">
                <div class="brand-text"><?= $brandName ?></div>
                <div class="brand-tag"><?= $portalTag ?></div>
            </div>
            <button type="button" class="btn-sidebar-close" onclick="toggleSidebar()" title="Tutup Menu">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="sidebar-menu">
            <div class="menu-category">Utama</div>
            <a href="<?= base_url('merchant/dashboard') ?>" class="menu-item <?= (uri_string() == 'merchant/dashboard' || uri_string() == 'merchant') ? 'active' : '' ?>">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Dashboard</span>
            </a>
            <a href="<?= base_url('merchant/orders/live') ?>" class="menu-item <?= (strpos(uri_string(), 'merchant/orders') !== false) ? 'active' : '' ?>">
                <i class="fa-solid fa-bell-concierge"></i>
                <span>Pesanan Live (Kasir)</span>
                <span class="badge" id="liveOrderBadge" style="display: none;">0</span>
            </a>

            <div class="menu-category"><?= $categoryCatalog ?></div>
            <a href="<?= base_url('merchant/menus') ?>" class="menu-item <?= (strpos(uri_string(), 'merchant/menus') !== false) ? 'active' : '' ?>">
                <i class="fa-solid <?= $menuManageIcon ?>"></i>
                <span><?= $menuManageLabel ?></span>
            </a>
            <a href="<?= base_url('merchant/profile') ?>" class="menu-item <?= (strpos(uri_string(), 'merchant/profile') !== false) ? 'active' : '' ?>">
                <i class="fa-solid fa-store"></i>
                <span>Profil & Jam Toko</span>
            </a>

            <div class="menu-category">Bantuan</div>
            <a href="https://wa.me/6281234567890" target="_blank" class="menu-item">
                <i class="fa-brands fa-whatsapp"></i>
                <span>Hubungi CS Maijek</span>
            </a>
        </div>

        <div class="sidebar-footer">
            <div class="merchant-profile-card">
                <?php
                $mPhoto = session()->get('merchant_photo');
                $photoUrl = !empty($mPhoto) ? base_url($mPhoto) : 'https://ui-avatars.com/api/?name=' . urlencode(session()->get('merchant_name') ?? 'Toko') . '&background=ea580c&color=fff';
                ?>
                <img src="<?= $photoUrl ?>" alt="Toko" class="merchant-avatar">
                <div style="flex: 1; min-width: 0;">
                    <div style="color: white; font-weight: 700; font-size: 0.85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        <?= esc(session()->get('merchant_name')) ?>
                    </div>
                    <div style="color: #64748b; font-size: 0.75rem;">
                        <?= esc(session()->get('merchant_phone')) ?>
                    </div>
                </div>
                <a href="<?= base_url('merchant/logout') ?>" title="Keluar" style="color: #ef4444; font-size: 1rem; padding: 4px;">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="merchant-main">
        <!-- Topbar -->
        <header class="merchant-topbar">
            <div class="topbar-left">
                <!-- Hamburger Button for Mobile/Tablet -->
                <button type="button" class="btn-icon btn-hamburger" onclick="toggleSidebar()" id="sidebarToggleBtn" title="Buka Menu Navigasi">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="topbar-title"><?= $title ?? $panelTitle ?></div>

                <!-- Status Toko Toggle Switch -->
                <button type="button" onclick="toggleStoreStatus()" id="storeStatusBtn" class="store-status-pill <?= (session()->get('merchant_is_open') == 1) ? 'open' : 'closed' ?>" title="Klik untuk membuka / menutup penerimaan pesanan toko">
                    <span class="status-dot"></span>
                    <span id="storeStatusText">
                        <span class="status-text-full"><?= (session()->get('merchant_is_open') == 1) ? 'Toko Buka (Menerima Order)' : 'Toko Tutup Sementara' ?></span>
                        <span class="status-text-short"><?= (session()->get('merchant_is_open') == 1) ? 'Buka' : 'Tutup' ?></span>
                    </span>
                </button>
            </div>

            <div class="topbar-actions">
                <a href="<?= base_url('merchant/orders/live') ?>" class="btn-primary btn-cashier" style="padding: 8px 16px;">
                    <i class="fa-solid fa-cash-register"></i> <span>Layar Kasir</span>
                </a>
                <button type="button" class="btn-icon" onclick="toggleMuteAudio()" id="muteAudioBtn" title="Suara Notifikasi Order">
                    <i class="fa-solid fa-volume-high" id="muteAudioIcon"></i>
                </button>
                <button type="button" class="btn-icon" onclick="toggleFullScreen()" title="Layar Penuh (Full Screen)">
                    <i class="fa-solid fa-expand"></i>
                </button>
            </div>
        </header>

        <!-- Body Section -->
        <main class="page-content">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <span><?= session()->getFlashdata('success') ?></span>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert-error">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span><?= session()->getFlashdata('error') ?></span>
                </div>
            <?php endif; ?>

            <?= $this->renderSection('content') ?>
        </main>
    </div>

    <!-- Audio & Global Script -->
    <script>
        let isAudioMuted = false;
        let audioCtx = null;
        let silentAudioElement = null;
        let audioHeartbeatInterval = null;

        // 💓 LAPIS 1: Silent Audio Heartbeat (Anti-Sleep Tab & Background Audio Protection)
        function startSilentHeartbeat() {
            // 1. Silent HTML5 Audio Loop: Membuat Chrome/Edge menganggap tab ini sebagai "Media Player Aktif"
            // Browser Chromium TIDAK PERNAH menidurkan/discard tab yang sedang memutar media!
            if (!silentAudioElement) {
                try {
                    silentAudioElement = new Audio("data:audio/wav;base64,UklGRigAAABXQVZFZm10IBIAAAABAAEARKwAAIhYAQACABAAAABkYXRhAgAAAAEA");
                    silentAudioElement.loop = true;
                    silentAudioElement.volume = 0.001; // nyaris tak terdengar, namun aktif di audio mixer OS
                    silentAudioElement.play().catch(() => {});
                } catch (e) {}
            }

            // 2. Web Audio Keepalive Tick: Mencegah AudioContext di-suspend otomatis oleh browser saat tab idle
            if (!audioHeartbeatInterval && audioCtx) {
                audioHeartbeatInterval = setInterval(() => {
                    try {
                        if (audioCtx) {
                            if (audioCtx.state === 'suspended') {
                                audioCtx.resume();
                            }
                            // Emisi detak sub-audible (0.00001) menjaga pipeline audio tetap 'running'
                            const osc = audioCtx.createOscillator();
                            const gain = audioCtx.createGain();
                            gain.gain.value = 0.00001;
                            osc.connect(gain);
                            gain.connect(audioCtx.destination);
                            osc.start();
                            osc.stop(audioCtx.currentTime + 0.05);
                        }
                    } catch (e) {}
                }, 20000); // detak setiap 20 detik
            }
        }

        function getAudioContext() {
            if (!audioCtx) {
                audioCtx = new(window.AudioContext || window.webkitAudioContext)();
            }
            if (audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
            startSilentHeartbeat();
            return audioCtx;
        }

        // Unlock audio context on user interaction
        document.addEventListener('click', getAudioContext, {
            once: false
        });
        document.addEventListener('keydown', getAudioContext, {
            once: false
        });

        // Web Audio Synthesizer: Nada Dering Bel Kasir / Sepeda "Kring-Kring" Nyaring
        function playOrderChime() {
            if (isAudioMuted) return;
            try {
                const ctx = getAudioContext();
                if (ctx.state === 'suspended') {
                    ctx.resume();
                }
                const now = ctx.currentTime;

                // Compressor dinamis agar suara sangat nyaring & bulat tanpa distorsi di speaker kasir/laptop
                const compressor = ctx.createDynamicsCompressor();
                compressor.threshold.setValueAtTime(-10, now);
                compressor.knee.setValueAtTime(20, now);
                compressor.ratio.setValueAtTime(12, now);
                compressor.attack.setValueAtTime(0.002, now);
                compressor.release.setValueAtTime(0.2, now);
                compressor.connect(ctx.destination);

                // Master gain untuk volume maksimal
                const masterGain = ctx.createGain();
                masterGain.gain.setValueAtTime(1.0, now);
                masterGain.connect(compressor);

                // Fungsi menghasilkan 1 ketukan bel lonceng (strike)
                function strikeBell(time, isLast) {
                    const freqs = [{
                            f: 2093,
                            type: 'sine',
                            vol: 0.75
                        }, // Fundamental C7 (Lonceng nyaring)
                        {
                            f: 2793,
                            type: 'triangle',
                            vol: 0.55
                        }, // Overtone F7 (Karakter kuningan/brass)
                        {
                            f: 3520,
                            type: 'sine',
                            vol: 0.35
                        } // Shimmer A7 (Resonansi tajam)
                    ];

                    const duration = isLast ? 0.55 : 0.07;

                    freqs.forEach(item => {
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();

                        osc.type = item.type;
                        osc.frequency.setValueAtTime(item.f, time);

                        // Enveloping ketukan cepat
                        gain.gain.setValueAtTime(item.vol, time);
                        gain.gain.exponentialRampToValueAtTime(0.001, time + duration);

                        osc.connect(gain);
                        gain.connect(masterGain);

                        osc.start(time);
                        osc.stop(time + duration);
                    });
                }

                // 1 siklus "Kring" (getaran roda palu 7 ketukan cepat)
                function playKring(startTime) {
                    const strikeCount = 7;
                    const interval = 0.034; // 34ms per getaran palu
                    for (let i = 0; i < strikeCount; i++) {
                        const t = startTime + (i * interval);
                        strikeBell(t, i === strikeCount - 1);
                    }
                }

                // Pola Lonceng: "Kring-Kring! ... Kring-Kring!" Nyaring
                // Siklus 1
                playKring(now);
                playKring(now + 0.32);

                // Siklus 2 (setelah jeda singkat agar kasir langsung tanggap)
                playKring(now + 0.95);
                playKring(now + 1.27);

            } catch (e) {
                console.error("Audio chime error:", e);
            }
        }

        function toggleMuteAudio() {
            isAudioMuted = !isAudioMuted;
            const icon = document.getElementById('muteAudioIcon');
            if (isAudioMuted) {
                icon.className = 'fa-solid fa-volume-xmark';
                icon.style.color = '#ef4444';
            } else {
                icon.className = 'fa-solid fa-volume-high';
                icon.style.color = '';
                playOrderChime();
            }
        }

        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        }

        function toggleSidebar() {
            const sidebar = document.querySelector('.merchant-sidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            if (sidebar && backdrop) {
                const isActive = sidebar.classList.toggle('active');
                backdrop.classList.toggle('active', isActive);
                document.body.style.overflow = isActive ? 'hidden' : '';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.sidebar-menu .menu-item').forEach(item => {
                item.addEventListener('click', () => {
                    if (window.innerWidth <= 992) {
                        toggleSidebar();
                    }
                });
            });
        });

        function toggleStoreStatus() {
            fetch('<?= base_url('merchant/dashboard/toggleStatus') ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 200) {
                        const btn = document.getElementById('storeStatusBtn');
                        const txt = document.getElementById('storeStatusText');
                        if (data.is_open == 1) {
                            btn.className = 'store-status-pill open';
                            txt.innerHTML = '<span class="status-text-full">Toko Buka (Menerima Order)</span><span class="status-text-short">Buka</span>';
                        } else {
                            btn.className = 'store-status-pill closed';
                            txt.innerHTML = '<span class="status-text-full">Toko Tutup Sementara</span><span class="status-text-short">Tutup</span>';
                        }
                    }
                });
        }
    </script>
</body>

</html>