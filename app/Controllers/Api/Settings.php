<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\SettingModel;

class Settings extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $settingModel = new SettingModel();

        $settings = $settingModel->findAll();

        $data = [];
        foreach ($settings as $setting) {
            $data[$setting['setting_key']] = $setting['setting_value'];
        }

        return $this->respond([
            'status' => 200,
            'message' => 'Berhasil mengambil pengaturan sistem',
            'data' => [
                'base_fare' => isset($data['base_fare']) ? (int)$data['base_fare'] : 12000,
                'base_fare_distance' => isset($data['base_fare_distance']) ? (float)$data['base_fare_distance'] : 3.0,
                'per_km_fare' => isset($data['per_km_fare']) ? (int)$data['per_km_fare'] : 3000,

                // Mai-Ride Pricing (Mobil)
                'ride_mobil_base_fare' => isset($data['ride_mobil_base_fare']) ? (int)$data['ride_mobil_base_fare'] : 15000,
                'ride_mobil_base_fare_distance' => isset($data['ride_mobil_base_fare_distance']) ? (float)$data['ride_mobil_base_fare_distance'] : 3.0,
                'ride_mobil_per_km_fare' => isset($data['ride_mobil_per_km_fare']) ? (int)$data['ride_mobil_per_km_fare'] : 4000,

                'platform_fee' => isset($data['platform_fee']) ? (float)$data['platform_fee'] : 0,
                'food_base_fare' => isset($data['food_base_fare']) ? (int)$data['food_base_fare'] : 5000,
                'food_base_fare_distance' => isset($data['food_base_fare_distance']) ? (float)$data['food_base_fare_distance'] : 3.0,
                'food_per_km_fare' => isset($data['food_per_km_fare']) ? (int)$data['food_per_km_fare'] : 2000,
                'food_car_base_fare' => isset($data['food_car_base_fare']) ? (int)$data['food_car_base_fare'] : 12000,
                'food_car_base_fare_distance' => isset($data['food_car_base_fare_distance']) ? (float)$data['food_car_base_fare_distance'] : 3.0,
                'food_car_per_km_fare' => isset($data['food_car_per_km_fare']) ? (int)$data['food_car_per_km_fare'] : 4000,

                // Mai-Send Pricing
                'send_motor_base_fare' => isset($data['send_motor_base_fare']) ? (int)$data['send_motor_base_fare'] : 8000,
                'send_motor_per_km' => isset($data['send_motor_per_km']) ? (int)$data['send_motor_per_km'] : 2000,
                'send_mobil_base_fare' => isset($data['send_mobil_base_fare']) ? (int)$data['send_mobil_base_fare'] : 20000,
                'send_mobil_per_km' => isset($data['send_mobil_per_km']) ? (int)$data['send_mobil_per_km'] : 4000,

                // Mai-Titip Pricing
                'titip_motor_base_fare' => isset($data['titip_motor_base_fare']) ? (int)$data['titip_motor_base_fare'] : 10000,
                'titip_motor_per_km' => isset($data['titip_motor_per_km']) ? (int)$data['titip_motor_per_km'] : 2000,
                'titip_mobil_base_fare' => isset($data['titip_mobil_base_fare']) ? (int)$data['titip_mobil_base_fare'] : 25000,
                'titip_mobil_per_km' => isset($data['titip_mobil_per_km']) ? (int)$data['titip_mobil_per_km'] : 5000,

                'driver_help_center' => isset($data['driver_help_center']) ? $data['driver_help_center'] : 'Pusat Bantuan Maijek',
                'driver_about' => isset($data['driver_about']) ? $data['driver_about'] : 'Aplikasi Maijek Driver',
                'driver_zero_balance_enabled' => isset($data['driver_zero_balance_enabled']) ? (int)$data['driver_zero_balance_enabled'] : 0,
                'driver_min_balance' => isset($data['driver_min_balance']) ? (int)$data['driver_min_balance'] : 10000,
                'driver_promo_zero_balance_bypass' => isset($data['driver_promo_zero_balance_bypass']) ? (int)$data['driver_promo_zero_balance_bypass'] : 1,
                'driver_min_withdraw' => isset($data['driver_min_withdraw']) ? (int)$data['driver_min_withdraw'] : 50000,
            ]
        ], 200);
    }
}
