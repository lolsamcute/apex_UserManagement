# User Management System API

This is a User Management System API built using Laravel. It allows you to perform CRUD operations on users with role-based authentication.

## Setup

1. Clone the repository:

git clone https://github.com/yourusername/user-management-api.git


2. Navigate to the project directory:

cd user-management-api


3. Install dependencies:

composer install


4. Create a copy of the `.env.example` file and rename it to `.env`:

cp .env.example .env


5. Generate an application key:

php artisan key:generate


6. Configure your database connection in the `.env` file:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password


7. Run database migrations to create tables:

php artisan migrate


## Seed the Database (if applicable)

If you have seeders set up to populate the database with sample data, run the following command:

php artisan db:seed


## Start the Server

To start the Laravel development server, run:

php artisan serve


By default, the server will start at `http://localhost:8000`.

## Running Tests

To run tests, execute:

php artisan test


This will run all the tests defined in the `tests` directory.
