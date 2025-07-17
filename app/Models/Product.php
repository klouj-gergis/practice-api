<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'sku',
        'image'
    ];

    public function isInStock(): bool
    {
        return $this->stock > 0;
    }

    public function getImageUrlAttribute(){
        return $this->Image ? asset('storage/'. $this->image) : null;
    }

    public function orders(){
        return $this->hasMany(Order::class);
    }
}
