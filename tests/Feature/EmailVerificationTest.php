<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Laravel\Fortify\Features;

test('email verification screen can be rendered', function () {
    $user = User::factory()->withPersonalTeam()->create([
        'email_verified_at' => null,
    ]);

    $response = $this->actingAs($user)->get('/email/verify');

    $response->assertStatus(200);
})->skip(function () {
    return ! Features::enabled(Features::emailVerification());
}, 'Email verification not enabled.');

test('client email can be verified and redirects them to login after logout', function () {
    Event::fake(Verified::class);

    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);
    $user->assignRole('client');

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    Event::assertDispatched(Verified::class);

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    $response->assertRedirect(route('login', absolute: false));
    $response->assertSessionHas('status', 'Your email has been verified successfully. Please log in to continue.');
})->skip(function () {
    return ! Features::enabled(Features::emailVerification());
}, 'Email verification not enabled.');

test('email can not verified with invalid hash', function () {
    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1('wrong-email')]
    );

    $this->actingAs($user)->get($verificationUrl);

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
})->skip(function () {
    return ! Features::enabled(Features::emailVerification());
}, 'Email verification not enabled.');
