# GANG Project

## Local demo data

Run `php scripts/demo.php` from the project root, then open [the demo](http://127.0.0.1:8001).
This starts a separate local server with SQLite and its own uploads and sessions in `storage/app/demo`.
Restarting keeps your demo changes. Use `php scripts/demo.php --seed-only` to prepare the database without starting a server.

All demo accounts use the password `GangDemo!2026`.

| Login | Example accounts |
| --- | --- |
| [Super Admin](http://127.0.0.1:8001/super-admin/login) | `superadmin@demo.test` |
| [Court Owner](http://127.0.0.1:8001/admin/login) | `owner@demo.test` (active), `new@demo.test` (no application), `pending@demo.test` (under review), `rejected@demo.test` (can resubmit), `suspended@demo.test` |
| [Player](http://127.0.0.1:8001/login) | `player@demo.test`, `unverified@demo.test` (preloaded verification code `246810`), `suspended-player@demo.test`, `teammate@demo.test` |

The demo includes court listing statuses, owner applications with a downloadable file,
booking and payment states, group games and waiting lists, flexible and fixed subscriptions,
published and unavailable websites, moderation reports, and an assistant conversation.
Booking and group-game checkouts use demo payments. Live Stripe, SMS, and Gemini credentials
are excluded from the demo, and mail is written to the demo log.

## Project Management

[Trello Board](https://trello.com/b/VnELJ4DJ/courtly)

## Design

[Figma
File](https://www.figma.com/design/Ga1DsZWUEpK9qxpq9icO96/Untitled?node-id=0-1&t=Ot7QhuLpYDX8w93T-1)
