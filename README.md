# Laravel Project Blog-API

## Requirements

-   PHP >= 8.0
-   Composer
-   MySQL or supported database

## Setup

1. **Clone the Repository**

    ```bash
    git clone https://github.com/Likanstha/blog-api.git
    cd blog-api
    ```

2. **Install deependency**
   composer install

3. **Generate the Application Key**
   php artisan key:generate

4. **Configure Environment**  
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=your_database_user
   DB_PASSWORD=your_database_password

5. **Run Migrations**

    php artisan migrate

## Start

6.  **Start the Laravel Server**  
    php artisan serve

7.  **Start the Queue Worker**
    php artisan queue:work

8.  **Run tests**
    php artisan test

9.  **Swagger Documentation**
    php artisan l5-swagger:generate
