<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterEmployeeNEAT extends Model
{
    public $connection = 'sqlsrv2';
    public $table = 'MasterLogin';
    protected $primaryKey = 'EmpID';
    public $timestamps = false;

    protected $fillable =[
        "EmpID", "Password", "Module"
    ];
}
