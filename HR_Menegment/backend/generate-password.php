<?php
$password = 'password';
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
echo "Password: " . $password . "<br>";
echo "Hash: " . $hash . "<br>";
echo "<br>";
echo "Copy the hash above and run this SQL:<br>";
echo "<pre>";
echo "UPDATE HR_Admins SET Hr_password = '" . $hash . "' WHERE Hr_email = 'admin@hrms.com';\n";
echo "UPDATE Employees SET Emp_password = '" . $hash . "' WHERE Emp_code LIKE 'EMP%';\n";
echo "</pre>";
?>