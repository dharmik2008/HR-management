<?php
/**
 * Download Payroll as Excel/CSV
 * Generates an Excel-compatible CSV file with employee payroll data
 */

require_once __DIR__ . '/bootstrap.php';
Auth::requireAdmin();

$empModel = new EmployeeModel($db);
$categoryModel = new ProjectCategoryModel($db);

// Get all employees (no pagination for export)
$employees = $empModel->getAllEmployees();

// Set headers for Excel download
$filename = 'payroll_' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

// Open output stream
$output = fopen('php://output', 'w');

// Add BOM for UTF-8 (Excel compatibility)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add header row
fputcsv($output, ['Name', 'Department', 'Monthly Salary', 'Annual Salary', 'Status']);

// Add data rows
foreach ($employees as $emp) {
    $name = $emp['Emp_firstName'] . ' ' . $emp['Emp_lastName'];
    $department = $emp['Category_name'] ?? 'N/A';
    $monthlySalary = $emp['Salary'] ?? 0;
    $annualSalary = ($monthlySalary * 12);
    $status = $emp['Status'] ?? 'N/A';
    
    fputcsv($output, [
        $name,
        $department,
        '₹' . number_format($monthlySalary, 2),
        '₹' . number_format($annualSalary, 2),
        $status
    ]);
}

fclose($output);
exit;

