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
    $stmt = $conn->prepare("SELECT Emp_id, Emp_firstName FROM employees WHERE Emp_email = ?");
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

            // Use CID embedding for reliability (Base64 can be too large for some email clients)
            $logoPath = __DIR__ . '/../asset s/HELIX.png';
            if (file_exists($logoPath)) {
                $mail->addEmbeddedImage($logoPath, 'helix_logo');
                $logoHtml = "<img src='cid:helix_logo' alt='HELIX' style='height: 60px; width: auto;'>";
            } else {
                $logoHtml = "<h2 style='color: #000; font-size: 28px; font-weight: 800; letter-spacing: -0.5px; margin: 0;'>HELIX</h2>";
            }

            $row = $result->fetch_assoc();
            $firstName = $row['Emp_firstName'] ?? 'User';

            $mail->isHTML(true);                                  
            $mail->Subject = 'Verify your helper account';
            
            // Stylized HTML Body
            $mail->Body = "
            <div style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 40px 20px; color: #1a1a1a;'>
                <div style='text-align: center; margin-bottom: 40px;'>
                    $logoHtml
                </div>
                
                <h1 style='font-size: 32px; font-weight: 800; text-align: center; margin-bottom: 40px; color: #000;'>Your password reset code</h1>
                
                <div style='background-color: #f4f4f4; border-radius: 16px; padding: 40px; margin-bottom: 40px; text-align: center;'>
                    <span style='font-size: 56px; font-weight: 800; letter-spacing: 4px; color: #000; font-family: monospace;'>$code</span>
                </div>
                
                <div style='font-size: 16px; line-height: 1.6;'>
                    <p style='margin-bottom: 20px; font-weight: 500;'>Hi $firstName,</p>
                    <p style='margin-bottom: 20px; color: #444;'>
                        You recently requested to reset the password for your HELIX HRMS account. In order to complete your login, please use the above code.
                    </p>
                    <p style='margin-top: 40px; font-size: 14px; color: #888;'>
                        If you did not request this code, you can safely ignore this email. Someone else might have typed your email address by mistake.
                    </p>
                </div>
                
                <div style='margin-top: 60px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; font-size: 12px; color: #aaa;'>
                    &copy; " . date('Y') . " HELIX HRMS. All rights reserved.
                </div>
            </div>
            ";

            // Plain text version for fallback
            $mail->AltBody = "Hi $firstName,\n\nYour password reset code is: $code\n\nIf you did not request this, please ignore this email.";

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