<?php
// Role-based permissions configuration

// Define role constants
define('ROLE_ADMIN', 'admin');
define('ROLE_STAFF', 'staff');
define('ROLE_CASHIER', 'cashier');

// Define page access permissions
$page_permissions = [
    'index.php' => [ROLE_ADMIN, ROLE_STAFF, ROLE_CASHIER],
    'inventory.php' => [ROLE_ADMIN, ROLE_STAFF],
    'manage_products.php' => [ROLE_ADMIN],
    'queue.php' => [ROLE_ADMIN, ROLE_STAFF],
    'reports.php' => [ROLE_ADMIN, ROLE_STAFF],
    'pos.php' => [ROLE_ADMIN, ROLE_CASHIER],
    'manage_users.php' => [ROLE_ADMIN],
    'settings.php' => [ROLE_ADMIN]
];

// Define feature permissions
$feature_permissions = [
    // Product Management
    'add_product' => [ROLE_ADMIN],
    'edit_product' => [ROLE_ADMIN],
    'delete_product' => [ROLE_ADMIN],
    'view_products' => [ROLE_ADMIN, ROLE_STAFF, ROLE_CASHIER],
    
    // Inventory Management
    'manage_inventory' => [ROLE_ADMIN, ROLE_STAFF],
    'view_inventory' => [ROLE_ADMIN, ROLE_STAFF, ROLE_CASHIER],
    'update_stock' => [ROLE_ADMIN, ROLE_STAFF],
    
    // User Management
    'manage_users' => [ROLE_ADMIN],
    'view_users' => [ROLE_ADMIN],
    
    // Reports
    'view_all_reports' => [ROLE_ADMIN],
    'view_sales_reports' => [ROLE_ADMIN, ROLE_STAFF],
    'view_inventory_reports' => [ROLE_ADMIN, ROLE_STAFF],
    'view_basic_reports' => [ROLE_ADMIN, ROLE_STAFF, ROLE_CASHIER],
    
    // POS Features
    'process_payments' => [ROLE_ADMIN, ROLE_CASHIER],
    'issue_refunds' => [ROLE_ADMIN, ROLE_CASHIER],
    'view_transactions' => [ROLE_ADMIN, ROLE_CASHIER],
    
    // Queue Management
    'manage_queue' => [ROLE_ADMIN, ROLE_STAFF],
    'view_queue' => [ROLE_ADMIN, ROLE_STAFF, ROLE_CASHIER]
];

/**
 * Check if user has access to a specific page
 * @param string $page The page to check access for
 * @param string $user_role The user's role
 * @return bool True if user has access, false otherwise
 */
function has_page_access($page, $user_role) {
    global $page_permissions;
    return isset($page_permissions[$page]) && in_array($user_role, $page_permissions[$page]);
}

/**
 * Check if user has access to a specific feature
 * @param string $feature The feature to check access for
 * @param string $user_role The user's role
 * @return bool True if user has access, false otherwise
 */
function has_feature_access($feature, $user_role) {
    global $feature_permissions;
    return isset($feature_permissions[$feature]) && in_array($user_role, $feature_permissions[$feature]);
}

/**
 * Get all features available to a specific role
 * @param string $user_role The user's role
 * @return array List of features available to the role
 */
function get_role_features($user_role) {
    global $feature_permissions;
    $features = [];
    foreach ($feature_permissions as $feature => $roles) {
        if (in_array($user_role, $roles)) {
            $features[] = $feature;
        }
    }
    return $features;
}

/**
 * Redirect unauthorized users
 * @param string $user_role The user's role
 * @param string $page The page being accessed
 */
function check_page_access($user_role, $page) {
    if (!has_page_access($page, $user_role)) {
        header('Location: access_denied.php');
        exit();
    }
}
?> 