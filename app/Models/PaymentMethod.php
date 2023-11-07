<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;
    protected $table='paymentmethods';
    protected $fillable=
        ['id',
        'name',
        'image',
        'status',
        'client_secret',
        'server_secret',
        'success_url',
        'failure_url'
        ];
}

