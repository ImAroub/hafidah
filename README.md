# Hafidah

**Hafidah** is an Arabic web application for managing purchase invoices and everything associated with them after a purchase. It was developed as a practical project during a **summer training program**.

The system brings invoices, warranty periods, return and replacement deadlines, subscription renewals, and post-purchase operations together in one place.

Hafidah allows users to store purchase information and invoice images, keep track of important deadlines, and document events that occur after a purchase, such as maintenance, replacement, or returns. It also provides alerts for approaching deadlines and includes a separate administration panel for managing users, categories, invoices, and operations.

The project was developed using **PHP, MySQL, JavaScript, HTML, and CSS**, with an Arabic **Single-Page Application (SPA)** frontend communicating with a PHP-based backend API.

---

## Project Idea

Hafidah was created to address the need to keep purchase invoices and their related information accessible even after the purchase is complete.

An invoice may be needed later for a warranty claim, return, or replacement, while products and subscriptions may also have important deadlines that are easy to forget.

Hafidah brings this information into one system where users can store invoices, track their associated periods, and record all operations that take place after a purchase.

---

## Key Features

### Invoice Management

Users can create invoices and store information such as:

- Product or service name
- Category
- Store or purchase location
- Contact information
- Purchase date
- Cost
- Invoice image

Invoices can also be edited or deleted later.

### Important Period Tracking

Invoices can be associated with different periods depending on the product or subscription, including:

- Warranty
- Return
- Replacement
- Renewal

The system automatically calculates each expiration date based on the purchase date and the duration entered by the user.

### Alerts

The system displays alerts for deadlines that will expire within **14 days**, including:

- Warranty expiration
- Return-period expiration
- Replacement-period expiration
- Subscription renewal

Invoices with approaching deadlines are also prioritized so users can identify items that require attention more easily.

### Operation History

Users can record post-purchase operations related to an invoice, including:

- Maintenance
- Maintenance completion
- Replacement
- Return
- Renewal
- Cancellation
- Other operations

Each operation can include:

- Operation date
- Expected completion date
- Cost
- Notes
- An image or supporting document

The system automatically updates the item's status according to the latest recorded operation.

### Products and Subscriptions

The system supports two main category types:

- **Products**
- **Subscriptions**

Available operations depend on the category type. For example, products may support maintenance, replacement, and returns, while subscriptions support renewal and cancellation.

### Search and Filtering

Invoices and operations can be searched and filtered using several criteria, including:

- Category
- Item status
- Operation type
- Purchase period
- Invoice-related deadlines

Invoices can also be sorted by priority, nearest expiration date, newest, oldest, or name.

---

## User Dashboard

The main dashboard provides users with a quick overview of their data, including:

- Total invoices
- Total operations
- Current alerts
- Recent invoices
- Recent operations
- Upcoming alerts

This allows users to quickly identify items that may require attention.

---

## User Accounts

The system supports:

- Account registration
- Login and logout
- Profile updates
- Password changes
- Password recovery using a temporary verification code
- Persistent authentication sessions

Passwords are stored using **password hashing** rather than plain text.

---

## Account Deletion and Recovery

When a user requests account deletion, the account and its data are not removed immediately.

The account is scheduled for permanent deletion after **30 days**. During this period, the user can restore the account by signing in again and cancelling the deletion request.

After the recovery period expires, the system removes the user's:

- Account
- Invoices
- Related operations
- Uploaded images and files

The related data is removed together as part of the account cleanup process.

---

## Admin Panel

The project includes a **separate administration panel** with Admin accounts that are independent from regular user accounts.

The administration panel allows administrators to:

- View system statistics
- Manage and search users
- View user details
- View a user's invoices and operations
- Schedule user accounts for deletion
- Restore accounts before permanent deletion
- Manage categories
- View all invoices
- View all operations
- Delete invoices and operations when necessary

The admin dashboard also displays recent users, invoices, operations, and system statistics.

---

## Category Management

Administrators can:

- Create categories
- Edit categories
- Delete categories

Each category can be defined as either a **product** or a **subscription**.

The system also prevents administrators from deleting categories that are already associated with existing invoices.

---

## System Architecture

The project is mainly divided into three components:

### User Application

The main user interface is built using:

- HTML
- CSS
- JavaScript
- Templates

It operates as a **Single-Page Application (SPA)**, allowing pages and content to change dynamically without loading a completely new application page for every action.

### PHP API

The backend is responsible for:

- Authentication
- Data processing
- Invoice management
- Operation management
- Alerts
- File handling
- Business rules
- Database communication

Data is exchanged between the frontend and backend API using **JSON**.

### Admin Control Panel

The administration interface is separated from the user application and includes:

- Dedicated routes
- A separate Admin API
- Independent Admin authentication
- Permissions separated from regular users

---

## Database

The project uses **MySQL** and is primarily built around the following tables:

```text
users
  │
  └── invoices
        │
        └── operations

categories
  │
  └── invoices
```

### Users

Stores user and administrator accounts, along with authentication, password recovery, and account-deletion information.

### Categories

Stores invoice categories and determines whether a category represents a product or subscription.

### Invoices

Stores invoice information, warranty, return, replacement, and renewal periods, as well as the current item status.

### Operations

Stores the history of operations associated with each invoice.

---

## Image and File Uploads

The system supports uploading images for:

- Invoices
- Operations

Files are validated by type and size before being stored.

Supported image formats include:

- JPEG
- PNG
- WebP

Uploaded images are converted to **WebP** before storage to standardize the format and reduce file size.

---

## Technologies Used

### Backend
- PHP
- REST-like JSON API
- MySQL / MySQLi
- Prepared Statements

### Frontend
- HTML
- CSS
- JavaScript
- SPA Routing
- Template-based UI
- Fetch API
- Zepto.js

### Authentication & Security
- Password Hashing
- Secure Authentication Cookies
- Prepared SQL Statements
- Server-side Validation
- File Type and Size Validation

---

## Project Structure

```text
api/
├── auth/
├── invoices/
├── operations/
├── alerts/
├── dashboard/
├── categories/
├── env/
└── lib/

app/
├── css/
├── js/
└── tpl/

cp/
├── api/
└── app/

welcome/
```

- `api` contains the user-facing backend API.
- `app` contains the main user application.
- `cp` contains the Admin Control Panel and its API.
- `welcome` contains the project's landing page.

---

## Landing Page

The project includes a separate landing page that introduces **Hafidah** and explains the main workflow in three steps:

1. Add an invoice and its purchase information.
2. Record operations associated with the invoice.
3. Track deadlines and alerts through Hafidah.

---

## Project Context

**Hafidah** was developed as a practical project using **PHP and JavaScript** during cooperative training, with the goal of building a complete web system that combines relational database design, backend API development, a dynamic user interface, account and permission management, and handling interconnected files and data.

---

## Future Improvements

Possible future improvements include:

- Sending alerts through email or notifications
- Connecting password recovery to a real email service
- Supporting PDF uploads in addition to images
- Adding backups for invoices and uploaded files
- Adding data and report export features
- Adding automated API tests
- Improving role and permission management
- Deploying the system to a cloud production environment
