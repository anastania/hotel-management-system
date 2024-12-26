<?php

class AdminLogger {
    public static function log($action, $details = null) {
        $auth = Auth::getInstance();
        if (!$auth->check() || !$auth->user()->isAdmin()) {
            return false;
        }
        
        $db = Database::getInstance();
        return $db->insert('admin_logs', [
            'admin_id' => $auth->id(),
            'action' => $action,
            'details' => is_array($details) ? json_encode($details) : $details,
            'ip_address' => $_SERVER['REMOTE_ADDR']
        ]);
    }
    
    public static function getRecentLogs($limit = 50) {
        $db = Database::getInstance();
        return $db->fetchAll("
            SELECT al.*, u.email as admin_email 
            FROM admin_logs al 
            LEFT JOIN users u ON al.admin_id = u.id 
            ORDER BY al.created_at DESC 
            LIMIT ?
        ", [$limit]);
    }
    
    public static function getLogsByAdmin($adminId, $limit = 50) {
        $db = Database::getInstance();
        return $db->fetchAll("
            SELECT * FROM admin_logs 
            WHERE admin_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ", [$adminId, $limit]);
    }
    
    public static function getLogsByAction($action, $limit = 50) {
        $db = Database::getInstance();
        return $db->fetchAll("
            SELECT al.*, u.email as admin_email 
            FROM admin_logs al 
            LEFT JOIN users u ON al.admin_id = u.id 
            WHERE al.action = ? 
            ORDER BY al.created_at DESC 
            LIMIT ?
        ", [$action, $limit]);
    }
    
    public static function searchLogs($query, $limit = 50) {
        $db = Database::getInstance();
        $searchTerm = "%$query%";
        return $db->fetchAll("
            SELECT al.*, u.email as admin_email 
            FROM admin_logs al 
            LEFT JOIN users u ON al.admin_id = u.id 
            WHERE al.action LIKE ? 
                OR al.details LIKE ? 
                OR u.email LIKE ? 
            ORDER BY al.created_at DESC 
            LIMIT ?
        ", [$searchTerm, $searchTerm, $searchTerm, $limit]);
    }
}
