@extends('layouts.master')

@section('content')
    <div class="right_panel">
        <div class="white_box_outer large_table">
            <div class="heading_holder">
                <span class="lft value_span9">Payment Details</span>
            </div>
            @if(session()->has('success'))
                <div class="alert alert-success">
                    <h3>{{ session()->get('success') }}</h3>
                </div>
            @endif
            @if(session()->has('error'))
                <div class="alert alert-danger">
                    <h3>{{ session()->get('error') }}</h3>
                </div>
            @endif
            @if ($payoutDetails && $payoutDetails->onboarding_complete)
                <p>Login to the Stripe Dashboard to see details about your account.</p>
                <a target="_blank" href='/user/stripe-login/{{$user->idrep}}'>Log Into Stripe Dashboard</a>
                <p>NOTE: Your login link is only valid for a limited time. If it expires you need to return here and click the button above again.</p>
            @elseif($payoutDetails && !$payoutDetails->onboarding_complete)
                <p>Looks like you didnt finish setting up your payment information with Stripe.</p>
                <p>Before we can send any payments to you. You'll need to submit your payment info to Stripe Gateway.</p>
                <p>Stripe is a third party application we use to securely handle any payment related needs.</p>
                <p>It's easy to set up. Click the button below and follow the instructions to connect your bank info so you can get paid!</p>
                <a class="btn btn-default value_span11 value_span2 value_span4" href={{ route('stripe.refresh.url') }}>Finishing Setting Up Now!</a>
            @else
                <p>Before we can send any payments to you. You'll need to submit your payment info to Stripe Gateway.</p>
                <p>Stripe is a third party application we use to securely handle any payment related needs.</p>
                <p>It's easy to set up. Click the button below and follow the instructions to connect your bank info so you can get paid!</p>
                <a class="btn btn-default value_span11 value_span2 value_span4" href='/user/add-payment-details/{{$user->idrep}}'>Set Up Payment Now!</a>
            @endif
        </div>
    </div>
@endsection