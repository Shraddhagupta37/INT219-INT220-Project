<script>
    if (window.history && window.history.pushState) {
        window.history.pushState(null, "", window.location.href);
        window.onpopstate = function () {
            window.location.replace(window.location.href);
        };
    }
</script><?php
session_start();
require_once dirname(__FILE__) . '/../Backend/PHP/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="./src/tailwind.css" rel="stylesheet">
    <title>CodeLens - Candidate Assessment Tool</title>
</head>
<body class="flex flex-col min-h-screen bg-gray-50">
    <!-- Header/Navigation -->
    <!-- Modern Glassmorphism Header -->
    <header class="bg-white/70 backdrop-blur border-b border-amber-100 shadow-lg sticky top-0 z-30">
        <nav class="container mx-auto px-4 md:px-8 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-8">
                    <!-- Logo with accent -->
                    <a href="index.php" class="flex items-center gap-2 text-2xl font-extrabold text-amber-600 tracking-tight drop-shadow-sm">
                        <span class="inline-block w-6 h-6 bg-amber-500 rounded-full mr-1"></span>CodeLens
                    </a>
                    <!-- Navigation Links with animated underline -->
                    <?php
    if (isset($_SESSION['user_id'])) {
        // Assuming you store user role as 'role' in session: 'admin' or 'candidate'
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            echo "<a href='Pages/admin-dashboard.php' class='relative text-gray-700 hover:text-amber-600 transition-colors duration-200 after:content-[\'\'] after:block after:h-0.5 after:bg-amber-600 after:scale-x-0 hover:after:scale-x-100 after:transition-transform after:duration-300 after:origin-left'>Dashboard</a>";
        } else {
            echo "<a href='Pages/candidate-dashboard.php' class='relative text-gray-700 hover:text-amber-600 transition-colors duration-200 after:content-[\'\'] after:block after:h-0.5 after:bg-amber-600 after:scale-x-0 hover:after:scale-x-100 after:transition-transform after:duration-300 after:origin-left'>Dashboard</a>";
        }
    } else {
        echo "<a href='Pages/login.php' class='relative text-gray-700 hover:text-amber-600 transition-colors duration-200 after:content-[\'\'] after:block after:h-0.5 after:bg-amber-600 after:scale-x-0 hover:after:scale-x-100 after:transition-transform after:duration-300 after:origin-left'>Dashboard</a>";
    }
