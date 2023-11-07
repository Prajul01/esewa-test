<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\destinations_images;

class destinations extends Model
{
    use HasFactory;
    protected $table='destinations';
    protected $fillable=[
        'placename',
        'category_id',
        'address',
        'latitude',
        'longitude',
        'price',
        'description',
        'status',
        'topdestination',
        'ticket_quantity',
        'category_id'
    ];

    public function Categories()
    {
        return $this->belongsTo(categories::class, 'category_id');
    }
    public function Images()
    {
        return $this->hasMany(destinations_images::class,'destination_id');
    }
}
