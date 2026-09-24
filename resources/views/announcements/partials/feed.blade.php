@include('announcements.partials.styles')
<div class="white_box_outer">
    <div class="heading_holder"><span id="announcements-heading" class="lft value_span9">Announcements</span></div>
    <div class="clear"></div>
    <section class="white_box value_span8 announcement-feed" aria-labelledby="announcements-heading" tabindex="0">
        @forelse($announcements as $announcement)
            @include('announcements.partials.card')
        @empty
            <p class="value_span10">No announcements yet.</p>
        @endforelse
    </section>
</div>
