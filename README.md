# OCMS – Online Course Management System with AI Assessment

OCMS is a web-based learning platform built with Laravel. Instructors build courses (chapters, videos, documents, quizzes and essays), students enrol and learn, and administrators manage users and categories. Essay answers are scored automatically by the **Google Gemini API** using an instructor-defined rubric, with a manual-grading fallback when the AI is unavailable.

## Features

| Role | What they can do |
|------|------------------|
| **Guest** | Browse and search the course catalogue, view course details, register, log in |
| **Student** | Cart, wishlist, simulated card checkout, enrol in free courses, learn chapter by chapter, take MCQ and essay assessments, see AI feedback, track progress |
| **Instructor** | Create courses, build chapters and content (video, PDF, text), create assessments (MCQ and essay with rubric), review and override AI scores, see enrolments |
| **Admin** | Manage users (activate or deactivate), manage categories, platform overview |

## Tech stack

- PHP 8.1+ and Laravel 10
- SQLite (no database server needed)
- Blade templates, Tailwind CSS 4, Vite
- Google Gemini API (AI essay scoring)

## Requirements

Install these first:

- **PHP 8.1 or newer** with the extensions `pdo_sqlite`, `sqlite3`, `mbstring`, `openssl`, `fileinfo`, `curl`, `xml`, `ctype`, `tokenizer`
  (on XAMPP/Laragon these are already enabled; otherwise uncomment them in `php.ini`)
