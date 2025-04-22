# SBDL63 Management System

This is a simple PHP-based inventory management system for managing customers, products, and purchases. Built with PHP, MySQL, and Bootstrap 5.

## Features

- **Dashboard:** Overview of system statistics and recent products
- **Customer Management:** Add, edit, and delete customer records
- **Product Management:** Add, edit, and delete product records with stock tracking
- **Purchase Management:** Record purchases with automatic stock adjustment

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher (or MariaDB 10.4 or higher)
- Web server (Apache, Nginx, etc.)

## Installation

1. **Clone or download the repository to your web server directory**

```bash
git clone https://github.com/yourusername/sbdl63_website.git
```

2. **Import the database**

Use phpMyAdmin or another MySQL client to import the `db_sbdl63.sql` file.

3. **Configure the database connection**

Edit the `config/database.php` file with your database credentials:

```php
$host = "localhost";     // Your database host
$username = "root";      // Your database username
$password = "";          // Your database password
$database = "db_sbdl63"; // Your database name
```

4. **Access the website**

Open your web browser and navigate to:
```
http://localhost/sbdl63_website/
```

## Directory Structure

```
sbdl63_website/
├── config/
│   └── database.php
├── includes/
│   ├── header.php
│   └── footer.php
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── script.js
├── pages/
│   ├── customer/
│   │   ├── index.php
│   │   ├── add.php
│   │   ├── edit.php
│   │   └── delete.php
│   ├── product/
│   │   ├── index.php
│   │   ├── add.php
│   │   ├── edit.php
│   │   └── delete.php
│   └── purchase/
│       ├── index.php
│       ├── add.php
│       ├── edit.php
│       └── delete.php
├── index.php
└── README.md
```

## Usage

### Customer Management

- View all customers: Navigate to "Customers" in the navigation menu
- Add a new customer: Click the "Add New Customer" button
- Edit a customer: Click the "Edit" button next to a customer
- Delete a customer: Click the "Delete" button next to a customer

### Product Management

- View all products: Navigate to "Products" in the navigation menu
- Add a new product: Click the "Add New Product" button
- Edit a product: Click the "Edit" button next to a product
- Delete a product: Click the "Delete" button next to a product

### Purchase Management

- View all purchases: Navigate to "Purchases" in the navigation menu
- Record a new purchase: Click the "Add New Purchase" button
- Edit a purchase: Click the "Edit" button next to a purchase
- Delete a purchase: Click the "Delete" button next to a purchase

## Database Schema

### tb_customer
- `nama_customer` - Customer name
- `id_customer` - Customer ID (Primary Key)
- `alamat_customer` - Customer address
- `no_wa_cutomer` - Customer WhatsApp number
- `email_customer` - Customer email

### tb_produk
- `id_produk` - Product ID (Primary Key)
- `nama_produk` - Product name
- `harga_produk` - Product price
- `stok_produk` - Product stock quantity
- `jenis_produk` - Product type/category
- `exp_produk` - Product expiry date

### tb_pembelian
- `tanggal_pembelian` - Purchase date
- `id_pembelian` - Purchase ID (Primary Key)
- `id_produk` - Product ID (Foreign Key)
- `jumlah_produk` - Quantity purchased
- `id_customer` - Customer ID (Foreign Key)
- `metode_pembayaran` - Payment method
- `jumlah_pembayaran` - Payment amount