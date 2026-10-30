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
        modules: { toolbar: '#announcement-toolbar' },
        formats: ['font', 'size', 'color', 'background', 'bold', 'italic', 'underline', 'strike']
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

    editor.on('text-change', function () {
        error.hidden = true;
        editor.root.removeAttribute('aria-invalid');
    });
    textarea.form.addEventListener('submit', function (event) {
        var text = editor.getText().replace(/\n$/, '');
        var html = editor.root.innerHTML;
        var message = '';
        if (!text.replace(/[\s\u200B\uFEFF]/g, '')) message = 'Please enter announcement text.';
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
