<?php
// Start PHP session to access cart data
session_start();

// Include database connection file
require_once 'db.php';

// Handle Cart Actions (Increase, Decrease, Remove, Clear, Place Order)
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

    // Increase product quantity
    if ($action === 'increase' && $product_id) {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += 1;
        }
    }

    // Decrease product quantity
    if ($action === 'decrease' && $product_id) {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] -= 1;
            // Remove item if quantity falls to 0 or below
            if ($_SESSION['cart'][$product_id] <= 0) {
                unset($_SESSION['cart'][$product_id]);
            }
        }
    }

    // Remove single product from cart
    if ($action === 'remove' && $product_id) {
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
        }
    }

    // Clear entire cart
    if ($action === 'clear') {
        unset($_SESSION['cart']);
    }

    // Place Order action
    if ($action === 'place_order') {
        if (!empty($_SESSION['cart'])) {
            // In a beginner application, we clear the session cart and set a success notification
            unset($_SESSION['cart']);
            $_SESSION['order_success'] = "Thank you! Your order has been placed successfully.";
        }
    }

    // Redirect back to cart.php to prevent form re-submission on refresh
    header("Location: cart.php");
    exit();
}

// Calculate total cart items count for the navigation badge
$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cart_count = array_sum($_SESSION['cart']);
}

// Fetch details for products currently in the cart
$cart_products = [];
$total_amount = 0.00;

