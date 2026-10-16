<!-- ================= MODAL DETAIL LENGKAP PESANAN ================= -->
<div id="orderDetailModal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 16px; overflow-y: auto;" onclick="if(event.target === this) closeOrderDetail();">
    <div style="background: #ffffff; border-radius: 16px; width: 100%; max-width: 760px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden; animation: modalPop 0.2s ease-out; margin: auto;">
        
        <!-- Header Modal -->
        <div style="padding: 16px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div id="modalIconBox" style="width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; background: #e0f2fe; color: #0284c7;">
                    <i id="modalIcon" class="fa-solid fa-receipt"></i>
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <h2 id="modalOrderTitle" style="margin: 0; font-size: 1.2rem; font-weight: 800; color: #0f172a;">#ORD-0000</h2>
                        <span id="modalServiceBadge" class="badge" style="font-size: 0.75rem; padding: 2px 8px;">Layanan</span>
                        <span id="modalStatusBadge" class="badge" style="font-size: 0.75rem; padding: 2px 8px;">Status</span>
                    </div>
                    <span id="modalOrderDate" style="font-size: 0.78rem; color: #64748b; margin-top: 2px; display: inline-block;">-</span>
                </div>
            </div>
            <button type="button" onclick="closeOrderDetail()" style="background: none; border: none; font-size: 1.6rem; color: #94a3b8; cursor: pointer; padding: 4px 10px; border-radius: 8px; line-height: 1; transition: color 0.15s;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#94a3b8'">&times;</button>
        </div>

        <!-- Body Modal (Scrollable) -->
        <div style="padding: 20px 24px; overflow-y: auto; flex: 1;">
            
            <!-- Loading State -->
            <div id="modalLoading" style="text-align: center; padding: 40px 10px;">
                <i class="fa-solid fa-circle-notch fa-spin" style="font-size: 2.2rem; color: var(--primary);"></i>
                <p style="margin-top: 12px; color: #64748b; font-size: 0.9rem;">Memuat rincian pesanan...</p>
            </div>

            <!-- Content Container -->
            <div id="modalContent" style="display: none; flex-direction: column; gap: 16px;">
                
                <!-- 1. Alasan Batal (Jika cancelled) -->
                <div id="modalCancelBox" style="display: none; background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 10px 14px; border-radius: 10px; font-size: 0.85rem;">
                    <i class="fa-solid fa-circle-exclamation" style="margin-right: 6px;"></i>
                    <strong>Alasan Pembatalan:</strong> <span id="modalCancelReason">-</span>
                </div>

                <!-- 2. Profil Pelanggan & Pengemudi -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 12px;">
                    <!-- Pelanggan Card -->
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                            <span style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">
                                <i class="fa-solid fa-user" style="color: #3b82f6; margin-right: 4px;"></i> Pelanggan (Pemesan)
                            </span>
                            <span id="modalCustomerWaBtn"></span>
                        </div>
                        <div style="font-size: 0.95rem; font-weight: 800; color: #1e293b;" id="modalCustomerName">-</div>
                        <div style="font-size: 0.8rem; color: #64748b; margin-top: 2px;" id="modalCustomerPhone">-</div>
                    </div>

                    <!-- Pengemudi Card -->
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                            <span style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">
                                <i class="fa-solid fa-motorcycle" style="color: #10b981; margin-right: 4px;"></i> Kurir / Pengemudi
                            </span>
                            <span id="modalDriverWaBtn"></span>
                        </div>
                        <div style="font-size: 0.95rem; font-weight: 800; color: #1e293b;" id="modalDriverName">Belum Ada Kurir</div>
                        <div style="font-size: 0.8rem; color: #64748b; margin-top: 2px;" id="modalDriverPhone">-</div>
                        <div style="margin-top: 4px;" id="modalDriverPlateBox"></div>
                    </div>
                </div>

                <!-- 3. Rincian Toko / Restoran (Khusus Food & Mart) -->
                <div id="modalMerchantSection" style="display: none; background: #fff7ed; border: 1px solid #ffedd5; border-radius: 12px; padding: 12px 14px;">
                    <div style="font-size: 0.75rem; font-weight: 700; color: #ea580c; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                        <i class="fa-solid fa-store" style="margin-right: 4px;"></i> Toko / Resto Mitra
                    </div>
                    <div style="font-size: 1rem; font-weight: 800; color: #c2410c;" id="modalMerchantName">-</div>
                    <div style="font-size: 0.8rem; color: #7c2d12; margin-top: 2px;" id="modalMerchantAddress">-</div>
                    <div style="font-size: 0.8rem; color: #9a3412; margin-top: 2px;" id="modalMerchantPhone">-</div>
                </div>

                <!-- 4. Rute Lokasi & Google Maps -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fa-solid fa-route" style="color: #6366f1; margin-right: 4px;"></i> Alamat & Rute Pengantaran
                        </span>
                        <div id="modalMapsLinkContainer"></div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 10px; font-size: 0.85rem;">
                        <div id="modalPickupRow" style="display: flex; align-items: flex-start; gap: 8px;">
                            <div style="width: 20px; height: 20px; border-radius: 50%; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; flex-shrink: 0; margin-top: 1px;">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-size: 0.75rem; color: #16a34a; font-weight: 700;">TITIK PENJEMPUTAN:</div>
                                <div id="modalPickupAddress" style="color: #1e293b; line-height: 1.35;">-</div>
                            </div>
                        </div>

                        <div style="display: flex; align-items: flex-start; gap: 8px;">
                            <div style="width: 20px; height: 20px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; flex-shrink: 0; margin-top: 1px;">
                                <i class="fa-solid fa-flag-checkered"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-size: 0.75rem; color: #dc2626; font-weight: 700;">TITIK PENGANTARAN / TUJUAN:</div>
                                <div id="modalDropoffAddress" style="color: #1e293b; line-height: 1.35;">-</div>
                            </div>
                        </div>

                        <div id="modalDistanceRow" style="font-size: 0.78rem; color: #64748b; padding-left: 28px;">
                            Jarak Tempuh: <strong id="modalDistance" style="color: #1e293b;">0 KM</strong>
                        </div>
                    </div>

                    <!-- Informasi Paket / Titip -->
                    <div id="modalPackageSection" style="display: none; margin-top: 12px; padding-top: 10px; border-top: 1px dashed #e2e8f0; font-size: 0.82rem;">
                        <div style="color: #475569;">
                            <strong style="color: #1e293b;">Deskripsi Barang:</strong> <span id="modalItemDesc">-</span>
                        </div>
                        <div id="modalRecipientBox" style="margin-top: 4px; color: #475569;">
                            <strong style="color: #1e293b;">Penerima:</strong> <span id="modalRecipient">-</span>
                        </div>
                    </div>
                </div>

                <!-- 5. Daftar Menu Makanan (Khusus Food & Mart) -->
                <div id="modalItemsSection" style="display: none; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px;">
                    <div style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px;">
                        <i class="fa-solid fa-utensils" style="color: #ea580c; margin-right: 4px;"></i> Daftar Menu / Produk yang Dipesan
                    </div>
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                        <thead>
                            <tr style="border-bottom: 2px solid #f1f5f9; color: #64748b; text-align: left;">
                                <th style="padding: 6px 8px;">Menu / Produk</th>
                                <th style="padding: 6px 8px; text-align: center;">Qty</th>
                                <th style="padding: 6px 8px; text-align: right;">Harga Satuan</th>
                                <th style="padding: 6px 8px; text-align: right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="modalItemsTableBody">
                            <!-- Items injected via JS -->
                        </tbody>
                    </table>
                </div>

                <!-- 6. Rincian Pembayaran / Kwitansi -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px;">
                    <div style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                        <i class="fa-solid fa-file-invoice-dollar" style="color: #10b981; margin-right: 4px;"></i> Rincian Biaya & Pembayaran
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 6px; font-size: 0.85rem;">
                        <div id="modalRowBaseFare" style="display: flex; justify-content: space-between; color: #475569;">
                            <span id="modalLabelBaseFare">Tarif / Makanan</span>
                            <span id="modalValBaseFare">Rp 0</span>
                        </div>
                        <div id="modalRowDeliveryFee" style="display: none; justify-content: space-between; color: #475569;">
                            <span>Ongkos Kirim</span>
                            <span id="modalValDeliveryFee">Rp 0</span>
                        </div>
                        <div id="modalRowDiscount" style="display: none; justify-content: space-between; color: #16a34a;">
                            <span>Potongan Promo (<span id="modalPromoCode">PROMO</span>)</span>
                            <span id="modalValDiscount">-Rp 0</span>
                        </div>
                        <div style="border-top: 1px dashed #cbd5e1; margin-top: 4px; padding-top: 8px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-weight: 800; font-size: 1.05rem; color: #0f172a;">Total Pembayaran</div>
                                <div id="modalPaymentMethodBadge" style="margin-top: 2px;"></div>
                            </div>
                            <div style="font-size: 1.25rem; font-weight: 900; color: var(--primary);" id="modalTotalAmount">Rp 0</div>
                        </div>
                    </div>
                </div>

                <!-- 7. Foto Bukti Pengantaran / Struk Belanja -->
                <div id="modalProofSection" style="display: none; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px;">
                    <div style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                        <i class="fa-solid fa-camera" style="color: #8b5cf6; margin-right: 4px;"></i> Foto Bukti Pengantaran / Struk Belanja
                    </div>
                    <div id="modalProofImagesContainer" style="display: flex; gap: 12px; flex-wrap: wrap;"></div>
                </div>

                <!-- 8. Rating & Ulasan -->
                <div id="modalReviewSection" style="display: none; background: #fffbeb; border: 1px solid #fef3c7; border-radius: 12px; padding: 12px 14px; font-size: 0.85rem;">
                    <div style="font-size: 0.75rem; font-weight: 700; color: #d97706; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">
                        <i class="fa-solid fa-star" style="color: #f59e0b; margin-right: 4px;"></i> Ulasan & Penilaian Pelanggan
                    </div>
                    <div id="modalReviewStars" style="color: #f59e0b; margin-bottom: 4px;"></div>
                    <div id="modalReviewComment" style="color: #78350f; font-style: italic;"></div>
                </div>

            </div>
        </div>

        <!-- Footer Modal -->
        <div style="padding: 14px 24px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <div id="modalActionButtons" style="display: flex; gap: 8px;"></div>
            <button type="button" onclick="closeOrderDetail()" style="padding: 8px 20px; background: #e2e8f0; color: #334155; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: background 0.15s;" onmouseover="this.style.background='#cbd5e1'" onmouseout="this.style.background='#e2e8f0'">
                Tutup
            </button>
        </div>
    </div>
