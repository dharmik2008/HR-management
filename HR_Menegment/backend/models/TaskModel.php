<?php
/**
 * Task Management Model
 */

class TaskModel {
    
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Create task
     */
    public function createTask($title, $description, $assignedBy, $assignedTo, $priority, $dueDate, $projectId = null) {
        $stmt = $this->db->prepare("INSERT INTO Tasks 
            (Title, Description, Assigned_by, Assigned_to, Project_id, Priority, Status, Due_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        return $stmt->execute([
            $title,
            $description,
            $assignedBy,
            $assignedTo,
            $projectId,
            $priority ?: 'Medium',
            'Pending',
            $dueDate
        ]);
    }
    
    /**
     * Get tasks for employee
     */
    public function getTasksByEmployee($empId, $status = null, $priority = null, $limit = null, $offset = 0) {
        $query = "SELECT t.*, hr.Hr_code as assigned_by_name, 
                         p.Project_name, p.Project_id
                  FROM Tasks t 
                  LEFT JOIN HR_Admins hr ON t.Assigned_by = hr.Hr_id 
                  LEFT JOIN Projects p ON t.Project_id = p.Project_id
                  WHERE t.Assigned_to = ?";
        $params = [$empId];
        
        if ($status) {
            $query .= " AND t.Status = ?";
            $params[] = $status;
        }
        
        if ($priority) {
            $query .= " AND t.Priority = ?";
            $params[] = $priority;
        }
        
        $query .= " ORDER BY t.Due_date ASC, t.Priority DESC";

        if ($limit !== null) {
            $query .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all tasks
     */
    public function getAllTasks($status = null, $assignedTo = null, $limit = null, $offset = 0) {
        $query = "SELECT t.*, 
                         e.Emp_firstName, e.Emp_lastName, e.Emp_email, 
                         CONCAT(COALESCE(e.Emp_firstName,''), ' ', COALESCE(e.Emp_lastName,'')) AS employee_name,
                         hr.Hr_code,
                         p.Project_name, p.Project_id
                  FROM Tasks t 
                  LEFT JOIN Employees e ON t.Assigned_to = e.Emp_id 
                  LEFT JOIN HR_Admins hr ON t.Assigned_by = hr.Hr_id 
                  LEFT JOIN Projects p ON t.Project_id = p.Project_id
                  WHERE 1=1";
        $params = [];
        
        if ($status) {
            $query .= " AND t.Status = ?";
            $params[] = $status;
        }
        
        if ($assignedTo) {
            $query .= " AND t.Assigned_to = ?";
            $params[] = $assignedTo;
        }
        
        $query .= " ORDER BY t.Due_date ASC";

        if ($limit !== null) {
            $query .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Update task
     */
    public function updateTask($taskId, $title, $description, $priority, $dueDate, $status, $projectId = null) {
        $stmt = $this->db->prepare("UPDATE Tasks 
                                    SET Title = ?, Description = ?, Priority = ?, Due_date = ?, Status = ?, Project_id = ?
                                    WHERE Task_id = ?");
        return $stmt->execute([$title, $description, $priority, $dueDate, $status, $projectId, $taskId]);
    }

    /**
     * Delete task
     */
    public function deleteTask($taskId) {
        $stmt = $this->db->prepare("DELETE FROM Tasks WHERE Task_id = ?");
        return $stmt->execute([$taskId]);
    }
    
    /**
     * Update task status
     */
    public function updateTaskStatus($taskId, $status) {
        $stmt = $this->db->prepare("UPDATE Tasks SET Status = ? WHERE Task_id = ?");
        return $stmt->execute([$status, $taskId]);
    }
    
    /**
     * Get task by ID
     */
    public function getTaskById($taskId) {
        $stmt = $this->db->prepare("SELECT t.*, e.Emp_firstName, e.Emp_lastName, p.Project_name, p.Project_id
                                   FROM Tasks t 
                                   LEFT JOIN Employees e ON t.Assigned_to = e.Emp_id 
                                   LEFT JOIN Projects p ON t.Project_id = p.Project_id
                                   WHERE t.Task_id = ?");
        $stmt->execute([$taskId]);
        return $stmt->fetch();
    }

    /**
     * Get total tasks count
     */
    public function getTotalCount() {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM Tasks");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
}
?>