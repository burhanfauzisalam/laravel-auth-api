<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mtoken extends Model
{
    protected $table = "mtoken";
    protected $fillable = ['duration'];
    
}