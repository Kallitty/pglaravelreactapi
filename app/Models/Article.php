<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'author',
        'main_image_url',
        'additional_image_urls'
    ];

    protected $casts = [
        'additional_image_urls' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($article) {
            $article->slug = Str::slug($article->title);
            
            // Ensure slug is unique
            $originalSlug = $article->slug;
            $count = 1;
            while (static::where('slug', $article->slug)->exists()) {
                $article->slug = $originalSlug . '-' . $count++;
            }
        });

        static::updating(function ($article) {
            if ($article->isDirty('title')) {
                $article->slug = Str::slug($article->title);
                
                // Ensure slug is unique
                $originalSlug = $article->slug;
                $count = 1;
                while (static::where('slug', $article->slug)
                       ->where('id', '!=', $article->id)
                       ->exists()) {
                    $article->slug = $originalSlug . '-' . $count++;
                }
            }
        });
    }
}