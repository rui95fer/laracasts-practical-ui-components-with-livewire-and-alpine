<?php

use App\Models\Tag;
use Livewire\Livewire;

test('the tag input episode route renders', function () {
    // Act
    $response = $this->get(route('episodes.tag-input'));

    // Assert
    $response
        ->assertSuccessful()
        ->assertSeeLivewire('pages::episodes.tag-input')
        ->assertSee('Tag input')
        ->assertSee('Add a tag');
});

test('the tag input starts with empty state', function () {
    // Act
    $component = Livewire::test('pages::episodes.tag-input');

    // Assert
    $component
        ->assertSet('search', '')
        ->assertSet('suggestions', [])
        ->assertSet('selectedTags', [])
        ->assertSet('canCreateTag', false);
});

test('search loads at most five suggestions and excludes selected tags', function () {
    // Arrange
    $selectedTag = Tag::factory()->create(['name' => 'Art']);
    Tag::factory()->createMany([
        ['name' => 'Alpha'],
        ['name' => 'Bravo'],
        ['name' => 'Charlie'],
        ['name' => 'Delta'],
        ['name' => 'Gamma'],
        ['name' => 'Lambda'],
    ]);

    // Act
    $component = Livewire::test('pages::episodes.tag-input')
        ->set('selectedTags', [
            ['id' => $selectedTag->id, 'name' => $selectedTag->name],
        ])
        ->set('search', 'a');

    // Assert
    $suggestions = $component->get('suggestions');

    expect($suggestions)->toHaveCount(5)
        ->and(collect($suggestions)->pluck('id')->all())->not->toContain($selectedTag->id);
});

test('adding a tag prevents duplicates and clears the search state', function () {
    // Arrange
    $tag = Tag::factory()->create(['name' => 'Art']);
    $component = Livewire::test('pages::episodes.tag-input')
        ->set('search', 'art');

    // Act
    $component
        ->call('addTag', $tag->id)
        ->call('addTag', $tag->id);

    // Assert
    $component
        ->assertSet('selectedTags', [
            ['id' => $tag->id, 'name' => 'Art'],
        ])
        ->assertSet('search', '')
        ->assertSet('suggestions', [])
        ->assertSet('canCreateTag', false);
});

test('removing a tag filters it from the selected state', function () {
    // Arrange
    $art = Tag::factory()->create(['name' => 'Art']);
    $gaming = Tag::factory()->create(['name' => 'Gaming']);
    $component = Livewire::test('pages::episodes.tag-input')
        ->set('selectedTags', [
            ['id' => $art->id, 'name' => $art->name],
            ['id' => $gaming->id, 'name' => $gaming->name],
        ]);

    // Act
    $component->call('removeTag', $art->id);

    // Assert
    $component->assertSet('selectedTags', [
        ['id' => $gaming->id, 'name' => $gaming->name],
    ]);
});

test('creating an existing tag reuses a case-insensitive match and dispatches a toast', function () {
    // Arrange
    $tag = Tag::factory()->create(['name' => 'Art']);
    $component = Livewire::test('pages::episodes.tag-input')
        ->set('search', ' art ');

    // Act
    $component->call('createTag');

    // Assert
    expect(Tag::query()->count())->toBe(1);

    $component
        ->assertSet('selectedTags', [
            ['id' => $tag->id, 'name' => 'Art'],
        ])
        ->assertDispatched('toast', message: 'Tag added', type: 'success');
});

test('creating a missing tag trims the name, selects it, and dispatches a toast', function () {
    // Arrange
    $component = Livewire::test('pages::episodes.tag-input')
        ->set('search', ' Singing ');

    // Act
    $component->call('createTag');

    // Assert
    $tag = Tag::query()->where('name', 'Singing')->firstOrFail();

    $component
        ->assertSet('selectedTags', [
            ['id' => $tag->id, 'name' => 'Singing'],
        ])
        ->assertSet('search', '')
        ->assertSet('suggestions', [])
        ->assertDispatched('toast', message: 'Tag added', type: 'success');
});

test('the create option is available only when there is no exact match', function () {
    // Arrange
    Tag::factory()->create(['name' => 'Art']);
    $component = Livewire::test('pages::episodes.tag-input');

    // Act
    $component->set('search', 'art');

    // Assert
    $component
        ->assertSet('canCreateTag', false)
        ->assertDontSee('wire:click="createTag"', false);

    // Act
    $component->set('search', 'singing');

    // Assert
    $component
        ->assertSet('canCreateTag', true)
        ->assertSee('wire:click="createTag"', false);
});

test('the markup includes Alpine dropdown behavior and keyboard navigation', function () {
    // Act
    $response = $this->get(route('episodes.tag-input'));
    $tag = Tag::factory()->create(['name' => 'Art']);
    $component = Livewire::test('pages::episodes.tag-input')
        ->set('search', 'ar');

    // Assert
    $response
        ->assertSuccessful()
        ->assertSee('x-on:click.outside="open = false; highlightedIndex = -1"', false)
        ->assertSee('x-on:keydown.arrow-down.prevent="moveDown()"', false)
        ->assertSee('x-on:keydown.arrow-up.prevent="moveUp()"', false)
        ->assertSee('x-on:keydown.enter.prevent="selectHighlighted()"', false)
        ->assertSee('wire:model.live.debounce.300ms="search"', false);

    $component->assertSee('wire:key="suggestion-'.$tag->id.'"', false);
});
