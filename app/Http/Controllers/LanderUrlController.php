<?php

namespace App\Http\Controllers;

use App\LanderUrl;
use Illuminate\Http\Request;

class LanderUrlController extends Controller
{
    public function index()
    {
        return view('lander.urls', ['urls' => LanderUrl::orderBy('id')->get()]);
    }

    public function create()
    {
        return view('lander.form', ['lander' => new LanderUrl(['status' => true])]);
    }

    public function store(Request $request)
    {
        LanderUrl::create($this->validated($request));
        return redirect('/lander/urls');
    }

    public function edit($id)
    {
        return view('lander.form', ['lander' => LanderUrl::findOrFail($id)]);
    }

    public function update(Request $request, $id)
    {
        LanderUrl::findOrFail($id)->update($this->validated($request));
        return redirect('/lander/urls');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'url' => ['required', 'string', 'max:2048', 'url:http,https'],
            'status' => ['required', 'in:0,1'],
        ]);
    }
}
