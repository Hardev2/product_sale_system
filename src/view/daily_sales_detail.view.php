<?php
include 'src/config/database.php'; // Include your database connection file

// Get the date from the URL
$sale_date = isset($_GET['date']) ? $_GET['date'] : null;

if (!$sale_date) {
    die("No date provided.");
}

// Fetch detailed sales data for the selected date, including cents in the price
$sales_details = mysqli_query($conn, "
    SELECT 
        p.name AS product_name, 
        (p.price + 0.25) AS credited_price,   -- Adding the cent part to the product price
        s.quantity, 
        (s.quantity * (p.price + 0.25)) AS total_income  -- Total income should consider the credited price
    FROM 
        sales s
    JOIN 
        products p ON s.product_id = p.id
    WHERE 
        s.sale_date = '$sale_date'
    ORDER BY 
        p.name ASC
");

// Initialize a variable to store the total income
$total_income = 0;
?>

<?php include 'public/components/header.php' ?>
<body>
    <div class="container">
        <?php include 'public/components/side-bar.php' ?>
        <div class="hero">
            <div class="content">
                <h1>Sales Details for <?php echo htmlspecialchars((new DateTime($sale_date))->format('F j, Y')); ?></h1>
                <?php if (mysqli_num_rows($sales_details) > 0): ?>
                    <table id="myTable">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Quantity Sold</th>
                                <th>Total Income</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($sales_details)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                    <td>₱<?php echo number_format($row['credited_price'], 2); ?></td>  <!-- Display credited price with cents -->
                                    <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                                    <td>₱<?php echo number_format($row['total_income'], 2); ?></td>
                                </tr>
                                <?php
                                // Add the current row's total income to the overall total
                                $total_income += $row['total_income'];
                                ?>
                            <?php endwhile; ?>
                        </tbody>
                        <!-- Display the overall total in the last row -->
                        <tfoot>
                            <tr>
                                <td colspan="3"><strong>Overall Total</strong></td>
                                <td><strong>₱<?php echo number_format($total_income, 2); ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                <?php else: ?>
                    <p class="no-records">No sales details available for this date.</p>
                <?php endif; ?>
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
