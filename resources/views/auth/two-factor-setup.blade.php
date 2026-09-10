@extends('layouts.master')
@section('content')
<div class="right_panel rl-settings">
    <div class="rl-page-heading"><div><h1>Google Authenticator</h1><p>Extra verification for God-role logins from IP addresses outside the whitelist</p></div></div>

    @if(session('two_factor_status'))<div class="rl-settings-message" role="status">{{ session('two_factor_status') }}</div>@endif
    @if($errors->any())<div class="rl-settings-message" role="alert"><strong>That could not be completed.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    @if($recoveryCodes)
        <section class="rl-card rl-settings-card">
            <h2>Save your recovery codes now</h2>
            <p>Each code works once. Store these somewhere safe; they will not be shown again.</p>
            <div class="rl-link-box" style="display:block"><code style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px">@foreach($recoveryCodes as $code)<span>{{ $code }}</span>@endforeach</code></div>
            <button class="rl-button" type="button" style="margin-top:14px" data-copy-text="{{ implode("\n", $recoveryCodes) }}">Copy recovery codes</button>
        </section>
    @endif

    @if($enabled)
        <section class="rl-card rl-settings-card">
            <h2>Authenticator is enabled</h2>
            <p>God-role logins from an IP address outside the whitelist must provide an authenticator or unused recovery code. Whitelisted IP addresses continue to use the password-only login.</p>
            <p class="rl-settings-help">You are currently connected from {{ $ipAddress }}.</p>
        </section>
        <section class="rl-card rl-settings-card">
            <h2>Disable authenticator</h2>
            <p>This can only be done from a whitelisted IP and requires a current authenticator or recovery code.</p>
            <form method="post" action="{{ route('two-factor.disable') }}">
                @csrf
                @method('DELETE')
                <label for="disable-code">Authenticator or recovery code</label>
                <input id="disable-code" type="text" name="code" autocomplete="one-time-code" maxlength="32" required>
                <button class="rl-button" type="submit" style="margin-top:14px">Disable authenticator</button>
            </form>
        </section>
    @else
        <section class="rl-card rl-settings-card">
            <h2>Set up Google Authenticator</h2>
            <ol>
                <li>Open Google Authenticator on your phone.</li>
                <li>Tap the plus button and choose <strong>Scan a QR code</strong>.</li>
                <li>Scan this code, then enter the six-digit number below.</li>
            </ol>
            <div style="display:flex;flex-wrap:wrap;gap:24px;align-items:center;margin:20px 0">
                <img src="{{ $qrCode }}" width="260" height="260" alt="Google Authenticator setup QR code" style="background:#fff;padding:10px;border-radius:12px">
                <div><p class="rl-note">Can’t scan it? Enter this setup key manually:</p><div class="rl-link-box"><code>{{ $secret }}</code><button class="rl-button" type="button" data-copy-text="{{ $secret }}">Copy</button></div></div>
            </div>
            <form method="post" action="{{ route('two-factor.confirm') }}">
                @csrf
                <label for="confirm-code">Six-digit code</label>
                <input id="confirm-code" type="text" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required>
                <button class="rl-button rl-primary" type="submit" style="margin-top:14px">Confirm and enable</button>
            </form>
        </section>
    @endif
</div>
@endsection
