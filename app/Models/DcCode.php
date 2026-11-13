<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DcCode extends Model
{
    protected $guarded = [];

    // Sesuai legenda warna di Excel
    public const STATUSES = [
        'Update',
        'Need to recheck',
        'Design change can be replace',
        'No production',
        'Use in schedule only',
    ];
}