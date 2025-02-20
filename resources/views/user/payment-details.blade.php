@extends('layouts.master')

@section('content')

    <div class="right_panel payment_details">
        <div class="white_box_outer large_table">
            <div class="heading_holder">
                <span class="lft value_span9">Payment Details</span>
            </div>
            @if(session()->has('message'))
                <div class="alert alert-success">
                    <h3>{{ session()->get('message') }}</h3>
                </div>
            @endif
            @if(session()->has('error'))
                <div class="alert alert-danger">
                    <h3>{{ session()->get('error') }}</h3>
                </div>
            @endif
            <div class="white_box value_span8">
                @if ($payoutDetails)
                    <div class="current_payout_details">
                        @if($payoutDetails->payout_type == 'paypal')
                            <div class="logo_wrap mt-0">
                                <img class="payout_logo" src="{{asset('/images/paypal-logo.png?v=2')}}" alt="">
                            </div>
                        @endif
                        @if($payoutDetails->payout_type == 'wise')
                            <div class="logo_wrap mt-0">
                                <img class="payout_logo" src="{{asset('/images/wise-logo.png')}}" alt="">
                            </div>
                        @endif
                        <p class="mt-4 mb-2">You are currently setup to be paid through <span class="text-uppercase">{{$payoutDetails->payout_type}}</span> using id {{$payoutDetails->payout_id}}</p>
                        <a class="text-decoration-underline text-uppercase" id="update_details_link" target="_blank" href='#'>Change Payout Details</a>
                    </div>
                    <div id="update_payout_form" class="payout_form hidden">
                        @include('components.payout-form')
                    </div>
                @else
                    <p class="subtext">Before we can send any payments to you. You'll need to select a payment method and submit your details.</p>
                    @include('components.payout-form')
                @endif
            </div>
        </div>
    </div>
@endsection