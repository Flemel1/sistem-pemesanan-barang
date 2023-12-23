<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MatanYadaev\EloquentSpatial\Objects\Point;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_name',
        'order_product_stock',
        'order_address',
        'order_payment_method',
        'order_location_maps',
        'customer_id',
        'order_status',
        'order_charge',
        'order_deliver_fee',
        'order_proof_payment',
        'location',
    ];

    protected $casts = [
        'order_date' => 'date:d-m-Y',
        'location' => Point::class,
    ];

    // protected $appends = [
    //     'location',
    // ];

    // public function getLocationAttribute(): array
    // {
    //     return [
    //         "lat" => 110.3800343098,
    //         "lng" => -7.7819877163002,
    //     ];
    // }

    // public function getLocationAttribute(): array
    // {
    //     return [
    //         "lat" => (float)$this->lat,
    //         "lng" => (float)$this->lon,
    //     ];
    // }

    // public function setLocationAttribute(?array $location): void
    // {
    //     if (is_array($location))
    //     {
    //         $this->attributes['lat'] = $location['lat'];
    //         $this->attributes['lon'] = $location['lng'];
    //         unset($this->attributes['location']);
    //     }
    // }

    // public static function getLatLngAttributes(): array
    // {
    //     return [
    //         'lat' => 'lat',
    //         'lng' => 'lon',
    //     ];
    // }

    public static function getComputedLocation(): string
    {
        return 'location';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
