<?php

/** custom taskhub config */

return [

    /*
    |--------------------------------------------------------------------------
    | Project status labels
    |--------------------------------------------------------------------------
    */

    'project_status_labels' => [
        'completed' => "success",
        "onhold" => "warning",
        "ongoing" => "info",
        "started" => "primary",
        "cancelled" => "danger"
    ],

    'task_status_labels' => [
        'completed' => "success",
        "onhold" => "warning",
        "started" => "primary",
        "cancelled" => "danger",
        "ongoing" => "info"
    ],

    'role_labels' => [
        'admin' => "info",
        "Super Admin" => "danger",
        "HR" => "primary",
        "member" => "warning",
        'default' => "dark"
    ],

    'priority_labels' => [
        'low' => "success",
        "high" => "danger",
        "medium" => "warning"
    ],

    'permissions' => [
        'Projects' =>  array('create_projects', 'manage_projects', 'edit_projects', 'delete_projects'),
        'Tasks' =>  array('create_tasks', 'manage_tasks', 'edit_tasks', 'delete_tasks'),
        'Users' =>  array('create_users', 'manage_users', 'edit_users', 'delete_users'),
        'Clients' =>  array('create_clients', 'manage_clients', 'edit_clients', 'delete_clients'),
        'Workspaces' =>  array('create_workspaces', 'manage_workspaces', 'edit_workspaces', 'delete_workspaces'),
        'Contracts' =>  array('create_contracts', 'manage_contracts', 'edit_contracts', 'delete_contracts'),
        'Timesheet' =>  array('create_timesheet', 'manage_timesheet', 'delete_timesheet'),
        'Activity Log' =>  array('manage_activity_log', 'delete_activity_log'),
        'Estimates/Invoices' =>  array('create_estimates_invoices', 'manage_estimates_invoices', 'edit_estimates_invoices', 'delete_estimates_invoices'),
        'Expenses' =>  array('create_expenses', 'manage_expenses', 'edit_expenses', 'delete_expenses'),
        'Milestones' =>  array('create_milestones', 'manage_milestones', 'edit_milestones', 'delete_milestones'),
        'System Notifications' =>  array('manage_system_notifications', 'delete_system_notifications')
    ],

    //Modules
    'modules' => [
        'tasks' => ['icon' => 'bx bx-task', 'description' => 'Manage tasks and assignments efficiently'],
        'notes' => ['icon' => 'bx bx-note', 'description' => 'Take and organize notes for better productivity'],
        'chat' => ['icon' => 'bx bx-chat', 'description' => 'Communicate with team members in real-time'],
        'contracts' => ['icon' => 'bx bx-news', 'description' => 'Manage contracts and agreements with clients'],
        'finance' => ['icon' => 'bx bx-dollar', 'description' => 'Create and Manange Expenses Payments and Invoice Estimates'],
    ],

];
