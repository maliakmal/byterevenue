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
4. Use Redis for caching and queueing
   1. Fill the `REDIS_...` block values in the `/.env` file
5. From CLI run `php artisan key:generate`
6. Create a main-database and fill DB values in the `.env` file
   #### **[optional]** Create new Mysql server (second/archive connection for storage db)
   5.1. Fill the `DB_STORAGE_...` block values in the `/.env` file
   #### !Important: The 'storage_database' table has MyISAM engine, so make sure to set the correct engine in the `config/database.php` file 
7. From CLI run: `php artisan migrate`
8. From CLI run: `php artisan db:seed`
9. From CLI run: `npm install`
10. From CLI run: `npm run build`
11. From CLI run: `php artisan optimize:clear`
12. Start Horizon daemon: `php artisan horizon`

### Credentials

| User login           | User Role | Password   |
|----------------------|-----------|------------|
| `user@example.com`   | **user**  | `password` | 
| `admin@example.com` | **admin** | `password` |

### Notes
- The project uses Laravel Horizon. Restart the Horizon daemon after each deployment
