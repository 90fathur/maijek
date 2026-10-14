<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SettingModel;
use App\Models\DriverModel;

class DriverBalance extends BaseController
{
    public function index()
    {
        $settingModel = new SettingModel();
        $driverModel = new DriverModel();
        $db = \Config\Database::connect();

        // 1. Ambil pengaturan saldo driver
        $zeroBalance = $settingModel->where('setting_key', 'driver_zero_balance_enabled')->first();
        $minBalance = $settingModel->where('setting_key', 'driver_min_balance')->first();
        $promoBypass = $settingModel->where('setting_key', 'driver_promo_zero_balance_bypass')->first();
        $minWithdraw = $settingModel->where('setting_key', 'driver_min_withdraw')->first();

        $minBalanceVal = $minBalance ? (float)$minBalance['setting_value'] : 10000;

        // 2. Statistik Driver Real-time
        $totalDrivers = $driverModel->where('verification_status', 'approved')->countAllResults();
        $zeroBalanceDrivers = $driverModel->where('verification_status', 'approved')->where('balance <=', 0)->countAllResults();
        $lowBalanceDrivers = $driverModel->where('verification_status', 'approved')->where('balance >', 0)->where('balance <', $minBalanceVal)->countAllResults();
        $safeDrivers = $driverModel->where('verification_status', 'approved')->where('balance >=', $minBalanceVal)->countAllResults();

        $avgBalanceRow = $db->table('drivers')->where('verification_status', 'approved')->selectAvg('balance')->get()->getRow();
        $avgBalance = $avgBalanceRow ? round($avgBalanceRow->balance) : 0;

        $data = [
            'title' => 'Minimal Saldo Driver | Maijek Admin',
            'driver_zero_balance_enabled' => $zeroBalance,
            'driver_min_balance' => $minBalance,
            'driver_promo_zero_balance_bypass' => $promoBypass,
            'driver_min_withdraw' => $minWithdraw,
            'stats' => [
                'total_drivers' => $totalDrivers,
                'zero_balance_drivers' => $zeroBalanceDrivers,
                'low_balance_drivers' => $lowBalanceDrivers,
                'safe_drivers' => $safeDrivers,
                'avg_balance' => $avgBalance
            ]
        ];

        return view('admin/driver_balance/index', $data);
    }

    public function update()
    {
        $settingModel = new SettingModel();

        $this->saveOrUpdateSetting($settingModel, 'driver_zero_balance_enabled', $this->request->getPost('driver_zero_balance_enabled'));
        $this->saveOrUpdateSetting($settingModel, 'driver_min_balance', $this->request->getPost('driver_min_balance'));
        $this->saveOrUpdateSetting($settingModel, 'driver_promo_zero_balance_bypass', $this->request->getPost('driver_promo_zero_balance_bypass'));
        $this->saveOrUpdateSetting($settingModel, 'driver_min_withdraw', $this->request->getPost('driver_min_withdraw'));

        session()->setFlashdata('success', 'Kebijakan minimal saldo & penarikan driver berhasil disimpan!');
        return redirect()->to('/admin/driver-balance');
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
