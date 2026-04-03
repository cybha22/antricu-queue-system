<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'name',
        'prefix',
        'total_digits',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function counters()
    {
        return $this->hasMany(Counter::class);
    }

    public function queues()
    {
        return $this->hasMany(Queue::class);
    }
}
