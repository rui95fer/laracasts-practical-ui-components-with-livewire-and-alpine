<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Livewire\Livewire;

test('the dynamic search episode route renders', function () {
    // Arrange
    Post::factory()->create();

    // Act
    $response = $this->get(route('episodes.dynamic-search'));

    // Assert
    $response
        ->assertSuccessful()
        ->assertSeeLivewire('pages::episodes.dynamic-search')
        ->assertSee('Dynamic search')
        ->assertSee('wire:model.live.debounce.300ms="search"', false)
        ->assertSee('wire:loading wire:target="search"', false)
        ->assertSee('Use the arrow keys to navigate')
        ->assertSee('scrollIntoView({ block: \'nearest\' })', false)
        ->assertSee('window.location.href = url', false);
});

test('the search requires two characters and matches published posts across searchable fields', function () {
    // Arrange
    $author = User::factory()->create(['name' => 'Needle Author']);
    $otherAuthor = User::factory()->create(['name' => 'Other Author']);

    $titleMatch = Post::factory()->for($otherAuthor, 'author')->create([
        'title' => 'Needle in the title',
        'excerpt' => 'A plain excerpt.',
        'content' => 'A plain body.',
    ]);
    $excerptMatch = Post::factory()->for($otherAuthor, 'author')->create([
        'title' => 'An excerpt result',
        'excerpt' => 'The needle is in this excerpt.',
        'content' => 'A plain body.',
    ]);
    $contentMatch = Post::factory()->for($otherAuthor, 'author')->create([
        'title' => 'A content result',
        'excerpt' => 'A plain excerpt.',
        'content' => 'The needle is in this body.',
    ]);
    $authorMatch = Post::factory()->for($author, 'author')->create([
        'title' => 'An author result',
        'excerpt' => 'A plain excerpt.',
        'content' => 'A plain body.',
    ]);
    $unpublishedMatch = Post::factory()->for($author, 'author')->create([
        'title' => 'Needle in an unpublished post',
        'published' => false,
    ]);
    $component = Livewire::test('pages::episodes.dynamic-search');

    // Act
    $component->set('search', 'n');
    $shortResults = $component->get('results');
    $component->set('search', 'NEEDLE');
    $results = $component->get('results');

    // Assert
    expect($shortResults)->toBeEmpty()
        ->and($results)->toHaveCount(4)
        ->and($results->pluck('id')->all())
        ->toContain($titleMatch->id, $excerptMatch->id, $contentMatch->id, $authorMatch->id)
        ->not->toContain($unpublishedMatch->id);
});

test('the search returns only the ten latest matching posts', function () {
    // Arrange
    $posts = Post::factory()
        ->count(12)
        ->sequence(fn (Sequence $sequence): array => [
            'title' => 'Needle result '.$sequence->index,
            'created_at' => now()->subMinutes(12 - $sequence->index),
        ])
        ->create();
    $component = Livewire::test('pages::episodes.dynamic-search');

    // Act
    $component->set('search', 'needle');
    $results = $component->get('results');

    // Assert
    expect($results)->toHaveCount(10)
        ->and($results->first()->is($posts->last()))->toBeTrue()
        ->and($results->pluck('id'))->not->toContain($posts->first()->id)
        ->and($results->pluck('id'))->not->toContain($posts[1]->id);
});

test('highlighting escapes post text and snippets prefer excerpts before content matches', function () {
    // Arrange
    $post = Post::factory()->create([
        'title' => 'Markup <strong>Needle</strong>',
        'excerpt' => 'An excerpt without the search term.',
        'content' => 'A longer body containing Needle with useful context.',
    ]);
    $excerptPost = Post::factory()->create([
        'excerpt' => 'Needle appears in the excerpt.',
        'content' => 'A different body without the search term.',
    ]);
    $component = Livewire::test('pages::episodes.dynamic-search')->set('search', 'needle');

    // Act
    $highlighted = $component->instance()->highlightMatch($post->title);
    $contentSnippet = $component->instance()->getSnippet($post);
    $excerptSnippet = $component->instance()->getSnippet($excerptPost);

    // Assert
    expect($highlighted)->toContain('<mark class="')
        ->toContain('Needle')
        ->toContain('&lt;strong&gt;')
        ->not->toContain('<strong>')
        ->and($contentSnippet)->toContain('Needle')
        ->and($excerptSnippet)->toBe($excerptPost->excerpt);
});

test('recent searches are trimmed, case-insensitive, limited to five, and renderless', function () {
    // Arrange
    $component = Livewire::test('pages::episodes.dynamic-search');

    // Act
    $component
        ->call('addToRecentSearches', ' Alpha ')
        ->call('addToRecentSearches', 'Bravo')
        ->call('addToRecentSearches', 'Charlie')
        ->call('addToRecentSearches', 'Delta')
        ->call('addToRecentSearches', 'Echo')
        ->call('addToRecentSearches', 'Foxtrot')
        ->call('addToRecentSearches', 'ALPHA')
        ->call('addToRecentSearches', 'a');

    // Assert
    $component->assertSet('recentSearches', ['ALPHA', 'Foxtrot', 'Echo', 'Delta', 'Charlie']);
});

test('recent searches persist through a new component instance', function () {
    // Arrange
    $component = Livewire::test('pages::episodes.dynamic-search');

    // Act
    $component->call('addToRecentSearches', 'Persisted term');
    $refreshedComponent = Livewire::test('pages::episodes.dynamic-search');

    // Assert
    $refreshedComponent->assertSet('recentSearches', ['Persisted term']);
});

test('a recent search can be restored', function () {
    // Arrange
    $component = Livewire::test('pages::episodes.dynamic-search');

    // Act
    $component->call('useSearch', 'Design');

    // Assert
    $component
        ->assertSet('search', 'Design')
        ->assertSet('recentSearches', ['Design']);
});

test('recent searches can be cleared and notify Alpine', function () {
    // Arrange
    $component = Livewire::test('pages::episodes.dynamic-search')
        ->call('addToRecentSearches', 'Design');

    // Act
    $component->call('clearRecentSearches');

    // Assert
    $component
        ->assertSet('recentSearches', [])
        ->assertDispatched('recent-searches-cleared');
});

test('search results link to the post detail destination', function () {
    // Arrange
    $post = Post::factory()->create([
        'title' => 'Needle destination',
    ]);

    // Act
    $response = $this->get(route('posts.show', $post));

    // Assert
    $response
        ->assertSuccessful()
        ->assertSeeLivewire('pages::posts.show')
        ->assertSee($post->title)
        ->assertSee('Back to search');
});
