# Antiterror Press 1.12

Editorial WordPress theme evolved from `antiterror-press` 1.11. The `at_` prefix, post types (`bpla_event`, `at_milestone`), dossier meta, page templates, and paper design tokens stay in place.

## Interactive layer

Vanilla JS in `assets/js/interact.js` talks to the existing `at/v1` REST namespace. Every route checks the `X-WP-Nonce` header (`wp_rest`).

| Route | Role |
| --- | --- |
| `GET /wp-json/at/v1/search?q=` | Live results inside the masthead search dialog |
| `GET /wp-json/at/v1/feed` | Category tabs and “load more” for the journal, archives, and search |
| `POST /wp-json/at/v1/contact` | Dialog form. The classic `admin-post.php` action `at_contact` still works without JS |

The journal feed reuses `at_news_args()`, so fighter dossiers stay out of the news list. Feed arguments are whitelisted. Staff category slugs are rejected.

The contact handler is shared (`at_contact_send`): sanitize, honeypot, consent, and a five-message hourly limit. The contest page uses ARIA tabs and opens the same dialog with the nomination already filled in.

Scroll reveals follow the section rhythm of the paper layout and stay off when `prefers-reduced-motion` is set.

## 1.14

Motion is a WordPress front-end layer, not a page-builder effect.

- `assets/css/motion.css` and `assets/js/motion.js` are registered in `wp_enqueue_scripts` as `at-motion`. The stylesheet is render-blocking and loads after `theme.css`. The script is deferred and is a dependency of `at-interact`, so `window.atMotion` exists before search and the feed run.
- The inline script in `header.php` sets `data-motion="on"` before first paint, and removes it when the visitor asks for reduced motion. `theme.css` then stops every animation.
- The first screen only translates, so the lead story stays paintable. Blocks further down fade in once, through `IntersectionObserver`.
- Replacing the journal list calls `document.startViewTransition()` around the HTML returned by `at/v1/feed`. Appending a page fades the new rows. Search results use the same enter helper.
- Cross-document View Transitions keep the logo (`view-transition-name: at-brand`) stable between WordPress templates. The customizer adds `at-quiet` so those names do not fight the preview iframe. `body.admin-bar` lifts the toast and the back-to-top button.

## 1.13

Patterns taken from the Redsign Media news template, without its jQuery plugins: a CSS trending rail, share links, previous and next stories, reading time, and an RSS enclosure with a non-adult rating. Search results and previews are `noindex`. Schema, Open Graph, and archive lists were tightened. View counting now requires the REST nonce and an hourly cap. Public user routes, XML-RPC, and pingbacks are closed. Response headers set `nosniff`, a referrer policy, `SAMEORIGIN`, and HSTS on HTTPS.

## Install

Copy the `antiterror-press` directory into `wp-content/themes/` and activate it. Existing 1.11 content, menus, and customizer values keep working.
