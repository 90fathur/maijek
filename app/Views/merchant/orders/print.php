<?php
$isMart = !empty($isMart) ? $isMart : ((session()->get('merchant_type') === 'mart') || (($merchant['merchant_type'] ?? '') === 'mart'));
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pesanan #<?= $order['id'] ?> - <?= esc($merchant['name']) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Courier New', Courier, monospace;
        }

        @page {
            size: 80mm auto;
            margin: 0;
        }

        body {
            font-size: 12px;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 10px;
        }

        .receipt-ticket {
            width: 100%;
            max-width: 78mm;
            margin: 0 auto;
        }

        .text-center {
            text-align: center;
        }

        .store-title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            margin-bottom: 3px;
        }

        .item-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin: 6px 0;
        }

        .item-table td {
            vertical-align: top;
            padding: 3px 0;
        }

        .item-note {
            font-size: 11px;
            font-style: italic;
            padding-left: 14px;
        }

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .footer-text {
            text-align: center;
            font-size: 10px;
            margin-top: 10px;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                padding: 0;
            }
        }
    </style>
</head>

<body>

    <div class="receipt-ticket">
        <div class="no-print" style="margin-bottom: 12px; text-align: center;">
            <button onclick="window.print()" style="padding: 8px 16px; background: #ea580c; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">
                🖨️ Cetak Struk Sekarang
            </button>
        </div>

        <div class="text-center">
            <div class="store-title"><?= esc($merchant['name']) ?></div>
            <div><?= esc($merchant['address'] ?? ($isMart ? 'MaiMart Merchant' : 'MaiFood Merchant')) ?></div>
            <div>Telp: <?= esc($merchant['phone'] ?? '-') ?></div>
        </div>

        <div class="divider"></div>

        <div class="meta-row">
            <span>NO. ORDER:</span>
            <span class="bold">#<?= $order['id'] ?> (<?= $isMart ? 'MAIMART' : 'MAIFOOD' ?>)</span>
        </div>
        <div class="meta-row">
            <span>TANGGAL:</span>
            <span><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
        </div>
        <div class="meta-row">
            <span>PEMESAN:</span>
            <span class="bold"><?= esc($order['user_name'] ?? 'Pelanggan') ?></span>
        </div>
        <div class="meta-row">
            <span>ARMADA:</span>
            <span class="bold"><?= ($order['vehicle_type'] == 'mobil') ? 'MOBIL 🚗' : 'MOTOR 🛵' ?></span>
        </div>
        <?php if (!empty($order['driver_name'])): ?>
            <div class="meta-row">
                <span>DRIVER:</span>
                <span><?= esc($order['driver_name']) ?> (<?= esc($order['driver_plate'] ?? '-') ?>)</span>
            </div>
        <?php endif; ?>

        <div class="divider"></div>

        <table class="item-table">
            <?php foreach ($items as $item): ?>
                <tr>
                    <td style="width: 20px;" class="bold"><?= $item['quantity'] ?>x</td>
                    <td><?= esc($item['menu_name'] ?? ($isMart ? 'Produk' : 'Menu')) ?></td>
                    <td class="text-right">Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></td>
                </tr>
                <?php if (!empty($item['notes'])): ?>
                    <tr>
                        <td colspan="3" class="item-note">* <?= esc($item['notes']) ?></td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </table>

        <div class="divider"></div>

        <?php
        $foodTotal = !empty($order['food_total']) ? $order['food_total'] : ($order['total_price'] - ($order['delivery_fee'] ?? 0));
        $deliveryFee = $order['delivery_fee'] ?? 0;
        $totalPrice = $order['total_price'];
        $isCash = ($order['payment_method'] == 'cash');
        ?>

        <div class="meta-row">
            <span>Subtotal Belanja <?= $isMart ? 'Barang/Produk' : 'Makanan' ?>:</span>
            <span class="bold">Rp <?= number_format($foodTotal, 0, ',', '.') ?></span>
        </div>
        <div class="meta-row">
            <span>Ongkir Pengantaran:</span>
            <span>Rp <?= number_format($deliveryFee, 0, ',', '.') ?></span>
        </div>
        <div class="meta-row">
            <span>Total Tagihan Pelanggan:</span>
            <span>Rp <?= number_format($totalPrice, 0, ',', '.') ?></span>
        </div>

        <div class="divider"></div>

        <div class="meta-row" style="font-size: 13px;">
            <span class="bold">TAGIHAN KASIR:</span>
            <span class="bold" style="font-size: 14px;">
                <?= $isCash ? 'Rp ' . number_format($foodTotal, 0, ',', '.') : 'Rp 0 (LUNAS)' ?>
            </span>
        </div>
        <div class="meta-row" style="font-size: 11px; margin-top: 2px;">
            <span>Metode Pembayaran:</span>
            <span class="bold"><?= $isCash ? 'TUNAI (Ditalangi Kurir)' : 'MAI-PAY (NON-TUNAI)' ?></span>
        </div>

        <?php if ($isCash): ?>
            <div style="font-size: 10px; margin-top: 6px; padding: 4px; border: 1px solid #000; text-align: center; font-weight: bold;">
                * PERHATIAN KASIR: Kasir HANYA menerima Rp <?= number_format($foodTotal, 0, ',', '.') ?> dari kurir untuk belanja <?= $isMart ? 'barang/produk' : 'makanan' ?>. Jangan minta ongkir ke kurir!
            </div>
        <?php else: ?>
            <div style="font-size: 10px; margin-top: 6px; padding: 4px; border: 1px solid #000; text-align: center; font-weight: bold;">
                * LUNAS NON-TUNAI: Kasir TIDAK MENAGIH UANG APAPUN ke kurir.
            </div>
        <?php endif; ?>

        <div class="divider"></div>

        <div class="footer-text">
            Terima Kasih Atas Pesanan Anda!<br>
            -- <?= $isMart ? 'MaiMart' : 'MaiFood' ?> Order Ticket --
        </div>
    </div>

    <script>
        // Otomatis muncul dialog print saat halaman terbuka
        window.addEventListener('load', function() {
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>

</html>