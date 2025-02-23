<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_products')
                    ->withPivot('quantity', 'price');
    }
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}