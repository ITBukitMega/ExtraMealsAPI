<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListMasterLoginNEAT extends Model
{
    //
    use HasFactory;
    public $connection = "sqlsrv2";
    public $table = "NEAT.dbo.ListMasterLogin";

    public $fillable = ["EmpID", "EmpName", "Password", "Module", "Used"];
}
