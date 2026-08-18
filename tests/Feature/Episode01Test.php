<?php

test('the getting started episode renders its page', function () {
    // Arrange
    $url = route('episodes.getting-started');

    // Act
    $response = $this->get($url);

    // Assert
    $response
        ->assertSuccessful()
        ->assertSeeLivewire('pages::episodes.getting-started')
        ->assertSee('Getting started')
        ->assertSee('Learn the division of responsibility');
});
