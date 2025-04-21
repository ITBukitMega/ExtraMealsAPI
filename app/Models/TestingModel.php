<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestingModel extends Model
{
    public $connection = "sqlsrv2";

    public $table = "NEAT.dbo.TrAttendanceV2";
    
    public $timestamps = false;

    protected $fillable = [
        "BatchID",
        "EmpID",
        "SiteName",
        "Date",
        "Time",
        "Type",
        "Lattitude",
        "Longitude",
        "Shift"
    ];
}
