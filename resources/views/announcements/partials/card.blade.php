<article class="announcement-card value_span10">
    <div class="announcement-meta">
        <span class="announcement-type-badge announcement-type--{{ $announcement->type }}">{{ $announcement->typeLabel() }}</span>
        @if($announcement->is_pinned)<strong><i class="fas fa-thumbtack" aria-hidden="true"></i> Pinned</strong>@endif
        <time datetime="{{ $announcement->created_at->toIso8601String() }}">{{ $announcement->created_at->format('M j, Y g:i A') }}</time>
    </div>
    <h3 class="value_span9">{{ $announcement->title }}</h3>
    <p class="announcement-body">{{ $announcement->body }}</p>
    @if($announcement->hasAttachment())
        <p><a href="{{ route('announcements.attachment', $announcement) }}"><i class="fas fa-paperclip" aria-hidden="true"></i> Download {{ $announcement->attachment_name }}</a></p>
    @endif
    @if($managing ?? false)
        <div class="announcement-actions">
            <a href="{{ route('announcements.edit', $announcement) }}">Edit</a>
            <form method="post" action="{{ route('announcements.destroy', $announcement) }}" onsubmit="return confirm('Delete this announcement?');">
                @csrf @method('DELETE')
                <button class="btn btn-default" type="submit">Delete</button>
            </form>
        </div>
    @endif
</article>
