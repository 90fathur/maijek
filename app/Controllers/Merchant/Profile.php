<?php

namespace App\Controllers\Merchant;

use App\Controllers\BaseController;
use App\Models\MerchantModel;

class Profile extends BaseController
{
    public function index()
    {
        $merchantId = session()->get('merchant_id');
        $merchantModel = new MerchantModel();
        $merchant = $merchantModel->find($merchantId);

        $isMart = ($merchant && ($merchant['merchant_type'] ?? '') === 'mart');
        if (!session()->get('merchant_type') && $merchant) {
            session()->set('merchant_type', $merchant['merchant_type'] ?? 'food');
        }

        return view('merchant/profile/index', [
            'title'    => $isMart ? 'Pengaturan Toko | MaiMart Panel' : 'Pengaturan Toko | MaiFood Panel',
            'merchant' => $merchant,
            'isMart'   => $isMart
        ]);
    }

    public function update()
    {
        $merchantId = session()->get('merchant_id');
        $merchantModel = new MerchantModel();

        $mode = $this->request->getPost('schedule_mode') ?? 'same';

        if ($mode === 'daily') {
            $dailyActive = $this->request->getPost('daily_active') ?? [];
            $dailyOpen   = $this->request->getPost('daily_open') ?? [];
            $dailyClose  = $this->request->getPost('daily_close') ?? [];

            $dayNames = [
                '1' => 'Senin',
                '2' => 'Selasa',
                '3' => 'Rabu',
                '4' => 'Kamis',
                '5' => 'Jumat',
                '6' => 'Sabtu',
                '7' => 'Minggu',
            ];

            $daysSchedule = [];
            $fallbackOpen = '08:00';
            $fallbackClose = '21:00';
            $foundFallback = false;

            foreach ($dayNames as $dNum => $dName) {
                $isOpen = !empty($dailyActive[$dNum]);
                $openT  = !empty($dailyOpen[$dNum]) ? $dailyOpen[$dNum] : '08:00';
                $closeT = !empty($dailyClose[$dNum]) ? $dailyClose[$dNum] : '21:00';

                $daysSchedule[(string)$dNum] = [
                    'name'    => $dName,
                    'is_open' => $isOpen,
                    'open'    => $openT,
                    'close'   => $closeT,
                ];

                if ($isOpen && !$foundFallback) {
                    $fallbackOpen  = $openT;
                    $fallbackClose = $closeT;
                    $foundFallback = true;
                }
            }

            $operatingDaysPayload = json_encode([
                'mode' => 'daily',
                'days' => $daysSchedule,
            ], JSON_UNESCAPED_UNICODE);

            $data = [
                'opening_time'   => $fallbackOpen,
                'closing_time'   => $fallbackClose,
                'operating_days' => $operatingDaysPayload,
                'address'        => $this->request->getPost('address'),
            ];
        } else {
            $days = $this->request->getPost('operating_days');
            if (is_array($days) && !empty($days)) {
                $operatingDaysStr = implode(',', $days);
            } elseif (!empty($days)) {
                $operatingDaysStr = (string)$days;
            } else {
                $operatingDaysStr = '1,2,3,4,5,6,7';
            }

            $data = [
                'opening_time'   => $this->request->getPost('opening_time') ?? '08:00',
                'closing_time'   => $this->request->getPost('closing_time') ?? '21:00',
                'operating_days' => $operatingDaysStr,
                'address'        => $this->request->getPost('address'),
            ];
        }

        $lat = $this->request->getPost('latitude');
        $lng = $this->request->getPost('longitude');
        if ($lat !== null && $lat !== '') {
            $data['latitude'] = (float)$lat;
        }
        if ($lng !== null && $lng !== '') {
            $data['longitude'] = (float)$lng;
        }

        $merchantModel->update($merchantId, $data);

        return redirect()->to('/merchant/profile')->with('success', 'Informasi toko, jam operasional, dan titik koordinat berhasil disimpan.');
    }

    public function updatePhoto()
    {
        $merchantId = session()->get('merchant_id');
        $merchantModel = new MerchantModel();

        $file = $this->request->getFile('photo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            if (!is_dir(FCPATH . 'uploads/merchants')) {
                mkdir(FCPATH . 'uploads/merchants', 0777, true);
            }
            $ext = $file->guessExtension() ?? $file->getExtension();
            if (empty($ext) || $ext === 'bin' || $ext === '') $ext = 'jpg';
            $newName = uniqid('merchant_') . '.' . $ext;
            $file->move(FCPATH . 'uploads/merchants', $newName);

            $merchantModel->update($merchantId, ['photo' => 'uploads/merchants/' . $newName]);
            session()->set('merchant_photo', 'uploads/merchants/' . $newName);

            return redirect()->to('/merchant/profile')->with('success', 'Foto profil toko berhasil diperbarui.');
        }

        return redirect()->to('/merchant/profile')->with('error', 'Gagal mengunggah foto.');
    }
}
