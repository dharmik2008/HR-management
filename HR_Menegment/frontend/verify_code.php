<?php
session_start();
$conn = new mysqli("localhost", "root", "", "hrms_db");

if (!isset($_SESSION['reset_email'])) {
    header('location: forgot_password.php');
    exit();
}

if (isset($_POST['verify_code'])) {
    $code = $_POST['otp'];
    $email = $_SESSION['reset_email'];

    $stmt = $conn->prepare("SELECT reset_code, reset_expiry FROM employees WHERE Emp_email = ? AND reset_code = ?");
    $stmt->bind_param("si", $email, $code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (strtotime($row['reset_expiry']) < time()) {
             $error = "Code expired.";
        } else {
            $_SESSION['otp_verified'] = true;
            header('location: new_password.php');
            exit();
        }
    } else {
        $error = "Invalid Code.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Code | HRMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: linear-gradient(135deg, #c3ecf7 0%, #d4b1f5 50%, #fbd0e4 100%); height: 100vh; display: flex; align-items: center; justify-content: center; }
        .container { background: white; padding: 40px; border-radius: 16px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1); width: 100%; max-width: 400px; text-align: center; }
        .logo-area h2 { color: #5B4DBC; font-weight: 700; font-size: 24px; margin-bottom: 5px; }
        .logo-area p { color: #888; font-size: 14px; margin-bottom: 20px; }
        
        .input-group { text-align: left; margin-bottom: 20px; }
        .input-group label { display: block; font-size: 14px; color: #333; margin-bottom: 8px; font-weight: 500; }
        .input-group input { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 18px; letter-spacing: 5px; text-align: center; transition: 0.3s; }
        .input-group input:focus { border-color: #5B4DBC; outline: none; }

        .btn-primary { width: 100%; padding: 12px; background: #5B4DBC; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: 0.3s; }
        .btn-primary:hover { background: #483a9e; }
        
        .success-msg { background: #e6fffa; color: #2c7a7b; padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 15px; border-left: 4px solid #2c7a7b; }
        .error-msg { background: #ffe6e6; color: #d93025; padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 15px; border-left: 4px solid #d93025; }
    </style>
</head>
<body>

    <div class="container">
        <div class="logo-area">
            <h2>Verification</h2> 
            <p>Enter the code sent to your email</p>
        </div>

        <?php if(isset($_SESSION['info'])) echo "<div class='success-msg'>".$_SESSION['info']."</div>"; ?>
        <?php if(isset($error)) echo "<div class='error-msg'>$error</div>"; ?>

        <form method="POST" action="">
            <div class="input-group">
                <input type="number" name="otp" placeholder="123456" required>
            </div>
            <button type="submit" name="verify_code" class="btn-primary">Verify Code</button>
        </form>
    </div>

</body>
</html>