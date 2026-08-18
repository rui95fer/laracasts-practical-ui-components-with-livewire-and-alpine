<?php

use App\Models\Meeting;
use Livewire\Livewire;

test('the inline editing episode route renders', function () {
    // Arrange
    Meeting::factory()->create();
    $url = route('episodes.inline-editing');

    // Act
    $response = $this->get($url);

    // Assert
    $response
        ->assertSuccessful()
        ->assertSeeLivewire('pages::meeting-editor');
});

test('the meeting editor loads the first meeting', function () {
    // Arrange
    $meeting = Meeting::factory()->create([
        'title' => 'Planning session',
        'notes' => 'Review the launch checklist.',
    ]);

    // Act
    $component = Livewire::test('pages::meeting-editor');

    // Assert
    $component
        ->assertSet('meeting.id', $meeting->id)
        ->assertSet('title', 'Planning session')
        ->assertSet('notes', 'Review the launch checklist.');
});

test('editing the title autosaves only the title and dispatches an event', function () {
    // Arrange
    $meeting = Meeting::factory()->create([
        'title' => 'Original title',
        'notes' => 'Original notes',
    ]);
    $component = Livewire::test('pages::meeting-editor');

    // Act
    $component->set('title', 'Updated title');

    // Assert
    $component->assertDispatched('meeting-saved');

    $savedMeeting = $meeting->fresh();

    expect($savedMeeting->title)->toBe('Updated title')
        ->and($savedMeeting->notes)->toBe('Original notes');
});

test('editing the notes autosaves only the notes and dispatches an event', function () {
    // Arrange
    $meeting = Meeting::factory()->create([
        'title' => 'Original title',
        'notes' => 'Original notes',
    ]);
    $component = Livewire::test('pages::meeting-editor');

    // Act
    $component->set('notes', 'Updated notes');

    // Assert
    $component->assertDispatched('meeting-saved');

    $savedMeeting = $meeting->fresh();

    expect($savedMeeting->title)->toBe('Original title')
        ->and($savedMeeting->notes)->toBe('Updated notes');
});

test('an invalid meeting field is not persisted or dispatched', function (string $property, string $value, string $rule) {
    // Arrange
    $meeting = Meeting::factory()->create([
        'title' => 'Original title',
        'notes' => 'Original notes',
    ]);
    $component = Livewire::test('pages::meeting-editor');

    // Act
    $component->set($property, $value);

    // Assert
    $component
        ->assertHasErrors([$property => [$rule]])
        ->assertNotDispatched('meeting-saved');

    $savedMeeting = $meeting->fresh();

    expect($savedMeeting->title)->toBe('Original title')
        ->and($savedMeeting->notes)->toBe('Original notes');
})->with([
    'title' => ['title', str_repeat('x', 256), 'max:255'],
    'notes' => ['notes', str_repeat('x', 5001), 'max:5000'],
]);
