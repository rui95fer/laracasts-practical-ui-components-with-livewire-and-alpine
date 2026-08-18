<?php

use Livewire\Livewire;

test('the toast notifications episode route renders', function () {
    // Arrange
    $url = route('episodes.toast-notifications');

    // Act
    $response = $this->get($url);

    // Assert
    $response
        ->assertSuccessful()
        ->assertSeeLivewire('pages::episodes.toast-notifications');
});

test('saving the display name dispatches a success toast', function () {
    // Arrange
    $component = Livewire::test('pages::episodes.toast-notifications');

    // Act
    $component
        ->set('displayName', 'Morgan')
        ->call('save');

    // Assert
    $component->assertDispatched('toast', message: 'Display name updated', type: 'success');
});

test('the toast demo dispatches each semantic notification type', function (string $type, string $message) {
    // Arrange
    $component = Livewire::test('pages::episodes.toast-notifications');

    // Act
    $component->call('showToast', $type);

    // Assert
    $component->assertDispatched('toast', message: $message, type: $type);
})->with([
    'success' => ['success', 'Everything worked as expected.'],
    'warning' => ['warning', 'This action needs your attention.'],
    'info' => ['info', 'Here is a little more context.'],
    'error' => ['error', 'Something went wrong. Try again.'],
]);

test('invalid display names do not dispatch a toast', function (string $displayName, string $rule) {
    // Arrange
    $component = Livewire::test('pages::episodes.toast-notifications');

    // Act
    $component
        ->set('displayName', $displayName)
        ->call('save');

    // Assert
    $component
        ->assertHasErrors(['displayName' => [$rule]])
        ->assertNotDispatched('toast');
})->with([
    'required' => ['', 'required'],
    'max length' => [str_repeat('x', 81), 'max:80'],
]);

test('unsupported notification types are ignored', function () {
    // Arrange
    $component = Livewire::test('pages::episodes.toast-notifications');

    // Act
    $component->call('showToast', 'critical');

    // Assert
    $component->assertNotDispatched('toast');
});

test('toast markup includes the Alpine listener, animations, and color palettes', function () {
    // Arrange
    $url = route('episodes.toast-notifications');

    // Act
    $response = $this->get($url);

    // Assert
    $response
        ->assertSuccessful()
        ->assertSee('x-on:toast.window="add($event)"', false)
        ->assertSee('x-for="toast in toasts"', false)
        ->assertSee('animate-fade-in-down', false)
        ->assertSee('animate-fade-out-up', false)
        ->assertSee('border-emerald-500', false)
        ->assertSee('border-amber-500', false)
        ->assertSee('border-sky-500', false)
        ->assertSee('border-red-500', false);
});
