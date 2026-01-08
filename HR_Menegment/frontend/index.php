<?php
require_once __DIR__ . '/../backend/bootstrap.php';

// If already logged in, redirect
if (Session::isLoggedIn()) {
    if (Session::isAdmin()) {
        header('Location: ' . APP_URL . '/admin%20panel/dashboard.php');
    } else {
        header('Location: ' . APP_URL . '/frontend/employee-dashboard.php');
    }
    exit;
}

$error = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $userType = sanitize($_POST['user_type'] ?? 'employee');
    
    if (empty($email) || empty($password)) {
        $error = 'Email and password are required';
    } else {
        if ($userType === 'hr') {
            if ($auth->loginHRAdmin($email, $password)) {
                header('Location: ' . APP_URL . '/admin%20panel/dashboard.php');
                exit;
            } else {
                $error = 'Invalid HR credentials';
            }
        } else {
            if ($auth->loginEmployee($email, $password)) {
                header('Location: ' . APP_URL . '/frontend/employee-dashboard.php');
                exit;
            } else {
                $error = 'Invalid employee credentials';
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
    <link rel="icon" type="image/png" href="../assets/HELIX.png">
    <title>HRMS | Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/dark-mode.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: radial-gradient(circle at 20% 20%, rgba(79,70,229,0.14), transparent 28%),
                        radial-gradient(circle at 80% 0%, rgba(16,185,129,0.18), transparent 32%),
                        radial-gradient(circle at 10% 80%, rgba(236,72,153,0.14), transparent 30%),
                        linear-gradient(140deg, #f7f9ff 0%, #eef2ff 45%, #f8fbff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: "Inter", system-ui, -apple-system, sans-serif;
            position: relative;
            overflow: hidden;
        }
        /* Animated soft glow blobs */
        .bg-blob {
            position: absolute;
            width: 420px;
            height: 420px;
            filter: blur(90px);
            opacity: 0.35;
            animation: float 18s ease-in-out infinite alternate;
            z-index: 0;
        }
        .bg-blob.one { background: #4f46e5; top: -140px; right: -80px; animation-delay: 0s; }
        .bg-blob.two { background: #0ea5e9; bottom: -120px; left: -60px; animation-delay: 4s; }
        .bg-blob.three { background: #ec4899; bottom: 10%; right: 20%; animation-delay: 8s; width: 320px; height: 320px; }
        @keyframes float {
            0%   { transform: translate3d(0,0,0) scale(1); }
            50%  { transform: translate3d(12px,-10px,0) scale(1.05); }
            100% { transform: translate3d(-10px,16px,0) scale(1.02); }
        }
        /* Subtle noise overlay */
        .noise {
            position: fixed;
            inset: 0;
            pointer-events: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='120' viewBox='0 0 120 120'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='120' height='120' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
            z-index: 0;
        }
        .card {
            border: 1px solid #eef0f6;
            border-radius: 16px;
            box-shadow: 0 24px 60px rgba(43, 54, 116, 0.12);
            position: relative;
            z-index: 1;
            backdrop-filter: blur(8px);
            background: rgba(255,255,255,0.9);
        }
        .brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            color: #4f46e5;
            text-decoration: none;
        }
        .brand span {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: #4f46e5;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }
        .tab-pill {
            border-radius: 10px;
            font-weight: 600;
        }
        .tab-pill.active {
            background: #4f46e5;
            color: #fff;
        }
        .tab-pill:not(.active) {
            background: #f3f4f6;
            color: #4b5563;
        }
        .btn-primary {
            background: #4f46e5;
            border-color: #4f46e5;
        }
        .btn-primary:hover {
            background: #4338ca;
            border-color: #4338ca;
        }
        .form-control:focus {
            border-color: #c7d2fe;
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.15);
        }
        .link-muted { color: #6b7280; text-decoration: none; }
        .link-muted:hover { color: #4338ca; }
        .alert { border-radius: 10px; }
    </style>
</head>
<body>
<div class="bg-blob one"></div>
<div class="bg-blob two"></div>
<div class="bg-blob three"></div>
<div class="noise"></div>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card p-4 p-md-5 bg-white">
                <div class="d-flex align-items-center mb-4">
                    <img src="../assets/HELIX.png" alt="HELIX Logo" style="height:60px; width:auto; max-width:100%; border-radius:8px; object-fit:contain; margin-right:10px;">
                    <div>
                        <div class="fw-bold" style="color:#4f46e5; font-size:1.25rem;">HELIX</div>
                        <div class="text-muted small">HR Management System</div>
                    </div>
                </div>

                <div class="d-flex gap-2 mb-4">
                    <button class="btn tab-pill active w-50" type="button" data-target="employee">Employee</button>
                    <button class="btn tab-pill w-50" type="button" data-target="hr">HR</button>
                </div>

                <h4 class="mb-4">Sign in to continue to HRMS</h4>
                
                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" id="userTypeInput" name="user_type" value="employee">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address</label>
                        <input type="email" class="form-control form-control-lg" name="email" placeholder="your@email.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password</label>
                        <div class="input-group input-group-lg">
                            <input type="password" class="form-control" name="password" placeholder="••••••••" id="passwordField" required>
                            <span class="input-group-text" style="cursor: pointer;" onclick="togglePassword()"><i class="bi bi-eye"></i></span>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="remember" name="remember">
        <label class="form-check-label" for="remember">Remember me</label>
    </div>
    <a href="forgot_password.php" class="link-muted small" id="forgotPasswordLink">Forgot password?</a>
</div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Log In</button>
                    </div>
                </form>
                    <div class="text-center mt-4 pt-3 border-top">
                    <small class="text-muted">©2025 Design And Developed By Helix Team (Vaishnani Dharmik, Nirav Nimavat, Shivam Kansagara)</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const tabs = document.querySelectorAll('.tab-pill');
    const userTypeInput = document.getElementById('userTypeInput');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            const target = tab.dataset.target;
            userTypeInput.value = target;

            // Show/Hide forgot password link
            const forgotPasswordLink = document.getElementById('forgotPasswordLink');
            if (target === 'hr') {
                forgotPasswordLink.style.display = 'none';
            } else {
                forgotPasswordLink.style.display = 'block';
            }
        });
    });

    function togglePassword() {
        const field = document.getElementById('passwordField');
        field.type = field.type === 'password' ? 'text' : 'password';
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php include __DIR__ . '/partials/theme-toggle.php'; ?>
<script src="../assets/js/dark-mode.js"></script>
</body>
</html>