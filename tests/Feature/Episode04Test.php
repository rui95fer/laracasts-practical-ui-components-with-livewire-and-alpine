<?php

use Livewire\Livewire;

test('the multi-step wizard episode route renders', function () {
    // Arrange
    $url = route('episodes.multi-step-wizard');

    // Act
    $response = $this->get($url);

    // Assert
    $response
        ->assertSuccessful()
        ->assertSeeLivewire('pages::episodes.multi-step-wizard')
        ->assertSee('Build a multi-step wizard')
        ->assertSee('Create product');
});

test('the wizard starts on the first step with empty state', function () {
    // Act
    $component = Livewire::test('pages::episodes.multi-step-wizard');

    // Assert
    $component
        ->assertSet('currentStep', 1)
        ->assertSet('name', '')
        ->assertSet('category', '')
        ->assertSet('description', '')
        ->assertSet('price', '')
        ->assertSet('url', '');
});

test('the first step validates only its visible fields', function () {
    // Arrange
    $component = Livewire::test('pages::episodes.multi-step-wizard');

    // Act
    $component
        ->set('name', 'Acme Analytics')
        ->set('category', 'saas')
        ->set('description', 'A useful analytics platform for growing teams.')
        ->call('nextStep');

    // Assert
    $component
        ->assertSet('currentStep', 2)
        ->assertHasNoErrors();
});

test('the first step blocks advancement and reports its own errors', function () {
    // Arrange
    $component = Livewire::test('pages::episodes.multi-step-wizard');

    // Act
    $component->call('nextStep');

    // Assert
    $component
        ->assertSet('currentStep', 1)
        ->assertHasErrors([
            'name' => ['required'],
            'category' => ['required'],
            'description' => ['required'],
        ]);
});

test('the second step validates price and URL before showing the preview', function () {
    // Arrange
    $component = Livewire::test('pages::episodes.multi-step-wizard')
        ->set('name', 'Acme Analytics')
        ->set('category', 'saas')
        ->set('description', 'A useful analytics platform for growing teams.')
        ->call('nextStep');

    // Act
    $component->call('nextStep');

    // Assert
    $component
        ->assertSet('currentStep', 2)
        ->assertHasErrors([
            'price' => ['required'],
            'url' => ['required'],
        ]);
});

test('valid second-step details advance to the preview', function () {
    // Arrange
    $component = Livewire::test('pages::episodes.multi-step-wizard')
        ->set('name', 'Acme Analytics')
        ->set('category', 'saas')
        ->set('description', 'A useful analytics platform for growing teams.')
        ->call('nextStep')
        ->set('price', '49.00')
        ->set('url', 'https://example.com/product');

    // Act
    $component->call('nextStep');

    // Assert
    $component
        ->assertSet('currentStep', 3)
        ->assertSee('Acme Analytics')
        ->assertSee('https://example.com/product');
});

test('preview edit actions return to the requested step and preserve values', function (int $step) {
    // Arrange
    $component = Livewire::test('pages::episodes.multi-step-wizard')
        ->set('name', 'Acme Analytics')
        ->set('category', 'saas')
        ->set('description', 'A useful analytics platform for growing teams.')
        ->set('price', '49.00')
        ->set('url', 'https://example.com/product')
        ->set('currentStep', 3);

    // Act
    $component->call('goToStep', $step);

    // Assert
    $component
        ->assertSet('currentStep', $step)
        ->assertSet('name', 'Acme Analytics')
        ->assertSet('price', '49.00');
})->with([1, 2]);

test('back navigation moves one step without losing values', function () {
    // Arrange
    $component = Livewire::test('pages::episodes.multi-step-wizard')
        ->set('name', 'Acme Analytics')
        ->set('category', 'saas')
        ->set('description', 'A useful analytics platform for growing teams.')
        ->set('currentStep', 3)
        ->set('price', '49.00')
        ->set('url', 'https://example.com/product');

    // Act
    $component->call('previousStep');

    // Assert
    $component
        ->assertSet('currentStep', 2)
        ->assertSet('name', 'Acme Analytics')
        ->assertSet('price', '49.00')
        ->assertSet('url', 'https://example.com/product');
});

test('session-backed wizard state survives a new component instance', function () {
    // Arrange
    Livewire::test('pages::episodes.multi-step-wizard')
        ->set('name', 'Acme Analytics')
        ->set('category', 'saas')
        ->set('description', 'A useful analytics platform for growing teams.')
        ->set('price', '49.00')
        ->set('url', 'https://example.com/product')
        ->set('currentStep', 3);

    // Act
    $component = Livewire::test('pages::episodes.multi-step-wizard');

    // Assert
    $component
        ->assertSet('currentStep', 3)
        ->assertSet('name', 'Acme Analytics')
        ->assertSet('category', 'saas')
        ->assertSet('description', 'A useful analytics platform for growing teams.')
        ->assertSet('price', '49.00')
        ->assertSet('url', 'https://example.com/product');
});

test('submitting the completed wizard resets state, closes the modal, and dispatches a toast', function () {
    // Arrange
    $component = Livewire::test('pages::episodes.multi-step-wizard')
        ->set('name', 'Acme Analytics')
        ->set('category', 'saas')
        ->set('description', 'A useful analytics platform for growing teams.')
        ->set('price', '49.00')
        ->set('url', 'https://example.com/product')
        ->set('currentStep', 3);

    // Act
    $component->call('submit');

    // Assert
    $component
        ->assertSet('currentStep', 1)
        ->assertSet('name', '')
        ->assertSet('category', '')
        ->assertSet('description', '')
        ->assertSet('price', '')
        ->assertSet('url', '')
        ->assertDispatched('modal-close', name: 'create-product')
        ->assertDispatched('toast', message: 'Product created', type: 'success');
});

test('the wizard markup includes the modal bounds, named transitions, and navigation controls', function () {
    // Arrange
    $url = route('episodes.multi-step-wizard');

    // Act
    $response = $this->get($url);
    $stepTwo = Livewire::test('pages::episodes.multi-step-wizard')
        ->set('currentStep', 2);
    $stepThree = Livewire::test('pages::episodes.multi-step-wizard')
        ->set('currentStep', 3);

    // Assert
    $response
        ->assertSuccessful()
        ->assertSee('max-h-[85vh] overflow-y-auto', false)
        ->assertSee('wire:transition="form"', false);

    $stepTwo
        ->assertSee('wire:click="nextStep"', false)
        ->assertSee('wire:click="previousStep"', false);

    $stepThree->assertSee('wire:click="submit"', false);
});
