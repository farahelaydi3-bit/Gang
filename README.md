> 🌐 **Language:** **English** | [العربية](./README.ar.md)

# Gang

**Gang** is a platform for booking sports courts and organizing games in Amman. It brings players and court owners together so people can discover venues, compare availability and prices, book courts, and join open games.

## About the project

The project is built with Laravel. Its interface uses Blade, CSS, and JavaScript. The platform has three account types:

- **Player:** discovers courts and games, books courts, manages subscriptions, and uses the Gang assistant.
- **Court owner:** manages their venue, courts, bookings, and group games.
- **Super Admin:** manages court owners, venues, sports, and platform content.

## Features

- Browse courts and filter by sport and area.
- Find nearby courts on an interactive map.
- Book courts by selecting an available time within the venue's opening hours.
- Pay a booking deposit and manage bookings.
- Set up recurring weekly subscriptions with availability checks and invoices.
- Create and join open group games and waiting lists.
- Player and court-owner profiles, with phone verification.
- Court ratings and content moderation.
- Gang assistant for finding courts and available times.
- Public pages for court owners.

## Live demo and test accounts

- **Website:** [gang.infinityfreeapp.com](https://gang.infinityfreeapp.com/)
- **Super Admin:** [Sign in](https://gang.infinityfreeapp.com/super-admin/login) — `admin@admin.com` / `123456789`
- **Court owner:** [Sign in](https://gang.infinityfreeapp.com/admin/login) — `owner@owner.com` / `123456789`
- **Player:** [Sign in](https://gang.infinityfreeapp.com/login) — `user@user.com` / `123456789`

These are public demo credentials for presentation purposes only. Do not use them for real data or a private production environment. If the live site's demo accounts change, update this section.

## Planned GangSite direction

GangSite Studio currently lets court owners create public pages, while booking links take players to Gang's booking routes. The planned improvement is to let an owner request an AI-generated website and let players explore availability, complete a booking, and pay within one continuous, branded journey.

Publishing, hosting, booking connections, payments, security, and platform management will remain under the Gang team's control. The owner's editing permissions for venue information and page content can be defined when this change is implemented. This describes a future direction, not current behavior.

## Requirements

- PHP 8.2 or newer, with the PHP extensions required by Laravel and PDO for the database.
- Composer.
- MySQL or MariaDB for normal operation.
- Node.js and npm to build the frontend assets and run Vite.

The isolated demo mode also requires the `pdo_sqlite` extension.

## Run the project locally

1. Install the PHP and JavaScript dependencies:

   ```bash
   composer install
   npm install
   ```

2. Create the environment file and application key.

   On Windows PowerShell:

   ```powershell
   Copy-Item .env.example .env
   php artisan key:generate
   ```

   On macOS or Linux:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. Create a MySQL database named `gang`, then update the connection settings in `.env`:

   ```dotenv
   APP_NAME=Gang
   APP_URL=http://127.0.0.1:8000
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=gang
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. Run the migrations and seed the database:

   ```bash
   php artisan migrate --seed
   ```

5. Start the development environment:

   ```bash
   composer run dev
   ```

   This starts Laravel, Vite, the queue worker, and development logs. Open [http://127.0.0.1:8000](http://127.0.0.1:8000).

   To build the frontend assets without the development server:

   ```bash
   npm run build
   ```

## Main platform routes

| Page | Route |
| --- | --- |
| Home | `/` |
| Courts | `/courts` |
| Nearby courts | `/nearby` |
| Open games | `/games` |
| Create a booking | `/bookings/create` |
| My bookings | `/bookings` |
| My subscriptions | `/my-subscriptions` |
| Assistant | `/assistant` |
| Court-owner sign in | `/admin/login` |
| Super Admin sign in | `/super-admin/login` |

## Payments and external services

The default settings in `.env.example` use the demo payment gateway:

```dotenv
PAYMENT_GATEWAY=demo
PAYMENT_DEMO_ENABLED=true
```

Demo payments are for development and do not charge real money. Optional external services can be configured in `.env` when needed:

- `GEMINI_API_KEY` for the Gang assistant and Gemini tools.
- `TEXTBEE_API_KEY` and `TEXTBEE_DEVICE_ID` to send SMS through TextBee.
- Stripe keys when payment is configured for a suitable environment.

Do not add API keys, production passwords, or private credentials to Git or the README. The public demo accounts above are an intentional exception for presentations and must not be used as production accounts.

## Useful commands

```bash
php artisan route:list       # List routes
php artisan migrate:status   # Show migration status
php artisan test             # Run tests
npm run dev                  # Run Vite during development
npm run build                # Build frontend assets
```

## Project files

- `app/Http/Controllers` — page, booking, and payment logic.
- `app/Models` — database models.
- `app/Services` — availability, subscription, and integration logic.
- `resources/views` — Blade templates.
- `public/courtbook-assets` — Gang interface styles, images, and assets.
- `routes` — player, court-owner, and Super Admin routes.
- `database/migrations` — database structure changes.

## Project links

- [Trello board](https://trello.com/b/VnELJ4DJ/courtly)
- [Figma file](https://www.figma.com/design/Ga1DsZWUEpK9qxpq9icO96/Untitled?node-id=0-1&t=Ot7QhuLpYDX8w93T-1)