- **Composer** 2.x – https://getcomposer.org
- **Node.js 18+ and npm** – https://nodejs.org
- **Git**
- A **Gemini API key** (optional, only needed for AI essay scoring – see [step 6](#6-get-a-gemini-api-key-for-ai-scoring))

Check your versions:

```bash
php -v
composer -V
node -v
npm -v
```

## Setup (from clone to a demo-ready site)

### 1. Clone and enter the project

```bash
git clone <repository-url> online-course-management-system
cd online-course-management-system
```

### 2. Install PHP and JavaScript dependencies

```bash
composer install
npm install
```

### 3. Create the environment file and app key

macOS / Linux / Git Bash:

```bash
cp .env.example .env
```

Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Then generate the application key:

```bash
php artisan key:generate
```

### 4. Create the database, tables and demo data

The project uses SQLite, so the database is just a file. Create an empty one:

macOS / Linux / Git Bash:

```bash
touch database/database.sqlite
```

Windows PowerShell:

```powershell
New-Item -ItemType File database/database.sqlite
```

Run the migrations **and** the seeders:

```bash
php artisan migrate:fresh --seed
```

> `migrate:fresh` drops all tables first, so this is safe on a new clone. Use it again any time you want to reset the demo data. **Do not run it on a database that holds data you want to keep.**

The seeders create (see `database/seeders/`):

| Seeder | Creates |
|--------|---------|
| `DatabaseSeeder` | The 10 demo accounts below, then calls the other seeders |
| `CategorySeeder` | 5 course categories (Design & Creative, Tech & Programming, Business & Marketing, Personal Development, Photography) |
| `CourseSeeder` | 13 sample courses with chapters, video/text/PDF content, MCQ and essay assessments, and a sample handout PDF |
| `LearningActivitySeeder` | Enrolments, progress, payments and submissions so the dashboards have data |

Course thumbnails are bundled in `public/images/courses/`, so no extra download is needed.

### 5. Link the storage folder and build the front-end assets

Makes uploaded files (thumbnails, PDFs) publicly reachable:

```bash
php artisan storage:link
```

Build the CSS/JS:

```bash
npm run build
```

### 6. Get a Gemini API key (for AI scoring)

AI scoring is used for **essay** assessments. MCQ assessments are auto-scored without AI, and everything else works without a key. Without a key, essay submissions are saved as *pending* and the instructor grades them manually.

1. Open https://aistudio.google.com/apikey and sign in with a Google account.
2. Click **Create API key** (choose or create a Google Cloud project when asked).
3. Copy the key.
4. Open `.env` and paste it:

   ```env
   GEMINI_API_KEY=your-key-here
   GEMINI_MODEL=gemini-2.5-flash
   GEMINI_FALLBACK_MODEL=gemini-2.5-flash-lite
   GEMINI_TIMEOUT=45
   ```

5. If the server is already running, clear the cached config:

   ```bash
   php artisan config:clear
   ```

| Variable | Meaning |
|----------|---------|
| `GEMINI_API_KEY` | Your key. Keep it secret, never commit `.env` |
| `GEMINI_MODEL` | Primary model used for scoring |
| `GEMINI_FALLBACK_MODEL` | Used automatically if the primary model fails |
| `GEMINI_TIMEOUT` | Request timeout in seconds |

The free tier of the Gemini API has rate limits; if scoring is rejected, wait a minute and use **Rescore** from the instructor's submission page.

### 7. Start the application

```bash
php artisan serve
```

Open **http://127.0.0.1:8000** in your browser.

If you change CSS/JS while developing, run `npm run dev` in a second terminal instead of `npm run build`.

## Demo accounts

Every account uses the password **`password`**.

| Role | Email | Name |
|------|-------|------|
| Admin | `admin@example.com` | Admin User |
| Instructor | `instructor@example.com` | Sarah Chen |
| Instructor | `john@example.com` | John Doe |
| Instructor | `emily@example.com` | Emily Rose |
| Instructor | `alex@example.com` | Alex Kim |
| Student | `student@example.com` | Ahmad Rizki |
| Student | `lisa@example.com` | Lisa Park |
| Student | `david@example.com` | David Chen |
| Student | `maya@example.com` | Maya Putri |
| Student | `budi@example.com` | Budi Santoso |

## Demo walkthrough

1. **Browse** – open the home page and `/courses`, search for "design".
2. **Student** – log in as `student@example.com`. Open *My Courses*, learn a chapter, take an assessment.
3. **Checkout** – add a paid course to the cart and check out. Payment is a *simulated* gateway, no real money is used:

   | Card number | Result |
   |-------------|--------|
   | `4242 4242 4242 4242` | Approved (Visa) |
   | `5555 5555 5555 4444` | Approved (Mastercard) |
   | `4000 0000 0000 0002` | Declined (to demo the failure flow); invalid numbers are rejected too |

   Use any future expiry date (e.g. `12/30`), any 3-digit CVC and any name.
4. **AI scoring** – as a student, submit an essay assessment. With a valid `GEMINI_API_KEY` you get a score and per-criterion feedback within seconds.
5. **Instructor** – log in as `instructor@example.com`, open the course builder, add a question to an assessment, review submissions and override an AI score.
6. **Admin** – log in as `admin@example.com`, open *Users* and *Categories*.

## Running the tests

```bash
php artisan test
```

All tests use a temporary in-memory setup and fake the Gemini API, so no API key is needed.

## Troubleshooting

| Problem | Fix |
|---------|-----|
| `could not find driver` | Enable `pdo_sqlite` and `sqlite3` in `php.ini`, restart the terminal |
| Page is unstyled or `Vite manifest not found` | Run `npm run build` |
| `No application encryption key has been specified` | Run `php artisan key:generate` |
| Uploaded images or PDFs do not show | Run `php artisan storage:link` (on Windows, run the terminal once as administrator if the link fails) |
| Port 8000 already in use | `php artisan serve --port=8001` |
| Essay submissions stay *Pending* | `GEMINI_API_KEY` is empty or invalid. Fix `.env`, run `php artisan config:clear`, then use **Rescore** |
| Want a clean slate | `php artisan migrate:fresh --seed` |

## Project structure (short)

```
app/Http/Controllers/   Admin, Instructor, Student and public controllers
app/Services/           GeminiScoringService (AI), PaymentGateway (simulated)
app/Models/             Course, Chapter, Assessment, Enrolment, ...
database/migrations/    Table definitions
database/seeders/       Demo data
resources/views/        Blade templates
routes/web.php          All routes
tests/                  Feature and unit tests
```

## License

Developed for the CPP401 Final Project. The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
