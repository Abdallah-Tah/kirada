<?php

use App\Http\Controllers\BwaWhatsAppEventController;
use Illuminate\Support\Facades\Route;

Route::post('/internal/bwa/whatsapp/events', BwaWhatsAppEventController::class)
    ->middleware('throttle:kirada-webhooks')
    ->name('internal.bwa.whatsapp.events');
