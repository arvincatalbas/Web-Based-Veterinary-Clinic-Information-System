<?php

include_once '../database/db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
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

$edit_pet = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $pet_id = $conn->real_escape_string($_GET['edit']);
    $sql = "SELECT * FROM pets WHERE id = '$pet_id'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $edit_pet = $result->fetch_assoc();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pet_name = $conn->real_escape_string($_POST['pet_name']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $species = $conn->real_escape_string($_POST['species']);
    $breed = $conn->real_escape_string($_POST['breed']);
    $owner_name = $conn->real_escape_string($_POST['owner_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $owner_contact = $conn->real_escape_string($_POST['owner_contact']);
    $vaccination_status = $conn->real_escape_string($_POST['vaccination_status']);
    $medical_history = $conn->real_escape_string($_POST['medical_history']);

    // Handle pet profile picture upload
    $profile_picture = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/pet_pics/';
        $file_tmp = $_FILES['profile_picture']['tmp_name'];
        $file_name = $_FILES['profile_picture']['name'];
        $file_size = $_FILES['profile_picture']['size'];
        $file_type = $_FILES['profile_picture']['type'];
        
        // Check if file is an image
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($file_type, $allowed_types)) {
            // Generate unique filename
            $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
            $unique_filename = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $unique_filename;
            
            // Check file size (limit to 5MB)
            if ($file_size <= 5000000) {
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    $profile_picture = $unique_filename;
                } else {
                    $_SESSION['error_message'] = "Failed to upload profile picture.";
                }
            } else {
                $_SESSION['error_message'] = "File size too large. Maximum 5MB allowed.";
            }
        } else {
            $_SESSION['error_message'] = "Invalid file type. Only JPEG, PNG, GIF, and WebP allowed.";
        }
    }

    if (isset($_POST['pet_id']) && !empty($_POST['pet_id'])) {
        $pet_id = $conn->real_escape_string($_POST['pet_id']);
        
        // Handle existing profile picture for updates
        if ($profile_picture === null && isset($edit_pet)) {
            $profile_picture = $edit_pet['profile_picture'];
        }
        
        $sql = "UPDATE pets SET 
                pet_name = '$pet_name',
                gender = '$gender',
                species = '$species',
                breed = '$breed',
                owner_name = '$owner_name',
                email = '$email',
                owner_contact = '$owner_contact',
                vaccination_status = '$vaccination_status',
                medical_history = '$medical_history',
                profile_picture = '$profile_picture'
                WHERE id = '$pet_id'";
        if ($conn->query($sql) === TRUE) {
            $_SESSION['success_message'] = "";
        } else {
            $_SESSION['error_message'] = "Error updating pet: " . $conn->error;
        }
    } 
    
    else {
        $sql = "INSERT INTO pets (pet_name, gender, species, breed, owner_name, email, owner_contact, vaccination_status, medical_history, profile_picture, registration_date) 
                VALUES ('$pet_name', '$gender', '$species', '$breed', '$owner_name', '$email', '$owner_contact', '$vaccination_status', '$medical_history', '$profile_picture', NOW())";
        if ($conn->query($sql) === TRUE) {
            $_SESSION['success_message'] = "";
        } else {
            $_SESSION['error_message'] = "Error: " . $conn->error;
        }
    }
    header("Location: list.php");
    exit();
}

