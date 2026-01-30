<?php

namespace App\Providers; // Correct namespace for this file
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Business;
use App\Policies\BusinessPolicy;
use App\Models\Review;
use App\Policies\ReviewPolicy;
use App\Models\Category;        
use App\Policies\CategoryPolicy; 
use App\Models\Tag;             
use App\Policies\TagPolicy;      
use App\Models\Facility;        
use App\Policies\FacilityPolicy; 
use App\Models\User;            
use App\Policies\UserPolicy;     
use App\Models\County;          
use App\Policies\CountyPolicy;   
use App\Models\EventReview;
use App\Policies\EventReviewPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
         Business::class => BusinessPolicy::class,
        Review::class   => ReviewPolicy::class,
        Category::class => CategoryPolicy::class,   
        Tag::class      => TagPolicy::class,         
        Facility::class => FacilityPolicy::class,  
        User::class     => UserPolicy::class,      
        County::class   => CountyPolicy::class,
		Event::class => EventPolicy::class,
		EventReview::class => EventReviewPolicy::class,		
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies(); // This line registers the policies defined above

        // You can also define Gates here if needed
        // Gate::define('edit-settings', function (User $user) {
        //     return $user->isAdmin();
        // });
    }
}