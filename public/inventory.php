<?php

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once __DIR__ . '/../database/db.php';

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// View mode (table or large card display) - default to large
$viewMode = (isset($_GET['view']) && $_GET['view'] === 'table') ? 'table' : 'large';
$viewQuery = $viewMode === 'large' ? '?view=large' : '';

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

// Archive item instead of delete
if (isset($_GET['archive_id'])) {
    $archiveId = (int) $_GET['archive_id'];
    if ($archiveId > 0) {
        try {
            $stmt = $pdo->prepare('UPDATE inventory SET archived = 1 WHERE id = :id');
            $stmt->execute([':id' => $archiveId]);
            $_SESSION['success_message'] = "Item archived successfully.";
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Error archiving item: " . $e->getMessage();
        }
        header('Location: inventory.php' . $viewQuery);
        exit;
    }
}


// Create/Update item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_inventory'])) {
    $inventoryId = isset($_POST['inventory_id']) && $_POST['inventory_id'] !== '' ? (int) $_POST['inventory_id'] : null;
    $productName = trim($_POST['product_name'] ?? '');
    $brand = trim($_POST['brand'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);
    $quantity = (int) ($_POST['quantity'] ?? 0);
    $expirationDate = $_POST['expiration_date'] ?? null;
    $stock = (int) ($_POST['stock'] ?? 0);

    // Handle picture upload
    $existingPicture = trim($_POST['current_picture'] ?? '');
    $picture = $existingPicture;
    if (isset($_FILES['picture']) && is_array($_FILES['picture']) && ($_FILES['picture']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $tmpPath = $_FILES['picture']['tmp_name'];
        $originalName = $_FILES['picture']['name'] ?? '';
        $uploadDir = __DIR__ . '/uploads/inventory';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmpPath);
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];
        if (isset($allowed[$mime])) {
            $ext = $allowed[$mime];
            try {
                $basename = bin2hex(random_bytes(6));
            } catch (Throwable $e) {
                $basename = uniqid('', true);
            }
            $filename = $basename . '.' . $ext;
            $destPath = $uploadDir . '/' . $filename;
            if (@move_uploaded_file($tmpPath, $destPath)) {
                $picture = $filename;
            }
        }
    }

    if ($inventoryId) {
        $stmt = $pdo->prepare('UPDATE inventory SET picture = :picture, product_name = :product_name, brand = :brand, price = :price, quantity = :quantity, expiration_date = :expiration_date, stock = :stock WHERE id = :id');
        $stmt->execute([
            ':picture' => $picture,
            ':product_name' => $productName,
            ':brand' => $brand,
            ':price' => $price,
            ':quantity' => $quantity,
            ':expiration_date' => $expirationDate,
            ':stock' => $stock,
            ':id' => $inventoryId,
        ]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO inventory (picture, product_name, brand, price, quantity, expiration_date, stock) VALUES (:picture, :product_name, :brand, :price, :quantity, :expiration_date, :stock)');
        $stmt->execute([
            ':picture' => $picture,
            ':product_name' => $productName,
            ':brand' => $brand,
            ':price' => $price,
            ':quantity' => $quantity,
            ':expiration_date' => $expirationDate,
            ':stock' => $stock,
        ]);
    }

    header('Location: inventory.php' . $viewQuery);
    exit;
}

