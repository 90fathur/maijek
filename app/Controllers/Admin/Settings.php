<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SettingModel;
use App\Models\AdminModel;

class Settings extends BaseController
{
    public function index()
    {
        $settingModel = new SettingModel();

        $data = [
            'title' => 'Pengaturan & Tarif | Maijek Admin',
            'base_fare' => $settingModel->where('setting_key', 'base_fare')->first(),
            'base_fare_distance' => $settingModel->where('setting_key', 'base_fare_distance')->first(),
            'per_km_fare' => $settingModel->where('setting_key', 'per_km_fare')->first(),

            // Tarif & Biaya (Mai-Ride Mobil)
            'ride_mobil_base_fare' => $settingModel->where('setting_key', 'ride_mobil_base_fare')->first(),
            'ride_mobil_base_fare_distance' => $settingModel->where('setting_key', 'ride_mobil_base_fare_distance')->first(),
            'ride_mobil_per_km_fare' => $settingModel->where('setting_key', 'ride_mobil_per_km_fare')->first(),

            'platform_fee' => $settingModel->where('setting_key', 'platform_fee')->first(),
            'platform_fee_type' => $settingModel->where('setting_key', 'platform_fee_type')->first(),
            'food_base_fare' => $settingModel->where('setting_key', 'food_base_fare')->first(),
            'food_base_fare_distance' => $settingModel->where('setting_key', 'food_base_fare_distance')->first(),
            'food_per_km_fare' => $settingModel->where('setting_key', 'food_per_km_fare')->first(),
            'food_car_base_fare' => $settingModel->where('setting_key', 'food_car_base_fare')->first(),
            'food_car_base_fare_distance' => $settingModel->where('setting_key', 'food_car_base_fare_distance')->first(),
            'food_car_per_km_fare' => $settingModel->where('setting_key', 'food_car_per_km_fare')->first(),
            'food_platform_fee' => $settingModel->where('setting_key', 'food_platform_fee')->first(),
            'food_platform_fee_type' => $settingModel->where('setting_key', 'food_platform_fee_type')->first(),
            'driver_help_center' => $settingModel->where('setting_key', 'driver_help_center')->first(),
            'driver_about' => $settingModel->where('setting_key', 'driver_about')->first(),

            // Mai-Send & Mai-Titip Settings
            'send_motor_base_fare' => $settingModel->where('setting_key', 'send_motor_base_fare')->first(),
            'send_motor_base_distance' => $settingModel->where('setting_key', 'send_motor_base_distance')->first(),
            'send_motor_per_km' => $settingModel->where('setting_key', 'send_motor_per_km')->first(),
            'send_mobil_base_fare' => $settingModel->where('setting_key', 'send_mobil_base_fare')->first(),
            'send_mobil_base_distance' => $settingModel->where('setting_key', 'send_mobil_base_distance')->first(),
            'send_mobil_per_km' => $settingModel->where('setting_key', 'send_mobil_per_km')->first(),
            'titip_motor_base_fare' => $settingModel->where('setting_key', 'titip_motor_base_fare')->first(),
            'titip_motor_base_distance' => $settingModel->where('setting_key', 'titip_motor_base_distance')->first(),
            'titip_motor_per_km' => $settingModel->where('setting_key', 'titip_motor_per_km')->first(),
            'titip_mobil_base_fare' => $settingModel->where('setting_key', 'titip_mobil_base_fare')->first(),
            'titip_mobil_base_distance' => $settingModel->where('setting_key', 'titip_mobil_base_distance')->first(),
            'titip_mobil_per_km' => $settingModel->where('setting_key', 'titip_mobil_per_km')->first(),

            // Kebijakan Saldo Minimal Driver
            'driver_zero_balance_enabled' => $settingModel->where('setting_key', 'driver_zero_balance_enabled')->first(),
            'driver_min_balance' => $settingModel->where('setting_key', 'driver_min_balance')->first(),
            'driver_promo_zero_balance_bypass' => $settingModel->where('setting_key', 'driver_promo_zero_balance_bypass')->first(),

            // Reward & Penalti Driver
            'driver_skip_penalty' => $settingModel->where('setting_key', 'driver_skip_penalty')->first(),
            'driver_reward_threshold' => $settingModel->where('setting_key', 'driver_reward_threshold')->first(),
            'driver_reward_amount' => $settingModel->where('setting_key', 'driver_reward_amount')->first(),

            // Cashback Koin
            'user_coin_threshold' => $settingModel->where('setting_key', 'user_coin_threshold')->first(),
            'user_coin_reward' => $settingModel->where('setting_key', 'user_coin_reward')->first(),
            'driver_coin_threshold' => $settingModel->where('setting_key', 'driver_coin_threshold')->first(),
            'driver_coin_reward' => $settingModel->where('setting_key', 'driver_coin_reward')->first(),

            // Promo Dinamis
            'promo_dynamic_status' => $settingModel->where('setting_key', 'promo_dynamic_status')->first(),
            'promo_dynamic_day' => $settingModel->where('setting_key', 'promo_dynamic_day')->first(),
            'promo_dynamic_type' => $settingModel->where('setting_key', 'promo_dynamic_type')->first(),
            'promo_dynamic_amount' => $settingModel->where('setting_key', 'promo_dynamic_amount')->first(),

            // API Keys (OTP Fallback: Cloudchat -> Bablast -> Whapify -> Fonnte)
            'cloudchat_api_key' => $settingModel->where('setting_key', 'cloudchat_api_key')->first(),
            'bablast_api_key' => $settingModel->where('setting_key', 'bablast_api_key')->first(),
            'whapify_api_secret' => $settingModel->where('setting_key', 'whapify_api_secret')->first(),
            'whapify_account_id' => $settingModel->where('setting_key', 'whapify_account_id')->first(),
            'fonnte_api_key' => $settingModel->where('setting_key', 'fonnte_api_key')->first(),

            // Radius Radar Pencarian Driver Dinamis (KM)
            'search_radius_ride'  => $settingModel->where('setting_key', 'search_radius_ride')->first(),
            'search_radius_send'  => $settingModel->where('setting_key', 'search_radius_send')->first(),
            'search_radius_titip' => $settingModel->where('setting_key', 'search_radius_titip')->first(),
            'search_radius_food'  => $settingModel->where('setting_key', 'search_radius_food')->first(),
            'search_radius_mart'  => $settingModel->where('setting_key', 'search_radius_mart')->first(),

            // Driver Prioritas (Priority Dispatch)
            'priority_dispatch_enabled' => $settingModel->where('setting_key', 'priority_dispatch_enabled')->first(),
            'priority_dispatch_timeout' => $settingModel->where('setting_key', 'priority_dispatch_timeout')->first(),
        ];

        return view('admin/settings/index', $data);
    }

