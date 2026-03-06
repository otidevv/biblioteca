<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CodigosSeeder extends Seeder
{
    public function run(): void
    {
        $json = File::get(database_path('data/codigos.json'));
        $data = json_decode($json, true);

        foreach (array_chunk($data, 1000) as $chunk) {
            DB::table('codido_cutters')->insert($chunk);
        }
    }
}