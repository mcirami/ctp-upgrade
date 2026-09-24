<style>
.announcement-feed{max-height:500px;overflow-y:auto;overscroll-behavior:contain;margin-bottom:30px}
.announcement-card{padding:20px 0;border-bottom:1px solid #dce1eb;overflow-wrap:anywhere}
.announcement-card:first-child{padding-top:0}.announcement-card:last-child{border-bottom:0;padding-bottom:0}
.announcement-meta{display:flex;gap:12px;flex-wrap:wrap;font-size:13px}.announcement-meta time{margin-left:auto}
.announcement-card h3{font-size:20px;margin:12px 0}.announcement-body{white-space:pre-wrap;overflow-wrap:anywhere}
/* Shared type colors keep the picker and published badges consistent. */
.announcement-type--new_offer{--announcement-type-color:#129c63;--announcement-type-bg:#e2f3eb}
.announcement-type--bonus{--announcement-type-color:#bc7400;--announcement-type-bg:#fff3db}
.announcement-type--info{--announcement-type-color:#3474dd;--announcement-type-bg:#e9f0ff}
.announcement-type--payments{--announcement-type-color:#8250d9;--announcement-type-bg:#f1eaff}
.announcement-type--other{--announcement-type-color:#737373;--announcement-type-bg:#efefef}
.announcement-type-badge{display:inline-flex;align-items:center;padding:5px 12px;border-radius:999px;background:var(--announcement-type-bg,#efefef);color:var(--announcement-type-color,#737373);font-size:12px;font-weight:700;line-height:1.4;text-transform:uppercase;letter-spacing:.3px}
.right_panel .announcement-editor{max-width:960px;width:94%}
.announcement-editor .announcement-form-card{padding:30px;border-radius:10px;box-sizing:border-box}
.announcement-form{width:100%;margin:0}
.announcement-form .form-group{margin-bottom:26px}
.announcement-form .form-group>label{display:block;margin-bottom:9px;font-weight:600}
.announcement-form input[type=text],.announcement-form textarea{width:100%;max-width:100%;box-sizing:border-box;border:1px solid #dce1eb;border-radius:6px;padding:12px 14px;font:inherit;line-height:1.5;color:#434957;background:#fff;box-shadow:none}
.announcement-form input[type=text]{height:46px}.announcement-form textarea{min-height:170px;resize:vertical}
.announcement-form input[type=text]:focus,.announcement-form textarea:focus{border-color:#129c63;outline:2px solid #129c6333;outline-offset:1px}
.announcement-form .announcement-types{min-width:0;margin:0 0 26px;padding:0 0 26px;border:0;border-bottom:1px solid #dce1eb}
.announcement-form .announcement-types legend{width:auto;margin:0 0 12px;padding:0;border:0;color:inherit;font-size:15px;font-weight:600}
.announcement-type-options{display:flex;flex-wrap:wrap;gap:10px}
.announcement-form .announcement-option{position:relative;display:inline-flex;margin:0;cursor:pointer;font-weight:600}
.announcement-form .announcement-option input[type=radio]{position:absolute;width:1px;height:1px!important;margin:0;padding:0;opacity:0;overflow:hidden}
.announcement-type-choice{display:inline-flex;align-items:center;gap:8px;padding:10px 16px;border:1px solid #dce1eb;border-radius:999px;color:var(--announcement-type-color);line-height:1.4;transition:background .15s,border-color .15s}
.announcement-type-dot{width:9px;height:9px;flex:0 0 9px;border-radius:50%;background:currentColor}
.announcement-option input:checked+.announcement-type-choice{border-color:var(--announcement-type-color);background:var(--announcement-type-bg);box-shadow:inset 0 0 0 1px var(--announcement-type-color)}
.announcement-option:hover .announcement-type-choice{background:var(--announcement-type-bg)}
.announcement-option input:focus-visible+.announcement-type-choice{outline:2px solid var(--announcement-type-color);outline-offset:3px}
.announcement-form input[type=file]{display:block;width:100%;max-width:100%;padding:12px;border:1px dashed #cbd2df;border-radius:6px;box-sizing:border-box;font:inherit;background:transparent;color:inherit}
.announcement-form input[type=file]::file-selector-button{margin-right:12px;padding:8px 12px;border:1px solid #dce1eb;border-radius:4px;background:#f4f6f8;color:#434957;cursor:pointer}
.announcement-form .announcement-checkbox{display:flex;align-items:center;gap:10px;margin:14px 0;font-weight:normal;cursor:pointer}
.announcement-form .announcement-checkbox input[type=checkbox]{flex:0 0 16px;width:16px;height:16px!important;margin:0;accent-color:#129c63}
.right_panel .announcement-form .announcement-submit{width:auto;max-width:100%;margin:0;padding:12px 22px;border:0;border-radius:6px;background:#00994d;color:#fff;font-size:15px;font-weight:600;line-height:1.5;white-space:normal}
.right_panel .announcement-form .announcement-submit:hover{background:#007e3f}
.right_panel .announcement-form .announcement-submit:focus-visible{outline:2px solid #007e3f;outline-offset:3px}
.announcement-form .announcement-actions{padding-top:22px;border-top:1px solid #dce1eb}
@media(max-width:600px){.announcement-editor .announcement-form-card{padding:20px}.announcement-type-choice{padding:9px 12px}.announcement-meta time{width:100%;margin-left:0}}
.announcement-actions{display:flex;gap:15px;align-items:center;flex-wrap:wrap;margin-top:20px}
.announcement-actions form{margin:0}.announcement-heading{display:flex;align-items:center;justify-content:space-between;gap:15px;flex-wrap:wrap;margin-bottom:20px}
</style>
