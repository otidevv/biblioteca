<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Artisan;
use DB;

class ProgramadorController extends Controller
{
    //
    public function test_pgsql(Request $request)
    {
        try 
        {
            $result = DB::connection('pgsql')->select("SELECT 1");            
            return "CORRECTO";
        }
        catch (\Exception $e) {
            return "ERROR: ".$e;
        }
    }

    public function link()
    {
        File::link(
            storage_path('app/public'), public_path('storage')
        );
        return 'CORRECTO';
    }

    public function refresh()
    {
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('config:cache');
        return 'ACTUALIZADO';
    }
}