<?php

namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MergeCategoriesCommand extends Command
{
    /**
     * Usage: php artisan categories:merge {target_id} {source_id}
     * Example: php artisan categories:merge 10 15 (Moves everything from 15 into 10)
     */
    protected $signature = 'categories:merge {target_id} {source_id}';
    protected $description = 'Merges a source category into a target category and deletes the source.';

    public function handle(): int
    {
        $targetId = $this->argument('target_id');
        $sourceId = $this->argument('source_id');

        $target = Category::find($targetId);
        $source = Category::find($sourceId);

        if (!$target || !$source) {
            $this->error("One or both categories not found.");
            return 1;
        }

        $this->warn("Merging [{$source->name}] into [{$target->name}]...");

        DB::transaction(function () use ($target, $source) {
            // 1. Get all businesses attached to the source category
            $businessIds = DB::table('business_category')
                ->where('category_id', $source->id)
                ->pluck('business_id');

            // 2. Attach them to the target category
            // We use 'syncWithoutDetaching' logic via DB to avoid duplicates
            foreach ($businessIds as $id) {
                $exists = DB::table('business_category')
                    ->where('business_id', $id)
                    ->where('category_id', $target->id)
                    ->exists();

                if (!$exists) {
                    DB::table('business_category')->insert([
                        'business_id' => $id,
                        'category_id' => $target->id
                    ]);
                }
            }

            // 3. Remove the associations from the source
            DB::table('business_category')->where('category_id', $source->id)->delete();

            // 4. Delete the source category
            $source->delete();
        });

        $this->info("✅ Successfully merged '{$source->name}' into '{$target->name}'.");
        return 0;
    }
}