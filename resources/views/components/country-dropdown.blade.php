@php
	if(!isset($country)) {
		$country = null;
	}
@endphp

<select class="selectBox" name="country" id="payout_country" required data-value="<?php if ($country) echo $country; ?>">
    <option value="">Select Your Country</option>
    @foreach (config('countries') as $key => $country)
        <option value="{{$key}}">{{$country['name']}}</option>
    @endforeach
</select>