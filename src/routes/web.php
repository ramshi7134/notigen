<?php

use Illuminate\Support\Facades\Route;
use Notigen\Http\Controllers\NotigenController;

Route::group([
    'prefix' => config('notigen.route_prefix'),
    'middleware' => config('notigen.middleware', ['web', 'auth'])
], function () {
    Route::get('/', [NotigenController::class, 'index'])->name('notigen.index');
    Route::get('/create', [NotigenController::class, 'create'])->name('notigen.create');
    Route::post('/', [NotigenController::class, 'store'])->name('notigen.store');
    Route::get('/{template}', [NotigenController::class, 'show'])->name('notigen.show');
    Route::get('/{template}/edit', [NotigenController::class, 'edit'])->name('notigen.edit');
    Route::put('/{template}', [NotigenController::class, 'update'])->name('notigen.update');
    Route::delete('/{template}', [NotigenController::class, 'destroy'])->name('notigen.destroy');
});
