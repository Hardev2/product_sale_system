<?php

date_default_timezone_set('Asia/Manila');
include 'src/config/database.php';  // Include your database connection file

// Handle form submission to add sales
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $date = date('Y-m-d'); // Get today's date

    // Start a transaction to ensure data consistency
    mysqli_begin_transaction($conn);

    try {
        // Insert the sales data into the sales table
        $stmt = $conn->prepare("INSERT INTO sales (product_id, quantity, sale_date) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $product_id, $quantity, $date);
        $stmt->execute();
        $stmt->close();

        // Update the product's quantity in the products table
        $updateStmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ? AND quantity >= ?");
        $updateStmt->bind_param("iii", $quantity, $product_id, $quantity);
        $updateStmt->execute();

        if ($updateStmt->affected_rows === 0) {
            throw new Exception("Not enough stock available for this product.");
        }

        $updateStmt->close();

        // Commit the transaction
        mysqli_commit($conn);
    } catch (Exception $e) {
        // Rollback transaction if there's an error
        mysqli_roll_back($conn);
        $error_message = $e->getMessage();
    }
}

// Fetch products for the dropdown
$products = mysqli_query($conn, "SELECT id, name, quantity FROM products ORDER BY name");

// Fetch today's sales tally
$sales_tally = mysqli_query($conn, "
    SELECT p.name AS product_name, SUM(s.quantity) AS total_sold, p.price AS product_price, SUM(s.quantity * p.price) AS total_income
    FROM sales s
    JOIN products p ON s.product_id = p.id
    WHERE s.sale_date = CURDATE()
    GROUP BY s.product_id
    ORDER BY total_sold DESC
");

// Calculate the total income from all sales
$total_income_query = mysqli_query($conn, "
    SELECT SUM(s.quantity * p.price) AS total_income
    FROM sales s
    JOIN products p ON s.product_id = p.id
    WHERE s.sale_date = CURDATE()
");
$total_income_row = mysqli_fetch_assoc($total_income_query);
$total_income = $total_income_row['total_income'] ?? 0;
?>
<?php include 'public/components/header.php' ?>
<body>
    <div class="container">
    <?php include 'public/components/side-bar.php' ?>
       <div class="hero">
            <div class="content">

            <h1><?php echo isset($title) ? $title : 'Default Title'; ?> <?php echo date('F j, Y'); ?></h1>
            <!-- Input Form -->
            <form method="POST" action="">
                <div class="form-container">
                        <div class="form-row">
                            <div class="form-col">
                                <label for="product_id">Select Product:</label>
                                <select name="product_id" id="product_id" required>
                                    <option value="" disabled selected>-- Select Product --</option>
                                    <?php while ($row = mysqli_fetch_assoc($products)): ?>
                                        <option value="<?php echo $row['id']; ?>">
                                            <?php echo htmlspecialchars($row['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                    <div class="form-row">
                        <div class="form-col">
                            <label for="quantity">Quantity Sold:</label>
                            <input type="number" name="quantity" id="quantity" placeholder="Type here.." min="1" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <button type="submit"><i class="fa-solid fa-square-plus"></i>Add Sale</button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Tally of Sales -->
          
            <div class="tally_container">
            <h2>Sales Tally</h2>
            <ul>
                <?php if (mysqli_num_rows($sales_tally) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($sales_tally)): ?>
                        <li><i class="fa-solid fa-box"></i>
                            <span class="product-name"><?php echo htmlspecialchars($row['product_name']); ?></span>
                            <span class="quantity"><?php echo htmlspecialchars($row['total_sold']); ?> sold</span>
                            <span class="price">₱<?php echo number_format($row['product_price'], 2); ?> each</span>
                            <span class="income">₱<?php echo number_format($row['total_income'], 2); ?> total</span>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No sales recorded for today.</p>
                <?php endif; ?>
            </ul>
            <h3>Total Income: <span>₱<?php echo number_format($total_income, 2); ?></span></h3>
            </div>
            </div>
       </div>
    </div>
</body>
</html>
