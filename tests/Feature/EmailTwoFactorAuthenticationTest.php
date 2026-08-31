<?php

use App\Livewire\Profile\TwoFactorAuthenticationForm;
use App\Models\User;
use App\Notifications\TwoFactor\EmailOtpCode;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;
use Livewire\Livewire;

test('enabling email two factor sends a code but does not confirm it yet', function () {
    Notification::fake();

    $this->actingAs($user = User::factory()->create()->fresh());
    $this->withSession(['auth.password_confirmed_at' => time()]);

    Livewire::test(TwoFactorAuthenticationForm::class)
        ->call('sendEmailTwoFactorCode');

    $user = $user->fresh();

    expect($user->email_otp_code)->not->toBeNull();
    expect($user->email_two_factor_confirmed_at)->toBeNull();
    expect($user->usesEmailTwoFactor())->toBeFalse();

    Notification::assertSentTo($user, EmailOtpCode::class);
})->skip(fn () => ! Features::canManageTwoFactorAuthentication(), 'Two factor authentication is not enabled.');

test('wrong code does not confirm email two factor', function () {
    Notification::fake();

    $this->actingAs($user = User::factory()->create()->fresh());
    $this->withSession(['auth.password_confirmed_at' => time()]);

    $component = Livewire::test(TwoFactorAuthenticationForm::class)
        ->call('sendEmailTwoFactorCode')
        ->set('emailCode', '000000')
        ->call('confirmEmailTwoFactorAuthentication');

    $component->assertHasErrors('emailCode');

    expect($user->fresh()->usesEmailTwoFactor())->toBeFalse();
})->skip(fn () => ! Features::canManageTwoFactorAuthentication(), 'Two factor authentication is not enabled.');

test('correct code confirms email two factor', function () {
    Notification::fake();

    $this->actingAs($user = User::factory()->create()->fresh());
    $this->withSession(['auth.password_confirmed_at' => time()]);

    Livewire::test(TwoFactorAuthenticationForm::class)
        ->call('sendEmailTwoFactorCode');

    $code = null;
    Notification::assertSentTo($user, EmailOtpCode::class, function ($notification) use (&$code) {
        $code = $notification->code;

        return true;
    });

    Livewire::test(TwoFactorAuthenticationForm::class)
        ->set('emailCode', $code)
        ->call('confirmEmailTwoFactorAuthentication')
        ->assertHasNoErrors();

    $user = $user->fresh();

    expect($user->usesEmailTwoFactor())->toBeTrue();
    expect($user->two_factor_method)->toBe('email');
    expect($user->email_otp_code)->toBeNull();
})->skip(fn () => ! Features::canManageTwoFactorAuthentication(), 'Two factor authentication is not enabled.');

test('confirming email two factor disables an already active authenticator app', function () {
    Notification::fake();

    $this->actingAs($user = User::factory()->create()->fresh());
    $this->withSession(['auth.password_confirmed_at' => time()]);

    Livewire::test(TwoFactorAuthenticationForm::class)
        ->call('enableTwoFactorAuthentication');

    expect($user->fresh()->two_factor_secret)->not->toBeNull();

    Livewire::test(TwoFactorAuthenticationForm::class)
        ->call('sendEmailTwoFactorCode');

    $code = null;
    Notification::assertSentTo($user, EmailOtpCode::class, function ($notification) use (&$code) {
        $code = $notification->code;

        return true;
    });

    Livewire::test(TwoFactorAuthenticationForm::class)
        ->set('emailCode', $code)
        ->call('confirmEmailTwoFactorAuthentication');

    $user = $user->fresh();

    expect($user->usesEmailTwoFactor())->toBeTrue();
    expect($user->two_factor_secret)->toBeNull();
})->skip(fn () => ! Features::canManageTwoFactorAuthentication(), 'Two factor authentication is not enabled.');

test('email two factor authentication can be disabled', function () {
    Notification::fake();

    $this->actingAs($user = User::factory()->create()->fresh());
    $this->withSession(['auth.password_confirmed_at' => time()]);

    Livewire::test(TwoFactorAuthenticationForm::class)
        ->call('sendEmailTwoFactorCode');

    $code = null;
    Notification::assertSentTo($user, EmailOtpCode::class, function ($notification) use (&$code) {
        $code = $notification->code;

        return true;
    });

    $component = Livewire::test(TwoFactorAuthenticationForm::class)
        ->set('emailCode', $code)
        ->call('confirmEmailTwoFactorAuthentication');

    expect($user->fresh()->usesEmailTwoFactor())->toBeTrue();

    $component->call('disableEmailTwoFactorAuthentication');

    expect($user->fresh()->usesEmailTwoFactor())->toBeFalse();
})->skip(fn () => ! Features::canManageTwoFactorAuthentication(), 'Two factor authentication is not enabled.');

test('login redirects an email two factor user to the challenge and a correct code completes login', function () {
    Notification::fake();

    $user = User::factory()->create([
        'two_factor_method' => 'email',
        'email_two_factor_confirmed_at' => now(),
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('two-factor.login'));
    $this->assertGuest();
    $this->assertEquals($user->id, session('login.id'));

    $code = null;
    Notification::assertSentTo($user, EmailOtpCode::class, function ($notification) use (&$code) {
        $code = $notification->code;

        return true;
    });

    $this->post('/two-factor-challenge', ['code' => $code]);

    $this->assertAuthenticatedAs($user);
})->skip(fn () => ! Features::canManageTwoFactorAuthentication(), 'Two factor authentication is not enabled.');

test('login challenge rejects an incorrect email code', function () {
    Notification::fake();

    $user = User::factory()->create([
        'two_factor_method' => 'email',
        'email_two_factor_confirmed_at' => now(),
    ]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->post('/two-factor-challenge', ['code' => '000000']);

    $this->assertGuest();
    expect($user->fresh()->email_otp_attempts)->toBe(1);
})->skip(fn () => ! Features::canManageTwoFactorAuthentication(), 'Two factor authentication is not enabled.');

test('resending the email code is rate limited', function () {
    Notification::fake();

    $user = User::factory()->create([
        'two_factor_method' => 'email',
        'email_two_factor_confirmed_at' => now(),
    ]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->post(route('two-factor.email.resend'));
    $response = $this->post(route('two-factor.email.resend'));

    $response->assertStatus(429);
})->skip(fn () => ! Features::canManageTwoFactorAuthentication(), 'Two factor authentication is not enabled.');
