# Clothing Store

A self-hosted PHP and MySQL clothing store with a customer storefront and an administrative dashboard. The project brings product browsing, shopping, checkout, customer accounts, and store operations together in one codebase.

> **Own your storefront.** Hosted commerce platforms can make it easy to get started, but their recurring subscription fees and platform rules may not suit every store. This project provides a customizable storefront that you can run and maintain yourself, without a required third-party storefront subscription. You control the code and store configuration. Hosting, a domain name, payment providers, and ongoing maintenance may still have their own costs.

## The Problem This Project Addresses

Small businesses and independent sellers often need a professional online storefront without committing to a recurring commerce-platform subscription or being limited to a fixed template. At the same time, building every store feature from scratch takes substantial time.

Clothing Store provides a starting point: customers can discover products, manage a cart, and place orders, while store staff can manage products and orders from an admin area. Because the application is built with PHP, MySQL, HTML, CSS, and JavaScript rather than a hosted store builder, developers can inspect and adapt the implementation to their requirements.

This is a self-managed project, not a hosted service. You are responsible for installing, securing, deploying, backing up, and maintaining it. It is intended as a learning project and a foundation for a small storefront, not as a fully managed or enterprise e-commerce platform.

## Features

### Customer storefront

- Browse the home page, catalog, product details, About, and Contact pages.
- Search and sort products in the catalog.
- View product pricing, descriptions, stock information, and images.
- Add products to a cart and manage cart quantities.
- Save products to a wishlist.
- Register, log in, log out, and update account details.
- Submit an order with customer and shipping information.
- Choose from the payment methods presented at checkout. The current application records the selected method with the order; it does not include a payment-provider integration.
- Review order history and order details.
- Submit product reviews from the order experience.
- Request a password reset.

### Administration

- Register and log in to the admin area.
- View a dashboard with store summaries, recent orders, and sales information.
- Create and manage products, pricing, stock, and product images.
- Review orders and update their status.
- Manage the owner account and assistant accounts.
- Use owner and assistant roles, with owner-only controls for assistant account management.
- Request an admin password reset.

### Built-in foundations

- PHP sessions for customer and administrator login state.
- PDO for MySQL database access.
- Password hashing and CSRF-related form protection.
- A relational schema with foreign keys and indexes for core store data.
- Page-specific CSS and JavaScript assets without a required frontend framework.

## Screenshots

### Storefront

| Landing page | Catalog |
| --- | --- |
| <img src="store%20images/landing%20page.png" alt="Store landing page" width="420"> | <img src="store%20images/catalog.png" alt="Product catalog" width="420"> |

| Product view | About |
| --- | --- |
| <img src="store%20images/product%20view.png" alt="Product details page" width="420"> | <img src="store%20images/about.png" alt="About page" width="420"> |

| Cart | Checkout |
| --- | --- |
| <img src="store%20images/cart.png" alt="Shopping cart" width="420"> | <img src="store%20images/checkout.png" alt="Checkout page" width="420"> |

| Contact |
| --- |
| <img src="store%20images/contact.png" alt="Contact page" width="420"> |

### Customer account

| Account creation | Account login |
| --- | --- |
| <img src="store%20images/account%20creation.png" alt="Account creation page" width="420"> | <img src="store%20images/account%20login.png" alt="Account login page" width="420"> |

| Customer overview | Purchases |
| --- | --- |
| <img src="store%20images/customer%20overview.png" alt="Customer account overview" width="420"> | <img src="store%20images/customer%20Purchases.png" alt="Customer purchases" width="420"> |

| Wishlist |
| --- |
| <img src="store%20images/customer%20wishlish.png" alt="Customer wishlist" width="420"> |

### Admin

| Admin login | Admin overview |
| --- | --- |
| <img src="store%20images/admin%20login.png" alt="Admin login page" width="420"> | <img src="store%20images/admin%20overview.png" alt="Admin dashboard overview" width="420"> |

| Admin products | Admin orders |
| --- | --- |
| <img src="store%20images/admin%20products.png" alt="Admin product management" width="420"> | <img src="store%20images/admin%20orders.png" alt="Admin order management" width="420"> |

| Admin account settings | Add assistant |
| --- | --- |
| <img src="store%20images/admin%20account%20settings.png" alt="Admin account settings" width="420"> | <img src="store%20images/add%20assistant.png" alt="Add assistant page" width="420"> |

## Technology

- **Server-side:** PHP 8 or later, using procedural PHP.
- **Database:** MySQL or MariaDB, accessed with PDO.
- **Web server for local development:** Apache through XAMPP.
- **Frontend:** HTML, project-specific CSS, and JavaScript.
- **IDE recommendation:** Visual Studio Code. The project does not require a particular IDE; any editor that supports PHP can be used.
- **Helpful development tools:** XAMPP Control Panel, phpMyAdmin or another MySQL client, Git, and a modern web browser.

There is no PHP package manager manifest or JavaScript package manifest in the current project. No framework or frontend build step is required to run the application locally.

## Project Structure

```text
clothing-store/
├── admin/                         # Admin login, dashboard, products, orders, accounts
│   ├── admin-account.php
│   ├── admin-dashboard.php
│   ├── admin-forgot-password.php
│   ├── admin-login.php
│   ├── admin-orders.php
│   ├── admin-products.php
│   └── admin-register.php
├── assets/
│   ├── css/                       # Page-specific storefront and admin styles
│   └── javascript/                # Admin page interactions
├── config/
│   ├── config.php                # Store name, URL, currency, announcement
│   └── database.php              # PDO connection and database creation
├── database/
│   └── schema.sql                # Database tables, indexes, and relationships
├── includes/
│   ├── admin-auth.php            # Admin authentication and role checks
│   ├── admin-footer.php
│   ├── admin-header.php
│   ├── auth.php                  # Customer session helpers
│   ├── csrf.php                  # CSRF helpers
│   ├── footer.php
│   ├── functions.php             # Shared helpers and store operations
│   ├── header.php
│   └── navbar.php
├── store images/                  # Screenshots used in this README
├── uploads/
│   └── products/                 # Product image uploads
├── about.php
├── account.php
├── cart.php
├── checkout.php
├── contact.php
├── forgot-password.php
├── index.php                     # Storefront landing page
├── login.php
├── logout.php
├── order-details.php
├── orders.php
├── product.php
├── register.php
├── shop.php
├── wishlist.php
└── README.md
```

