<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use App\Models\AbandonedCart;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('cart:send-reminders', function () {
    $threshold = now()->subHours(24);
    $carts = AbandonedCart::query()
        ->where('last_activity_at', '<=', $threshold)
        ->whereNull('reminder_sent_at')
        ->whereNotNull('email')
        ->limit(100)
        ->get();

    $sent = 0;
    foreach ($carts as $cart) {
        try {
            Mail::html(
                'You left items in your cart. Return to complete checkout.',
                fn ($message) => $message->to($cart->email)->subject('Complete your order')
            );
            $cart->update(['reminder_sent_at' => now()]);
            $sent++;
        } catch (\Throwable $exception) {
        }
    }

    $this->info('Reminders sent: ' . $sent);
})->purpose('Send abandoned cart reminder emails');
