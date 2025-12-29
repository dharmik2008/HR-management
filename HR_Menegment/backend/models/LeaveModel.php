<?php
/**
 * Leave Model
 */

class LeaveModel {
    
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get all leaves for an employee
     */
    public function getLeavesByEmployee($empId, $limit = null, $offset = 0) {
        $query = "SELECT * FROM Leaves WHERE Emp_id = ? ORDER BY Requested_at DESC";
        $params = [$empId];
        
        if ($limit) {
            $query .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get leave by ID
     */
    public function getLeaveById($leaveId) {
        $stmt = $this->db->prepare("SELECT * FROM Leaves WHERE Leave_id = ?");
        $stmt->execute([$leaveId]);
        return $stmt->fetch();
    }
    
    /**
     * Get pending leaves (for HR)
     */
    public function getPendingLeaves($limit = null, $offset = 0) {
        $query = "SELECT l.*, e.Emp_firstName, e.Emp_lastName, e.Emp_email 
                  FROM Leaves l
                  JOIN Employees e ON l.Emp_id = e.Emp_id
                  WHERE l.Status = 'Pending'
                  ORDER BY l.Requested_at DESC";
        $params = [];
        
        if ($limit) {
            $query .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Apply for leave
     */
    public function applyLeave($empId, $leaveType, $startDate, $endDate, $days, $comment = null, $attachment = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO Leaves (Emp_id, Leave_type, Start_date, End_date, Days, Comment, Attachment)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $result = $stmt->execute([$empId, $leaveType, $startDate, $endDate, $days, $comment, $attachment]);
            
            if ($result) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Approve/Reject leave
     */
    public function updateLeaveStatus($leaveId, $status, $actionById) {
        try {
            $stmt = $this->db->prepare("
                UPDATE Leaves 
                SET Status = ?, Action_by = ?
                WHERE Leave_id = ?
            ");
            return $stmt->execute([$status, $actionById, $leaveId]);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get leave balance for employee
     */
    public function getLeaveBalance($empId) {
        // Total annual leave (usually 12 days)
        $totalLeave = 12;
        
        // Calculate used leaves
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(Days), 0) as usedDays 
            FROM Leaves 
            WHERE Emp_id = ? AND Status = 'Approved' AND YEAR(Start_date) = YEAR(CURDATE())
        ");
        $stmt->execute([$empId]);
        $result = $stmt->fetch();
        $usedDays = $result['usedDays'] ?? 0;
        
        return [
            'total' => $totalLeave,
            'used' => intval($usedDays),
            'remaining' => $totalLeave - intval($usedDays)
        ];
    }
    
    /**
     * Get all leaves (for HR)
     */
    public function getAllLeaves($status = null, $limit = null, $offset = 0) {
        $query = "SELECT l.*, e.Emp_firstName, e.Emp_lastName, e.Emp_email 
                  FROM Leaves l
                  JOIN Employees e ON l.Emp_id = e.Emp_id";
        $params = [];
        
        if ($status) {
            $query .= " WHERE l.Status = ?";
            $params[] = $status;
        }
        
        $query .= " ORDER BY l.Requested_at DESC";
        
        if ($limit) {
            $query .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get total pending leaves count
     */
    public function getPendingLeavesCount() {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM Leaves WHERE Status = 'Pending'");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

}
?>