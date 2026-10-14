<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Struk Maijek</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #0056D2;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .header h1 {
            color: #0056D2;
            margin: 0;
            font-size: 32px;
        }

        .header p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 14px;
        }

        .row {
            width: 100%;
            margin-bottom: 15px;
        }

        .col-left {
            float: left;
            width: 50%;
        }

        .col-right {
            float: right;
            width: 50%;
            text-align: right;
        }

        .clear {
            clear: both;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #0056D2;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .detail-item {
            margin-bottom: 8px;
            font-size: 14px;
        }

        .detail-label {
            color: #777;
            font-size: 12px;
        }

        .price-box {
            background-color: #f8f9fa;
            border: 1px solid #eee;
            padding: 15px;
            text-align: center;
            border-radius: 8px;
            margin-top: 20px;
        }

        .price-box h2 {
            margin: 0;
            color: #28a745;
            font-size: 28px;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #888;
            border-top: 1px dashed #ccc;
            padding-top: 20px;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>Maijek</h1>
        <p>Struk Perjalanan Digital</p>
    </div>

    <div class="row">
        <div class="col-left">
            <div class="detail-label">ID PESANAN</div>
            <div class="detail-item" style="font-weight: bold;">#<?= $order['id'] ?></div>
        </div>
        <div class="col-right">
            <div class="detail-label">TANGGAL PERJALANAN</div>
            <div class="detail-item"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></div>
        </div>
        <div class="clear"></div>
    </div>

    <div class="section-title">Detail Penumpang & Supir</div>
    <div class="row">
        <div class="col-left">
            <div class="detail-label">PENUMPANG</div>
            <div class="detail-item"><?= esc($order['user_name']) ?></div>
        </div>
        <div class="col-right">
            <div class="detail-label">SUPIR</div>
            <div class="detail-item"><?= esc($order['driver_name'] ?? '-') ?> <br> (<?= esc($order['vehicle_plate'] ?? '-') ?>)</div>
        </div>
        <div class="clear"></div>
    </div>

    <div class="section-title">Rute Perjalanan</div>
    <div class="detail-label">LOKASI JEMPUT</div>
    <div class="detail-item"><?= esc($order['pickup_address']) ?></div>

    <div class="detail-label" style="margin-top:10px;">LOKASI TUJUAN</div>
    <div class="detail-item"><?= esc($order['dropoff_address']) ?></div>

    <div class="detail-label" style="margin-top:10px;">JARAK</div>
    <div class="detail-item"><?= esc($order['distance']) ?> KM (Layanan: <?= strtoupper(esc($order['service_type'])) ?>)</div>

    <div class="price-box">
        <div class="detail-label" style="margin-bottom: 5px;">TOTAL PEMBAYARAN</div>
        <h2>Rp <?= number_format($order['price'], 0, ',', '.') ?></h2>
        <div class="detail-item" style="margin-top: 10px; color: #666;">
            Metode Pembayaran: <strong><?= strtoupper(esc($order['payment_method'])) ?></strong>
        </div>
    </div>

    <div class="footer">
        Terima kasih telah menggunakan layanan Maijek.<br>
        Struk ini adalah bukti pembayaran yang sah yang dicetak secara otomatis oleh sistem.
    </div>

</body>

</html>