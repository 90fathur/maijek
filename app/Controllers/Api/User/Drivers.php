<?php

namespace App\Controllers\Api\User;

use CodeIgniter\RESTful\ResourceController;
use App\Models\DriverModel;

class Drivers extends ResourceController
{
    public function nearby()
    {
        $lat = (float) $this->request->getVar('lat');
        $lng = (float) $this->request->getVar('lng');

        $vehicleType = strtolower((string) ($this->request->getVar('vehicle_type') ?? $this->request->getVar('type') ?? ''));
        $isCarRequested = in_array($vehicleType, ['mobil', 'car', 'car_premium', 'mai_send_mobil', 'mai_titip_mobil']);
        $isMotorRequested = in_array($vehicleType, ['motor', 'ride', 'mai_send_motor', 'mai_titip_motor']);

        if (empty($lat) || empty($lng)) {
            return $this->respond(['status' => 400, 'message' => 'Latitude dan Longitude diperlukan'], 400);
        }

        $db = \Config\Database::connect();
        $builder = $db->table('drivers');
        $builder->where('is_online', 1);
        $builder->where('verification_status', 'approved');

        // Pilih driver yang koordinatnya tidak null dan bukan 0
        $builder->where('latitude IS NOT NULL');
        $builder->where('longitude IS NOT NULL');
        $builder->where('latitude !=', 0);
        $builder->where('longitude !=', 0);

        if ($isCarRequested) {
            $builder->groupStart()
                ->where('vehicle_type', 'mobil')
                ->orWhere('vehicle_type', 'car')
                ->orWhere('vehicle_type', 'car_premium')
            ->groupEnd();
        } else if ($isMotorRequested) {
            $builder->where('vehicle_type', 'motor');
        }

        // Radius filter dinamis (berdasarkan search_radius_ride atau minimal 5 KM)
        $radiusSetting = $db->table('settings')->where('setting_key', 'search_radius_ride')->get()->getRow();
        $radius = $radiusSetting ? max(5.0, (float)$radiusSetting->setting_value) : 10;
        $builder->select('id, name, vehicle_type, latitude, longitude, ( 6371 * acos( LEAST(1.0, cos( radians(' . $db->escape($lat) . ') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $db->escape($lng) . ') ) + sin( radians(' . $db->escape($lat) . ') ) * sin( radians( latitude ) ) ) ) ) AS distance');
        $builder->having('distance <', $radius);
        $builder->orderBy('distance', 'ASC');
        $builder->limit(10); // Batasi maksimal 10 kendaraan di peta agar tidak berat

        $nearbyDrivers = $builder->get()->getResult();

        // SIMULASI GHOST DRIVERS jika tidak ada driver asli yang online
        if (count($nearbyDrivers) == 0) {
            $nearbyDrivers = [];
            $t = time();

            // Buat 6 simulasi kendaraan
            for ($i = 0; $i < 6; $i++) {
                $isCar = $isCarRequested ? true : ($isMotorRequested ? false : ($i % 2 !== 0));
                // Sudut berubah perlahan (mengitari user)
                $angle = ($i * M_PI / 3) + ($t / 100);
                // Radius mengecil dan membesar perlahan agar tidak berbentuk lingkaran kaku
                $radius = 0.005 + (sin(($t / 50) + $i) * 0.002);

                $currentLat = $lat + ($radius * cos($angle));
                $currentLng = $lng + ($radius * sin($angle));

                $nearbyDrivers[] = [
                    'id' => 'ghost_' . $i,
                    'name' => ($isCar ? 'Driver MaiCar ' : 'Driver MaiRide ') . ($i + 1),
                    'vehicle_type' => $isCar ? 'mobil' : 'motor',
                    'latitude' => $currentLat,
                    'longitude' => $currentLng,
                    'is_ghost' => true
                ];
            }
        }

        return $this->respond([
            'status' => 200,
            'message' => 'Nearby drivers retrieved',
            'data' => $nearbyDrivers
        ]);
    }
}