// Fetch available inventory items (with stock > 0)
$products = [];
try {
    $stmt = $pdo->query("SELECT id, product_name, stock FROM inventory WHERE archived = 0 AND stock > 0 ORDER BY product_name ASC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $products = [];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../public/css/register.css">
    <link rel="stylesheet" href="css/notifications.css">
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
                        <h5>Bulan Veterinary Clinic</h5>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-home me-2"></i>Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="list.php">
                                <i class="fas fa-list me-2"></i>Pet List
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="customers_profile.php">
                                <i class="fas fa-users me-2"></i>Customers Profile
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="inventory.php">
                                <i class="fas fa-box me-2"></i>Inventory
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?php echo isset($edit_pet) ? 'Edit Pet' : 'Pet Registration'; ?></h1>
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
                <div class="cancel-btn">
                    <?php if (isset($edit_pet)): ?>
                        <a href="Register.php" class="btn btn-secondary">Cancel Edit</a>
                    <?php endif; ?>
                </div>

                <?php
                if (isset($_SESSION['success_message'])) {
                    echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
                    unset($_SESSION['success_message']);
                }
                if (isset($_SESSION['error_message'])) {
                    echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
                    unset($_SESSION['error_message']);
                }
                ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
                            <?php if (isset($edit_pet)): ?>
                                <input type="hidden" name="pet_id" value="<?php echo $edit_pet['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="pet_name" class="form-label">Pet Name</label>
                                    <input type="text" class="form-control" id="pet_name" name="pet_name" 
                                           value="<?php echo isset($edit_pet) ? htmlspecialchars($edit_pet['pet_name']) : ''; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="gender" class="form-label">Gender</label>
                                    <select class="form-select" id="gender" name="gender" required>
                                        <option value="">Gender</option>
                                        <?php
                                        $gender_options = ['male', 'female'];
                                        foreach ($gender_options as $option) {
                                            $selected = (isset($edit_pet) && $edit_pet['gender'] == $option) ? 'selected' : '';
                                            echo "<option value=\"$option\" $selected>$option</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="species" class="form-label">Species</label>
                                    <select class="form-select" id="species" name="species" required>
                                        <option value="">Select Species</option>
                                        <?php
                                        $species_options = ['Dog', 'Cat', 'Other'];
                                        foreach ($species_options as $option) {
                                            $selected = (isset($edit_pet) && $edit_pet['species'] == $option) ? 'selected' : '';
                                            echo "<option value=\"$option\" $selected>$option</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="breed" class="form-label">Breed</label>
                                    <input type="text" class="form-control" id="breed" name="breed" 
                                           value="<?php echo isset($edit_pet) ? htmlspecialchars($edit_pet['breed']) : ''; ?>" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="owner_name" class="form-label">Owner Name</label>
                                    <input type="text" class="form-control" id="owner_name" name="owner_name" 
                                           value="<?php echo isset($edit_pet) ? htmlspecialchars($edit_pet['owner_name']) : ''; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="text" class="form-control" id="email" name="email"
                                            value="<?php echo isset($edit_pet) ? htmlspecialchars($edit_pet['email']) : ''; ?>" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="owner_contact" class="form-label">Owner Contact</label>
                                    <input type="tel" class="form-control" id="owner_contact" name="owner_contact" 
                                           value="<?php echo isset($edit_pet) ? htmlspecialchars($edit_pet['owner_contact']) : ''; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="pet_status" class="form-label">Vaccination Status</label>
                                    <select class="form-select" id="vaccination_status" name="vaccination_status" required>
                                        <option value="">Select Status</option>
                                        <?php
                                        $vaccination_status_options = ['vaccinated', 'non-vaccinated'];
                                        foreach ($vaccination_status_options as $option) {
                                            $selected = (isset($edit_pet) && $edit_pet['vaccination_status'] == $option) ? 'selected' : '';
                                            echo "<option value=\"$option\" $selected>$option</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="product_used" class="form-label">Product Used</label>
                                <select class="form-select" id="product_used" name="product_used">
                                    <option value="">-- Select Product --</option>
                                    <?php foreach ($products as $p): ?>
                                        <option value="<?php echo $p['id']; ?>">
                                            <?php echo htmlspecialchars($p['product_name']); ?> (Stock: <?php echo (int)$p['stock']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="quantity_used" class="form-label">Quantity Used</label>
                                <input type="number" class="form-control" id="quantity_used" name="quantity_used" min="1" value="1">
                            </div>
                            <div class="mb-3">
                                <label for="medical_history" class="form-label">Medical History</label>
                                <textarea class="form-control" id="medical_history" name="medical_history" rows="3"><?php echo isset($edit_pet) ? htmlspecialchars($edit_pet['medical_history']) : ''; ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="profile_picture" class="form-label">Pet Profile Picture</label>
                                <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*" style="width: fit-content;">
                                <?php if (isset($edit_pet) && !empty($edit_pet['profile_picture'])): ?>
                                    <div class="mt-2">
                                        <p class="text-muted">Current picture:</p>
                                        <img src="uploads/pet_pics/<?php echo htmlspecialchars($edit_pet['profile_picture']); ?>" 
                                             alt="Current pet picture" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                                    </div>
                                <?php endif; ?>
                                <small class="form-text text-muted"></small>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <?php echo isset($edit_pet) ? 'Update Pet' : 'Register Pet'; ?>
                            </button>
                            <a href="list.php" class="btn btn-danger">Back</a>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="js/dropdown.js"></script>
    <script src="js/notifications.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>