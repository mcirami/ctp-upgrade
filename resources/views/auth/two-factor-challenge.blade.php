<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('layouts.partials.network-theme-init')
    <title>Authenticator verification · {{ config('app.network_name', 'Rising Limitless') }}</title>
    <link rel="stylesheet" href="/css/font-awesome/css/all.css">
    <link rel="stylesheet" href="/css/network.css?v={{ filemtime(public_path('css/network.css')) }}">
</head>
<body class="rl-auth">
<main class="rl-login">
    <a class="rl-brand" href="/"><span class="rl-brand-mark">RL</span>{{ config('app.network_name', 'Rising Limitless') }}</a>
    <section class="rl-login-card">
        <h1>Verify it’s you</h1>
        @if($configured)
            <p>This God account is signing in from an IP address that is not on the whitelist.</p>
            <form method="post" action="{{ route('two-factor.verify') }}">
                @csrf
                @if($errors->has('code'))<div role="alert" class="rl-note" style="color:#c84242;margin:0 0 18px">{{ $errors->first('code') }}</div>@endif
                <label for="two-factor-code">Authenticator or recovery code</label>
                <input id="two-factor-code" type="text" name="code" autocomplete="one-time-code" placeholder="6-digit or recovery code" maxlength="32" required autofocus>
                <p class="rl-note">Request from {{ $ipAddress }}. This verification expires after five minutes.</p>
                <button class="rl-button rl-primary" type="submit">Verify and sign in <span aria-hidden="true">→</span></button>
            </form>
        @else
            <p>Google Authenticator has not been enrolled for this God account yet.</p>
            <div role="alert" class="rl-note" style="color:#c84242;margin:18px 0">Sign in from a whitelisted IP address first, then open My Account → Google Authenticator to finish setup.</div>
            <a class="rl-button" href="/login">Back to sign in</a>
        @endif
    </section>
    <footer class="rl-login-footer"><span>{{ config('app.network_name', 'Rising Limitless') }} · Protected account</span></footer>
</main>
</body>
</html>
