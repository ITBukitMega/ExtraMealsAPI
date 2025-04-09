<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SickLeaveRequestNEAT extends Model
{
    use HasFactory;

    public $connection = "sqlsrv2";
    public $table = "NEAT.dbo.TrSickLeaves";
    
    public $timestamps = false;

    protected $fillable = [
        'EmpID',
        'EmpName',
        'SiteName',
        'Shift',
        'SicknessType',
        'StartDate',
        'EndDate',
        'StartTime',
        'EndTime',
        'Condition',
        'Status',
        'CreatedAt'
    ];
}