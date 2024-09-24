<?php
session_start();
include 'connect.php';

// Initialize variables for error message and result
$error = '';
$result = [];
$total_sales = 0;

// Function to fetch product sales data
function fetchProductSalesData($con, $start_date = '', $end_date = '') {
    $sql = "SELECT i.product_name, SUM(st.quantity) AS total_quantity_sold, SUM(s.subtotal) AS total_price
            FROM sales s 
            JOIN sale_transactions st ON s.sales_id = st.sales_id
            JOIN inventory i ON st.product_id = i.id";
    
    // Add date filtering if provided
    if (!empty($start_date) && !empty($end_date)) {
        $sql .= " WHERE DATE(s.date) BETWEEN ? AND ?";
    }

    $sql .= " GROUP BY i.product_name";

    // Prepare the SQL statement
    $stmt = mysqli_prepare($con, $sql);
    if ($stmt) {
        // Bind parameters if dates are provided
        if (!empty($start_date) && !empty($end_date)) {
            mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
        }
        
        // Execute the statement
        mysqli_stmt_execute($stmt);

        // Get result
        $result = mysqli_stmt_get_result($stmt);

        // Initialize variables for sales data
        $report_data = [];
        $total_sales = 0;

        // Fetch and store sales data
        while ($row = mysqli_fetch_assoc($result)) {
            $total_sales += $row['total_price'];
            $report_data[] = [
                'product_name' => $row['product_name'],
                'total_quantity_sold' => $row['total_quantity_sold'],
                'total_price' => $row['total_price']
            ];
        }

        // Close statement and free result set
        mysqli_stmt_close($stmt);
        mysqli_free_result($result);

        return ['report_data' => $report_data, 'total_sales' => $total_sales];
    } else {
        // Handle statement preparation error
        $error = mysqli_error($con);
        return ['error' => $error];
    }
}

// Function to fetch the most sold and least sold products
function fetchMostAndLeastSoldProducts($con, $start_date = '', $end_date = '') {
    $most_sold_query = "SELECT i.product_name, SUM(st.quantity) AS total_quantity_sold
                        FROM sales s
                        JOIN sale_transactions st ON s.sales_id = st.sales_id
                        JOIN inventory i ON st.product_id = i.id";

    // Add date filtering if provided
    if (!empty($start_date) && !empty($end_date)) {
        $most_sold_query .= " WHERE DATE(s.date) BETWEEN ? AND ?";
    }

    $most_sold_query .= " GROUP BY i.product_name
                         ORDER BY total_quantity_sold DESC
                         LIMIT 1";

    // Prepare the query
    $stmt = mysqli_prepare($con, $most_sold_query);
    if ($stmt) {
        // Bind parameters if dates are provided
        if (!empty($start_date) && !empty($end_date)) {
            mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
        }

        // Execute the statement
        mysqli_stmt_execute($stmt);

        // Get result
        $result = mysqli_stmt_get_result($stmt);
        
        // Fetch most sold product
        $most_sold_product = mysqli_fetch_assoc($result);

        // Close statement and free result set
        mysqli_stmt_close($stmt);
        mysqli_free_result($result);

        // Query for least sold product
        $least_sold_query = "SELECT i.product_name, SUM(st.quantity) AS total_quantity_sold
                             FROM sales s
                             JOIN sale_transactions st ON s.sales_id = st.sales_id
                             JOIN inventory i ON st.product_id = i.id";

        // Add date filtering if provided
        if (!empty($start_date) && !empty($end_date)) {
            $least_sold_query .= " WHERE DATE(s.date) BETWEEN ? AND ?";
        }

        $least_sold_query .= " GROUP BY i.product_name
                              ORDER BY total_quantity_sold ASC
                              LIMIT 1";

        // Prepare the query
        $stmt_least = mysqli_prepare($con, $least_sold_query);
        if ($stmt_least) {
            // Bind parameters if dates are provided
            if (!empty($start_date) && !empty($end_date)) {
                mysqli_stmt_bind_param($stmt_least, "ss", $start_date, $end_date);
            }

            // Execute the statement
            mysqli_stmt_execute($stmt_least);

            // Get result
            $result_least = mysqli_stmt_get_result($stmt_least);
            
            // Fetch least sold product
            $least_sold_product = mysqli_fetch_assoc($result_least);

            // Close statement and free result set
            mysqli_stmt_close($stmt_least);
            mysqli_free_result($result_least);

            return ['most_sold' => $most_sold_product, 'least_sold' => $least_sold_product];
        } else {
            // Handle statement preparation error for least sold query
            $error_least = mysqli_error($con);
            return ['error' => $error_least];
        }
    } else {
        // Handle statement preparation error for most sold query
        $error_most = mysqli_error($con);
        return ['error' => $error_most];
    }
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_filter'])) {
    // Get start date and end date from form
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Fetch product sales data and most/least sold products with date filter
    $sales_data = fetchProductSalesData($con, $start_date, $end_date);
    if (isset($sales_data['error'])) {
        $error = $sales_data['error'];
    } else {
        $report_data = $sales_data['report_data'];
        $total_sales = $sales_data['total_sales'];
        
        $most_least_sold_products = fetchMostAndLeastSoldProducts($con, $start_date, $end_date);
        if (isset($most_least_sold_products['error'])) {
            $error = $most_least_sold_products['error'];
        } else {
            $most_sold_product = $most_least_sold_products['most_sold']['product_name'] ?? '';
            $least_sold_product = $most_least_sold_products['least_sold']['product_name'] ?? '';
        }
    }
}

