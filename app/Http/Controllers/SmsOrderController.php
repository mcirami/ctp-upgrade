<?php

namespace App\Http\Controllers;

use App\SmsOrder;
use App\Services\SmsPoolService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Throwable;

class SmsOrderController extends Controller
{
	public function store(Request $request, SmsPoolService $smsPool): JsonResponse
	{
		$data = $request->validate([
			'service' => ['required', 'string', 'max:255'],
			'country' => ['required', 'string', 'max:8'],
			'pool' => ['nullable', 'string', 'max:255'],
			'client_reference' => ['nullable', 'string', 'max:255'],
		]);

		try {
			$result = $smsPool->orderSms(
				country: $data['country'],
				service: $data['service'],
				pool: $data['pool'] ?? null,
			);

			/*
			 * SMSPool response keys may vary a bit depending on endpoint/version.
			 * Adjust these mappings if your live response uses different names.
			 */
			$smspoolOrderId = $result['orderid'] ?? $result['order_id'] ?? null;
			$phoneNumber = $result['number'] ?? $result['phonenumber'] ?? null;

			if (! $smspoolOrderId) {
				return response()->json([
					'message' => 'SMSPool response did not include an order ID.',
					'raw' => $result,
				], 422);
			}

			$order = SmsOrder::create([
				'client_reference' => $data['client_reference'] ?? null,
				'smspool_order_id' => (string) $smspoolOrderId,
				'phone_number' => $phoneNumber ? (string) $phoneNumber : null,
				'service' => $data['service'],
				'country' => $data['country'],
				'pool' => $data['pool'] ?? null,
				'status' => 'pending',
				'raw_order_response' => $result,
				'expires_at' => Carbon::now()->addMinutes(15),
			]);

			return response()->json([
				'id' => $order->id,
				'smspool_order_id' => $order->smspool_order_id,
				'phone_number' => $order->phone_number,
				'status' => $order->status,
				'expires_at' => optional($order->expires_at)->toISOString(),
			], 201);
		} catch (Throwable $e) {
			report($e);

			return response()->json([
				'message' => 'Unable to create SMS order.',
				'error' => $e->getMessage(),
			], 500);
		}
	}

	public function showOrder(SmsOrder $smsOrder, SmsPoolService $smsPool): JsonResponse
	{
		if (
			$smsOrder->status === 'pending' &&
			(
				! $smsOrder->last_checked_at ||
				$smsOrder->last_checked_at->lt(now()->subSeconds(10))
			)
		) {
			try {
				$result = $smsPool->checkSms($smsOrder->smspool_order_id);

				$smsOrder->raw_last_check_response = $result;
				$smsOrder->last_checked_at = now();

				$sms = $result['sms'] ?? null;
				$fullSms = $result['full_sms'] ?? null;

				if (! empty($sms)) {
					$smsOrder->status = 'received';
					$smsOrder->code = (string) $sms;
					$smsOrder->full_sms = $fullSms;
					$smsOrder->received_at = now();
				}

				$smsOrder->save();
			} catch (\Throwable $e) {
				report($e);
			}
		}

		return response()->json([
			'id' => $smsOrder->id,
			'smspool_order_id' => $smsOrder->smspool_order_id,
			'phone_number' => $smsOrder->phone_number,
			'status' => $smsOrder->status,
			'code' => $smsOrder->code,
			'full_sms' => $smsOrder->full_sms,
			'received_at' => optional($smsOrder->received_at)->toISOString(),
			'last_checked_at' => optional($smsOrder->last_checked_at)->toISOString(),
			'expires_at' => optional($smsOrder->expires_at)->toISOString(),
		]);
	}

	public function show() {

		return view('sms.sms-pool');
	}
}
