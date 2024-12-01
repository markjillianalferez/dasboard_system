<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  // If not logged in, redirect to login page
  $_SESSION['error_message'] = "You must log in to access the dashboard.";
  header("Location: index.php");
  exit();
}

// Database connection settings
$servername = "localhost";
$username = "root";  // Change this to your DB username
$password = "";  // Change this to your DB password
$dbname = "user_db";  // Change this to your DB name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Assuming user is logged in and user_id is stored in session
$user_id = $_SESSION['user_id'];

// Query to fetch user profile and settings details from the combined table
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch data and display
if ($result->num_rows > 0) {
  $user = $result->fetch_assoc();
  $full_name = $user['full_name'];
  $email = $user['email'];
  $phone = $user['phone'];
  $address = $user['address'];
  $username = $user['username'];
  $profile_picture = $user['profile_picture'];
} else {
  echo "No user found!";
}

// Initialize error variable
$password_error = "";

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Assuming we have an uploaded profile picture
  if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] === UPLOAD_ERR_OK) {
    $profile_picture = 'uploads/' . basename($_FILES['profilePicture']['name']);
    move_uploaded_file($_FILES['profilePicture']['tmp_name'], $profile_picture);
  } else {
    $profile_picture = null;  // Or use the existing one
  }

  $new_username = $_POST['newUsername'];
  $new_password = $_POST['newPassword'];
  $new_confirm_password = $_POST['confirmPassword'];  // Get the confirm password
  $new_phone = $_POST['newPhone'];
  $new_address = $_POST['newAddress'];

  // Check if passwords match
  if ($new_password !== $new_confirm_password) {
    $password_error = "Passwords do not match!";
  } else {
    // Assuming you handle the file upload and other details
    if (!empty($new_password)) {
      $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    }

    // Update the user details in the database
    $sql = "UPDATE users SET username = ?, password = ?, profile_picture = ?, phone = ?, address = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $new_username, $hashed_password, $profile_picture, $new_phone, $new_address, $user_id);
    $stmt->execute();
    $stmt->close();

    header("Location: dashboard.php");
  }
}