// Close MySQLi connection
mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        .content {
            max-width: 800px;
            margin: auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .print-button-section {
            text-align: right;
            margin-bottom: 20px;
        }
        .back-button {
            margin-bottom: 20px;
        }
        @media print {
            .print-button-section, .back-button, .print-btn, h2 {
                display: none !important;
            }
            body {
                visibility: hidden;
            }
            .content {
                visibility: visible;
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                margin: 0;
                padding: 0;
                background-color: #fff;
            }
        }
    </style>
</head>
<body>
    <div class="content">
        <div class="back-button">
            <a href="user.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
        </div>

        <div class="print-button-section">
            <button class="btn btn-primary print-btn" onclick="window.print()">Print Report</button>
        </div>

        <h2 class="text-center">Sales Reports</h2>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
<div class="form-row align-items-center">
    <div class="col-md-4 mb-3">
        <label for="start_date">Start Date:</label>
        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo isset($start_date) ? htmlspecialchars($start_date) : ''; ?>">
    </div>
    <div class="col-md-4 mb-3">
        <label for="end_date">End Date:</label>
        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo isset($end_date) ? htmlspecialchars($end_date) : ''; ?>">
    </div>
    <div class="col-md-4 mb-3">
        <label></label>
        <button type="submit" class="btn btn-primary" name="apply_filter">Apply Filter</button>
    </div>
</div>
</form>

<?php if (!empty($report_data)) : ?>
<h3 class="mt-4">Product Sales Report</h3>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Product Name</th>
            <th>Total Quantity Sold (Kg)</th>
            <th>Total Price (Ksh)</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($report_data as $row) : ?>
            <tr>
                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                <td><?php echo htmlspecialchars($row['total_quantity_sold']); ?></td>
                <td><?php echo htmlspecialchars($row['total_price']); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<p><strong>Total Sales: <?php echo number_format($total_sales, 2); ?> Ksh</strong></p>

<div class="row">
    <div class="col-md-6">
        <h3>Most Sold Product: <?php echo $most_sold_product; ?></h3>
        <div id="most-sold-chart" style="height: 300px;"></div>
    </div>
    <?php if (!empty($least_sold_product)) : ?>
    <div class="col-md-6">
        <h3>Least Sold Product: <?php echo $least_sold_product; ?></h3>
        <div id="least-sold-chart" style="height: 300px;"></div>
    </div>
    <?php endif; ?>
</div>
<?php elseif ($error) : ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php else : ?>
<div class="alert alert-info">No sales data found.</div>
<?php endif; ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
<script>
google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(drawCharts);

function drawCharts() {
var mostSoldData = new google.visualization.DataTable();
mostSoldData.addColumn('string', 'Product');
mostSoldData.addColumn('number', 'Sales');

var leastSoldData = new google.visualization.DataTable();
leastSoldData.addColumn('string', 'Product');
leastSoldData.addColumn('number', 'Sales');

<?php
    $most_sold_chart_data = [];
    $least_sold_chart_data = [];

    foreach ($report_data as $row) {
        $most_sold_chart_data[] = "['" . $row['product_name'] . "', " . $row['total_quantity_sold'] . "]";
        if (!empty($least_sold_product) && $row['product_name'] === $least_sold_product) {
            $least_sold_chart_data[] = "['" . $row['product_name'] . "', " . $row['total_quantity_sold'] . "]";
        }
    }

    echo "mostSoldData.addRows([" . implode(',', $most_sold_chart_data) . "]);";
    if (!empty($least_sold_chart_data)) {
        echo "leastSoldData.addRows([" . implode(',', $least_sold_chart_data) . "]);";
    }
?>

var chartOptions = {
    pieSliceText: 'label',
    slices: {
        0: { offset: 0.1 }, // Adjust offset for most sold product
        1: { offset: 0.0 }  // Adjust offset for least sold product
    }
};

var mostSoldChart = new google.visualization.PieChart(document.getElementById('most-sold-chart'));
mostSoldChart.draw(mostSoldData, chartOptions);

<?php if (!empty($least_sold_chart_data)) : ?>
var leastSoldChart = new google.visualization.PieChart(document.getElementById('least-sold-chart'));
leastSoldChart.draw(leastSoldData, chartOptions);
<?php endif; ?>
}
</script>
</body>
</html>
