<?php
session_start();

// Display error message if set
if (isset($_SESSION['error_message'])) {
  $error_message = $_SESSION['error_message'];
  unset($_SESSION['error_message']); // Clear the error message after displaying
}
require_once 'dbcon/connect.php';

ini_set('display_errors', 1);  // Show errors for debugging
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        // Check if user exists
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Password is correct, start session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];

            // Set success flag to trigger modal
            $_SESSION['login_success'] = true;

            // Redirect to dashboard (with delay to show modal)
            header("Location: dashboard.php");  // You can also use JavaScript to redirect with delay for better user experience
            exit();
        } else {
            $error = "Invalid email or password";
        }
    } catch (PDOException $e) {
        // Log database errors
        error_log($e->getMessage());
        $error = "Database error, please try again later.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    /* General Styles */
    body {
      font-family: 'Arial', sans-serif;
    }

    .login-bg {
      position: relative;
      background: linear-gradient(to right, #6a11cb, #2575fc);
      height: 100vh;
      overflow: hidden;
    }

    .login-bg::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
    }

    .login-card {
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      background: #fff;
      transition: transform 0.3s ease-in-out, box-shadow 0.3s;
    }

    .logo {
      width: 80px;
      height: 80px;
    }

    .login-btn {
      background: linear-gradient(to right, #007bff, #6610f2);
      border: none;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .login-btn:hover {
      background: linear-gradient(to right,#6610f2, #007bff);
      transform: translateY(-2px);
      box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
    }

    .password-wrapper {
      position: relative;
    }

    .form-control {
      border-radius: 25px;
      padding-right: 40px;
    }

    .toggle-password {
      position: absolute;
      top: 73%;
      right: 10px;
      transform: translateY(-50%);
      cursor: pointer;
      color: #6c757d;
    }

    h2 {
      font-weight: bold;
      font-size: 1.8rem;
    }

    /* Animation */
    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .login-card {
      animation: fadeIn 1s ease-out;
    }

    .form-control:focus {
      border-color: #6610f2;
      box-shadow: 0 0 5px rgba(102, 16, 242, 0.5);
    }
  </style>
</head>
<body class="login-bg">
  <div class="d-flex justify-content-center align-items-center vh-100">
    <div class="card p-4 shadow-lg login-card">
      <div class="text-center mb-4">
        <img src="iscc.png" alt="Logo" class="logo">
        <h2 class="mt-3">Welcome Back!</h2>
        <p class="text-muted">Sign in to continue</p>
      </div>
      <!-- Show error if not logged in or if there's any login error -->
      <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
      <?php endif; ?>
       <!-- Display error if login fails -->
       <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form action="" method="POST">
        <div class="mb-3">
          <label for="email" class="form-label">Email Address</label>
          <input type="email" class="form-control" id="email" name="email" required placeholder="Enter your email">
        </div>
        <div class="mb-3 password-wrapper">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control" id="password" name="password" required placeholder="Enter your password">
          <span class="toggle-password" id="togglePassword">
            <i class="bi bi-eye-slash"></i>
          </span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="form-check">
            <input type="checkbox" class="form-check-input" id="rememberMe">
            <label class="form-check-label" for="rememberMe">Remember Me</label>
          </div>
        </div>
        <button type="submit" class="btn btn-primary w-100 login-btn">Login</button>
      </form>
      <div class="text-center mt-3">
        <p class="text-muted">Don't have an account? <a href="signup.php" class="text-primary text-decoration-none">Sign Up</a></p>
      </div>
    </div>
  </div>

  <!-- Modal -->
  <?php if (isset($_SESSION['login_success']) && $_SESSION['login_success']): ?>
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="successModalLabel">Login Successful</h5>
          </div>
          <div class="modal-body">
            Welcome back! You will be redirected shortly to your dashboard.
          </div>
        </div>
      </div>
    </div>
    <?php unset($_SESSION['login_success']); ?>
  <?php endif; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Show the success modal if login was successful
    <?php if (isset($_SESSION['login_success']) && $_SESSION['login_success']): ?>
      var myModal = new bootstrap.Modal(document.getElementById('successModal'));
      myModal.show();

      // Redirect to dashboard after 3 seconds
      setTimeout(function() {
        window.location.href = "dashboard.php";
      }, 3000);
    <?php endif; ?>
    
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');

    togglePassword.addEventListener('click', function () {
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);
      this.innerHTML =
        type === 'password'
          ? '<i class="bi bi-eye-slash"></i>'
          : '<i class="bi bi-eye"></i>';
    });
  </script>
</body>
</html>
