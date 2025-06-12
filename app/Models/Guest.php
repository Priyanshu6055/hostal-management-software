<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'gender',
        'scholar_no',
        'fathers_name',
        'mothers_name',
        'local_guardian_name',
        'emergency_no',
        'number',
        'parent_no',
        'guardian_no',
        'room_preference',
        'food_preference',
        'fee_waiver',
        'remarks',
        'status',
        'months',
        'attachment_path',
        'days'
    ];

    public function accessory()
    {
        return $this->belongsToMany(Accessory::class, 'guest_accessory', 'guest_id', 'accessory_id');
    }
    public function accessories()
    {
        return $this->belongsToMany(Accessory::class, 'guest_accessory', 'guest_id', 'accessory_head_id')
            ->withPivot(['price', 'total_amount', 'from_date', 'to_date'])
            ->with('accessoryHead');
    }

    // app/Models/Guest.php

    public function feeException()
    {
        return $this->hasOne(FeeException::class);
    }

    
}
