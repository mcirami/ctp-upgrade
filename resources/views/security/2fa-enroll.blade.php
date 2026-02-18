@php
    use PragmaRX\Google2FA\Google2FA;

	$google2fa = app(Google2FA::class);

    $qrUrl = $google2fa->getQRCodeUrl(
        config('app.name'),
        $user->email,
        $user->two_factor_secret
    );
@endphp

{!! QrCode::size(220)->generate($qrUrl) !!}