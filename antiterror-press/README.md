# Антитеррор 1.12

WordPress theme for the «Антитеррор России» journal. Built on **antiterror-press v1.11**: same `at_*` helpers, templates, paper/ink design tokens, fighter directory, media vault, BPLA feed and milestone index.

## What 1.12 adds

Interactive layer stays inside the existing theme.js IIFE and `inc/interact.php`. No jQuery, no extra libraries.

- Live search overlay (`Ctrl/Cmd+K`) via `GET /wp-json/at/v1/search` with `X-WP-Nonce`
- AJAX journal filters, in-feed search and “load more” via `GET /wp-json/at/v1/feed`
- Contact popup + Fetch submit via `POST /wp-json/at/v1/contact` (classic `admin-post.php` remains the no-JS fallback)
- Tabs on the contest page; BPLA filters keep their original DOM contract
- Scroll reveal, back-to-top, in-article TOC, VK/Telegram/copy share, related stories

## Install

1. Copy the `antiterror-press` folder to `wp-content/themes/`.
2. Activate **Антитеррор** (replaces JournalX without touching its files).
3. Optional: keep ABC Pagination active for the milestone index.

Requires WordPress 6.2+ and PHP 8.1+.
