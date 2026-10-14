<?php

namespace App\Controllers\Api\User;

use CodeIgniter\RESTful\ResourceController;
use App\Models\PromoModel;

class Promo extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $promoModel = new PromoModel();

        // Hanya ambil promo yang aktif dan belum expired
        $promos = $promoModel->where('status', 'active')
            ->where('valid_until >=', date('Y-m-d H:i:s'))
            ->findAll();

        return $this->respond([
            'status' => 200,
            'message' => 'Berhasil mengambil daftar promo',
            'data' => $promos
        ], 200);
    }

    public function validatePromo()
    {
        $rules = [
            'code' => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->respond([
                'status' => 400,
                'message' => 'Kode promo wajib diisi',
                'data' => $this->validator->getErrors()
            ], 400);
        }

        $code = strtoupper($this->request->getVar('code'));
        $promoModel = new PromoModel();

        $promo = $promoModel->where('code', $code)->first();

        if (!$promo) {
            return $this->respond([
                'status' => 404,
                'message' => 'Kode promo tidak ditemukan',
                'data' => null
            ], 404);
        }

        if ($promo['status'] !== 'active') {
            return $this->respond([
                'status' => 400,
                'message' => 'Kode promo sudah tidak aktif',
                'data' => null
            ], 400);
        }

        if (strtotime($promo['valid_until']) < time()) {
            return $this->respond([
                'status' => 400,
                'message' => 'Kode promo sudah kedaluwarsa',
                'data' => null
            ], 400);
        }

        return $this->respond([
            'status' => 200,
            'message' => 'Promo berhasil divalidasi',
            'data' => $promo
        ], 200);
    }

    public function dynamic()
    {
        $settingModel = new \App\Models\SettingModel();

        $status = $settingModel->where('setting_key', 'promo_dynamic_status')->first();
        $day = $settingModel->where('setting_key', 'promo_dynamic_day')->first();
        $type = $settingModel->where('setting_key', 'promo_dynamic_type')->first();
        $amount = $settingModel->where('setting_key', 'promo_dynamic_amount')->first();

        $statusVal = $status ? $status['setting_value'] : 'inactive';
        $dayVal = $day ? $day['setting_value'] : '';

        $currentDay = date('l'); // 'Monday', 'Tuesday', dll

        if ($statusVal === 'active' && strtolower($dayVal) === strtolower($currentDay)) {
            return $this->respond([
                'status' => 200,
                'message' => 'Dynamic promo active today',
                'data' => [
                    'active' => true,
                    'type' => $type ? $type['setting_value'] : 'fixed',
                    'amount' => $amount ? (float) $amount['setting_value'] : 0,
                    'day' => $dayVal
                ]
            ]);
        }

        // Cek promo otomatis (is_automatic = 1) di tabel promos
        $promoModel = new PromoModel();
        $autoPromo = $promoModel->where('status', 'active')
            ->where('is_automatic', 1)
            ->groupStart()
                ->where('valid_until >=', date('Y-m-d H:i:s'))
                ->orWhere('valid_until', null)
            ->groupEnd()
            ->first();

        if ($autoPromo) {
            return $this->respond([
                'status' => 200,
                'message' => 'Automatic promo active',
                'data' => [
                    'active' => true,
                    'code' => $autoPromo['code'],
                    'type' => $autoPromo['discount_type'],
                    'amount' => (float)$autoPromo['discount_amount'],
                    'day' => 'All'
                ]
            ]);
        }

        return $this->respond([
            'status' => 200,
            'message' => 'No active dynamic promo for today',
            'data' => [
                'active' => false
            ]
        ]);
    }
}
