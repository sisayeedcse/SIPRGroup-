<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('proposals:finalize', function () {
    $count = app(\App\Services\ProposalGovernanceService::class)->finalizeEligible();
    $this->info("Finalized {$count} proposal(s).");
})->purpose('Finalize proposals that reached deadline or quorum');

Schedule::command('proposals:finalize')->hourly();
