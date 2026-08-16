<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::meeting-editor')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', 'pages::meeting-editor')->name('dashboard');
});

require __DIR__.'/settings.php';
