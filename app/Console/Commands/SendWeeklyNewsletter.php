<?php

namespace App\Console\Commands;

use App\Models\DiscoveryCollection;
use App\Models\Subscriber;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendWeeklyNewsletter extends Command
{
    /**
     * The name and signature of the console command.
     * Usage: php artisan newsletter:send
     */
    protected $signature = 'newsletter:send';

    /**
     * The console command description.
     */
    protected $description = 'Sends the latest curated collection guide to all active subscribers via email.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info("📧 Initializing weekly newsletter engine...");

        // 1. Fetch the latest active collection
        // This will be the one you most recently generated via command
        $collection = DiscoveryCollection::where('is_active', true)
            ->latest()
            ->first();

        if (!$collection) {
            $this->error("❌ No active collections found to send.");
            return 1;
        }

        $this->info("📚 Preparing Guide: '{$collection->title}'");

        // 2. Load the businesses within that collection
        // We eager load 'county' and 'media' so the email template has everything it needs instantly
        $businesses = $collection->businesses()
            ->with(['county', 'media'])
            ->get();

        if ($businesses->isEmpty()) {
            $this->warn("⚠️ This collection has no businesses attached. Skipping mail.");
            return 0;
        }

        // 3. Fetch active subscribers in chunks
        // This prevents the server from running out of memory if you have 10,000+ subscribers
        $subscribers = Subscriber::where('is_active', true);
        $totalSubscribers = $subscribers->count();

        if ($totalSubscribers === 0) {
            $this->info("⏭️ No active subscribers found. Work on your growth!");
            return 0;
        }

        $this->info("📨 Sending to {$totalSubscribers} explorers...");
        $bar = $this->output->createProgressBar($totalSubscribers);
        $bar->start();

        $subscribers->chunk(100, function ($batch) use ($collection, $businesses, $bar) {
            foreach ($batch as $subscriber) {
                try {
                    // Send the email using the template we created
                    Mail::send('emails.weekly-guide', [
                        'collection' => $collection, 
                        'businesses' => $businesses,
                        'subscriber' => $subscriber
                    ], function ($message) use ($subscriber, $collection) {
                        $message->to($subscriber->email)
                                ->subject("🌍 Weekly Discovery: " . $collection->title);
                    });
                } catch (\Exception $e) {
                    Log::error("Failed to send newsletter to {$subscriber->email}: " . $e->getMessage());
                }
                
                $bar->advance();
            }
        });

        $bar->finish();
        $this->info("\n\n✅ Newsletter successfully dispatched!");

        return 0;
    }
}