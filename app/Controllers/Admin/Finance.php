<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OrderModel;
use App\Models\FoodOrderModel;
use App\Models\SettingModel;

class Finance extends BaseController
{
    private function resolveDateFilter()
    {
        $filter = $this->request->getGet('filter') ?? '';
        $startDate = $this->request->getGet('start_date') ?? '';
        $endDate = $this->request->getGet('end_date') ?? '';

        $today = date('Y-m-d');
        if ($filter === 'today') {
            $startDate = $today;
            $endDate = $today;
        } elseif ($filter === 'yesterday') {
            $startDate = date('Y-m-d', strtotime('-1 day'));
            $endDate = date('Y-m-d', strtotime('-1 day'));
        } elseif ($filter === '7days') {
            $startDate = date('Y-m-d', strtotime('-6 days'));
            $endDate = $today;
        } elseif ($filter === 'this_month') {
            $startDate = date('Y-m-01');
            $endDate = date('Y-m-t');
        } elseif ($filter === 'last_month') {
            $startDate = date('Y-m-01', strtotime('first day of last month'));
            $endDate = date('Y-m-t', strtotime('last day of last month'));
        } elseif (!empty($startDate) || !empty($endDate)) {
            $filter = 'custom';
            if (empty($startDate)) $startDate = '2020-01-01';
            if (empty($endDate)) $endDate = $today;
        } else {
            $filter = 'all';
            $startDate = '';
            $endDate = '';
        }

        return [
            'filter'     => $filter,
            'start_date' => $startDate,
            'end_date'   => $endDate,
        ];
    }

