<?php

namespace App\Providers;

use App\Enums\Role;
use App\Models\Announcement;
use App\Models\Document;
use App\Models\Investment;
use App\Models\Proposal;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Policies\AnnouncementPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\InvestmentPolicy;
use App\Policies\MemberPolicy;
use App\Policies\ProposalPolicy;
use App\Policies\TransactionPolicy;
use App\Policies\WalletPolicy;
use App\Support\RoleAccess;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Gate::policy(Transaction::class, TransactionPolicy::class);
        Gate::policy(User::class, MemberPolicy::class);
        Gate::policy(Investment::class, InvestmentPolicy::class);
        Gate::policy(Announcement::class, AnnouncementPolicy::class);
        Gate::policy(Proposal::class, ProposalPolicy::class);
        Gate::policy(Document::class, DocumentPolicy::class);
        Gate::policy(Wallet::class, WalletPolicy::class);

        Gate::define('viewReports', function (User $user): bool {
            return RoleAccess::allows($user->role->value ?? $user->role, 'reports');
        });

        Gate::define('viewActivityLog', function (User $user): bool {
            return RoleAccess::allows($user->role->value ?? $user->role, 'activities');
        });
    }
}
