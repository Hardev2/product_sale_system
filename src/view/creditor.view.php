<?php
include 'src/config/database.php';

// Handle adding a credit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $creditor_id = $_POST['creditor_id'];
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $credit_date = date('Y-m-d');

    // Fetch the original product details
    $product_query = $conn->prepare("SELECT price, quantity AS available_quantity FROM products WHERE id = ?");
    $product_query->bind_param("i", $product_id);
    $product_query->execute();
    $product_result = $product_query->get_result();
    $product = $product_result->fetch_assoc();

    if ($product) {
        $new_price = $product['price'] + 0.25; // Add 25 cents to the price
        $available_quantity = $product['available_quantity'];

        // Check if there is enough stock
        if ($available_quantity >= $quantity) {
            // Insert into creditor_products table
            $stmt = $conn->prepare("INSERT INTO creditor_products (creditor_id, product_id, quantity, credit_date) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiis", $creditor_id, $product_id, $quantity, $credit_date);
            $stmt->execute();
            $stmt->close();

            // Update the products table to decrease the quantity
            $update_product_query = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
            $update_product_query->bind_param("ii", $quantity, $product_id);
            $update_product_query->execute();
            $update_product_query->close();
        } else {
            echo "<script>alert('Not enough stock available for this product.');</script>";
        }
    }
}

// Fetch creditors and products
$creditors = mysqli_query($conn, "SELECT id, name FROM creditors ORDER BY name");
$products = mysqli_query($conn, "SELECT id, name, price, quantity FROM products ORDER BY name");


$creditor_products_query = "
    SELECT c.name AS creditor_name, p.name AS product_name, cp.quantity, (p.price + 0.25) AS credited_price, cp.credit_date
    FROM creditor_products cp
    JOIN creditors c ON cp.creditor_id = c.id
    JOIN products p ON cp.product_id = p.id
    ORDER BY c.name, cp.credit_date DESC
";

$creditor_products = mysqli_query($conn, $creditor_products_query);
?>

<?php include 'public/components/header.php' ?>
<body>
   <div class="container">
   <?php include 'public/components/side-bar.php' ?>
    <div class="hero">
        <div class="content">
        <h1><?php echo isset($title) ? $title : 'Default Title'; ?></h1>
            <form method="POST" action="creditor.php">
               <div class="form-container">
               <div class="form-row">
                    <div class="form-col">
                        <label for="creditor_id">Select Creditor:</label>
                        <select name="creditor_id" id="creditor_id" required>
                            <option value="" disabled selected>-- Select Creditor --</option>
                            <?php while ($creditor = mysqli_fetch_assoc($creditors)): ?>
                                <option value="<?php echo $creditor['id']; ?>"><?php echo htmlspecialchars($creditor['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

              <div class="form-row">
                    <div class="form-col">
                        <label for="product_id">Select Product:</label>
                        <select name="product_id" id="product_id" required>
                            <option value="" disabled selected>-- Select Product --</option>
                            <?php while ($product = mysqli_fetch_assoc($products)): ?>
                                <option value="<?php echo $product['id']; ?>">
                                    <?php echo htmlspecialchars($product['name']); ?> 
                                    (₱<?php echo number_format($product['price'] + 0.25, 2); ?>) 
                                    [Stock: <?php echo $product['quantity']; ?>]
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
              </div>

              <div class="form-row">
                <div class="form-col">
                    <label for="quantity">Quantity:</label>
                    <input type="number" name="quantity" id="quantity" placeholder="Type here.." min="1" required>
                </div>
              </div>

              <div class="form-row">
                <div class="form-col">
                    <button type="submit"><i class="fa-solid fa-square-plus"></i> Add Credit</button>
                </div>
              </div>
               </div>
            </form>

            <div class="creditor_table">
            <h1>Creditor Product List</h1>
                <table id="myTable">
                    <thead>
                        <tr>
                            <th>Creditor Name</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Credited Price</th>
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
                                <td><?php echo (new DateTime($row['credit_date']))->format('F j, Y'); ?></td>

                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
   </div>

   <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script>
    $(document).ready( function () {
        $('#myTable').DataTable();
    });
    </script>
</body>
</html>
