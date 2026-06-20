<?php

use App\Http\Controllers\AssociationController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DealController;
use Illuminate\Support\Facades\Route;

Route::post("/contacts", [ContactController::class, "store"])->name('contacts.create');
Route::post("/deals", [DealController::class, "store"])->name('deals.create');
Route::post("/associations", [AssociationController::class, "store"])->name('associations.create');