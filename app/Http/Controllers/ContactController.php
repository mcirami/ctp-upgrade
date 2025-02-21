<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactFormRequest;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;

class ContactController extends Controller
{
    public function showContactPage(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application {

		return view('contact.show');
    }

	public function submitContactForm(ContactFormRequest $request): RedirectResponse {

		$text = "New contact form submission:\n";
		$text .= "Name: " . $request->input('name') . "\n";
		$text .= "Email: " . $request->input('email') . "\n";
		$text .= "IM: " . $request->input('im_type') . "\n";
		$text .= "IM Username: " . $request->input('im_username') . "\n";
		$text .= "Message: " . $request->input('message') . "\n";

		$botToken = config('services.telegram.bot_token');
		$chatId   = config('services.telegram.chat_id');

		// Endpoint to Telegram Bot API
		$url = "https://api.telegram.org/bot{$botToken}/sendMessage";

		$response = Http::post($url, [
			'chat_id' => $chatId,
			'text'    => $text,
		]);

		if (!$response->ok()) {
			return back()->with('error', $response->json());
		}

		return back()->with('success', 'Thanks for contacting us!');
	}
}
