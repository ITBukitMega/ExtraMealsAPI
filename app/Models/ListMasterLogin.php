<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListMasterLogin extends Model
{
    /** @use HasFactory<\Database\Factories\ListMasterLoginFactory> */
    use HasFactory;

    public $table = "ListMasterLogin";

    public $fillable = ["EmpID", "EmpName", "Password", "Module"];
}
