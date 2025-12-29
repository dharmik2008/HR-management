<?php
/**
 * Project Management Model
 */

class ProjectModel {
    
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Create a new project
     */
    public function createProject($name, $categoryId, $description, $startDate, $endDate, $status = 'Active') {
        $stmt = $this->db->prepare("INSERT INTO Projects (Project_name, ProjectCategory_id, Description, Start_date, End_date, Status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $categoryId, $description, $startDate, $endDate, $status]);
        return $this->db->lastInsertId();
    }

    /**
     * Assign employee to project (ignore duplicates)
     */
    public function assignEmployee($projectId, $empId) {
        // Check if already assigned
        $check = $this->db->prepare("SELECT 1 FROM Project_Assign WHERE Project_id = ? AND Emp_id = ?");
        $check->execute([$projectId, $empId]);
        if ($check->fetch()) {
            return false;
        }
        $stmt = $this->db->prepare("INSERT INTO Project_Assign (Project_id, Emp_id) VALUES (?, ?)");
        return $stmt->execute([$projectId, $empId]);
    }

    /**
     * Remove employee from project
     */
    public function removeEmployee($projectId, $empId) {
        $stmt = $this->db->prepare("DELETE FROM Project_Assign WHERE Project_id = ? AND Emp_id = ?");
        return $stmt->execute([$projectId, $empId]);
    }

    /**
     * Count all projects
     */
    public function countProjects() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM Projects");
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    /**
     * Get all projects
     */
    public function getAllProjects($limit = null, $offset = 0) {
        $query = "SELECT p.*, pc.Category_name 
                  FROM Projects p 
                  LEFT JOIN Project_Category pc ON p.ProjectCategory_id = pc.ProjectCategory_id 
                  ORDER BY p.Created_at DESC";
        if ($limit !== null) {
            $query .= " LIMIT ? OFFSET ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$limit, $offset]);
            return $stmt->fetchAll();
        }
        $stmt = $this->db->query($query);
        return $stmt->fetchAll();
    }
    
    /**
     * Get projects by employee
     */
    public function getProjectsByEmployee($empId) {
        $stmt = $this->db->prepare("SELECT p.*, pc.Category_name 
                                   FROM Projects p 
                                   LEFT JOIN Project_Category pc ON p.ProjectCategory_id = pc.ProjectCategory_id 
                                   JOIN Project_Assign pa ON p.Project_id = pa.Project_id 
                                   WHERE pa.Emp_id = ? 
                                   ORDER BY p.Created_at DESC");
        $stmt->execute([$empId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Assign project to employee
     */
    public function assignProjectToEmployee($projectId, $empId) {
        $stmt = $this->db->prepare("INSERT INTO Project_Assign (Project_id, Emp_id) VALUES (?, ?)");
        return $stmt->execute([$projectId, $empId]);
    }
    
    /**
     * Get project by ID
     */
    public function getProjectById($projectId) {
        $stmt = $this->db->prepare("SELECT p.*, pc.Category_name 
                                   FROM Projects p 
                                   LEFT JOIN Project_Category pc ON p.ProjectCategory_id = pc.ProjectCategory_id 
                                   WHERE p.Project_id = ?");
        $stmt->execute([$projectId]);
        return $stmt->fetch();
    }
    
    /**
     * Get employees assigned to project
     */
    public function getProjectEmployees($projectId) {
        $stmt = $this->db->prepare("SELECT e.*, pa.Assigned_at 
                                   FROM Employees e 
                                   JOIN Project_Assign pa ON e.Emp_id = pa.Emp_id 
                                   WHERE pa.Project_id = ?");
        $stmt->execute([$projectId]);
        return $stmt->fetchAll();
    }
}
?>