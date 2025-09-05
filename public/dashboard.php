<?php

include_once '../database/db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$sqlPets = "SELECT COUNT(*) AS total_pets FROM pets";
$resultPets = mysqli_query($conn, $sqlPets);
$rowPets = mysqli_fetch_assoc($resultPets);
$totalPets = $rowPets['total_pets'];

$sqlInventory = "SELECT COUNT(*) AS total_inventory FROM inventory";
$resultInventory = mysqli_query($conn, $sqlInventory);
$rowInventory = mysqli_fetch_assoc($resultInventory);
$totalInventory = $rowInventory['total_inventory'];

$sqlInventory = "SELECT COUNT(*) AS total_price FROM inventory";
$resultInventory = mysqli_query($conn, $sqlInventory);
$rowInventory = mysqli_fetch_assoc($resultInventory);
$totalPrice = $rowInventory['total_price'];

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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
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
                            <a class="nav-link active" href="#">
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
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="dropdown" id="profileDropdown">
                        <button class="profile-btn" onclick="toggleDropdown()">
                            <img src="<?php echo htmlspecialchars($profileImageSrc); ?>" alt="Profile">
                        </button>
                        <div class="dropdown-content">
                            <div class="profile-header">
                                <img src="<?php echo htmlspecialchars($profileImageSrc); ?>" alt="Profile">
                                <h4>Hi, <?php echo htmlspecialchars($username); ?> <span class="badge"> Online</span></h4>
                            </div>
                            <a href="profile.php">My Profile</a>
                            <a href="logout.php">Sign Out</a>
                        </div>
                    </div>
                </div>
                
                <!-- Welcome Section -->
                <div class="row mb-4">
                    <div class="col-lg-8">
                        <div class="card welcome-card">
                            <div class="card-body">
                                <h3 class="card-title">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h3>
                                <p class="card-text">Manage your veterinary practice efficiently with our comprehensive dashboard.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Modern Stats Section -->
                <div class="row mb-4 g-4">
                    <div class="col-md-3">
                        <div class="stat-card p-4">
                            <div class="stat-icon bg-primary">
                                <i class="fas fa-paw"></i>
                            </div>
                            <div>
                                <h3 class="stat-number"><?php echo $totalPets; ?></h3>
                                <p class="stat-label">Total Pets</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card p-4">
                            <div class="stat-icon bg-success">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div>
                                <h3 class="stat-number"><?php echo $totalPrice; ?></h3>
                                <p class="stat-label">Total Price</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card p-4">
                            <div class="stat-icon bg-warning">
                                <i class="fas fa-pills"></i>
                            </div>
                            <div>
                                <h3 class="stat-number"><?php echo $totalInventory; ?></h3>
                                <p class="stat-label">Inventory Items</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card p-4">
                            <div class="stat-icon bg-info">
                                <i class="fas fa-users"></i>
                            </div>
                            <div>
                                <h3 class="stat-number"><?php echo $totalPets; ?></h3>
                                <p class="stat-label">Clients</p>
                            </div>
                        </div>
                    </div>
                </div>

                
                <!-- Quick Access Section (Modern Design) -->
                <div class="row g-4">
                    <!-- Quick Actions -->
                    <div class="col-lg-6">
                        <div class="modern-card">
                            <div class="modern-card-header">
                                <h5><i class="fas fa-tasks me-2"></i>Quick Actions</h5>
                            </div>
                            <div class="modern-card-body">
                                <div class="quick-actions">
                                    <a href="profile.php" class="quick-btn bg-secondary">
                                        <i class="fas fa-user"></i>
                                        <span>View Profile</span>
                                    </a>
                                    <a href="list.php" class="quick-btn bg-primary">
                                        <i class="fas fa-plus"></i>
                                        <span>Add New Pet</span>
                                    </a>
                                    <a href="inventory.php" class="quick-btn bg-success">
                                        <i class="fas fa-box"></i>
                                        <span>Manage Inventory</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="col-lg-6">
                        <div class="modern-card">
                            <div class="modern-card-header">
                                <h5><i class="fas fa-clock me-2"></i>Recent Activity</h5>
                            </div>
                            <div class="modern-card-body">
                                <ul class="activity-list">
                                    <li>
                                        <div class="activity-icon bg-primary">
                                            <i class="fas fa-paw"></i>
                                        </div>
                                        <div class="activity-text">
                                            <p>New pet <b>"Max"</b> registered</p>
                                            <small>2 hours ago</small>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="activity-icon bg-success">
                                            <i class="fas fa-calendar"></i>
                                        </div>
                                        <div class="activity-text">
                                            <p>Appointment scheduled for tomorrow</p>
                                            <small>4 hours ago</small>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="activity-icon bg-warning">
                                            <i class="fas fa-box"></i>
                                        </div>
                                        <div class="activity-text">
                                            <p>Inventory updated</p>
                                            <small>1 day ago</small>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="js/dropdown.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>