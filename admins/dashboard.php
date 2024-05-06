<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    include '../component/connection.php';
    session_start();

    $errorMessage = []; // Initialize an empty array for error messages

    $admin_id = $_SESSION['admin_id'];

    if(!isset($admin_id)){
        header('location:admin_login.php');
    }

    // Function to get total sales revenue
    function getTotalSalesRevenue($conn) {
        // Fetch total sales revenue from orders table
        $selectTotalRevenue = $conn->query("SELECT SUM(total_price) AS totalRevenue FROM oders WHERE payment_status = 'completed'");
        $fetchTotalRevenue = $selectTotalRevenue->fetch(PDO::FETCH_ASSOC);
        return $fetchTotalRevenue['totalRevenue'];
    }

    // Function to get number of orders
    function getNumberOfOrders($conn) {
        // Fetch number of orders from orders table
        $selectNumOrders = $conn->query("SELECT COUNT(*) AS numOrders FROM oders");
        $fetchNumOrders = $selectNumOrders->fetch(PDO::FETCH_ASSOC);
        return $fetchNumOrders['numOrders'];
    }

    // Function to get average order value
    function getAverageOrderValue($conn) {
        // Fetch average order value from orders table
        $selectAvgOrderValue = $conn->query("SELECT AVG(total_price) AS avgOrderValue FROM oders WHERE payment_status = 'completed'");
        $fetchAvgOrderValue = $selectAvgOrderValue->fetch(PDO::FETCH_ASSOC);
        return $fetchAvgOrderValue['avgOrderValue'];
    }

    // Function to calculate conversion rate
    function getConversionRate($conn) {
        // Fetch number of website visitors
        // (You need to implement the functionality to track website visitors separately)
        $numVisitors = 1000; // Placeholder value for demonstration
        // Fetch number of orders
        $numOrders = getNumberOfOrders($conn);
        // Calculate conversion rate
        if ($numVisitors > 0) {
            return round(($numOrders / $numVisitors) * 100, 2);
        } else {
            return 0;
        }
    }

    // Function to calculate customer lifetime value
    function getCustomerLifetimeValue($conn) {
        // Calculate total revenue from all orders
        $selectTotalRevenue = $conn->query("SELECT SUM(total_price) AS totalRevenue FROM oders WHERE payment_status = 'completed'");
        $fetchTotalRevenue = $selectTotalRevenue->fetch(PDO::FETCH_ASSOC);
        $totalRevenue = $fetchTotalRevenue['totalRevenue'];

        // Count the number of unique customers
        $selectUniqueCustomers = $conn->query("SELECT COUNT(DISTINCT user_id) AS numCustomers FROM oders");
        $fetchUniqueCustomers = $selectUniqueCustomers->fetch(PDO::FETCH_ASSOC);
        $numCustomers = $fetchUniqueCustomers['numCustomers'];

        // Calculate customer lifetime value
        if ($numCustomers > 0) {
            return round($totalRevenue / $numCustomers, 2);
        } else {
            return 0;
        }
    }

    // Function to calculate customer acquisition cost
    function getCustomerAcquisitionCost($conn) {
        // Calculate total marketing and sales expenses
        $totalExpenses = 1000; // Placeholder value for demonstration

        // Count the number of new customers acquired within the last month
        $query = "SELECT COUNT(*) AS numNewCustomers FROM users WHERE id IN (
            SELECT id FROM users WHERE id > (
                SELECT MAX(id) FROM users WHERE DATE_SUB(CURDATE(), INTERVAL 1 MONTH) <= created_at
            )
        )";
        $stmt = $conn->query($query);
        $fetchNewCustomers = $stmt->fetch(PDO::FETCH_ASSOC);
        $numNewCustomers = $fetchNewCustomers['numNewCustomers'];

        // Calculate customer acquisition cost
        if ($numNewCustomers > 0) {
            return round($totalExpenses / $numNewCustomers, 2);
        } else {
            return 0;
        }
    }

    // Function to calculate repeat purchase rate
    function getRepeatPurchaseRate($conn) {
        // Count the number of repeat orders
        $selectRepeatOrders = $conn->query("SELECT COUNT(*) AS numRepeatOrders FROM oders WHERE order_number IN (SELECT order_number FROM oders GROUP BY order_number HAVING COUNT(*) > 1)");
        $fetchRepeatOrders = $selectRepeatOrders->fetch(PDO::FETCH_ASSOC);
        $numRepeatOrders = $fetchRepeatOrders['numRepeatOrders'];

        // Count the total number of orders
        $selectTotalOrders = $conn->query("SELECT COUNT(*) AS numTotalOrders FROM oders");
        $fetchTotalOrders = $selectTotalOrders->fetch(PDO::FETCH_ASSOC);
        $numTotalOrders = $fetchTotalOrders['numTotalOrders'];

        // Calculate repeat purchase rate
        if ($numTotalOrders > 0) {
            return round(($numRepeatOrders / $numTotalOrders) * 100, 2);
        } else {
            return 0;
        }
    }

    // Function to calculate shopping cart abandonment rate
    function getShoppingCartAbandonmentRate($conn) {
        // Count the number of abandoned carts
        $selectAbandonedCarts = $conn->query("SELECT COUNT(*) AS numAbandonedCarts FROM cart WHERE status = 'abandoned'");
        $fetchAbandonedCarts = $selectAbandonedCarts->fetch(PDO::FETCH_ASSOC);
        $numAbandonedCarts = $fetchAbandonedCarts['numAbandonedCarts'];

        // Count the total number of initiated carts
        $selectInitiatedCarts = $conn->query("SELECT COUNT(*) AS numInitiatedCarts FROM cart");
        $fetchInitiatedCarts = $selectInitiatedCarts->fetch(PDO::FETCH_ASSOC);
        $numInitiatedCarts = $fetchInitiatedCarts['numInitiatedCarts'];

        // Calculate shopping cart abandonment rate
        if ($numInitiatedCarts > 0) {
            return round(($numAbandonedCarts / $numInitiatedCarts) * 100, 2);
        } else {
            return 0;
        }
    }

    // Function to get product performance metrics
    function getProductPerformance($conn) {
        // Parse the total_products column to extract product names
        $query = "SELECT total_products FROM oders";
        $stmt = $conn->query($query);
        $productNames = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $products = explode(",", $row['total_products']);
            foreach ($products as $product) {
                $productNames[] = trim($product);
            }
        }

        // Calculate performance metrics based on the extracted product names
        $productCounts = array_count_values($productNames);
        arsort($productCounts); // Sort product counts in descending order

        return $productCounts;
    }

    // Function to retrieve and display sales by channel
    function getSalesByChannel($conn) {
        // Query to retrieve sales by channel (e.g., website, mobile app, social media)
        $query = "SELECT channel, SUM(total_price) AS totalSales FROM oders GROUP BY channel";
        $stmt = $conn->query($query);
        $channels = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Display sales by channel
        echo "<h2>Sales by Channel</h2>";
        echo "<ul>";
        foreach ($channels as $channel) {
            echo "<li>" . $channel['channel'] . ": $" . $channel['totalSales'] . "</li>";
        }
        echo "</ul>";
    }

    // Function to retrieve and display sales by geography
    function getSalesByGeography($conn) {
        // Query to retrieve sales by geography (e.g., country, region)
        $query = "SELECT country, SUM(total_price) AS totalSales FROM oders GROUP BY country";
        $stmt = $conn->query($query);
        $geographies = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Display sales by geography
        echo "<h2>Sales by Geography</h2>";
        echo "<ul>";
        foreach ($geographies as $geography) {
            echo "<li>" . $geography['country'] . ": $" . $geography['totalSales'] . "</li>";
        }
        echo "</ul>";
    }

    // Function to retrieve and display sales by time period
    function getSalesByTimePeriod($conn) {
        // Query to retrieve sales by time period (e.g., daily, weekly, monthly)
        $query = "SELECT DATE(order_date) AS date, SUM(total_price) AS totalSales FROM oders GROUP BY DATE(order_date)";
        $stmt = $conn->query($query);
        $timePeriods = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Display sales by time period
        echo "<h2>Sales by Time Period</h2>";
        echo "<ul>";
        foreach ($timePeriods as $timePeriod) {
            echo "<li>" . $timePeriod['date'] . ": $" . $timePeriod['totalSales'] . "</li>";
        }
        echo "</ul>";
    }

    // Call functions to retrieve and display sales performance metrics
    // echo "<div>";
    // echo "<h2>Customer Lifetime Value: $" . getCustomerLifetimeValue($conn) . "</h2>";
    // echo "<h2>Customer Acquisition Cost: $" . getCustomerAcquisitionCost($conn) . "</h2>";
    // echo "<h2>Repeat Purchase Rate: " . getRepeatPurchaseRate($conn) . "%</h2>";
    // echo "<h2>Shopping Cart Abandonment Rate: " . getShoppingCartAbandonmentRate($conn) . "%</h2>";
    // getProductPerformance($conn);
    // getSalesByChannel($conn);
    // getSalesByGeography($conn);
    // getSalesByTimePeriod($conn);
    // echo "</div>";

    // Display error message 

    // include '../component/admin_header.php'; 
    if (!empty($errorMessage)) {
        foreach ($errorMessage as $error) {
            echo '
            <div class="message">
                <span>' . $error . '</span>
                <i class="fas fa-times" onclick="removeErrorMessage(this);"></i>
            </div>';
        }
    }
    echo "<script>
    function removeErrorMessage(element){
        element.parentElement.remove();
    }
    </script>"

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>dasboard</title>

    <!-- link to css file -->
    <link rel="stylesheet" href="../css/admin_style.css">
    <!-- link font awesom -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>

    <?php

        include '../component/admin_header.php';
    ?>

    <!-- ***************************************  admin dashboard section starts *************************************** -->
    <section class="dashboard">
        <h1 class="heading">dashboard</h1>

        <div class="box-container">

            <div class="box">
                <?php
                // Fetch profile data
                $select_profile = $conn->prepare("SELECT * FROM `admins` WHERE id = ?");
                $select_profile->execute([$admin_id]);
                $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

                // Check if $fetch_profile contains valid data
                if ($fetch_profile !== false) {
                    ?>
                    <h3>Welcome</h3>
                    <p><?= $fetch_profile['name']; ?></p>
                    <a href="update_profile.php" class="btn1">update profile</a>
                <?php } else {
                    // Handle case where profile data is not found
                    echo "Profile data not found.";
                }
                ?>
            </div>

            <div class="box">
                <?php

                    $total_pendings = 0;
                    $select_pendings = $conn ->prepare("SELECT * FROM `oders` WHERE payment_status = ?");
                    $select_pendings->execute(['pending']);
                    while($fetch_pendings = $select_pendings->fetch(PDO::FETCH_ASSOC)){
                    $total_pendings += $fetch_pendings['total_price'];
                    }
                ?>
                <h3><span>$</span><?= $total_pendings; ?><span>/-</span></h3>
                <p>total pendings</p>
                <a href="placed_orders.php" class="btn1">see orders</a>
            </div>

            <div class="box">
                <?php

                    $total_completes = 0;
                    $select_completes = $conn ->prepare("SELECT * FROM `oders` WHERE payment_status = ?");
                    $select_completes->execute(['completed']);
                    while($fetch_completes = $select_completes->fetch(PDO::FETCH_ASSOC)){
                        $total_completes += $fetch_completes['total_price'];
                    }
                ?>
                <h3><span>$</span><?= $total_completes; ?><span>/-</span></h3>
                <p>total completes</p>
                <a href="placed_orders.php" class="btn1">see orders</a>
            </div>

            <div class="box">
                <?php

                    $total_orders = 0;
                    $select_orders = $conn ->prepare("SELECT * FROM `oders`");
                    $select_orders->execute();
                    $numbers_of_orders = $select_orders->rowCount();
                    /*while($fetch_pendings = $select_pendings->fetch(PDO::FETCH_ASSOC)){
                        $total_pendings += $fetch_pendings['total_price '];
                    }*/
                ?>
                <h3><?= $numbers_of_orders;?></h3>
                <p>total orders</p>
                <a href="placed_orders.php" class="btn1">see orders</a>
            </div>

            <div class="box">
                <?php

                    $total_products = 0;
                    $select_products = $conn ->prepare("SELECT * FROM `products`");
                    $select_products->execute();
                    $numbers_of_products = $select_products->rowCount();
                ?>
                <h3><?= $numbers_of_products;?></h3>
                <p>products added</p>
                <a href="products.php" class="btn1">see products</a>
            </div>

            <div class="box">
                <?php

                    $total_users = 0;
                    $select_users = $conn ->prepare("SELECT * FROM `users`");
                    $select_users->execute();
                    $numbers_of_users = $select_users->rowCount();
                ?>
                <h3><?= $numbers_of_users;?></h3>
                <p>users accounts</p>
                <a href="users_accounts.php" class="btn1">see accounts</a>
            </div>

            <div class="box">
                <?php

                    $total_admins = 0;
                    $select_admins = $conn ->prepare("SELECT * FROM `admins`");
                    $select_admins->execute();
                    $numbers_of_admins = $select_admins->rowCount();
                ?>
                <h3><?= $numbers_of_admins;?></h3>
                <p>total admins</p>
                <a href="admin_accounts.php" class="btn1">see admins</a>
            </div>

            <div class="box">
                <?php

                    $total_messages = 0;
                    $select_messages = $conn ->prepare("SELECT * FROM `messages`");
                    $select_messages->execute();
                    $numbers_of_messages = $select_messages->rowCount();
                ?>
                <h3><?= $numbers_of_messages;?></h3>
                <p>new messages</p>
                <a href="messages.php" class="btn1">see messages</a>
            </div>

            <!-- Total Sales Revenue Box -->
            <div class="box">
                <h3>Total Sales Revenue</h3>
                <p>$<?= getTotalSalesRevenue($conn); ?></p>
            </div>

            <!-- Number of Orders Box -->
            <div class="box">
                <h3>Number of Orders</h3>
                <p><?= getNumberOfOrders($conn); ?></p>
            </div>

            <!-- Average Order Value Box -->
            <div class="box">
                <h3>Average Order Value</h3>
                <p>$<?= getAverageOrderValue($conn); ?></p>
            </div>

            <!-- Conversion Rate Box -->
            <div class="box">
                <h3>Conversion Rate</h3>
                <p><?= getConversionRate($conn); ?>%</p>
            </div>

            <!-- Customer Lifetime Value Box -->
            <div class="box">
                <h3>Customer Lifetime Value</h3>
                <p>$<?= getCustomerLifetimeValue($conn); ?></p>
            </div>

            <!-- Customer Acquisition Cost Box -->
            <div class="box">
                <h3>Customer Acquisition Cost</h3>
                <p>$<?= getCustomerAcquisitionCost($conn); ?></p>
            </div>

            <!-- Repeat Purchase Rate Box -->
            <div class="box">
                <h3>Repeat Purchase Rate</h3>
                <p><?= getRepeatPurchaseRate($conn); ?>%</p>
            </div>

            <!-- Product Performance Box -->
            <div class="box">
                <h3>Product Performance</h3>
    
                <!-- Display Product Performance Data -->
                <?php
                $productPerformance = getProductPerformance($conn);
                // Check if $productPerformance is an array
                if (is_array($productPerformance)) {
                    // If $productPerformance is an array, output it as a list
                    echo "<ul>";
                    foreach ($productPerformance as $product => $count) {
                        echo "<li>$product: $count</li>";
                    }
                    echo "</ul>";
                } else {
                    // If $productPerformance is not an array, output it directly
                    echo "<p>$productPerformance</p>";
                }
                ?>
            </div>

            <!-- Sales by Channel Box -->
            <div class="box">
                <h3>Sales by Channel</h3>
                <p><?= getSalesByChannel($conn); ?></p>
            </div>

            <!-- Sales by Geography Box -->
            <div class="box">
                <h3>Sales by Geography</h3>
                <p><?= getSalesByGeography($conn); ?></p>
            </div>

            <!-- Sales by Time Period Box -->
            <div class="box">
                <h3>Sales by Time Period</h3>
                <p><?= getSalesByTimePeriod($conn); ?></p>
            </div>

        </div>

    </section>
    <!-- ***************************************  admin dashboard section ends *************************************** -->

    <!-- custom jQuery link -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- custom js file link -->
    <script src="../js/admin_js.js"></script>
</body>
</html>
