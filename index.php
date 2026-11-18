<?php
// Start PHP session to maintain cart items across pages
session_start();

// Include database connection file
require_once 'db.php';

// Handle "Add to Cart" form submission
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

    if ($product_id) {
        // Verify product exists in database using a prepared statement
        $check_stmt = $conn->prepare("SELECT product_id, product_name FROM products WHERE product_id = ?");
        $check_stmt->bind_param("i", $product_id);
        $check_stmt->execute();
        $check_res = $check_stmt->get_result();

        if ($check_row = $check_res->fetch_assoc()) {
            // Initialize cart session array if not already set
            if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }

            // Increment quantity if product is already in cart, else add with quantity 1
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id] += 1;
            } else {
                $_SESSION['cart'][$product_id] = 1;
            }

            $_SESSION['flash_message'] = "Added <strong>" . htmlspecialchars($check_row['product_name']) . "</strong> to your cart!";
        }
        $check_stmt->close();
    }

    // Redirect to index.php to prevent form re-submission on refresh (Post/Redirect/Get pattern)
    header("Location: index.php");
    exit();
}

// Calculate total cart items count for the navigation badge
$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cart_count = array_sum($_SESSION['cart']);
}

// Fetch 6 sample products from MySQL using a prepared statement
$stmt = $conn->prepare("SELECT product_id, product_name, description, price, image FROM products ORDER BY product_id ASC LIMIT 6");
$stmt->execute();
$products_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple E-Commerce Store - Product Catalog</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
                <i class="bi bi-shop text-warning fs-4"></i>
                <span>ShopEase</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center gap-2">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="index.php">
                            <i class="bi bi-house-door me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link position-relative btn btn-outline-light btn-sm text-white px-3 py-1" href="cart.php">
                            <i class="bi bi-cart3 me-1"></i>Cart
                            <span class="badge bg-danger rounded-pill cart-badge ms-1" id="cart-counter">
                                <?= $cart_count ?>
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container my-5">
        
        <!-- Flash Message Notification -->
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div><?= $_SESSION['flash_message'] ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>

        <!-- Hero / Section Heading -->
        <div class="text-center mb-5">
            <h1 class="fw-bold text-dark">Explore Our Products</h1>
            <p class="text-muted">High quality gadgets and lifestyle accessories at affordable prices.</p>
            <hr class="w-25 mx-auto text-primary" style="height: 3px; opacity: 0.8;">
        </div>

        <!-- Product Grid -->
        <div class="row g-4">
            <?php if ($products_result && $products_result->num_rows > 0): ?>
                <?php while ($product = $products_result->fetch_assoc()): ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card h-100 product-card">
                            <!-- Product Image -->
                            <div class="product-img-wrapper">
                                <img 
                                    src="<?= htmlspecialchars($product['image']) ?>" 
                                    alt="<?= htmlspecialchars($product['product_name']) ?>"
                                    onerror="this.src='https://placehold.co/600x400?text=Product+Image';"
                                    loading="lazy"
                                >
                            </div>

                            <!-- Product Info -->
                            <div class="card-body d-flex flex-column">
                                <h5 class="product-title"><?= htmlspecialchars($product['product_name']) ?></h5>
                                <p class="product-desc flex-grow-1"><?= htmlspecialchars($product['description']) ?></p>
                                
                                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                    <span class="product-price">$<?= number_format($product['price'], 2) ?></span>
                                    
                                    <!-- Add to Cart Form -->
                                    <form method="POST" action="index.php" class="m-0">
                                        <input type="hidden" name="product_id" value="<?= (int)$product['product_id'] ?>">
                                        <button type="submit" name="add_to_cart" class="btn btn-primary d-flex align-items-center gap-2">
                                            <i class="bi bi-cart-plus"></i> Add to Cart
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <!-- No Products Found Message -->
                <div class="col-12 text-center py-5">
                    <div class="p-5 bg-white rounded-3 shadow-sm">
                        <i class="bi bi-box-seam text-secondary fs-1 mb-3 d-block"></i>
                        <h4 class="text-muted">No Products Available</h4>
                        <p class="text-secondary">Please check if the <code>database.sql</code> file has been imported into MySQL.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <!-- Footer -->
    <footer class="text-center">
        <div class="container">
            <p class="mb-1">&copy; <?= date('Y') ?> <strong>ShopEase</strong> - Beginner-Friendly E-Commerce Application</p>
            <small class="text-muted">Built with PHP, MySQLi, and Bootstrap 5</small>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
// Close prepared statement and database connection
$stmt->close();
$conn->close();
?>
