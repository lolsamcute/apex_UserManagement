
To run the Laravel project locally and write tests for all endpoints, follow these steps:

1. Clone the Repository or Initialize a New Laravel Project:
If you already have a Laravel project, skip to step 2.
git clone <repository_url>
cd <project_directory>
Or, if you're initializing a new Laravel project:
laravel new apex_wallet
cd apex_wallet

2. Set Up the Environment:
Copy the .env.example file to .env and configure your database connection settings.
cp .env.example .env

3. Install Dependencies:
composer install

4. Generate an Application Key:
php artisan key:generate


5. Run Migrations:
This will create the necessary database tables.
php artisan migrate

6. Serve the Application:

php artisan serve
