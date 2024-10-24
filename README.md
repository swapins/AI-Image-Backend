# Laravel Backend for SaaS Image Generation Application

This repository contains the backend API built with Laravel for a SaaS application that allows users to upload images and generate variations using a generative AI API. The application provides user authentication, image uploads, image generation, and API endpoints for communication with the frontend built in Next.js.

## Table of Contents
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
  - [Mail Configuration](#mail-configuration)
  - [OpenAI API Setup](#openai-api-setup)
  - [Pusher Configuration](#pusher-configuration)
- [Running the Application](#running-the-application)
- [Queue Setup](#queue-setup)
- [API Documentation](#api-documentation)
- [Database Design](#database-design)
- [Testing](#testing)
- [Job Queues](#job-queues)
- [Performance Considerations](#performance-considerations)
- [Bonus Features](#bonus-features)

## Features
- **User Authentication**: User registration, login, and access control using Laravel's built-in authentication.
- **Image Upload**: Upload PNG or JPG images to cloud storage (e.g., AWS S3).
- **Generative AI API Integration**: Generate image variations using a generative AI service OpenAI. 
- **User Dashboard API**: Provide endpoints for users to view, manage, and download uploaded and generated images.
- **Role-Based Access Control (RBAC)**: Admin users can view all images, while regular users can view only their own.
- **Job Queues**: Handle image generation in the background using Laravel Queues for efficient processing.
- **Real-Time Updates**: Use Pusher for real-time updates during image generation.

## Requirements
- PHP 8.0 or higher
- Composer
- MySQL
- Laravel 11.x
- AWS S3 (or any other cloud storage for images)
- Redis (for queues)
- Docker (optional, for containerization)
- Pusher (for real-time notifications)
- OpenAI API Key (for generative AI)

## Installation

### 1. Clone the Repository
```bash
git https://github.com/swapins/AI-Image-Backend.git
cd AI-Image-Backend
```

### 2. Install Dependencies
Run the following command to install Laravel and its dependencies:
```bash
composer install
```

### 3. Set Up Environment Variables
Copy the `.env.example` file to `.env` and update the following variables:
```bash
cp .env.example .env
```

- **Database Configuration**: Update the `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` values to your own:
```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

- **AWS S3 Configuration** (for storing uploaded and generated images):
```bash
AWS_ACCESS_KEY_ID=your_aws_access_key
AWS_SECRET_ACCESS_KEY=your_aws_secret_key
AWS_DEFAULT_REGION=your_region
AWS_BUCKET=your_s3_bucket_name
```

### 4. Generate Application Key
Generate a new application key by running:
```bash
php artisan key:generate
```

### 5. Run Migrations
Run database migrations to create the necessary tables:
```bash
php artisan migrate
```

### 6. Install Docker (Optional)
If you prefer to use Docker for containerization, ensure you have Docker installed and set up by following [Docker's official installation guide](https://docs.docker.com/get-docker/).

## Configuration

### Mail Configuration
To set up a mail client for your Laravel application, you can use services like SMTP, Mailgun, or SendGrid. Update the following mail configuration variables in your `.env` file:

```bash
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mail_username
MAIL_PASSWORD=your_mail_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```
Make sure you replace the `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, and `MAIL_PASSWORD` with your actual SMTP service credentials.

### OpenAI API Setup
To integrate the OpenAI API for generating image variations, you need to provide your OpenAI API key in the `.env` file:

```bash
OPENAI_API_KEY=your_openAI_key
```

This key will be used to interact with the OpenAI API for generating image variations from uploaded images. due to restrictions the demo will proceed with a mocked version if Daily limits of OpenAI key is not met.

### Pusher Configuration
Pusher is used to implement real-time updates, allowing users to see the progress of their image generation in real time. Add the Pusher credentials in the `.env` file:

```bash
PUSHER_APP_ID=1884805
PUSHER_APP_KEY=15483f78c495d25ba602
PUSHER_APP_SECRET=7d31f40d802a6a0acae0
PUSHER_APP_CLUSTER=ap2
PUSHER_APP_DEBUG=true
```

You can sign up for a Pusher account and get your credentials from the [Pusher dashboard](https://dashboard.pusher.com/).

### Queue Setup
To handle image generation in the background, you need to configure a queue system (e.g., Redis). You can install and configure Redis with Laravel as follows:

1. **Install Redis**:
   Follow the official Redis installation instructions for your environment, or use Docker to run Redis locally:
   ```bash
   docker run --name redis -d redis
   ```

2. **Configure Laravel Queues**:
   In your `.env` file, set the queue driver to `redis`:
   ```bash
   QUEUE_CONNECTION=redis
   ```

3. **Run the Queue Worker**:
   Start the Laravel queue worker to handle jobs in the background:
   ```bash
   php artisan queue:work
   ```

   This command will start processing jobs in the background, ensuring that the image generation process does not block other application functionality.

4. **Can also use database as Laravel Queue Connection**:
    In your `.env` file, set the queue driver to `database`:
    ```bash
    QUEUE_CONNECTION=database
    ```


## Running the Application

### 1. Run Laravel Server Locally

You can serve the application using the following command:

```bash
php artisan serve
```

### 2. Running the Queue Worker

To process image generation jobs in the background, you need to run the Laravel queue worker. Use the following command to start the worker:

```bash
php artisan queue:work
```

This will handle the background processing of image variations using the OpenAI API.

## API Documentation
The following API endpoints are available for interacting with the Laravel backend:

- **POST /api/upload-image**: Upload a PNG or JPG image.
- **GET /api/images**: Get a list of uploaded and generated images.
- **GET /api/images/{id}/download**: Download a generated image by ID.

Ensure proper authentication is implemented for all routes, supports Sanctum, API key, And No Auth for Demo.

## Database Design
The database consists of the following key tables:
- `users`: Stores user information.
- `images`: Stores metadata for uploaded images.
- `generated_images`: Stores metadata for generated image variations.

### Relationships:
- A `user` has many `images`.
- An `image` has many `generated_images`.

## Testing
You can run basic unit and integration tests to ensure the application's key features work correctly. Some areas to include tests are:
- User authentication
- Image upload
- Image generation via queues

Run the tests with the following command:
```bash
php artisan test
```

## Performance Considerations
- Use queues to offload time-consuming tasks like image generation.
- Optimize database queries with appropriate indexes and relationships.
- Store images on cloud services like AWS S3 to reduce local storage overhead and improve scalability.

## Bonus Features
- **Real-Time Updates**: Implemented via Pusher, users can see live updates of their image generation progress.
- **Role-Based Access Control (RBAC)**: Admin users can view all uploaded images, while regular users can only view their own.

You can include a **Developer Information** section at the end of the `README.md` file to provide contact details, acknowledgments, or any other relevant information about the developer. Here's an example you can use:

---

## Developer Information

This project was developed by Swapin Vidya.

### Contact Information:
- **Email**: swapin@laravelone.in
- **GitHub**: [https://github.com/swapins](https://github.com/swapins)
- **LinkedIn**: [www.linkedin.com/in/swapin-vidya](www.linkedin.com/in/swapin-vidya)
- **Portfolio**: [https://sevati.in/swapin](https://sevati.in/swapin)

### Acknowledgments
Special thanks to all open-source libraries and tools used in this project:
