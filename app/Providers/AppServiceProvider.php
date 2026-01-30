<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\Paginator; // 1. ADD THIS IMPORT
use App\Models\County;
use App\Models\Category;
use App\Models\DiscoveryCollection;
use App\Models\Post;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // 2. FIXED: Image Optimizer logic belongs in register()
        /*
        $this->app->bind(OptimizerChain::class, function () {
            return (new OptimizerChain())->addOptimizer(new Intervention([
                'quality' => 85,
            ]));
        });
        */
    }

    /**
     * Bootstrap any application services.
     */
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useTailwind();
    }
}