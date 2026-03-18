@extends('layouts.master')
@section('content')

    <div class = "right_panel member_home">
        <div class = "white_box_outer">
            <div class = "heading_holder">
                <span class = "lft value_span9">Verification</span>
            </div>
            <div style="margin-bottom: 20px;" class = "white_box value_span8">
                <div style="margin-bottom: 12px;">
                    <label class="value_span9" for="country">Country: </label>
                    <select style="border-radius: 5px;" class="input-sm" id="country">
                        <option value="NL" selected>NL - Netherlands</option>
                    </select>
                </div>
                <p class="value_span9" style="font-size: 16px;">Phone number: <span id="phone-number">-</span></p>
                <p class="value_span9" style="margin: 10px 0; font-size: 16px;">Status: <span style="font-weight: 800;" class="font-weight-bold" id="status">Idle</span></p>
                <p class="value_span9" style="font-size: 16px;">Code: <strong id="code">-</strong></p>

                <a href="#" class="btn btn-sm value_span6-1 value_span2 value_span4" onclick="requestSmsOrder(); return false;">Request Verification Number</a>
            </div>
            <div style="display:inline-block;" id="instruction">
                <p class="value_span9">
                    Choose a country and click the button above to request a phone number.
                </p>
                <p class="value_span9">Enter this phone number into Instagram, then wait for the verification code to appear here.</p>
            </div>
        </div>
    </div>
    <!--right_panel-->
<script>
	async function requestSmsOrder() {
		const res = await fetch('/api/sms-orders', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json'
			},
			body: JSON.stringify({
				service: 'Instagram',
				country: 'US'
			})
		});

		const data = await res.json();

		if (!res.ok) {
			throw new Error(data.message || 'Failed to create SMS order');
		}

		document.getElementById('phone-number').textContent = data.phone_number || 'Number unavailable';
		document.getElementById('status').textContent = 'Waiting for verification code...';

		pollSmsOrder(data.id);
	}

	function pollSmsOrder(orderId) {
		const interval = setInterval(async () => {
			const res = await fetch(`/api/sms-orders/${orderId}`);
			const data = await res.json();

			if (!res.ok) {
				clearInterval(interval);
				document.getElementById('status').textContent = 'There was a problem checking the order.';
				return;
			}

			if (data.status === 'received' && data.code) {
				clearInterval(interval);
				document.getElementById('status').textContent = 'Code received';
				document.getElementById('code').textContent = data.code;
				return;
			}

			if (data.status !== 'pending') {
				clearInterval(interval);
				document.getElementById('status').textContent = `Status: ${data.status}`;
			}
		}, 4000);
	}
</script>
@endsection