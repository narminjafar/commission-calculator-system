<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Amount extends Model
{
    use HasFactory;

    protected $fillable = [
        'value',
        'currency',
    ];

    protected $casts = [
        'value' => 'float',
    ];

    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }
}
