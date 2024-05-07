<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// session_start();

$errorMessage = []; // Initialize an empty array for error messages
$fetch_profile = []; // Initialize $fetch_profile to an empty array

// Check if the "Remember Me" cookie is set
if (isset($_COOKIE['remember_me'])) {
    $tokenFromCookie = $_COOKIE['remember_me'];

    // Check if the token exists in the database
    $stmt = $conn->prepare("SELECT id FROM admins WHERE token = ?");
    $stmt->execute([$tokenFromCookie]);
    $admin = $stmt->fetch();

    if (!$admin) {
        $_SESSION['admin_id'] = $admin['id'];
        // Redirect to dashboard or authenticated page
        header("Location: admin_login.php");
        exit();
    } 

    $_SESSION['admin_id'] = $admin['id'];
    $admin_id = $admin['id']; // Set admin_id here
    // Retrieve admin's profile
    $select_profile = $conn->prepare("SELECT * FROM `admins` WHERE id = ?");
    $select_profile->execute([$admin_id]);
    $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

}

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

<header class="header">
    <section class="flex">
        <a href="../admin/dashboard.php" class="logo">Admin<span>Panel</span></a>
        <nav class="navbar">
            <a href="../admins/dashboard.php">home</a>
            <a href="../admins/products.php">products</a>
            <a href="../admins/placed_orders.php">orders</a>
            <a href="../admins/admin_accounts.php">admin</a>
            <a href="../admins/users_accounts.php">users</a>
            <a href="../admins/messages.php">messages</a>
        </nav>
        <div class="icons">
            <div id="menu-btn" class="fas fa-bars"></div>
            <div id="user-btn" class="fas fa-user"></div>
        </div>
        <div class="profile">
            <?php
            // Check if $fetch_profile is an array before accessing its elements
            if (is_array($fetch_profile) && isset($fetch_profile['name'])) {
            echo "<p>{$fetch_profile['name']}</p>";
            } else {
                echo "<p>No profile found</p>";
            }
            ?>
            <a href="../admins/update_profile.php" class="btn1">update profile</a>
            <div class="flex-btn">
                <a href="../admins/admin_login.php" class="option-btn">login</a>
                <a href="../admins/register_admin.php" class="option-btn">register</a>
            </div>
            <a href="../component/admin_logout.php" class="delete-btn" onclick="return confirm('log out from this website?');">
                logout
            </a>
        </div>
    </section>
</header>
    