### How the application is organized

- **Root PHP pages** handle customer-facing pages and their request processing. For example, `shop.php` displays the catalog, `product.php` displays product details, and `checkout.php` handles order placement.
- **`admin/`** contains the separate administration area for store operations.
- **`includes/`** contains shared templates, authentication and authorization helpers, CSRF functions, and reusable application logic.
- **`config/`** contains the application settings and database connection. The current database connection settings are local-development defaults and should be changed before deployment.
- **`database/schema.sql`** defines the main relational data model.
- **`assets/css/` and `assets/javascript/`** hold page-specific frontend files.
- **`uploads/products/`** is the destination for product images added to the store.
- **`store images/`** contains documentation screenshots; it is separate from the product upload directory.

## Database Overview

The default database name is `clothing_store`. The schema includes tables for:

- Customer and administrator accounts (`users`, `admins`).
- Product categories, products, and product images.
- Carts and cart items.
- Wishlists and wishlist items.
- Coupons.
- Orders and order items.
- Product reviews.
- Customer and admin password-reset tokens, created by application helpers when needed.

Product and order records are connected with foreign keys. Order items retain product names and prices so that an order can still show its purchased item details if a product is later changed or removed.

## Getting Started (XAMPP on Windows)

### Requirements

- Windows with XAMPP installed.
- Apache and MySQL/MariaDB enabled in XAMPP.
- PHP 8 or later with PDO MySQL enabled.
- A web browser.

### 1. Put the project in the web root

Place or clone the project directory at:

```text
C:\xampp\htdocs\clothing-store
```

### 2. Configure the application

Review `config/config.php`. The default local URL is:

```php
define('BASE_URL', 'http://localhost/clothing-store/');
```

If the project folder or local host name differs, update `BASE_URL` to match.

### 3. Configure MySQL access

Review the local database host, name, username, and password in `config/database.php`. The defaults are intended for a typical XAMPP installation (`127.0.0.1`, database `clothing_store`, user `root`, and an empty password).

The connection script creates the database if it is missing. **It does not import the tables for you.** Create the tables by importing `database/schema.sql` into MySQL using phpMyAdmin or the MySQL command-line client.

### 4. Start Apache and MySQL

Open the XAMPP Control Panel and start both services.

### 5. Open the storefront

Visit:

```text
http://localhost/clothing-store/
```

### 6. Create the initial admin account

Open the admin registration page:

```text
http://localhost/clothing-store/admin/admin-register.php
```

Create the store owner account, then sign in at:

```text
http://localhost/clothing-store/admin/admin-login.php
```

## Development Environment and IDE

Visual Studio Code is a convenient IDE for this project, but it is optional. A practical local setup is:

1. Open the `clothing-store` folder in VS Code.
2. Install a PHP language support extension, such as PHP Intelephense, for autocomplete and diagnostics.
3. Use XAMPP to run Apache and MySQL.
4. Use phpMyAdmin or a MySQL extension/client to inspect the `clothing_store` database.
5. Open the local URL in a browser and use the browser developer tools to inspect layout and client-side behavior.

There is no build command for the frontend in the current project. Edit the relevant PHP, CSS, or JavaScript file and refresh the browser to see changes.

## Configuration and Customization

Common settings and customization locations:

- **Store name, local URL, currency, and announcement:** `config/config.php`.
- **Database connection:** `config/database.php`.
- **Landing page and storefront layout:** `index.php` and `assets/css/index.css`.
- **Product catalog:** `shop.php` and `assets/css/shop.css`.
- **Product detail page:** `product.php` and `assets/css/product.css`.
- **Cart and checkout:** `cart.php`, `checkout.php`, and their corresponding stylesheets.
- **Customer accounts and orders:** customer PHP pages in the project root.
- **Admin workflows:** PHP pages under `admin/` and their matching stylesheets/scripts under `assets/`.
- **Database structure:** `database/schema.sql`.
- **Product images:** managed through the admin interface and stored under `uploads/products/`.

## Security and Deployment Notes

The project contains useful baseline safeguards, including password hashing, CSRF-related helpers, session-based authentication, admin role checks, and prepared PDO statements in application queries. 

Before deploying to a public server:

- Set `display_errors` to `Off` and configure server-side error logging.
- Replace local database credentials with a dedicated database user and a strong password; do not publish secrets.
- Use HTTPS and secure session-cookie settings.
- Review authentication, authorization, input validation, output escaping, file-upload handling, and error handling for your deployment requirements.
- Restrict access to setup/registration workflows as appropriate for your store.
- Configure reliable database and uploaded-file backups.
- Configure and test a real payment provider separately if online payment processing is required. The current checkout records a payment method but does not process card payments.
- Test the application on the exact PHP and MySQL versions used by your hosting provider.

The default configuration enables PHP error display for local development. Do not leave that setting enabled on a public production site.

## Limitations

- This project does not include a payment gateway or process online card payments.
- No automated test suite or deployment pipeline is currently included.
- Additional review and hardening are required before handling real customer data or public production traffic.

## Devoloper
 Nosheen Sajid

## License
OPEN SOURCE


