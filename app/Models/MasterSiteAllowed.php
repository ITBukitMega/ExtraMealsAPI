<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterSiteAllowed extends Model
{
    /** @use HasFactory<\Database\Factories\MasterSiteAllowedFactory> */
    use HasFactory;

    public $table = "MasterSite";
    protected $primaryKey = "SiteCode";

    public $incrementing = false;

    protected $fillable = ["SiteCode", "SiteName", "latitude", "longitude", "radius"];
}
