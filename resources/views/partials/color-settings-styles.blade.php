{{--
    Styles for the colour settings widget. Scoped to its own class names so the
    widget renders the same whichever CSS framework the host application uses,
    and so including it never collides with the host's own styles.
--}}
<style>
/* Dark mode follows the host page, not the operating system, so the widget
   never goes dark inside a light layout. Tailwind uses the .dark class and
   Bootstrap 5 uses data-bs-theme. */
.notifications-settings { --ns-border: #e5e7eb; --ns-text: #111827; --ns-muted: #6b7280; --ns-surface: #ffffff; color: var(--ns-text); }
:is(.dark) .notifications-settings,
:is([data-bs-theme="dark"]) .notifications-settings,
.notifications-settings:where(.dark, [data-bs-theme="dark"]) { --ns-border: #374151; --ns-text: #f3f4f6; --ns-muted: #9ca3af; --ns-surface: #1f2937; }
.notifications-settings [x-cloak] { display: none !important; }
.notifications-settings-grid { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); }
.notifications-settings-field { border: 1px solid var(--ns-border); border-radius: .5rem; padding: .75rem; background: var(--ns-surface); }
.notifications-settings-label { display: block; font-size: .875rem; font-weight: 600; }
.notifications-settings-hint { margin: .125rem 0 .5rem; font-size: .75rem; color: var(--ns-muted); }
.notifications-settings-controls { display: flex; align-items: center; gap: .5rem; }
.notifications-settings-swatch { inline-size: 2.5rem; block-size: 2.25rem; padding: 0; border: 1px solid var(--ns-border); border-radius: .375rem; background: none; cursor: pointer; }
.notifications-settings-hex { inline-size: 7rem; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: .8125rem; padding: .375rem .5rem; border: 1px solid var(--ns-border); border-radius: .375rem; background: var(--ns-surface); color: var(--ns-text); }
.notifications-settings-reset { font-size: .75rem; color: var(--ns-muted); background: none; border: 0; cursor: pointer; text-decoration: underline; padding: .25rem; }
.notifications-settings-subtitle { font-size: .875rem; font-weight: 600; margin: 1.5rem 0 .5rem; }
.notifications-preview-row { display: flex; align-items: center; gap: .75rem; padding: .75rem; border: 1px solid transparent; border-radius: .5rem; margin-bottom: .5rem; }
.notifications-preview-icon { display: inline-flex; align-items: center; justify-content: center; inline-size: 2.25rem; block-size: 2.25rem; border-radius: 9999px; flex: none; }
.notifications-preview-icon svg { inline-size: 1rem; block-size: 1rem; }
.notifications-preview-body { display: flex; flex-direction: column; gap: .125rem; font-size: .875rem; min-inline-size: 0; }
.notifications-preview-body span { color: var(--ns-muted); font-size: .8125rem; }
.notifications-preview-badge { margin-inline-start: auto; font-size: .6875rem; font-weight: 700; line-height: 1; padding: .25rem .4rem; border-radius: 9999px; flex: none; }
.notifications-settings-actions { margin-top: 1.25rem; }
.notifications-settings-save { font-size: .875rem; font-weight: 600; padding: .5rem 1rem; border: 0; border-radius: .5rem; background: var(--notifications-unread, #2563eb); color: #fff; cursor: pointer; }
.notifications-settings-save[disabled], .notifications-settings-reset-all[disabled] { opacity: .5; cursor: not-allowed; }
.notifications-settings-reset-all { margin-top: .75rem; font-size: .8125rem; color: var(--ns-muted); background: none; border: 0; cursor: pointer; text-decoration: underline; padding: .25rem 0; }
.notifications-settings-flash { font-size: .875rem; padding: .625rem .75rem; border-radius: .5rem; background: rgba(22, 163, 74, .12); color: #166534; margin-bottom: 1rem; }
.notifications-settings-error { font-size: .8125rem; color: #b91c1c; margin: .375rem 0 0; }
@media (prefers-reduced-motion: reduce) { .notifications-settings * { transition: none !important; } }
</style>
