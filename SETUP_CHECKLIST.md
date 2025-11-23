# NutriCoach AI - Setup Checklist ✅

Follow this checklist to ensure your application is properly configured.

## Pre-Installation ☑️

- [ ] XAMPP installed (PHP 8.0+, MySQL 8.0+)
- [ ] XAMPP Apache and MySQL services started
- [ ] Project files in `c:\xampp\htdocs\NutriCoachAI\`

## Database Setup ☑️

- [ ] Open phpMyAdmin: `http://localhost/phpmyadmin`
- [ ] Create database `nutricoach_db` (or import schema.sql which creates it)
- [ ] Import `database/schema.sql`
- [ ] Verify tables created: users, user_profiles, chat_history, etc.

## Configuration ☑️

- [ ] Copy `config/config.example.php` to `config/config.php`
- [ ] Update database credentials in `config/config.php`:
  - DB_HOST = 'localhost'
  - DB_USER = 'root' (or your MySQL user)
  - DB_PASS = '' (or your MySQL password)
  - DB_NAME = 'nutricoach_db'

## API Key Setup ☑️

Choose ONE option:

### Option A: Groq (Recommended - Free)
- [ ] Visit: https://console.groq.com/
- [ ] Sign up (no credit card needed)
- [ ] Create API key
- [ ] In `config/config.php`:
  - Set `GEMINI_API_KEY` to your Groq API key
  - Set `USE_GROQ_API` to `true`

### Option B: Google Gemini
- [ ] Visit: https://makersuite.google.com/app/apikey
- [ ] Create API key
- [ ] In `config/config.php`:
  - Set `GEMINI_API_KEY` to your Gemini API key
  - Set `USE_GROQ_API` to `false`

## Testing ☑️

- [ ] Open browser: `http://localhost/xampp/NutriCoachAI/`
- [ ] Landing page loads with CSS styling
- [ ] Click "Get Started" - signup modal opens
- [ ] Create test account
- [ ] Complete onboarding form
- [ ] Dashboard loads successfully
- [ ] Navigate to each page:
  - [ ] Workouts page
  - [ ] Meals page
  - [ ] AI Chat page
  - [ ] Profile page
  - [ ] Progress page
- [ ] Test AI Chat - send a message
- [ ] Test logout

## Common Issues & Solutions 🔧

### Issue: CSS/JS not loading
**Solution**: 
- Clear browser cache (Ctrl+F5)
- Check browser console for 404 errors
- Verify Apache is running

### Issue: Database connection error
**Solution**:
- Check MySQL is running in XAMPP Control Panel
- Verify credentials in `config/config.php`
- Ensure database exists

### Issue: AI Chat not responding
**Solution**:
- Verify API key is correct
- Check `USE_GROQ_API` setting matches your API provider
- Check browser console for errors

### Issue: Login fails with 404
**Solution**:
- Already fixed in latest version
- Clear browser cache
- Verify `api/auth/login.php` exists

### Issue: Redirects not working
**Solution**:
- Already fixed in latest version
- The `redirect()` function now handles dynamic paths

## Verification Commands 🔍

### Check PHP Version
Open XAMPP Shell and run:
```bash
php -v
```
Should show PHP 8.0 or higher

### Check MySQL Connection
In phpMyAdmin, run this query:
```sql
SELECT COUNT(*) FROM users;
```
Should return 0 (or number of users if you've created accounts)

### Check File Permissions
Ensure these directories are writable:
- `logs/` (for error logs)
- `uploads/` (for file uploads)

## Post-Setup ☑️

- [ ] Change default admin password (if applicable)
- [ ] Set up regular database backups
- [ ] Review security settings in `config/config.php`
- [ ] Test on different devices (mobile, tablet)
- [ ] Bookmark your local URL

## Production Deployment (Optional) ☑️

If deploying to production server:
- [ ] Set `APP_ENV` to 'production' in config
- [ ] Enable HTTPS
- [ ] Use strong database password
- [ ] Secure API keys (environment variables)
- [ ] Set up automated backups
- [ ] Configure email for password reset
- [ ] Test all features on production

## Need Help? 💬

1. Check `README.md` for detailed documentation
2. Review browser console for JavaScript errors
3. Check XAMPP error logs: `xampp/apache/logs/error.log`
4. Verify all files are in correct locations

---

**Setup Complete!** 🎉 You're ready to start your fitness journey with NutriCoach AI!
