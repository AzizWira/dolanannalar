<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guardian extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];
}
