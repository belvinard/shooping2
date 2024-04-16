<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../component/connection.php';
session_start();

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function generateToken() {
    return bin2hex(random_bytes(32)); // Generates a secure random token
}


$errorMessage = [];

if (isset($_POST['submit'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errorMessage[] = "CSRF token mismatch.";
    } else {
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        // $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

        $pass = $_POST['pass'];
        $cpass = $_POST['cpass'];

        // Add regex validation for password
        if (!preg_match('/^[a-zA-Z0-9!@#$%^&*()_+}{:;?]{8,12}$/', $pass)) {
            $errorMessage[] = 'Password must be between 8 and 12 characters long and can contain letters, numbers, and special characters.';
        }

        // Add regex validation for admin's name (at least two names and at most three names)
        if (!preg_match('/^([a-zA-Z]+(\s[a-zA-Z]+)*){1,3}$/', $name)) {
            $errorMessage[] = 'Admin name must have at least two names and at most three names.';
        }

        if ($pass !== $cpass) {
            $errorMessage[] = 'Password and confirm password do not match.';
        } else {
            $stmt = $conn->prepare("SELECT id FROM admins WHERE name = ?");
            $stmt->execute([$name]);

            if ($stmt->rowCount() > 0) {
                $errorMessage[] = 'Username already exists.';
            } else {
                $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
                // $stmt = $conn->prepare("INSERT INTO admins (name, password) VALUES (?, ?)");
                $token = generateToken(); 
                $stmt = $conn->prepare("INSERT INTO admins (name, password, token) VALUES (?, ?, ?)");
                $stmt->execute([$name, $hashed_password, $token]);
                // $stmt->execute([$name, $hashed_password]);

                $errorMessage[] = 'User registered successfully!';
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

    <!-- link to css file -->
    <link rel="stylesheet" href="../css/admin_style.css">
    <!-- link font awesom -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>
    <?php foreach ($errorMessage as $message): ?>
        <p><?php echo $message; ?></p>
    <?php endforeach; ?>

    <!-- Register admin section starts -->
    <section class="form_container">

        <form action="" method="post">
            <h3>register new</h3>
            <input type="text" name="name" required placeholder="enter your username" maxlength="20" class="box" autocomplete="off">
            <div style="position: relative;">
                <input type="password" name="pass" required placeholder="enter your password" maxlength="20" class="box password-field" oninput="this.value = this.value.replace(/\s/g, '')" autocomplete="off">
                <span class="eye-icon image" onclick="togglePasswordVisibility(this)"></span>
            </div>
            <input type="password" name="cpass" required placeholder="confirm your password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <input type="submit" value="register now" class="btn" name='submit'>
        </form>

    </section>
    <!-- Register admin section ends -->

    <!-- custom js file link -->
    <script src="../js/admin_script.js"></script>

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
</body>
</html>
