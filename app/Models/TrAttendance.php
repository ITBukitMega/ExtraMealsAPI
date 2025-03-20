<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrAttendance extends Model
{
    /** @use HasFactory<\Database\Factories\TrAttendanceFactory> */
    use HasFactory;

    public $connection = "sqlsrv";
    public $table = "TrAttendance";
    protected $primaryKey = "EmpID";

    public $timestamps = false;

    protected $fillable = ["SiteName", "EmpID", "Date", "CheckIn", "CheckOut", "Lattitude", "Longitude", "Shift"];
}
