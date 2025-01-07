<?php
include 'src/config/database.php'; // Include your database connection file

// Fetch total daily sales and income
$daily_income_summary = mysqli_query($conn, "
    SELECT 
        s.sale_date, 
        SUM(s.quantity) AS total_quantity_sold, 
        SUM(s.quantity * p.price) AS total_income 
    FROM 
        sales s
    JOIN 
        products p ON s.product_id = p.id
    GROUP BY 
        s.sale_date
    ORDER BY 
        s.sale_date DESC
");
?>

<?php include 'public/components/header.php' ?>
<body>
    <div class="container">
    <?php include 'public/components/side-bar.php' ?>
       <div class="hero">
            <div class="content">
            <h1><?php echo isset($title) ? $title : 'Default Title'; ?></h1>
                <?php if (mysqli_num_rows($daily_income_summary) > 0): ?>
                    <table id="myTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Total Quantity Sold</th>
                                <th>Total Income</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($daily_income_summary)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars((new DateTime($row['sale_date']))->format('F j, Y')); ?></td>
                                    <td><?php echo htmlspecialchars($row['total_quantity_sold']); ?></td>
                                    <td>₱<?php echo number_format($row['total_income'], 2); ?></td>
                                    <td>
                                        <a href="router.php?page=detail&date=<?php echo urlencode($row['sale_date']); ?>" class="btn-view"><i class="fa-solid fa-eye"></i>View</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="no-records">No daily income summary available.</p>
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
