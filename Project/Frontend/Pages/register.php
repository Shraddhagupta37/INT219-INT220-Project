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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Country &amp; Phone Number</label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <select name="country_code" id="country_code" class="block w-1/3 px-2 py-2 rounded-l-md border-gray-300 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" required>
                            <option value="+61">Australia (+61)</option>
                            <option value="+55">Brazil (+55)</option>
                            <option value="+1">Canada (+1)</option>
                            <option value="+86">China (+86)</option>
                            <option value="+33">France (+33)</option>
                            <option value="+49">Germany (+49)</option>
                            <option value="+91">India (+91)</option>
                            <option value="+62">Indonesia (+62)</option>
                            <option value="+39">Italy (+39)</option>
                            <option value="+81">Japan (+81)</option>
                            <option value="+52">Mexico (+52)</option>
                            <option value="+7">Russia (+7)</option>
                            <option value="+966">Saudi Arabia (+966)</option>
                            <option value="+27">South Africa (+27)</option>
                            <option value="+82">South Korea (+82)</option>
                            <option value="+34">Spain (+34)</option>
                            <option value="+90">Turkey (+90)</option>
                            <option value="+971">UAE (+971)</option>
                            <option value="+44">United Kingdom (+44)</option>
                            <option value="+1">United States (+1)</option>
                        </select>
                        <input name="phone" type="tel" id="phone"
                               class="block w-2/3 px-3 py-2 rounded-r-md border-gray-300 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                               pattern="[0-9]{5,15}"
                               required
                               placeholder="Phone number (no country code)">
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Select country code and enter your phone number (no length restriction).</p>
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