<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Deploying sequence
## Requirements
- PHP ^8.2
- Mysql ^8.*
- Composer ^2.*
- Node.js ^20.*
-------------------
## Installation
1. Clone the repository to the target server directory
2. Create a `/.env` file from `/.env.example` and fill in the necessary details
3. From CLI run `composer install`
4. From CLI run `php artisan key:generate`
5. Create a main-database and fill DB values in the `.env` file
  5.1. **[optional]** Create new Mysql server and change the `DB_STORAGE_...` block values in the `/.env` file
6. From CLI run `php artisan migrate`
7. From CLI run `php artisan db:seed`
8. From CLI run `npm install`
9. From CLI run `npm run build`

| User login           | User Role | Password   |
|----------------------|-----------|------------|
| `user@example.com`   | **user**  | `password` | 
| `admin@example.com` | **admin** | `password` |
