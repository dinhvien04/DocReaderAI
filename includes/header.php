<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= $pageTitle ?? 'DocReader AI Studio' ?></title>
    <meta name="description" content="Chuyển đổi văn bản thành giọng nói với AI - DocReader AI Studio">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/images/vie.png">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Toastify CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    
    <!-- Custom Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#667eea',
                        secondary: '#764ba2'
                    }
                }
            }
        }
    </script>
    
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .spinner {
            border: 2px solid #f3f3f3;
            border-top: 2px solid #667eea;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            animation: spin 1s linear infinite;
            display: inline-block;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Navigation -->
    <nav class="gradient-bg shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <!-- Logo -->
                <a href="<?= BASE_URL ?>/" class="flex items-center space-x-2">
                    <div class="text-white text-2xl font-bold">
                        🎙️ DocReader AI Studio
                    </div>
                </a>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-6">
                    <?php if (isLoggedIn()): ?>
                        <span class="text-white text-sm">
                            👤 <?= htmlspecialchars($_SESSION['user']['email']) ?>
                        </span>
                        <a href="<?= BASE_URL ?>/dashboard" class="text-white hover:text-gray-200 transition">
                            Dashboard
                        </a>
                        <?php if (isAdmin()): ?>
                            <a href="<?= BASE_URL ?>/admin" class="text-white hover:text-gray-200 transition">
                                Admin
                            </a>
                        <?php endif; ?>
                        <button onclick="logout()" class="bg-white text-purple-600 px-4 py-2 rounded-lg hover:bg-gray-100 transition font-medium">
                            Đăng xuất
                        </button>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/login" class="text-white hover:text-gray-200 transition">
                            Đăng nhập
                        </a>
                        <a href="<?= BASE_URL ?>/register" class="bg-white text-purple-600 px-6 py-2 rounded-lg hover:bg-gray-100 transition font-medium">
                            Đăng ký
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" class="md:hidden text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Mobile Menu -->
            <div id="mobile-menu" class="hidden md:hidden pb-4">
                <?php if (isLoggedIn()): ?>
                    <div class="flex flex-col space-y-2">
                        <span class="text-white text-sm py-2">
                            👤 <?= htmlspecialchars($_SESSION['user']['email']) ?>
                        </span>
                        <a href="<?= BASE_URL ?>/dashboard" class="text-white hover:text-gray-200 py-2">
                            Dashboard
                        </a>
                        <?php if (isAdmin()): ?>
                            <a href="<?= BASE_URL ?>/admin" class="text-white hover:text-gray-200 py-2">
                                Admin
                            </a>
                        <?php endif; ?>
                        <button onclick="logout()" class="bg-white text-purple-600 px-4 py-2 rounded-lg hover:bg-gray-100 transition text-left">
                            Đăng xuất
                        </button>
                    </div>
                <?php else: ?>
                    <div class="flex flex-col space-y-2">
                        <a href="<?= BASE_URL ?>/login" class="text-white hover:text-gray-200 py-2">
                            Đăng nhập
                        </a>
                        <a href="<?= BASE_URL ?>/register" class="bg-white text-purple-600 px-4 py-2 rounded-lg hover:bg-gray-100 transition text-center">
                            Đăng ký
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="flex-grow">