@extends('layouts.master')
@section('content')
@include('announcements.partials.styles')
<div class="right_panel">
    <div class="white_box_outer announcement-editor">
        <div class="heading_holder"><span class="lft value_span9">Edit Announcement</span></div>
        <div class="clear"></div>
        <div class="white_box value_span8 announcement-form-card">
            @include('announcements.partials.form', ['action' => route('announcements.update', $announcement), 'method' => "PUT", 'submitLabel' => "Save Changes"])
        </div>
    </div>
</div>
@endsection
