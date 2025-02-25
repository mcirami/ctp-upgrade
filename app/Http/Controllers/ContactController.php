<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactFormRequest;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class ContactController extends Controller
{
	private string $telegramBotUrl;
	public function __construct() {

		$telegramBotToken = config('services.telegram.bot_token');
		$this->telegramBotUrl = "https://api.telegram.org/bot{$telegramBotToken}/sendMessage";
	}
    public function showContactPage(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application {

		return view('contact.show');
    }

	public function submitContactForm(ContactFormRequest $request): RedirectResponse {

		// 2. Verify reCAPTCHA
		$secretKey = config('services.recaptcha.secret_key');
		$token = $request->input('recaptcha_token');
		$verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

		$response = Http::asForm()->post($verifyUrl, [
			'secret' => $secretKey,
			'response' => $token,
			// 'remoteip' => $request->ip(), // optional
		]);

		$captchaResult = $response->json();

		// $captchaResult['success'] is true/false
		// $captchaResult['score'] is from 0.0 (bad) to 1.0 (good)
		// $captchaResult['action'] is the action name you used (e.g. 'contact_form')
		if (!$captchaResult['success'] || $captchaResult['score'] < 0.5) {
			return back()->withErrors(['error' => 'reCAPTCHA validation failed.']);
		}

		$text = "New contact form submission:\n";
		$text .= "Name: " . $request->input('name') . "\n";
		$text .= "Email: " . $request->input('email') . "\n";
		$text .= "IM: " . $request->input('im_type') . "\n";
		$text .= "IM Username: " . $request->input('im_username') . "\n";
		$text .= "Message: " . $request->input('message') . "\n";

		$response = Http::post($this->telegramBotUrl, [
			'chat_id' =>  config('services.telegram.contact_chat_id'),
			'text'    => $text,
		]);

		if (!$response->ok()) {
			return back()->withErrors(['error' => $response->json()]);
		}

		return back()->with('success', 'Thanks for contacting us!');
	}

	public function sendSignupNotification(Request $request) {

		$text = "New User Signup:\n";
		$text .= "First Name: " . $request->get('tys_first_name') . "\n";
		$text .= "Last Name: " . $request->get('tys_last_name') . "\n";
		$text .= "Email: " . $request->get('tys_email') . "\n";
		$text .= "Username: " . $request->get('tys_username') . "\n";
		$text .= "IM: " . $request->get('im_type') . "\n";
		$text .= "IM Username: " . $request->get('im_username') . "\n";
		$text .= "Mid: " . $request->get('mid') . "\n";

		$response = Http::post($this->telegramBotUrl, [
			'chat_id' => config('services.telegram.joins_chat_id'),
			'text'    => $text,
		]);

		if (!$response->ok()) {
			return response()->json(['success' => false, 'error' => $request]);
		}

		return response()->json(['success' => true]);
	}
}
