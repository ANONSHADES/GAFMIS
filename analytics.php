<?php
// Include necessary files
include 'connect.php';
include 'functions.php';

// Fetch products data
$products = getProducts();

// Calculate total capacity and find most and least sold products
$totalCapacity = 0;
$mostSoldProduct = null;
$leastSoldProduct = null;
$maxSales = 0;
$minSales = PHP_INT_MAX;

foreach ($products as $product) {
    $totalCapacity += $product['capacity'];
    if ($product['total_sales'] > $maxSales) {
        $maxSales = $product['total_sales'];
        $mostSoldProduct = $product;
    }
    if ($product['total_sales'] < $minSales) {
        $minSales = $product['total_sales'];
        $leastSoldProduct = $product;
    }
}

// Check for understocked products
$understockedProducts = array();
foreach ($products as $product) {
    if ($product['capacity'] < 50) {
        $understockedProducts[] = $product;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Analytics</title>
    <!-- Include Chart.js library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h1>Product Analytics</h1>

    <!-- Pie Chart for Most and Least Sold Products -->
    <canvas id="productPieChart" width="400" height="400"></canvas>

    <!-- Display Understocked Products -->
    <?php if (!empty($understockedProducts)) : ?>
        <div class="warning">
            <h2>Understocked Products</h2>
            <ul>
                <?php foreach ($understockedProducts as $product) : ?>
                    <li><?php echo $product['name']; ?></li>
                <?php endforeach; ?>
            </ul>
            <p>These products are below 50% of their capacity and may need restocking.</p>
        </div>
    <?php endif; ?>

    <!-- Reports Button -->
    <button onclick="printReport()">Print Report</button>

    <script>
        // Function to print report
        function printReport() {
            window.print();
        }

        // Data for pie chart
        var ctx = document.getElementById('productPieChart').getContext('2d');
        var productPieChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Most Sold', 'Least Sold'],
                datasets: [{
                    label: 'Product Sales',
                    data: [<?php echo $mostSoldProduct['total_sales']; ?>, <?php echo $leastSoldProduct['total_sales']; ?>],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.5)',
                        'rgba(54, 162, 235, 0.5)',
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: false
            }
        });
    </script>
</body>
</html>
