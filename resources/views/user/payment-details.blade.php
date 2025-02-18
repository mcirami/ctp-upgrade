@extends('layouts.master')

@section('content')
    <div class="right_panel payment_details">
        <div class="white_box_outer large_table">
            <div class="heading_holder value_span9">
                <span class="lft">Payment Details</span>
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
                <form method="POST" action="{{ route('collect.wise.details') }}">
                    @csrf
                    <div class="radio_row">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="accountType" id="personal" checked>
                            <label class="form-check-label" for="personal">
                                Personal Account
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="accountType" id="business">
                            <label class="form-check-label" for="business">
                                Business Account
                            </label>
                        </div>
                    </div>
                    <div>
                        <label for="currency">Currency:</label>
                        <select id="currency" name="currency" required>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                            <option value="GBP">GBP</option>
                        </select>
                    </div>

                    {{--<div>
                        <label for="iban">IBAN:</label>
                        <input type="text" id="iban" name="iban" required>
                    </div>--}}
                    <button class="button value_span11 value_span2 value_span4" type="submit">Submit</button>
                </form>
            </div>
        </div>
    </div>
@endsection