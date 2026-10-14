<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\OrderModel;

class Orders extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $orderModel = new OrderModel();
        // Return all orders (In a real app, filter by the logged-in user id from JWT)
        $orders = $orderModel->findAll();
        return $this->respond($orders);
    }

    public function create()
    {
        $rules = [
            'user_id' => 'required|numeric',
            'pickup_address' => 'required',
            'dropoff_address' => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $orderModel = new OrderModel();
        $data = [
            'user_id'         => $this->request->getVar('user_id'),
            'pickup_address'  => $this->request->getVar('pickup_address'),
            'pickup_lat'      => $this->request->getVar('pickup_lat'),
            'pickup_lng'      => $this->request->getVar('pickup_lng'),
            'dropoff_address' => $this->request->getVar('dropoff_address'),
            'dropoff_lat'     => $this->request->getVar('dropoff_lat'),
            'dropoff_lng'     => $this->request->getVar('dropoff_lng'),
            'distance'        => $this->request->getVar('distance') ?? 0,
            'price'           => $this->request->getVar('price') ?? 0,
            'status'          => 'pending'
        ];

        $orderModel->insert($data);
        return $this->respondCreated(['message' => 'Order created successfully', 'order_id' => $orderModel->getInsertID()]);
    }

    public function track($id)
    {
        $orderModel = new OrderModel();

        // Gabungkan tabel orders dengan drivers untuk mendapat info driver dan lokasinya
        $order = $orderModel->select('orders.*, drivers.name as driver_name, drivers.vehicle_plate, drivers.phone as driver_phone, drivers.current_lat, drivers.current_lng')
            ->join('drivers', 'drivers.id = orders.driver_id', 'left')
            ->find($id);

        if (!$order) {
            return $this->respond(['status' => 404, 'message' => 'Pesanan tidak ditemukan'], 404);
        }

        return $this->respond(['status' => 200, 'message' => 'Detail pesanan', 'data' => $order]);
    }
}
