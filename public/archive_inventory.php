<?php

include_once '../database/db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['delete_inventory'])) {
    $inventory_id = $conn->real_escape_string($_POST['inventory_id']);

    // Delete from database
    $sql = "DELETE FROM inventory WHERE id = '$inventory_id'";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['success_message'] = "Inventory item permanently deleted.";
    } else {
        $_SESSION['error_message'] = "Error deleting item: " . $conn->error;
    }
}

// Handle bulk delete
if (isset($_POST['delete_selected']) && isset($_POST['selected_items'])) {
    $selected_items = $_POST['selected_items'];
    $deleted_count = 0;
    $errors = [];
    
    foreach ($selected_items as $item_id) {
        $inventory_id = $conn->real_escape_string($item_id);
        $sql = "DELETE FROM inventory WHERE id = '$inventory_id'";
        
        if ($conn->query($sql) === TRUE) {
            $deleted_count++;
        } else {
            $errors[] = "Error deleting item ID $inventory_id: " . $conn->error;
        }
    }
    
    if ($deleted_count > 0) {
        $_SESSION['success_message'] = "$deleted_count item(s) permanently deleted.";
    }
    
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode('<br>', $errors);
    }
}

// Prepare profile data for dropdown
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'User';
$profile_pic = isset($_SESSION['profile_pic']) ? $_SESSION['profile_pic'] : null;
if ($profile_pic && !empty($profile_pic)) {
    if (filter_var($profile_pic, FILTER_VALIDATE_URL)) {
        $profileImageSrc = $profile_pic;
    } else {
        $possiblePath = 'uploads/profile_pics/' . $profile_pic;
        $profileImageSrc = file_exists($possiblePath) ? $possiblePath : 'img/vet-logo.png';
    }
} else {
    $profileImageSrc = 'img/vet-logo.png';
}

$sql = "SELECT * FROM inventory WHERE archived = 1 ORDER BY product_name ASC";
$result = $conn->query($sql);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Archives</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../public/css/archive_inventory.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse bg-dark">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <div class="vet-logo">
                            <div>
                                <img src="./img/vet-logo.png">
                            </div>
                        </div>
                        <h5 class="text-white">Bulan Veterinary Clinic</h5>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-home me-2"></i>Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="list.php">
                                <i class="fas fa-list me-2"></i>Pet List
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="customers_profile.php">
                                <i class="fas fa-users me-2"></i>Customers Profile
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="inventory.php">
                                <i class="fas fa-box me-2"></i>Inventory
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Inventory Archives</h1>
                    <div class="dropdown" id="profileDropdown">
                        <button class="profile-btn" onclick="toggleDropdown()">
                            <img src="<?php echo htmlspecialchars($profileImageSrc); ?>" alt="Profile">
                        </button>
                        <div class="dropdown-content">
                            <div class="profile-header">
                                <img src="<?php echo htmlspecialchars($profileImageSrc); ?>" alt="Profile">
                                <h4>Hi, <?php echo htmlspecialchars($username); ?> <span class="badge">Online</span></h4>
                            </div>
                            <a href="profile.php">My Profile</a>
                            <a href="logout.php">Sign Out</a>
                        </div>
                    </div>
                </div>
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_SESSION['error_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="inventory.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Inventory
                    </a>
                    <div class="btn-group">
                        <button type="button" class="btn btn-warning" onclick="selectAll()">
                            <i class="fas fa-check-square me-2"></i>Select All
                        </button>
                        <button type="button" class="btn btn-danger" onclick="deleteSelected()" id="deleteSelectedBtn" disabled>
                            <i class="fas fa-trash me-2"></i>Delete Selected
                        </button>
                    </div>
                </div>

                <div class="table-responsive mt-4">
                    <table class="table table-bordered table-striped align-middle">
                        <thead>
                            <tr>
                                <th width="50">
                                    <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll()">
                                </th>
                                <th>Picture</th>
                                <th>Product Name</th>
                                <th>Brand</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Stock</th>
                                <th>Expiration Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                if ($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        // Checkbox column
                                        echo "<td class='text-center'>";
                                        echo "<input type='checkbox' class='item-checkbox' value='" . $row['id'] . "' onchange='updateDeleteButton()'>";
                                        echo "</td>";
                                        // Picture column
                                        echo "<td class='text-center'>";
                                        if (!empty($row['picture'])) {
                                            $picture_path = 'uploads/inventory/' . $row['picture'];
                                            if (file_exists($picture_path)) {
                                                echo '<img src="' . htmlspecialchars($picture_path) . '" class="inventory-image">';
                                            } else {
                                                echo '<i class="fas fa-box text-muted fs-4"></i>';
                                            }
                                        } else {
                                            echo '<i class="fas fa-box text-muted fs-4"></i>';
                                        }
                                        echo "</td>";
                                        echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['brand']) . "</td>";
                                        echo "<td>₱" . number_format((float)$row['price'], 2) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['stock']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['expiration_date']) . "</td>";
                                        echo "<td class='text-center'>";
                                        // Action buttons
                                        echo "<div class='btn-group' role='group'>";
                                        // Restore button
                                        echo "<form method='POST' action='restore_inventory.php' style='display:inline;'>";
                                        echo "<input type='hidden' name='inventory_id' value='" . $row['id'] . "'>";
                                        echo "<button type='submit' name='restore_inventory' class='btn btn-sm btn-success' title='Restore Item'>";
                                        echo "<i class='fas fa-undo'></i>";
                                        echo "</button>";
                                        echo "</form>";
                                        // Delete button
                                        echo "<form method='POST' action='archive_inventory.php' style='display:inline;' onsubmit=\"return confirm('Are you sure you want to permanently delete this item?');\">";
                                        echo "<input type='hidden' name='inventory_id' value='" . $row['id'] . "'>";
                                        echo "<button type='submit' name='delete_inventory' class='btn btn-sm btn-danger ms-1' title='Delete Permanently'>";
                                        echo "<i class='fas fa-trash'></i>";
                                        echo "</button>";
                                        echo "</form>";
                                        echo "</div>";
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='9' class='text-center py-4'>";
                                    echo "<i class='fas fa-archive text-muted fs-1 mb-3 d-block'></i>";
                                    echo "<h5 class='text-muted'>No archived inventory items found</h5>";
                                    echo "<p class='text-muted'>Items that are archived will appear here.</p>";
                                    echo "</td></tr>";
                                }
                                ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>
    <script src="js/dropdown.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../public/js/archive_inventory.js"></script>
</body>
</html>