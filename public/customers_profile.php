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

// Fetch customers with their pets
$sql = "
    SELECT DISTINCT owner_name, email, owner_contact,
           pet_name, species, breed, gender, vaccination_status, profile_picture
    FROM pets 
    WHERE archived = 0
    ORDER BY owner_name ASC
";

$result = mysqli_query($conn, $sql);

// Group pets by owner
$customers = [];
while ($row = mysqli_fetch_assoc($result)) {
    $ownerName = $row['owner_name'];
    if (!isset($customers[$ownerName])) {
        $customers[$ownerName] = [
            'name' => $row['owner_name'],
            'email' => $row['email'],
            'contact' => $row['owner_contact'],
            'pets' => []
        ];
    }
    if ($row['pet_name']) {
        $customers[$ownerName]['pets'][] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../public/css/customers_profile.css">
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
                            <a class="nav-link active" href="customers_profile.php">
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
                    <h1 class="h2">Customers Profile</h1>
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

                <div class="row g-4">
                    <?php foreach ($customers as $customer): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="customer-card">
                            <div class="customer-header">
                                <div class="customer-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <h5><?php echo htmlspecialchars($customer['name']); ?></h5>
                                    <p class="text-muted"><?php echo htmlspecialchars($customer['email']); ?></p>
                                    <p class="text-muted"><?php echo htmlspecialchars($customer['contact']); ?></p>
                                </div>
                                <div class="customer-actions mt-3">
                                    <form method="POST" action="send_sms.php">
                                        <input type="hidden" name="contact" value="<?php echo htmlspecialchars($customer['contact']); ?>">
                                        <input type="hidden" name="name" value="<?php echo htmlspecialchars($customer['name']); ?>">
                                        <button type="submit" class="btn btn-primary btn-sm"> Message</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </main>
        </div>
    </div>
    <script src="js/dropdown.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>