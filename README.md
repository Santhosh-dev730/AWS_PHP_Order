# Beginner-Friendly E-Commerce Web Application

A clean, responsive, and beginner-friendly e-commerce web application built using **PHP**, **MySQL Workbench**, **HTML5**, **CSS3**, and **Bootstrap 5**.

---

## 📁 Project Structure

```text
PHP_Project/
│
├── database.sql        # Database schema and 6 sample products
├── db.php              # MySQL database connection file (MySQLi)
├── index.php           # Product listing & catalog page (Home)
├── cart.php            # Shopping cart & checkout page
├── assets/
│   └── css/
│       └── style.css   # Custom CSS styling and animations
└── README.md           # Step-by-step setup and execution guide
```

---

## 🛠️ Technologies Used

- **Frontend**: HTML5, CSS3, Bootstrap 5.3 (via CDN), Bootstrap Icons
- **Backend**: PHP 7.4+ / 8.x
- **Database**: MySQL 5.7+ / 8.x (managed via MySQL Workbench or phpMyAdmin)
- **Local Server**: XAMPP (Apache & MySQL)

---

## 🚀 Step-by-Step Setup & Execution Guide

### Step 1: Start XAMPP Services
1. Open the **XAMPP Control Panel**.
2. Click the **Start** button next to **Apache**.
3. Click the **Start** button next to **MySQL**.
4. Ensure both services show green status indicators.

---

### Step 2: Create Database in MySQL Workbench
1. Open **MySQL Workbench**.
2. Connect to your local MySQL instance (usually `localhost:3306`, user: `root`).
3. Click on **File** > **Open SQL Script...** (or press `Ctrl + Shift + O`).
4. Select the `database.sql` file located in this project directory.
5. Click the **Lightning Bolt (Execute)** icon in MySQL Workbench to run the script.
   
> **What this does:**
> - Creates the `ecommerce_db` database.
> - Creates the `products` table (`product_id`, `product_name`, `description`, `price`, `image`).
> - Inserts 6 sample electronic and lifestyle products.

*(Alternatively, you can also import `database.sql` via **phpMyAdmin** at `http://localhost/phpmyadmin/`)*.

---

### Step 3: Place Project in XAMPP `htdocs`
1. Copy or move this entire project folder (`PHP_Project`) into your XAMPP web root directory:
   ```text
   C:\xampp\htdocs\PHP_Project
   ```
2. Your project structure should look like:
   ```text
   C:\xampp\htdocs\PHP_Project\index.php
   C:\xampp\htdocs\PHP_Project\cart.php
   C:\xampp\htdocs\PHP_Project\db.php
   ...
   ```

---

### Step 4: Verify Database Connection (`db.php`)
Open `db.php` in any code editor (VS Code, Notepad++, etc.) and check your MySQL credentials:
```php
$db_host = "localhost";
$db_user = "root";
$db_pass = "santhosh";     // Configured for your MySQL root password
$db_name = "ecommerce_db";
```
If you set a password for MySQL in MySQL Workbench (e.g. `root` or `admin123`), update `$db_pass = "your_password";`.

---

### Step 5: Run the Application in Your Browser
Open your favorite web browser (Chrome, Edge, Firefox) and navigate to:
```text
http://localhost/PHP_Project/index.php
```

---

## 💡 Features & Application Flow

### 1. `index.php` (Product Catalog Page)
- **Dynamic Database Fetch**: Queries the 6 products from MySQL dynamically using prepared statements.
- **Product Display**: Displays product photo, title, description, formatted price (`$XX.XX`), and an "Add to Cart" button.
- **Interactive Navbar**: Shows active "Home" link and "Cart" link with a dynamic badge displaying total items in cart.
- **Flash Alerts**: Shows a smooth dismissible alert when an item is added to the cart.

### 2. `cart.php` (Shopping Cart Page)
- **Product Details**: Shows thumbnail image, product title, item ID, unit price, quantity controls, and row subtotal.
- **Quantity Controls**: Click `+` to increase or `-` to decrease item count. Dropping quantity to `0` removes it from the cart.
- **Remove & Clear**: Remove individual items or clear the entire cart with confirmation.
- **Order Summary**: Real-time calculation of total items and total amount.
- **Place Order**: Clicking "Place Order" clears the cart and triggers a celebratory order confirmation banner with a return button.

---

## 🔒 Beginner-Friendly Concepts Explained

### 1. PHP Sessions for Cart Management
Instead of storing temporary cart items in the database, PHP Sessions (`$_SESSION['cart']`) store the item IDs and quantities on the server while the user browses:
```php
// Cart is stored as an associative array: [ product_id => quantity ]
$_SESSION['cart'][1] = 2; // Product 1 with quantity 2
```

### 2. MySQLi Prepared Statements
Prepared statements prevent SQL injection attacks by separating the query structure from user inputs:
```php
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
```

### 3. Post/Redirect/Get (PRG) Pattern
After adding an item or placing an order via POST, the script redirects (`header("Location: index.php"); exit();`) to prevent duplicate submissions when the user refreshes the page.
