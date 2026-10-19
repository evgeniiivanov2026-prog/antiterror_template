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

## 1.13

Patterns taken from the Redsign Media news template, without its jQuery plugins: a CSS trending rail, share links, previous and next stories, reading time, and an RSS enclosure with a non-adult rating. Search results and previews are `noindex`. Schema, Open Graph, and archive lists were tightened. View counting now requires the REST nonce and an hourly cap. Public user routes, XML-RPC, and pingbacks are closed. Response headers set `nosniff`, a referrer policy, `SAMEORIGIN`, and HSTS on HTTPS.

## Install

Copy the `antiterror-press` directory into `wp-content/themes/` and activate it. Existing 1.11 content, menus, and customizer values keep working.
