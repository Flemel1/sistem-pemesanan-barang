<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\Point;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_deliver_fee',
        'location',
    ];

    protected $casts = [
        'location' => Point::class,
    ];

    public static function getComputedLocation(): string
    {
        return 'location';
    }
}
