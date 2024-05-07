<?php
// Include database connection
include '../component/connection.php';
// Start session
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('location:admin_login.php');
    exit;
}

// Get the admin ID from the session
$admin_id = $_SESSION['admin_id'];

// Fetch the admin's current details from the database
$select_profile = $conn->prepare("SELECT * FROM `admins` WHERE id = ?");
$select_profile->execute([$admin_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

// Initialize an array to store error messages
$errorMessage = [];

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate form inputs
    $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $old_email = trim(filter_input(INPUT_POST, 'old_email', FILTER_SANITIZE_EMAIL));
    $new_email = trim(filter_input(INPUT_POST, 'new_email', FILTER_SANITIZE_EMAIL));
    $old_pass = trim(filter_input(INPUT_POST, 'old_pass', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $new_pass = trim(filter_input(INPUT_POST, 'new_pass', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $confirm_pass = trim(filter_input(INPUT_POST, 'confirm_pass', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

    // Check for changes
    if (!empty($name) || !empty($old_email) || !empty($new_email) || !empty($old_pass) || !empty($new_pass) || !empty($confirm_pass)) {
        
        // Check if provided old email matches the current email in the database
        if ($old_email !== $fetch_profile['email']) {
            $errorMessage[] = 'Old email does not match current email';
        }

        // Check if old password is provided and matches the stored password
        if (!empty($old_pass) && !password_verify($old_pass, $fetch_profile['password'])) {
            $errorMessage[] = 'Invalid old password';
        }

        // Check if new password and confirm password match
        if (!empty($new_pass) && $new_pass != $confirm_pass) {
            $errorMessage[] = 'New password and confirm password do not match';
        }

        // If there are no errors, update the admin's profile
        if (empty($errorMessage)) {
            // Update name if provided
            if (!empty($name)) {
                $update_name = $conn->prepare("UPDATE `admins` SET name = ? WHERE id = ?");
                $update_name->execute([$name, $admin_id]);
            }

            // Update email if provided and old email matched
            if (!empty($new_email) && empty($errorMessage)) {
                $update_email = $conn->prepare("UPDATE `admins` SET email = ? WHERE id = ?");
                $update_email->execute([$new_email, $admin_id]);
            }

            // Update password if provided
            if (!empty($new_pass)) {
                // Hash the new password
                $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
                $update_password = $conn->prepare("UPDATE `admins` SET password = ? WHERE id = ?");
                $update_password->execute([$hashed_password, $admin_id]);
            }

            // Redirect to a success page or display a success message
            header('Location: update_profile.php');
            exit;
        }
    } else {
        $errorMessage[] = 'No changes were made.';
    }
}

// Display error messages
if (!empty($errorMessage)) {
    foreach ($errorMessage as $error) {
        echo "<div class='message'><span>{$error}</span><i class='fas fa-times' onclick='removeErrorMessage(this);'></i></div>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Update admin</title>

    <!-- link to css file -->
    <link rel="stylesheet" href="../css/admin_style.css">
    <!-- link font awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>
    <?php
    // Include the header
    include '../component/admin_header.php';
    ?>

    <!-- Admin profile section starts -->
    <section class="form_container">
        <form action="" method="post">
            <h3>Update Profile</h3>
            <input type="text" name="name" value="<?= isset($fetch_profile['name']) ? trim($fetch_profile['name']) : '' ?>" required placeholder="Enter your username" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')" autocomplete="off">
            <!-- <input type="text" name="name" value="<?= $fetch_profile['name'] ?>" required placeholder="Enter your username" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')" autocomplete="off"> -->
            <input type="email" name="old_email" required placeholder="enter your old email" maxlength="50" class="box" oninput="this.value = this.value.replace(/\s/g, '')" autocomplete="off">
            <input type="email" name="new_email" required placeholder="enter your new_email" maxlength="50" class="box" oninput="this.value = this.value.replace(/\s/g, '')" autocomplete="off">
            <input type="password" name="old_pass" placeholder="Enter your old password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')" autocomplete="off">

            <div style="position: relative;">
                <input type="password" id="password" name="new_pass" required placeholder="enter your new password" maxlength="20" class="box password-field" oninput="checkPasswordStrength(this.value)" autocomplete="off" onfocus="showProgressBar()">
                <span class="eye-icon image" onclick="togglePasswordVisibility(this)"></span>
            </div>
            <div class="progress hidden"> <!-- Initially hidden -->
                <div id="password-strength-bar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>


            <!-- <input type="password" name="new_pass" placeholder="Enter your new password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')" autocomplete="off"> -->
            <input type="password" name="confirm_pass" placeholder="Confirm your new password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')" autocomplete="off">

            <input type="submit" value="Update Now" class="btn" name="submit">
        </form>
    </section>
    <!-- Admin profile section ends -->

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

    <!-- custom js file link -->
    <script src="../js/admin.js"></script>


</body>
</html>