<?php
use Flagship\Facades\Flagship;
use Workbench\App\Models\User;
use Illuminate\Support\Facades\Blade;

it('renders the feature content when the global feature is enabled', function () {
    Flagship::shouldReceive('isEnabled')
        ->with('new-design')
        ->once()
        ->andReturn(true);

    $view = $this->blade('
        @feature(\'new-design\')
            <div class="new-ui">New</div>
        @else
            <div class="legacy-ui">Legacy</div>
        @endfeature
   ');
    $view->assertDontSee('Legacy');
    $view->assertSee('New');
});

it('renders the fallback content when the global feature is disabled', function () {
    Flagship::shouldReceive('isEnabled')
        ->with('new-design')
        ->once()
        ->andReturn(false);

   $view = $this->blade('
        @feature(\'new-design\')
            <div class="new-ui">New</div>
        @else
            <div class="legacy-ui">Legacy</div>
        @endfeature
   ');
   $view->assertSee('Legacy');
   $view->assertDontSee('New');
});

it('renders content when feature is enabled for the user', function () {
    $user = User::factory()->make();

    Flagship::shouldReceive('isEnabledForUser')
        ->with('premium-features', $user)
        ->once()
        ->andReturn(true);

    $blade = <<<'BLADE'
        @featureForUser('premium-features', $user)
            <div class="premium-content">Premium</div>
        @endfeature
    BLADE;

    $output = Blade::render($blade, ['user' => $user]);

    expect($output)->toContain('premium-content');
});

it('does not render content when feature is disabled for the user', function () {
    $user = User::factory()->make();

    Flagship::shouldReceive('isEnabledForUser')
        ->with('premium-features', $user)
        ->once()
        ->andReturn(false);

    $blade = <<<'BLADE'
        @featureForUser('premium-features', $user)
            <div class="premium-content">Premium</div>
        @endfeature
    BLADE;

    $output = Blade::render($blade, ['user' => $user]);

    expect($output)->not->toContain('premium-content');
});
