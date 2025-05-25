<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

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

                $count = self::where('user_id', $post->user_id)
                    ->whereDate('scheduled_time', Carbon::parse($post->scheduled_time)->toDateString())
                    ->count();

                if ($count >= 10) {
                    throw ValidationException::withMessages([
                        'scheduled_time' => 'You can only schedule up to 10 posts per day.',
                    ]);
                }
            }
        });
    }
}