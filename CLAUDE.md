# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Environment

Start the local server (PHP 8.2 + Apache on port 8080, Mailpit on port 8026):
```bash
docker compose up
```

Access the site at `http://localhost:8080`. Test emails are captured by Mailpit at `http://localhost:8026`.

No build step is required for most of the site. The only exception is the SDGs section, which uses Tailwind CSS:
```bash
# From the sdgs/ directory
npx tailwindcss -i input.css -o output.css --watch
```

## Architecture

This is a **static HTML website with a PHP contact form** — not a framework-based application.

- **Static pages**: `index.html`, `about/index.html`, `powder-processing/index.html`, `steelmaking-auxiliary-materials/index.html`, `greening-materials/index.html`, `privacy/index.html`
- **Assets**: `assets/css/style.css` (main stylesheet, CSS custom properties), `assets/js/main.js` (vanilla JS), `assets/img/` (JPG + WebP pairs)
- **Contact form**: Multi-step PHP flow in `contact/` (see below)
- **SDGs section**: `sdgs/` is a self-contained subsection with its own Tailwind CSS, CSS, and JS

### Contact Form Flow

The form uses session-based CSRF protection and a PRG (Post/Redirect/Get) pattern:

1. `contact/index.php` — form input, CSRF token issued
2. `contact/confirm/index.php` — server-side validation + confirmation display
3. `contact/send.php` — sends emails (company notification + customer auto-reply), redirects
4. `contact/thanks/index.php` — success page
5. `contact/error/index.php` — error page

Shared logic lives in `contact/_lib.php` (validation, SMTP send, session helpers). PHP partials (header/footer) are in `contact/_parts/`. Direct access to `_lib.php` is blocked by the `NAKAKO_CONTACT` constant guard.

Email is sent via a custom raw SMTP implementation using `fsockopen` — not PHP's `mail()`. SMTP is configured via environment variables (`SMTP_HOST`, `SMTP_PORT`) set in `docker-compose.yaml`.

### Apache Configuration

`.htaccess` handles:
- HTTP Basic authentication (user/group files referenced by path)
- Automatic WebP image delivery for supporting browsers via `mod_rewrite`

The `.htaccess` file in the repo root is named `_.htaccess` and must be renamed or deployed as `.htaccess` on the server.

## Technology Stack

- **HTML/CSS/JS**: Vanilla — no framework except Tailwind in `sdgs/`
- **PHP**: 8.2, procedural, no Composer dependencies
- **CSS design tokens**: defined as custom properties at the top of `assets/css/style.css` (colors, breakpoint at 768px, container width 1200px)
- **Icons**: Material Symbols Rounded (loaded from Google CDN)
- **Fonts**: Noto Sans JP (body), Noto Serif JP (headings), loaded from Google Fonts