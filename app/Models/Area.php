<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Device;

class Area extends Model
{
    use HasFactory;

    public function Devices(){
        return $this->hasMany(Device::class,'areaId','id');
    }
}
