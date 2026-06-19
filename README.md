# PawFinder - Missing & Stray Animals System

Modern PHP MVC + MySQL web app for reporting missing pets and found/stray animals.

## Stack
HTML5, CSS3, Bootstrap 5, JavaScript ES6, PHP MVC, MySQL

## Setup
1. Import `database/pawfinder.sql` in phpMyAdmin.
2. Put folder in `htdocs` if using XAMPP/WAMP.
3. Edit `config/database.php` if needed.
4. Open: `http://localhost/pawfinder/public/`

## Demo Login
Admin: admin@pawfinder.test / admin123
User: user@pawfinder.test / user123

## Features
- Landing page
- Missing animal reports
- Found/stray animal reports
- Search/filter
- Upload animal photo
- Sighting reports
- User login/register
- User dashboard
- Admin dashboard
- Report approval/status
- Friendly orange/yellow UI


## Latest updates
- Added admin success notifications after approval/status updates.
- Added login/register password hide/show toggle.
- Added optional sighting photo upload.
- Added other contact information field when submitting missing/found reports.

### Existing database update
If you already imported the old SQL, run this in phpMyAdmin SQL tab:

```sql
ALTER TABLE reports ADD COLUMN owner_contact VARCHAR(255) NULL AFTER location;
ALTER TABLE sightings ADD COLUMN photo VARCHAR(255) NULL AFTER note;
```
