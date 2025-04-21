<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrAttendanceV2 extends Model
{
    public $connection = 'sqlsrv2';
    public $table = "NEAT.dbo.TrAttendanceV2";
    protected $primaryKey = 'BatchID';
    public $timestamps = false;
    
    protected $fillable = [
        'EmpID',
        'SiteName',
        'Date',
        'Time',
        'Type',
        'Lattitude',
        'Longitude',
        'Shift'
    ];
    
    // For database-specific date formatting
    protected $casts = [
        'Date' => 'date',
        'Time' => 'datetime:H:i:s',
        'Lattitude' => 'decimal:6',
        'Longitude' => 'decimal:6'
    ];
}