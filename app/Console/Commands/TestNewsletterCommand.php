<?php

namespace App\Console\Commands;

use App\Models\DiscoveryCollection;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestNewsletterCommand extends Command
{
    // Usage: php artisan newsletter:test yourname@email.com
    protected $signature = 'newsletter:test {email}';
    protected $description = 'Sends a test newsletter to a specific email address.';

    public function handle(): int
    {
        $email = $this->argument('email');
        
        // 1. Get the latest collection
        $collection = DiscoveryCollection::where('is_active', true)->latest()->first();
        
        if (!$collection) {
            $this->error("No active collections found to test with.");
            return 1;
        }

        // 2. Load top 3 businesses
        $businesses = $collection->businesses()->with(['county', 'media'])->take(3)->get();

        $this->info("Sending test: '{$collection->title}' to {$email}...");

        try {
            Mail::send('emails.weekly-guide', [
                'collection' => $collection,
                'businesses' => $businesses
            ], function($m) use ($email, $collection) {
                $m->to($email)->subject("TEST GUIDE: " . $collection->title);
            });

            $this->info("✅ Test email sent! Check your inbox.");
        } catch (\Exception $e) {
            $this->error("❌ Failed to send: " . $e->getMessage());
        }

        return 0;
    }
}