<?php
session_start();
// Redirect if already logged in
if(isset($_SESSION['user_id'])) {
    header("Location: /Frontend/Pages/dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="/Frontend/src/tailwind.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CodeLens</title>
</head>
<body class="min-h-screen flex bg-white">
    <!-- Add a back to home link -->
    <a href="../index.php" class="absolute top-4 left-4 text-amber-600 hover:text-amber-700">
        ← Back to Home
    </a>
    
    <div class="w-full flex items-center justify-center p-8">
        <div class="w-full max-w-md">
            <h2 class="text-3xl font-bold text-center mb-8">Welcome Back</h2>
            <?php if(isset($_GET['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($_GET['error']); ?></span>
                </div>
            <?php endif; ?>
            <form class="space-y-6" action="../../Backend/PHP/login.php" method="POST">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email or Registration Number</label>
                    <input name="identifier" type="text" 
                           class="mt-1 block w-full px-3 py-2 rounded-md border-gray-300 shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" 
                           required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input name="password" type="password" 
                           class="mt-1 block w-full px-3 py-2 rounded-md border-gray-300 shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" 
                           required>
                </div>
                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                    Login
                </button>
            </form>
            <div class="text-center mt-4">
                <p class="text-sm text-gray-600">Don't have an account? 
                    <a href="register.php" class="font-medium text-amber-600 hover:text-amber-500">Register here</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>