<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    // Nama di kolom L sampai P Excel. Ubah di sini kalau ada yang berganti.
    public const APPROVERS = ['VINDA', 'ADITHYA', 'SUCANDRA', 'MULYANA', 'UEHIGASHI'];

    protected $casts = [
        'approvals' => 'array',
        'po_issued' => 'boolean',
        'is_closed' => 'boolean',
    ];

    public function rfi()
    {
        return $this->belongsTo(Rfi::class);
    }
}