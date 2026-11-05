<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('bookhub:remind-due', function () {
    $summary = app(\App\Services\DueReminderService::class)->run();
    $this->info('Thông báo mới: '.$summary['created'].', sách chuyển quá hạn: '.$summary['overdue']);
})->purpose('Gửi thông báo sắp đến hạn/quá hạn cho người mượn sách');

Schedule::command('bookhub:remind-due')->dailyAt('08:00')->withoutOverlapping();