?>
                    <?php
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    echo "<a href=\"#\" onclick=\"alert('Admins cannot take the mock tests');return false;\" class='relative text-gray-700 hover:text-amber-600 transition-colors duration-200 after:content-[\'\'] after:block after:h-0.5 after:bg-amber-600 after:scale-x-0 hover:after:scale-x-100 after:transition-transform after:duration-300 after:origin-left'>Mock Test</a>";
} else {
    echo "<a href='Pages/take-test.php?test_id=1' class='relative text-gray-700 hover:text-amber-600 transition-colors duration-200 after:content-[\'\'] after:block after:h-0.5 after:bg-amber-600 after:scale-x-0 hover:after:scale-x-100 after:transition-transform after:duration-300 after:origin-left'>Mock Test</a>";
}
?>
                    <a href="Pages/about.php" class="relative text-gray-700 hover:text-amber-600 transition-colors duration-200 after:content-[\'\'] after:block after:h-0.5 after:bg-amber-600 after:scale-x-0 hover:after:scale-x-100 after:transition-transform after:duration-300 after:origin-left">About</a>
                </div>

                <!-- Auth Buttons -->
                <div class="flex items-center space-x-4">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        <a href="../Backend/PHP/logout.php" class="bg-amber-600 text-white px-4 py-2 rounded-md hover:bg-amber-700">Logout</a>
                    <?php else: ?>
                        <a href="Pages/login.php" class="text-amber-600 hover:text-amber-700">Login</a>
                        <a href="Pages/register.php" class="bg-amber-600 text-white px-4 py-2 rounded-md hover:bg-amber-700">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-amber-500 via-amber-600 to-amber-900 text-white py-32">
        <div class="container mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-12">
            <div class="max-w-xl">
                <h1 class="text-5xl md:text-6xl font-extrabold mb-6 leading-tight drop-shadow-lg">
                    Revolutionize Hackathon Assessments
                </h1>
                <p class="text-xl md:text-2xl mb-8 font-medium opacity-90">
                    Evaluate, shortlist, and empower candidates with our proctored, AI-powered examination platform.
                </p>
                <ul class="mb-8 space-y-2">
                    <li class="flex items-center gap-2"><span class="text-amber-200">&#10003;</span> Secure, proctored online tests</li>
                    <li class="flex items-center gap-2"><span class="text-amber-200">&#10003;</span> Instant analytics & reports</li>
                    <li class="flex items-center gap-2"><span class="text-amber-200">&#10003;</span> Customizable for any hackathon</li>
                </ul>
                <?php if(!isset($_SESSION['user_id'])): ?>
                    <a href="Pages/register.php" class="bg-white text-amber-700 px-8 py-3 rounded-md font-bold shadow hover:bg-gray-100 transform hover:scale-105 transition-transform duration-200">
                        Get Started
                    </a>
                <?php else: ?>
                    <?php
                        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                            $dashboardUrl = 'Pages/admin-dashboard.php';
                        } else {
                            $dashboardUrl = 'Pages/candidate-dashboard.php';
                        }
                    ?>
                    <a href="<?php echo $dashboardUrl; ?>" class="bg-white text-amber-700 px-8 py-3 rounded-md font-bold shadow hover:bg-gray-100 transform hover:scale-105 transition-transform duration-200">
                        Go to Dashboard
                    </a>
                <?php endif; ?>
            </div>

                <!-- Animated SVG Illustration (right side, visible on md+) -->
                <div class="hidden md:block flex-1 flex items-center justify-center">
                    <!-- Floating animation using Tailwind animate-bounce (or custom animation if you want smoother effect) -->
                    <img src="/Backend/Animation-vector/undraw_online-test_20lm.svg" alt="Online Test Illustration" class="w-full max-w-md mx-auto animate-bounce-slow drop-shadow-xl" style="animation-duration: 3s;">
                </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-24 bg-white">
        <div class="container mx-auto px-6">
            <h2 class="text-4xl font-bold text-center mb-12">Platform Features</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-10">
                <div class="bg-gray-50 p-8 rounded-lg shadow transition-colors transition-transform duration-300 flex flex-col items-center hover:scale-105 hover:bg-gradient-to-br hover:from-amber-500 hover:to-amber-700 hover:text-white active:scale-100 active:bg-gradient-to-br active:from-amber-600 active:to-amber-900 group">
    <div class="mb-4 group-hover:text-white group-active:text-white">
        <svg class="w-12 h-12 text-amber-600 transition-colors duration-300 group-hover:text-white group-hover:stroke-white group-hover:fill-white group-active:text-white group-active:stroke-white group-active:fill-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="9" class="group-hover:stroke-white group-active:stroke-white" stroke="currentColor" stroke-width="2.5" fill="none" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4" class="group-hover:stroke-white group-active:stroke-white" stroke-width="2.5"/>
        </svg>
    </div>
    <h3 class="text-xl font-semibold mb-2 text-center group-hover:text-white group-active:text-white transition-colors duration-300">Fair Assessment</h3>
    <p class="text-gray-600 text-center group-hover:text-white group-active:text-white transition-colors duration-300">Automated, unbiased grading ensures every candidate gets a fair shot.</p>
                </div>
                <div class="bg-gray-50 p-8 rounded-lg shadow transition-colors transition-transform duration-300 flex flex-col items-center hover:scale-105 hover:bg-gradient-to-br hover:from-amber-500 hover:to-amber-700 hover:text-white active:scale-100 active:bg-gradient-to-br active:from-amber-600 active:to-amber-900 group">
                    <div class="mb-4 group-hover:text-white group-active:text-white">
                        <svg class="w-12 h-12 text-amber-600 group-hover:text-white group-hover:stroke-white group-hover:fill-white group-active:text-white group-active:stroke-white group-active:fill-white transition-colors duration-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2 text-center group-hover:text-white group-active:text-white transition-colors duration-300">Secure Proctoring</h3>
                    <p class="text-gray-600 text-center group-hover:text-white group-active:text-white transition-colors duration-300">Advanced monitoring system to maintain examination integrity.</p>
                </div>
                <div class="bg-gray-50 p-8 rounded-lg shadow transition-colors transition-transform duration-300 flex flex-col items-center hover:scale-105 hover:bg-gradient-to-br hover:from-amber-500 hover:to-amber-700 hover:text-white active:scale-100 active:bg-gradient-to-br active:from-amber-600 active:to-amber-900 group">
                    <div class="mb-4 group-hover:text-white group-active:text-white">
                        <svg class="w-12 h-12 text-amber-600 group-hover:text-white group-hover:stroke-white group-hover:fill-white group-active:text-white group-active:stroke-white group-active:fill-white transition-colors duration-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2 text-center group-hover:text-white group-active:text-white transition-colors duration-300">Detailed Analytics</h3>
                    <p class="text-gray-600 text-center group-hover:text-white group-active:text-white transition-colors duration-300">Comprehensive reports and insights for better decision making.</p>
                </div>
                <div class="bg-gray-50 p-8 rounded-lg shadow transition-colors transition-transform duration-300 flex flex-col items-center hover:scale-105 hover:bg-gradient-to-br hover:from-amber-500 hover:to-amber-700 hover:text-white active:scale-100 active:bg-gradient-to-br active:from-amber-600 active:to-amber-900 group">
                    <div class="mb-4 group-hover:text-white group-active:text-white">
                        <svg class="w-12 h-12 text-amber-600 group-hover:text-white group-hover:stroke-white group-hover:fill-white group-active:text-white group-active:stroke-white group-active:fill-white transition-colors duration-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h10M7 16h10"/></svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2 text-center group-hover:text-white group-active:text-white transition-colors duration-300">Easy Integration</h3>
                    <p class="text-gray-600 text-center group-hover:text-white group-active:text-white transition-colors duration-300">Seamlessly connect with your existing tools and workflows.</p>
                </div>
                <div class="bg-gray-50 p-8 rounded-lg shadow transition-colors transition-transform duration-300 flex flex-col items-center hover:scale-105 hover:bg-gradient-to-br hover:from-amber-500 hover:to-amber-700 hover:text-white active:scale-100 active:bg-gradient-to-br active:from-amber-600 active:to-amber-900 group">
                    <div class="mb-4 group-hover:text-white group-active:text-white">
                        <svg class="w-12 h-12 text-amber-600 group-hover:text-white group-hover:stroke-white group-hover:fill-white group-active:text-white group-active:stroke-white group-active:fill-white transition-colors duration-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m4 0h-1v-4h-1m4 0h-1v-4h-1"/></svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2 text-center group-hover:text-white group-active:text-white transition-colors duration-300">Custom Branding</h3>
                    <p class="text-gray-600 text-center group-hover:text-white group-active:text-white transition-colors duration-300">Personalize the platform with your event’s branding and colors.</p>
                </div>
                <div class="bg-gray-50 p-8 rounded-lg shadow transition-colors transition-transform duration-300 flex flex-col items-center hover:scale-105 hover:bg-gradient-to-br hover:from-amber-500 hover:to-amber-700 hover:text-white active:scale-100 active:bg-gradient-to-br active:from-amber-600 active:to-amber-900 group">
                    <div class="mb-4 group-hover:text-white group-active:text-white">
                        <svg class="w-12 h-12 text-amber-600 group-hover:text-white group-hover:stroke-white group-hover:fill-white group-active:text-white group-active:stroke-white group-active:fill-white transition-colors duration-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3"/></svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2 text-center group-hover:text-white group-active:text-white transition-colors duration-300">Real-time Feedback</h3>
                    <p class="text-gray-600 text-center group-hover:text-white group-active:text-white transition-colors duration-300">Candidates and organizers get instant feedback and results.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="bg-gray-100 py-16">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl font-bold text-center mb-10">How It Works</h2>
            <div class="grid grid-cols-1 md:grid-cols-5 gap-8">
                <div class="text-center flex flex-col items-center transition-transform duration-300 group hover:scale-105">
                    <div class="bg-amber-600 text-white w-12 h-12 rounded-full flex items-center justify-center mb-4 text-xl font-bold shadow-lg">1</div>
                    <h3 class="font-semibold mb-2 text-lg">Create Account</h3>
                    <p class="text-gray-600">Sign up as an organizer or candidate to get started.</p>
                </div>
                <div class="text-center flex flex-col items-center transition-transform duration-300 group hover:scale-105">
                    <div class="bg-amber-600 text-white w-12 h-12 rounded-full flex items-center justify-center mb-4 text-xl font-bold shadow-lg">2</div>
                    <h3 class="font-semibold mb-2 text-lg">Set Up Assessment</h3>
                    <p class="text-gray-600">Organizers configure tests, criteria, and invite candidates.</p>
                </div>
                <div class="text-center flex flex-col items-center transition-transform duration-300 group hover:scale-105">
                    <div class="bg-amber-600 text-white w-12 h-12 rounded-full flex items-center justify-center mb-4 text-xl font-bold shadow-lg">3</div>
                    <h3 class="font-semibold mb-2 text-lg">Attempt Exam</h3>
                    <p class="text-gray-600">Candidates join, complete the proctored exam online.</p>
                </div>
                <div class="text-center flex flex-col items-center transition-transform duration-300 group hover:scale-105">
                    <div class="bg-amber-600 text-white w-12 h-12 rounded-full flex items-center justify-center mb-4 text-xl font-bold shadow-lg">4</div>
                    <h3 class="font-semibold mb-2 text-lg">Live Proctoring</h3>
                    <p class="text-gray-600">AI-powered monitoring ensures fairness and integrity.</p>
                </div>
                <div class="text-center flex flex-col items-center transition-transform duration-300 group hover:scale-105">
                    <div class="bg-amber-600 text-white w-12 h-12 rounded-full flex items-center justify-center mb-4 text-xl font-bold shadow-lg">5</div>
                    <h3 class="font-semibold mb-2 text-lg">Get Instant Results</h3>
                    <p class="text-gray-600">Organizers and candidates receive detailed analytics and reports.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="bg-white py-16">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl font-bold text-center mb-10">FAQ</h2>
            <div class="max-w-2xl mx-auto divide-y divide-gray-200">
                <div class="py-6">
                    <h3 class="font-semibold text-lg mb-2">Is CodeLens suitable for any hackathon?</h3>
                    <p class="text-gray-600">Yes! It's customizable for any coding event, big or small.</p>
                </div>
                <div class="py-6">
                    <h3 class="font-semibold text-lg mb-2">How secure is the proctoring?</h3>
                    <p class="text-gray-600">We use advanced monitoring and anti-cheating measures for exam integrity.</p>
                </div>
                <div class="py-6">
                    <h3 class="font-semibold text-lg mb-2">Do I get analytics and reports?</h3>
                    <p class="text-gray-600">Absolutely! Organizers and candidates get detailed insights and downloadable reports.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-auto">
        <div class="container mx-auto px-6 py-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">CodeLens</h3>
                    <p class="text-gray-400">Revolutionizing hackathon candidate assessment with standardized proctored examinations.</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="#features" class="hover:underline text-gray-300">Features</a></li>
                        <li><a href="#how-it-works" class="hover:underline text-gray-300">How It Works</a></li>
                        <li><a href="#faq" class="hover:underline text-gray-300">FAQ</a></li>
                        <li><a href="Pages/mock-test.php" class="hover:underline text-gray-300">Mock Test</a></li>
                        <li><a href="Pages/login.php" class="hover:underline text-gray-300">Login</a></li>
                        <li><a href="Pages/register.php" class="hover:underline text-gray-300">Sign Up</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Connect</h3>
                    <div class="flex space-x-4 mb-4">
                        <a href="https://github.com/Shraddhagupta37" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-white">
                            <span class="sr-only">GitHub</span>
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"></path></svg>
                        </a>
                        <a href="https://www.linkedin.com/in/shraddha-gupta-8ab9b828a/" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-white">
                            <span class="sr-only">LinkedIn</span>
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        </a>
                    </div>
                    <div class="text-gray-400 text-sm">Email: <a href="mailto:info@codelens.com" class="underline">info@codelens.com</a></div>
                </div>
            </div>
            <div class="mt-8 text-center text-gray-400 text-sm">&copy; <?php echo date('Y'); ?> CodeLens. All rights reserved.</div>
        </div>
    </footer>
</body>
</html>