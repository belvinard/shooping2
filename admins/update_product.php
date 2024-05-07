<?php
    // Reporting error
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    include '../component/connection.php'; // Include database connection
    session_start(); // Start session

    $errorMessage = []; // Initialize array to store error messages

    // Check if form is submitted
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        
        $pid = $_POST['pid'] ?? ''; // Get product ID from form data
        $product_name = $_POST['product_name'] ?? ''; // Get product name from form data
        $product_name = filter_var($product_name, FILTER_SANITIZE_FULL_SPECIAL_CHARS); // Sanitize product name
        $price = $_POST['price'] ?? ''; // Get product price from form data
        $price = filter_var($price, FILTER_SANITIZE_FULL_SPECIAL_CHARS); // Sanitize product price
        $details = $_POST['details'] ?? ''; // Get product details from form data
        $details = filter_var($details, FILTER_SANITIZE_FULL_SPECIAL_CHARS); // Sanitize product details

        // Fetch existing product data
        $stmt = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
        $stmt->execute([$pid]);
        $existingProduct = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if each field is empty and retain the existing value if not updated
        $name = !empty($product_name) ? $product_name : $existingProduct['product_name'];
        $price = !empty($price) ? $price : $existingProduct['price'];
        $details = !empty($details) ? $details : $existingProduct['details'];
        $image_01 = !empty($_FILES['image_01']['name']) ? $_FILES['image_01']['name'] : $existingProduct['image_01'];
        $image_02 = !empty($_FILES['image_02']['name']) ? $_FILES['image_02']['name'] : $existingProduct['image_02'];
        $image_03 = !empty($_FILES['image_03']['name']) ? $_FILES['image_03']['name'] : $existingProduct['image_03'];


        // Update the product with the new values
        $update_product = $conn->prepare("UPDATE `products` SET product_name = ?, price = ?, details = ? WHERE id = ?");
        $update_product->execute([$product_name, $price, $details, $pid]);

        $errorMessage[] = 'Product updated successfully!'; // Add success message

        // Function to handle image update
        function updateImage($imageKey, $oldImage, $pid) {

            global $errorMessage; // Access global error message array

            $image = $_FILES[$imageKey]['name']; // Get image file name from form data
            $image = filter_var($image, FILTER_SANITIZE_FULL_SPECIAL_CHARS); // Sanitize image file name
            $imageSize = $_FILES[$imageKey]['size']; // Get image file size from form data
            $imageTmpName = $_FILES[$imageKey]['tmp_name']; // Get temporary file name of uploaded file
            $imageFolder = '../uploaded_img/' . $image; // Define destination folder for image

            // Check if image is uploaded
            if(!empty($image)){
                // Check image size
                if($imageSize > 2000000){
                    $errorMessage[] = 'Image size is too large!'; // Add error message
                }else{
                    // Prepare SQL statement to update image in database
                    $updateImage = $conn->prepare("UPDATE `products` SET $imageKey = ? WHERE id = ?");
                    // Execute SQL statement
                    $updateImage->execute([$image, $pid]);
                    // Move uploaded file to destination folder
                    move_uploaded_file($imageTmpName, $imageFolder);
                    // Remove old image file
                    unlink('../uploaded_img/'.$oldImage);
                    $errorMessage[] = ucfirst($imageKey) . ' updated successfully!'; // Add success message
                }
            }
        }

        // Call updateImage function for each image field
        updateImage('image_01', $_POST['old_image_01'], $pid);
        updateImage('image_02', $_POST['old_image_02'], $pid);
        updateImage('image_03', $_POST['old_image_03'], $pid);
    }

    // Display error message 
    if (!empty($errorMessage)) {
        foreach ($errorMessage as $error) {
            echo '
            <div class="message">
                <span>' . $error . '</span>
                <i class="fas fa-times" onclick="removeErrorMessage(this);"></i>
            </div>';
        }
    }
    // JavaScript function to remove error message
    echo "<script>
    function removeErrorMessage(element){
        element.parentElement.remove();
    }
    </script>";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Update</title>
    <link rel="stylesheet" href="../css/admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>
    <?php include '../component/admin_header.php';?>

    <section class="update-product">

        <h1 class="heading">Update Product</h1>

        <?php
        
            $update_id = $_GET['update'];
            $show_products = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
            $show_products->execute([$update_id]);
            if($show_products->rowCount() > 0){
                while($fetch_products=$show_products->fetch(PDO::FETCH_ASSOC)){
        ?>

        <form action="" method="POST" enctype="multipart/form-data">

            <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
            <input type="hidden" name="old_image_01" value="<?= $fetch_products['image_01']; ?>">
            <input type="hidden" name="old_image_02" value="<?= $fetch_products['image_02']; ?>">
            <input type="hidden" name="old_image_03" value="<?= $fetch_products['image_03']; ?>">
            <input type="hidden" name="old_name" value="<?= $fetch_products['product_name']; ?>">
            <input type="hidden" name="old_price" value="<?= $fetch_products['price']; ?>">
            <input type="hidden" name="old_details" value="<?= $fetch_products['details']; ?>">

            <div class="image-container">
                <div class="main-image">
                    <img src="../uploaded_img/<?= $fetch_products['image_01']; ?>" alt="">;
                </div>
                <div class="sub-image">
                    <img src="../uploaded_img/<?= $fetch_products['image_01']; ?>" alt="">;
                    <img src="../uploaded_img/<?= $fetch_products['image_02']; ?>" alt="">;
                    <img src="../uploaded_img/<?= $fetch_products['image_03']; ?>" alt="">;
                </div>
            </div>

            <span>Update name</span>
            <input type="text" required placeholder="Enter product name" class="box" name="product_name" maxlength="100" value="<?= $fetch_products['product_name']; ?>">

            <span>Update price</span>
            <input type="number" min="0" max="9999999999" placeholder="Enter product price" class="box" name="price" onkeypress="if(this.value.length == 10) return false;">

            <span>Update details</span>
            <textarea name="details" class="box" placeholder="Enter product details" maxlength="500" cols="30" rows="10"><?= $fetch_products['details']; ?></textarea>

            <span>Update image_01</span>
            <input type="file" name="image_01" class="box" accept="image/jpg, image/jpeg, image/png, image/webp">

            <span>Update image_02</span>
            <input type="file" name="image_02" class="box" accept="image/jpg, image/jpeg, image/png, image/webp">

            <span>Update image_03</span>
            <input type="file" name="image_03" class="box" accept="image/jpg, image/jpeg, image/png, image/webp">

            <div class="flex-btn">
                <input type="submit" name="update" class="btn" value="Update">
                <a href="products.php" class="option-btn">Go Back</a>
            </div>

        </form>

        <?php
                }
            } else {
                echo '<p class="empty">No products added yet!</p>';
            }
        ?>
    </section>
    <script src="../js/admin.js"></script>
</body>
</html>
