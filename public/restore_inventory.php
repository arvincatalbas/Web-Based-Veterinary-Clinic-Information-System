<?php

include_once '../database/db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['restore_inventory'])) {
    $inventory_id = $conn->real_escape_string($_POST['inventory_id']);
    $sql = "UPDATE inventory SET archived = 0 WHERE id = '$inventory_id'";
    
    if ($conn->query($sql) === TRUE) {
        $_SESSION['success_message'] = "Inventory item restored successfully.";
    } else {
        $_SESSION['error_message'] = "Error restoring inventory item: " . $conn->error;
    }
}

header("Location: archive_inventory.php");
exit();

?>