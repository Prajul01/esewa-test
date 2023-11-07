<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class destinations_images extends Model
{
    use HasFactory;
    protected $table='destinations_images';
    protected $fillable=['image','destination_id'];

    public function destination()
    {
        return $this->belongsTo(destinations::class, 'destination_id');
    }
}
