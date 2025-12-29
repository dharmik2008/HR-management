<?php
require_once __DIR__ . '/config/config.php';

echo "<h2>Database Connection Test</h2>";

try {
    // Test connection
    $stmt = $db->prepare("SELECT 1");
    $stmt->execute();
    echo "<p style='color: green;'><strong>✓ Database connection successful!</strong></p>";
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>✗ Database connection failed:</strong> " . $e->getMessage() . "</p>";
    exit;
}

echo "<h3>HR Admins in Database:</h3>";
$stmt = $db->prepare("SELECT Hr_id, Hr_email, Hr_password FROM HR_Admins");
$stmt->execute();
$admins = $stmt->fetchAll();
echo "<pre>";
print_r($admins);
echo "</pre>";

echo "<h3>Employees in Database:</h3>";
$stmt = $db->prepare("SELECT Emp_id, Emp_email, Emp_password, Status FROM Employees LIMIT 2");
$stmt->execute();
$employees = $stmt->fetchAll();
echo "<pre>";
print_r($employees);
echo "</pre>";

echo "<h3>Test Password Verification:</h3>";
$testPassword = 'password';
$hash = '$2y$12$TUzB8.kW7z.kKqL6J0L5W.8KF5Y5xN5f5KXm5Z5mK5pL5mN5pQ5oM';
$result = password_verify($testPassword, $hash);
echo "Password 'password' with hash verifies: " . ($result ? "YES ✓" : "NO ✗");
echo "<br>";

echo "<h3>Test Login Manually:</h3>";
$stmt = $db->prepare("SELECT * FROM HR_Admins WHERE Hr_email = ?");
$stmt->execute(['admin@hrms.com']);
$user = $stmt->fetch();

if ($user) {
    echo "Found HR user: " . $user['Hr_email'] . "<br>";
    echo "Password match: " . (password_verify('password', $user['Hr_password']) ? "YES ✓" : "NO ✗");
} else {
    echo "No HR user found with email admin@hrms.com";
}
?>