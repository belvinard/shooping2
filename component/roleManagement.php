<?php
// roleManagement.php

// Function to check if a user is an administrator
function isAdmin($admin_id) {
    global $conn;

    // Retrieve the list of administrators
    $select_admins = $conn->prepare("SELECT id FROM admins WHERE role = 'administrator'");
    $select_admins->execute();
    $admins = $select_admins->fetchAll(PDO::FETCH_COLUMN);

    // Return true if the user is an administrator
    return in_array($admin_id, $admins);
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
?>
