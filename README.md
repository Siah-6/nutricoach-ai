# NutriCoach AI 🏋️‍♂️

An AI-powered fitness coaching web application that provides personalized workout plans, meal recommendations, and 24/7 AI chat support.

## Features ✨

- **AI Chat Coach**: Get instant fitness advice powered by Google Gemini or Groq AI
- **Personalized Workout Plans**: Custom exercises based on your fitness level and goals
- **Meal Planning**: Filipino-focused meal recommendations with macro tracking
- **Progress Tracking**: Monitor your weight, workouts, and achievements
- **Gamification**: Earn XP and level up as you complete workouts
- **Mobile-Friendly**: Responsive design works on all devices

## Tech Stack 🛠️

- **Backend**: PHP 8.0+, MySQL 8.0+
- **Frontend**: Vanilla JavaScript, CSS3
- **AI**: Google Gemini API / Groq API
- **Server**: Apache (XAMPP)

## Installation 📦

### Prerequisites

- XAMPP (PHP 8.0+, MySQL 8.0+, Apache)
- Google Gemini API key OR Groq API key (free)

### Setup Steps

1. **Clone/Download** the project to your XAMPP htdocs folder:
   ```
   c:\xampp\htdocs\NutriCoachAI\
   ```

2. **Create Database**:
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Import the database schema: `database/schema.sql`

3. **Configure Application**:
   - Copy `config/config.example.php` to `config/config.php`
   - Edit `config/config.php` with your settings:
     ```php
     // Database
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'nutricoach_db');
     
     // AI API (Choose one)
     // Option 1: Gemini (Google)
     define('GEMINI_API_KEY', 'your_gemini_api_key');
     define('USE_GROQ_API', false);
     
     // Option 2: Groq (Free - Recommended)
     // Get key from: https://console.groq.com/
     define('GEMINI_API_KEY', 'your_groq_api_key');
     define('USE_GROQ_API', true);
     ```

4. **Start XAMPP**:
   - Start Apache and MySQL services

5. **Access Application**:
   - Open browser: `http://localhost/xampp/NutriCoachAI/`
   - Or if using port 3000: `http://localhost:3000/xampp/NutriCoachAI/`

## Getting API Keys 🔑

### Option 1: Groq (Recommended - Free & Fast)
1. Visit: https://console.groq.com/
2. Sign up (no credit card required)
3. Create an API key
4. Copy key to `config.php`

### Option 2: Google Gemini
1. Visit: https://makersuite.google.com/app/apikey
2. Create API key
3. Copy key to `config.php`

## Usage 📱

1. **Sign Up**: Create a new account
2. **Onboarding**: Complete your fitness profile
3. **Dashboard**: View your personalized fitness plan
4. **Workouts**: Follow daily workout routines
5. **Meals**: Get meal recommendations
6. **AI Chat**: Ask your AI coach anything
7. **Progress**: Track your fitness journey

## Project Structure 📁

```
NutriCoachAI/
├── api/                    # API endpoints
│   ├── auth/              # Authentication
│   ├── chat/              # AI chat
│   ├── fitness/           # Workout & meal plans
│   ├── user/              # User management
│   └── workout/           # Workout sessions
├── assets/                # Static files
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript
│   └── images/           # Images
├── config/               # Configuration
│   ├── config.php        # App config (create from example)
│   ├── config.example.php
│   └── database.php      # Database connection
├── database/             # Database files
│   └── schema.sql        # Database schema
├── includes/             # PHP includes
│   ├── functions.php     # Helper functions
│   ├── header.php        # Header component
│   └── footer.php        # Footer component
├── pages/                # Application pages
│   ├── dashboard.php
│   ├── chat.php
│   ├── workout-plan-improved.php
│   ├── meal-plan-new.php
│   ├── profile.php
│   ├── progress.php
│   └── onboarding.php
└── index.php             # Landing page
```

## Troubleshooting 🔧

### CSS/JS Not Loading
- Clear browser cache (Ctrl+F5)
- Check that Apache is running
- Verify file paths in browser console

### Database Connection Error
- Check MySQL is running in XAMPP
- Verify database credentials in `config/config.php`
- Ensure database is created and schema is imported

### AI Chat Not Working
- Verify API key is correct in `config/config.php`
- Check API key has not expired
- Try switching between Groq and Gemini

### Login Issues
- Clear browser cookies
- Check database `users` table exists
- Verify session configuration

## Security Notes 🔒

- Never commit `config/config.php` to version control
- Use strong passwords for production
- Enable HTTPS in production
- Keep API keys secure
- Regular database backups recommended

## Development 💻

### Adding New Features
1. Create API endpoint in `api/`
2. Add page in `pages/`
3. Update JavaScript in `assets/js/`
4. Add styles in `assets/css/`

### Database Changes
1. Update `database/schema.sql`
2. Create migration script if needed
3. Document changes in this README

## Credits 👏

- AI powered by Google Gemini / Groq
- Icons from emoji set
- Built with ❤️ for fitness enthusiasts

## License 📄

This project is for educational purposes.

## Support 💬

For issues or questions:
1. Check the Troubleshooting section
2. Review browser console for errors
3. Check XAMPP error logs

---

**Made with 💪 by NutriCoach AI Team**
