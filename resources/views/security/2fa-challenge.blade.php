@extends('layouts.master')

@section('content')
    <div style="max-width: 420px; margin: 60px auto; text-align: center;">

        <h2>Two-Factor Authentication</h2>

        <p>
            Enter the 6-digit code from your authenticator app.
        </p>

        @if ($errors->any())
            <div style="color: red; margin-bottom: 15px;">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('2fa.challenge.verify') }}">
            @csrf

            <input
                    type="text"
                    name="code"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="10"
                    autofocus
                    style="font-size: 20px; padding: 10px; text-align: center; width: 100%;"
            />

            <div style="margin-top: 20px;">
                <button type="submit" style="padding: 10px 20px;">
                    Verify
                </button>
            </div>
        </form>

        <div style="margin-top: 25px; font-size: 14px; color: #666;">
            Lost your device? You may enter one of your recovery codes instead.
        </div>

    </div>

@endsection