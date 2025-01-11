<?php
include 'src/config/database.php';

// Get creditor ID from the URL
$creditor_id = isset($_GET['creditor_id']) ? intval($_GET['creditor_id']) : 0;

// Fetch creditor details
$creditor_query = $conn->prepare("SELECT name FROM creditors WHERE id = ?");
$creditor_query->bind_param("i", $creditor_id);
$creditor_query->execute();
$creditor_result = $creditor_query->get_result();
$creditor = $creditor_result->fetch_assoc();

if (!$creditor) {
    die("Creditor not found.");
}

// Fetch credited products and calculate total
$products_query = $conn->prepare("
    SELECT p.id AS product_id, p.name AS product_name, cp.quantity, 
           (p.price + 0.25) AS credited_price, cp.credit_date, 
           ((p.price + 0.25) * cp.quantity) AS total_price
    FROM creditor_products cp
    JOIN products p ON cp.product_id = p.id
    WHERE cp.creditor_id = ? AND cp.status = 'unpaid'
    ORDER BY cp.credit_date DESC
");
$products_query->bind_param("i", $creditor_id);
$products_query->execute();
$products_result = $products_query->get_result();

$total_credits = 0;
$product_rows = [];
while ($row = $products_result->fetch_assoc()) {
    $total_credits += $row['total_price'];
    $product_rows[] = $row;
}

// Handle "Mark as Paid" button click
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_paid'])) {
    $conn->begin_transaction();

    try {
        // Insert each credited product into the sales table
        $sales_query = $conn->prepare("
            INSERT INTO sales (product_id, quantity, credited_price, sale_date) 
            VALUES (?, ?, ?, NOW())
        ");

        // Update the status in creditor_products
        $update_status_query = $conn->prepare("
            UPDATE creditor_products SET status = 'paid'
            WHERE creditor_id = ? AND product_id = ? AND status = 'unpaid' 
        ");

        // Insert and update for each product
        foreach ($product_rows as $row) {
            // Check if the product has already been added to sales
            $check_sales_query = $conn->prepare("SELECT 1 FROM sales WHERE product_id = ? AND sale_date = CURDATE()");
            $check_sales_query->bind_param("i", $row['product_id']);
            $check_sales_query->execute();
            $check_sales_result = $check_sales_query->get_result();

            // Only insert into sales if it hasn't already been recorded for today
            if ($check_sales_result->num_rows == 0) {
                // Insert the sale
                $sales_query->bind_param("iid", $row['product_id'], $row['quantity'], $row['credited_price']);
                $sales_query->execute();
            }

            // Update the product status to 'paid'
            $update_status_query->bind_param("ii", $creditor_id, $row['product_id']);
            $update_status_query->execute();
        }

        // Commit transaction
        $conn->commit();

        // Redirect to avoid duplicate submission
        header("Location: router.php?page=creditor_details&creditor_id=$creditor_id&status=paid");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        die("Error processing payment: " . $e->getMessage());
    }
}

// Continue with fetching creditor products for history
$creditor_products_query = "
    SELECT c.name AS creditor_name, p.name AS product_name, cp.status, cp.quantity, (p.price + 0.25) AS credited_price, cp.credit_date
    FROM creditor_products cp
    JOIN creditors c ON cp.creditor_id = c.id
    JOIN products p ON cp.product_id = p.id
    WHERE cp.creditor_id = '$creditor_id' AND cp.status = 'paid'
    ORDER BY c.name, cp.credit_date DESC;
";

$creditor_products = mysqli_query($conn, $creditor_products_query);
?>

<?php include 'public/components/header.php' ?>
<body>
   <div class="container">
   <?php include 'public/components/side-bar.php' ?>
    <div class="hero">
        <div class="content">
            <h1>Credited Products for <?php echo htmlspecialchars($creditor['name']); ?></h1>
            <?php if (isset($_GET['status']) && $_GET['status'] === 'paid'): ?>
                <p style="color: green;">Payment successfully recorded!</p>
            <?php endif; ?>
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Credited Price</th>
                        <th>Total Price</th>
                        <th>Credit Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($product_rows)): ?>
                        <?php foreach ($product_rows as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                <td><?php echo $row['quantity']; ?></td>
                                <td>₱<?php echo number_format($row['credited_price'], 2); ?></td>
                                <td>₱<?php echo number_format($row['total_price'], 2); ?></td>
                                <td><?php echo (new DateTime($row['credit_date']))->format('F j, Y'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No products credited to this creditor.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($product_rows)): ?>
                    <tfoot>
                        <tr>
                            <td colspan="1" style="text-align: right;"><strong>Total Credits:</strong></td>
                            <td colspan="4"><strong>₱<?php echo number_format($total_credits, 2); ?></strong></td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
            <?php if (!empty($product_rows)): ?>
                <form method="POST" style="margin-top: 20px;">
                    <button type="submit" name="mark_paid" class="btn-paid"><i class="fa-solid fa-thumbs-up"></i> Mark as Paid</button>
                </form>
            <?php endif; ?>
            <div class="action-btn">
                <a href="router.php?page=creditor_list" class="btn-view back-btn"><i class="fa-solid fa-arrow-left"></i> Back to Creditors List</a>
            </div>
            <h3 style="margin-top:20px">Credits History of <?=$creditor['name']?></h3>
            <table id="myTable">
                    <thead>
                        <tr>
                            <th>Creditor Name</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Credited Price</th>
                            <th>Status</th>
                            <th>Credit Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($creditor_products)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['creditor_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                <td><?php echo $row['quantity']; ?></td>
                                <td>₱<?php echo number_format($row['credited_price'], 2); ?></td>
                                <td><?php echo $row['status']; ?></td>
                                <td><?php echo (new DateTime($row['credit_date']))->format('F j, Y'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
        </div>
    </div>
   </div>
</body>
</html>
