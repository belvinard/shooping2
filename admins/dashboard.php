<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>dashboard</title>
</head>
<body>
    <h1>Welcome to the dasboard!</h1>
    <?php
/*error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Include database connection file
require_once "db_connection.php";

$errorMessage = []; // Initialize an empty array for error messages

// Function to generate CSRF token
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validate CSRF token
if ($_SERVER["REQUEST_METHOD"] == "POST" && (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token'])) {
    $errorMessage[] = "CSRF token validation failed.";
}

if(isset($_POST['submit'])){
    $name = $_POST['name'];
    $name = filter_var($name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $pass = $_POST['pass'];
    $pass = filter_var($pass, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Check if the user exists
    $query = "SELECT * FROM `admins` WHERE name = :name";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':name', $name);
    $stmt->execute();

    if($stmt->rowCount() > 0){
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify password
        if(password_verify($pass, $row['password'])){
            $_SESSION['admin_id'] = $row['id']; // Store the id in the session
            $_SESSION['name'] = $name;
            header("Location: dashboard.php"); // Redirect to dashboard
            exit();
        } else {
            $errorMessage[] = "Invalid password";
        }
    } else {
        $errorMessage[] = "User does not exist";
    }
}*/

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>admin login</title>

    <!-- link to css file -->
    <link rel="stylesheet" href="../css/admin_style.css">
    <!-- link font awesom -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6

</body>
</html>