<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterLogin extends Model
{
    public $connection = "sqlsrv";
    protected $table = "MasterLogin";

    protected $primaryKey = "EmpID";


    public $timestamps = false;

    protected $fillable = [
        "EmpID",
        "Password",
        "Create",
        "Edit",
        "View",
        "ManagerID",
        "Approval",
        "SiteName",
        "Module",
        "Used",
    ];
}
