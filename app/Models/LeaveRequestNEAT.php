<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequestNEAT extends Model
{
    use HasFactory;

    public $connection = "sqlsrv2";
    public $table = "NEAT.dbo.LeaveRequests";
    
    public $timestamps = false;

    protected $fillable = [
        "EmpID", 
        "EmpName", 
        "SiteName", 
        "Shift", 
        "LeaveType", 
        "StartDate", 
        "EndDate", 
        "Reason", 
        "Status", 
        "CreatedAt"
    ];
}