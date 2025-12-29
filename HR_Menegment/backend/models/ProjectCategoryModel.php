<?php
/**
 * Project Category (Department) Model
 */

class ProjectCategoryModel {
    
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get all categories
     */
    public function getAllCategories() {
        $stmt = $this->db->query("SELECT * FROM Project_Category ORDER BY Category_name");
        return $stmt->fetchAll();
    }
    
    /**
     * Get category by ID
     */
    public function getCategoryById($categoryId) {
        $stmt = $this->db->prepare("SELECT * FROM Project_Category WHERE ProjectCategory_id = ?");
        $stmt->execute([$categoryId]);
        return $stmt->fetch();
    }

    /**
     * Delete category by ID
     */
    public function deleteCategory($categoryId) {
        $stmt = $this->db->prepare("DELETE FROM Project_Category WHERE ProjectCategory_id = ?");
        return $stmt->execute([$categoryId]);
    }

    /**
     * Get number of employees assigned to a category
     */
    public function getEmployeeCount($categoryId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM Employees WHERE ProjectCategory_id = ?");
        $stmt->execute([$categoryId]);
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }
    
    /**
     * Get number of projects assigned to a category
     */
    public function getProjectCount($categoryId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM Projects WHERE ProjectCategory_id = ?");
        $stmt->execute([$categoryId]);
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }
    
    /**
     * Get projects assigned to a category
     */
    public function getProjectsByCategory($categoryId) {
        $stmt = $this->db->prepare("SELECT * FROM Projects WHERE ProjectCategory_id = ? ORDER BY Created_at DESC");
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Create category
     */
    public function createCategory($name, $description) {
        $stmt = $this->db->prepare("INSERT INTO Project_Category (Category_name, Description) VALUES (?, ?)");
        return $stmt->execute([$name, $description]);
    }
    
    /**
     * Update category
     */
    public function updateCategory($categoryId, $name, $description) {
        $stmt = $this->db->prepare("UPDATE Project_Category SET Category_name = ?, Description = ? WHERE ProjectCategory_id = ?");
        return $stmt->execute([$name, $description, $categoryId]);
    }

    /**
     * Get category with employee and project counts
     */
    public function getCategoryWithCount($categoryId) {
        $stmt = $this->db->prepare("SELECT pc.*, 
                                    (SELECT COUNT(*) FROM Employees e WHERE e.ProjectCategory_id = pc.ProjectCategory_id) as employee_count,
                                    (SELECT COUNT(*) FROM Projects p WHERE p.ProjectCategory_id = pc.ProjectCategory_id) as project_count
                                    FROM Project_Category pc
                                    WHERE pc.ProjectCategory_id = ?");
        $stmt->execute([$categoryId]);
        return $stmt->fetch();
    }
}
?>