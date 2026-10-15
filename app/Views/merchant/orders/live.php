<?= $this->extend('merchant/layout/main') ?>

<?= $this->section('content') ?>

<?php
$isMart = (session()->get('merchant_type') === 'mart');
$processingLabel = $isMart ? 'Sedang Disiapkan' : 'Sedang Dimasak';
$processingEmpty = $isMart ? 'Tidak ada pesanan disiapkan' : 'Tidak ada pesanan dimasak';
$processingTab = $isMart ? 'Disiapkan' : 'Dimasak';
$processingIcon = $isMart ? 'fa-box-open' : 'fa-fire-burner';
?>

<style>
    .kanban-board {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        min-height: calc(100vh - 170px);
        align-items: flex-start;
    }

    .kanban-column {
        background: #f1f5f9;
        border-radius: 18px;
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 14px;
        max-height: calc(100vh - 170px);
        overflow-y: auto;
        border: 1px solid var(--border-color);
    }

    .column-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 4px 6px;
    }

    .column-title {
        font-size: 0.95rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--text-main);
    }

    .column-count {
        background: white;
        color: var(--text-main);
        font-size: 0.8rem;
        font-weight: 800;
        padding: 2px 10px;
        border-radius: 99px;
        border: 1px solid var(--border-color);
    }

    /* CARD PESANAN */
    .order-card {
        background: white;
        border-radius: 16px;
        padding: 16px;
        border: 1px solid var(--border-color);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        transition: all 0.2s;
        display: flex;
        flex-direction: column;
        gap: 12px;
        position: relative;
    }

    .order-card.new-alert {
        border: 2px solid #ea580c;
        box-shadow: 0 0 15px rgba(234, 88, 12, 0.25);
        animation: glow 1.5s infinite alternate;
    }

    @keyframes glow {
        from {
            box-shadow: 0 0 8px rgba(234, 88, 12, 0.2);
        }

        to {
            box-shadow: 0 0 18px rgba(234, 88, 12, 0.45);
        }
    }

    .card-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px dashed var(--border-color);
        padding-bottom: 10px;
    }

    .order-id {
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--primary);
    }

    .order-time {
        font-size: 0.75rem;
        color: var(--text-muted);
        font-weight: 600;
    }

    .customer-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .customer-avatar {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        font-size: 0.85rem;
    }

    .item-list {
        background: #f8fafc;
        border-radius: 10px;
        padding: 10px 12px;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .item-row {
        display: flex;
        justify-content: space-between;
        font-size: 0.85rem;
    }

    .item-qty {
        font-weight: 800;
        color: var(--primary);
        margin-right: 6px;
    }

    .item-notes {
        font-size: 0.75rem;
        color: #ea580c;
        font-style: italic;
        margin-top: 2px;
        padding-left: 18px;
    }

    .vehicle-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .vehicle-badge.motor {
        background: #ffedd5;
        color: #c2410c;
    }

    .vehicle-badge.mobil {
        background: #dbeafe;
        color: #1e40af;
    }

    .card-actions {
        display: flex;
        gap: 8px;
        margin-top: 4px;
    }

    .btn-action {
        flex: 1;
        padding: 10px;
        border-radius: 10px;
        border: none;
        font-weight: 700;
        font-size: 0.85rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: all 0.2s;
    }

    .btn-accept {
        background: #16a34a;
        color: white;
    }

    .btn-accept:hover {
        background: #15803d;
    }

    .btn-ready {
        background: #2563eb;
        color: white;
    }

    .btn-ready:hover {
        background: #1d4ed8;
    }

    .btn-reject {
        background: #fee2e2;
        color: #dc2626;
    }

    .btn-reject:hover {
        background: #fca5a5;
    }

    /* Modal Tolak Pesanan */
    .modal-backdrop {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(4px);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }

    .modal-box {
        background: white;
        border-radius: 20px;
        width: 90%;
        max-width: 480px;
        padding: 24px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        animation: modalPop 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes modalPop {
        from { transform: scale(0.95); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }

    .reason-option-label {
        transition: all 0.15s ease;
    }

    .reason-option-label:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    .btn-print {
        background: #f1f5f9;
        color: #475569;
        padding: 10px 14px;
        border-radius: 10px;
        border: 1px solid var(--border-color);
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-print:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #94a3b8;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .live-orders-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .kanban-mobile-tabs {
        display: none;
        gap: 8px;
        margin-bottom: 16px;
        overflow-x: auto;
        padding-bottom: 4px;
        -webkit-overflow-scrolling: touch;
    }

    .kanban-tab-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        background: white;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--text-muted);
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.2s;
    }

    .kanban-tab-btn.active {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
        box-shadow: 0 4px 12px var(--primary-glow);
    }

    .kanban-tab-btn.active i {
        color: white !important;
    }

    .tab-badge {
        background: #f1f5f9;
        color: var(--text-main);
        font-size: 0.75rem;
        padding: 2px 7px;
        border-radius: 99px;
        font-weight: 800;
    }

    .kanban-tab-btn.active .tab-badge {
        background: rgba(255, 255, 255, 0.25);
        color: white;
    }

    @media (max-width: 992px) {
        .kanban-board {
            grid-template-columns: 1fr;
            gap: 16px;
            min-height: auto;
        }

        .kanban-column {
            max-height: none;
        }

        .kanban-mobile-tabs {
            display: flex;
        }

        .kanban-board.filter-pending .kanban-column:not(.col-pending) {
            display: none;
        }

        .kanban-board.filter-processing .kanban-column:not(.col-processing) {
            display: none;
        }

        .kanban-board.filter-ready .kanban-column:not(.col-ready) {
            display: none;
        }
    }

    @media (max-width: 640px) {
        .live-orders-header h2 {
            font-size: 1.15rem;
        }

        .live-orders-header p {
            font-size: 0.78rem;
        }
    }
</style>

<div class="live-orders-header">
    <div>
        <h2 style="font-size: 1.35rem; font-weight: 800; letter-spacing: -0.5px;">Layar Kasir Pesanan Live</h2>
        <p style="font-size: 0.85rem; color: var(--text-muted);">Sistem otomatis memperbarui daftar pesanan setiap 4 detik. Pastikan suara speaker aktif.</p>
    </div>
    <div style="display: flex; align-items: center; gap: 12px;">
        <span style="font-size: 0.8rem; color: var(--text-muted);" id="lastUpdateTime">Memuat...</span>
        <button onclick="fetchLiveOrders()" class="btn-icon" title="Segarkan Manual">
            <i class="fa-solid fa-arrows-rotate"></i>
        </button>
    </div>
</div>

<!-- Audio Unlock Overlay -->
<div id="audioUnlockOverlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 9999; display: flex; flex-direction: column; align-items: center; justify-content: center; color: white;">
    <i class="fa-solid fa-volume-high" style="font-size: 4rem; color: #10b981; margin-bottom: 20px; animation: bounce 2s infinite;"></i>
    <h2 style="font-size: 2rem; font-weight: 800; margin-bottom: 10px;">Layar Kasir Siap!</h2>
    <p style="font-size: 1.1rem; color: #cbd5e1; margin-bottom: 30px; text-align: center; max-width: 500px;">
        Sistem keamanan browser mewajibkan Anda untuk menekan tombol di bawah ini agar <b>Suara Alarm Pesanan Baru</b> dapat berbunyi otomatis.
    </p>
    <button onclick="unlockAudioAndStart()" style="background: #10b981; color: white; border: none; padding: 15px 40px; font-size: 1.2rem; font-weight: 800; border-radius: 99px; cursor: pointer; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);">
        AKTIFKAN SUARA & MULAI PANTAU
    </button>
</div>

<style>
    @keyframes bounce {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-15px);
        }
    }
