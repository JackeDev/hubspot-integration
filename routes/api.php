<?php

use App\Http\Controllers\AssociationController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\Webhook\ContactController as WebhookContactController;
use App\Http\Middleware\LogWebhookRequest;
use Illuminate\Support\Facades\Route;

Route::post("/contacts", [ContactController::class, "store"])->name('contacts.create');
Route::post("/deals", [DealController::class, "store"])->name('deals.create');
Route::post("/associations", [AssociationController::class, "store"])->name('associations.create');
Route::post("/webhooks/contact-updated", [WebhookContactController::class, "update"])->name('webhooks.contacts.update')->middleware(LogWebhookRequest::class);