    public function index()
    {
        $orderModel = new OrderModel();
        $foodOrderModel = new FoodOrderModel();
        $settingModel = new SettingModel();
        $db = \Config\Database::connect();

        $dateInfo = $this->resolveDateFilter();
        $filter = $dateInfo['filter'];
        $startDate = $dateInfo['start_date'];
        $endDate = $dateInfo['end_date'];

        $hasDateFilter = ($filter !== 'all');
        $dateCondition = '';
        $dateParams = [];

        if ($hasDateFilter) {
            $startDt = $startDate . ' 00:00:00';
            $endDt = $endDate . ' 23:59:59';
            $dateCondition = " AND created_at >= ? AND created_at <= ?";
            $dateParams = [$startDt, $endDt];
        }

        // 1. Get Settings
        $platformFee = (float)($settingModel->where('setting_key', 'platform_fee')->first()['setting_value'] ?? 15);
        $platformFeeType = $settingModel->where('setting_key', 'platform_fee_type')->first()['setting_value'] ?? 'percent';

        $foodPlatformFee = (float)($settingModel->where('setting_key', 'food_platform_fee')->first()['setting_value'] ?? 15);
        $foodPlatformFeeType = $settingModel->where('setting_key', 'food_platform_fee_type')->first()['setting_value'] ?? 'percent';

        // 2. Successful Orders (Completed)
        $orderQuery = $orderModel->where('status', 'completed');
        if ($hasDateFilter) {
            $orderQuery->where('created_at >=', $startDt)->where('created_at <=', $endDt);
        }
        $completedOrderCount = $orderQuery->countAllResults();

        $foodQuery = $foodOrderModel->where('status', 'completed');
        if ($hasDateFilter) {
            $foodQuery->where('created_at >=', $startDt)->where('created_at <=', $endDt);
        }
        $completedFoodCount = $foodQuery->countAllResults();
        $totalSuccess = $completedOrderCount + $completedFoodCount;

        $orderGrossSql = "SELECT SUM(price + COALESCE(discount_amount, 0)) as total_fare, SUM(COALESCE(discount_amount, 0)) as total_discount FROM orders WHERE status = 'completed'" . $dateCondition;
        $orderGrossQuery = $db->query($orderGrossSql, $dateParams)->getRow();
        $orderOriginalGross = (float)($orderGrossQuery->total_fare ?? 0);
        $orderDiscountSubsidi = (float)($orderGrossQuery->total_discount ?? 0);

        $foodGrossSql = "SELECT SUM(total_price + COALESCE(discount_amount, 0)) as total_gmv, SUM(COALESCE(discount_amount, 0)) as total_discount FROM food_orders WHERE status = 'completed'" . $dateCondition;
        $foodGrossQuery = $db->query($foodGrossSql, $dateParams)->getRow();
        $foodOriginalGross = (float)($foodGrossQuery->total_gmv ?? 0);
        $foodDiscountSubsidi = (float)($foodGrossQuery->total_discount ?? 0);

        $totalGross = $orderOriginalGross + $foodOriginalGross;
        $totalPromoSubsidy = $orderDiscountSubsidi + $foodDiscountSubsidi;

        // Pendapatan Komisi Kotor Platform
        $grossOrderCommission = ($platformFeeType == 'percent') ? ($orderOriginalGross * ($platformFee / 100)) : ($completedOrderCount * $platformFee);
        $grossFoodCommission = ($foodPlatformFeeType == 'percent') ? ($foodOriginalGross * ($foodPlatformFee / 100)) : ($completedFoodCount * $foodPlatformFee);
        $totalGrossCommission = $grossOrderCommission + $grossFoodCommission;

        // Laba Bersih Platform (Komisi Kotor - Beban Subsidi Promo yang Ditanggung Platform)
        $totalNetProfit = $totalGrossCommission - $totalPromoSubsidy;

        // 3. Cancelled Orders (Lost Potential)
        $cancOrderQuery = $orderModel->where('status', 'cancelled');
        if ($hasDateFilter) {
            $cancOrderQuery->where('created_at >=', $startDt)->where('created_at <=', $endDt);
        }
        $cancelledOrderCount = $cancOrderQuery->countAllResults();

        $cancFoodQuery = $foodOrderModel->where('status', 'cancelled');
        if ($hasDateFilter) {
            $cancFoodQuery->where('created_at >=', $startDt)->where('created_at <=', $endDt);
        }
        $cancelledFoodCount = $cancFoodQuery->countAllResults();
        $totalCancelled = $cancelledOrderCount + $cancelledFoodCount;

        $lostOrderSql = "SELECT SUM(price + COALESCE(discount_amount, 0)) as total_fare FROM orders WHERE status = 'cancelled'" . $dateCondition;
        $lostOrderQuery = $db->query($lostOrderSql, $dateParams)->getRow();
        $lostOrderRevenue = (float)($lostOrderQuery->total_fare ?? 0);

        $lostFoodSql = "SELECT SUM(total_price + COALESCE(discount_amount, 0)) as total_gmv FROM food_orders WHERE status = 'cancelled'" . $dateCondition;
        $lostFoodQuery = $db->query($lostFoodSql, $dateParams)->getRow();
        $lostFoodRevenue = (float)($lostFoodQuery->total_gmv ?? 0);

        $lostNetOrder = ($platformFeeType == 'percent') ? ($lostOrderRevenue * ($platformFee / 100)) : ($cancelledOrderCount * $platformFee);
        $lostNetFood = ($foodPlatformFeeType == 'percent') ? ($lostFoodRevenue * ($foodPlatformFee / 100)) : ($cancelledFoodCount * $foodPlatformFee);
        $totalLostProfit = $lostNetOrder + $lostNetFood;

        // 4. Detailed Transactions Table (Server-Side Pagination)
        $page = (int)($this->request->getVar('page_trans') ?? 1);
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        $orderDateCond = $hasDateFilter ? " AND created_at >= '$startDt' AND created_at <= '$endDt'" : "";

        $countQuery = $db->query("
            SELECT COUNT(*) as total FROM (
                SELECT id FROM orders WHERE status IN ('completed', 'cancelled') $orderDateCond
                UNION ALL
                SELECT id FROM food_orders WHERE status IN ('completed', 'cancelled') $orderDateCond
            ) as combined
        ");
        $totalTrans = (int)($countQuery->getRow()->total ?? 0);

        $dataQuery = $db->query("
            SELECT * FROM (
                SELECT id, 'Transport/Kurir' as type, created_at, status, (price + COALESCE(discount_amount, 0)) as gross, COALESCE(discount_amount, 0) as discount_amount 
                FROM orders WHERE status IN ('completed', 'cancelled') $orderDateCond
                UNION ALL
                SELECT id, 'Mai-Food' as type, created_at, status, (total_price + COALESCE(discount_amount, 0)) as gross, COALESCE(discount_amount, 0) as discount_amount 
                FROM food_orders WHERE status IN ('completed', 'cancelled') $orderDateCond
            ) as combined
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?
        ", [$perPage, $offset]);

        $recentTransactions = $dataQuery->getResultArray();

        $mergedTransactions = [];
        foreach ($recentTransactions as $t) {
            $gross = (float)$t['gross'];
            $discount = (float)$t['discount_amount'];
            $feeCommission = 0;
            if ($t['type'] == 'Transport/Kurir') {
                $feeCommission = ($platformFeeType == 'percent') ? ($gross * ($platformFee / 100)) : $platformFee;
                $displayId = $t['id'];
            } else {
                $feeCommission = ($foodPlatformFeeType == 'percent') ? ($gross * ($foodPlatformFee / 100)) : $foodPlatformFee;
                $displayId = 'F' . $t['id'];
            }

            // Margin Bersih Platform (Komisi - Beban Subsidi Promo)
            $net = $feeCommission - $discount;

            $mergedTransactions[] = [
                'id' => $displayId,
                'type' => $t['type'],
                'created_at' => $t['created_at'],
                'status' => $t['status'],
                'gross' => $gross,
                'discount' => $discount,
                'commission' => $feeCommission,
                'net' => $net
            ];
        }

        $pager = \Config\Services::pager();

        // Label periode teks
        $periodLabel = 'Semua Waktu (All Time)';
        if ($filter === 'today') {
            $periodLabel = 'Hari Ini (' . date('d M Y') . ')';
        } elseif ($filter === 'yesterday') {
            $periodLabel = 'Kemarin (' . date('d M Y', strtotime('-1 day')) . ')';
        } elseif ($filter === '7days') {
            $periodLabel = '7 Hari Terakhir (' . date('d M Y', strtotime($startDate)) . ' - ' . date('d M Y', strtotime($endDate)) . ')';
        } elseif ($filter === 'this_month') {
            $periodLabel = 'Bulan Ini (' . date('F Y') . ')';
        } elseif ($filter === 'last_month') {
            $periodLabel = 'Bulan Lalu (' . date('F Y', strtotime('first day of last month')) . ')';
        } elseif ($filter === 'custom') {
            $periodLabel = date('d M Y', strtotime($startDate)) . ' s/d ' . date('d M Y', strtotime($endDate));
        }

        $data = [
            'title' => 'Laporan Keuangan | Maijek Admin',
            'platform_fee' => $platformFee,
            'platform_fee_type' => $platformFeeType,
            'food_platform_fee' => $foodPlatformFee,
            'food_platform_fee_type' => $foodPlatformFeeType,
            'filter' => $filter,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'period_label' => $periodLabel,
            'total_success' => $totalSuccess,
            'total_gross' => $totalGross,
            'total_commission' => $totalGrossCommission,
            'total_promo_subsidy' => $totalPromoSubsidy,
            'total_net_profit' => $totalNetProfit,
            'total_cancelled' => $totalCancelled,
            'total_lost_profit' => $totalLostProfit,
            'transactions' => $mergedTransactions,
            'total_trans' => $totalTrans,
            'pager' => $pager->makeLinks($page, $perPage, $totalTrans, 'default_full', 0, 'page_trans')
        ];

        return view('admin/finance/index', $data);
    }

    public function exportCsv()
    {
        $settingModel = new SettingModel();
        $db = \Config\Database::connect();

        $dateInfo = $this->resolveDateFilter();
        $filter = $dateInfo['filter'];
        $startDate = $dateInfo['start_date'];
        $endDate = $dateInfo['end_date'];

        $hasDateFilter = ($filter !== 'all');
        $startDt = $startDate . ' 00:00:00';
        $endDt = $endDate . ' 23:59:59';
        $orderDateCond = $hasDateFilter ? " AND created_at >= '$startDt' AND created_at <= '$endDt'" : "";

        $platformFee = (float)($settingModel->where('setting_key', 'platform_fee')->first()['setting_value'] ?? 15);
        $platformFeeType = $settingModel->where('setting_key', 'platform_fee_type')->first()['setting_value'] ?? 'percent';

        $foodPlatformFee = (float)($settingModel->where('setting_key', 'food_platform_fee')->first()['setting_value'] ?? 15);
        $foodPlatformFeeType = $settingModel->where('setting_key', 'food_platform_fee_type')->first()['setting_value'] ?? 'percent';

        $dataQuery = $db->query("
            SELECT * FROM (
                SELECT id, 'Transport/Kurir' as type, created_at, status, (price + COALESCE(discount_amount, 0)) as gross, COALESCE(discount_amount, 0) as discount_amount 
                FROM orders WHERE status IN ('completed', 'cancelled') $orderDateCond
                UNION ALL
                SELECT id, 'Mai-Food' as type, created_at, status, (total_price + COALESCE(discount_amount, 0)) as gross, COALESCE(discount_amount, 0) as discount_amount 
                FROM food_orders WHERE status IN ('completed', 'cancelled') $orderDateCond
            ) as combined
            ORDER BY created_at DESC
        ");

        $rows = $dataQuery->getResultArray();

        $filename = 'laporan_keuangan_maijek_' . ($hasDateFilter ? ($startDate . '_sd_' . $endDate) : 'semua_waktu') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // BOM for Excel compatibility (UTF-8)
        fputs($output, "\xEF\xBB\xBF");

        // Header Title
        fputcsv($output, ['LAPORAN KEUANGAN MAIJEK']);
        fputcsv($output, ['Periode', $hasDateFilter ? ($startDate . ' s/d ' . $endDate) : 'Semua Waktu']);
        fputcsv($output, ['Tanggal Unduh', date('d-m-Y H:i:s') . ' WITA']);
        fputcsv($output, []); // Baris kosong

        // Table Header
        fputcsv($output, [
            'No',
            'Waktu Transaksi',
            'ID Pesanan',
            'Layanan',
            'Status',
            'Tarif Resmi (GMV)',
            'Subsidi Diskon Promo',
            'Komisi Platform',
            'Laba Bersih Platform'
        ]);

        $sumGross = 0;
        $sumDiscount = 0;
        $sumCommission = 0;
        $sumNet = 0;
        $no = 1;

        foreach ($rows as $r) {
            $gross = (float)$r['gross'];
            $discount = (float)$r['discount_amount'];
            $feeCommission = 0;

            if ($r['type'] === 'Transport/Kurir') {
                $feeCommission = ($platformFeeType === 'percent') ? ($gross * ($platformFee / 100)) : $platformFee;
                $displayId = '#' . $r['id'];
            } else {
                $feeCommission = ($foodPlatformFeeType === 'percent') ? ($gross * ($foodPlatformFee / 100)) : $foodPlatformFee;
                $displayId = '#F' . $r['id'];
            }

            $net = $feeCommission - $discount;

            if ($r['status'] === 'completed') {
                $sumGross += $gross;
                $sumDiscount += $discount;
                $sumCommission += $feeCommission;
                $sumNet += $net;
            }

            fputcsv($output, [
                $no++,
                $r['created_at'],
                $displayId,
                $r['type'],
                $r['status'] === 'completed' ? 'Sukses' : ($r['status'] === 'cancelled' ? 'Batal' : $r['status']),
                $gross,
                $discount,
                $feeCommission,
                $net
            ]);
        }

        // Summary row at the bottom
        fputcsv($output, []);
        fputcsv($output, [
            'TOTAL SUKSES',
            '',
            '',
            '',
            '',
            $sumGross,
            $sumDiscount,
            $sumCommission,
            $sumNet
        ]);

        fclose($output);
        exit;
    }
}
