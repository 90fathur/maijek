<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Maijek | Ojek Online, Mobil & Pesan Makanan Polewali Mandar') ?></title>
    <meta name="description" content="Maijek adalah aplikasi transportasi online, pesan antar makanan (Mai-Food), dan pengiriman barang kebanggaan Polewali Mandar, Sulawesi Barat. Cepat, hemat, dan aman.">
    <meta name="keywords" content="Maijek, Ojek Online Polman, Ojek Polewali Mandar, Pesan Makanan Polman, Mai-Food, Mai-Ride, Mai-Car, Kurir Polman">
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --primary-navy: #0e1b38;
            --navy-dark: #080f20;
            --navy-light: #162b56;
            --accent-gold: #fdb813;
            --gold-dark: #e5a000;
            --gold-light: #fff8e7;
            --primary-green: #10b981;
            --green-dark: #059669;
            --green-light: #dcfce7;
            --bg-light: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --radius-lg: 20px;
            --radius-md: 14px;
            --radius-sm: 8px;
            --shadow-sm: 0 2px 4px rgba(15, 23, 42, 0.05);
            --shadow-md: 0 10px 25px -5px rgba(14, 27, 56, 0.08), 0 8px 10px -6px rgba(14, 27, 56, 0.04);
            --shadow-lg: 0 20px 35px -5px rgba(14, 27, 56, 0.12), 0 10px 15px -5px rgba(14, 27, 56, 0.06);
            --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-light);
            color: var(--text-main);
            line-height: 1.6;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, .brand-font {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
        }

        /* Container */
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* Glassmorphism Navbar */
        .header-nav {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            transition: var(--transition);
        }

        .nav-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 76px;
        }

        .logo-group {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .logo-symbol {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, var(--accent-gold) 0%, var(--gold-dark) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--navy-dark);
            font-family: 'Outfit', sans-serif;
            font-weight: 900;
            font-size: 1.5rem;
            box-shadow: 0 4px 12px rgba(253, 184, 19, 0.35);
        }

        .logo-text {
            display: flex;
            flex-direction: column;
        }

        .logo-title {
            font-size: 1.45rem;
            font-weight: 800;
            color: var(--primary-navy);
            letter-spacing: -0.5px;
            line-height: 1.1;
        }

        .logo-title span {
            color: var(--accent-gold);
        }

        .logo-tagline {
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--text-muted);
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 26px;
            list-style: none;
        }

        .nav-link {
            text-decoration: none;
            color: var(--text-main);
            font-size: 0.9rem;
            font-weight: 600;
            transition: var(--transition);
            position: relative;
            padding: 6px 0;
        }

        .nav-link:hover, .nav-link.active-link {
            color: var(--primary-green);
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0%;
            height: 2px;
            background: var(--primary-green);
            transition: var(--transition);
            border-radius: 2px;
        }

        .nav-link:hover::after, .nav-link.active-link::after {
            width: 100%;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-portal {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: var(--radius-sm);
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            background: #f1f5f9;
            color: var(--text-main);
            border: 1px solid var(--border-color);
            transition: var(--transition);
        }

        .btn-portal:hover {
            background: #e2e8f0;
            color: var(--navy-dark);
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
            font-weight: 700;
            text-decoration: none;
            background: var(--primary-navy);
            color: white;
            box-shadow: 0 4px 14px rgba(14, 27, 56, 0.2);
            transition: var(--transition);
        }

        .btn-primary:hover {
            background: var(--navy-light);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(14, 27, 56, 0.25);
        }

        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--text-main);
            cursor: pointer;
        }

        /* Hero Section */
        .hero {
            position: relative;
            padding: 70px 0 90px;
            background: radial-gradient(circle at 80% 20%, rgba(253, 184, 19, 0.08) 0%, transparent 40%),
                        radial-gradient(circle at 10% 80%, rgba(16, 185, 129, 0.08) 0%, transparent 40%);
            border-bottom: 1px solid var(--border-color);
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            align-items: center;
            gap: 50px;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            background: var(--gold-light);
            border: 1px solid rgba(253, 184, 19, 0.3);
            border-radius: 30px;
            color: var(--gold-dark);
            font-size: 0.82rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 20px;
        }

        .hero h1 {
            font-size: 3.2rem;
            line-height: 1.15;
            color: var(--primary-navy);
            margin-bottom: 20px;
            letter-spacing: -1px;
        }

        .hero h1 span.highlight {
            color: var(--primary-green);
            position: relative;
            display: inline-block;
        }

        .hero p.lead {
            font-size: 1.1rem;
            color: var(--text-muted);
            margin-bottom: 35px;
            max-width: 580px;
            line-height: 1.65;
        }

        /* Store Badges Row */
        .store-buttons-container {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-bottom: 40px;
        }

        .store-badges-row {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .btn-store-badge {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: #000000;
            color: #ffffff;
            padding: 10px 22px;
            border-radius: 12px;
            text-decoration: none;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .btn-store-badge:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.35);
            background: #111111;
            border-color: var(--accent-gold);
        }

        .btn-store-badge .store-icon {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-store-badge .store-text {
            display: flex;
            flex-direction: column;
            text-align: left;
            line-height: 1.1;
        }

        .btn-store-badge .store-sub {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #cbd5e1;
            font-weight: 500;
        }

        .btn-store-badge .store-name {
            font-size: 1.15rem;
            font-weight: 700;
            font-family: 'Outfit', sans-serif;
            color: #ffffff;
        }

        .apk-quick-row {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .apk-quick-label {
            font-weight: 600;
            margin-right: 4px;
        }

        .apk-chip {
            padding: 5px 12px;
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 20px;
            color: var(--primary-navy);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.78rem;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .apk-chip:hover {
            background: var(--navy-light);
            color: white;
            border-color: var(--navy-light);
            transform: translateY(-1px);
        }

        .hero-stats-bar {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            padding-top: 30px;
            border-top: 1px solid var(--border-color);
        }

        .stat-item h3 {
            font-size: 1.75rem;
            color: var(--primary-navy);
            line-height: 1;
            margin-bottom: 4px;
        }

        .stat-item p {
            font-size: 0.8rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        /* Hero Right Preview Card */
        .hero-preview-card {
            background: linear-gradient(145deg, #0e1b38 0%, #162b56 100%);
            border-radius: var(--radius-lg);
            padding: 34px 28px;
            color: white;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(253, 184, 19, 0.2);
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .pulse-circle {
            width: 10px;
            height: 10px;
            background: #10b981;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulse-green 1.8s infinite;
        }

        @keyframes pulse-green {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        /* Section General */
        .section-padding {
            padding: 90px 0;
        }

        .section-header {
            text-align: center;
            max-width: 650px;
            margin: 0 auto 50px auto;
        }

        .section-tag {
            display: inline-block;
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--primary-green);
            background: var(--green-light);
            padding: 4px 14px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }

        .section-title {
            font-size: 2.3rem;
            color: var(--primary-navy);
            margin-bottom: 14px;
            letter-spacing: -0.5px;
        }

        .section-subtitle {
            font-size: 1rem;
            color: var(--text-muted);
            line-height: 1.6;
        }

        /* Merchant Filter & Search */
        .merchant-section {
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
        }

        .filter-tabs-wrapper {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .filter-tab {
            padding: 8px 18px;
            border-radius: 30px;
            border: 1px solid var(--border-color);
            background: #f8fafc;
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .filter-tab:hover, .filter-tab.active {
            background: var(--primary-navy);
            color: #ffffff;
            border-color: var(--primary-navy);
        }

        /* Search input box */
        .merchant-search-wrapper {
            max-width: 560px;
            margin: 0 auto 36px auto;
            position: relative;
        }

        .merchant-search-input {
            width: 100%;
            padding: 14px 20px 14px 48px;
            border-radius: 30px;
            border: 1.5px solid #cbd5e1;
            outline: none;
            font-size: 0.95rem;
            color: #1e293b;
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
            transition: var(--transition);
        }

        .merchant-search-input:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
        }

        .merchant-search-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1rem;
        }

        .merchants-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 28px;
        }

        .merchant-card {
            background: var(--card-bg);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            position: relative;
            cursor: pointer;
        }

        .merchant-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-lg);
            border-color: #cbd5e1;
        }

        .merchant-card-cover {
            height: 170px;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            position: relative;
            background-size: cover;
            background-position: center;
        }

        .merchant-card-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(15, 23, 42, 0.8) 0%, rgba(15, 23, 42, 0.1) 100%);
        }

        .merchant-badge-status {
            position: absolute;
            top: 14px;
            right: 14px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            z-index: 2;
            backdrop-filter: blur(8px);
        }

        .merchant-badge-status.open {
            background: rgba(220, 252, 231, 0.95);
            color: #166534;
            border: 1px solid #86efac;
        }

        .merchant-badge-status.closed {
            background: rgba(241, 245, 249, 0.95);
            color: #64748b;
            border: 1px solid #cbd5e1;
        }

        .merchant-card-body {
            padding: 22px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .merchant-cat-pill {
            display: inline-block;
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--gold-dark);
            background: var(--gold-light);
            padding: 3px 10px;
            border-radius: 6px;
            margin-bottom: 10px;
            align-self: flex-start;
        }

        .merchant-name {
            font-size: 1.35rem;
            color: var(--primary-navy);
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .merchant-address {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 16px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            line-height: 1.4;
        }

        .merchant-address i {
            color: #ef4444;
            margin-top: 3px;
        }

        .merchant-footer-info {
            margin-top: auto;
            padding-top: 14px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .merchant-hours {
            font-size: 0.78rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-order-merchant {
            padding: 7px 15px;
            font-size: 0.8rem;
            font-weight: 700;
            border-radius: 8px;
            background: var(--primary-navy);
            color: white;
            text-decoration: none;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-order-merchant:hover {
            background: var(--accent-gold);
            color: var(--navy-dark);
        }

        /* Merchant Join CTA Box */
        .merchant-cta-box {
            margin-top: 60px;
            background: linear-gradient(135deg, #0e1b38 0%, #1e3a8a 100%);
            border-radius: var(--radius-lg);
            padding: 45px 40px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 30px;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(253, 184, 19, 0.2);
            flex-wrap: wrap;
        }

        .merchant-cta-content h3 {
            font-size: 1.8rem;
            margin-bottom: 8px;
            color: white;
        }

        .merchant-cta-content p {
            color: #cbd5e1;
            font-size: 0.95rem;
            max-width: 620px;
        }

        .btn-join-merchant {
            padding: 14px 28px;
            background: #25d366;
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 6px 20px rgba(37, 211, 102, 0.35);
            transition: var(--transition);
            white-space: nowrap;
        }

        .btn-join-merchant:hover {
            background: #20ba5a;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(37, 211, 102, 0.45);
        }

        /* Services Grid */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 28px;
        }

        .service-card {
            background: white;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            padding: 34px 28px;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
        }

        .service-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-md);
            border-color: var(--accent-gold);
        }

        .service-icon-wrap {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 20px;
        }

        .service-icon-wrap.ride { background: #e0e7ff; color: #3730a3; }
        .service-icon-wrap.car { background: #dbeafe; color: #1d4ed8; }
        .service-icon-wrap.food { background: #ffedd5; color: #c2410c; }
        .service-icon-wrap.send { background: #dcfce7; color: #15803d; }
        .service-icon-wrap.titip { background: #fef3c7; color: #b45309; }
        .service-icon-wrap.mart { background: #f3e8ff; color: #7e22ce; }

        .service-card h3 {
            font-size: 1.35rem;
            color: var(--primary-navy);
            margin-bottom: 8px;
        }

        .service-fare-badge {
            display: inline-block;
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--primary-green);
            background: var(--green-light);
            padding: 3px 10px;
            border-radius: 20px;
            margin-bottom: 12px;
            align-self: flex-start;
        }

        .service-card p {
            color: var(--text-muted);
            font-size: 0.92rem;
            line-height: 1.6;
        }

        /* ================= FARE ESTIMATOR SECTION ================= */
        .estimator-section {
            background: linear-gradient(135deg, #0e1b38 0%, #162b56 100%);
            color: white;
            position: relative;
            overflow: hidden;
            border-top: 1px solid rgba(255,255,255,0.08);
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .estimator-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: var(--radius-lg);
            padding: 40px 36px;
            max-width: 860px;
            margin: 0 auto;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
        }

        .estimator-tabs {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 28px;
            justify-content: center;
        }

        .estimator-tab {
            padding: 10px 18px;
            background: rgba(255, 255, 255, 0.08);
            color: #cbd5e1;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            font-size: 0.88rem;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .estimator-tab.active, .estimator-tab:hover {
            background: var(--accent-gold);
            color: var(--navy-dark);
            border-color: var(--accent-gold);
            transform: translateY(-2px);
        }

        .slider-container {
            margin: 28px 0;
            background: rgba(0, 0, 0, 0.2);
            padding: 24px;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.06);
        }

        .slider-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .slider-val-badge {
            background: var(--primary-green);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 800;
            font-size: 1rem;
        }

        input[type=range].fare-slider {
            width: 100%;
            height: 8px;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.2);
            outline: none;
            -webkit-appearance: none;
        }

        input[type=range].fare-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--accent-gold);
            cursor: pointer;
            box-shadow: 0 0 10px rgba(253, 184, 19, 0.6);
            transition: transform 0.1s;
        }

        input[type=range].fare-slider::-webkit-slider-thumb:hover {
            transform: scale(1.2);
        }

        .preset-routes {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 14px;
        }

        .btn-preset {
            padding: 4px 10px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 6px;
            color: #cbd5e1;
            font-size: 0.76rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-preset:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .result-fare-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(14, 165, 233, 0.15) 100%);
            border: 1px solid rgba(16, 185, 129, 0.4);
            border-radius: 14px;
            padding: 22px 28px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .fare-amount-text {
            font-size: 2.2rem;
            font-weight: 800;
            color: #6ee7b7;
            font-family: 'Outfit', sans-serif;
            line-height: 1;
        }

        /* ================= TESTIMONIALS SECTION ================= */
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
        }

        .testimonial-card {
            background: white;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            padding: 28px 24px;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .testimonial-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
            border-color: #cbd5e1;
        }

        .stars-row {
            color: #fbbf24;
            font-size: 0.95rem;
            margin-bottom: 12px;
        }

        .testimonial-text {
            font-size: 0.92rem;
            color: #334155;
            line-height: 1.6;
            margin-bottom: 20px;
            font-style: italic;
            flex-grow: 1;
        }

        .testi-user-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            border-top: 1px solid #f1f5f9;
            padding-top: 14px;
        }

        .testi-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-navy);
            font-weight: 700;
            font-size: 1.1rem;
        }

        .testi-name {
            font-weight: 700;
            color: var(--primary-navy);
            font-size: 0.92rem;
        }

        .testi-role {
            font-size: 0.75rem;
            color: #64748b;
        }

        /* ================= FAQ ACCORDION ================= */
        .faq-item {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            margin-bottom: 14px;
            overflow: hidden;
            transition: var(--transition);
        }

        .faq-header {
            padding: 18px 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            font-weight: 700;
            color: var(--primary-navy);
            user-select: none;
            transition: background 0.2s;
        }

        .faq-header:hover {
            background: #f8fafc;
        }

        .faq-header i {
            transition: transform 0.25s ease;
            color: var(--accent-gold);
        }

        .faq-item.active .faq-header i {
            transform: rotate(180deg);
        }

        .faq-body {
            padding: 0 22px 20px 22px;
            font-size: 0.92rem;
            color: var(--text-muted);
            line-height: 1.6;
            display: none;
            border-top: 1px solid #f1f5f9;
            padding-top: 14px;
        }

        /* Workflow */
        .workflow-box {
            background: white;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            padding: 50px 40px;
            box-shadow: var(--shadow-sm);
        }

        .workflow-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            position: relative;
        }

        .workflow-step {
            text-align: center;
            position: relative;
        }

        .step-num-bubble {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: var(--gold-light);
            color: var(--gold-dark);
            border: 2px solid var(--accent-gold);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            font-weight: 800;
            margin: 0 auto 18px auto;
            box-shadow: 0 4px 10px rgba(253, 184, 19, 0.25);
        }

        .workflow-step h4 {
            font-size: 1.15rem;
            color: var(--primary-navy);
            margin-bottom: 8px;
        }

        .workflow-step p {
            font-size: 0.85rem;
            color: var(--text-muted);
            line-height: 1.5;
        }

        /* Download Center */
        .download-center {
            background: linear-gradient(135deg, #0e1b38 0%, #080f20 100%);
            border-radius: var(--radius-lg);
            padding: 70px 40px;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(253, 184, 19, 0.2);
        }

        .download-center::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(253, 184, 19, 0.15) 0%, transparent 70%);
            border-radius: 50%;
        }

        .dl-cards-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            position: relative;
            z-index: 2;
        }

        .dl-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: var(--radius-md);
            padding: 30px 24px;
            text-align: center;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
        }

        .dl-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.09);
            border-color: var(--accent-gold);
        }

        .dl-card-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            background: rgba(253, 184, 19, 0.15);
            color: var(--accent-gold);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin: 0 auto 18px auto;
        }

        .dl-card h4 {
            font-size: 1.25rem;
            margin-bottom: 8px;
            color: white;
        }

        .dl-card p {
            font-size: 0.85rem;
            color: #cbd5e1;
            margin-bottom: 24px;
            line-height: 1.5;
            flex-grow: 1;
        }

        .btn-card-apk {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 12px;
            background: var(--accent-gold);
            color: var(--navy-dark);
            text-decoration: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.9rem;
            transition: var(--transition);
            box-shadow: 0 4px 14px rgba(253, 184, 19, 0.3);
        }

        .btn-card-apk:hover {
            background: #ffffff;
            color: var(--navy-dark);
            transform: translateY(-2px);
        }

        /* Footer */
        footer {
            background: #080f20;
            color: white;
            padding: 80px 0 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr 1.2fr;
            gap: 40px;
            margin-bottom: 60px;
        }

        .footer-col h4 {
            color: white;
            font-size: 1.15rem;
            margin-bottom: 20px;
            position: relative;
        }

        .footer-col h4::after {
            content: '';
            position: absolute;
            bottom: -6px;
            left: 0;
            width: 30px;
            height: 2px;
            background: var(--accent-gold);
            border-radius: 2px;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.9rem;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .footer-links a:hover {
            color: var(--accent-gold);
            transform: translateX(3px);
        }

        .contact-info-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 16px;
            font-size: 0.9rem;
            color: #94a3b8;
        }

        .contact-info-item i {
            color: var(--accent-gold);
            margin-top: 4px;
        }

        .bottom-bar {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            font-size: 0.85rem;
            color: #64748b;
        }

        /* Floating WhatsApp Button */
        .floating-wa-btn {
            position: fixed;
            bottom: 26px;
            right: 26px;
            z-index: 9999;
            background: #25d366;
            color: white;
            width: 58px;
            height: 58px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.85rem;
            box-shadow: 0 10px 25px rgba(37, 211, 102, 0.45);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .floating-wa-btn:hover {
            transform: scale(1.1) rotate(5deg);
            background: #20ba5a;
            color: white;
        }

        .floating-wa-btn::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 2px solid #25d366;
            animation: waPulse 1.8s infinite;
        }

        @keyframes waPulse {
            0% { transform: scale(1); opacity: 0.8; }
            100% { transform: scale(1.6); opacity: 0; }
        }

        /* Modal Resto Quick View */
        .modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(8px);
            z-index: 10000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-content-box {
            background: white;
            border-radius: var(--radius-lg);
            max-width: 520px;
            width: 100%;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            animation: modalFadeIn 0.25s ease-out;
            position: relative;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        /* Responsive Breakpoints */
        @media (max-width: 992px) {
            .hero-grid {
                grid-template-columns: 1fr;
                text-align: center;
            }
            .hero p.lead {
                margin: 0 auto 30px auto;
            }
            .download-cta-group {
                justify-content: center;
            }
            .hero-stats-bar {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }
            .workflow-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .dl-cards-grid {
                grid-template-columns: 1fr;
            }
            .footer-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .nav-links, .nav-actions {
                display: none;
            }
            .mobile-toggle {
                display: block;
            }
            .hero h1 {
                font-size: 2.3rem;
            }
            .section-title {
                font-size: 1.8rem;
            }
            .merchants-grid {
                grid-template-columns: 1fr;
            }
            .workflow-grid {
                grid-template-columns: 1fr;
            }
            .footer-grid {
                grid-template-columns: 1fr;
            }
            .merchant-cta-box {
                flex-direction: column;
                text-align: center;
            }
            .result-fare-box {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>

<body>

    <!-- Header Navigation -->
    <header class="header-nav">
        <div class="container">
            <div class="nav-wrapper">
                <a href="<?= base_url() ?>" class="logo-group">
                    <div class="logo-symbol">M</div>
                    <div class="logo-text">
                        <div class="logo-title">Mai<span>jek</span></div>
                        <div class="logo-tagline">Polewali Mandar</div>
                    </div>
                </a>

                <ul class="nav-links">
                    <li><a href="#beranda" class="nav-link active-link">Beranda</a></li>
                    <li><a href="#mitra" class="nav-link">Mitra Resto</a></li>
                    <li><a href="#layanan" class="nav-link">Layanan</a></li>
                    <li><a href="#estimasi" class="nav-link">Simulasi Tarif</a></li>
                    <li><a href="#testimoni" class="nav-link">Testimoni</a></li>
                    <li><a href="#faq" class="nav-link">FAQ</a></li>
                    <li><a href="#unduh" class="nav-link">Unduh APK</a></li>
                </ul>

                <div class="nav-actions">
                    <a href="<?= base_url('admin/login') ?>" class="btn-portal" title="Portal Admin / Pengelola">
                        <i class="fa-solid fa-lock"></i> Admin
                    </a>
                    <a href="#unduh" class="btn-primary">
                        <i class="fa-solid fa-download"></i> Unduh Aplikasi
                    </a>
                </div>

                <button type="button" class="mobile-toggle" onclick="toggleMobileNav()" aria-label="Menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Nav Menu Panel -->
        <div id="mobileMenuPanel" style="display: none; background: white; border-bottom: 1px solid #e2e8f0; padding: 20px 24px;">
            <ul style="list-style: none; display: flex; flex-direction: column; gap: 16px; margin-bottom: 20px;">
                <li><a href="#beranda" onclick="toggleMobileNav()" style="text-decoration: none; color: var(--text-main); font-weight: 600;">Beranda</a></li>
                <li><a href="#mitra" onclick="toggleMobileNav()" style="text-decoration: none; color: var(--text-main); font-weight: 600;">Mitra Resto</a></li>
                <li><a href="#layanan" onclick="toggleMobileNav()" style="text-decoration: none; color: var(--text-main); font-weight: 600;">Layanan</a></li>
                <li><a href="#estimasi" onclick="toggleMobileNav()" style="text-decoration: none; color: var(--text-main); font-weight: 600;">Simulasi Tarif</a></li>
                <li><a href="#testimoni" onclick="toggleMobileNav()" style="text-decoration: none; color: var(--text-main); font-weight: 600;">Testimoni</a></li>
                <li><a href="#faq" onclick="toggleMobileNav()" style="text-decoration: none; color: var(--text-main); font-weight: 600;">FAQ</a></li>
                <li><a href="#unduh" onclick="toggleMobileNav()" style="text-decoration: none; color: var(--text-main); font-weight: 600;">Unduh APK</a></li>
            </ul>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <a href="#unduh" onclick="toggleMobileNav()" class="btn-primary" style="justify-content: center;">
                    <i class="fa-solid fa-download"></i> Unduh Aplikasi Maijek
                </a>
                <a href="<?= base_url('admin/login') ?>" class="btn-portal" style="justify-content: center;">
                    <i class="fa-solid fa-lock"></i> Masuk Portal Admin
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="beranda" class="hero">
        <div class="container">
            <div class="hero-grid">
                <div>
                    <div class="hero-badge">
                        <i class="fa-solid fa-star"></i> Pelopor Ojek Online & Kurir Polewali Mandar
                    </div>
                    <h1>
                        Perjalanan Cepat, Kuliner Lezat, <span class="highlight">Bangga Buatan Daerah!</span>
                    </h1>
                    <p class="lead">
                        Maijek hadir melayani masyarakat Kabupaten Polewali Mandar. Pesan ojek motor lincah, mobil sejuk ber-AC, kirim barang kilat, hingga jelajahi aneka kuliner lezat dari puluhan mitra resto lokal terpercaya.
                    </p>

                    <div class="store-buttons-container">
                        <div class="store-badges-row">
                            <!-- Google Play Button -->
                            <a href="#unduh" class="btn-store-badge playstore">
                                <div class="store-icon">
                                    <svg width="26" height="26" viewBox="0 0 512 512" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M47.2 18.3C40.6 22 36.5 28.9 36.5 37.4V474.6C36.5 483.1 40.6 490 47.2 493.7L265.4 256 47.2 18.3Z" fill="#00D3FF"/>
                                        <path d="M337.8 183.6L265.4 256L337.8 328.4L422.5 279.5C437.9 270.6 446.5 255.4 446.5 256C446.5 256.6 437.9 241.4 422.5 232.5L337.8 183.6Z" fill="#FFC900"/>
                                        <path d="M47.2 493.7C52.7 496.8 59.5 496.6 66.8 492.4L337.8 328.4L265.4 256L47.2 493.7Z" fill="#FF3A44"/>
                                        <path d="M47.2 18.3L265.4 256L337.8 183.6L66.8 19.6C59.5 15.4 52.7 15.2 47.2 18.3Z" fill="#00E676"/>
                                    </svg>
                                </div>
                                <div class="store-text">
                                    <span class="store-sub">TEMUKAN DI</span>
                                    <span class="store-name">Google Play</span>
                                </div>
                            </a>

                            <!-- Apple App Store Button -->
                            <a href="#unduh" class="btn-store-badge appstore">
                                <div class="store-icon">
                                    <i class="fa-brands fa-apple" style="font-size: 32px; color: #ffffff;"></i>
                                </div>
                                <div class="store-text">
                                    <span class="store-sub">Download di</span>
                                    <span class="store-name">App Store</span>
                                </div>
                            </a>
                        </div>

                        <!-- Quick Direct APK Badges -->
                        <div class="apk-quick-row">
                            <span class="apk-quick-label"><i class="fa-solid fa-download"></i> Unduh APK Langsung:</span>
                            <a href="<?= base_url('maijek_app.apk') ?>" class="apk-chip" download title="Unduh APK Penumpang">
                                <i class="fa-brands fa-android"></i> Penumpang
                            </a>
                            <a href="<?= base_url('maijek_driver.apk') ?>" class="apk-chip" download title="Unduh APK Driver">
                                <i class="fa-solid fa-motorcycle"></i> Driver
                            </a>
                            <a href="<?= base_url('downloads/maijek_mitra.apk') ?>" class="apk-chip" download title="Unduh APK Merchant">
                                <i class="fa-solid fa-store"></i> Merchant
                            </a>
                        </div>
                    </div>

                    <!-- Hero Stats Bar -->
                    <div class="hero-stats-bar">
                        <div class="stat-item">
                            <h3><?= esc($total_drivers ?? 38) ?>+</h3>
                            <p>Mitra Driver Siaga</p>
                        </div>
                        <div class="stat-item">
                            <h3><?= esc($total_merchants ?? count($merchants ?? [])) ?>+</h3>
                            <p>Mitra Resto & Cafe</p>
                        </div>
                        <div class="stat-item">
                            <h3>100%</h3>
                            <p>Karya Anak Mandar</p>
                        </div>
                        <div class="stat-item">
                            <h3>24/7</h3>
                            <p>Layanan Cepat</p>
                        </div>
                    </div>
                </div>

                <!-- Hero Right Interactive Card -->
                <div class="hero-preview-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <span style="font-size: 0.85rem; font-weight: 700; color: white; display: flex; align-items: center; gap: 8px;">
                            <span class="pulse-circle"></span> Live Radar Driver & Resto
                        </span>
                        <span style="font-size: 0.75rem; background: rgba(253, 184, 19, 0.2); color: var(--accent-gold); padding: 3px 10px; border-radius: 20px; font-weight: 700;">
                            Polman Area
                        </span>
                    </div>

                    <div style="background: rgba(14, 27, 56, 0.7); border-radius: 16px; padding: 22px; text-align: left; margin-bottom: 18px; border: 1px solid rgba(255,255,255,0.08);">
                        <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 14px;">
                            <div style="width: 46px; height: 46px; border-radius: 12px; background: rgba(253, 184, 19, 0.15); color: var(--accent-gold); display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                                <i class="fa-solid fa-utensils"></i>
                            </div>
                            <div>
                                <div style="font-size: 0.76rem; color: #94a3b8; text-transform: uppercase; font-weight: 700;">Layanan Paling Favorit</div>
                                <div style="font-size: 1.1rem; font-weight: 800; color: white;">Mai-Food Delivery</div>
                            </div>
                        </div>
                        <p style="font-size: 0.82rem; color: #cbd5e1; margin-bottom: 0;">
                            Pesan makanan langsung diantar dari <b>Kofimo, OBAT HAUS, JEYU TAKOYAKI, Ikan Bakar Kapuka</b>, dan resto pilihan lainnya.
                        </p>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; text-align: left;">
                        <div style="background: rgba(255,255,255,0.05); padding: 14px; border-radius: 12px;">
                            <div style="font-size: 0.72rem; color: #94a3b8;">Tarif Ojek Motor</div>
                            <div style="font-weight: 800; font-size: 1rem; color: #6ee7b7;">Mulai Rp <?= number_format((float)($settings['base_fare'] ?? 8500), 0, ',', '.') ?></div>
                        </div>
                        <div style="background: rgba(255,255,255,0.05); padding: 14px; border-radius: 12px;">
                            <div style="font-size: 0.72rem; color: #94a3b8;">Tarif Mobil Sejuk</div>
                            <div style="font-weight: 800; font-size: 1rem; color: #93c5fd;">Mulai Rp <?= number_format((float)($settings['ride_mobil_base_fare'] ?? 11000), 0, ',', '.') ?></div>
                        </div>
                    </div>

                    <div style="margin-top: 20px; padding-top: 14px; border-top: 1px solid rgba(255,255,255,0.1); font-size: 0.8rem; color: #94a3b8;">
                        <i class="fa-solid fa-shield-check" style="color: #10b981;"></i> Pembayaran Praktis: Tunai, QRIS & Mai-Pay (Powered by Duitku)
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= MITRA MERCHANT SECTION ================= -->
    <section id="mitra" class="section-padding merchant-section">
        <div class="container">
            <div class="section-header">
                <span class="section-tag"><i class="fa-solid fa-store"></i> Mitra Resmi Maijek</span>
                <h2 class="section-title">Jelajahi Mitra Restoran & Kuliner Favorit</h2>
                <p class="section-subtitle">
                    Temukan aneka hidangan khas, kopi kekinian, street food, dan jajanan favorit di Polewali Mandar yang siap diantar cepat ke depan pintu rumah Anda.
                </p>
            </div>

            <!-- Filter Buttons -->
            <div class="filter-tabs-wrapper">
                <button type="button" class="filter-tab active" onclick="filterMerchants('all', this)">
                    <i class="fa-solid fa-border-all"></i> Semua Mitra (<?= count($merchants ?? []) ?>)
                </button>
                <button type="button" class="filter-tab" onclick="filterMerchants('open', this)">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #16a34a; display: inline-block;"></span> Sedang Buka
                </button>
                <button type="button" class="filter-tab" onclick="filterMerchants('cafe', this)">
                    <i class="fa-solid fa-mug-hot"></i> Kopi & Cafe
                </button>
                <button type="button" class="filter-tab" onclick="filterMerchants('food', this)">
                    <i class="fa-solid fa-bowl-rice"></i> Makanan & Resto
                </button>
            </div>

            <!-- Search Bar Realtime -->
            <div class="merchant-search-wrapper">
                <i class="fa-solid fa-magnifying-glass merchant-search-icon"></i>
                <input type="text" id="merchantSearchInput" oninput="liveSearchMerchants()" placeholder="Cari nama resto, cafe, warkop, atau toko (misal: Kofimo, Takoyaki)..." class="merchant-search-input">
                <div id="merchantSearchSummary" style="font-size: 0.8rem; color: #64748b; text-align: center; margin-top: 8px;"></div>
            </div>

            <!-- Merchants Cards Grid -->
            <div class="merchants-grid" id="merchantsContainer">
                <?php if (!empty($merchants)): ?>
                    <?php foreach ($merchants as $m): ?>
                        <?php
                        $isOpen = !empty($m['is_open']) && (int)$m['is_open'] === 1;
                        $cat = trim((string)($m['category'] ?? ''));
                        if (empty($cat)) {
                            $cat = (strtolower($m['merchant_type'] ?? '') === 'mart') ? 'Toko & Mart' : 'Kuliner Polman';
                        }
                        $isCafe = (stripos($cat, 'kopi') !== false || stripos($cat, 'cafe') !== false || stripos($m['name'], 'kopi') !== false || stripos($m['name'], 'haus') !== false);
                        
                        // Gambar usaha
                        $photoUrl = '';
                        $candidate = !empty($m['photo']) ? $m['photo'] : (!empty($m['business_image']) ? $m['business_image'] : '');
                        if (!empty($candidate)) {
                            $photoUrl = (strpos($candidate, 'http') === 0) ? $candidate : base_url($candidate);
                        }

                        // Waktu buka
                        $hoursStr = 'Buka Setiap Hari';
                        if (!empty($m['opening_time']) && !empty($m['closing_time'])) {
                            $hoursStr = substr($m['opening_time'], 0, 5) . ' - ' . substr($m['closing_time'], 0, 5) . ' WITA';
                        }

                        $merchantDataJson = htmlspecialchars(json_encode([
                            'id' => $m['id'],
                            'name' => $m['name'],
                            'category' => $cat,
                            'address' => $m['address'] ?: 'Polewali Mandar',
                            'phone' => $m['phone'] ?? '',
                            'hours' => $hoursStr,
                            'is_open' => $isOpen,
                            'photo' => $photoUrl,
                            'latitude' => $m['latitude'] ?? '',
                            'longitude' => $m['longitude'] ?? ''
                        ]), ENT_QUOTES, 'UTF-8');
                        ?>
                        <div class="merchant-card" 
                             data-open="<?= $isOpen ? '1' : '0' ?>"
                             data-is-cafe="<?= $isCafe ? '1' : '0' ?>"
                             data-type="<?= esc($m['merchant_type'] ?? 'food') ?>"
                             data-name="<?= esc(strtolower($m['name'])) ?>"
                             data-address="<?= esc(strtolower($m['address'] ?? '')) ?>"
                             onclick="openMerchantModal(<?= $merchantDataJson ?>)">
                            
                            <div class="merchant-card-cover" style="<?= $photoUrl ? 'background-image: url(\'' . $photoUrl . '\');' : '' ?>">
                                <div class="merchant-card-overlay"></div>
                                
                                <span class="merchant-badge-status <?= $isOpen ? 'open' : 'closed' ?>">
                                    <?php if ($isOpen): ?>
                                        <span style="width: 7px; height: 7px; border-radius: 50%; background: #16a34a;"></span>
                                        Buka Sekarang
                                    <?php else: ?>
                                        <span style="width: 7px; height: 7px; border-radius: 50%; background: #94a3b8;"></span>
                                        Sedang Tutup
                                    <?php endif; ?>
                                </span>

                                <?php if (!$photoUrl): ?>
                                    <div style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.3); font-size: 3.5rem;">
                                        <i class="fa-solid fa-store"></i>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="merchant-card-body">
                                <span class="merchant-cat-pill">
                                    <i class="fa-solid fa-tag"></i> <?= esc($cat) ?>
                                </span>

                                <h3 class="merchant-name"><?= esc($m['name']) ?></h3>

                                <div class="merchant-address">
                                    <i class="fa-solid fa-location-dot"></i>
                                    <span><?= esc($m['address'] ?: 'Polewali Mandar, Sulawesi Barat') ?></span>
                                </div>

                                <div class="merchant-footer-info">
                                    <div class="merchant-hours">
                                        <i class="fa-regular fa-clock" style="color: var(--accent-gold);"></i>
                                        <span><?= esc($hoursStr) ?></span>
                                    </div>
                                    <span class="btn-order-merchant">
                                        <i class="fa-solid fa-eye"></i> Detail
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--text-muted);">
                        Belum ada data merchant yang terdaftar.
                    </div>
                <?php endif; ?>
            </div>

            <div id="noMerchantMatch" style="display: none; text-align: center; padding: 50px 20px; color: var(--text-muted);">
                <i class="fa-solid fa-magnifying-glass" style="font-size: 2.5rem; color: #cbd5e1; margin-bottom: 12px; display: block;"></i>
                <h4 style="color: var(--primary-navy); margin-bottom: 6px;">Resto Tidak Ditemukan</h4>
                <p>Coba gunakan kata kunci pencarian yang lain, atau jelajahi kategori di atas.</p>
            </div>

            <!-- Merchant Join Call To Action -->
            <div class="merchant-cta-box">
                <div class="merchant-cta-content">
                    <div style="display: inline-flex; align-items: center; gap: 6px; color: var(--accent-gold); font-size: 0.85rem; font-weight: 700; text-transform: uppercase; margin-bottom: 6px;">
                        <i class="fa-solid fa-handshake"></i> Peluang Kemitraan Usaha
                    </div>
                    <h3>Punya Usaha Kuliner atau Toko di Polewali Mandar?</h3>
                    <p>
                        Tingkatkan omzet usaha Anda bersama Maijek. Daftarkan warung, cafe, atau toko Anda tanpa ribet. Dapatkan pesanan tanpa batas dan langsung diantar oleh kurir kami.
                    </p>
                </div>
                <div>
                    <a href="https://wa.me/6285117228559?text=Halo%20Admin%20Maijek,%20saya%20tertarik%20mendaftarkan%20usaha%20saya%20menjadi%20Mitra%20Merchant%20Maijek" target="_blank" class="btn-join-merchant">
                        <i class="fa-brands fa-whatsapp" style="font-size: 1.3rem;"></i> Daftar Mitra via WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= LAYANAN UNGGULAN ================= -->
    <section id="layanan" class="section-padding">
        <div class="container">
            <div class="section-header">
                <span class="section-tag"><i class="fa-solid fa-cubes"></i> Solusi Sehari-Hari</span>
                <h2 class="section-title">Semua Kebutuhan Dalam Satu Aplikasi</h2>
                <p class="section-subtitle">
                    Dari mobilitas harian hingga urusan perut dan logistik barang, Maijek menyediakan ragam armada siap jemput dan antar di Polewali Mandar.
                </p>
            </div>

            <div class="services-grid">
                <!-- Mai-Ride -->
                <div class="service-card">
                    <div class="service-icon-wrap ride">
                        <i class="fa-solid fa-motorcycle"></i>
                    </div>
                    <span class="service-fare-badge">Mulai Rp <?= number_format((float)($settings['base_fare'] ?? 8500), 0, ',', '.') ?></span>
                    <h3>Mai-Ride (Motor)</h3>
                    <p>
                        Layanan ojek motor lincah anti-macet. Cocok untuk pergi kerja, sekolah, kampus, atau bepergian kilat di area Polewali Mandar dengan tarif ramah kantong.
                    </p>
                </div>

                <!-- Mai-Car -->
                <div class="service-card">
                    <div class="service-icon-wrap car">
                        <i class="fa-solid fa-car-side"></i>
                    </div>
                    <span class="service-fare-badge">Mulai Rp <?= number_format((float)($settings['ride_mobil_base_fare'] ?? 11000), 0, ',', '.') ?></span>
                    <h3>Mai-Car (Mobil)</h3>
                    <p>
                        Transportasi roda empat ber-AC yang nyaman, bersih, dan privat. Pilihan terbaik untuk bepergian bersama keluarga, belanja banyak, atau saat cuaca hujan.
                    </p>
                </div>

                <!-- Mai-Food -->
                <div class="service-card">
                    <div class="service-icon-wrap food">
                        <i class="fa-solid fa-utensils"></i>
                    </div>
                    <span class="service-fare-badge">Mulai Rp <?= number_format((float)($settings['food_base_fare'] ?? 7000), 0, ',', '.') ?></span>
                    <h3>Mai-Food (Kuliner)</h3>
                    <p>
                        Layanan pesan antar makanan dari puluhan mitra restoran, warkop, dan cafe lokal terfavorit. Cepat sampai dan makanan tetap hangat saat diterima.
                    </p>
                </div>

                <!-- Mai-Send -->
                <div class="service-card">
                    <div class="service-icon-wrap send">
                        <i class="fa-solid fa-box"></i>
                    </div>
                    <span class="service-fare-badge">Mulai Rp <?= number_format((float)($settings['send_motor_base_fare'] ?? 8000), 0, ',', '.') ?></span>
                    <h3>Mai-Send (Kirim Barang)</h3>
                    <p>
                        Kirim paket, berkas dokumen, kado, atau barang dagangan sameday langsung sampai dalam hitungan menit di seluruh wilayah kota Polman.
                    </p>
                </div>

                <!-- Mai-Titip -->
                <div class="service-card">
                    <div class="service-icon-wrap titip">
                        <i class="fa-solid fa-basket-shopping"></i>
                    </div>
                    <span class="service-fare-badge">Mulai Rp <?= number_format((float)($settings['titip_motor_base_fare'] ?? 8000), 0, ',', '.') ?></span>
                    <h3>Mai-Titip (Jasa Titip)</h3>
                    <p>
                        Butuh belanja sayur di pasar, beli obat di apotek, atau titip beli sesuatu di toko terdekat? Driver kami siap membelikan dan mengantar ke rumah Anda.
                    </p>
                </div>

                <!-- Mai-Mart -->
                <div class="service-card">
                    <div class="service-icon-wrap mart">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </div>
                    <span class="service-fare-badge">Belanja Mudah</span>
                    <h3>Mai-Mart (Sembako)</h3>
                    <p>
                        Belanja kebutuhan harian, sembako, dan perlengkapan rumah tangga dari mini market atau toko mitra terpercaya tanpa perlu antre di kasir.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= KALKULATOR & SIMULASI TARIF INTERAKTIF ================= -->
    <section id="estimasi" class="section-padding estimator-section">
        <div class="container">
            <div class="section-header" style="color: white;">
                <span class="section-tag" style="background: rgba(253, 184, 19, 0.2); color: var(--accent-gold);">
                    <i class="fa-solid fa-calculator"></i> Transparansi Tarif
                </span>
                <h2 class="section-title" style="color: white;">Simulasi Tarif & Estimasi Ongkir</h2>
                <p class="section-subtitle" style="color: #cbd5e1;">
                    Cek perkiraan ongkos perjalanan atau tarif antar sebelum memesan. Transparan, adil, dan tanpa biaya tersembunyi.
                </p>
            </div>

            <div class="estimator-card">
                <!-- Service Selector Tabs -->
                <div class="estimator-tabs">
                    <button type="button" class="estimator-tab active" onclick="selectEstimatorService('ride', this)">
                        <i class="fa-solid fa-motorcycle"></i> Mai-Ride (Motor)
                    </button>
                    <button type="button" class="estimator-tab" onclick="selectEstimatorService('car', this)">
                        <i class="fa-solid fa-car-side"></i> Mai-Car (Mobil)
                    </button>
                    <button type="button" class="estimator-tab" onclick="selectEstimatorService('food', this)">
                        <i class="fa-solid fa-utensils"></i> Mai-Food (Kuliner)
                    </button>
                    <button type="button" class="estimator-tab" onclick="selectEstimatorService('send', this)">
                        <i class="fa-solid fa-box"></i> Mai-Send (Kurir Paket)
                    </button>
                </div>

                <!-- Distance Slider Container -->
                <div class="slider-container">
                    <div class="slider-header">
                        <span style="font-size: 0.95rem; font-weight: 600; color: #cbd5e1;">
                            <i class="fa-solid fa-route" style="color: var(--accent-gold);"></i> Jarak Pengantaran:
                        </span>
                        <span class="slider-val-badge" id="sliderValDisplay">3 KM</span>
                    </div>

                    <input type="range" min="1" max="25" value="3" class="fare-slider" id="fareDistanceSlider" oninput="updateFareEstimator()">

                    <!-- Preset Route Shortcuts in Polman -->
                    <div class="preset-routes">
                        <span style="font-size: 0.76rem; color: #94a3b8; align-self: center;">Pintas Rute:</span>
                        <button type="button" class="btn-preset" onclick="setDistance(2)">Manding - Pekkabata (2 KM)</button>
                        <button type="button" class="btn-preset" onclick="setDistance(5)">Polewali - Matakali (5 KM)</button>
                        <button type="button" class="btn-preset" onclick="setDistance(14)">Polewali - Wonomulyo (14 KM)</button>
                        <button type="button" class="btn-preset" onclick="setDistance(20)">Polewali - Tinambung (20 KM)</button>
                    </div>
                </div>

                <!-- Result Fare Box -->
                <div class="result-fare-box">
                    <div>
                        <div style="font-size: 0.8rem; text-transform: uppercase; color: #94a3b8; font-weight: 700;">Perkiraan Tarif Resmi</div>
                        <div class="fare-amount-text" id="fareEstimatedAmount">Rp 13.500</div>
                        <div style="font-size: 0.8rem; color: #cbd5e1; margin-top: 4px;" id="fareFormulaNote">
                            Tarif Dasar: Rp 8.500 + Tarif Jarak (2 KM x Rp 2.500)
                        </div>
                    </div>

                    <div>
                        <a href="#unduh" class="btn-primary" style="background: var(--accent-gold); color: var(--navy-dark); font-weight: 800; padding: 12px 24px; box-shadow: 0 4px 15px rgba(253, 184, 19, 0.4);">
                            <i class="fa-solid fa-mobile-screen"></i> Pesan di Aplikasi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= CARA KERJA & ALUR ================= -->
    <section id="cara-kerja" class="section-padding" style="background: white; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);">
        <div class="container">
            <div class="section-header">
                <span class="section-tag"><i class="fa-solid fa-route"></i> Praktis & Cepat</span>
                <h2 class="section-title">Bagaimana Cara Pesan di Maijek?</h2>
                <p class="section-subtitle">
                    Hanya butuh 4 langkah mudah dari smartphone Anda untuk menikmati kemudahan layanan Maijek.
                </p>
            </div>

            <div class="workflow-box">
                <div class="workflow-grid">
                    <div class="workflow-step">
                        <div class="step-num-bubble">1</div>
                        <h4>Pilih Layanan</h4>
                        <p>Buka aplikasi Maijek, pilih menu Ride, Car, Food, Send, Titip, atau Mart sesuai kebutuhan Anda.</p>
                    </div>

                    <div class="workflow-step">
                        <div class="step-num-bubble">2</div>
                        <h4>Tentukan Lokasi</h4>
                        <p>Masukkan titik penjemputan dan tujuan pengantaran Anda. Tarif transparan akan otomatis muncul di layar.</p>
                    </div>

                    <div class="workflow-step">
                        <div class="step-num-bubble">3</div>
                        <h4>Pilih Pembayaran</h4>
                        <p>Bebas pilih bayar Tunai ke driver, atau gunakan dompet digital Mai-Pay (QRIS Duitku) yang aman dan instan.</p>
                    </div>

                    <div class="workflow-step">
                        <div class="step-num-bubble">4</div>
                        <h4>Driver Menjemput</h4>
                        <p>Driver terdekat akan segera datang menjemput Anda atau mengantarkan pesanan makanan Anda sampai tujuan.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= TESTIMONI PENGGUNA & MITRA ================= -->
    <section id="testimoni" class="section-padding" style="background: #f8fafc;">
        <div class="container">
            <div class="section-header">
                <span class="section-tag"><i class="fa-solid fa-heart"></i> Cerita Pelanggan</span>
                <h2 class="section-title">Apa Kata Masyarakat Polewali Mandar?</h2>
                <p class="section-subtitle">
                    Ribuan warga, pelajar, pemilik usaha kuliner, dan mitra driver telah merasakan kemudahan bersama Maijek.
                </p>
            </div>

            <div class="testimonials-grid">
                <!-- Testi 1 -->
                <div class="testimonial-card">
                    <div class="stars-row">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                    </div>
                    <p class="testimonial-text">
                        "Sangat membantu mobilitas harian saya dari Manding ke kantor di Polewali. Drivernya sopan, datang cepat, dan tarifnya sangat terjangkau dibanding ojek pangkalan."
                    </p>
                    <div class="testi-user-wrap">
                        <div class="testi-avatar" style="background: #e0e7ff; color: #3730a3;">R</div>
                        <div>
                            <div class="testi-name">Rahmat Hidayat</div>
                            <div class="testi-role">Pegawai Swasta &bull; Polewali</div>
                        </div>
                    </div>
                </div>

                <!-- Testi 2 -->
                <div class="testimonial-card">
                    <div class="stars-row">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                    </div>
                    <p class="testimonial-text">
                        "Paling suka pesan Mai-Food kalau lagi sibuk ngurus anak di rumah. Pilihan resto di Wonomulyo lengkap, packing makanannya rapi dan sampai masih panas!"
                    </p>
                    <div class="testi-user-wrap">
                        <div class="testi-avatar" style="background: #fce7f3; color: #be185d;">N</div>
                        <div>
                            <div class="testi-name">Nur Annisa</div>
                            <div class="testi-role">Ibu Rumah Tangga &bull; Wonomulyo</div>
                        </div>
                    </div>
                </div>

                <!-- Testi 3 -->
                <div class="testimonial-card">
                    <div class="stars-row">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                    </div>
                    <p class="testimonial-text">
                        "Omzet kafe kami meningkat pesat sejak bergabung jadi mitra merchant resmi Maijek. Pelanggan yang mager keluar sekarang tinggal pesan lewat aplikasi."
                    </p>
                    <div class="testi-user-wrap">
                        <div class="testi-avatar" style="background: #fef3c7; color: #b45309;">K</div>
                        <div>
                            <div class="testi-name">Owner Kofimo Cafe</div>
                            <div class="testi-role">Mitra Merchant UMKM &bull; Polman</div>
                        </div>
                    </div>
                </div>

                <!-- Testi 4 -->
                <div class="testimonial-card">
                    <div class="stars-row">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                    </div>
                    <p class="testimonial-text">
                        "Aplikasi buatan daerah yang membanggakan. Bagi hasilnya transparan, penarikan saldo cepat, dan orderan di seputaran Polman lancar setiap hari."
                    </p>
                    <div class="testi-user-wrap">
                        <div class="testi-avatar" style="background: #dcfce7; color: #15803d;">I</div>
                        <div>
                            <div class="testi-name">Pak Ilham</div>
                            <div class="testi-role">Mitra Driver Mai-Ride &bull; Matakali</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= TANYA JAWAB (FAQ) ================= -->
    <section id="faq" class="section-padding" style="background: #ffffff; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);">
        <div class="container">
            <div class="section-header">
                <span class="section-tag"><i class="fa-solid fa-circle-question"></i> Pusat Informasi</span>
                <h2 class="section-title">Pertanyaan yang Sering Diajukan</h2>
                <p class="section-subtitle">
                    Temukan jawaban seputar pemesanan, kemitraan, dan keamanan layanan Maijek di Polewali Mandar.
                </p>
            </div>

            <div style="max-width: 800px; margin: 0 auto;">
                <!-- FAQ 1 -->
                <div class="faq-item" onclick="toggleFaq(this)">
                    <div class="faq-header">
                        <span>Apa itu aplikasi Maijek?</span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                    <div class="faq-body">
                        Maijek adalah platform layanan transportasi online roda dua (Mai-Ride), roda empat (Mai-Car), pesan-antar kuliner (Mai-Food), kurir kilat (Mai-Send), dan belanja harian (Mai-Titip & Mai-Mart) pertama yang beroperasi secara resmi dan terintegrasi di Kabupaten Polewali Mandar, Sulawesi Barat.
                    </div>
                </div>

                <!-- FAQ 2 -->
                <div class="faq-item" onclick="toggleFaq(this)">
                    <div class="faq-header">
                        <span>Bagaimana cara menggunakan aplikasi Maijek?</span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                    <div class="faq-body">
                        Anda dapat mengunduh aplikasi Maijek langsung dari Google Play Store, Apple App Store, atau unduh berkas APK di website resmi ini. Anda bahkan bisa langsung menjelajahi menu makanan dan cek tarif tanpa login. Saat ingin melakukan pemesanan, cukup daftarkan nomor telepon aktif Anda.
                    </div>
                </div>

                <!-- FAQ 3 -->
                <div class="faq-item" onclick="toggleFaq(this)">
                    <div class="faq-header">
                        <span>Metode pembayaran apa saja yang diterima?</span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                    <div class="faq-body">
                        Maijek mendukung dua metode pembayaran yang fleksibel: Tunai langsung kepada driver saat pesanan tiba, atau Non-Tunai instan menggunakan QRIS semua Bank & E-Wallet (BCA, BRI, Mandiri, BNI, Dana, OVO, GoPay, ShopeePay) yang terhubung aman lewat gerbang pembayaran resmi Duitku.
                    </div>
                </div>

                <!-- FAQ 4 -->
                <div class="faq-item" onclick="toggleFaq(this)">
                    <div class="faq-header">
                        <span>Bagaimana cara mendaftar menjadi Mitra Driver?</span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                    <div class="faq-body">
                        Siapkan SIM, STNK aktif, KTP, dan kendaraan motor atau mobil yang laik jalan. Anda dapat mengunduh aplikasi <b>Maijek Driver</b> di pusat unduhan atau langsung menghubungi Customer Service kami melalui WhatsApp untuk proses verifikasi kilat.
                    </div>
                </div>

                <!-- FAQ 5 -->
                <div class="faq-item" onclick="toggleFaq(this)">
                    <div class="faq-header">
                        <span>Bagaimana cara mendaftarkan usaha restoran atau toko saya?</span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                    <div class="faq-body">
                        Kami sangat terbuka untuk merangkul UMKM lokal di Polewali Mandar! Klik tombol <b>Daftar Mitra via WhatsApp</b> di website ini atau unduh aplikasi <b>Maijek Merchant</b> untuk memasukkan profil resto, jam buka, dan foto menu kuliner Anda.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= DOWNLOAD CENTER ================= -->
    <section id="unduh" class="section-padding">
        <div class="container">
            <div class="download-center">
                <div style="text-align: center; max-width: 680px; margin: 0 auto; position: relative; z-index: 2;">
                    <span style="display: inline-block; padding: 4px 14px; background: rgba(253, 184, 19, 0.2); color: var(--accent-gold); border-radius: 20px; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; margin-bottom: 12px;">
                        <i class="fa-solid fa-cloud-arrow-down"></i> Pusat Unduh Resmi
                    </span>
                    <h2 style="font-size: 2.4rem; margin-bottom: 12px; color: white;">Download Aplikasi Maijek Sekarang</h2>
                    <p style="color: #cbd5e1; font-size: 1.05rem; margin-bottom: 30px;">
                        Dapatkan kemudahan transportasi, pesan antar makanan, dan pengiriman barang Polewali Mandar langsung di smartphone Anda melalui <b>Google Play Store</b> dan <b>Apple App Store</b>, atau unduh paket instalasi APK resmi di bawah ini.
                    </p>

                    <!-- Official Store Badges (Google Play & App Store) -->
                    <div class="store-badges-row" style="justify-content: center; margin-bottom: 45px;">
                        <!-- Google Play Badge -->
                        <a href="https://play.google.com/store/apps" target="_blank" class="btn-store-badge playstore" style="background: rgba(0, 0, 0, 0.7); border: 1.5px solid rgba(255, 255, 255, 0.3); padding: 12px 26px;">
                            <div class="store-icon">
                                <svg width="30" height="30" viewBox="0 0 512 512" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M47.2 18.3C40.6 22 36.5 28.9 36.5 37.4V474.6C36.5 483.1 40.6 490 47.2 493.7L265.4 256 47.2 18.3Z" fill="#00D3FF"/>
                                    <path d="M337.8 183.6L265.4 256L337.8 328.4L422.5 279.5C437.9 270.6 446.5 255.4 446.5 256C446.5 256.6 437.9 241.4 422.5 232.5L337.8 183.6Z" fill="#FFC900"/>
                                    <path d="M47.2 493.7C52.7 496.8 59.5 496.6 66.8 492.4L337.8 328.4L265.4 256L47.2 493.7Z" fill="#FF3A44"/>
                                    <path d="M47.2 18.3L265.4 256L337.8 183.6L66.8 19.6C59.5 15.4 52.7 15.2 47.2 18.3Z" fill="#00E676"/>
                                </svg>
                            </div>
                            <div class="store-text">
                                <span class="store-sub">TEMUKAN DI</span>
                                <span class="store-name" style="font-size: 1.25rem;">Google Play</span>
                            </div>
                        </a>

                        <!-- Apple App Store Badge -->
                        <a href="https://apps.apple.com" target="_blank" class="btn-store-badge appstore" style="background: rgba(0, 0, 0, 0.7); border: 1.5px solid rgba(255, 255, 255, 0.3); padding: 12px 26px;">
                            <div class="store-icon">
                                <i class="fa-brands fa-apple" style="font-size: 36px; color: #ffffff;"></i>
                            </div>
                            <div class="store-text">
                                <span class="store-sub">Download di</span>
                                <span class="store-name" style="font-size: 1.25rem;">App Store</span>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="dl-cards-grid">
                    <!-- APK Penumpang -->
                    <div class="dl-card">
                        <div class="dl-card-icon">
                            <i class="fa-solid fa-mobile-screen-button"></i>
                        </div>
                        <h4>Maijek Penumpang</h4>
                        <p>Untuk pesan ojek motor, mobil sejuk, pesan antar makanan, dan kirim barang kebutuhan harian.</p>
                        <a href="<?= base_url('maijek_app.apk') ?>" class="btn-card-apk" download>
                            <i class="fa-brands fa-android"></i> Unduh APK Penumpang
                        </a>
                    </div>

                    <!-- APK Driver -->
                    <div class="dl-card">
                        <div class="dl-card-icon">
                            <i class="fa-solid fa-motorcycle"></i>
                        </div>
                        <h4>Maijek Driver</h4>
                        <p>Khusus pahlawan jalanan mitra pengemudi motor & mobil untuk menerima order dan mendapatkan penghasilan.</p>
                        <a href="<?= base_url('maijek_driver.apk') ?>" class="btn-card-apk" download>
                            <i class="fa-brands fa-android"></i> Unduh APK Driver
                        </a>
                    </div>

                    <!-- APK Mitra Merchant -->
                    <div class="dl-card">
                        <div class="dl-card-icon">
                            <i class="fa-solid fa-shop"></i>
                        </div>
                        <h4>Maijek Merchant</h4>
                        <p>Khusus pemilik usaha restoran, cafe, warung makan, dan toko untuk mengelola pesanan & menu kuliner.</p>
                        <a href="<?= base_url('downloads/maijek_mitra.apk') ?>" class="btn-card-apk" download>
                            <i class="fa-brands fa-android"></i> Unduh APK Merchant
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <!-- Col 1: Brand Info -->
                <div class="footer-col">
                    <div class="logo-group" style="margin-bottom: 18px;">
                        <div class="logo-symbol">M</div>
                        <div class="logo-text">
                            <div class="logo-title" style="color: white;">Mai<span style="color: var(--accent-gold);">jek</span></div>
                            <div class="logo-tagline" style="color: #94a3b8;">Polewali Mandar</div>
                        </div>
                    </div>
                    <p style="font-size: 0.9rem; line-height: 1.7; color: #94a3b8; margin-bottom: 20px;">
                        Maijek adalah platform layanan transportasi online, kuliner, dan pengiriman terintegrasi pertama kebanggaan masyarakat Kabupaten Polewali Mandar, Sulawesi Barat.
                    </p>
                </div>

                <!-- Col 2: Layanan -->
                <div class="footer-col">
                    <h4>Layanan Kami</h4>
                    <ul class="footer-links">
                        <li><a href="#layanan"><i class="fa-solid fa-chevron-right" style="font-size: 0.7rem;"></i> Mai-Ride (Motor)</a></li>
                        <li><a href="#layanan"><i class="fa-solid fa-chevron-right" style="font-size: 0.7rem;"></i> Mai-Car (Mobil)</a></li>
                        <li><a href="#mitra"><i class="fa-solid fa-chevron-right" style="font-size: 0.7rem;"></i> Mai-Food (Kuliner)</a></li>
                        <li><a href="#layanan"><i class="fa-solid fa-chevron-right" style="font-size: 0.7rem;"></i> Mai-Send (Kirim Barang)</a></li>
                        <li><a href="#layanan"><i class="fa-solid fa-chevron-right" style="font-size: 0.7rem;"></i> Mai-Titip (Jasa Titip)</a></li>
                        <li><a href="#layanan"><i class="fa-solid fa-chevron-right" style="font-size: 0.7rem;"></i> Mai-Mart (Belanja)</a></li>
                    </ul>
                </div>

                <!-- Col 3: Navigasi Cepat -->
                <div class="footer-col">
                    <h4>Informasi & Portal</h4>
                    <ul class="footer-links">
                        <li><a href="#beranda">Beranda</a></li>
                        <li><a href="#mitra">Daftar Mitra Merchant</a></li>
                        <li><a href="#estimasi">Simulasi Tarif</a></li>
                        <li><a href="#testimoni">Testimoni Pengguna</a></li>
                        <li><a href="#faq">FAQ</a></li>
                        <li><a href="#unduh">Pusat Unduhan APK</a></li>
                        <li><a href="<?= base_url('privacy') ?>">Kebijakan Privasi</a></li>
                        <li><a href="<?= base_url('admin/login') ?>"><i class="fa-solid fa-lock" style="font-size: 0.75rem;"></i> Portal Admin Maijek</a></li>
                    </ul>
                </div>

                <!-- Col 4: Kontak & Wilayah -->
                <div class="footer-col">
                    <h4>Pusat Bantuan</h4>
                    <div class="contact-info-item">
                        <i class="fa-solid fa-location-dot"></i>
                        <span>Polewali Mandar, Sulawesi Barat, Indonesia</span>
                    </div>
                    <div class="contact-info-item">
                        <i class="fa-brands fa-whatsapp"></i>
                        <a href="https://wa.me/6285117228559" target="_blank" style="color: #6ee7b7; text-decoration: none; font-weight: 600;">
                            0851-1722-8559
                        </a>
                    </div>
                    <div class="contact-info-item">
                        <i class="fa-solid fa-envelope"></i>
                        <span>support@maijek.polman.id</span>
                    </div>
                    <div style="margin-top: 20px; font-size: 0.8rem; color: #64748b;">
                        <b>Wilayah Operasional:</b><br>
                        Polewali, Wonomulyo, Tinambung, Campalagian, Matakali, & sekitarnya.
                    </div>
                </div>
            </div>

            <div class="bottom-bar">
                &copy; <?= date('Y') ?> <b>Maijek</b> &bull; Layanan Transportasi & Pesan Antar Polewali Mandar. Hak Cipta Dilindungi Undang-Undang.
            </div>
        </div>
    </footer>

    <!-- Floating WhatsApp Support Button -->
    <a href="https://wa.me/6285117228559?text=Halo%20Admin%20Maijek,%20saya%20butuh%20bantuan%20seputar%20aplikasi%20Maijek" 
       target="_blank" 
       class="floating-wa-btn" 
       title="Hubungi Customer Service Maijek via WhatsApp">
        <i class="fa-brands fa-whatsapp"></i>
    </a>

    <!-- Modal Resto Quick View -->
    <div id="merchantModal" class="modal-backdrop" onclick="if(event.target === this) closeMerchantModal()">
        <div class="modal-content-box">
            <div id="modalCover" style="height: 180px; background: linear-gradient(135deg, #0e1b38 0%, #1e293b 100%); background-size: cover; background-position: center; position: relative;">
                <button type="button" onclick="closeMerchantModal()" style="position: absolute; top: 14px; right: 14px; width: 32px; height: 32px; border-radius: 50%; background: rgba(0,0,0,0.6); color: white; border: none; font-size: 1.1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 5;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
                <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(15,23,42,0.85) 0%, transparent 100%);"></div>
                <div style="position: absolute; bottom: 16px; left: 20px; right: 20px; color: white;">
                    <span id="modalCategory" style="font-size: 0.72rem; font-weight: 700; background: var(--accent-gold); color: var(--navy-dark); padding: 3px 8px; border-radius: 4px; display: inline-block; margin-bottom: 6px;">Kategori</span>
                    <h3 id="modalName" style="font-size: 1.4rem; line-height: 1.2; margin: 0; color: white;">Nama Resto</h3>
                </div>
            </div>

            <div style="padding: 24px;">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 14px;">
                    <span id="modalStatusBadge" style="padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;">Status</span>
                    <span style="font-size: 0.8rem; color: #64748b;" id="modalHours"><i class="fa-regular fa-clock"></i> Jam Buka</span>
                </div>

                <div style="font-size: 0.88rem; color: #475569; margin-bottom: 20px; display: flex; align-items: flex-start; gap: 8px; line-height: 1.4;">
                    <i class="fa-solid fa-location-dot" style="color: #ef4444; margin-top: 3px;"></i>
                    <span id="modalAddress">Alamat resto</span>
                </div>

                <div style="background: #f8fafc; border-radius: 12px; padding: 14px 16px; margin-bottom: 20px; border: 1px solid #e2e8f0; font-size: 0.82rem; color: #475569;">
                    <i class="fa-solid fa-circle-info" style="color: var(--primary-green);"></i> 
                    Pesan menu kuliner dari resto ini melalui aplikasi <b>Maijek</b> agar driver terdekat langsung membelikan dan mengantar ke rumah Anda.
                </div>

                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="#unduh" onclick="closeMerchantModal()" class="btn-primary" style="flex: 1; justify-content: center; padding: 10px; text-decoration: none;">
                        <i class="fa-solid fa-download"></i> Pesan di Aplikasi
                    </a>
                    <a id="modalWaBtn" href="#" target="_blank" style="padding: 10px 16px; background: #25d366; color: white; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fa-brands fa-whatsapp"></i> Chat Resto
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Client-side Scripts -->
    <script>
        // Data tarif dari database
        const FARE_SETTINGS = {
            ride: {
                base: <?= (float)($settings['base_fare'] ?? 8500) ?>,
                perKm: <?= (float)($settings['per_km_fare'] ?? 2500) ?>,
                name: 'Mai-Ride (Motor)'
            },
            car: {
                base: <?= (float)($settings['ride_mobil_base_fare'] ?? 11000) ?>,
                perKm: <?= (float)($settings['ride_mobil_per_km_fare'] ?? 4000) ?>,
                name: 'Mai-Car (Mobil)'
            },
            food: {
                base: <?= (float)($settings['food_base_fare'] ?? 7000) ?>,
                perKm: <?= (float)($settings['food_per_km_fare'] ?? 2000) ?>,
                name: 'Mai-Food (Kurir Makanan)'
            },
            send: {
                base: <?= (float)($settings['send_motor_base_fare'] ?? 8000) ?>,
                perKm: <?= (float)($settings['send_motor_per_km_fare'] ?? 2500) ?>,
                name: 'Mai-Send (Paket Barang)'
            }
        };

        let currentEstimatorService = 'ride';

        function selectEstimatorService(service, btn) {
            currentEstimatorService = service;
            document.querySelectorAll('.estimator-tab').forEach(b => b.classList.remove('active'));
            if (btn) btn.classList.add('active');
            updateFareEstimator();
        }

        function setDistance(km) {
            const slider = document.getElementById('fareDistanceSlider');
            if (slider) {
                slider.value = km;
                updateFareEstimator();
            }
        }

        function updateFareEstimator() {
            const slider = document.getElementById('fareDistanceSlider');
            const distDisplay = document.getElementById('sliderValDisplay');
            const fareAmount = document.getElementById('fareEstimatedAmount');
            const formulaNote = document.getElementById('fareFormulaNote');

            if (!slider) return;

            const dist = parseInt(slider.value) || 1;
            if (distDisplay) distDisplay.textContent = dist + ' KM';

            const cfg = FARE_SETTINGS[currentEstimatorService] || FARE_SETTINGS.ride;
            
            // Perhitungan tarif: 1 KM pertama = base_fare, KM selanjutnya = dist-1 * perKm
            let total = cfg.base;
            if (dist > 1) {
                total += (dist - 1) * cfg.perKm;
            }

            const formatted = 'Rp ' + total.toLocaleString('id-ID');
            if (fareAmount) fareAmount.textContent = formatted;

            if (formulaNote) {
                if (dist <= 1) {
                    formulaNote.textContent = 'Tarif Dasar 1 KM Pertama: Rp ' + cfg.base.toLocaleString('id-ID');
                } else {
                    const extraKm = dist - 1;
                    const extraCost = extraKm * cfg.perKm;
                    formulaNote.textContent = 'Tarif Dasar: Rp ' + cfg.base.toLocaleString('id-ID') + ' + Jarak Tambahan (' + extraKm + ' KM x Rp ' + cfg.perKm.toLocaleString('id-ID') + ' = Rp ' + extraCost.toLocaleString('id-ID') + ')';
                }
            }
        }

        function toggleMobileNav() {
            const panel = document.getElementById('mobileMenuPanel');
            if (panel.style.display === 'none' || panel.style.display === '') {
                panel.style.display = 'block';
            } else {
                panel.style.display = 'none';
            }
        }

        let currentCategoryFilter = 'all';

        function filterMerchants(type, btn) {
            currentCategoryFilter = type;
            document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
            if (btn) btn.classList.add('active');
            applyMerchantFilters();
        }

        function liveSearchMerchants() {
            applyMerchantFilters();
        }

        function applyMerchantFilters() {
            const searchInput = document.getElementById('merchantSearchInput');
            const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
            const cards = document.querySelectorAll('.merchant-card');
            const noMatch = document.getElementById('noMerchantMatch');
            const summary = document.getElementById('merchantSearchSummary');

            let visibleCount = 0;

            cards.forEach(card => {
                const isOpen = card.getAttribute('data-open') === '1';
                const isCafe = card.getAttribute('data-is-cafe') === '1';
                const name = (card.getAttribute('data-name') || '').toLowerCase();
                const address = (card.getAttribute('data-address') || '').toLowerCase();

                let matchCategory = false;
                if (currentCategoryFilter === 'all') {
                    matchCategory = true;
                } else if (currentCategoryFilter === 'open') {
                    matchCategory = isOpen;
                } else if (currentCategoryFilter === 'cafe') {
                    matchCategory = isCafe;
                } else if (currentCategoryFilter === 'food') {
                    matchCategory = !isCafe;
                }

                let matchSearch = true;
                if (query !== '') {
                    matchSearch = (name.includes(query) || address.includes(query));
                }

                if (matchCategory && matchSearch) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (noMatch) {
                noMatch.style.display = (visibleCount === 0) ? 'block' : 'none';
            }

            if (summary) {
                if (query !== '') {
                    summary.textContent = 'Ditemukan ' + visibleCount + ' resto/toko yang cocok dengan "' + query + '"';
                } else {
                    summary.textContent = '';
                }
            }
        }

        function openMerchantModal(data) {
            const modal = document.getElementById('merchantModal');
            if (!modal) return;

            document.getElementById('modalName').textContent = data.name;
            document.getElementById('modalCategory').textContent = data.category;
            document.getElementById('modalAddress').textContent = data.address;
            document.getElementById('modalHours').innerHTML = '<i class="fa-regular fa-clock"></i> ' + data.hours;

            const cover = document.getElementById('modalCover');
            if (data.photo) {
                cover.style.backgroundImage = "url('" + data.photo + "')";
            } else {
                cover.style.backgroundImage = "linear-gradient(135deg, #0e1b38 0%, #1e293b 100%)";
            }

            const badge = document.getElementById('modalStatusBadge');
            if (data.is_open) {
                badge.textContent = 'Buka Sekarang';
                badge.style.background = '#dcfce7';
                badge.style.color = '#15803d';
            } else {
                badge.textContent = 'Sedang Tutup';
                badge.style.background = '#f1f5f9';
                badge.style.color = '#64748b';
            }

            const waBtn = document.getElementById('modalWaBtn');
            let phone = (data.phone || '').replace(/[^0-9]/g, '');
            if (phone.startsWith('0')) {
                phone = '62' + phone.substring(1);
            }
            if (phone) {
                waBtn.href = 'https://wa.me/' + phone + '?text=Halo%20' + encodeURIComponent(data.name) + ',%20saya%20melihat%20resto%20Anda%20di%20Maijek.';
                waBtn.style.display = 'inline-flex';
            } else {
                waBtn.style.display = 'none';
            }

            modal.style.display = 'flex';
        }

        function closeMerchantModal() {
            const modal = document.getElementById('merchantModal');
            if (modal) modal.style.display = 'none';
        }

        function toggleFaq(item) {
            const body = item.querySelector('.faq-body');
            const isActive = item.classList.contains('active');

            // Tutup semua FAQ lainnya
            document.querySelectorAll('.faq-item').forEach(fi => {
                fi.classList.remove('active');
                const b = fi.querySelector('.faq-body');
                if (b) b.style.display = 'none';
            });

            if (!isActive && body) {
                item.classList.add('active');
                body.style.display = 'block';
            }
        }

        // Jalankan inisialisasi awal saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            updateFareEstimator();
        });
    </script>
</body>

</html>