$stmt->close();
$conn->close(); // Close the database connection
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* General Styles */
    body {
      font-family: 'Arial', sans-serif;
    }

    .dashboard-bg {
      background: linear-gradient(to right, #00b4db, #0083b0);
      color: white;
      min-height: 100vh;
    }

    .card {
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .animated-title {
      font-size: 2rem;
      font-weight: bold;
      color: white;
    }

    .nav-tabs .nav-link {
      border-radius: 20px;
      background: #0083b0;
      color: white;
      transition: background 0.3s ease, transform 0.3s ease;
    }

    .nav-tabs .nav-link.active {
      background: #00b4db;
      color: white;
      transform: scale(1.1);
    }

    .btn-custom {
      background: linear-gradient(to right, #ff416c, #ff4b2b);
      color: white;
      border: none;
      transition: transform 0.3s, box-shadow 0.3s;
    }

    .btn-custom:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
    }

    .profile-picture {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      object-fit: cover;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
      margin-bottom: 15px;
    }

    .highlight-card {
      background: #ffffff;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
      padding: 20px;
      text-align: center;
      color: #333;
    }

    .highlight-card h4 {
      font-weight: bold;
      color: #00b4db;
    }

    .highlight-card p {
      font-size: 1.2rem;
    }

    .highlight-card .icon {
      font-size: 2rem;
      margin-bottom: 10px;
      color: #ff416c;
    }

    /* New Styles */
    .recent-activities {
      background-color: #fff;
      border-radius: 10px;
      padding: 20px;
      margin-top: 20px;
    }

    .recent-activities h5 {
      color: #00b4db;
      font-weight: bold;
    }

    .activity-item {
      padding: 10px;
      border-bottom: 1px solid #ddd;
    }

    .activity-item:last-child {
      border-bottom: none;
    }

    .activity-item p {
      margin: 0;
      font-size: 1rem;
    }

    .activity-item .timestamp {
      font-size: 0.85rem;
      color: #888;
    }

    .quick-links {
      background-color: #fff;
      border-radius: 10px;
      padding: 20px;
      margin-top: 20px;
    }

    .quick-links h5 {
      color: #00b4db;
      font-weight: bold;
    }

    .quick-links .btn {
      margin: 5px 0;
      width: 100%;
    }
    .btn-logout {
        display: inline-block;
        font-size: 1.2rem;
        font-weight: bold;
        color: white;
        background: linear-gradient(to right, #ff416c, #ff4b2b);
        padding: 12px 30px;
        border: none;
        border-radius: 50px;
        text-decoration: none;
        transition: all 0.3s ease-in-out;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    .btn-logout:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
    }

    .btn-logout:active {
        transform: scale(0.98);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    /* Mobile-Friendly Styling */
    @media (max-width: 576px) {
        .btn-logout {
            font-size: 1rem;
            padding: 10px 20px;
        }
    }
  </style>
</head>

<body class="dashboard-bg">
  <div class="container py-5">
    <div class="text-center mb-4">
      <h1 class="animated-title">Welcome to Your Dashboard</h1>
    </div>
    <div class="card p-4">
      <!-- Tabs -->
      <ul class="nav nav-tabs justify-content-center mb-4" id="dashboardTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button"
            role="tab" aria-controls="profile" aria-selected="true">
            View Profile
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button"
            role="tab" aria-controls="settings" aria-selected="false">
            Settings
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="highlights-tab" data-bs-toggle="tab" data-bs-target="#highlights" type="button"
            role="tab" aria-controls="highlights" aria-selected="false">
            Dashboard Highlights
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="activities-tab" data-bs-toggle="tab" data-bs-target="#activities" type="button"
            role="tab" aria-controls="activities" aria-selected="false">
            Recent Activities
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="links-tab" data-bs-toggle="tab" data-bs-target="#quick-links" type="button"
            role="tab" aria-controls="quick-links" aria-selected="false">
            Quick Links
          </button>
        </li>
      </ul>

      <!-- Tab Content -->
      <div class="tab-content" id="dashboardTabsContent">
        <!-- View Profile -->
        <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
          <h3 class="text-center">Your Profile Information</h3>
          <div class="text-center">
            <!-- Profile Picture -->
            <img src="<?php echo $profile_picture ? $profile_picture : 'default-profile.jpg'; ?>" alt="Profile Picture"
              class="profile-picture">
          </div>
          <div class="row mt-4">
            <div class="col-md-6">
              <h5>Full Name:</h5>
              <p><strong><?php echo $full_name; ?></strong></p>
            </div>
            <div class="col-md-6">
              <h5>Email:</h5>
              <p><strong><?php echo $email; ?></strong></p>
            </div>
            <div class="col-md-6 mt-3">
              <h5>Phone Number:</h5>
              <p><strong><?php echo $phone; ?></strong></p>
            </div>
            <div class="col-md-6 mt-3">
              <h5>Address:</h5>
              <p><strong><?php echo $address; ?></strong></p>
            </div>
          </div>
        </div>

        <!-- Settings -->
        <div class="tab-pane fade" id="settings" role="tabpanel" aria-labelledby="settings-tab">
          <h3 class="text-center">Account Settings</h3>
          <form class="mt-4" action="" method="POST" enctype="multipart/form-data">

            <!-- Error Message for Password Mismatch -->
            <?php if ($password_error): ?>
              <div class="alert alert-danger" role="alert">
                <?php echo $password_error; ?>
              </div>
            <?php endif; ?>

            <!-- Update Profile Picture -->
            <div class="mb-3">
              <label for="profilePicture" class="form-label">Update Profile Picture</label>
              <input type="file" class="form-control" name="profilePicture" id="profilePicture">
            </div>
            <!-- Update Username -->
            <div class="mb-3">
              <label for="newUsername" class="form-label">New Username</label>
              <input type="text" class="form-control" name="newUsername" id="newUsername"
                value="<?php echo $username; ?>" placeholder="Enter new username">
            </div>
            <!-- Update Password -->
            <div class="mb-3">
              <label for="newPassword" class="form-label">New Password</label>
              <input type="password" class="form-control" name="newPassword" id="newPassword"
                placeholder="Enter new password">
            </div>
            <div class="mb-3">
              <label for="confirmPassword" class="form-label">Confirm Password</label>
              <input type="password" class="form-control" name="confirmPassword" id="confirmPassword"
                placeholder="Confirm new password">
            </div>

            <!-- Update Phone Number -->
            <div class="mb-3">
              <label for="newPhone" class="form-label">New Phone Number</label>
              <input type="text" class="form-control" name="newPhone" id="newPhone" value="<?php echo $phone; ?>"
                placeholder="Enter new phone number">
            </div>

            <!-- Update Address -->
            <div class="mb-3">
              <label for="newAddress" class="form-label">New Address</label>
              <textarea class="form-control" name="newAddress" id="newAddress" rows="3"
                placeholder="Enter new address"><?php echo $address; ?></textarea>
            </div>

            <div class="text-center">
              <button type="submit" class="btn btn-custom w-50">Update</button>
            </div>
          </form>
        </div>


        <!-- Dashboard Highlights -->
        <div class="tab-pane fade" id="highlights" role="tabpanel" aria-labelledby="highlights-tab">
          <h3 class="text-center">Dashboard Highlights</h3>
          <div class="row mt-4">
            <div class="col-md-4">
              <div class="highlight-card">
                <div class="icon">📊</div>
                <h4>Total Activities</h4>
                <p><strong>34</strong></p>
              </div>
            </div>
            <div class="col-md-4">
              <div class="highlight-card">
                <div class="icon">🕒</div>
                <h4>Hours Logged</h4>
                <p><strong>120</strong></p>
              </div>
            </div>
            <div class="col-md-4">
              <div class="highlight-card">
                <div class="icon">🏆</div>
                <h4>Achievements</h4>
                <p><strong>5</strong></p>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activities -->
        <div class="tab-pane fade" id="activities" role="tabpanel" aria-labelledby="activities-tab">
          <div class="recent-activities">
            <h5>Recent Activities</h5>
            <div class="activity-item">
              <p>Completed <strong>5 Activities</strong> in the last 24 hours.</p>
              <p class="timestamp">2 hours ago</p>
            </div>
            <div class="activity-item">
              <p>Logged <strong>8 hours</strong> in the system.</p>
              <p class="timestamp">Yesterday</p>
            </div>
            <div class="activity-item">
              <p>Achieved <strong>Level 5</strong> milestone.</p>
              <p class="timestamp">3 days ago</p>
            </div>
          </div>
        </div>
        <!-- Quick Links -->
        <div class="tab-pane fade" id="quick-links" role="tabpanel" aria-labelledby="links-tab">
          <div class="quick-links">
            <h5>Quick Links</h5>
            <button class="btn btn-custom w-100 mb-2" onclick="changeTab('profile')">View Profile</button>
            <button class="btn btn-custom w-100 mb-2" onclick="changeTab('settings')">Settings</button>
            <button class="btn btn-custom w-100 mb-2" onclick="changeTab('activities')">Activity Log</button>
            <button class="btn btn-custom w-100 mb-2" id="showPasswordBtn">Show Password of Account</button>
          </div>
        </div>

      </div>
    </div>

    <!-- Logout Button -->
    <div class="text-center mt-4">
      <a href="dbcon/logout.php" class="btn btn-secondary btn-lg btn-logout">Logout</a>
    </div>

    <script>

  document.getElementById('showPasswordBtn').addEventListener('click', function () {
    // Display the password securely
    alert("Your password is: <?php echo isset($user['password']) ? $user['password'] : 'Not available'; ?>");
  });

      function changeTab(tabId) {
        // Remove the 'active' class f  rom all tabs
        document.querySelectorAll('.nav-link').forEach(tab => {
          tab.classList.remove('active');
        });

        // Add the 'active' class to the clicked tab
        document.querySelector(`#${tabId}-tab`).classList.add('active');

        // Show the corresponding tab content
        document.querySelectorAll('.tab-pane').forEach(pane => {
          pane.classList.remove('show', 'active');
        });

        document.querySelector(`#${tabId}`).classList.add('show', 'active');
      }

      const passwordInput = document.getElementById('password');
      const confirmPasswordInput = document.getElementById('confirmPassword');
      const togglePassword = document.getElementById('togglePassword');
      const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');

      togglenewPassword.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.innerHTML =
          type === 'password'
            ? '<i class="bi bi-eye-slash"></i>'
            : '<i class="bi bi-eye"></i>';
      });

      toggleConfirmPassword.addEventListener('click', function () {
        const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmPasswordInput.setAttribute('type', type);
        this.innerHTML =
          type === 'password'
            ? '<i class="bi bi-eye-slash"></i>'
            : '<i class="bi bi-eye"></i>';
      });

      
    </script>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>