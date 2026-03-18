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

                <a id="get-number-btn" href="#" class="btn btn-sm value_span6-1 value_span2 value_span4">Request Verification Number</a>
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
	let currentPollInterval = null;

	document.getElementById('get-number-btn').addEventListener('click', async function () {
		const button = this;
		const country = document.getElementById('country').value;

		button.disabled = true;
		document.getElementById('phone-number').textContent = '-';
		document.getElementById('code').textContent = '-';
		document.getElementById('status').textContent = 'Requesting verification number...';

		if (currentPollInterval) {
			clearInterval(currentPollInterval);
			currentPollInterval = null;
		}

		try {
			const response = await fetch('/api/sms-orders', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Accept': 'application/json',
				},
				body: JSON.stringify({
					service: 'Instagram / Threads',
					country: country
				})
			});

			const data = await response.json();

			if (!response.ok) {
				throw new Error(data.message || 'Unable to create SMS order.');
			}

			document.getElementById('phone-number').textContent = data.number || '-';
			document.getElementById('status').textContent = 'Waiting for verification code...';

			startPolling(data.id);
		} catch (error) {
			document.getElementById('status').textContent = error.message;
		} finally {
			button.disabled = false;
		}
	});

	function startPolling(orderId) {
		currentPollInterval = setInterval(async () => {
			try {
				const response = await fetch(`/api/sms-orders/${orderId}`, {
					headers: {
						'Accept': 'application/json',
					}
				});

				const data = await response.json();

				if (!response.ok) {
					throw new Error(data.message || 'Error checking order.');
				}

				if (data.phone_number) {
					document.getElementById('phone-number').textContent = data.phone_number;
				}

				if (data.status === 'received' && data.code) {
					document.getElementById('status').textContent = 'Code received';
					document.getElementById('code').textContent = data.code;
					clearInterval(currentPollInterval);
					currentPollInterval = null;
					return;
				}

				if (data.status === 'pending') {
					document.getElementById('status').textContent = 'Waiting for verification code...';
					return;
				}

				document.getElementById('status').textContent = `Status: ${data.status}`;
				clearInterval(currentPollInterval);
				currentPollInterval = null;
			} catch (error) {
				document.getElementById('status').textContent = error.message;
				clearInterval(currentPollInterval);
				currentPollInterval = null;
			}
		}, 4000);
	}
</script>
@endsection