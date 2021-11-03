<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Area;

class AreaController extends Controller
{
    public function index(){
        return Area::select('id','title')->get();
    }
    public function getDevices($id){
        return Area::find($id)->Devices;
    }
}

