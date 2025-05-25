<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'image_url',
        'scheduled_time',
        'status',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function platforms()
    {
        return $this->belongsToMany(Platform::class, 'post_platforms')
            ->withPivot('platform_status')
            ->withTimestamps();
    }

    protected static function booted()
    {
        static::creating(function ($post) {

            if (auth()->check()) {
                $post->user_id = auth()->id();
            }
        });
    }
}
