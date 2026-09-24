@if($errors->any())
    <div class="alert alert-danger" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<form class="announcement-form value_span10" action="{{ $action }}" method="post" enctype="multipart/form-data">
    @csrf
    @if(($method ?? 'POST') !== 'POST') @method($method) @endif
    <div class="form-group">
        <label for="announcement-title">Title *</label>
        <input class="form-control" id="announcement-title" name="title" type="text" maxlength="150" placeholder="e.g. New offer live — TechGear Pro" value="{{ old('title', $announcement->title ?? '') }}" required>
    </div>
    <fieldset class="announcement-types"><legend>Announcement Type *</legend>
        <div class="announcement-type-options">
        @foreach(['new_offer'=>'New Offer', 'bonus'=>'Bonus', 'info'=>'Info', 'payments'=>'Payments', 'other'=>'Other'] as $value=>$label)
            <label class="announcement-option announcement-type--{{ $value }}"><input type="radio" name="type" value="{{ $value }}" @checked(old('type', $announcement->type ?? 'new_offer') === $value) required><span class="announcement-type-choice"><span class="announcement-type-dot" aria-hidden="true"></span>{{ $label }}</span></label>
        @endforeach
        </div>
    </fieldset>
    <div class="form-group">
        <label for="announcement-body">Announcement Text *</label>
        <textarea class="form-control" id="announcement-body" name="body" rows="7" maxlength="10000" required>{{ old('body', $announcement->body ?? '') }}</textarea>
    </div>
    <div class="form-group">
        <label for="announcement-attachment">Attachment (optional, maximum 10 MB)</label>
        <input id="announcement-attachment" name="attachment" type="file">
        @if(isset($announcement) && $announcement->hasAttachment())
            <p>Current attachment: <a href="{{ route('announcements.attachment', $announcement) }}">{{ $announcement->attachment_name }}</a></p>
            <label class="announcement-checkbox"><input type="checkbox" name="remove_attachment" value="1" @checked(old('remove_attachment'))> Remove the current attachment</label>
        @endif
    </div>
    <label class="announcement-checkbox"><input type="checkbox" name="is_pinned" value="1" @checked(old('is_pinned', $announcement->is_pinned ?? false))> Pin this announcement to the top</label>
    <div class="announcement-actions">
        <button class="announcement-submit" type="submit">{{ $submitLabel }}</button>
        <a href="{{ route('announcements.index') }}">Cancel</a>
    </div>
</form>
