<?php

namespace App\Jobs;

use App\AudioProcessor;
use App\User,DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPodcast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Create a new job instance.
     *
     * @param  Podcast  $podcast
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @param  AudioProcessor  $processor
     * @return void
     */
    public function handle()
    {
        \Log::info("job dispatched");
        /* $subscribers = DB::table('users')->get();

        foreach ($subscribers as $subscriber)
        {
            \Mail::send('emails.blog', ['post' => $this->user, 'subscriber' => $subscriber], function ($m) use($subscriber) {
                $m->to('testing.testuser6@gmail.com', $subscriber->first_name);
                $m->subject('A new article has been published.');
            });
        } */
    }
}