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

if (isset($_POST['archive_pet'])) {
    $pet_id = $conn->real_escape_string($_POST['pet_id']);
    $sql = "UPDATE pets SET archived = 1 WHERE id = '$pet_id'";
    
    if ($conn->query($sql) === TRUE) {
        $_SESSION['success_message'] = "Pet archived successfully.";
    } else {
        $_SESSION['error_message'] = "Error archiving pet: " . $conn->error;
    }
    
    header("Location: list.php");
    exit();
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../public/css/list.css">
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
                    <h1 class="h2">Pet List</h1>
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
                    <div class="btn-group">
                      <a href="Register.php" class="btn btn-secondary">
                        <i class="fas fa-plus me-2"></i> Add New Pet
                      </a>
                    </div>
                    <div class="btn-archive">
                      <a href="archive_pets.php" class="btn btn-warning">
                        <i class="fas fa-archive me-2"></i> Archives
                      </a>
                    </div>
                    
                    <!-- Search Bar -->
                    <div class="search-container">
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" id="petSearchInput" class="search-input" placeholder="Search by pet name...">
                            <button type="button" id="clearSearch" class="clear-search" style="display: none;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="table-responsive">
                        <table class="table table-custom align-middle">
                            <thead>
                                <tr>
                                    <th>Profile</th>
                                    <th>Pet Name</th>
                                    <th>Owner Name</th>
                                    <th>Email</th>
                                    <th>Contact</th>
                                    <th>Vaccination Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="petTableBody">
                                <?php
                                $sql = "SELECT * FROM pets WHERE archived = 0 ORDER BY registration_date DESC";
                                $result = $conn->query($sql);

                                if ($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>";
                                        if (!empty($row['profile_picture'])) {
                                            $profile_path = 'uploads/pet_pics/' . $row['profile_picture'];
                                            if (file_exists($profile_path)) {
                                                echo '<img src="' . htmlspecialchars($profile_path) . '" alt="Pet Picture" class="pet-profile-pic" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">';
                                            } else {
                                                echo '<div class="pet-pic-placeholder" style="width: 50px; height: 50px; background-color: #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center;"><i class="fas fa-paw text-muted"></i></div>';
                                            }
                                        } else {
                                            echo '<div class="pet-pic-placeholder" style="width: 50px; height: 50px; background-color: #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center;"><i class="fas fa-paw text-muted"></i></div>';
                                        }
                                        echo "</td>";
                                        echo "<td>" . htmlspecialchars($row['pet_name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['owner_name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['owner_contact']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['vaccination_status']) . "</td>";
                                        echo "<td>";
                                        echo "<a href='register.php?edit=" . $row['id'] . "' class='btn btn-sm btn-primary me-2'><i class='fas fa-edit'></i></a>";
                                        echo "<button type='button' class='btn btn-sm btn-info me-2' data-bs-toggle='modal' data-bs-target='#readRecordModal' ";
                                        echo "data-pet='" . htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') . "'";
                                        echo "><i class='fas fa-eye'></i></button>";
                                        echo "<form method='POST' style='display: inline;' onsubmit='return confirm(\"Are you sure you want to archive this pet?\")'>";
                                        echo "<input type='hidden' name='pet_id' value='" . $row['id'] . "'>";
                                        echo "<button type='submit' name='archive_pet' class='btn btn-sm btn-warning'><i class='fas fa-archive'></i></button>";
                                        echo "</form>";
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='10' class='text-center'>No pets registered yet</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <div class="modal fade" id="readRecordModal" tabindex="-1" aria-labelledby="readRecordModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content pet-modal">
          <div class="modal-header pet-modal-header">
            <div class="d-flex align-items-center gap-2">
              <i class="fas fa-paw"></i>
              <h5 class="modal-title mb-0" id="readRecordModalLabel">Pet Details</h5>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row g-4 align-items-center mb-2">
              <div class="col-12 col-md-4 text-center">
                <img id="modal_profile_picture" src="" alt="Pet Picture" class="pet-avatar img-fluid" style="display: none;">
                <div id="modal_no_picture" class="pet-avatar-empty" style="display: none;">
                  <i class="fas fa-paw"></i>
                </div>
              </div>
              <div class="col-12 col-md-8">
                <div class="card shadow-sm border-0 rounded-4">
                  <div class="card-body">
                    <div class="row g-3">
                      <div class="col-sm-6">
                        <div class="detail">
                          <div class="detail-label">Pet Name</div>
                          <div class="detail-value" id="modal_pet_name"></div>
                        </div>
                      </div>
                      <div class="col-sm-6">
                        <div class="detail">
                          <div class="detail-label">Gender</div>
                          <div class="detail-value" id="modal_gender"></div>
                        </div>
                      </div>
                      <div class="col-sm-6">
                        <div class="detail">
                          <div class="detail-label">Species</div>
                          <div class="detail-value" id="modal_species"></div>
                        </div>
                      </div>
                      <div class="col-sm-6">
                        <div class="detail">
                          <div class="detail-label">Breed</div>
                          <div class="detail-value" id="modal_breed"></div>
                        </div>
                      </div>
                      <div class="col-sm-6">
                        <div class="detail">
                          <div class="detail-label">Registration Date</div>
                          <div class="detail-value" id="modal_registration_date"></div>
                        </div>
                      </div>
                      <div class="col-sm-6">
                        <div class="detail">
                          <div class="detail-label">Vaccination Status</div>
                          <div class="detail-value"><span id="modal_vaccination_status" class="badge-status"></span></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row g-4 mt-1">
              <div class="col-12 col-lg-6">
                <div class="card shadow-sm border-0 rounded-4 h-100">
                  <div class="card-body">
                    <h6 class="section-title">Owner</h6>
                    <div class="detail">
                      <div class="detail-label">Owner Name</div>
                      <div class="detail-value" id="modal_owner_name"></div>
                    </div>
                    <div class="detail mt-2">
                      <div class="detail-label">Email</div>
                      <div class="detail-value" id="modal_email"></div>
                    </div>
                    <div class="detail mt-2">
                      <div class="detail-label">Owner Contact</div>
                      <div class="detail-value" id="modal_owner_contact"></div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-12 col-lg-6">
                <div class="card shadow-sm border-0 rounded-4 h-100">
                  <div class="card-body">
                    <h6 class="section-title">Medical</h6>
                    <div class="detail">
                      <div class="detail-label">Medical History</div>
                      <div class="detail-value" id="modal_medical_history"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/dropdown.js"></script>
    <script src="../public/js/list.js"></script>
    <script src="../public/js/search.js"></script>
</body>
</html>