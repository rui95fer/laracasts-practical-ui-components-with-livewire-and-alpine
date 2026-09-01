<?php

test('the home page renders the episode catalog', function () {
    // Arrange
    $url = route('home');

    // Act
    $response = $this->get($url);

    // Assert
    $response
        ->assertSuccessful()
        ->assertSeeLivewire('pages::episodes');
});

test('the home page lists every episode with its destination', function () {
    // Arrange
    $episodes = [
        [
            'label' => 'Episode 01',
            'title' => 'Getting Started',
            'url' => route('episodes.getting-started'),
        ],
        [
            'label' => 'Episode 02',
            'title' => 'Introducing Inline Editing',
            'url' => route('episodes.inline-editing'),
        ],
        [
            'label' => 'Episode 03',
            'title' => 'Toast Notifications',
            'url' => route('episodes.toast-notifications'),
        ],
        [
            'label' => 'Episode 04',
            'title' => 'Build a Multi-Step Wizard',
            'url' => route('episodes.multi-step-wizard'),
        ],
        [
            'label' => 'Episode 05',
            'title' => 'Tag Input',
            'url' => route('episodes.tag-input'),
        ],
        [
            'label' => 'Episode 06',
            'title' => 'Infinite Scroll',
            'url' => route('episodes.infinite-scroll'),
        ],
        [
            'label' => 'Episode 07',
            'title' => 'Notification Center',
            'url' => route('episodes.notification-center'),
        ],
    ];

    // Act
    $response = $this->get(route('home'));

    // Assert
    $response->assertSuccessful();

    foreach ($episodes as $episode) {
        $response
            ->assertSee($episode['label'])
            ->assertSee($episode['title'])
            ->assertSee($episode['url'], false);
    }
});
