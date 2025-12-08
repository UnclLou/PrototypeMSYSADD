<?php
session_start();

// Assume access is granted initially
$has_access = false;
$allowed_roles = ['admin', 'staff'];

// Check if the user is logged in and has an allowed role
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], $allowed_roles)) {
    $has_access = true;
}

// Database connection
$host = 'localhost';
$dbname = 'coffee_queue';
$username = 'root';
$password = '';

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Get date range from request or default to current month
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Sales Report
$sales_query = "SELECT 
    DATE(o.created_at) as date,
    COUNT(*) as total_orders,
    SUM(o.total_amount) as total_sales,
    AVG(o.total_amount) as average_order_value
FROM orders o
WHERE o.created_at BETWEEN ? AND ?
    AND o.status != 'cancelled'
GROUP BY DATE(o.created_at)
ORDER BY date DESC";

$stmt = $conn->prepare($sales_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$sales_result = $stmt->get_result();

// Top Products Report
$products_query = "SELECT 
    p.name,
    SUM(oi.quantity) as total_quantity,
    SUM(oi.quantity * oi.price) as total_revenue
FROM order_items oi
JOIN products p ON oi.product_id = p.id
JOIN orders o ON oi.order_id = o.id
WHERE o.created_at BETWEEN ? AND ?
    AND o.status != 'cancelled'
GROUP BY p.id
ORDER BY total_quantity DESC
LIMIT 5";

$stmt = $conn->prepare($products_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$products_result = $stmt->get_result();

// Order Status Report
$status_query = "SELECT 
    status,
    COUNT(*) as count
FROM orders
WHERE created_at BETWEEN ? AND ?
GROUP BY status";

$stmt = $conn->prepare($status_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$status_result = $stmt->get_result();

// Low Stock Report
$stock_query = "SELECT 
    name,
    stock,
    category
FROM products
WHERE stock < 10
ORDER BY stock ASC";

$stock_result = $conn->query($stock_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - CoffeeQueue</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <header>
            <nav>
                <div class="logo">
                    <h1>Let's Meet Cafe</h1>
                </div>
                <ul class="nav-links">
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <li><a href="payment_process.php">Payment</a></li>
                        <li><a href="queue.php">Queue</a></li>
                        <li><a href="reports.php">Reports</a></li>
                        <li><a href="manage_products.php">Products</a></li>
                        <li><a href="logout.php" class="logout-btn">Logout</a></li>
                    <?php elseif (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'staff'): ?>
                        <li><a href="queue.php">Queue</a></li>
                        <li><a href="reports.php">Reports</a></li>
                        <li><a href="logout.php" class="logout-btn">Logout</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </header>

        <main>

            <?php if ($has_access): ?>
                <!-- Date Range Filter -->
                <section class="filter-section">
                    <form method="GET" class="date-filter-form">
                        <div class="form-group">
                            <label for="start_date">Start Date:</label>
                            <input type="date" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                        </div>
                        <div class="form-group">
                            <label for="end_date">End Date:</label>
                            <input type="date" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                        </div>
                        <button type="submit" class="btn">Apply Filter</button>
                    </form>
                </section>

                <!-- Sales Overview -->
                <section class="reports-grid">
                    <div class="report-card">
                        <h3>Sales Overview</h3>
                        <canvas id="salesChart"></canvas>
                    </div>

                    <!-- Top Products -->
                    <div class="report-card">
                        <h3>Top Selling Products</h3>
                        <div class="table-responsive">
                            <table class="reports-table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity Sold</th>
                                        <th>Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($product = $products_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td><?php echo $product['total_quantity']; ?></td>
                                        <td>₱<?php echo number_format($product['total_revenue'], 2); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>


                </section>

            <?php else: ?>
                <section class="hero">
                    <h2>Access Denied</h2>
                    <p>You do not have permission to view this page.</p>
                </section>
            <?php endif; ?>

        </main>

        <footer>
            <p>&copy; 2025 Let's Meet Cafe. All rights reserved.</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php if ($has_access): ?>
    <script>
    // Sales Chart
    const salesData = <?php
        // Re-fetch data if needed, or ensure $sales_result is available
        // For simplicity, assuming $sales_result is available from the top PHP block
        // In a more complex app, you might refetch data here if the top block is skipped
        $sales_data_chart = [];
        // Rewind result set if already fetched
        if ($sales_result->num_rows > 0) {
             $sales_result->data_seek(0);
        }
        while ($row = $sales_result->fetch_assoc()) {
            $sales_data_chart[] = $row;
        }
        echo json_encode($sales_data_chart);
    ?>;

    new Chart(document.getElementById('salesChart'), {
        type: 'line',
        data: {
            labels: salesData.map(item => item.date),
            datasets: [{
                label: 'Daily Sales',
                data: salesData.map(item => item.total_sales),
                borderColor: '#6F4E37',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Daily Sales Trend'
                }
            }
        }
    });

    // Status Distribution Chart
    const statusData = <?php
        // Re-fetch data if needed, or ensure $status_result is available
        // Rewind result set if already fetched
         if ($status_result->num_rows > 0) {
             $status_result->data_seek(0);
        }
        $status_data_chart = [];
        while ($row = $status_result->fetch_assoc()) {
            $status_data_chart[] = $row;
        }
        echo json_encode($status_data_chart);
    ?>;

    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: statusData.map(item => item.status),
            datasets: [{
                data: statusData.map(item => item.count),
                backgroundColor: ['#6F4E37', '#B87E5C', '#D4B996', '#4A6741']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Order Status Distribution'
                }
            }
        }
    });
    </script>
    <?php endif; ?>
</body>
</html> 