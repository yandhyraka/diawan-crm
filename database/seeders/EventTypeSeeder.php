<?php

namespace Database\Seeders;

use App\Models\DiawanEventType;

use Illuminate\Database\Seeder;

class EventTypeSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $datas[] = array('COMMERCIAL_PURCHASE');

        DiawanEventType::truncate();

        foreach ($datas as $i => $data) {
            DiawanEventType::create([
                'event_type_name' => $data[0]
            ]);
        }
    }
}