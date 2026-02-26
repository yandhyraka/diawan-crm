<?php

namespace Database\Seeders;

use App\Models\DiawanInputSource;

use Illuminate\Database\Seeder;

class InputSourceSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $datas[] = array('SALES_APP');

        DiawanInputSource::truncate();

        foreach ($datas as $i => $data) {
            DiawanInputSource::create([
                'input_source_name' => $data[0]
            ]);
        }
    }
}