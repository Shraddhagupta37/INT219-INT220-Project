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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - CodeLens</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="min-h-screen flex bg-white">
    <!-- Add a back to home link -->
    <a href="../index.php" class="absolute top-4 left-4 text-amber-600 hover:text-amber-700">
        ← Back to Home
    </a>

    <!-- Registration Form -->
    <div class="w-full flex items-center justify-center p-8">
        <div class="w-full max-w-md">
            <h2 class="text-3xl font-bold text-center mb-8">Create Account</h2>
            <?php if(isset($_GET['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($_GET['error']); ?></span>
                </div>
            <?php endif; ?>
            <form class="space-y-6" action="../../Backend/PHP/register.php" method="POST" onsubmit="return validateForm(event)">
                <!-- Full Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input name="fullname" type="text" class="mt-1 block w-full px-3 py-2 rounded-md border-gray-300 shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" required>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input name="email" type="email" class="mt-1 block w-full px-3 py-2 rounded-md border-gray-300 shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" required>
                </div>

                <!-- Registration Number / UID -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Registration Number / UID</label>
                    <input name="uid" type="text" 
                           class="mt-1 block w-full px-3 py-2 rounded-md border-gray-300 shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" 
                           pattern="[0-9A-Za-z]+" 
                           title="Please enter a valid Registration Number/UID"
                           required>
                    <p class="mt-1 text-sm text-gray-500">Enter your college Registration Number or Employee ID</p>
                </div>

                <!-- Phone Number -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">+91</span>
                        </div>
                        <input name="phone" type="tel" id="phone" 
                               class="mt-1 block w-full pl-12 pr-3 py-2 rounded-md border-gray-300 shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" 
                               pattern="[6-9][0-9]{9}" 
                               maxlength="10" 
                               required 
                               oninput="validatePhone(this)">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input name="password" type="password" class="mt-1 block w-full px-3 py-2 rounded-md border-gray-300 shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" required>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input name="confirm_password" type="password" class="mt-1 block w-full px-3 py-2 rounded-md border-gray-300 shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" required>
                </div>

                <!-- Role Selection -->
                <div class="border-t border-gray-200 pt-4">
                    <div class="flex items-center justify-start space-x-8">
                        <label class="block text-sm font-medium text-gray-700">Register as:</label>
                        <div class="flex space-x-6">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" name="role" value="candidate" class="h-4 w-4 text-amber-600 focus:ring-amber-500 border-gray-300" checked>
                                <span class="ml-2 text-sm text-gray-700">Candidate</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" name="role" value="admin" class="h-4 w-4 text-amber-600 focus:ring-amber-500 border-gray-300">
                                <span class="ml-2 text-sm text-gray-700">Admin</span>
                            </label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                    Register
                </button>
                <div class="text-center mt-4">
                    <p class="text-sm text-gray-600">Already have an account? 
                        <a href="login.php" class="font-medium text-amber-600 hover:text-amber-500">Login here</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 