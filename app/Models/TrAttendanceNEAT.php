<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrAttendanceNEAT extends Model
{
    use HasFactory;

    public $connection = 'sqlsrv2';
    public $table = "TrAttendance";
    protected $primaryKey = "EmpID";

    public $timestamps = false;

    protected $fillable = ["SiteName", "EmpID", "Date", "CheckIn", "CheckOut", "Lattitude", "Longitude", "Shift"];
}