</style>

<script>
    let wakeLock = null;

    async function requestWakeLock() {
        try {
            if ('wakeLock' in navigator) {
                wakeLock = await navigator.wakeLock.request('screen');
                console.log('Screen Wake Lock aktif: Layar iPhone / HP tidak akan mati otomatis saat memantau order.');
            }
        } catch (err) {
            console.warn('Wake Lock error:', err);
        }
    }

    function unlockAudioAndStart() {
        document.getElementById('audioUnlockOverlay').style.display = 'none';
        if (typeof getAudioContext === 'function') {
            const ctx = getAudioContext();
            if (ctx.state === 'suspended') {
                ctx.resume();
            }
        }
        // Play a very short, silent sound just to permanently unlock the context in this tab
        try {
            const tempOsc = getAudioContext().createOscillator();
            const tempGain = getAudioContext().createGain();
            tempGain.gain.value = 0.01;
            tempOsc.connect(tempGain);
            tempGain.connect(getAudioContext().destination);
            tempOsc.start(getAudioContext().currentTime);
            tempOsc.stop(getAudioContext().currentTime + 0.1);
        } catch (e) {}

        // Request Screen Wake Lock (Layar iPhone / Ponsel tetap menyala terus di meja kasir)
        requestWakeLock();

        // Aktifkan Silent Heartbeat Anti-Sleep
        if (typeof startSilentHeartbeat === 'function') {
            startSilentHeartbeat();
        }

        // Minta izin Notifikasi Pop-up sistem jika didukung
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }
</script>

