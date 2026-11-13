<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeightStandard extends Model
{
    protected $guarded = [];

    // Sama seperti rumus di Excel: Upper = nominal + 40, Lower = nominal - 40
    public function getUpperAttribute(): int
    {
        return $this->nominal + $this->tolerance;
    }

    public function getLowerAttribute(): int
    {
        return $this->nominal - $this->tolerance;
    }
}