<?php

use App\Models\Meeting;

test('returns a successful response', function () {
    Meeting::factory()->create();

    $response = $this->get(route('home'));

    $response->assertOk();
});
