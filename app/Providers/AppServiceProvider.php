<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

use App\Models\Document;
use App\Models\Approval;
use App\Models\User;
use App\Models\DocumentFile;

use App\Observers\DocumentFileObserver;
use App\Observers\DocumentObserver;
use App\Observers\ApprovalObserver;
use App\Observers\UserObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Document::observe(DocumentObserver::class);
Approval::observe(ApprovalObserver::class);
User::observe(UserObserver::class);
DocumentFile::observe(DocumentFileObserver::class);
Paginator::useBootstrapFive();
    }
}
