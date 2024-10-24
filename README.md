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

### 5. Run DB Seed
Run database seeds to create an Admin user and normal user:
```bash
php artisan db:seed
```
This create two accounts 
   1. User Email : admin@example.com Password: 'password'
   2. User Email : user@example.com Password: 'password'
this can be used to experience the demo.


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

### Route Breakdown:

1. **POST `/upload-image`**:
   - **Purpose**: Allows an authenticated user to upload an image (PNG or JPG) to the server.
   - **Controller Method**: `ImageController@upload`
   - **Middleware**: `auth:sanctum` ensures that the route is protected and only accessible by users who have been authenticated via the Sanctum token-based authentication.
   - **Expected Input**: The image file should be included in the request, typically in a multipart form-data format.
   - **Response**: Upon successful upload, the server will likely return the image URL or some identifier to track the image for later operations (such as generating variations).

2. **GET `/generate-variations/{imageId}`**:
   - **Purpose**: Triggers the generation of AI-based variations for the image identified by `imageId`. This route does not require authentication, meaning it could be used in contexts where image generation happens in the background or by an external service.
   - **Controller Method**: `ImageController@generateImageVariations`
   - **Parameters**:
     - `imageId`: The ID of the image for which variations will be generated.
   - **Expected Behavior**: The server will use the OpenAI API or similar to generate variations of the provided image and save them. Depending on the implementation, it may return a success message or provide a link to the generated images.
   - **Response**: Could return the status of the generation process or URLs to the generated image variations.

3. **GET `/user-images`**:
   - **Purpose**: Retrieves a list of images uploaded by the authenticated user.
   - **Controller Method**: `ImageController@getUserImages`
   - **Middleware**: This route could use `auth:sanctum` if you want to restrict access to only authenticated users’ images.
   - **Expected Output**: A list of images associated with the authenticated user, including metadata like image URLs, upload dates, and possibly links to generated variations.
   - **Response**: A JSON response containing the list of the user's images.

---

### Example API Workflow:
1. **User Uploads Image**: 
   - POST `/upload-image`
   - Uploads the image via the frontend. The server stores the image and returns a response.

2. **Generate Image Variations**: 
   - GET `/generate-variations/{imageId}`
   - Once the image is uploaded, the user can request image variations to be generated via this route.

3. **View User's Uploaded Images**: 
   - GET `/user-images`
   - The authenticated user can retrieve and view the list of images they've uploaded, including generated variations.

---

### Suggested Improvements:

- **Authentication for Image Generation**: 
   - Currently, `/generate-variations/{imageId}` is public. If you want to secure this endpoint, consider adding authentication middleware (`auth:sanctum`) to ensure that only authorized users can generate variations for their images.

- **Validation**: 
   - Ensure that the image upload route includes validation to check file type and size.

- **Real-Time Updates for Variations**: 
   - If generating variations takes time, you might want to consider implementing a real-time progress indicator with Pusher or polling for the status of image generation jobs.



## Database Design
The database consists of the following key tables:
- `users`: Stores user information.
- `images`: Stores metadata for uploaded images and generated images.

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


## Developer Information

This project was developed by Swapin Vidya.

### Contact Information:
- **Email**: swapin@laravelone.in
- **GitHub**: [https://github.com/swapins](https://github.com/swapins)
- **LinkedIn**: [www.linkedin.com/in/swapin-vidya](www.linkedin.com/in/swapin-vidya)
- **Portfolio**: [https://sevati.in/swapin](https://sevati.in/swapin)

### Acknowledgments
Special thanks to all open-source libraries and tools used in this project:
