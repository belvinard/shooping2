<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    include '../component/connection.php';
    session_start();

    // Check if admin is logged in
    if (!isset($_SESSION['admin_id'])) {
        header('location: admin_login.php');
        exit;
    }

    $errorMessage = []; // Initialize array to store error messages

    // Check if the logged-in user is an administrator
    if (!isAdmin($_SESSION['admin_id'])) {
        // Redirect to a different page or display an error message
        header('location: access_denied.php');
        exit;
    }

    // Function to check if a user is an administrator
    function isAdmin($admin_id) {
        global $conn;

        // Retrieve the first five administrators
        $select_first_five_admins = $conn->prepare("SELECT id FROM admins ORDER BY id LIMIT 5");
        $select_first_five_admins->execute();
        $first_five_admins = $select_first_five_admins->fetchAll(PDO::FETCH_COLUMN);

        // Update the roles of the first five administrators to 'administrator'
        $update_admin_roles = $conn->prepare("UPDATE admins SET role = 'administrator' WHERE id IN (".implode(",", $first_five_admins).")");
        $update_admin_roles->execute();

        // Return true if the user is an administrator
        return in_array($admin_id, $first_five_admins);
    }

    // Function to update roles of administrators
    function updateAdminRoles() {
        global $conn;

        // Retrieve the first five administrators
        $select_first_five_admins = $conn->prepare("SELECT id FROM `admins` ORDER BY id LIMIT 5");
        $select_first_five_admins->execute();
        $first_five_admins = $select_first_five_admins->fetchAll(PDO::FETCH_COLUMN);

        // Update the roles of the first five administrators to 'administrator'
        $update_admin_roles = $conn->prepare("UPDATE `admins` SET `role` = 'administrator' WHERE id IN (".implode(",", $first_five_admins).")");
        $update_admin_roles->execute();

        // Update the roles of the remaining administrators to 'regular'
        $update_regular_roles = $conn->prepare("UPDATE `admins` SET `role` = 'regular' WHERE id NOT IN (".implode(",", $first_five_admins).")");
        $update_regular_roles->execute();
    }

    // File size validation function
    function validateFile($file, $maxFileSize, $allowedExtensions) {
        $fileSize = $file['size'];
        $fileName = $file['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ($fileSize > $maxFileSize) {
            return "File size is too large (maximum 2MB)";
        } elseif (!in_array($fileExtension, $allowedExtensions)) {
            return "Invalid file format. Allowed formats: JPG, JPEG, PNG, WEBP";
        } else {
            return "";
        }
    }

    // Generate CSRF token and store it in the session
    /*if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Generate a random token
    }*/

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        
        // Validate CSRF token
        /*if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            // Invalid CSRF token, handle accordingly (e.g., show error message or redirect)
            exit("CSRF token validation failed.");
        }*/

        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $details = filter_input(INPUT_POST, 'details', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $image_01 = $_FILES['image_01'];
        $image_02 = $_FILES['image_02'];
        $image_03 = $_FILES['image_03'];

        // Array to store error messages
        $errorMessage = array();

        // Validate each file
        $maxFileSize = 2000000; // 2MB
        $allowedExtensions = array("jpg", "jpeg", "png", "webp");
        $errorMessage[] = validateFile($image_01, $maxFileSize, $allowedExtensions);
        $errorMessage[] = validateFile($image_02, $maxFileSize, $allowedExtensions);
        $errorMessage[] = validateFile($image_03, $maxFileSize, $allowedExtensions);

        // Remove empty error messages
        $errorMessage = array_filter($errorMessage);

        // If there are no error messages, proceed with file upload and database insertion
        if (empty($errorMessage)) {
            // Move uploaded files to a permanent location
            $image_01_folder = '../uploaded_img/' . basename($image_01['name']);
            $image_02_folder = '../uploaded_img/' . basename($image_02['name']);
            $image_03_folder = '../uploaded_img/' . basename($image_03['name']);

            if (!move_uploaded_file($image_01['tmp_name'], $image_01_folder) ||
                !move_uploaded_file($image_02['tmp_name'], $image_02_folder) ||
                !move_uploaded_file($image_03['tmp_name'], $image_03_folder)) {
                $errorMessage[] = 'Failed to move uploaded files to destination folder.';
            } else {
                // Insert data into the database
                $insert_product = $conn->prepare("INSERT INTO `products` (product_name, price, details, image_01, image_02, image_03) VALUES (:product_name, :price, :details, :image_01, :image_02, :image_03)");
                // Bind parameters
                $insert_product->bindParam(':product_name', $name);
                $insert_product->bindParam(':price', $price);
                $insert_product->bindParam(':details', $details);
                $insert_product->bindParam(':image_01', $image_01_folder);
                $insert_product->bindParam(':image_02', $image_02_folder);
                $insert_product->bindParam(':image_03', $image_03_folder);
                // Execute the statement
                if (!$insert_product->execute()) {
                    $errorMessage[] = 'Failed to insert product into the database.';
                } else {
                    $errorMessage = 'New product added!';
                }
            }
        }
    }

    if (isset($_GET['delete'])) {
        $delete_id = $_GET['delete'];
        $delete_product_image = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
        $fetch_delete_image = $delete_product_image->fetch(PDO::FETCH_ASSOC);
        unlink('../uploaded_img/' . $fetch_delete_image['image_01']);
        unlink('../uploaded_img/' . $fetch_delete_image['image_02']);
        unlink('../uploaded_img/' . $fetch_delete_image['image_03']);

        $delete_product = $conn->prepare("DELETE FROM `products` WHERE id = ?");
        $delete_product->execute([$delete_id]);

        $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
        $delete_cart->execute([$delete_id]);
        $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE id = ?");
        $delete_wishlist->execute([$delete_id]);
        header('location: products.php');
    }

    // Display message 
    if (!empty($errorMessage)) {
        // Check if $errorMessage is an array before iterating over it
        if (is_array($errorMessage)) {
            foreach ($errorMessage as $error) {
                echo '
                <div class="message">
                    <span>' . $error . '</span>
                    <i class="fas fa-times" onclick="removeErrorMessage(this);"></i>
                </div>';
            }
        } else {
            // If $errorMessage is not an array, display it directly
            echo '
            <div class="message">
                <span>' . $errorMessage . '</span>
                <i class="fas fa-times" onclick="removeErrorMessage(this);"></i>
            </div>';
        }
    }

    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Products</title>

    <!-- link to css file -->
    <link rel="stylesheet" href="../css/admin_style.css">
    <!-- link font awesom -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>

    <!-- Display error message -->
    <?php include '../component/admin_header.php';?>

    <section class="add-products">

    <h1 class="heading">add product</h1>

        <form action="" method="POST" enctype="multipart/form-data">
            <!-- CSRF token -->
            <!-- <input type="hidden" name="csrf_token" value="<?php /*echo $_SESSION['csrf_token'];*/ ?>"> -->
            <div class="flex">
                <div class="inputBox">
                    <span>product name (required)</span>
                    <input type="text" required placeholder="enter product name" class="box" name="name" maxlength="100">
                </div>
                <div class="inputBox">
                    <span>product price (required)</span>
                    <input type="number" min="0" max="9999999999" required placeholder="enter product price" class="box" name="price"
                    onkeypress="if(this.value.length == 10) return false;">
                </div>
                <div class="inputBox">
                    <span>image 01 (required)</span>
                    <input type="file" name="image_01" class="box" accept="image/jpg, image/jpeg, image/png, image/webp" required>
                </div>
                <div class="inputBox">
                    <span>image 02 (required)</span>
                    <input type="file" name="image_02" class="box" accept="image/jpg, image/jpeg, image/png, image/webp" required>
                </div>
                <div class="inputBox">
                    <span>image 03 (required)</span>
                    <input type="file" name="image_03" class="box" accept="image/jpg, image/jpeg, image/png, image/webp" required>
                </div>
                <div class="inputBox">
                    <span>product details</span>
                    <textarea name="details" class="box" placeholder="enter product details" required maxlength="500" cols="30" rows="10"></textarea>
                </div>

                <input type="submit" value="add product" name="add_product" class="btn">
            </div>
        </form>
    </section>

    <!-- add products section ends -->

    <!-- show product section starts -->

    <section class="show-products" style="padding-top : 0">

        <h1 class="heading">product added</h1>

        <div class="box-container">

            <?php
                $show_products = $conn->prepare("SELECT * FROM `products`");
                $show_products->execute();
                if($show_products->rowCount() > 0){
                    while($fetch_products=$show_products->fetch(PDO::FETCH_ASSOC)){
            ?>
            <div class="box">
                <img src="../uploaded_img/<?= $fetch_products['image_01']; ?> " alt="">
                <div class='name'><?= $fetch_products['product_name']; ?> </div>
                <div class='price'><small>xaf</small> <?= $fetch_products['price']; ?><span>/-</span> </div>
                <div class='details'><?= $fetch_products['details']; ?> </div>
                <div class="flex-btn">
                <a href="update_product.php?update=<?= $fetch_products['id']; ?>" class="option-btn">update</a> 
                <a href="products.php?delete=<?= $fetch_products['id']; ?>" class="delete-btn" onclick="return confirm('delete this product?');">delete</a>
                </div>
            </div>
            <?php

                    }
                }else{
                    echo '<p class="empty">no products added yet!</p>';
                }
            ?>
        </div>
    </section>
    <!-- show product section endss -->

    <!-- custom js file link -->
    <script src="../js/admin.js"></script>

</body>
</html>
