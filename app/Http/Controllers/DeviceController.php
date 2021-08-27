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
        return view('welcome');
        //return Device::all();
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
            ->select('device_infos.deviceCode', 'device_infos.status', 'device_infos.volume', 'device_infos.is_playing','device_infos.relay1','device_infos.relay2')
            ->where('areas.title','like','Linh Đàm')
            ->get()
            ->map(function($device, $key){
                $device->schedule_list = Schedule::select('type', 'fileVoice as play','startDate','endDate', 'time as startTime', 'endTime', 'created_at as time_created')->where('startDate','<>',null)->where('deviceCode',$device->deviceCode)->get()->map(function($item, $key){
                    $type = ['none','file','streaming','fm','document','voice-record'
                    ];
                    $item->type = $type[(integer)$item->type];
                    return $item;
    
                });
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
            ->select('schedules.deviceCode','schedules.type','schedules.fileVoice as play','schedules.startDate','schedules.endDate','schedules.time as startTime','schedules.endTime','schedules.created_at as time_created')
            ->where('areas.title','like','Linh Đàm')
            ->where(function($q) use ($date){
                $q->where('schedules.startDate','like',$date)
                ->orwhere('schedules.startDate','like',implode('-',array_reverse(explode('-',$date))));
            })
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function playNow(Request $req)
    {
        $data = $req->all();

        if(!isset($data['deviceCode']) || !isset($data['url'])){
            return 'Not enough parameters!';
        }
        $devices = DB::table('devices')
            ->join('areas','devices.areaId','=','areas.id')
            ->where('areas.title','like','Linh Đàm')
            ->where('devices.deviceCode','like',$data['deviceCode'])
            ->get();
        if(count($devices) == 0)
            return 'Insufficient access!';

        return $this->playOnline($data['deviceCode'],$data['url']);
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

    /**
     * A call api to play now a program
     * 
     * @var type is integer to know type play, play file, stream, documents,...
     * @var deviceCode is string of devices array that are needed to stop play
     * @var songName is url of media play
     * @return curl_response
     */
    public function playOnline($deviceCode, $songName)
    {
        $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[';

        $dataRequest .= '{\"DeviceID\":\"' . trim($deviceCode) . '\",\"CommandSend\":\"{\\\\\"Data\\\\\":\\\\\"{\\\\\\\\\\\\\"PlayRepeatType\\\\\\\\\\\\\":1,\\\\\\\\\\\\\"PlayType\\\\\\\\\\\\\":1,\\\\\\\\\\\\\"SongName\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $songName . '\\\\\\\\\\\\\"}\\\\\",\\\\\"PacketType\\\\\":5}\"}';

        $dataRequest .= ']}"}';

        $this->stopPlay($deviceCode);

        return $this->curl_to_server($dataRequest);
    }
    /**
     * A call api to stop play of one or more devices
     * 
     * @var deviceCode is array of devices that are needed to stop play
     * @return curl_response
     */
    public function stopPlay($deviceCode)
    {

        $deviceCode = implode(",", array_map(function($value){
            return '{\"DeviceID\":\"' . $value . '\",\"CommandSend\":\"{\\\\\"Data\\\\\":\\\\\"Stop play music\\\\\\",\\\\\"PacketType\\\\\":7}\"}';
        },$deviceCode));

        $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":['.$deviceCode.']}"}';

        return $this->curl_to_server($dataRequest);
    }
    /**
     * A call api to send files from server to devices
     * 
     * @var devices is array of devices
     * @var file
     * @return curl_response
     */
    public function sendFileToDevice($devices, $file){

        $devices = array_map(function($device) use ($file){
            return '{\"DeviceID\":\"'.$device.'\",\"CommandSend\":\"{\\\"PacketType\\\":1,\\\"Data\\\":\\\"{\\\\\\\"URLlist\\\\\\\":[\\\\\\\"'.$file.'\\\\\\\"]}\\\"}\"}';
        },$devices);

        $devices = implode(',',$devices);

        $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":['.$devices.']}"}';

        //return $dataRequest;
        return $this->curl_to_server($dataRequest);
    }
    /**
     * A void to call api
     * 
     * @var dataRequest
     * @return curl_response
     */
    public function curl_to_server($dataRequest)
    {
        // if (env('APP_ENV') == 'local')
        //     dd($dataRequest);

        $request = base64_encode($dataRequest);

        $urlRequest = "http://103.130.213.161:906/" . $request;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $urlRequest,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response);
    }
}
