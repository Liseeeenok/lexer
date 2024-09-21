<?php

namespace App\Modules\Absolute\Model;

use Illuminate\Database\Eloquent\Model;

class AbsoluteProduct extends Model
{
    public $table = 'absolute_product';

    protected $fillable = [
        'name',
        'price',
        'category',
        'image',
    ];

    public static function saveProduct(array $product)
    {
        AbsoluteProduct::updateOrCreate(['name' => $product['name']], [
            'price' => $product['price'],
            'category' => $product['category'],
            'image' => $product['image'],
        ]);
    }
}
