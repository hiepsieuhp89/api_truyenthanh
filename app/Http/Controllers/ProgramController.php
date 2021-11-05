<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Area;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function play($deviceCode, $stringencode){

        $device = Device::where('deviceCode', $deviceCode)->first();
        if(!$device)
            return "device not found";

        $area = $device->areaId;
        //dd($area);

        if(!Area::find($area)->api_status)
            return false;

        // {\\\\\\\\\\\\\"PlayRepeatType\\\\\\\\\\\\\":1,\\\\\\\\\\\\\"PlayType\\\\\\\\\\\\\":2,\\\\\\\\\\\\\"SongName\\\\\\\\\\\\\":\\\\\\\\\\\\\"' . $songName . '\\\\\\\\\\\\\"}

        $dataRequest = '{"DataType":4,"Data":"{\"CommandItem_Ts\":[{\"DeviceID\":\"' . $deviceCode . '\",\"CommandSend\":\"{\\\\\"Data\\\\\":\\\\\"'.base64_decode($stringencode).'\\\\\",\\\\\"PacketType\\\\\":5}\"}]}"}';

        return $this->curl_to_server($dataRequest);

    }
    public function curl_to_server($dataRequest)
    {
        $dataRequest = base64_encode($dataRequest);
        //dd($dataRequest);
        $urlRequest = "http://103.130.213.161:906/" . $dataRequest;
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
        //dd($response);
        curl_close($curl);

        return json_decode($response);
    }
}
