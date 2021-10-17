# AturAja-backend
AturAja adalah aplikasi scheduling sederhana dengan fitur kolaborasi jadwal

Use PHP Composer + Artisan
---

Make sure you have composer installed
```bash
> composer --version
```
If it's not installed yet, you can download [here](https://getcomposer.org/)

You also need php v 7.3 and mySQL database.
You can use [XAMPP](https://www.apachefriends.org/download.html)

How To Install This Project
===
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
6. Create database using phpmyadmin
7. Migrate database
```cmd
> php artisan migrate
```
7. Run this project
```bash
> php artisan serve
```