<p align="center">
  <img src="assets/logo.png" alt="FG AutoLightbox" width="120">
</p>

<h1 align="center">FG AutoLightbox plugin for Joomla</h1>

<p align="center">
  <a href="https://github.com/FGcodework/plg_content_fgautolightbox/releases"><img src="https://img.shields.io/github/v/release/FGcodework/plg_content_fgautolightbox?color=FF6B4A&label=release" alt="Latest release"></a>
  <a href="https://github.com/FGcodework/plg_content_fgautolightbox"><img src="https://img.shields.io/badge/Joomla-6.0%2B-blue.svg" alt="Joomla"></a>
  <a href="https://github.com/FGcodework/plg_content_fgautolightbox"><img src="https://img.shields.io/badge/PHP-8.3%2B-purple.svg" alt="PHP"></a>
  <a href="LICENSE.txt"><img src="https://img.shields.io/badge/license-GPL--2.0-green.svg" alt="License"></a>
  <a href="https://github.com/FGcodework/plg_content_fgautolightbox/releases"><img src="https://img.shields.io/github/downloads/FGcodework/plg_content_fgautolightbox/total" alt="Downloads"></a>
  <a href="https://ko-fi.com/FGcodework"><img src="https://img.shields.io/badge/support-Ko--fi-F16061.svg?logo=ko-fi&logoColor=white" alt="Support on Ko-fi"></a>
</p>

A Joomla content plugin that automatically turns every image in your
articles into a lightbox gallery — with **no work required from your
content editors**. They keep inserting images exactly as they always
have; the plugin does the rest.

No jQuery, no external lightbox library, no build step. Native Joomla 6
architecture: PSR-4, constructor dependency injection via
`services/provider.php`, `WebAssetManager` for JS/CSS, PHP 8.3+ syntax
(enums, readonly properties, `match` expressions).

## Why

Most "auto lightbox" plugins either stopped receiving updates, moved
the useful bits behind a paid tier, or require editors to learn a tag
syntax like `{gallery}folder{/gallery}`. This one was built to fill that
gap: editors change nothing, administrators install one plugin.

## Features

- **Zero editor workflow change** — images inserted normally through
  TinyMCE/JCE are picked up automatically, including images an editor
  already wrapped in their own link (e.g. a "link to full-size image"
  option) — the existing link is upgraded in place rather than ignored
- **No dependencies** — self-contained vanilla JS/CSS, no jQuery, no
  build step
- Keyboard navigation (`Esc`, `←`, `→`) and touch swipe gestures (with
  correct pinch-zoom handling — the swipe handler never blocks a
  multi-finger gesture)
- Open/close animations, neighbouring-image preloading, `X / Y` counter,
  with full `prefers-reduced-motion` support
- Per-article grouping — on a category page, arrows navigate only within
  the article you clicked in
- Handles images added after page load (AJAX, infinite scroll) via
  `MutationObserver` — with an optional CSS selector to scope watching
  to just the content area, for better performance on very dynamic pages
- Lazy-load aware — configurable priority between `data-src` and
  `srcset`, since different lazy-load libraries use `data-src` for
  opposite purposes
- **Responsive images done right** — picks the best available resolution
  in order: `data-full`/`data-highres` (explicit override) → `data-src`
  → the largest candidate in `srcset` → plain `src` (or the reverse
  `data-src`/`srcset` priority, if your site's lazy-load setup needs
  it). Works with `<picture>` elements too (scans every `<source>`),
  and lets the browser pick the right image size for the viewport
  instead of always fetching the largest candidate
- Extensible beyond the built-in components (`com_content`, `com_contact`,
  `com_newsfeeds`) — add K2, Zoo, or any custom component via a setting
- Accessible: `role="dialog"`, `aria-modal`, real `<button>` controls with
  `aria-label`, a focus trap reinforced with `inert` on background
  content, `aria-live` announcements on navigation, and screen-reader
  alt text that stays present even when visible captions are turned off
- A broken image shows a clear error message instead of getting stuck
  on a permanent loading spinner

## Installation

