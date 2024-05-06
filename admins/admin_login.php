<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../component/connection.php';
session_start();

$errorMessage = []; // Initialize an empty array for error messages

function generateToken() {
    return bin2hex(random_bytes(32));  // Generates a secure random token
}

// Function to set a persistent login cookie
function setPersistentLoginCookie($token) {
    // Set cookie domain to empty string for local testing
    setcookie('remember_me', $token, $expiryTime, '/', '', false, true);
}

// Function to clear the persistent login cookie
function clearPersistentLoginCookie() {
    // Set cookie domain to empty string for local testing
    setcookie('remember_me', '', time() - 3600, '/', '', false, true);
}

// Check if the "Remember Me" cookie is set
if (isset($_COOKIE['remember_me'])) {
    $tokenFromCookie = $_COOKIE['remember_me'];

    // Check if the token exists in the database
    $stmt = $conn->prepare("SELECT id FROM admins WHERE token = ?");
    $stmt->execute([$tokenFromCookie]);
    $admin = $stmt->fetch();

    if ($admin) {
        $_SESSION['admin_id'] = $admin['id'];
        // Redirect to dashboard or authenticated page
        header("Location: dashboard.php");
        exit();
    } else {
        // Clear the invalid cookie
        clearPersistentLoginCookie();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $pass = trim(filter_input(INPUT_POST, 'pass', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    
    $rememberMe = isset($_POST['remember_me']); // Check if "Remember Me" is checked

    // Ensure "Remember Me" is checked before processing the login
    if (!$rememberMe) {
        $errorMessage[] = 'You must check "Remember Me" to log in.';
    } else {
        // Proceed with the login process
        $stmt = $conn->prepare("SELECT id, password FROM admins WHERE name = ?");
        $stmt->execute([$name]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($pass, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];

            // Generate a new token and set persistent login cookie
            $token = generateToken();
            $stmt = $conn->prepare("UPDATE admins SET token = ? WHERE id = ?");
            $stmt->execute([$token, $admin['id']]);
            setPersistentLoginCookie($token);

            // Redirect to a secure page (dashboard, etc.)
            header("Location: dashboard.php");
            exit();
        } else {
            $errorMessage[] = 'Invalid username or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login admin</title>

    <!-- link to css file -->
    <link rel="stylesheet" href="../css/admin_style.css">
    <!-- link font awesom -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>
    
    <!-- Display error message -->
    <?php

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

    <section class="form_container">

        <form action="" method="post">
            <h3>login now</h3>
            
            <input type="text" name="name" required placeholder="enter your username" maxlength="20" class="box" autocomplete="off">
            <!-- <input type="password" name="pass" required placeholder="enter your password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')" autocomplete="off"> -->
            <div style="position: relative;">
                <input type="password" name="pass" required placeholder="enter your password" maxlength="20" class="box password-field" oninput="this.value = this.value.replace(/\s/g, '')" autocomplete="off">
                <span class="eye-icon image" onclick="togglePasswordVisibility(this)"></span>
            </div>

            <div class="remember-container">
                
                <input type="checkbox" name="remember_me" id="remember_me" required>
                <label for="remember_me" class="js-remeber-me">Remember Me </label>
            </div>
            <input type="submit" value="login now" class="btn" name="submit">

        </form>

    </section>

    <script>
    function togglePasswordVisibility(icon) {
        let passwordField = icon.previousElementSibling;
        if (passwordField.type === "password") {
            passwordField.type = "text";
        } else {
            passwordField.type = "password";
        }
    }
</script>
    <!-- custom js file link -->
    <script src="../js/admin_js.js"></script>
</body>
</html>