</div>

<style>
@keyframes modalPop {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
</style>

<script>
function formatRupiah(number) {
    return 'Rp ' + Number(number).toLocaleString('id-ID');
}

function openOrderDetail(type, id) {
    const modal = document.getElementById('orderDetailModal');
    const loading = document.getElementById('modalLoading');
    const content = document.getElementById('modalContent');
    
    if (!modal) return;
    modal.style.display = 'flex';
    loading.style.display = 'block';
    content.style.display = 'none';

    fetch(`<?= base_url('admin/orders/detail') ?>/${type}/${id}`)
        .then(res => res.json())
        .then(res => {
            if (res.status !== 'success') {
                alert(res.message || 'Gagal memuat rincian pesanan');
                closeOrderDetail();
                return;
            }
            populateOrderDetailModal(res.data, res.type);
            loading.style.display = 'none';
            content.style.display = 'flex';
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan saat memuat data dari server');
            closeOrderDetail();
        });
}

function closeOrderDetail() {
    const modal = document.getElementById('orderDetailModal');
    if (modal) modal.style.display = 'none';
}

function populateOrderDetailModal(d, type) {
    // 1. Header & ID
    document.getElementById('modalOrderTitle').innerText = d.display_id || ('#' + d.id);
    document.getElementById('modalOrderDate').innerText = d.created_at || '-';
    
    // Service badge & Icon
    const iconBox = document.getElementById('modalIconBox');
    const iconEl = document.getElementById('modalIcon');
    const srvBadge = document.getElementById('modalServiceBadge');
    
    srvBadge.innerText = d.service_label || 'Layanan';
    if (type === 'food') {
        if (d.service_label === 'MaiMart') {
            iconBox.style.background = '#dcfce7'; iconBox.style.color = '#16a34a';
            iconEl.className = 'fa-solid fa-store';
            srvBadge.style.background = '#f0fdf4'; srvBadge.style.color = '#16a34a'; srvBadge.style.border = '1px solid #dcfce7';
        } else {
            iconBox.style.background = '#ffedd5'; iconBox.style.color = '#ea580c';
            iconEl.className = 'fa-solid fa-utensils';
            srvBadge.style.background = '#fff7ed'; srvBadge.style.color = '#ea580c'; srvBadge.style.border = '1px solid #ffedd5';
        }
    } else {
        iconBox.style.background = '#e0f2fe'; iconBox.style.color = '#0284c7';
        iconEl.className = 'fa-solid fa-motorcycle';
        srvBadge.style.background = '#f1f5f9'; srvBadge.style.color = '#475569'; srvBadge.style.border = '1px solid #e2e8f0';
    }

    // Status badge
    const statusBadge = document.getElementById('modalStatusBadge');
    const st = d.status;
    if (st === 'completed') {
        statusBadge.className = 'badge badge-success'; statusBadge.innerText = 'Selesai';
    } else if (st === 'cancelled') {
        statusBadge.className = 'badge badge-danger'; statusBadge.innerText = 'Batal';
    } else if (['in_progress', 'accepted_by_driver', 'on_trip'].includes(st)) {
        statusBadge.className = 'badge'; statusBadge.style.background = '#e0f2fe'; statusBadge.style.color = '#0284c7'; statusBadge.innerText = 'Sedang Diantar';
    } else if (['processing', 'ready'].includes(st)) {
        statusBadge.className = 'badge badge-warning'; statusBadge.innerText = 'Dimasak / Siap';
    } else {
        statusBadge.className = 'badge'; statusBadge.style.background = '#fef3c7'; statusBadge.style.color = '#92400e'; statusBadge.innerText = 'Menunggu Driver';
    }

    // Cancel Box
    const cancelBox = document.getElementById('modalCancelBox');
    if (d.status === 'cancelled' && d.cancel_reason) {
        cancelBox.style.display = 'block';
        document.getElementById('modalCancelReason').innerText = d.cancel_reason;
    } else {
        cancelBox.style.display = 'none';
    }

    // Customer
    document.getElementById('modalCustomerName').innerText = d.customer_name || 'Pelanggan';
    document.getElementById('modalCustomerPhone').innerText = d.customer_phone || '-';
    const custWaBtn = document.getElementById('modalCustomerWaBtn');
    if (d.customer_phone && d.customer_phone !== '-') {
        let cleanPhone = d.customer_phone.replace(/[^0-9]/g, '');
        if (cleanPhone.startsWith('0')) cleanPhone = '62' + cleanPhone.substring(1);
        custWaBtn.innerHTML = `<a href="https://wa.me/${cleanPhone}" target="_blank" style="padding: 2px 8px; background: #25d366; color: white; border-radius: 6px; text-decoration: none; font-size: 0.72rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;"><i class="fa-brands fa-whatsapp"></i> Chat</a>`;
    } else {
        custWaBtn.innerHTML = '';
    }

    // Driver
    const driverNameEl = document.getElementById('modalDriverName');
    const driverPhoneEl = document.getElementById('modalDriverPhone');
    const driverPlateEl = document.getElementById('modalDriverPlateBox');
    const driverWaBtn = document.getElementById('modalDriverWaBtn');

    if (d.driver_name) {
        driverNameEl.innerText = d.driver_name;
        driverPhoneEl.innerText = d.driver_phone || '-';
        driverPlateEl.innerHTML = d.driver_plate ? `<span style="background: #e2e8f0; color: #1e293b; font-weight: 700; font-size: 0.75rem; padding: 2px 7px; border-radius: 4px;">${d.driver_plate}</span>` : '';
        if (d.driver_phone && d.driver_phone !== '-') {
            let cleanPhone = d.driver_phone.replace(/[^0-9]/g, '');
            if (cleanPhone.startsWith('0')) cleanPhone = '62' + cleanPhone.substring(1);
            driverWaBtn.innerHTML = `<a href="https://wa.me/${cleanPhone}" target="_blank" style="padding: 2px 8px; background: #25d366; color: white; border-radius: 6px; text-decoration: none; font-size: 0.72rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;"><i class="fa-brands fa-whatsapp"></i> Chat</a>`;
        } else {
            driverWaBtn.innerHTML = '';
        }
    } else {
        driverNameEl.innerHTML = '<span style="color: #d97706; font-style: italic;"><i class="fa-regular fa-clock"></i> Belum Ada Driver (Mencari...)</span>';
        driverPhoneEl.innerText = '-';
        driverPlateEl.innerHTML = '';
        driverWaBtn.innerHTML = '';
    }

    // Merchant (Food only)
    const merchantSec = document.getElementById('modalMerchantSection');
    if (type === 'food') {
        merchantSec.style.display = 'block';
        document.getElementById('modalMerchantName').innerText = d.merchant_name || 'Mitra Toko/Resto';
        document.getElementById('modalMerchantAddress').innerText = d.merchant_address || '-';
        document.getElementById('modalMerchantPhone').innerText = d.merchant_phone ? ('Telp/WA: ' + d.merchant_phone) : '';
    } else {
        merchantSec.style.display = 'none';
    }

    // Route Addresses
    const pickupRow = document.getElementById('modalPickupRow');
    const pickupAddrEl = document.getElementById('modalPickupAddress');
    const dropoffAddrEl = document.getElementById('modalDropoffAddress');
    const distEl = document.getElementById('modalDistance');
    const mapsLinkContainer = document.getElementById('modalMapsLinkContainer');

    distEl.innerText = (d.distance || 0) + ' KM';

    let originLat = null, originLng = null, destLat = null, destLng = null;

    if (type === 'food') {
        pickupRow.style.display = 'none'; // pickup address is the merchant
        dropoffAddrEl.innerText = d.delivery_address || '-';
        destLat = d.delivery_lat;
        destLng = d.delivery_lng;
        originLat = d.merchant_lat;
        originLng = d.merchant_lng;
    } else {
        pickupRow.style.display = 'flex';
        pickupAddrEl.innerText = d.pickup_address || '-';
        dropoffAddrEl.innerText = d.dropoff_address || '-';
        originLat = d.pickup_lat;
        originLng = d.pickup_lng;
        destLat = d.dropoff_lat;
        destLng = d.dropoff_lng;
    }

    if (destLat && destLng) {
        let mapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${destLat},${destLng}`;
        if (originLat && originLng) {
            mapsUrl += `&origin=${originLat},${originLng}`;
        }
        mapsLinkContainer.innerHTML = `<a href="${mapsUrl}" target="_blank" style="padding: 4px 10px; background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; border-radius: 6px; text-decoration: none; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;"><i class="fa-solid fa-map-location-dot"></i> Buka Google Maps</a>`;
    } else {
        mapsLinkContainer.innerHTML = '';
    }

    // Package section (Ride/Send/Titip)
    const pkgSec = document.getElementById('modalPackageSection');
    if (type !== 'food' && (d.item_description || d.recipient_name)) {
        pkgSec.style.display = 'block';
        document.getElementById('modalItemDesc').innerText = (d.item_description || '-') + (d.item_weight ? ` (${d.item_weight} Kg)` : '');
        if (d.recipient_name) {
            document.getElementById('modalRecipientBox').style.display = 'block';
            document.getElementById('modalRecipient').innerText = d.recipient_name + (d.recipient_phone ? ` (${d.recipient_phone})` : '');
        } else {
            document.getElementById('modalRecipientBox').style.display = 'none';
        }
    } else {
        pkgSec.style.display = 'none';
    }

    // Items Section (Food only)
    const itemsSec = document.getElementById('modalItemsSection');
    const itemsTbody = document.getElementById('modalItemsTableBody');
    if (type === 'food' && d.items && d.items.length > 0) {
        itemsSec.style.display = 'block';
        itemsTbody.innerHTML = '';
        d.items.forEach(it => {
            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid #f1f5f9';
            tr.innerHTML = `
                <td style="padding: 8px;">
                    <div style="font-weight: 700; color: #1e293b;">${it.menu_name}</div>
                    ${it.variant_details ? `<div style="font-size: 0.72rem; color: #64748b;">Varian: ${it.variant_details}</div>` : ''}
                    ${it.notes ? `<div style="font-size: 0.72rem; color: #ea580c; font-style: italic;">Catatan: "${it.notes}"</div>` : ''}
                </td>
                <td style="padding: 8px; text-align: center; font-weight: 700;">${it.quantity}x</td>
                <td style="padding: 8px; text-align: right; color: #64748b;">${formatRupiah(it.price)}</td>
                <td style="padding: 8px; text-align: right; font-weight: 700; color: #0f172a;">${formatRupiah(it.subtotal)}</td>
            `;
            itemsTbody.appendChild(tr);
        });
    } else {
        itemsSec.style.display = 'none';
    }

    // Billing Breakdown
    const rowBase = document.getElementById('modalRowBaseFare');
    const lblBase = document.getElementById('modalLabelBaseFare');
    const valBase = document.getElementById('modalValBaseFare');
    const rowDelivery = document.getElementById('modalRowDeliveryFee');
    const valDelivery = document.getElementById('modalValDeliveryFee');
    const rowDiscount = document.getElementById('modalRowDiscount');
    const valDiscount = document.getElementById('modalValDiscount');
    const promoCodeEl = document.getElementById('modalPromoCode');
    const totalAmountEl = document.getElementById('modalTotalAmount');
    const payBadge = document.getElementById('modalPaymentMethodBadge');

    if (type === 'food') {
        lblBase.innerText = 'Subtotal Pesanan Menu';
        valBase.innerText = formatRupiah(d.food_total || 0);
        
        rowDelivery.style.display = 'flex';
        valDelivery.innerText = formatRupiah(d.delivery_fee || 0);
    } else {
        lblBase.innerText = 'Tarif Jasa Perjalanan';
        valBase.innerText = formatRupiah(d.price || 0);
        rowDelivery.style.display = 'none';
    }

    if (d.discount_amount && d.discount_amount > 0) {
        rowDiscount.style.display = 'flex';
        valDiscount.innerText = '-' + formatRupiah(d.discount_amount);
        promoCodeEl.innerText = d.promo_code || 'DISKON';
    } else {
        rowDiscount.style.display = 'none';
    }

    totalAmountEl.innerText = formatRupiah(d.total_price || d.price || 0);

    const isMaipay = (d.payment_method || '').toLowerCase() === 'maipay';
    payBadge.innerHTML = isMaipay 
        ? '<span class="badge" style="background: #eff6ff; color: #1d4ed8; font-size: 0.72rem; padding: 2px 8px;">Dibayar dengan Saldo MaiPay</span>'
        : '<span class="badge" style="background: #f1f5f9; color: #475569; font-size: 0.72rem; padding: 2px 8px;">Pembayaran Uang Tunai (Cash)</span>';

    // Proof Section (Photos)
    const proofSec = document.getElementById('modalProofSection');
    const proofContainer = document.getElementById('modalProofImagesContainer');
    proofContainer.innerHTML = '';
    let hasPhotos = false;

    if (d.proof_of_delivery) {
        hasPhotos = true;
        proofContainer.innerHTML += `
            <div>
                <a href="${d.proof_of_delivery}" target="_blank">
                    <img src="${d.proof_of_delivery}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid #cbd5e1;" alt="Bukti Antar">
                </a>
                <div style="font-size: 0.72rem; color: #64748b; margin-top: 3px; text-align: center;">Bukti Pengantaran</div>
            </div>
        `;
    }
    if (d.proof_of_pickup) {
        hasPhotos = true;
        proofContainer.innerHTML += `
            <div>
                <a href="${d.proof_of_pickup}" target="_blank">
                    <img src="${d.proof_of_pickup}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid #cbd5e1;" alt="Bukti Jemput">
                </a>
                <div style="font-size: 0.72rem; color: #64748b; margin-top: 3px; text-align: center;">Bukti Penjemputan</div>
            </div>
        `;
    }
    if (d.titip_receipt_img) {
        hasPhotos = true;
        proofContainer.innerHTML += `
            <div>
                <a href="${d.titip_receipt_img}" target="_blank">
                    <img src="${d.titip_receipt_img}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid #cbd5e1;" alt="Struk Belanja">
                </a>
                <div style="font-size: 0.72rem; color: #64748b; margin-top: 3px; text-align: center;">Struk Belanja Titip</div>
            </div>
        `;
    }
    proofSec.style.display = hasPhotos ? 'block' : 'none';

    // Review & Rating
    const reviewSec = document.getElementById('modalReviewSection');
    const ratingVal = d.rating || d.driver_rating || d.merchant_rating;
    const commentVal = d.review_comment || d.review_driver || d.review_merchant;

    if (ratingVal) {
        reviewSec.style.display = 'block';
        let starsHtml = '';
        for (let i = 1; i <= 5; i++) {
            starsHtml += i <= ratingVal ? '<i class="fa-solid fa-star"></i> ' : '<i class="fa-regular fa-star"></i> ';
        }
        document.getElementById('modalReviewStars').innerHTML = starsHtml + ` <strong style="color: #78350f; font-size: 0.85rem; margin-left: 4px;">(${ratingVal}/5)</strong>`;
        document.getElementById('modalReviewComment').innerText = commentVal ? `"${commentVal}"` : 'Pelanggan tidak memberikan komentar teks.';
    } else {
        reviewSec.style.display = 'none';
    }

    // Tombol Aksi Modal (Selesaikan & Batalkan Pesanan oleh Admin)
    const modalActionBox = document.getElementById('modalActionButtons');
    if (modalActionBox) {
        modalActionBox.innerHTML = '';
        const orderStatus = (d.status || '').toLowerCase();
        const activeStatuses = ['accepted', 'arrived', 'in_progress', 'on_trip', 'driver_assigned', 'accepted_by_driver', 'driver_at_merchant', 'delivering', 'processing', 'ready'];

        if (activeStatuses.includes(orderStatus)) {
            const isFood = (type === 'food' || type === 'maifood');
            const completeUrl = isFood
                ? `<?= base_url('admin/orders/complete/maifood') ?>/${d.id}`
                : `<?= base_url('admin/orders/complete/mairide') ?>/${d.id}`;
            const cancelUrl = isFood
                ? `<?= base_url('admin/orders/cancel/maifood') ?>/${d.id}`
                : `<?= base_url('admin/orders/cancel/mairide') ?>/${d.id}`;
            const csrfToken = `<?= csrf_field() ?>`;

            modalActionBox.innerHTML = `
                <form action="${completeUrl}" method="post" onsubmit="return confirm('Selesaikan pesanan #${d.id} ini secara manual oleh Admin?');" style="margin: 0; display: inline-block;">
                    ${csrfToken}
                    <button type="submit" style="padding: 7px 14px; background: #059669; color: white; border: none; border-radius: 8px; font-weight: 600; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; box-shadow: 0 1px 2px rgba(5, 150, 105, 0.2);">
                        <i class="fa-solid fa-circle-check"></i> Selesaikan Pesanan
                    </button>
                </form>
                <form action="${cancelUrl}" method="post" onsubmit="return confirm('Batalkan pesanan #${d.id} ini secara paksa?');" style="margin: 0; display: inline-block;">
                    ${csrfToken}
                    <button type="submit" style="padding: 7px 14px; background: #ef4444; color: white; border: none; border-radius: 8px; font-weight: 600; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                        <i class="fa-solid fa-ban"></i> Batalkan
                    </button>
                </form>
            `;
        }
    }
}
</script>
