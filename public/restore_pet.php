<?php
include_once '../database/db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['restore_pet'])) {
    $pet_id = $conn->real_escape_string($_POST['pet_id']);
    $sql = "UPDATE pets SET archived = 0 WHERE id = '$pet_id'";
    
    if ($conn->query($sql) === TRUE) {
        $_SESSION['success_message'] = "Pet restored successfully.";
    } else {
        $_SESSION['error_message'] = "Error restoring pet: " . $conn->error;
    }
}

header("Location: archive_pets.php");
exit();

?>