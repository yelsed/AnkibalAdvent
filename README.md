# 🎄 AnkiBalAdvent - Advent Calendar Application

A beautiful, interactive advent calendar application built with Laravel 12 and Svelte 5, featuring festive pink theming, confetti animations, and a mobile-first design.

## ✨ Features

- **Multi-User Support**: Each user can create and manage multiple advent calendars
- **31-Day Calendars**: Extended advent calendar covering the full month of December
- **Three Gift Types**:
  - Text messages
  - Image with text
  - Redeemable product/coupon cards
- **Smart Unlocking**: Days can only be unlocked in December, up to the current date
- **Confetti Celebrations**: Animated confetti when unlocking a day
- **Admin Management**: Admins can manage gift content for all calendars
- **Mobile-First Design**: Fully responsive with touch-friendly interactions
- **Festive Pink Theme**: Warm, motherly aesthetic with pink color palette

## 🛠️ Tech Stack

### Backend
- **Laravel 12** - PHP framework
- **MySQL** - Database
- **Inertia 2.0** - Modern monolith approach
- **Pest 4** - Testing framework with browser test support

### Frontend
- **Svelte 5** - Reactive UI framework with runes
- **Tailwind CSS 4** - Utility-first styling
- **shadcn-svelte** - UI component library
- **vaul-svelte** - Bottom sheet modals
- **canvas-confetti** - Celebration animations

## 📦 Installation

### Prerequisites
- PHP 8.3+
- Node.js 18+
- MySQL
- Composer

### Setup Steps

1. **Clone the repository**
```bash
git clone <repository-url>
cd AnkiBalAdvent
```

2. **Install PHP dependencies**
```bash
composer install
```

3. **Install Node dependencies**
```bash
npm install
```

4. **Configure environment**
```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` with your MySQL database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=advent_calendar
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. **Run migrations**
```bash
php artisan migrate
```

6. **Create storage symlink**
```bash
php artisan storage:link
```

7. **Build frontend assets**
```bash
npm run build
# Or for development:
npm run dev
```

8. **Start the development server**
```bash
php artisan serve
```

Visit `http://localhost:8000` to view the application.

## 🎨 Usage

### Creating a Calendar

1. Register and log in to the application
2. Navigate to the Calendars page
3. Click "Create New Calendar"
4. Fill in the calendar details (title, year, description, theme color)
5. The system automatically creates 31 days for your calendar

### Unlocking Days

- Days can only be unlocked in December
- You can unlock days from 1 up to the current day of the month
- Click on a day card to unlock and reveal the gift
- Enjoy the confetti animation! 🎉

### Admin Management

Users with admin privileges can:
- Access the management interface for any calendar
- Edit gift content for all 31 days
- Upload images for image_text gift types
- Create product/coupon cards

To make a user an admin, update the database:
```sql
UPDATE users SET is_admin = 1 WHERE email = 'admin@example.com';
```

## 🧪 Testing

Run the test suite:

```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test --filter=Calendar
php artisan test --filter=Admin

# Run with coverage
php artisan test --coverage
```

### Test Coverage

- **Feature Tests**: Calendar CRUD, day unlocking logic, authorization
- **Browser Tests**: End-to-end user flows (note: require browser setup)
- **Unit Tests**: Model methods and business logic

## 📁 Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── CalendarController.php      # Calendar CRUD
│   │   ├── CalendarDayController.php   # Day unlocking & updates
│   │   └── AdminController.php         # Admin management
│   └── Requests/
│       ├── StoreCalendarRequest.php
│       └── UpdateCalendarDayRequest.php
├── Models/
│   ├── Calendar.php                     # Calendar model with relationships
│   ├── CalendarDay.php                  # Day model with unlock logic
│   └── User.php
└── Policies/
    └── CalendarPolicy.php               # Authorization rules

resources/
└── js/
    ├── components/
    │   └── calendar/
    │       ├── ConfettiEffect.svelte    # Confetti animations
    │       ├── DayCard.svelte           # Day box with gift wrap
    │       ├── DayModal.svelte          # Modal for viewing gifts
    │       └── GiftContent.svelte       # Gift type renderer
    └── pages/
        ├── Calendars/
        │   ├── Index.svelte             # Calendar list
        │   └── Show.svelte              # Calendar grid view
        └── Admin/
            └── CalendarDays.svelte      # Admin management

database/
├── factories/
│   ├── CalendarFactory.php
│   └── CalendarDayFactory.php
├── migrations/
│   ├── *_add_is_admin_to_users_table.php
│   ├── *_create_calendars_table.php
│   └── *_create_calendar_days_table.php
└── seeders/
    └── CalendarSeeder.php
```

## 🎯 Key Features Explained

### Gift Wrap Design

Locked days display a beautiful gift wrap pattern with:
- Diagonal striped background (pink tones)
- Ribbon overlay (horizontal & vertical)
- Bow decoration in the center
- Lock icon to indicate unavailability

### Date Validation

The application enforces smart unlocking rules:
- Only works in December (`month === 12`)
- Can only unlock days 1 through current day
- Server-side validation prevents cheating
- Clear error messages for users

### Confetti Animation

When unlocking a day:
1. Multiple confetti bursts from different angles
2. Custom pink color palette matching the theme
3. 3-second animation duration
4. Triggered automatically on first unlock

## 🔧 Development

### Code Quality

The project uses:
- **Laravel Pint** for PHP code formatting
- **ESLint** for JavaScript linting
- **Prettier** for JavaScript formatting

Run formatters:
```bash
# PHP
vendor/bin/pint

# JavaScript
npm run lint
npm run format
```

### AI-Assisted Development

This project includes:
- `.cursorrules` file with project-specific guidelines
- Laravel Boost MCP server for enhanced AI assistance
- Svelte MCP server for component development

## 🚀 Deployment

### Production Build

1. Build optimized assets:
```bash
npm run build
```

2. Optimize Laravel:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

3. Set proper permissions:
```bash
chmod -R 755 storage bootstrap/cache
```

4. Configure your web server (Nginx/Apache) to point to `public/` directory

### Environment Configuration

Key production settings in `.env`:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql

# Storage
FILESYSTEM_DISK=public

# Cache
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

## 📝 License

This project is open-source software.

## 🙏 Acknowledgments

- Laravel framework team
- Svelte team
- shadcn-svelte component library
- canvas-confetti for amazing animations
- All contributors and testers

## 📞 Support

For questions or issues, please create an issue in the repository.

---

Made with ❤️ for creating memorable advent calendar experiences


# AnkibalAdvent
