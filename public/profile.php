<?php

include_once '../database/db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $new_username = trim($_POST['username']);
        $new_email = trim($_POST['email']);

        // Basic validation
        if (empty($new_username) || empty($new_email)) {
            $error = 'Username and email are required.';
        } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            if ($stmt->execute([$new_username, $new_email, $user_id])) {
                $_SESSION['username'] = $new_username;
                $_SESSION['email'] = $new_email;
                $message = 'Profile updated successfully!';
            } else {
                $error = 'Failed to update profile.';
            }
        }
    }

    if (isset($_POST['upload_photo'])) {
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $file_name = $_FILES['profile_pic']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $file_size = $_FILES['profile_pic']['size'];
            
            if ($file_size > 5 * 1024 * 1024) {
                $error = 'File size too large. Maximum size is 5MB.';
            } elseif (in_array($file_ext, $allowed)) {
                $new_file_name = uniqid('', true) . '.' . $file_ext;
                $file_destination = 'uploads/profile_pics/' . $new_file_name;
                
                if (!is_dir('uploads/profile_pics/')) {
                    mkdir('uploads/profile_pics/', 0755, true);
                }

                if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $file_destination)) {
                    $stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
                    if ($stmt->execute([$new_file_name, $user_id])) {
                        $_SESSION['profile_pic'] = $new_file_name;
                        $message = 'Profile picture updated successfully!';
                    } else {
                        $error = 'Failed to update profile picture in database.';
                    }
                } else {
                    $error = 'Failed to upload file. Please check directory permissions.';
                }
            } else {
                $error = 'Invalid file type. Allowed types: jpg, jpeg, png, gif.';
            }
        } else {
            $error = 'Please select a file to upload.';
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$_SESSION['profile_pic'] = $user['profile_pic'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/profile.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
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
                            <a class="nav-link" href="inventory.php">
                                <i class="fas fa-box me-2"></i>Inventory
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 ms-sm-auto px-md-4">
                <div class="main-content">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">Profile Settings</h1>
                    </div>

                    <!-- Alert Messages -->
                    <?php if ($message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <!-- Profile Information Card -->
                        <div class="col-lg-6 mb-4">
                            <div class="card profile-card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>User Information</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" id="profileForm">
                                        <div class="mb-3">
                                            <label for="username" class="form-label">Username</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                                <input type="text" class="form-control" id="username" name="username" 
                                                       value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                                <input type="email" class="form-control" id="email" name="email" 
                                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                            </div>
                                        </div>
                                        <div class="d-grid gap-2">
                                            <button type="submit" name="update_profile" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>Update Profile
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- Profile Photo Upload Card -->
                        <div class="col-lg-6 mb-4">
                            <div class="card profile-card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-camera me-2"></i>Profile Photo</h5>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-4">
                                        <div class="current-profile-pic">
                                            <?php
                                            $profile_pic = isset($_SESSION['profile_pic']) ? $_SESSION['profile_pic'] : null;
                                            if ($profile_pic && !empty($profile_pic)) {
                                                $profile_path = 'uploads/profile_pics/' . $profile_pic;
                                                if (file_exists($profile_path)) {
                                                    echo '<img src="' . htmlspecialchars($profile_path) . '" alt="Current Profile Picture" class="current-pic">';
                                                } else {
                                                    echo '<div class="current-pic-placeholder"><i class="fas fa-user"></i></div>';
                                                }
                                            } else {
                                                echo '<div class="current-pic-placeholder"><i class="fas fa-user"></i></div>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <form method="POST" enctype="multipart/form-data" id="photoForm">
                                        <div class="mb-3">
                                            <label for="profile_pic" class="form-label">Choose a new profile picture</label>
                                            <div class="input-group">
                                                <input type="file" class="form-control" id="profile_pic" name="profile_pic" 
                                                       accept="image/*" required>
                                            </div>
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Supported formats: JPG, JPEG, PNG, GIF (Max: 5MB)
                                            </div>
                                        </div>
                                        <div class="d-grid gap-2">
                                            <button type="submit" name="upload_photo" class="btn btn-success">
                                                <i class="fas fa-upload me-2"></i>Upload Photo
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>