1. Download the latest `plg_content_fgautolightbox_vX.Y.Z.zip` from
   [Releases](https://github.com/FGcodework/plg_content_fgautolightbox/releases)
2. In Joomla admin: **System → Install → Extensions**, upload the ZIP
3. Go to **System → Plugins**, search for `AutoLightbox`, and enable
   **Content - FG AutoLightbox**

That's it — existing articles work immediately, no content changes needed.

### Upgrading from an older (pre-2.0) release

Versions before 2.0 used a different internal architecture (a single
flat PHP file, supporting Joomla 3.10 through 6 from one codebase).
If a site still has one of those installed, install this version the
same way as above — Joomla treats it as a normal update to the same
plugin element, no separate uninstall step needed. Joomla 3.10 itself
is no longer supported; the last release that works there is
[v1.3.2](https://github.com/FGcodework/plg_content_fgautolightbox/releases/tag/v1.3.2),
which remains available on the Releases page but no longer receives
updates.

## Configuration

All settings are optional; the defaults are sensible for a typical site.

| Setting                            | Default                      | What it does                                                                                                                                                                                     |
| ----------------------------------- | ----------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Gallery group name                 | `autolightbox-gallery`       | Internal identifier used to group images for arrow navigation                                                                                                                                    |
| Extra link CSS class               | `autolightbox`               | Optional extra class on the generated link, for your own styling                                                                                                                                 |
| Exclude CSS classes                | *(empty)*                    | Comma-separated list; images (or their wrapping element, up to a few levels up — e.g. a `figure` or `div`) carrying any of these classes are skipped (e.g. `logo, banner, no-lightbox`)          |
| Caption under image                | Alt text                     | Alt text / file name / none                                                                                                                                                                      |
| Show caption on mobile too         | No                           | Captions are hidden on small screens by default so the image gets maximum space                                                                                                                  |
| Extra allowed contexts             | *(empty)*                    | Comma-separated contexts to process beyond the built-in ones — exact (`com_k2.item`) or a whole component via wildcard (`com_k2.*` or just `com_k2`); useful for K2, Zoo, custom components      |
| Exclude components                 | *(empty)*                    | Comma-separated component names to skip (e.g. `com_contact`)                                                                                                                                     |
| Exclude pages/URLs                 | *(empty)*                    | Comma-separated patterns matched against the page URL, with word-boundary awareness by default (`/12` matches `/12` or `/path/12`, but not `/120`); add `*` for deliberate broader matching (`/kontakt*`) |
| Allowed file extensions            | `jpg,jpeg,png,gif,webp,avif` | Only these get a lightbox. SVG is excluded by default since it can carry embedded scripts. An empty list falls back to this same safe default rather than allowing everything                   |
| Prefer srcset over data-src        | No (`data-src` wins)         | Some lazy-load libraries use `data-src` for the real full-size image (default), others use it only for a small placeholder while the real image lives in `srcset` — flip this if your lightbox opens a small/blurry thumbnail |
| Enable gallery navigation          | Yes                          | Disable for a single-image viewer — no prev/next arrows, counter, or keyboard/swipe navigation between images                                                                                    |
| Preload adjacent images            | Yes                          | Fetches the previous/next image in the background while the lightbox is open, for smoother navigation. Disable on very large galleries to skip the extra downloads                               |
| Watch for dynamically added images | No                           | Enables the `MutationObserver` for images added after page load (AJAX, infinite scroll, sliders). Off by default since it isn't free — turn it on only if your site actually needs it            |
| Watch container (CSS selector)     | *(empty)*                    | Only relevant if the above is enabled. Scope the `MutationObserver` to matching container(s) (e.g. `.item-page, .blog`) instead of the whole page, for better performance                        |

## Theming

The lightbox styles are driven by CSS custom properties, so you can
restyle it from your template's CSS without touching the plugin:

```css
#alb-overlay {
    --alb-z-index: 999999999;   /* if it clashes with a cookie bar */
    --alb-overlay-bg: rgba(20, 20, 40, 0.95);
    --alb-text-color: #fff;
    --alb-caption-color: #eee;
    --alb-caption-size: 13px;
    --alb-nav-size-mobile: 36px;
    --alb-nav-size-desktop: 56px;
    --alb-counter-bg: rgba(0, 0, 0, 0.5);
    --alb-counter-color: #ccc;
}
```

## Architecture

PSR-4 namespaced classes (`FG\Plugin\Content\Fgautolightbox\...`),
constructor dependency injection wired up in `services/provider.php`,
and Joomla's `WebAssetManager` for CSS/JS delivery — no legacy
`JPlugin`/positional-argument code paths. The core HTML-processing logic
(`HtmlProcessor` and its collaborators under `src/Support/`) has zero
Joomla API surface, so it can be unit-tested in complete isolation.
See [CHANGELOG.md](CHANGELOG.md) for the detailed history of how this
came together, including several real bugs found and fixed by testing
directly on live sites.

## Requirements

- Joomla 6.0 or later
- PHP 8.3 or later
- `dom`, `json`, `mbstring` PHP extensions (all standard Joomla 6
  requirements already)

## Conflicts with other lightboxes

If clicking an image opens a *different* lightbox than expected, another
extension is likely also grabbing images — common culprits are JCE
MediaBox, Mediabox CK, Image Effect CK, and gallery field plugins that
bundle GLightbox. Inspect the opened overlay: this plugin's markup always
uses `id="alb-overlay"`. If you see something else, disable that
extension's auto-lightbox behaviour.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for the full history, including the
classic (Joomla 3.10–6) build's history prior to the native rewrite.

## Support this project

This plugin is free, open source, and always will be — no feature is
locked behind a paywall. If it's saved your editors some manual work,
you can leave a one-off tip on Ko-fi. Entirely optional either way.

[![ko-fi](https://ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/FGcodework)

## License

GPL-2.0-or-later. See [LICENSE.txt](LICENSE.txt).
