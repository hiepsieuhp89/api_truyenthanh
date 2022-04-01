<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Area;
use App\Models\Schedule;

class AreaController extends Controller
{
    public function index(){
        return Area::select('id','title')->get();
    }
    public function getDevices($id){

        if(!Area::find($id)->api_status)
            return [];

        $devices = DB::table('devices')
            ->join('device_infos','devices.deviceCode','=','device_infos.deviceCode')
            ->join('areas','devices.areaId','=','areas.id')
            ->select('device_infos.deviceCode','devices.name','devices.areaId', 'device_infos.status', 'device_infos.volume', 'device_infos.is_playing','device_infos.relay1','device_infos.relay2')
            ->where('devices.areaId','=',$id)
            ->get()
            ->map(function($device){
                $device->schedule_list = Schedule::select('type', 'fileVoice as play','startDate','endDate', 'time as startTime', 'endTime', 'created_at as time_created')->where('startDate','<>',null)->where('deviceCode',$device->deviceCode)->get()->map(function($item){
                    $type = ['none','file','streaming','fm','document','voice-record'
                    ];
                    $item->type = $type[(integer)$item->type];
                    return $item;
                });
                return $device;
            });
        return $devices;
    }
}

