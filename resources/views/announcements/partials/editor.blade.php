<link rel="stylesheet" href="{{ asset('vendor/quill/quill.snow.css') }}">
<div id="announcement-rich-text" hidden>
    <div id="announcement-toolbar" aria-label="Text formatting">
        <span class="ql-formats">
            <select class="ql-font" aria-label="Font">
                <option value="" selected>Default font</option>
                <option value="Arial">Arial</option>
                <option value="Georgia">Georgia</option>
                <option value="Verdana">Verdana</option>
                <option value="Times New Roman">Times New Roman</option>
                <option value="Courier New">Courier New</option>
            </select>
            <select class="ql-size" aria-label="Font size">
                <option value="12px">12 px</option>
                <option value="" selected>16 px</option>
                <option value="20px">20 px</option>
                <option value="24px">24 px</option>
                <option value="32px">32 px</option>
                <option value="48px">48 px</option>
            </select>
        </span>
        <span class="ql-formats">
            <button type="button" class="ql-bold" aria-label="Bold"></button>
            <button type="button" class="ql-italic" aria-label="Italic"></button>
            <button type="button" class="ql-underline" aria-label="Underline"></button>
            <button type="button" class="ql-strike" aria-label="Strikethrough"></button>
        </span>
        <span class="ql-formats">
            <select class="ql-color" aria-label="Text color"></select>
            <select class="ql-background" aria-label="Highlight color"></select>
            <button type="button" class="ql-clean" aria-label="Clear formatting"></button>
        </span>
    </div>
    <div class="announcement-gif-controls">
        <button type="button" id="announcement-insert-gif" data-upload-url="{{ route('announcements.upload-gif') }}">Insert GIF</button>
        <input type="file" id="announcement-gif-file" accept="image/gif,.gif" hidden>
        <span id="announcement-gif-status" role="status" aria-live="polite">GIF files up to 10 MB.</span>
    </div>
    <div id="announcement-editor-input"></div>
    <p id="announcement-text-help">Select text to change its font, size, or color. Maximum 10,000 characters.</p>
    <p id="announcement-text-error" role="alert" hidden></p>
</div>
<script src="{{ asset('vendor/quill/quill.js') }}"></script>
<script src="{{ asset('js/announcement-editor.js').'?v=2' }}"></script>
