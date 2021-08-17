<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Schedule;
use App\Models\DeviceInfo;
use Illuminate\Support\Facades\DB;

class DeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Device::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showByArea($name)
    {
        if($name == 'linh-dam'){
            $devices = DB::table('devices')
            ->join('device_infos','devices.deviceCode','=','device_infos.deviceCode')
            ->join('areas','devices.areaId','=','areas.id')
            ->select('device_infos.updated_at as last_update', 'device_infos.deviceCode', 'device_infos.status', 'device_infos.volume', 'device_infos.is_playing','device_infos.relay1','device_infos.relay2')
            ->where('areas.title','like','Linh Đàm')
            ->get()
            ->map(function($device, $key){
                $device->schedule_list = Schedule::select('fileVoice as file','startDate as date', 'time')->where('deviceCode',$device->deviceCode)->get();
                return $device;
            });
            
            // $devices = $devices->map(function($device, $key){
            //     $device->schedule_list = Schedule::select('fileVoice as file','startDate as date', 'time')->where('deviceCode',$device->deviceCode)->get();
            //     return $device;
            // });

            return $devices;
        }
        
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showByDate($name, $date)
    {
        if($name == 'linh-dam'){
            $devices = DB::table('devices')
            ->join('schedules','devices.deviceCode','=','schedules.deviceCode')
            ->join('areas','devices.areaId','=','areas.id')
            ->select('schedules.deviceCode','schedules.type','schedules.fileVoice as play','schedules.startDate','schedules.endDate','schedules.time','schedules.endTime','schedules.created_at as time_created')
            ->where('areas.title','like','Linh Đàm')
            ->where('schedules.startDate','like',$date)
            ->get()->map(function($device, $key){
                $type = ['none','file','streaming','fm','document','voice-record'
                ];
                $device->type = $type[(integer)$device->type];
                return $device;

            });
            
            
            // $devices = $devices->map(function($device, $key){
            //     $device->schedule_list = Schedule::select('fileVoice as file','startDate as date', 'time')->where('deviceCode',$device->deviceCode)->get();
            //     return $device;
            // });

            return $devices;
        }
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}