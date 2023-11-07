<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookedTicket extends Model
{
    use HasFactory;
    protected $table='booked_tickets';
    protected $fillable=['user_id','destination_id','ticket_count','status'];

    public function Users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function Destination()
    {
        return $this->belongsTo(destinations::class, 'destination_id');
    }
}
