<?php
define('BASE_URL', 'http://localhost/umkm-admin/');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'UMKM Admin'; ?> - Sistem Administrasi UMKM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.18/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }
        
        /* Sidebar */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            overflow-y: auto;
            color: white;
            z-index: 1000;
        }
        
        .sidebar-brand {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 30px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            text-align: center;
        }
        
        .sidebar-brand i {
            margin-right: 8px;
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li {
            margin-bottom: 10px;
        }
        
        .sidebar-menu a {
            display: block;
            padding: 12px 15px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding-left: 20px;
        }
        
        .sidebar-menu a i {
            margin-right: 10px;
            width: 18px;
        }
        
        /* Main Content */
        .main-wrapper {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .navbar-top {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar-brand-text {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-menu .dropdown {
            position: relative;
        }
        
        .user-menu .dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            color: #333;
            text-decoration: none;
            font-size: 14px;
        }
        
        .content-wrapper {
            flex: 1;
            padding: 30px;
        }
        
        /* Cards */
        .card {
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-radius: 10px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px 10px 0 0 !important;
            padding: 20px;
            font-weight: 600;
        }
        
        .stat-card {
            background: white;
            border-left: 4px solid #667eea;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .stat-card.success {
            border-left-color: #28a745;
        }
        
        .stat-card.danger {
            border-left-color: #dc3545;
        }
        
        .stat-card.warning {
            border-left-color: #ffc107;
        }
        
        .stat-card.info {
            border-left-color: #17a2b8;
        }
        
        .stat-card-value {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-top: 10px;
        }
        
        .stat-card-label {
            font-size: 13px;
            color: #666;
            font-weight: 500;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .stat-card-icon {
            float: right;
            font-size: 40px;
            color: rgba(102, 126, 234, 0.2);
        }
        
        /* Table */
        .table {
            border-collapse: collapse;
            margin-bottom: 0;
        }
        
        .table thead th {
            background: #f8f9fa;
            border-bottom: 2px solid #e0e0e0;
            font-weight: 600;
            color: #333;
            padding: 15px;
            font-size: 13px;
            text-transform: uppercase;
        }
        
        .table tbody td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: middle;
        }
        
        .table tbody tr:hover {
            background: #f9f9f9;
        }
        
        /* Badge */
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        /* Button */
        .btn {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            font-size: 14px;
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a3f99 100%);
            color: white;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }
        
        .btn-danger {
            background: #dc3545;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #333;
        }
        
        .btn-warning:hover {
            background: #e0a800;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .btn-info {
            background: #17a2b8;
            color: white;
        }
        
        .btn-info:hover {
            background: #138496;
        }
        
        /* Form */
        .form-control {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 10px 15px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 10px rgba(102, 126, 234, 0.1);
        }
        
        /* Modal */
        .modal-content {
            border: none;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }
        
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px 10px 0 0;
        }
        
        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }
        
        /* Alert */
        .alert {
            border: none;
            border-radius: 8px;
            border-left: 4px solid;
        }
        
        .alert-success {
            background: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        
        .alert-danger {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        
        .alert-warning {
            background: #fff3cd;
            border-left-color: #ffc107;
            color: #856404;
        }
        
        .alert-info {
            background: #d1ecf1;
            border-left-color: #17a2b8;
            color: #0c5460;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                min-height: auto;
                position: relative;
            }
            
            .main-wrapper {
                margin-left: 0;
            }
            
            .content-wrapper {
                padding: 15px;
            }
            
            .stat-card {
                margin-bottom: 15px;
            }
            
            .table {
                font-size: 12px;
            }
            
            .table thead th,
            .table tbody td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>