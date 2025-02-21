@php
	$payoutType = $payoutDetails ? $payoutDetails->payout_type : null;
	$payoutId = $payoutDetails ? $payoutDetails->payout_id : null;
	$country = $payoutDetails ? $payoutDetails->country : null;
@endphp

<form id="submit_payment_details" method="POST" action="{{ route('add.payment.details') }}">
    @csrf
    <div class="column">
        <div class="radio_wrap">
            <div class="form-check">
                <input value="wise" class="form-check-input" type="radio" name="payout_type" id="wise"
				    <?php if($payoutType == "wise" || !$payoutDetails) echo "checked"; ?>>
                <label class="form-check-label" for="Wise">
                    <img class="payout_logo" src="{{asset('/images/wise-logo.png')}}" alt="">
                </label>
            </div>
            <div class="form-check">
                <input value="paypal" class="form-check-input" type="radio" name="payout_type" id="paypal"
				    <?php if($payoutType == "paypal") echo "checked"; ?>>
                <label class="form-check-label" for="paypal">
                    <img class="payout_logo" src="{{asset('/images/paypal-logo.png?v=2')}}" alt="">
                </label>
            </div>
        </div>
        <div class="payout_text">

        </div>
    </div>
    <div class="column">
        <div class="form-group">
            <label for="payout_id">
                Payout ID
            </label>
            <div class="column">
                <input required id="payout_id" name="payout_id" type="text" value="<?php if($payoutId) echo $payoutId ?>">
            </div>
            <div class="column">
                @include('components.country-dropdown')
            </div>
        </div>
    </div>
    <div class="form-group submit_wrap">
        <button class="button value_span11 value_span2 value_span4" type="submit">Submit</button>
        @if ($payoutDetails)
            <a id="cancel_payout_update" class="value_span6 value_span5" href="#">Cancel</a>
        @endif
    </div>
</form>
