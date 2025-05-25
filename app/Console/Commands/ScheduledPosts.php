<?php

namespace App\Console\Commands;

use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ScheduledPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scheduled-posts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish scheduled posts when their time comes';

    /**
     * Execute the console command.
     */

    public function handle()
    {
        $posts = Post::where('status', 'scheduled')
            ->where('scheduled_time', '<=', Carbon::now())
            ->get();

        foreach ($posts as $post) {

            $post->status = 'published';
            $post->save();

            foreach ($post->platforms as $platform) {
                $post->platforms()->updateExistingPivot($platform->id, [
                    'platform_status' => 'published',
                ]);

                Log::info("Mock publishing to {$platform->name} for Post ID: {$post->id}");
            }
        }

        return 0;
    }
}
