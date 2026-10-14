<?php

namespace App\Controllers\Api\User;

use CodeIgniter\RESTful\ResourceController;
use App\Models\OrderModel;
use Dompdf\Dompdf;
use Dompdf\Options;

class Receipt extends ResourceController
{
    public function download($orderId = null)
    {
        $payload = $_SERVER['jwt_payload'] ?? null;
        if (!$payload) {
            return $this->respond(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $orderModel = new OrderModel();

        // Ambil data order beserta relasi driver dan user
        $order = $orderModel->select('orders.*, drivers.name as driver_name, drivers.vehicle_plate, users.name as user_name')
            ->join('drivers', 'drivers.id = orders.driver_id', 'left')
            ->join('users', 'users.id = orders.user_id', 'left')
            ->where('orders.id', $orderId)
            ->where('orders.user_id', $payload['uid'])
            ->first();

        if (!$order) {
            return $this->respond(['status' => 404, 'message' => 'Pesanan tidak ditemukan'], 404);
        }

        if ($order['status'] !== 'completed') {
            return $this->respond(['status' => 400, 'message' => 'Hanya pesanan selesai yang dapat dicetak struknya'], 400);
        }

        // Siapkan HTML dari view
        $html = view('receipt_template', ['order' => $order]);

        // Inisialisasi Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);

        // Atur ukuran kertas
        $dompdf->setPaper('A5', 'portrait');

        // Render PDF
        $dompdf->render();

        // Download otomatis
        $filename = 'Struk_Maijek_#' . $order['id'] . '_' . date('Ymd', strtotime($order['created_at'])) . '.pdf';

        $dompdf->stream($filename, [
            "Attachment" => true
        ]);

        exit();
    }

    public function foodDownload($orderId = null)
    {
        $payload = $_SERVER['jwt_payload'] ?? null;
        if (!$payload) {
            return $this->respond(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $foodOrderModel = new \App\Models\FoodOrderModel();

        // Ambil data order beserta relasi driver dan user dan merchant
        $order = $foodOrderModel->select('food_orders.*, drivers.name as driver_name, drivers.vehicle_plate, users.name as user_name, merchants.name as merchant_name')
            ->join('drivers', 'drivers.id = food_orders.driver_id', 'left')
            ->join('users', 'users.id = food_orders.user_id', 'left')
            ->join('merchants', 'merchants.id = food_orders.merchant_id', 'left')
            ->where('food_orders.id', $orderId)
            ->where('food_orders.user_id', $payload['uid'])
            ->first();

        if (!$order) {
            return $this->respond(['status' => 404, 'message' => 'Pesanan tidak ditemukan'], 404);
        }

        if ($order['status'] !== 'completed') {
            return $this->respond(['status' => 400, 'message' => 'Hanya pesanan selesai yang dapat dicetak struknya'], 400);
        }

        // Siapkan HTML dari view
        $html = view('food_receipt_template', ['order' => $order]);

        // Inisialisasi Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);

        // Atur ukuran kertas
        $dompdf->setPaper('A5', 'portrait');

        // Render PDF
        $dompdf->render();

        // Download otomatis
        $filename = 'Struk_MaiFood_#' . $order['id'] . '_' . date('Ymd', strtotime($order['created_at'])) . '.pdf';

        $dompdf->stream($filename, [
            "Attachment" => true
        ]);

        exit();
    }
}
