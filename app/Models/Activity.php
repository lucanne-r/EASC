<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $guarded = [];

    public const STATUSES = ['Open', 'In Progress', 'Hold', 'Closed', 'Delete'];
    public const PRIORITIES = ['P1', 'P2', 'P3'];
    public const CATEGORIES = ['Preparation', 'Mechanical', 'Electrical', 'Programming', 'Trial', 'Dokumen', 'Design'];
    public const MACHINES = [
        'Barcode Assy + Weight Check 1' => 'P1',
        'Camera Thrust + Weight Check 2' => 'P2',
        'Camera Bolt Through' => 'P3',
        'PRV' => 'P4',
    ];

    public function attachments()
    {
        return $this->hasMany(ActivityAttachment::class);
    }
}