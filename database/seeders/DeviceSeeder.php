<?php
// database/seeders/DeviceSeeder.php

namespace Database\Seeders;

use App\Models\Device;
use Illuminate\Database\Seeder;

class DeviceSeeder extends Seeder
{
    public function run(): void
    {
        $total = 5000;
        $chunk = 500;
        $now   = now();

        // جهاز ثابت للتجربة اليدوية
        $fixed = 'TEDDY-A1B2C3D4E5F6';
        Device::firstOrCreate(['device_id' => $fixed], ['active' => true]);

        $ids = [$fixed => true];
        while (count($ids) < $total) {
            $ids['TEDDY-' . strtoupper(bin2hex(random_bytes(6)))] = true;
        }

        $rows = [];
        foreach (array_keys($ids) as $deviceId) {
            $rows[] = [
                'device_id'  => $deviceId,
                'active'     => random_int(1, 10) > 1,   // ~90% مفعّل
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, $chunk) as $batch) {
            Device::insertOrIgnore($batch);
        }
    }
}
