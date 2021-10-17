# AturAja-backend
AturAja adalah aplikasi scheduling sederhana dengan fitur kolaborasi jadwal

How To Install This Project
===

Use PHP Composer + Artisan
---
1. Clone this project

2. Install this Project Dependencies
```bash
> composer install
```

3. Replace the existing .env with the given .env

4. Generate JWT Secret Key
```bash
> php artisan jwt:secret
```
5. Run MySQL database using XAMPP control panel

6. Migrate database
```cmd
> php artisan migrate
```

7. Run this project
```bash
> php artisan serve
```
