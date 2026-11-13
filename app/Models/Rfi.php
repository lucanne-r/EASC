<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rfi extends Model
{
    protected $guarded = [];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}