<?php
session_start();

// 1. Load PHPMailer Manually
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// 2. Connect to Database
$conn = new mysqli("localhost", "root", "", "hrms_db");

if (isset($_POST['send_code'])) {
    $email = $_POST['email']; 

    // Logic remains exactly the same
    $stmt = $conn->prepare("SELECT Emp_id FROM employees WHERE Emp_email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $code = rand(100000, 999999);
        $expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));

        $update = $conn->prepare("UPDATE employees SET reset_code = ?, reset_expiry = ? WHERE Emp_email = ?");
        $update->bind_param("iss", $code, $expiry, $email);
        $update->execute();

        $mail = new PHPMailer(true);
        // $mail->SMTPDebug = 2; // Commented out for production
        try {
            $mail->isSMTP();                                            
            $mail->Host       = 'smtp.gmail.com';                     
            $mail->SMTPAuth   = true;                                   
            $mail->Username   = 'helix7606@gmail.com'; 
            $mail->Password   = 'vjeivhuhfvnnmwxc'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            
            $mail->Port       = 465;                       

            $mail->setFrom('helix7606@gmail.com', 'HR System'); 
            $mail->addAddress($email);     

            $mail->isHTML(true);                                  
            $mail->Subject = 'Password Reset Code';
            $mail->Body    = "Your code is: <b>$code</b>";

            $mail->send();
            
            $_SESSION['info'] = "We sent a code to $email";
            $_SESSION['reset_email'] = $email;
            header('location: verify_code.php');
            exit();

        } catch (Exception $e) {
            $error = "Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $error = "This email does not exist.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | HRMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        
        body {
            background: linear-gradient(135deg, #c3ecf7 0%, #d4b1f5 50%, #fbd0e4 100%);
            height: 100vh;
            display: flex;
            align_items: center;
            justify-content: center;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .logo-area { margin-bottom: 20px; }
        .logo-area h2 { color: #5B4DBC; font-weight: 700; font-size: 24px; }
        .logo-area p { color: #888; font-size: 14px; margin-top: 5px; }

        .input-group { text-align: left; margin-bottom: 20px; }
        .input-group label { display: block; font-size: 14px; color: #333; margin-bottom: 8px; font-weight: 500; }
        .input-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            color: #333;
            transition: 0.3s;
        }
        .input-group input:focus { border-color: #5B4DBC; outline: none; box-shadow: 0 0 5px rgba(91, 77, 188, 0.2); }

        .btn-primary {
            width: 100%;
            padding: 12px;
            background: #5B4DBC;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-primary:hover { background: #483a9e; }

        .links { margin-top: 20px; font-size: 14px; color: #666; }
        .links a { color: #5B4DBC; text-decoration: none; font-weight: 500; }
        .links a:hover { text-decoration: underline; }

        .error-msg {
            background: #ffe6e6; color: #d93025; padding: 10px; border-radius: 6px;
            font-size: 13px; margin-bottom: 15px; border-left: 4px solid #d93025;
            text-align: left;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="logo-area">
            <h2>HELIX</h2> 
            <p>Reset your password</p>
        </div>

        <?php if(isset($error)) echo "<div class='error-msg'>$error</div>"; ?>

        <form method="POST" action="">
            <div class="input-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="Enter your registered email" required>
            </div>
            <button type="submit" name="send_code" class="btn-primary">Send Code</button>
        </form>

        <div class="links">
            <a href="index.php">Back to Login</a>
        </div>
    </div>

</body>
</html>