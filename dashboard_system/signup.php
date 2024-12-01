<?php
session_start();
require_once 'dbcon/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Get form data
  $fullName = $_POST['fullName'];
  $username = $_POST['username'];
  $email = $_POST['email'];
  $password = $_POST['password'];
  $confirmPassword = $_POST['confirmPassword'];

  // Validate form fields
  if (empty($fullName) || empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
    $error = "All fields are required.";
  } elseif ($password !== $confirmPassword) {
    $error = "Passwords do not match.";
  } elseif (strlen($password) < 8) {
    $error = "Password must be at least 8 characters long.";
  } else {
    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if the email already exists
    $sql = "SELECT * FROM users WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
      $error = "Email is already registered.";
    } else {
      // Insert the new user into the database (including full name)
      $sql = "INSERT INTO users (full_name, username, email, password) VALUES (:fullName, :username, :email, :password)";
      $stmt = $pdo->prepare($sql);
      $stmt->bindParam(':fullName', $fullName);
      $stmt->bindParam(':username', $username);
      $stmt->bindParam(':email', $email);
      $stmt->bindParam(':password', $hashedPassword);

      if ($stmt->execute()) {
        $_SESSION['message'] = "Registration successful! Please login.";
        // Set success flag to trigger modal
        $success = true;
      } else {
        $error = "Something went wrong. Please try again.";
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Arial', sans-serif;
    }

    .signup-bg {
      position: relative;
      background: linear-gradient(to right, #ff416c, #ff4b2b);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 0 15px;
    }

    .signup-bg::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
    }

    .signup-card {
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      background: #fff;
      width: 100%;
      max-width: 400px;
      padding: 30px;
    }

    .signup-btn {
      background: linear-gradient(to right, #ff416c, #ff4b2b);
      border: none;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .signup-btn:hover {
      background: linear-gradient(to right, #ff4b2b, #ff416c);
      transform: translateY(-2px);
      box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
    }

    .toggle-password {
      position: absolute;
      top: 75%;
      right: 15px;
      transform: translateY(-50%);
      cursor: pointer;
    }

    h2 {
      font-weight: bold;
      font-size: 1.8rem;
    }
    .form-control {
      border-radius: 25px;
      padding-right: 40px;
    }
    .form-control:focus {
      border-color: #ff4b2b;
      box-shadow: 0 0 5px rgba(102, 16, 242, 0.5);
    }

  </style>
</head>

<body class="signup-bg">
  <div class="card signup-card">
    <div class="text-center mb-4">
      <h2>Create Your Account</h2>
      <p class="text-muted">Join us today!</p>
    </div>

    <!-- Display error message if exists -->
    <?php if (isset($error)): ?>
      <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="signup.php" method="POST">
      <div class="mb-3">
        <label for="fullName" class="form-label">Full Name</label>
        <input type="text" class="form-control" id="fullName" name="fullName" required placeholder="Enter your full name">
      </div>
      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" class="form-control" id="username" name="username" required placeholder="Enter your username">
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <input type="email" class="form-control" id="email" name="email" required placeholder="Enter your email">
      </div>
      <div class="mb-3 password-wrapper position-relative">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required
          placeholder="Create a password" pattern=".{8,}" title="Password must be at least 8 characters long">
        <span class="toggle-password" id="togglePassword">
          <i class="bi bi-eye-slash"></i>
        </span>
      </div>
      <div class="mb-3 password-wrapper position-relative">
        <label for="confirmPassword" class="form-label">Confirm Password</label>
        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required
          placeholder="Confirm your password" pattern=".{8,}" title="Password must be at least 8 characters long">
        <span class="toggle-password" id="toggleConfirmPassword">
          <i class="bi bi-eye-slash"></i>
        </span>
      </div>
      <button type="submit" class="btn btn-primary w-100 signup-btn">Sign Up</button>
    </form>
    <div class="text-center mt-3">
      <p class="text-muted">Already have an account? <a href="index.php"
          class="text-primary text-decoration-none">Login</a></p>
    </div>
  </div>

  <!-- Modal -->
  <?php if (isset($success) && $success): ?>
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="successModalLabel">Registration Successful</h5>
          </div>
          <div class="modal-body">
            Your account has been created successfully! You will be redirected to the login page shortly.
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Bootstrap JS and Popper.js (ensure these are included) -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

  <script>
    // Show the success modal if registration was successful
    <?php if (isset($success) && $success): ?>
      var myModal = new bootstrap.Modal(document.getElementById('successModal'));
      myModal.show();

      // Redirect to login page after 3 seconds
      setTimeout(function() {
        window.location.href = "index.php";
      }, 3000);
    <?php endif; ?>
    
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const togglePassword = document.getElementById('togglePassword');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');

    togglePassword.addEventListener('click', function () {
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
</body>

</html>

