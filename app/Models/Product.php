<?php

namespace App\Models;

use App\Models\Category;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table='products';
    protected $fillable=[
        'category_id', 'meta_title','meta_keyword','meta_descrip','slug','name','description','brand','selling_price','original_price','qty','featured' ,'popular','status'
    ];
        public function images()
    {
        return $this->hasMany(ProductImage::class);
    }


    protected $with=['category'];
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

            public function product_images()
        {
            return $this->hasMany(ProductImage::class);
        }
        
        
}






