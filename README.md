# Student Academic Assistance System

A Laravel-based academic management and student support platform designed to help students organize their academic activities, plan study sessions, practice through quizzes, and monitor their progress. The system provides dedicated functionality for students, teachers, and administrators.

## Features

### Student Portal
- Student dashboard and profile management
- Academic records for assignments, exams, attendance, and marks
- AI assistant interface for academic questions
- Study-plan generation and task tracking
- Quiz generation, attempts, and result review
- Academic document uploads

### Teacher Portal
- Teacher dashboard and profile management
- Course and student information
- Attendance and marks management
- Assignment and exam management

### Administrator Portal
- User management and account status controls
- Department and subject management
- Student records and student details
- Activity log viewing

### Authentication and Security
- User registration and login
- Email verification using one-time codes
- Password reset functionality
- Google sign-in integration
- Role-based access control
- Authentication and email-verification middleware

## Technology Stack

- **Backend:** PHP 8.2+ and Laravel 12
- **Database:** MySQL (configurable)
- **Frontend:** Laravel Blade, JavaScript, CSS
- **Frontend tooling:** Vite and npm
- **Authentication:** Laravel authentication and Socialite
- **AI integration:** Gemini service configuration

## Requirements

Before installing the project, ensure that the following are available:

- PHP 8.2 or newer compatible with the project
- Composer
- Node.js and npm
- MySQL
- Git

## Installation and Setup

### 1. Clone the Repository

```bash
git clone https://github.com/raja5757/student-academic-assistance.git
cd student-academic-assistance
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Configure Environment Variables

Copy the example environment file.

**Windows PowerShell:**

```powershell
Copy-Item .env.example .env
```

**macOS/Linux:**

```bash
cp .env.example .env
```

Configure the database, email settings, Google OAuth credentials, and other required variables in `.env`.

Keep real credentials in the local `.env` file. Never commit API keys, passwords, or private OAuth secrets to GitHub.

### 4. Generate the Application Key

```bash
php artisan key:generate
```

### 5. Configure the Database

Create the database specified in your `.env` configuration. Then run:

```bash
php artisan migrate
```

Review the database seeders and required initial records, such as roles, departments, and semesters, when setting up a fresh database.

### 6. Install Frontend Dependencies

```bash
npm install
npm run build
```

### 7. Start the Development Server

```bash
php artisan serve
```

Open the local URL printed by Laravel, usually:

`http://127.0.0.1:8000`

## Environment Configuration

The `.env.example` file includes configuration placeholders for:

- `DB_*` — database connection
- `MAIL_*` — SMTP and email delivery
- `GEMINI_API_KEY` — Gemini API configuration
- `GEMINI_MODEL` — configured Gemini model
- `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET` — Google OAuth
- `GOOGLE_REDIRECT_URI` — OAuth callback URL

Use valid credentials for the intended environment. Never put real secrets in source code or documentation.

## Testing

Run the Laravel test suite using:

```bash
php artisan test
```

## Development Note

The current Gemini service implementation contains a mock response path that returns before reaching the HTTP request code. Real Gemini API requests will therefore require reviewing and correcting that implementation.

## Security

- Keep `.env` excluded from version control.
- Never publish API keys, OAuth secrets, or email app passwords.
- Use secure application settings in production.
- Set `APP_DEBUG=false` in production.
- Configure production OAuth redirect URLs correctly.

## License

The license for this project should be explicitly confirmed before redistribution.
