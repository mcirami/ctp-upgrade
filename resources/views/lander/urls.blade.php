@extends('layouts.master')
@section('content')
<div class="right_panel">
    <div class="white_box_outer large_table">
        <div class="heading_holder">
            <span class="lft value_span9">Lander URLs</span>
            <a href="/lander/urls/create" class="btn btn-sm btn-default">Create New</a>
        </div>
        <div class="white_box manage_aff large_table">
            <table class="table table-bordered table_01">
                <thead><tr><th>URL</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                @forelse($urls as $url)
                    <tr>
                        <td>{{ $url->url }}</td>
                        <td>{{ $url->status ? 'Active' : 'Inactive' }}</td>
                        <td><a class="btn btn-default btn-sm" href="/lander/urls/{{ $url->id }}/edit">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="3">No lander URLs yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