if (!empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    
    // Create parameter placeholders for the IN clause (e.g., ?, ?, ?)
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $types = str_repeat('i', count($product_ids));

    // Prepared statement to fetch products matching the cart IDs
    $stmt = $conn->prepare("SELECT product_id, product_name, price, image FROM products WHERE product_id IN ($placeholders)");
    $stmt->bind_param($types, ...$product_ids);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $cart_products[$row['product_id']] = $row;
    }
    $stmt->close();

    // Pre-calculate grand total amount
    foreach ($_SESSION['cart'] as $id => $qty) {
        if (isset($cart_products[$id])) {
            $total_amount += ((float)$cart_products[$id]['price'] * $qty);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Shopping Cart - ShopEase</title>
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
                        <a class="nav-link" href="index.php">
                            <i class="bi bi-house-door me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active position-relative btn btn-outline-light btn-sm text-white px-3 py-1" href="cart.php">
                            <i class="bi bi-cart3 me-1"></i>Cart
                            <span class="badge bg-danger rounded-pill cart-badge ms-1">
                                <?= $cart_count ?>
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="container my-5">
        
        <!-- Order Success Message Banner -->
        <?php if (isset($_SESSION['order_success'])): ?>
            <div class="alert alert-success p-4 rounded-3 shadow-sm text-center mb-5" role="alert">
                <div class="display-5 text-success mb-2">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <h3 class="alert-heading fw-bold">Order Placed Successfully!</h3>
                <p class="mb-3 fs-5"><?= htmlspecialchars($_SESSION['order_success']) ?></p>
                <hr class="w-50 mx-auto my-3">
                <p class="text-muted mb-4">Your order reference has been generated. We will process your items shortly.</p>
                <a href="index.php" class="btn btn-success btn-lg px-4">
                    <i class="bi bi-arrow-left me-2"></i>Continue Shopping
                </a>
            </div>
            <?php unset($_SESSION['order_success']); ?>
        <?php endif; ?>

        <!-- Shopping Cart Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h2 class="fw-bold mb-1">Shopping Cart</h2>
                <p class="text-muted mb-0">Review items in your cart before checkout</p>
            </div>
            <?php if (!empty($cart_products)): ?>
                <!-- Clear Cart Form -->
                <form method="POST" action="cart.php" onsubmit="return confirm('Are you sure you want to empty your entire cart?');">
                    <input type="hidden" name="action" value="clear">
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-trash3 me-1"></i>Clear Cart
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <?php if (!empty($cart_products)): ?>
            <div class="row g-4">
                
                <!-- Cart Items Table Section -->
                <div class="col-12 col-lg-8">
                    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                        <div class="table-responsive">
                            <table class="table cart-table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" class="py-3 ps-3">Product</th>
                                        <th scope="col" class="py-3">Price</th>
                                        <th scope="col" class="py-3 text-center">Quantity</th>
                                        <th scope="col" class="py-3 text-end">Subtotal</th>
                                        <th scope="col" class="py-3 text-center pe-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    foreach ($_SESSION['cart'] as $id => $quantity): 
                                        if (!isset($cart_products[$id])) continue;
                                        $product = $cart_products[$id];
                                        $price = (float)$product['price'];
                                        $subtotal = $price * $quantity;
                                    ?>
                                        <tr>
                                            <!-- Product Info -->
                                            <td class="ps-3 py-3">
                                                <div class="d-flex align-items-center gap-3">
                                                    <img 
                                                        src="<?= htmlspecialchars($product['image']) ?>" 
                                                        alt="<?= htmlspecialchars($product['product_name']) ?>" 
                                                        class="cart-item-img"
                                                        onerror="this.src='https://placehold.co/100x100?text=Product';"
                                                    >
                                                    <div>
                                                        <h6 class="mb-0 fw-semibold text-dark"><?= htmlspecialchars($product['product_name']) ?></h6>
                                                        <small class="text-muted">Item ID: #<?= (int)$product['product_id'] ?></small>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Unit Price -->
                                            <td class="fw-semibold text-muted">
                                                $<?= number_format($price, 2) ?>
                                            </td>

                                            <!-- Quantity Controls (- / count / +) -->
                                            <td>
                                                <div class="quantity-controls justify-content-center">
                                                    <!-- Decrease Button Form -->
                                                    <form method="POST" action="cart.php" class="m-0">
                                                        <input type="hidden" name="action" value="decrease">
                                                        <input type="hidden" name="product_id" value="<?= (int)$id ?>">
                                                        <button type="submit" class="btn btn-outline-secondary btn-sm quantity-btn" title="Decrease Quantity">
                                                            <i class="bi bi-dash"></i>
                                                        </button>
                                                    </form>

                                                    <!-- Quantity Display -->
                                                    <span class="quantity-display"><?= (int)$quantity ?></span>

                                                    <!-- Increase Button Form -->
                                                    <form method="POST" action="cart.php" class="m-0">
                                                        <input type="hidden" name="action" value="increase">
                                                        <input type="hidden" name="product_id" value="<?= (int)$id ?>">
                                                        <button type="submit" class="btn btn-outline-secondary btn-sm quantity-btn" title="Increase Quantity">
                                                            <i class="bi bi-plus"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>

                                            <!-- Subtotal -->
                                            <td class="text-end fw-bold text-dark">
                                                $<?= number_format($subtotal, 2) ?>
                                            </td>

                                            <!-- Remove Button -->
                                            <td class="text-center pe-3">
                                                <form method="POST" action="cart.php" class="m-0" onsubmit="return confirm('Remove this product from cart?');">
                                                    <input type="hidden" name="action" value="remove">
                                                    <input type="hidden" name="product_id" value="<?= (int)$id ?>">
                                                    <button type="submit" class="btn btn-light btn-sm text-danger border-0" title="Remove Product">
                                                        <i class="bi bi-trash-fill fs-5"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Return to Shopping Link -->
                    <div class="mt-3">
                        <a href="index.php" class="text-decoration-none fw-semibold">
                            <i class="bi bi-arrow-left me-1"></i>Continue Shopping
                        </a>
                    </div>
                </div>

                <!-- Order Summary Card Section -->
                <div class="col-12 col-lg-4">
                    <div class="card order-summary-card p-4">
                        <h4 class="fw-bold mb-3">Order Summary</h4>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Total Items:</span>
                            <span class="fw-semibold"><?= $cart_count ?></span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal:</span>
                            <span class="fw-semibold">$<?= number_format($total_amount, 2) ?></span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Shipping:</span>
                            <span class="text-success fw-semibold">FREE</span>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="fs-5 fw-bold text-dark">Total Amount:</span>
                            <span class="fs-4 fw-bold text-success">$<?= number_format($total_amount, 2) ?></span>
                        </div>

                        <!-- Place Order Form -->
                        <form method="POST" action="cart.php">
                            <input type="hidden" name="action" value="place_order">
                            <button type="submit" class="btn btn-success btn-lg w-100 py-3 fw-bold d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-bag-check-fill"></i> Place Order
                            </button>
                        </form>

                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <i class="bi bi-shield-check text-success me-1"></i>Safe &amp; Secure Checkout
                            </small>
                        </div>
                    </div>
                </div>

            </div>
        <?php elseif (!isset($_SESSION['order_success'])): ?>
            <!-- Empty Cart View -->
            <div class="text-center py-5">
                <div class="p-5 bg-white rounded-3 shadow-sm mx-auto" style="max-width: 500px;">
                    <div class="mb-3 text-secondary display-1">
                        <i class="bi bi-cart-x"></i>
                    </div>
                    <h3 class="fw-bold text-dark">Your Cart is Empty</h3>
                    <p class="text-muted mb-4">Looks like you haven't added any products to your cart yet.</p>
                    <a href="index.php" class="btn btn-primary px-4 py-2">
                        <i class="bi bi-bag-plus me-1"></i>Start Shopping
                    </a>
                </div>
            </div>
        <?php endif; ?>

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
// Close database connection
$conn->close();
?>
