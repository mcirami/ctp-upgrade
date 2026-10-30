@extends('layouts.master')
@section('content')
<div class="right_panel">
    <div class="white_box_outer">
        <div class="heading_holder"><span class="value_span9">{{ $lander->exists ? 'Edit' : 'Add' }} Lander URL</span></div>
        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif
        <form method="post" action="{{ $lander->exists ? '/lander/urls/'.$lander->id : '/lander/urls' }}">
            @csrf
            @if($lander->exists) @method('PUT') @endif
            <div class="form-group">
                <label for="url">URL</label>
                <input class="form-control" type="url" id="url" name="url" maxlength="2048" required value="{{ old('url', $lander->url) }}" placeholder="https://example.com/slug/">
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select class="form-control" id="status" name="status">
                    <option value="1" @selected(old('status', (int) $lander->status) == 1)>Active</option>
                    <option value="0" @selected(old('status', (int) $lander->status) == 0)>Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-default">Save</button>
            <a href="/lander/urls" class="btn btn-default">Cancel</a>
        </form>
    </div>
</div>
@endsection
