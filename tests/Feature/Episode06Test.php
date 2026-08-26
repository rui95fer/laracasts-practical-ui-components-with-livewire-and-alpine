<?php

use App\Models\Post;
use Livewire\Livewire;

test('the infinite scroll episode route renders', function () {
    // Arrange
    Post::factory()->create();

    // Act
    $response = $this->get(route('episodes.infinite-scroll'));

    // Assert
    $response
        ->assertSuccessful()
        ->assertSeeLivewire('pages::episodes.infinite-scroll')
        ->assertSee('Infinite scroll')
        ->assertSee('wire:intersect="loadMore"', false)
        ->assertSee('wire:island.append="posts"', false)
        ->assertSee('wire:model.live.debounce.300ms="search"', false);
});

test('the feed loads only the first page initially', function () {
    // Arrange
    $posts = Post::factory()->count(6)->create();

    // Act
    $component = Livewire::test('pages::episodes.infinite-scroll');

    // Assert
    $component
        ->assertSet('page', 1)
        ->assertSet('perPage', 5)
        ->assertSee($posts->last()->title)
        ->assertDontSee($posts->first()->title);
});

test('loading more advances the page and dispatches end-of-feed', function () {
    // Arrange
    Post::factory()->count(6)->create();
    $component = Livewire::test('pages::episodes.infinite-scroll');

    // Act
    $component->call('loadMore');

    // Assert
    $component
        ->assertSet('page', 2)
        ->assertDispatched('end-of-feed');
});

test('changing category resets pagination and filters the feed', function () {
    // Arrange
    $technologyPost = Post::factory()->create([
        'title' => 'Technology systems',
        'category' => 'technology',
    ]);
    $designPost = Post::factory()->create([
        'title' => 'Design systems',
        'category' => 'design',
    ]);
    $component = Livewire::test('pages::episodes.infinite-scroll');

    // Act
    $component
        ->set('page', 2)
        ->set('category', 'technology');
    $filteredPosts = $component->get('posts');

    // Assert
    $component
        ->assertSet('page', 1)
        ->assertDispatched('feed-reset')
        ->assertDispatched('scroll-to-top');

    expect($filteredPosts)->toHaveCount(1)
        ->and($filteredPosts->first()->is($technologyPost))->toBeTrue();
});

test('changing search resets pagination and filters the feed', function () {
    // Arrange
    $matchingPost = Post::factory()->create([
        'title' => 'Design systems',
        'category' => 'technology',
    ]);
    $nonMatchingPost = Post::factory()->create([
        'title' => 'Database migrations',
        'category' => 'technology',
    ]);
    $component = Livewire::test('pages::episodes.infinite-scroll');

    // Act
    $component
        ->set('page', 2)
        ->set('search', 'systems');
    $searchedPosts = $component->get('posts');

    // Assert
    $component
        ->assertSet('page', 1)
        ->assertDispatched('feed-reset')
        ->assertDispatched('scroll-to-top');

    expect($searchedPosts)->toHaveCount(1)
        ->and($searchedPosts->first()->is($matchingPost))->toBeTrue()
        ->and($searchedPosts->pluck('id'))->not->toContain($nonMatchingPost->id);
});

test('the feed shows empty state for filters without matches', function () {
    // Arrange
    $component = Livewire::test('pages::episodes.infinite-scroll');

    // Act
    $component->set('search', 'does-not-exist');

    // Assert
    $component
        ->assertSet('page', 1)
        ->assertSee('No posts found')
        ->assertDispatched('feed-reset');
});

test('the markup exposes Alpine end-of-feed and scroll reset states', function () {
    // Arrange
    Post::factory()->create();

    // Act
    $response = $this->get(route('episodes.infinite-scroll'));

    // Assert
    $response
        ->assertSuccessful()
        ->assertSee('x-on:feed-reset.window="ended = false"', false)
        ->assertSee('x-on:end-of-feed.window="ended = true"', false)
        ->assertSee('x-on:scroll-to-top.window="window.scrollTo({ top: 0, behavior: \'smooth\' })"', false);
});
