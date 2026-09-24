@extends('layouts.master')
@section('content')
@include('announcements.partials.styles')
<div class="right_panel">
    <div class="white_box_outer">
        <div class="announcement-heading"><h1 class="value_span9">Announcements</h1><a class="btn btn-default value_span11 value_span2 value_span4" href="{{ route('announcements.create') }}">Create Announcement</a></div>
        @if(session('announcement_saved'))<div class="alert alert-success" role="status">{{ session('announcement_saved') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
        <div class="white_box value_span8">
            @forelse($announcements as $announcement)
                @include('announcements.partials.card', ['managing' => true])
            @empty
                <p class="value_span10">No announcements yet. Create one to share it with your agents and affiliates.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
