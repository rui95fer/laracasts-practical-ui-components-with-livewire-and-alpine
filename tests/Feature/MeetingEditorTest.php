<?php

use App\Models\Meeting;
use Livewire\Livewire;

test('the meeting editor route renders', function () {
    // Arrange
    Meeting::factory()->create();

    // Act
    $response = $this->get(route('home'));

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
    expect($meeting->fresh()->title)->toBe('Updated title')
        ->and($meeting->fresh()->notes)->toBe('Original notes');
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
    expect($meeting->fresh()->title)->toBe('Original title')
        ->and($meeting->fresh()->notes)->toBe('Updated notes');
});

test('an invalid title is not persisted', function () {
    // Arrange
    $meeting = Meeting::factory()->create([
        'title' => 'Original title',
    ]);
    $component = Livewire::test('pages::meeting-editor');

    // Act
    $component->set('title', str_repeat('x', 256));

    // Assert
    $component
        ->assertHasErrors(['title' => ['max:255']])
        ->assertNotDispatched('meeting-saved');
    expect($meeting->fresh()->title)->toBe('Original title');
});
