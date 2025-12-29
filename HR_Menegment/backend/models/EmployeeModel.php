<?php
/**
 * Employee Model
 */

class EmployeeModel {

    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get all employees
     */
    public function getAllEmployees($limit = null, $offset = 0) {
        $query = "SELECT e.*, p.Category_name FROM Employees e 
                  LEFT JOIN Project_Category p ON e.ProjectCategory_id = p.ProjectCategory_id 
                  ORDER BY e.Created_at DESC";

        if ($limit !== null) {
            $query .= " LIMIT ? OFFSET ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$limit, $offset]);
        } else {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
        }

        return $stmt->fetchAll();
    }

    /**
     * Get employee by ID
     */
    public function getEmployeeById($empId) {
        $stmt = $this->db->prepare("SELECT e.*, p.Category_name FROM Employees e 
                                   LEFT JOIN Project_Category p ON e.ProjectCategory_id = p.ProjectCategory_id 
                                   WHERE e.Emp_id = ?");
        $stmt->execute([$empId]);
        return $stmt->fetch();
    }

    /**
     * Get employee by email
     */
    public function getEmployeeByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM Employees WHERE Emp_email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /**
     * Get total employee count
     */
    public function getTotalCount() {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM Employees");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Get active employee count
     */
    public function getActiveCount() {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM Employees WHERE Status = 'Active'");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Create employee (password hashed)
     */
    public function createEmployee($empCode, $firstName, $lastName, $email, $password, $phone = null, $dob = null, $gender = null, $joiningDate = null, $categoryId = null, $salary = null, $address = null, $profilePic = null) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $this->db->prepare("
                INSERT INTO Employees 
                (Emp_code, Emp_firstName, Emp_lastName, Emp_email, Emp_password, Emp_phone, Emp_dob, Emp_gender, Joining_date, ProjectCategory_id, Salary, Address, Profile_pic, Status, Created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active', NOW())
            ");

            $result = $stmt->execute([
                $empCode, $firstName, $lastName, $email, $hashedPassword, $phone, $dob,
                $gender, $joiningDate, $categoryId, $salary, $address, $profilePic
            ]);

            if ($result) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Update employee
     */
    public function updateEmployee($empId, $firstName, $lastName, $phone = null, $dob = null, $gender = null, $categoryId = null, $address = null, $profilePic = null, $salary = null) {
        try {
            $query = "UPDATE Employees SET Emp_firstName = ?, Emp_lastName = ?";
            $params = [$firstName, $lastName];

            if ($phone !== null) {
                $query .= ", Emp_phone = ?";
                $params[] = $phone;
            }
            if ($dob !== null) {
                $query .= ", Emp_dob = ?";
                $params[] = $dob;
            }
            if ($gender !== null) {
                $query .= ", Emp_gender = ?";
                $params[] = $gender;
            }
            if ($categoryId !== null) {
                $query .= ", ProjectCategory_id = ?";
                $params[] = $categoryId;
            }
            if ($address !== null) {
                $query .= ", Address = ?";
                $params[] = $address;
            }
            if ($profilePic !== null) {
                $query .= ", Profile_pic = ?";
                $params[] = $profilePic;
            }
            if ($salary !== null) {
                $query .= ", Salary = ?";
                $params[] = $salary;
            }

            $query .= " WHERE Emp_id = ?";
            $params[] = $empId;

            $stmt = $this->db->prepare($query);
            return $stmt->execute($params);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Update salary
     */
    public function updateSalary($empId, $salary) {
        try {
            $stmt = $this->db->prepare("UPDATE Employees SET Salary = ? WHERE Emp_id = ?");
            return $stmt->execute([$salary, $empId]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Update status
     */
    public function updateStatus($empId, $status) {
        try {
            $stmt = $this->db->prepare("UPDATE Employees SET Status = ? WHERE Emp_id = ?");
            return $stmt->execute([$status, $empId]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Update email (admin only)
     */
    public function updateEmail($empId, $email) {
        try {
            $stmt = $this->db->prepare("UPDATE Employees SET Emp_email = ? WHERE Emp_id = ?");
            return $stmt->execute([$email, $empId]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Search employees
     */
    public function searchEmployees($search, $limit = null) {
        $query = "SELECT e.*, p.Category_name FROM Employees e 
                  LEFT JOIN Project_Category p ON e.ProjectCategory_id = p.ProjectCategory_id
                  WHERE e.Emp_firstName LIKE ? OR e.Emp_lastName LIKE ? OR e.Emp_email LIKE ?
                  ORDER BY e.Emp_firstName";

        $searchTerm = '%' . $search . '%';
        $params = [$searchTerm, $searchTerm, $searchTerm];

        if ($limit) {
            $query .= " LIMIT ?";
            $params[] = $limit;
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Delete employee and related data (hard delete).
     * If $hardDelete is false, performs a soft delete by marking status 'Inactive'.
     *
     * Returns true on success, false on failure.
     */
    public function deleteEmployee($empId, $hardDelete = true) {
        if (!$empId) {
            return false;
        }

        // Soft delete behaviour
        if (!$hardDelete) {
            return $this->updateStatus($empId, 'Inactive');
        }

        try {
            $this->db->beginTransaction();

            // Fetch employee to remove profile pic
            $stmt = $this->db->prepare("SELECT Profile_pic FROM Employees WHERE Emp_id = ?");
            $stmt->execute([$empId]);
            $emp = $stmt->fetch();

            if (!empty($emp['Profile_pic'])) {
                $profilePath = __DIR__ . '/../../' . ltrim($emp['Profile_pic'], '/\\');
                if (is_file($profilePath)) {
                    @unlink($profilePath);
                }
                if (defined('UPLOADS_PROFILES')) {
                    $maybe = rtrim(UPLOADS_PROFILES, '/\\') . '/' . basename($emp['Profile_pic']);
                    if (is_file($maybe)) {
                        @unlink($maybe);
                    }
                }
            }

            // Delete documents and files
            $stmt = $this->db->prepare("SELECT Doc_id, File_path FROM Documents WHERE Emp_id = ?");
            $stmt->execute([$empId]);
            $docs = $stmt->fetchAll();
            foreach ($docs as $doc) {
                if (!empty($doc['File_path'])) {
                    $docPath = __DIR__ . '/../../' . ltrim($doc['File_path'], '/\\');
                    if (is_file($docPath)) {
                        @unlink($docPath);
                    }
                    if (defined('UPLOADS_DOCUMENTS')) {
                        $maybe = rtrim(UPLOADS_DOCUMENTS, '/\\') . '/' . basename($doc['File_path']);
                        if (is_file($maybe)) {
                            @unlink($maybe);
                        }
                    }
                }
            }
            $stmt = $this->db->prepare("DELETE FROM Documents WHERE Emp_id = ?");
            $stmt->execute([$empId]);

            // Delete attendance
            $stmt = $this->db->prepare("DELETE FROM Attendance WHERE Emp_id = ?");
            $stmt->execute([$empId]);

            // Delete leaves
            $stmt = $this->db->prepare("DELETE FROM Leaves WHERE Emp_id = ?");
            $stmt->execute([$empId]);

            // Delete tasks assigned to this employee
            $stmt = $this->db->prepare("DELETE FROM Tasks WHERE Assigned_to = ?");
            $stmt->execute([$empId]);

            // Delete project allocations for this employee
            $stmt = $this->db->prepare("DELETE FROM Project_Assign WHERE Emp_id = ?");
            $stmt->execute([$empId]);

            // Delete notifications targeting this employee (assumes Target_type = 'employee')
            $stmt = $this->db->prepare("DELETE FROM Notifications WHERE Target_type = 'employee' AND Target_id = ?");
            $stmt->execute([$empId]);

            // Finally, delete employee row
            $stmt = $this->db->prepare("DELETE FROM Employees WHERE Emp_id = ?");
            $stmt->execute([$empId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

}
?>