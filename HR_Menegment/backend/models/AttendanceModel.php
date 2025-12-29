<?php
/**
 * Attendance Model
 */

class AttendanceModel {
    
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Mark attendance
     */
    public function markAttendance($empId, $date, $status, $checkinTime = null, $checkoutTime = null, $markedBy = null) {
        // Check if already exists
        $stmt = $this->db->prepare("SELECT * FROM Attendance WHERE Emp_id = ? AND Date = ?");
        $stmt->execute([$empId, $date]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Update existing
            $stmt = $this->db->prepare("UPDATE Attendance SET Status = ?, Checkin_time = ?, Checkout_time = ?, Marked_by = ? WHERE Emp_id = ? AND Date = ?");
            return $stmt->execute([$status, $checkinTime, $checkoutTime, $markedBy, $empId, $date]);
        } else {
            // Create new
            $stmt = $this->db->prepare("INSERT INTO Attendance (Emp_id, Date, Status, Checkin_time, Checkout_time, Marked_by) 
                                       VALUES (?, ?, ?, ?, ?, ?)");
            return $stmt->execute([$empId, $date, $status, $checkinTime, $checkoutTime, $markedBy]);
        }
    }
    
    /**
     * Get attendance by employee and date range
     */
    public function getAttendanceByEmployee($empId, $fromDate = null, $toDate = null, $limit = null, $offset = 0) {
        $query = "SELECT * FROM Attendance WHERE Emp_id = ?";
        $params = [$empId];
        
        if ($fromDate) {
            $query .= " AND Date >= ?";
            $params[] = $fromDate;
        }
        
        if ($toDate) {
            $query .= " AND Date <= ?";
            $params[] = $toDate;
        }
        
        $query .= " ORDER BY Date DESC";
        
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
     * Get this week attendance
     */
    public function getThisWeekAttendance($empId) {
        $mondayDate = date('Y-m-d', strtotime('monday this week'));
        $fridayDate = date('Y-m-d', strtotime('friday this week'));
        
        return $this->getAttendanceByEmployee($empId, $mondayDate, $fridayDate);
    }
    
    /**
     * Get attendance by date (all employees)
     */
    public function getAttendanceByDate($date) {
        $stmt = $this->db->prepare("SELECT a.*, e.Emp_firstName, e.Emp_lastName, e.Emp_email 
                                   FROM Attendance a 
                                   JOIN Employees e ON a.Emp_id = e.Emp_id 
                                   WHERE a.Date = ? 
                                   ORDER BY e.Emp_firstName");
        $stmt->execute([$date]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get attendance statistics
     */
    public function getAttendanceStats($empId, $months = 1) {
        $fromDate = date('Y-m-d', strtotime("-$months month"));
        $toDate = date('Y-m-d');
        
        $stmt = $this->db->prepare("SELECT 
                                   Status, COUNT(*) as count 
                                   FROM Attendance 
                                   WHERE Emp_id = ? AND Date BETWEEN ? AND ?
                                   GROUP BY Status");
        $stmt->execute([$empId, $fromDate, $toDate]);
        return $stmt->fetchAll();
    }

    /**
     * Get attendance summary (present/absent/wfh/leave) for a date range
     */
    public function getAttendanceSummaryByRange($empId, $startDate, $endDate) {
        $stmt = $this->db->prepare("SELECT Status, COUNT(*) as count 
                                   FROM Attendance 
                                   WHERE Emp_id = ? AND Date BETWEEN ? AND ?
                                   GROUP BY Status");
        $stmt->execute([$empId, $startDate, $endDate]);
        $rows = $stmt->fetchAll();
        $summary = ['present' => 0, 'absent' => 0, 'wfh' => 0, 'leave' => 0];
        foreach ($rows as $row) {
            $status = strtolower($row['Status'] ?? '');
            if ($status === 'present') {
                $summary['present'] = (int)$row['count'];
            } elseif ($status === 'absent') {
                $summary['absent'] = (int)$row['count'];
            } elseif ($status === 'wfh') {
                $summary['wfh'] = (int)$row['count'];
            } elseif ($status === 'leave') {
                $summary['leave'] = (int)$row['count'];
            }
        }
        return $summary;
    }

    /**
     * Get weekly attendance for an employee (default last 7 days)
     */
    public function getWeeklyAttendance($empId, $days = 7) {
        $startDate = date('Y-m-d', strtotime('-' . max(1, (int)$days - 1) . ' days'));
        $endDate = date('Y-m-d');
        return $this->getAttendanceByEmployee($empId, $startDate, $endDate);
    }

    /**
     * Attendance percentage over recent months (default 1 month)
     */
    public function getAttendancePercentage($empId, $months = 1) {
        $fromDate = date('Y-m-d', strtotime("-$months month"));
        $toDate = date('Y-m-d');

        $stmt = $this->db->prepare("SELECT 
                                        SUM(CASE WHEN Status = 'Present' THEN 1 ELSE 0 END) as present_count,
                                        COUNT(*) as total_count
                                    FROM Attendance
                                    WHERE Emp_id = ? AND Date BETWEEN ? AND ?");
        $stmt->execute([$empId, $fromDate, $toDate]);
        $row = $stmt->fetch();
        $total = (int)($row['total_count'] ?? 0);
        if ($total === 0) {
            return 0;
        }
        $present = (int)($row['present_count'] ?? 0);
        return ($present / $total) * 100;
    }

    /**
     * Attendance percentage for a specific date range
     */
    public function getAttendancePercentageByRange($empId, $startDate, $endDate) {
        $stmt = $this->db->prepare("SELECT 
                                        SUM(CASE WHEN Status = 'Present' THEN 1 ELSE 0 END) as present_count,
                                        COUNT(*) as total_count
                                    FROM Attendance
                                    WHERE Emp_id = ? AND Date BETWEEN ? AND ?");
        $stmt->execute([$empId, $startDate, $endDate]);
        $row = $stmt->fetch();
        $total = (int)($row['total_count'] ?? 0);
        if ($total === 0) {
            return 0;
        }
        $present = (int)($row['present_count'] ?? 0);
        return ($present / $total) * 100;
    }

    /**
     * Get all attendance (for dashboard/list) with optional filters
     */
    public function getAllAttendance($startDate = null, $endDate = null, $status = null, $limit = null, $offset = 0) {
        $query = "SELECT a.*, e.Emp_firstName, e.Emp_lastName, e.Emp_email 
                  FROM Attendance a
                  JOIN Employees e ON a.Emp_id = e.Emp_id
                  WHERE 1=1";
        $params = [];

        if ($startDate) {
            $query .= " AND a.Date >= ?";
            $params[] = $startDate;
        }

        if ($endDate) {
            $query .= " AND a.Date <= ?";
            $params[] = $endDate;
        }

        if ($status) {
            $query .= " AND a.Status = ?";
            $params[] = $status;
        }

        $query .= " ORDER BY a.Date DESC, a.Attendance_id DESC";

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
     * Get total attendance records (optionally filtered by date/status)
     */
    public function getTotalCount($startDate = null, $endDate = null, $status = null) {
        $query = "SELECT COUNT(*) as total 
                  FROM Attendance a
                  WHERE 1=1";
        $params = [];

        if ($startDate) {
            $query .= " AND a.Date >= ?";
            $params[] = $startDate;
        }

        if ($endDate) {
            $query .= " AND a.Date <= ?";
            $params[] = $endDate;
        }

        if ($status) {
            $query .= " AND a.Status = ?";
            $params[] = $status;
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }
}
?>