<script>
    function switchKanbanTab(type, btn) {
        document.querySelectorAll('.kanban-tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const board = document.querySelector('.kanban-board');
        board.classList.remove('filter-pending', 'filter-processing', 'filter-ready');
        if (type !== 'all') {
            board.classList.add('filter-' + type);
        }
    }
</script>

<!-- Mobile Column Filter Tabs (Only visible on screens <= 992px) -->
<div class="kanban-mobile-tabs">
    <button type="button" class="kanban-tab-btn active" onclick="switchKanbanTab('all', this)">
        <span>Semua Kolom</span>
    </button>
    <button type="button" class="kanban-tab-btn" onclick="switchKanbanTab('pending', this)">
        <i class="fa-solid fa-bell" style="color: #ea580c;"></i>
        <span>Pesanan Baru</span>
        <span class="tab-badge" id="tabPendingBadge">0</span>
    </button>
    <button type="button" class="kanban-tab-btn" onclick="switchKanbanTab('processing', this)">
        <i class="fa-solid <?= $processingIcon ?>" style="color: #f59e0b;"></i>
        <span><?= $processingTab ?></span>
        <span class="tab-badge" id="tabProcessingBadge">0</span>
    </button>
    <button type="button" class="kanban-tab-btn" onclick="switchKanbanTab('ready', this)">
        <i class="fa-solid fa-person-biking" style="color: #2563eb;"></i>
        <span>Siap Driver</span>
        <span class="tab-badge" id="tabReadyBadge">0</span>
    </button>
</div>

<div class="kanban-board">
    <!-- Kolom 1: Pesanan Masuk (Pending) -->
    <div class="kanban-column col-pending">
        <div class="column-header">
            <div class="column-title">
                <i class="fa-solid fa-bell" style="color: #ea580c;"></i>
                <span>Pesanan Baru</span>
            </div>
            <span class="column-count" id="pendingCount">0</span>
        </div>
        <div id="pendingContainer">
            <div class="empty-state">Tidak ada pesanan baru</div>
        </div>
    </div>

    <!-- Kolom 2: <?= $processingLabel ?> (Processing) -->
    <div class="kanban-column col-processing">
        <div class="column-header">
            <div class="column-title">
                <i class="fa-solid <?= $processingIcon ?>" style="color: #f59e0b;"></i>
                <span><?= $processingLabel ?></span>
            </div>
            <span class="column-count" id="processingCount">0</span>
        </div>
        <div id="processingContainer">
            <div class="empty-state"><?= $processingEmpty ?></div>
        </div>
    </div>

    <!-- Kolom 3: Siap Diambil Driver (Ready / Assigned) -->
    <div class="kanban-column col-ready">
        <div class="column-header">
            <div class="column-title">
                <i class="fa-solid fa-person-biking" style="color: #2563eb;"></i>
                <span>Siap Diambil Driver</span>
            </div>
            <span class="column-count" id="readyCount">0</span>
        </div>
        <div id="readyContainer">
            <div class="empty-state">Tidak ada pesanan siap diambil</div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Tolak Pesanan -->
<div id="rejectOrderModal" class="modal-backdrop" style="display: none;">
    <div class="modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="background: #fee2e2; color: #dc2626; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h3 style="font-size: 1.1rem; font-weight: 800; color: #1e293b; margin: 0;">Tolak Pesanan <span id="rejectModalOrderId">#0</span></h3>
                    <p style="font-size: 0.78rem; color: #64748b; margin: 2px 0 0 0;">Pilih alasan penolakan untuk pelanggan</p>
                </div>
            </div>
            <button type="button" onclick="closeRejectModal()" style="background: none; border: none; font-size: 1.4rem; color: #94a3b8; cursor: pointer;">&times;</button>
        </div>

        <div style="margin-bottom: 16px;">
            <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #475569; margin-bottom: 8px;">PILIH ALASAN PENOLAKAN:</label>
            <div style="display: flex; flex-direction: column; gap: 8px;" id="rejectReasonOptions">
                <label style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; border: 1.5px solid #e2e8f0; border-radius: 12px; cursor: pointer; font-size: 0.88rem; font-weight: 600; color: #1e293b;" class="reason-option-label">
                    <input type="radio" name="reject_reason" value="Stok barang/menu habis" checked onchange="toggleCustomReason(false)">
                    <span>📦 Stok barang / menu habis</span>
                </label>
                <label style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; border: 1.5px solid #e2e8f0; border-radius: 12px; cursor: pointer; font-size: 0.88rem; font-weight: 600; color: #1e293b;" class="reason-option-label">
                    <input type="radio" name="reject_reason" value="Toko/restoran sedang tutup atau istirahat" onchange="toggleCustomReason(false)">
                    <span>🚪 Toko / restoran sedang tutup / istirahat</span>
                </label>
                <label style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; border: 1.5px solid #e2e8f0; border-radius: 12px; cursor: pointer; font-size: 0.88rem; font-weight: 600; color: #1e293b;" class="reason-option-label">
                    <input type="radio" name="reject_reason" value="Antrean pesanan sedang terlalu ramai" onchange="toggleCustomReason(false)">
                    <span>⏳ Antrean pesanan sedang terlalu ramai</span>
                </label>
                <label style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; border: 1.5px solid #e2e8f0; border-radius: 12px; cursor: pointer; font-size: 0.88rem; font-weight: 600; color: #1e293b;" class="reason-option-label">
                    <input type="radio" name="reject_reason" value="Bahan baku / produk tidak tersedia" onchange="toggleCustomReason(false)">
                    <span>⚠️ Bahan baku / produk tidak tersedia</span>
                </label>
                <label style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; border: 1.5px solid #e2e8f0; border-radius: 12px; cursor: pointer; font-size: 0.88rem; font-weight: 600; color: #1e293b;" class="reason-option-label">
                    <input type="radio" name="reject_reason" value="other" onchange="toggleCustomReason(true)">
                    <span>✏️ Alasan lainnya...</span>
                </label>
            </div>

            <div id="customReasonWrapper" style="display: none; margin-top: 10px;">
                <textarea id="customReasonInput" rows="2" placeholder="Tuliskan alasan penolakan secara jelas..." style="width: 100%; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 10px; font-size: 0.85rem; outline: none; box-sizing: border-box; font-family: inherit;"></textarea>
            </div>
        </div>

        <div style="background: #fef2f2; border: 1px dashed #f87171; border-radius: 10px; padding: 10px 12px; font-size: 0.76rem; color: #991b1b; margin-bottom: 18px; line-height: 1.4;">
            <i class="fa-solid fa-circle-info"></i> Pesanan yang ditolak akan dibatalkan, dan jika pelanggan membayar via Mai-Pay saldo akan otomatis dikembalikan 100%.
        </div>

        <div style="display: flex; gap: 10px; justify-content: flex-end;">
            <button type="button" onclick="closeRejectModal()" style="padding: 10px 18px; border-radius: 10px; background: #f1f5f9; border: 1px solid #cbd5e1; font-weight: 700; font-size: 0.85rem; color: #475569; cursor: pointer;">
                Batal
            </button>
            <button type="button" id="confirmRejectBtn" onclick="confirmRejectOrder()" style="padding: 10px 20px; border-radius: 10px; background: #dc2626; border: none; font-weight: 700; font-size: 0.85rem; color: white; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-ban"></i> Tolak Pesanan Ini
            </button>
        </div>
    </div>
</div>

<script>
    let knownOrderIds = new Set();
    let isFirstLoad = true;
    const isMart = <?= $isMart ? 'true' : 'false' ?>;

    function fetchLiveOrders() {
        fetch('<?= base_url('merchant/orders/api-live') ?>')
            .then(res => res.json())
            .then(res => {
                if (res.status === 200) {
                    renderOrders(res.data);
                    document.getElementById('lastUpdateTime').innerText = 'Terakhir update: ' + res.time;
                }
            })
            .catch(err => console.error("Error live orders:", err));
    }

    function renderOrders(orders) {
        const pendingContainer = document.getElementById('pendingContainer');
        const processingContainer = document.getElementById('processingContainer');
        const readyContainer = document.getElementById('readyContainer');

        let pendingHtml = '';
        let processingHtml = '';
        let readyHtml = '';

        let pendingCount = 0;
        let processingCount = 0;
        let readyCount = 0;
        let hasNewPendingOrder = false;
        const currentIds = new Set(orders.map(o => o.id));

        orders.forEach(order => {
            const isPending = (order.status === 'pending' || order.status === 'accepted_by_merchant');
            const isProcessing = (order.status === 'processing');
            const isReady = (order.status === 'ready' || order.status === 'driver_assigned' || order.status === 'accepted' || order.status === 'driver_at_merchant' || order.status === 'delivering' || order.status === 'in_progress');

            if (isPending) {
                pendingCount++;
                if (!knownOrderIds.has(order.id) && !isFirstLoad) {
                    hasNewPendingOrder = true;
                }
                pendingHtml += buildOrderCard(order, 'pending');
            } else if (isProcessing) {
                processingCount++;
                processingHtml += buildOrderCard(order, 'processing');
            } else if (isReady) {
                readyCount++;
                readyHtml += buildOrderCard(order, 'ready');
            }
        });

        knownOrderIds = currentIds;

        // Update counts and badge
        document.getElementById('pendingCount').innerText = pendingCount;
        document.getElementById('processingCount').innerText = processingCount;
        document.getElementById('readyCount').innerText = readyCount;

        const tabPending = document.getElementById('tabPendingBadge');
        if (tabPending) tabPending.innerText = pendingCount;
        const tabProcessing = document.getElementById('tabProcessingBadge');
        if (tabProcessing) tabProcessing.innerText = processingCount;
        const tabReady = document.getElementById('tabReadyBadge');
        if (tabReady) tabReady.innerText = readyCount;

        const badge = document.getElementById('liveOrderBadge');
        const portalName = isMart ? 'MaiMart' : 'MaiFood';
        if (pendingCount > 0) {
            badge.innerText = pendingCount;
            badge.style.display = 'inline-block';
            document.title = '(' + pendingCount + ' Baru!) Layar Kasir ' + portalName;
        } else {
            badge.style.display = 'none';
            document.title = 'Layar Kasir ' + portalName;
        }

        // Set HTML or empty state
        pendingContainer.innerHTML = pendingHtml || '<div class="empty-state">Tidak ada pesanan baru</div>';
        processingContainer.innerHTML = processingHtml || ('<div class="empty-state">' + (isMart ? 'Tidak ada pesanan disiapkan' : 'Tidak ada pesanan dimasak') + '</div>');
        readyContainer.innerHTML = readyHtml || '<div class="empty-state">Tidak ada pesanan siap diambil</div>';

        // Play chime continuously every polling tick if there's an unaccepted order
        if (pendingCount > 0 && !isFirstLoad) {
            playOrderChime();
        }

        // 🔔 Picu Notifikasi Asli Windows (Desktop Toast) saat pesanan baru pertama kali terdeteksi
        if (hasNewPendingOrder) {
            if ('Notification' in window && Notification.permission === 'granted') {
                try {
                    const notif = new Notification('🔔 PESANAN ' + (isMart ? 'MAIMART' : 'MAIFOOD') + ' BARU MASUK!', {
                        body: 'Ada ' + pendingCount + ' pesanan baru di Layar Kasir. Klik untuk membuka!',
                        icon: '<?= base_url('favicon.ico') ?>',
                        requireInteraction: true // Notifikasi tetap nongkrong di layar komputer sampai diklik!
                    });
                    notif.onclick = function() {
                        window.focus();
                        this.close();
                    };
                } catch (e) {
                    console.warn('Desktop notification error:', e);
                }
            }
        }

        isFirstLoad = false;
    }

    function buildOrderCard(order, column) {
        let itemsHtml = '';
        if (order.items && order.items.length > 0) {
            order.items.forEach(item => {
                itemsHtml += `
                    <div>
                        <div class="item-row">
                            <span><strong class="item-qty">${item.quantity}x</strong> ${escapeHtml(item.menu_name || (isMart ? 'Produk' : 'Menu'))}</span>
                            <span>Rp ${(Number(item.price) * item.quantity).toLocaleString('id-ID')}</span>
                        </div>
                        ${item.notes ? `<div class="item-notes"><i class="fa-solid fa-pencil"></i> ${escapeHtml(item.notes)}</div>` : ''}
                    </div>
                `;
            });
        }

        const vehicle = (order.vehicle_type === 'mobil') ? 'mobil' : 'motor';
        const vehicleLabel = (vehicle === 'mobil') ? '🚗 Kurir Mobil (Box)' : '🛵 Kurir Motor';
        const isCash = (order.payment_method === 'cash');
        const paymentLabel = isCash ? '💵 Tunai (Ditalangi Kurir)' : '💳 Mai-Pay (Lunas Digital)';

        const foodTotal = Number(order.food_total || (order.total_price - (order.delivery_fee || 0)));
        const deliveryFee = Number(order.delivery_fee || 0);
        const totalPrice = Number(order.total_price);

        let paymentBreakdownHtml = '';
        if (isCash) {
            paymentBreakdownHtml = `
                <div style="background: #ecfdf5; border: 1.5px solid #10b981; border-radius: 12px; padding: 10px 12px; margin-top: 6px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem; font-weight: 800; color: #047857;">
                        <span><i class="fa-solid fa-hand-holding-dollar"></i> TAGIHAN KASIR:</span>
                        <span style="font-size: 1.15rem; color: #065f46;">Rp ${foodTotal.toLocaleString('id-ID')}</span>
                    </div>
                    <div style="margin-top: 6px; padding-top: 6px; border-top: 1px dashed #a7f3d0; display: flex; justify-content: space-between; font-size: 0.75rem; color: #065f46;">
                        <span>🛵 Ongkir Kurir (Hak Kurir):</span>
                        <span style="font-weight: 700;">Rp ${deliveryFee.toLocaleString('id-ID')}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                        <span>🏷️ Ditagih Kurir ke Pelanggan:</span>
                        <span style="font-weight: 600;">Rp ${totalPrice.toLocaleString('id-ID')}</span>
                    </div>
                    <div style="background: #d1fae5; border-radius: 6px; padding: 4px 8px; margin-top: 6px; font-size: 0.72rem; color: #065f46; font-weight: 600; line-height: 1.3;">
                        ⚠️ <em>Kasir HANYA menerima <strong>Rp ${foodTotal.toLocaleString('id-ID')}</strong> (hanya belanja ${isMart ? 'barang/produk' : 'makanan'})! Jangan menagih ongkir ke kurir.</em>
                    </div>
                </div>
            `;
        } else {
            paymentBreakdownHtml = `
                <div style="background: #f0fdf4; border: 1.5px solid #22c55e; border-radius: 12px; padding: 10px 12px; margin-top: 6px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.8rem; font-weight: 700; color: #166534;"><i class="fa-solid fa-circle-check"></i> PEMBAYARAN:</span>
                        <span style="background: #16a34a; color: white; padding: 2px 8px; border-radius: 6px; font-size: 0.72rem; font-weight: 800;">LUNAS DIGITAL (MAI-PAY)</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 6px; font-size: 0.85rem; font-weight: 800; color: #15803d;">
                        <span>Nilai Belanja ${isMart ? 'Toko' : 'Resto'}:</span>
                        <span style="font-size: 1.1rem;">Rp ${foodTotal.toLocaleString('id-ID')}</span>
                    </div>
                    <div style="background: #dcfce7; border-radius: 6px; padding: 4px 8px; margin-top: 6px; font-size: 0.72rem; color: #166534; font-weight: 600; line-height: 1.3;">
                        ✅ <em>Pesanan sudah lunas. Kasir <strong>TIDAK MENARIK UANG</strong> dari Kurir.</em>
                    </div>
                </div>
            `;
        }

        let buttonsHtml = '';
        if (column === 'pending') {
            buttonsHtml = `
                <div class="card-actions">
                    <button type="button" onclick="updateOrderStatus(${order.id}, 'processing')" class="btn-action btn-accept">
                        <i class="fa-solid fa-check"></i> ${isMart ? 'Terima & Siapkan' : 'Terima & Masak'}
                    </button>
                    <button type="button" onclick="printReceipt(${order.id})" class="btn-print" title="Cetak Struk">
                        <i class="fa-solid fa-print"></i>
                    </button>
                    <button type="button" onclick="rejectOrder(${order.id})" class="btn-action btn-reject" style="flex: 0 0 40px;" title="Tolak Pesanan">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            `;
        } else if (column === 'processing') {
            buttonsHtml = `
                <div style="font-size: 0.8rem; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 6px 10px; border-radius: 8px; color: #065f46; font-weight: 700; display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                    <i class="fa-solid fa-spinner fa-spin" style="color: #059669;"></i> Otomatis mencari kurir di sekitar...
                </div>
                <div class="card-actions">
                    <button type="button" onclick="updateOrderStatus(${order.id}, 'ready')" class="btn-action btn-ready">
                        <i class="fa-solid fa-check-double"></i> ${isMart ? 'Selesai Dikemas (Siap Diambil Kurir)' : 'Makanan Selesai (Siap Diambil Kurir)'}
                    </button>
                    <button type="button" onclick="printReceipt(${order.id})" class="btn-print" title="Cetak Struk">
                        <i class="fa-solid fa-print"></i>
                    </button>
                </div>
            `;
        } else {
            // Ready Column: info driver penjemput & status perjalanan
            let driverStatusBadge = '';
            let driverInfo = '';

            if (order.status === 'ready' && !order.driver_name) {
                driverStatusBadge = `
                    <div style="font-size: 0.8rem; background: #fef3c7; border: 1px solid #fde047; padding: 6px 10px; border-radius: 8px; color: #b45309; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-spinner fa-spin"></i> Mencari driver di sekitar...
                    </div>
                `;
            } else if (order.status === 'driver_assigned' || order.status === 'accepted') {
                driverStatusBadge = `
                    <div style="font-size: 0.8rem; background: #eff6ff; border: 1.5px solid #60a5fa; padding: 6px 10px; border-radius: 8px; color: #1d4ed8; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-person-biking"></i> Driver Menuju ${isMart ? 'Toko' : 'Resto'}
                    </div>
                `;
            } else if (order.status === 'driver_at_merchant') {
                driverStatusBadge = `
                    <div style="font-size: 0.82rem; background: #dcfce7; border: 1.5px solid #22c55e; padding: 8px 10px; border-radius: 8px; color: #15803d; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-location-dot" style="color: #16a34a; font-size: 1.1rem;"></i> KURIR SUDAH DI ${isMart ? 'TOKO' : 'RESTORAN'}!
                    </div>
                `;
            } else if (order.status === 'delivering' || order.status === 'in_progress') {
                driverStatusBadge = `
                    <div style="font-size: 0.8rem; background: #fff7ed; border: 1.5px solid #fb923c; padding: 6px 10px; border-radius: 8px; color: #c2410c; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-box"></i> Driver Sedang Mengantar ke Pembeli
                    </div>
                `;
            }

            if (order.driver_name) {
                driverInfo = `
                    <div style="font-size: 0.85rem; background: #f8fafc; border: 1px solid var(--border-color); padding: 8px 10px; border-radius: 8px; color: #1e293b; margin-top: 6px;">
                        <div><strong>Driver:</strong> ${escapeHtml(order.driver_name)} (${escapeHtml(order.driver_plate || '-')})</div>
                        <div><i class="fa-solid fa-phone" style="color: #2563eb;"></i> ${escapeHtml(order.driver_phone || '-')}</div>
                    </div>
                `;
            }

            buttonsHtml = `
                ${driverStatusBadge}
                ${driverInfo}
                <div class="card-actions" style="margin-top: 6px;">
                    <button type="button" onclick="printReceipt(${order.id})" class="btn-print" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <i class="fa-solid fa-print"></i> Cetak Struk Pesanan
                    </button>
                </div>
            `;
        }

        return `
            <div class="order-card ${column === 'pending' ? 'new-alert' : ''}" id="order_card_${order.id}">
                <div class="card-top">
                    <div>
                        <span class="order-id">#${order.id}</span>
                        <div class="order-time">${formatTime(order.created_at)}</div>
                    </div>
                    <span class="vehicle-badge ${vehicle}">${vehicleLabel}</span>
                </div>

                <div class="customer-info">
                    <div class="customer-avatar"><i class="fa-solid fa-user"></i></div>
                    <div>
                        <div style="font-weight: 700; font-size: 0.875rem;">${escapeHtml(order.user_name || 'Pelanggan')}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">${paymentLabel}</div>
                    </div>
                </div>

                <div class="item-list">
                    ${itemsHtml}
                </div>

                ${paymentBreakdownHtml}

                ${buttonsHtml}
            </div>
        `;
    }

    function updateOrderStatus(orderId, status, reason = '') {
        const formData = new FormData();
        formData.append('status', status);
        if (reason) {
            formData.append('reason', reason);
        }

        fetch(`<?= base_url('merchant/orders/update-status/') ?>/${orderId}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 200) {
                    fetchLiveOrders();
                } else {
                    alert(res.message || 'Gagal mengubah status pesanan.');
                }
            })
            .catch(err => {
                console.error("Error updating order status:", err);
                alert("Terjadi kesalahan koneksi saat memperbarui status.");
            });
    }

    let currentRejectOrderId = null;

    function rejectOrder(orderId) {
        currentRejectOrderId = orderId;
        document.getElementById('rejectModalOrderId').innerText = '#' + orderId;
        const firstRadio = document.querySelector('input[name="reject_reason"][value="Stok barang/menu habis"]');
        if (firstRadio) firstRadio.checked = true;
        document.getElementById('customReasonWrapper').style.display = 'none';
        document.getElementById('customReasonInput').value = '';
        document.getElementById('rejectOrderModal').style.display = 'flex';
    }

    function closeRejectModal() {
        currentRejectOrderId = null;
        document.getElementById('rejectOrderModal').style.display = 'none';
    }

    function toggleCustomReason(isCustom) {
        const wrapper = document.getElementById('customReasonWrapper');
        if (isCustom) {
            wrapper.style.display = 'block';
            document.getElementById('customReasonInput').focus();
        } else {
            wrapper.style.display = 'none';
        }
    }

    function confirmRejectOrder() {
        if (!currentRejectOrderId) return;

        let selectedReason = '';
        const checkedRadio = document.querySelector('input[name="reject_reason"]:checked');
        if (checkedRadio) {
            if (checkedRadio.value === 'other') {
                selectedReason = document.getElementById('customReasonInput').value.trim();
                if (!selectedReason) {
                    alert('Mohon tuliskan alasan penolakan pada kolom yang tersedia.');
                    document.getElementById('customReasonInput').focus();
                    return;
                }
            } else {
                selectedReason = checkedRadio.value;
            }
        } else {
            selectedReason = isMart ? 'Stok barang habis' : 'Stok menu habis';
        }

        const btn = document.getElementById('confirmRejectBtn');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menolak...';

        const orderId = currentRejectOrderId;
        closeRejectModal();

        updateOrderStatus(orderId, 'cancelled', selectedReason);
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    }

    function printReceipt(orderId) {
        const printWindow = window.open(`<?= base_url('merchant/orders/print/') ?>/${orderId}`, '_blank', 'width=450,height=700');
        if (printWindow) {
            printWindow.focus();
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.innerText = text;
        return div.innerHTML;
    }

    function formatTime(dateTimeStr) {
        if (!dateTimeStr) return '';
        const d = new Date(dateTimeStr);
        return d.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    // Polling setiap 4 detik menggunakan Web Worker (Agar tetap jalan akurat di Background Tab)
    fetchLiveOrders();

    const workerCode = `
        let timerId = null;
        self.onmessage = function(e) {
            if (e.data === 'start') {
                timerId = setInterval(() => {
                    self.postMessage('tick');
                }, 4000);
            } else if (e.data === 'stop') {
                clearInterval(timerId);
            }
        };
    `;
    const blob = new Blob([workerCode], {
        type: 'application/javascript'
    });
    const worker = new Worker(URL.createObjectURL(blob));

    worker.onmessage = function(e) {
        if (e.data === 'tick') {
            fetchLiveOrders();
        }
    };
    worker.postMessage('start');

    // Otomatis refresh cepat ekstra jika tab dibuka kembali & pulihkan Wake Lock serta Audio
    document.addEventListener("visibilitychange", async () => {
        if (document.visibilityState === "visible") {
            fetchLiveOrders();
            if (typeof getAudioContext === 'function') {
                const ctx = getAudioContext();
                if (ctx.state === 'suspended') {
                    ctx.resume();
                }
            }
            if ('wakeLock' in navigator && (wakeLock === null || wakeLock.released)) {
                await requestWakeLock();
            }
        }
    });
</script>

<?= $this->endSection() ?>