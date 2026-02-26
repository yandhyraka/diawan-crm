<?php

namespace Database\Seeders;

use App\Models\DiawanRelationType;

use Illuminate\Database\Seeder;

class RelationTypeSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $datas[] = array('HUSBAND-WIFE');
        $datas[] = array('PARENT-CHILD');
        $datas[] = array('BOSS-EMPLOYEE');

        DiawanRelationType::truncate();

        foreach ($datas as $i => $data) {
            DiawanRelationType::create([
                'relation_type_name' => $data[0]
            ]);
        }
    }
}