@extends('layouts.master')

@section('content')
    <div class="right_panel payment_details">
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
            <div class="white_box value_span8">
                @if ($payoutDetails)
                    <p>You are currently setup to be paid through {{$payoutDetails->payout_type}}</p>
                    <a target="_blank" href='#'>Change Payout Details</a>
              @else
                    <p>Before we can send any payments to you. You'll need to select a payment method and submit your details.</p>
                    <form method="POST" action="{{ route('add.payment.details') }}">
                        @csrf
                        <div class="radio_wrap">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payoutType" id="Wise" checked>
                                <label class="form-check-label" for="Wise">
                                    Wise
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payoutType" id="paypal">
                                <label class="form-check-label" for="paypal">
                                    PayPal
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payoutType" id="cashapp">
                                <label class="form-check-label" for="cashapp">
                                    CashApp
                                </label>
                            </div>
                        </div>
                        <div class="logo_wrap">
                            <img id="payout_logo" src="{{asset('images/wise-logo.png')}}" alt="">
                        </div>
                        <input type="text" placeholder="">
                        <button class="button value_span11 value_span2 value_span4" type="submit">Submit</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection