<?php
/**
 * Document Management Model
 */

class DocumentModel {
    
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Upload document
     */
    public function uploadDocument($empId, $fileName, $fileType, $filePath, $uploadedBy = null) {
        $stmt = $this->db->prepare("INSERT INTO Documents 
            (Emp_id, File_name, File_type, File_path, Uploaded_by) 
            VALUES (?, ?, ?, ?, ?)");
        
        return $stmt->execute([
            $empId,
            $fileName,
            $fileType,
            $filePath,
            $uploadedBy
        ]);
    }
    
    /**
     * Get documents by employee
     */
    public function getDocumentsByEmployee($empId, $limit = null, $offset = null) {
        $sql = "SELECT d.*, 
                       e.Emp_firstName, e.Emp_lastName, e.Emp_email,
                       CONCAT(e.Emp_firstName, ' ', e.Emp_lastName) as employee_name,
                       COALESCE(
                           CONCAT(emp_uploader.Emp_firstName, ' ', emp_uploader.Emp_lastName),
                           CONCAT(hr_uploader.Hr_firstName, ' ', hr_uploader.Hr_lastName),
                           'Unknown'
                       ) as uploader_name,
                       CASE 
                           WHEN emp_uploader.Emp_id IS NOT NULL THEN 'Employee'
                           WHEN hr_uploader.Hr_id IS NOT NULL THEN 'Admin'
                           ELSE 'Unknown'
                       END as uploader_type
                FROM Documents d 
                LEFT JOIN Employees e ON d.Emp_id = e.Emp_id
                LEFT JOIN Employees emp_uploader ON d.Uploaded_by = emp_uploader.Emp_id
                LEFT JOIN hr_admins hr_uploader ON d.Uploaded_by = hr_uploader.Hr_id
                WHERE d.Emp_id = ? 
                ORDER BY d.Uploaded_at DESC";
        
        if ($limit !== null && $offset !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$empId, $limit, $offset]);
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$empId]);
        }
        return $stmt->fetchAll();
    }
    
    /**
     * Get all documents
     */
    public function getAllDocuments($limit = null, $offset = null) {
        $sql = "SELECT d.*, 
                       e.Emp_firstName, e.Emp_lastName, e.Emp_email,
                       CONCAT(e.Emp_firstName, ' ', e.Emp_lastName) as employee_name,
                       COALESCE(
                           CONCAT(emp_uploader.Emp_firstName, ' ', emp_uploader.Emp_lastName),
                           CONCAT(hr_uploader.Hr_firstName, ' ', hr_uploader.Hr_lastName),
                           'Unknown'
                       ) as uploader_name,
                       CASE 
                           WHEN emp_uploader.Emp_id IS NOT NULL THEN 'Employee'
                           WHEN hr_uploader.Hr_id IS NOT NULL THEN 'Admin'
                           ELSE 'Unknown'
                       END as uploader_type
                FROM Documents d 
                LEFT JOIN Employees e ON d.Emp_id = e.Emp_id
                LEFT JOIN Employees emp_uploader ON d.Uploaded_by = emp_uploader.Emp_id
                LEFT JOIN hr_admins hr_uploader ON d.Uploaded_by = hr_uploader.Hr_id
                ORDER BY d.Uploaded_at DESC";
        
        if ($limit !== null && $offset !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit, $offset]);
        } else {
            $stmt = $this->db->query($sql);
        }
        return $stmt->fetchAll();
    }
    
    /**
     * Get document by ID
     */
    public function getDocumentById($docId) {
        $stmt = $this->db->prepare("SELECT * FROM Documents WHERE Doc_id = ?");
        $stmt->execute([$docId]);
        return $stmt->fetch();
    }
    
    /**
     * Delete document
     */
    public function deleteDocument($docId) {
        $stmt = $this->db->prepare("DELETE FROM Documents WHERE Doc_id = ?");
        return $stmt->execute([$docId]);
    }
}
?>