    public function update()
    {
        $settingModel = new SettingModel();

        $settingModel->where('setting_key', 'base_fare')->set(['setting_value' => $this->request->getPost('base_fare')])->update();
        $settingModel->where('setting_key', 'base_fare_distance')->set(['setting_value' => $this->request->getPost('base_fare_distance')])->update();
        $settingModel->where('setting_key', 'per_km_fare')->set(['setting_value' => $this->request->getPost('per_km_fare')])->update();

        // Mai-Ride Mobil Update
        $this->saveOrUpdateSetting($settingModel, 'ride_mobil_base_fare', $this->request->getPost('ride_mobil_base_fare'));
        $this->saveOrUpdateSetting($settingModel, 'ride_mobil_base_fare_distance', $this->request->getPost('ride_mobil_base_fare_distance'));
        $this->saveOrUpdateSetting($settingModel, 'ride_mobil_per_km_fare', $this->request->getPost('ride_mobil_per_km_fare'));

        $settingModel->where('setting_key', 'platform_fee')->set(['setting_value' => $this->request->getPost('platform_fee')])->update();
        $settingModel->where('setting_key', 'platform_fee_type')->set(['setting_value' => $this->request->getPost('platform_fee_type')])->update();

        // Helper function for insert or update for Food settings
        $this->saveOrUpdateSetting($settingModel, 'food_base_fare', $this->request->getPost('food_base_fare'));
        $this->saveOrUpdateSetting($settingModel, 'food_base_fare_distance', $this->request->getPost('food_base_fare_distance'));
        $this->saveOrUpdateSetting($settingModel, 'food_per_km_fare', $this->request->getPost('food_per_km_fare'));
        $this->saveOrUpdateSetting($settingModel, 'food_car_base_fare', $this->request->getPost('food_car_base_fare'));
        $this->saveOrUpdateSetting($settingModel, 'food_car_base_fare_distance', $this->request->getPost('food_car_base_fare_distance'));
        $this->saveOrUpdateSetting($settingModel, 'food_car_per_km_fare', $this->request->getPost('food_car_per_km_fare'));
        $this->saveOrUpdateSetting($settingModel, 'food_platform_fee', $this->request->getPost('food_platform_fee'));
        $this->saveOrUpdateSetting($settingModel, 'food_platform_fee_type', $this->request->getPost('food_platform_fee_type'));

        // Helper function for insert or update
        $this->saveOrUpdateSetting($settingModel, 'driver_help_center', $this->request->getPost('driver_help_center'));
        $this->saveOrUpdateSetting($settingModel, 'driver_about', $this->request->getPost('driver_about'));

        // Mai-Send & Mai-Titip Update
        $this->saveOrUpdateSetting($settingModel, 'send_motor_base_fare', $this->request->getPost('send_motor_base_fare'));
        $this->saveOrUpdateSetting($settingModel, 'send_motor_base_distance', $this->request->getPost('send_motor_base_distance'));
        $this->saveOrUpdateSetting($settingModel, 'send_motor_per_km', $this->request->getPost('send_motor_per_km'));
        $this->saveOrUpdateSetting($settingModel, 'send_mobil_base_fare', $this->request->getPost('send_mobil_base_fare'));
        $this->saveOrUpdateSetting($settingModel, 'send_mobil_base_distance', $this->request->getPost('send_mobil_base_distance'));
        $this->saveOrUpdateSetting($settingModel, 'send_mobil_per_km', $this->request->getPost('send_mobil_per_km'));
        $this->saveOrUpdateSetting($settingModel, 'titip_motor_base_fare', $this->request->getPost('titip_motor_base_fare'));
        $this->saveOrUpdateSetting($settingModel, 'titip_motor_base_distance', $this->request->getPost('titip_motor_base_distance'));
        $this->saveOrUpdateSetting($settingModel, 'titip_motor_per_km', $this->request->getPost('titip_motor_per_km'));
        $this->saveOrUpdateSetting($settingModel, 'titip_mobil_base_fare', $this->request->getPost('titip_mobil_base_fare'));
        $this->saveOrUpdateSetting($settingModel, 'titip_mobil_base_distance', $this->request->getPost('titip_mobil_base_distance'));
        $this->saveOrUpdateSetting($settingModel, 'titip_mobil_per_km', $this->request->getPost('titip_mobil_per_km'));

        // Kebijakan Saldo Minimal Driver Update
        $this->saveOrUpdateSetting($settingModel, 'driver_zero_balance_enabled', $this->request->getPost('driver_zero_balance_enabled'));
        $this->saveOrUpdateSetting($settingModel, 'driver_min_balance', $this->request->getPost('driver_min_balance'));
        $this->saveOrUpdateSetting($settingModel, 'driver_promo_zero_balance_bypass', $this->request->getPost('driver_promo_zero_balance_bypass'));

        // Reward & Penalti Driver Update
        $this->saveOrUpdateSetting($settingModel, 'driver_skip_penalty', $this->request->getPost('driver_skip_penalty'));
        $this->saveOrUpdateSetting($settingModel, 'driver_reward_threshold', $this->request->getPost('driver_reward_threshold'));
        $this->saveOrUpdateSetting($settingModel, 'driver_reward_amount', $this->request->getPost('driver_reward_amount'));

        // Cashback Koin Update
        $this->saveOrUpdateSetting($settingModel, 'user_coin_threshold', $this->request->getPost('user_coin_threshold'));
        $this->saveOrUpdateSetting($settingModel, 'user_coin_reward', $this->request->getPost('user_coin_reward'));
        $this->saveOrUpdateSetting($settingModel, 'driver_coin_threshold', $this->request->getPost('driver_coin_threshold'));
        $this->saveOrUpdateSetting($settingModel, 'driver_coin_reward', $this->request->getPost('driver_coin_reward'));

        // Promo Dinamis Update
        $this->saveOrUpdateSetting($settingModel, 'promo_dynamic_status', $this->request->getPost('promo_dynamic_status'));
        $this->saveOrUpdateSetting($settingModel, 'promo_dynamic_day', $this->request->getPost('promo_dynamic_day'));
        $this->saveOrUpdateSetting($settingModel, 'promo_dynamic_type', $this->request->getPost('promo_dynamic_type'));
        $this->saveOrUpdateSetting($settingModel, 'promo_dynamic_amount', $this->request->getPost('promo_dynamic_amount'));

        // API Keys Update
        $this->saveOrUpdateSetting($settingModel, 'cloudchat_api_key', $this->request->getPost('cloudchat_api_key'));
        $this->saveOrUpdateSetting($settingModel, 'bablast_api_key', $this->request->getPost('bablast_api_key'));
        $this->saveOrUpdateSetting($settingModel, 'whapify_api_secret', $this->request->getPost('whapify_api_secret'));
        $this->saveOrUpdateSetting($settingModel, 'whapify_account_id', $this->request->getPost('whapify_account_id'));
        $this->saveOrUpdateSetting($settingModel, 'fonnte_api_key', $this->request->getPost('fonnte_api_key'));

        // Radius Radar Pencarian Driver Dinamis (KM) Update
        $this->saveOrUpdateSetting($settingModel, 'search_radius_ride', $this->request->getPost('search_radius_ride'));
        $this->saveOrUpdateSetting($settingModel, 'search_radius_send', $this->request->getPost('search_radius_send'));
        $this->saveOrUpdateSetting($settingModel, 'search_radius_titip', $this->request->getPost('search_radius_titip'));
        $this->saveOrUpdateSetting($settingModel, 'search_radius_food', $this->request->getPost('search_radius_food'));
        $this->saveOrUpdateSetting($settingModel, 'search_radius_mart', $this->request->getPost('search_radius_mart'));

        // Driver Prioritas (Priority Dispatch) Update
        $this->saveOrUpdateSetting($settingModel, 'priority_dispatch_enabled', $this->request->getPost('priority_dispatch_enabled') ?? '1');
        $this->saveOrUpdateSetting($settingModel, 'priority_dispatch_timeout', $this->request->getPost('priority_dispatch_timeout') ?? '10');

        $new_password = $this->request->getPost('new_password');
        if (!empty($new_password)) {
            $adminModel = new AdminModel();
            $adminModel->update(session()->get('admin_id'), [
                'password' => password_hash($new_password, PASSWORD_BCRYPT)
            ]);
        }

        session()->setFlashdata('success', 'Pengaturan berhasil disimpan!');
        return redirect()->to('/admin/settings');
    }

    private function saveOrUpdateSetting($model, $key, $value)
    {
        if ($value === null) return;

        $existing = $model->where('setting_key', $key)->first();
        if ($existing) {
            $model->update($existing['id'], ['setting_value' => $value]);
        } else {
            $model->insert([
                'setting_key' => $key,
                'setting_value' => $value,
                'description' => 'Dynamic settings for ' . $key
            ]);
        }
    }
}
