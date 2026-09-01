<?php

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Livewire\Livewire;

test('visitors can view the notification center without signing in', function () {
    // Arrange
    User::factory()->create(['email' => 'test@example.com']);

    // Act
    $response = $this->get(route('episodes.notification-center'));

    // Assert
    $response
        ->assertSuccessful()
        ->assertSeeLivewire('pages::episodes.notification-center')
        ->assertSee('Notification center')
        ->assertSee('wire:poll.10s="checkForNew"', false)
        ->assertSee('Mark all as read');
});

test('the center loads at most the latest one hundred notifications initially', function () {
    // Arrange
    $demoUser = User::factory()->create(['email' => 'test@example.com']);
    $actor = User::factory()->create();
    Notification::factory()
        ->count(101)
        ->for($demoUser, 'user')
        ->for($actor, 'actor')
        ->sequence(fn (Sequence $sequence): array => [
            'message' => 'Notification '.$sequence->index,
        ])
        ->create();

    $component = Livewire::test('pages::episodes.notification-center');

    // Act
    $notifications = $component->get('notifications');

    // Assert
    expect($notifications)->toHaveCount(100)
        ->and($notifications->first()->message)->toBe('Notification 100')
        ->and($notifications->last()->message)->toBe('Notification 1');

    $component->assertSet('latestLoadedId', $notifications->first()->id);
});

test('polling prepends new notifications and refreshes the unread count', function () {
    // Arrange
    $demoUser = User::factory()->create(['email' => 'test@example.com']);
    $actor = User::factory()->create();
    Notification::factory()
        ->for($demoUser, 'user')
        ->for($actor, 'actor')
        ->read()
        ->create();

    $component = Livewire::test('pages::episodes.notification-center');

    // Act
    $newNotification = Notification::factory()
        ->for($demoUser, 'user')
        ->for($actor, 'actor')
        ->unread()
        ->create(['message' => 'A new notification']);

    $component->call('checkForNew');

    // Assert
    $component
        ->assertSet('latestLoadedId', $newNotification->id)
        ->assertSet('unreadCount', 1);

    expect(data_get($component->effects, 'islandFragments.0'))->toContain('A new notification');
});

test('marking a notification as read is scoped to the demo user', function () {
    // Arrange
    $demoUser = User::factory()->create(['email' => 'test@example.com']);
    $otherUser = User::factory()->create();
    $actor = User::factory()->create();
    $notification = Notification::factory()
        ->for($demoUser, 'user')
        ->for($actor, 'actor')
        ->unread()
        ->create();
    $otherNotification = Notification::factory()
        ->for($otherUser, 'user')
        ->for($actor, 'actor')
        ->unread()
        ->create();

    $component = Livewire::test('pages::episodes.notification-center');

    // Act
    $component->call('markAsRead', $notification->id);

    // Assert
    $component->assertSet('unreadCount', 0);

    expect($notification->refresh()->read_at)->not->toBeNull()
        ->and($otherNotification->refresh()->read_at)->toBeNull();
});

test('marking all notifications as read dispatches an Alpine sync event', function () {
    // Arrange
    $demoUser = User::factory()->create(['email' => 'test@example.com']);
    $otherUser = User::factory()->create();
    $actor = User::factory()->create();
    $notifications = Notification::factory()
        ->count(2)
        ->for($demoUser, 'user')
        ->for($actor, 'actor')
        ->unread()
        ->create();
    $otherNotification = Notification::factory()
        ->for($otherUser, 'user')
        ->for($actor, 'actor')
        ->unread()
        ->create();

    $component = Livewire::test('pages::episodes.notification-center');

    // Act
    $component->call('markAllAsRead');

    // Assert
    $component
        ->assertSet('unreadCount', 0)
        ->assertDispatched('notifications-marked-read');

    expect($notifications->fresh()->every(fn (Notification $notification): bool => $notification->read_at !== null))->toBeTrue()
        ->and($otherNotification->refresh()->read_at)->toBeNull();
});

test('the simulator creates an unread notification for the demo user', function () {
    // Arrange
    $demoUser = User::factory()->create(['email' => 'test@example.com']);
    User::factory()->create();

    $component = Livewire::test('pages::episodes.notification-center');

    // Act
    $component->call('simulateIncomingNotification');

    // Assert
    $notification = Notification::query()
        ->where('user_id', $demoUser->id)
        ->latest('id')
        ->first();

    expect($notification)->not->toBeNull()
        ->and($notification->read_at)->toBeNull();
});

test('the notification center exposes Alpine read state and island animation markup', function () {
    // Arrange
    $demoUser = User::factory()->create(['email' => 'test@example.com']);
    Notification::factory()->for($demoUser, 'user')->create();

    // Act
    $response = $this->get(route('episodes.notification-center'));

    // Assert
    $response
        ->assertSuccessful()
        ->assertSee('animate-slide-down', false)
        ->assertSee('x-on:notifications-marked-read.window="read = true"', false)
        ->assertSee('x-data="{ read:', false)
        ->assertSee('min-h-0 overflow-hidden', false);
});
