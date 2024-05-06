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

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errorMessage[] = "CSRF token mismatch.";
        } else {

            // Validate form inputs
            $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
            $pass = trim(filter_input(INPUT_POST, 'pass', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            $cpass = trim(filter_input(INPUT_POST, 'cpass', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

            // Add regex validation for password
            if (!preg_match('/^[a-zA-Z0-9!@#$%^&*()_+}{:;?]{8}$/', $pass)) {
                $errorMessage[] = 'Password is 8 long and can contain letters, numbers, or special characters.';
            }

            if (!preg_match('/^[A-Za-z]+$/', $name)) {
                $errorMessage[] = 'Name must contain only letters.';
            }

            if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
                $errorMessage[] = 'Invalid email format.';
            }

            if ($pass !== $cpass) {
                $errorMessage[] = 'Password and confirm password do not match.';
            } else {
                $stmt = $conn->prepare("SELECT id FROM admins WHERE name = ?");
                $stmt->execute([$name]);

                if ($stmt->rowCount() > 0) {
                    $errorMessage[] = 'Admin already exists.';
                } else {
                    $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
                    // $stmt = $conn->prepare("INSERT INTO admins (name, password) VALUES (?, ?)");
                    $token = generateToken(); 
                    // $stmt = $conn->prepare("INSERT INTO admins (name, password, token) VALUES (?, ?, ?)");
                    $stmt = $conn->prepare("INSERT INTO admins (name, email, password, token) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$name, $email, $hashed_password, $token]);

                    // $stmt->execute([$name, $hashed_password, $token]);
                    // $stmt->execute([$name, $hashed_password]);

                    $errorMessage[] = 'Admin registered successfully!';
                }
            }
        }
    }
    // Display message
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
    <title>Register</title>

    <!-- link to css file -->
    <link rel="stylesheet" href="../css/admin_style.css">
    <!-- link font awesom -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>

    <!-- Display error message -->
    <?php include '../component/admin_header.php';?>

    <!-- Register admin section starts -->
<section class="form_container">
    <form action="" method="post" autocomplete="off">
        <h3>register new</h3>
        <input type="text" name="name" required placeholder="enter your username" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')" autocomplete="off">
        <input type="email" name="email" required placeholder="enter your email" maxlength="50" class="box" oninput="this.value = this.value.replace(/\s/g, '')" autocomplete="off">

        <div style="position: relative;">
            <input type="password" id="password" name="pass" required placeholder="enter your password" maxlength="20" class="box password-field" oninput="checkPasswordStrength(this.value)" autocomplete="off" onfocus="showProgressBar()">
            <span class="eye-icon image" onclick="togglePasswordVisibility(this)"></span>
        </div>
        <div class="progress hidden"> <!-- Initially hidden -->
            <div id="password-strength-bar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
        </div>

        <input type="password" name="cpass" required placeholder="confirm your password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        <input type="submit" value="register now" class="btn" name='submit'>
    </form>
</section>
<!-- Register admin section ends -->

    <script>
        function togglePasswordVisibility(icon) {
            let passwordField = icon.previousElementSibling;
            if (passwordField.type === "password") {
                passwordField.type = "text";
            } else {
                passwordField.type = "password";
            }
        }

        function showProgressBar() {
            var progress = document.querySelector('.progress');
            progress.classList.remove('hidden');
        }

    function checkPasswordStrength(password) {
        var strength = 0;
        var progress = document.getElementById('password-strength-bar');

        // Add points for length
        strength += password.length * 2;

        // Check for special characters
        var specialChars = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/;
        if (specialChars.test(password)) {
            strength += 5;
        }

        // Check for uppercase and lowercase letters
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) {
            strength += 20;
        }

        // Check for numbers
        if (/\d/.test(password)) {
            strength += 20;
        }

        // Check if password matches the regex pattern
        if (/^[a-zA-Z0-9!@#$%^&*()_+}{:;?]{8}$/.test(password)) {
            // Password meets the requirements, set progress to 100%
            progress.style.width = '100%';
            progress.classList.remove('bg-danger', 'bg-warning');
            progress.classList.add('bg-success');
        } else {
            // Password does not meet the requirements, set progress based on strength
            progress.style.width = strength + '%';
            // Change color based on strength
            if (strength < 30) {
                progress.classList.remove('bg-success', 'bg-warning');
                progress.classList.add('bg-danger');
            } else if (strength < 60) {
                progress.classList.remove('bg-danger', 'bg-success');
                progress.classList.add('bg-warning');
            } else { // Adjusted threshold for bg-success
                progress.classList.remove('bg-danger', 'bg-warning');
                progress.classList.add('bg-success');
            }
        }
    }
    </script>

    <!-- custom jQuery link -->
    <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->

    <!-- custom js file link -->
    <!-- <script src="js/admin_js.js"></script> -->
    <!-- <script src="../js/profile_js.js"></script> -->

</body>
</html>
