<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::episodes')->name('home');

Route::livewire('episodes/getting-started', 'pages::episodes.getting-started')
    ->name('episodes.getting-started');

Route::livewire('episodes/inline-editing', 'pages::meeting-editor')
    ->name('episodes.inline-editing');

Route::livewire('episodes/toast-notifications', 'pages::episodes.toast-notifications')
    ->name('episodes.toast-notifications');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', 'pages::meeting-editor')->name('dashboard');
});

require __DIR__.'/settings.php';
