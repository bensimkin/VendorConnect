<?php

use Illuminate\Support\Facades\Route;

// API-only application - all frontend routes removed
// New frontend will be built separately and consume the API

Route::get("/", function () {
    return response()->json([
        "message" => "VendorConnect API",
        "version" => "1.0",
        "status" => "API-only mode",
        "documentation" => "/api/v1"
    ]);
});

Route::get("/api", function () {
    return response()->json([
        "message" => "VendorConnect API v1",
        "endpoints" => [
            "auth" => "/api/v1/auth",
            "dashboard" => "/api/v1/dashboard", 
            "tasks" => "/api/v1/tasks",
            "users" => "/api/v1/users",
            "clients" => "/api/v1/clients",
            "projects" => "/api/v1/projects"
        ]
    ]);
});
