<?php
session_start();
$conn = new mysqli("localhost", "root", "", "hrms_db");

if (!isset($_SESSION['otp_verified'])) {
    header('location: forgot_password.php');
    exit();
}

if (isset($_POST['change_password'])) {
    $new_pass = $_POST['password'];
    $confirm_pass = $_POST['confirm_password'];

    if ($new_pass === $confirm_pass) {
        $email = $_SESSION['reset_email'];
        $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE employees SET Emp_password = ?, reset_code = NULL, reset_expiry = NULL WHERE Emp_email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);
        
        if ($stmt->execute()) {
            session_destroy();
            // Success Message with Redirect link
            $success = "Password Changed Successfully!";
        } else {
            $error = "DB Error.";
        }
    } else {
        $error = "Passwords do not match.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Password | HRMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: linear-gradient(135deg, #c3ecf7 0%, #d4b1f5 50%, #fbd0e4 100%); height: 100vh; display: flex; align-items: center; justify-content: center; }
        .container { background: white; padding: 40px; border-radius: 16px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1); width: 100%; max-width: 400px; text-align: center; }
        
        .logo-area h2 { color: #5B4DBC; font-weight: 700; font-size: 24px; margin-bottom: 5px; }
        .logo-area p { color: #888; font-size: 14px; margin-bottom: 20px; }

        .input-group { text-align: left; margin-bottom: 20px; }
        .input-group label { display: block; font-size: 14px; color: #333; margin-bottom: 8px; font-weight: 500; }
        .input-group input { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; color: #333; transition: 0.3s; }
        .input-group input:focus { border-color: #5B4DBC; outline: none; }

        .btn-primary { width: 100%; padding: 12px; background: #5B4DBC; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: 0.3s; }
        .btn-primary:hover { background: #483a9e; }
        
        .error-msg { background: #ffe6e6; color: #d93025; padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 15px; border-left: 4px solid #d93025; text-align: left;}
        .success-container { text-align: center; }
        .success-container h3 { color: #2c7a7b; margin-bottom: 10px; }
        .success-container a { display: inline-block; background: #5B4DBC; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; margin-top: 10px; }
    </style>
</head>
<body>

    <div class="container">
        
        <?php if(isset($success)): ?>
            <div class="success-container">
                <h3>Success!</h3>
                <p><?php echo $success; ?></p>
                <a href="index.php">Login Now</a>
            </div>
        <?php else: ?>
            <div class="logo-area">
                <h2>New Password</h2> 
                <p>Create a secure password</p>
            </div>

            <?php if(isset($error)) echo "<div class='error-msg'>$error</div>"; ?>

            <form method="POST" action="">
                <div class="input-group">
                    <label>New Password</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
                <div class="input-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" placeholder="••••••••" required>
                </div>
                <button type="submit" name="change_password" class="btn-primary">Reset Password</button>
            </form>
        <?php endif; ?>

    </div>

</body>
</html>