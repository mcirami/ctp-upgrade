(function () {
    'use strict';
    if (!window.Quill) return;

    var textarea = document.getElementById('announcement-body');
    var format = document.getElementById('announcement-body-format');
    var wrapper = document.getElementById('announcement-rich-text');
    var error = document.getElementById('announcement-text-error');
    var Font = Quill.import('attributors/style/font');
    Font.whitelist = ['Arial', 'Georgia', 'Verdana', 'Times New Roman', 'Courier New'];
    var Size = Quill.import('attributors/style/size');
    Size.whitelist = ['12px', '20px', '24px', '32px', '48px'];
    Quill.register(Font, true);
    Quill.register(Size, true);

    var editor = new Quill('#announcement-editor-input', {
        theme: 'snow',
        modules: {
            toolbar: '#announcement-toolbar',
            // All images must go through the validated GIF upload endpoint.
            uploader: { mimetypes: [] }
        },
        formats: ['font', 'size', 'color', 'background', 'bold', 'italic', 'underline', 'strike', 'image']
    });
    editor.clipboard.addMatcher('IMG', function (node, delta) {
        return /^\/announcements\/media\/[a-f0-9-]{36}\.gif$/.test(node.getAttribute('src') || '')
            ? delta : new (Quill.import('delta'))();
    });
    if (format.value === 'html') {
        editor.setContents(editor.clipboard.convert({ html: textarea.value }), 'silent');
    } else {
        editor.setText(textarea.value, 'silent');
    }
    editor.root.setAttribute('role', 'textbox');
    editor.root.setAttribute('aria-multiline', 'true');
    editor.root.setAttribute('aria-required', 'true');
    editor.root.setAttribute('aria-labelledby', 'announcement-body-label');
    editor.root.setAttribute('aria-describedby', 'announcement-text-help announcement-text-error');
    document.getElementById('announcement-body-label').addEventListener('click', function () { editor.focus(); });
    textarea.hidden = true;
    textarea.required = false;
    wrapper.hidden = false;

    // Keep the accessible labels on Snow's replacement dropdown controls.
    wrapper.querySelectorAll('select[aria-label]').forEach(function (select) {
        var picker = select.previousElementSibling;
        if (picker && picker.classList.contains('ql-picker')) {
            picker.querySelector('.ql-picker-label').setAttribute('aria-label', select.getAttribute('aria-label'));
        }
    });

    var gifButton = document.getElementById('announcement-insert-gif');
    var gifInput = document.getElementById('announcement-gif-file');
    var gifStatus = document.getElementById('announcement-gif-status');
    var uploading = false;
    var insertionIndex = 0;
    gifButton.addEventListener('click', function () {
        var selection = editor.getSelection();
        insertionIndex = selection ? selection.index : editor.getLength() - 1;
        gifInput.click();
    });
    gifInput.addEventListener('change', async function () {
        var file = gifInput.files[0];
        if (!file) return;
        gifInput.value = '';
        if (file.size > 10 * 1024 * 1024) {
            gifStatus.textContent = 'Please choose a GIF smaller than 10 MB.';
            return;
        }
        uploading = true;
        gifButton.disabled = true;
        editor.enable(false);
        gifStatus.textContent = 'Uploading GIF…';
        try {
            var data = new FormData();
            data.append('gif', file);
            var response = await fetch(gifButton.dataset.uploadUrl, {
                method: 'POST', credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': textarea.form.querySelector('[name="_token"]').value },
                body: data
            });
            var result = await response.json();
            if (!response.ok) throw new Error(result.errors && result.errors.gif ? result.errors.gif[0] : 'The GIF could not be uploaded. Please try again.');
            editor.enable(true);
            editor.insertEmbed(insertionIndex, 'image', result.url, 'user');
            editor.setSelection(insertionIndex + 1, 0, 'silent');
            gifStatus.textContent = 'GIF inserted. Save the announcement to publish it.';
        } catch (uploadError) {
            gifStatus.textContent = uploadError instanceof SyntaxError ? 'Upload failed. Refresh the page and try again.' : uploadError.message;
        } finally {
            uploading = false;
            gifButton.disabled = false;
            editor.enable(true);
        }
    });

    editor.on('text-change', function () {
        error.hidden = true;
        editor.root.removeAttribute('aria-invalid');
    });
    textarea.form.addEventListener('submit', function (event) {
        if (uploading) {
            event.preventDefault();
            gifStatus.textContent = 'Please wait for the GIF upload to finish before saving.';
            return;
        }
        var text = editor.getText().replace(/\n$/, '');
        var html = editor.root.innerHTML;
        var message = '';
        if (!text.replace(/[\s\u200B\uFEFF]/g, '') && !editor.root.querySelector('img')) message = 'Please enter announcement text.';
        else if (Array.from(text).length > 10000) message = 'Announcement text may not exceed 10,000 characters.';
        else if (new TextEncoder().encode(html).length > 60000) message = 'This announcement has too much formatting. Clear some formatting and try again.';
        if (message) {
            event.preventDefault();
            error.textContent = message;
            error.hidden = false;
            editor.root.setAttribute('aria-invalid', 'true');
            editor.focus();
            return;
        }
        textarea.value = html;
        format.value = 'html';
    });
}());
