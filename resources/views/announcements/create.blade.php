@extends('layouts.master')
@section('content')
@include('announcements.partials.styles')
<div class="right_panel">
    <div class="white_box_outer announcement-editor">
        <div class="heading_holder"><span class="lft value_span9">Create Announcement</span></div>
        <div class="clear"></div>
        <div class="white_box value_span8 announcement-form-card">
            @include('announcements.partials.form', ['action' => route('announcements.store'), 'method' => "POST", 'submitLabel' => "Post Announcement"])
        </div>
    </div>
</div>
@endsection
