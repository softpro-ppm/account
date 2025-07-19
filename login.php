<?php
// Database connection
include 'inc/config.php'; // Include the database connection file

$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Check if the user exists
    $sql = "SELECT * FROM users WHERE username = ? AND status = 'Active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Start session and store user data
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect to the dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            $message = "<div class='alert alert-danger'>Invalid password. Please try again.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>User not found or inactive. Please contact the administrator.</div>";
    }
}
/*
// Hash the password
$password = password_hash('admin@12345', PASSWORD_DEFAULT);

// Insert the user into the database
$sql = "INSERT INTO users (username, name, password, role, email, status) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssss", $username, $name, $hashedPassword, $role, $email, $status);

// User details
$username = 'admin';
$name = 'Administrator';
$hashedPassword = $password;
$role = 'Admin';
$email = 'admin@example.com';
$status = 'Active';

if ($stmt->execute()) {
    echo "User 'admin' inserted successfully.";
} else {
    echo "Error: " . $stmt->error;
}
*/

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
  <link rel="stylesheet" href="assets/css/responsive.css">
  <style>
    body {
      background: linear-gradient(135deg, #f8f9fa, #e9ecef);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .login-container {
      background: white;
      padding: 2rem;
      border-radius: 0.5rem;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 400px;
    }
    .login-container h3 {
      margin-bottom: 1.5rem;
      font-weight: bold;
      text-align: center;
    }
    .form-control {
      border-radius: 0.5rem;
    }
    .btn-primary {
      border-radius: 0.5rem;
      width: 100%;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <h3>Login</h3>
    <?php if (!empty($message)): ?>
      <?php echo $message; ?>
    <?php endif; ?>
    <form action="" method="POST">
      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
      </div>
      <button type="submit" class="btn btn-primary">Login</button>
    </form>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/responsive.js"></script>
</body>
</html>