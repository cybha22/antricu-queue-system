<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    protected $fillable = [
        'number',
        'service_id',
        'counter_id',
        'status',
        'called_at',
        'serving_at',
        'served_at',
        'cancelled_at',
        'date',
    ];

    protected function casts(): array
    {
        return [
            'called_at' => 'datetime',
            'serving_at' => 'datetime',
            'served_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'date' => 'date',
        ];
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function counter()
    {
        return $this->belongsTo(Counter::class);
    }
}
