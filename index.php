<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Madrasa Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Custom styles for the angled hero divider and background */
        .hero-bg {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), 
                        url('asset/images/backgr.png');
            position: relative;
        }
        .hero-divider {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            overflow: hidden;
            line-height: 0;
        }
        .hero-divider svg {
            position: relative;
            display: block;
            width: calc(100% + 1.3px);
            height: 80px;
            font-size: 1px;
        }
        .hero-divider .shape-fill {
            fill: #ffffff;
        }
        .brand-green { color: #007A48; }
        .bg-brand-green { background-color: #007A48; }
        .bg-dark-green { background-color: #004D2E; }
        .border-brand-green { border-color: #007A48; }
        .hover-brand-green:hover { background-color: #005f37; }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased text-gray-800">

    <?php
        // PHP variables for dynamic content initialization (optional)
        $phone = "+255 710 929 770";
        $email = "info@madrasa.ac.tz";
        $year = date("Y");
    ?>

    <div class="bg-dark-green text-white text-xs py-2 px-4 md:px-12 flex flex-col md:flex-row justify-between items-center space-y-2 md:space-y-0">
        <div class="flex items-center space-x-2">
            <i class="fa-solid fa-mosque"></i>
            <span>Madrasa Management System</span>
        </div>
        <div class="flex flex-col md:flex-row items-center space-y-1 md:space-y-0 md:space-x-6">
            <a href="tel:<?php echo $phone; ?>" class="hover:underline flex items-center space-x-1">
                <i class="fa-solid fa-phone text-[10px]"></i> <span><?php echo $phone; ?></span>
            </a>
            <a href="mailto:<?php echo $email; ?>" class="hover:underline flex items-center space-x-1">
                <i class="fa-solid fa-envelope text-[10px]"></i> <span><?php echo $email; ?></span>
            </a>
            <div class="flex space-x-3 pt-1 md:pt-0">
                <a href="#" class="hover:text-gray-300"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="#" class="hover:text-gray-300"><i class="fa-brands fa-twitter"></i></a>
                <a href="#" class="hover:text-gray-300"><i class="fa-brands fa-youtube"></i></a>
            </div>
        </div>
    </div>

    <nav class="bg-white shadow-sm py-4 px-4 md:px-12 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="text-brand-green text-4xl">
                    <i class="fa-solid fa-mosque"></i>
                </div>
                <div>
                    <div class="font-bold text-xl tracking-wide leading-none text-gray-900">MADRASA</div>
                    <div class="text-[10px] text-gray-500 font-semibold tracking-widest mt-0.5">MANAGEMENT SYSTEM</div>
                </div>
            </div>

            <div class="hidden lg:flex items-center space-x-8 font-medium text-sm text-gray-600">
                <a href="#" class="brand-green border-b-2 border-brand-green pb-1 font-semibold">HOME</a>
                <a href="#" class="hover:text-green-700 transition">ABOUT US</a>
                <a href="#" class="hover:text-green-700 transition">FEATURES</a>
                <a href="#" class="hover:text-green-700 transition">GALLERY</a>
                <a href="#" class="hover:text-green-700 transition">CONTACT US</a>
            </div>

            <div class="hidden sm:flex items-center space-x-3">
                <a href="auth/login.php" class="border border-brand-green brand-green px-5 py-2 rounded text-sm font-semibold hover:bg-green-50 transition">LOGIN</a>
                <a href="auth/register.php" class="bg-brand-green text-white px-5 py-2 rounded text-sm font-semibold hover-brand-green transition">REGISTER</a>
            </div>

            <button id="menu-btn" class="lg:hidden text-gray-700 focus:outline-none">
                <i class="fa-solid fa-bars text-2xl"></i>
            </button>
        </div>

        <div id="mobile-menu" class="hidden lg:hidden flex flex-col space-y-3 mt-4 px-2 pt-2 pb-4 border-t border-gray-100">
            <a href="#" class="brand-green font-semibold">HOME</a>
            <a href="#" class="hover:text-green-700">ABOUT US</a>
            <a href="#" class="hover:text-green-700">FEATURES</a>
            <a href="#" class="hover:text-green-700">GALLERY</a>
            <a href="#" class="hover:text-green-700">CONTACT US</a>
            <div class="flex flex-col space-y-2 pt-2 border-t border-gray-100">
                <a href="#" class="text-center border border-brand-green brand-green py-2 rounded text-sm font-semibold">LOGIN</a>
                <a href="#" class="text-center bg-brand-green text-white py-2 rounded text-sm font-semibold">REGISTER</a>
            </div>
        </div>
    </nav>

    <section class="hero-bg min-h-[550px] md:min-h-[600px] flex items-center px-4 md:px-12 text-white pb-24 relative">
        <div class="max-w-7xl mx-auto w-full z-10">
            <div class="max-w-2xl">
                <h1 class="text-3xl md:text-4xl lg:text-5xl font-extrabold tracking-tight mb-2">
                    WELCOME TO <br>
                    <span class="brand-green">MADRASA</span> <br>
                    MANAGEMENT SYSTEM
                </h1>
                <div class="w-16 h-1 bg-brand-green mb-6"></div>
                <p class="text-gray-200 text-sm md:text-base mb-8 leading-relaxed">
                    A comprehensive solution to manage students, teachers, classes, payments,  and all madrasa activities efficiently and effectively.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="auth/register.php" class="bg-brand-green hover-brand-green px-6 py-3 rounded text-sm font-semibold flex items-center space-x-2 transition shadow-lg">
                        <i class="fa-solid fa-id-card-clip"></i>
                        <span>GET STARTED</span>
                    </a>
                    <a href="auth/login.php" class="border border-white hover:bg-white/10 px-6 py-3 rounded text-sm font-semibold flex items-center space-x-2 transition">
                        <i class="fa-solid fa-circle-info"></i>
                        <span>LEARN MORE</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="hero-divider">
            <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,42.4V0Z" class="shape-fill"></path>
            </svg>
        </div>
    </section>

    <section class="py-16 px-4 md:px-12 bg-white">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-2xl md:text-3xl font-bold uppercase tracking-wide text-gray-800">Our Features</h2>
                <div class="w-12 h-0.5 bg-brand-green mx-auto mt-2"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6">
                
                <div class="border border-gray-100 rounded-lg p-6 text-center shadow-sm hover:shadow-md transition bg-white flex flex-col justify-between">
                    <div>
                        <div class="text-brand-green text-3xl mb-4">
                            <i class="fa-solid fa-users-gear"></i>
                        </div>
                        <h3 class="font-bold text-xs uppercase tracking-wider brand-green mb-2">Student Management</h3>
                        <p class="text-gray-500 text-xs leading-relaxed">
                            Manage student admission, profiles, classes and performance.
                        </p>
                    </div>
                </div>

                <div class="border border-gray-100 rounded-lg p-6 text-center shadow-sm hover:shadow-md transition bg-white flex flex-col justify-between">
                    <div>
                        <div class="text-brand-green text-3xl mb-4">
                            <i class="fa-solid fa-chalkboard-user"></i>
                        </div>
                        <h3 class="font-bold text-xs uppercase tracking-wider brand-green mb-2">Teacher Management</h3>
                        <p class="text-gray-500 text-xs leading-relaxed">
                            Manage teacher information, subjects, timetable and attendance.
                        </p>
                    </div>
                </div>

                <div class="border border-gray-100 rounded-lg p-6 text-center shadow-sm hover:shadow-md transition bg-white flex flex-col justify-between">
                    <div>
                        <div class="text-brand-green text-3xl mb-4">
                            <i class="fa-solid fa-book-open"></i>
                        </div>
                        <h3 class="font-bold text-xs uppercase tracking-wider brand-green mb-2">Academic Management</h3>
                        <p class="text-gray-500 text-xs leading-relaxed">
                            Manage madrasa class groups, lessons, attendance and learner activities.
                        </p>
                    </div>
                </div>

                <div class="border border-gray-100 rounded-lg p-6 text-center shadow-sm hover:shadow-md transition bg-white flex flex-col justify-between">
                    <div>
                        <div class="text-brand-green text-3xl mb-4">
                            <i class="fa-solid fa-circle-dollar-to-slot"></i>
                        </div>
                        <h3 class="font-bold text-xs uppercase tracking-wider brand-green mb-2">Payment Management</h3>
                        <p class="text-gray-500 text-xs leading-relaxed">
                            Track fee structure, payments, balances and generate reports.
                        </p>
                    </div>
                </div>

                <div class="border border-gray-100 rounded-lg p-6 text-center shadow-sm hover:shadow-md transition bg-white flex flex-col justify-between">
                    <div>
                        <div class="text-brand-green text-3xl mb-4">
                            <i class="fa-solid fa-chart-bar"></i>
                        </div>
                        <h3 class="font-bold text-xs uppercase tracking-wider brand-green mb-2">Reports & Analytics</h3>
                        <p class="text-gray-500 text-xs leading-relaxed">
                            Generate various reports and analytics for better decision making.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <footer class="bg-dark-green text-white/80 text-xs py-4 px-4 md:px-12 border-t border-emerald-900">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center space-y-3 md:space-y-0">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-building-columns"></i>
                <span>© <?php echo $year; ?> Madrasa Management System. All rights reserved.</span>
            </div>
            <div class="flex space-x-6">
                <a href="#" class="hover:text-white transition">Privacy Policy</a>
                <span>|</span>
                <a href="#" class="hover:text-white transition">Terms of Service</a>
                <span>|</span>
                <a href="#" class="hover:text-white transition">Help & Support</a>
            </div>
        </div>
    </footer>

    <script>
        const menuBtn = document.getElementById('menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');

        menuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    </script>
</body>
</html>