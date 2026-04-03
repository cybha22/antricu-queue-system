<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Counter extends Model
{
    protected $fillable = [
        'name',
        'service_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function queues()
    {
        return $this->hasMany(Queue::class);
    }
}
