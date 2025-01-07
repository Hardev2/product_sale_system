<?php

date_default_timezone_set('Asia/Manila');
include 'src/config/database.php'; // Include your database connection file

// Get the total income for today
$today = date('Y-m-d');
$today_income_query = mysqli_query($conn, "
    SELECT SUM(s.quantity * p.price) AS total_income 
    FROM sales s
    JOIN products p ON s.product_id = p.id
    WHERE s.sale_date = '$today'
");
$today_income = mysqli_fetch_assoc($today_income_query);

// Get the total income for the current month
$current_month = date('Y-m');
$monthly_income_query = mysqli_query($conn, "
    SELECT SUM(s.quantity * p.price) AS total_income 
    FROM sales s
    JOIN products p ON s.product_id = p.id
    WHERE s.sale_date LIKE '$current_month%'
");
$monthly_income = mysqli_fetch_assoc($monthly_income_query);

// Get the total income summary for each day in the current month
$daily_income_summary = mysqli_query($conn, "
    SELECT 
        s.sale_date, 
        SUM(s.quantity * p.price) AS total_income 
    FROM sales s
    JOIN products p ON s.product_id = p.id
    WHERE s.sale_date LIKE '$current_month%'
    GROUP BY s.sale_date
    ORDER BY s.sale_date DESC
");

// Get the total income summary for the last 30 days
$last_30_days = mysqli_query($conn, "
    SELECT 
        s.sale_date, 
        SUM(s.quantity * p.price) AS total_income 
    FROM sales s
    JOIN products p ON s.product_id = p.id
    WHERE s.sale_date >= CURDATE() - INTERVAL 30 DAY
    GROUP BY s.sale_date
    ORDER BY s.sale_date DESC
");

// Prepare data for graphs
$daily_labels = [];
$daily_data = [];
while ($row = mysqli_fetch_assoc($daily_income_summary)) {
    $daily_labels[] = $row['sale_date'];
    $daily_data[] = $row['total_income'];
}

$monthly_labels = [];
$monthly_data = [];
while ($row = mysqli_fetch_assoc($last_30_days)) {
    $monthly_labels[] = $row['sale_date'];
    $monthly_data[] = $row['total_income'];
}

$total_products_query = mysqli_query($conn, "SELECT COUNT(*) AS total_products FROM products");
$total_products = mysqli_fetch_assoc($total_products_query)['total_products'];


$overall_income_query = mysqli_query($conn, "
    SELECT SUM(s.quantity * p.price) AS total_income 
    FROM sales s
    JOIN products p ON s.product_id = p.id
");
$overall_income = mysqli_fetch_assoc($overall_income_query)['total_income'];
?>

<?php include 'public/components/header.php' ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<body>
    <div class="container">
        <?php include 'public/components/side-bar.php' ?>
        <div class="hero">
            <div class="content">
            <h1><?php echo isset($title) ? $title : 'Default Title'; ?></h1>
                    <div class="summary_sales">
                    <div class="sale_card total_products">
                            <h3><i class="fa-solid fa-box"></i><?php echo $total_products; ?></h3>
                            <p>Total Products</p>
                        </div>
                        <div class="sale_card income_day">
                            <h3>₱<?php echo number_format($today_income['total_income'], 2); ?></h3>
                            <p>Total Income Today</p>
                        </div>
                        <div class="sale_card income_month">
                            <h3>₱<?php echo number_format($monthly_income['total_income'], 2); ?></h3>
                            <p>Total Income This Month</p>
                        </div>
                        <div class="sale_card overall_income">
                            <h3>₱<?php echo number_format($overall_income, 2); ?></h3>
                            <p>Overall Total Income</p>
                        </div>
                    </div>
                    <div class="graph_wrapper">
                        <div class="day_chart">
                            <h2>Daily Income Growth</h2>
                            <canvas id="dailyIncomeChart" ></canvas>
                        </div>
                        <div class="month_chart">
                            <h2>Monthly Income Growth</h2>
                            <canvas id="monthlyIncomeChart"></canvas>
                        </div>
                    </div>
            </div>
        </div>
    </div>

    <script>
        // Daily Income Chart
        const dailyIncomeCtx = document.getElementById('dailyIncomeChart').getContext('2d');
        const dailyIncomeChart = new Chart(dailyIncomeCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_reverse($daily_labels)); ?>,
                datasets: [{
                    label: 'Daily Income',
                    data: <?php echo json_encode(array_reverse($daily_data)); ?>,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Total Income (₱)'
                        }
                    }
                }
            }
        });

        // Monthly Income Chart
        const monthlyIncomeCtx = document.getElementById('monthlyIncomeChart').getContext('2d');
        const monthlyIncomeChart = new Chart(monthlyIncomeCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_reverse($monthly_labels)); ?>,
                datasets: [{
                    label: 'Monthly Income',
                    data: <?php echo json_encode(array_reverse($monthly_data)); ?>,
                    borderColor: 'rgba(153, 102, 255, 1)',
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Total Income (₱)'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>