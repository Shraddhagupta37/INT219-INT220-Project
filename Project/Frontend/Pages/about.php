<?php
session_start();
require_once '../../Backend/PHP/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/Frontend/src/tailwind.css" rel="stylesheet">
    <title>About - CodeLens</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="flex flex-col min-h-screen bg-gray-50">
    <!-- Copy the same header from index.php with updated amber colors -->
    <header class="bg-white shadow-md">
        <nav class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-8">
                    <a href="../index.php" class="text-2xl font-bold text-amber-600">CodeLens</a>
                    <a href="dashboard.php" class="text-gray-700 hover:text-amber-600">Dashboard</a>
                    <a href="mock-test.php" class="text-gray-700 hover:text-amber-600">Mock Test</a>
                    <a href="about.php" class="text-gray-700 hover:text-amber-600">About</a>
                </div>
            </div>
        </nav>
    </header>
    
    <div class="flex flex-1 flex-col items-center justify-center text-center px-6 py-24 bg-gradient-to-br from-amber-500 via-amber-600 to-amber-900 w-full">
        <h1 class="text-6xl md:text-8xl font-extrabold text-white drop-shadow-lg mb-8 animate-pulse">COMING SOON</h1>
        <p class="text-2xl md:text-3xl text-amber-100 mb-8">Thanks for your patience!<br>About page will be available soon.</p>
        <img src="../../Backend/Animation-vector/undraw_under-construction_c2y1.svg" alt="Under Construction" class="w-full max-w-xs md:max-w-md mx-auto mt-6 mb-8 drop-shadow-xl animate-bounce-slow" style="animation-duration:2.5s;" />
        <a href="../index.php" class="mt-6 inline-block bg-white text-amber-700 px-8 py-3 rounded-md font-bold shadow hover:bg-gray-100 hover:scale-105 transition-transform duration-200">Back to Home</a>
    </div>
    <!-- Copy the same footer from index.php with updated amber colors -->
</body>
</html>