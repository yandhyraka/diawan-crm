<?php

namespace Database\Seeders;

use App\Models\DiawanLogType;

use Illuminate\Database\Seeder;

class LogTypeSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $datas[] = array('INSERT');
        $datas[] = array('UPDATE');
        $datas[] = array('DELETE');

        DiawanLogType::truncate();

        foreach ($datas as $i => $data) {
            DiawanLogType::create([
                'log_type_name' => $data[0]
            ]);
        }
    }
}