// Fetch item for edit (only non-archived items)
$edit_inventory = null;
if (isset($_GET['edit_id'])) {
    $editId = (int) $_GET['edit_id'];
    if ($editId > 0) {
        $stmt = $pdo->prepare('SELECT * FROM inventory WHERE id = :id AND archived = 0');
        $stmt->execute([':id' => $editId]);
        $edit_inventory = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}

// Fetch all non-archived items
$items = [];
try {
    $stmt = $pdo->query('SELECT * FROM inventory WHERE archived = 0 ORDER BY product_name ASC');
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $items = [];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../public/css/inventory.css">
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
                    <h1 class="h2">Inventory</h1>
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
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#inventoryModal">
                        <i class="fas fa-plus me-2"></i>Add New Item
                    </button>
                    <div class="btn-archive">
                      <a href="archive_inventory.php" class="btn btn-warning">
                        <i class="fas fa-archive me-2"></i> Archives
                      </a>
                    </div>
                    <div class="ms-auto">
                        <a href="inventory.php?view=table" class="btn btn-outline-secondary btn-sm <?php echo $viewMode==='table' ? 'active' : ''; ?>" title="Table view">
                            <i class="fas fa-table"></i>
                        </a>
                        <a href="inventory.php?view=large" class="btn btn-outline-secondary btn-sm <?php echo $viewMode==='large' ? 'active' : ''; ?>" title="Large card view">
                            <i class="fas fa-th-large"></i>
                        </a>
                    </div>
                </div>
                <div class="mt-4">
                    <?php if ($viewMode === 'table'): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-responsive table-bordered table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>Picture</th>
                                        <th>Product Name</th>
                                        <th>Brand</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Expiration Date</th>
                                        <th>Stock</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($items)): ?>
                                        <?php $today = new DateTime('today'); ?>
                                        <?php foreach ($items as $item): ?>
                                            <?php
                                                $exp = !empty($item['expiration_date']) ? new DateTime($item['expiration_date']) : null;
                                                $isExpired = $exp ? ($exp < $today) : false;
                                                $isOutOfStock = ((int)($item['stock'] ?? 0)) <= 0;
                                                $statusLabel = $isExpired ? 'Expired' : ($isOutOfStock ? 'Out of Stock' : 'In Stock');
                                                $statusClass = $isExpired ? 'badge bg-danger' : ($isOutOfStock ? 'badge bg-warning text-dark' : 'badge bg-success');
                                            ?>
                                            <tr>
                                                <td>
                                                    <?php if (!empty($item['picture']) && file_exists(__DIR__ . '/uploads/inventory/' . $item['picture'])): ?>
                                                        <img src="<?php echo 'uploads/inventory/' . h($item['picture']); ?>" alt="Item" class="img-thumbnail" style="max-width: 60px; max-height: 60px;">
                                                    <?php else: ?>
                                                        <span class="text-muted">No image</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo h($item['product_name']); ?></td>
                                                <td><?php echo h($item['brand']); ?></td>
                                                <td>₱<?php echo number_format((float)($item['price'] ?? 0), 2); ?></td>
                                                <td><?php echo (int)($item['quantity'] ?? 0); ?></td>
                                                <td><?php echo !empty($item['expiration_date']) ? h($item['expiration_date']) : '-'; ?></td>
                                                <td><?php echo (int)($item['stock'] ?? 0); ?></td>
                                                <td><span class="<?php echo $statusClass; ?>"><?php echo $statusLabel; ?></span></td>
                                                <td class="text-nowrap">
                                                    <a class="btn btn-sm btn-outline-primary" href="inventory.php?edit_id=<?php echo (int)$item['id']; ?>&view=table" title="Update">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a class="btn btn-sm btn-outline-warning ms-1" 
                                                        href="inventory.php?archive_id=<?php echo (int)$item['id']; ?>&view=table" 
                                                        onclick="return confirm('Archive this item?');" title="Archive">
                                                        <i class="fas fa-archive"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">No inventory items found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php if (!empty($items)): ?>
                                <?php $today = new DateTime('today'); ?>
                                <?php foreach ($items as $item): ?>
                                    <?php
                                        $exp = !empty($item['expiration_date']) ? new DateTime($item['expiration_date']) : null;
                                        $isExpired = $exp ? ($exp < $today) : false;
                                        $isOutOfStock = ((int)($item['stock'] ?? 0)) <= 0;
                                        $statusLabel = $isExpired ? 'Expired' : ($isOutOfStock ? 'Out of Stock' : 'In Stock');
                                        $statusClass = $isExpired ? 'badge bg-danger' : ($isOutOfStock ? 'badge bg-warning text-dark' : 'badge bg-success');
                                        $imgPath = (!empty($item['picture']) && file_exists(__DIR__ . '/uploads/inventory/' . $item['picture'])) ? ('uploads/inventory/' . h($item['picture'])) : null;
                                    ?>
                                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                        <div class="card h-100">
                                            <?php if ($imgPath): ?>
                                                <img src="<?php echo $imgPath; ?>" class="card-img-top" alt="Item image" style="object-fit: cover; height: 180px;">
                                            <?php else: ?>
                                                <div class="d-flex align-items-center justify-content-center bg-light" style="height: 180px;">
                                                    <span class="text-muted">No image</span>
                                                </div>
                                            <?php endif; ?>
                                            <div class="card-body d-flex flex-column">
                                                <h5 class="card-title mb-1"><?php echo h($item['product_name']); ?></h5>
                                                <p class="card-subtitle text-muted mb-2"><?php echo h($item['brand']); ?></p>
                                                <div class="mb-2"><strong>₱<?php echo number_format((float)($item['price'] ?? 0), 2); ?></strong></div>
                                                <div class="small mb-2">Qty: <?php echo (int)($item['quantity'] ?? 0); ?> • Stock: <?php echo (int)($item['stock'] ?? 0); ?></div>
                                                <div class="small mb-2">Exp: <?php echo !empty($item['expiration_date']) ? h($item['expiration_date']) : '-'; ?></div>
                                                <div class="mb-3"><span class="<?php echo $statusClass; ?>"><?php echo $statusLabel; ?></span></div>
                                                <div class="mt-auto">
                                                    <a class="btn btn-sm btn-outline-primary" href="inventory.php?edit_id=<?php echo (int)$item['id']; ?>&view=large" title="Update">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <a class="btn btn-sm btn-outline-warning ms-1" href="inventory.php?archive_id=<?php echo (int)$item['id']; ?>&view=large" onclick="return confirm('Archive this item?');" title="Archive">
                                                        <i class="fas fa-archive"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12 text-center text-muted">No inventory items found.</div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Add/Update Inventory Modal -->
    <div class="modal fade" id="inventoryModal" tabindex="-1" aria-labelledby="inventoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <i class="fas fa-box me-2"></i>
                    <h5 class="modal-title" id="inventoryModalLabel"><?php echo isset($edit_inventory) ? 'Update Inventory' : 'Add Inventory'; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data">
                    <div class="modal-body">
                        <?php if (isset($edit_inventory)): ?>
                            <input type="hidden" name="inventory_id" value="<?php echo htmlspecialchars($edit_inventory['id']); ?>">
                        <?php endif; ?>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="product_name" class="form-label">Product Name</label>
                                <input type="text" class="form-control" id="product_name" name="product_name" value="<?php echo isset($edit_inventory) ? htmlspecialchars($edit_inventory['product_name']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="brand" class="form-label">Brand</label>
                                <input type="text" class="form-control" id="brand" name="brand" value="<?php echo isset($edit_inventory) ? htmlspecialchars($edit_inventory['brand']) : ''; ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col mb-6">
                                <label for="price" class="form-label">Price</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="price" name="price" value="<?php echo isset($edit_inventory) ? htmlspecialchars($edit_inventory['price']) : ''; ?>" required>
                            </div>
                            <div class="col mb-6">
                                <label for="quantity" class="form-label">Quantity</label>
                                <input type="number" min="0" class="form-control" id="quantity" name="quantity" value="<?php echo isset($edit_inventory) ? htmlspecialchars($edit_inventory['quantity']) : ''; ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col mb-6">
                                <label for="expiration_date" class="form-label">Expiration Date</label>
                                <input type="date" class="form-control" id="expiration_date" name="expiration_date" value="<?php echo isset($edit_inventory) ? htmlspecialchars($edit_inventory['expiration_date']) : ''; ?>" required>
                            </div>
                            <div class="col mb-6">
                                <label for="stock" class="form-label">Stock</label>
                                <input type="number" min="0" class="form-control" id="stock" name="stock" value="<?php echo isset($edit_inventory) ? htmlspecialchars($edit_inventory['stock']) : ''; ?>" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="picture" class="form-label">Upload item</label>
                            <input type="file" class="form-control" id="picture" name="picture" accept="image/*" style="width: fit-content; margin-left: 1.5%;">
                            <input type="hidden" name="current_picture" value="<?php echo isset($edit_inventory) ? h($edit_inventory['picture']) : ''; ?>">
                            <?php if (isset($edit_inventory) && !empty($edit_inventory['picture'])): ?>
                                <div class="mt-2">
                                    <p class="text-muted">Current Picture:</p>
                                    <?php if (file_exists(__DIR__ . '/uploads/inventory/' . $edit_inventory['picture'])): ?>
                                        <img src="<?php echo 'uploads/inventory/' . h($edit_inventory['picture']); ?>" alt="current picture" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                                    <?php else: ?>
                                        <span class="text-muted">Image not found</span>
                                    <?php endif; ?>
                                    <picture></picture>
                                </div>
                            <?php endif; ?>
                            <small class="form-text text-muted"></small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="save_inventory" class="btn btn-primary"><?php echo isset($edit_inventory) ? 'Update Inventory' : 'Add Inventory'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="js/dropdown.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/inventory.js"></script>
    <?php if (!empty($edit_inventory)): ?>
    <script>
        const editModal = new bootstrap.Modal(document.getElementById('inventoryModal'));
            editModal.show();
    </script>
    <?php endif; ?>
</body>
</html>