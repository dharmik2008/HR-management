<?php
/**
 * Notification Management Model
 */

class NotificationModel {
    
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Create notification
     */
    public function createNotification($targetType, $targetId, $title, $message) {
        $stmt = $this->db->prepare("INSERT INTO Notifications 
            (Target_type, Target_id, Title, Message, Status) 
            VALUES (?, ?, ?, ?, ?)");
        
        return $stmt->execute([
            $targetType,
            $targetId,
            $title,
            $message,
            'Unread'
        ]);
    }
    
    /**
     * Get notifications for user
     */
    public function getNotifications($targetType, $targetId, $status = null) {
        $query = "SELECT * FROM Notifications WHERE Target_type = ? AND Target_id = ?";
        $params = [$targetType, $targetId];
        
        if ($status) {
            $query .= " AND Status = ?";
            $params[] = $status;
        }
        
        $query .= " ORDER BY Created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Mark as read
     */
    public function markAsRead($notificationId) {
        $stmt = $this->db->prepare("UPDATE Notifications SET Status = 'Read' WHERE Notification_id = ?");
        return $stmt->execute([$notificationId]);
    }

    /**
     * Mark as unread
     */
    public function markAsUnread($notificationId) {
        $stmt = $this->db->prepare("UPDATE Notifications SET Status = 'Unread' WHERE Notification_id = ?");
        return $stmt->execute([$notificationId]);
    }
    
    /**
     * Mark all as read
     */
    public function markAllAsRead($targetType, $targetId) {
        $stmt = $this->db->prepare("UPDATE Notifications SET Status = 'Read' WHERE Target_type = ? AND Target_id = ?");
        return $stmt->execute([$targetType, $targetId]);
    }
    
    /**
     * Get unread count
     */
    public function getUnreadCount($targetType, $targetId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM Notifications 
                                   WHERE Target_type = ? AND Target_id = ? AND Status = 'Unread'");
        $stmt->execute([$targetType, $targetId]);
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    /**
     * Delete notification
     */
    public function deleteNotification($notificationId) {
        $stmt = $this->db->prepare("DELETE FROM Notifications WHERE Notification_id = ?");
        return $stmt->execute([$notificationId]);
    }
}
?>