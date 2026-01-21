<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BimCheck - SISTEM DIGITAL ANTRIAN BIMBINGAN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        * { font-family: 'Inter', sans-serif; }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .feature-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-14 sm:h-16">
                <div class="flex items-center">
                    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 p-1.5 sm:p-2 rounded-lg">
                        <i class="fas fa-shield-alt text-white text-lg sm:text-xl"></i>
                    </div>
                    <span class="ml-2 sm:ml-3 text-lg sm:text-xl font-bold gradient-text">BimCheck</span>
                </div>
                <div class="flex items-center space-x-2 sm:space-x-4">
                    <a href="views/login.php" class="text-gray-600 hover:text-purple-600 font-medium text-sm sm:text-base transition px-2 sm:px-0">Masuk</a>
                    <a href="views/register.php" class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-3 sm:px-6 py-1.5 sm:py-2 rounded-lg text-xs sm:text-base font-bold hover:shadow-lg transition whitespace-nowrap">
                        <span class="hidden sm:inline">Daftar Sekarang</span>
                        <span class="sm:hidden">Daftar</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="gradient-bg text-white py-12 sm:py-16 md:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold mb-4 sm:mb-6 leading-tight">
                    Sistem Digital Antrian Bimbingan
                </h1>
                <p class="text-base sm:text-lg md:text-xl lg:text-2xl text-purple-100 mb-6 sm:mb-8 max-w-3xl mx-auto px-2">
                    Solusi modern untuk mengelola antrian bimbingan skripsi dengan mudah, efisien, dan terintegrasi
                </p>
                <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center px-4 sm:px-0">
                    <a href="views/register.php" class="bg-white text-purple-600 px-6 sm:px-8 py-3 sm:py-4 rounded-xl font-bold text-base sm:text-lg hover:bg-gray-100 transition shadow-lg w-full sm:w-auto">
                        <i class="fas fa-user-plus mr-2"></i> Mulai Sekarang
                    </a>
                    <a href="views/login.php" class="bg-purple-800 bg-opacity-50 text-white px-6 sm:px-8 py-3 sm:py-4 rounded-xl font-bold text-base sm:text-lg hover:bg-opacity-70 transition border-2 border-white border-opacity-30 w-full sm:w-auto">
                        <i class="fas fa-sign-in-alt mr-2"></i> Masuk
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-12 sm:py-16 md:py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10 sm:mb-12 md:mb-16">
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-800 mb-2 sm:mb-4">Fitur Unggulan</h2>
                <p class="text-gray-600 text-sm sm:text-base md:text-lg px-4">Semua yang Anda butuhkan untuk bimbingan skripsi yang lebih baik</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                <!-- Feature 1 -->
                <div class="feature-card bg-white p-6 sm:p-8 rounded-2xl shadow-lg border border-gray-100">
                    <div class="bg-purple-100 w-12 h-12 sm:w-16 sm:h-16 rounded-xl flex items-center justify-center mb-4 sm:mb-6">
                        <i class="fas fa-qrcode text-purple-600 text-xl sm:text-2xl"></i>
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold text-gray-800 mb-2 sm:mb-3">Validasi QR Code</h3>
                    <p class="text-gray-600 text-sm sm:text-base">
                        Sistem validasi kehadiran menggunakan QR Code untuk memastikan mahasiswa hadir tepat waktu
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="feature-card bg-white p-6 sm:p-8 rounded-2xl shadow-lg border border-gray-100">
                    <div class="bg-blue-100 w-12 h-12 sm:w-16 sm:h-16 rounded-xl flex items-center justify-center mb-4 sm:mb-6">
                        <i class="fas fa-calendar-check text-blue-600 text-xl sm:text-2xl"></i>
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold text-gray-800 mb-2 sm:mb-3">Booking Online</h3>
                    <p class="text-gray-600 text-sm sm:text-base">
                        Mahasiswa dapat booking jadwal bimbingan secara online tanpa perlu datang ke kampus
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="feature-card bg-white p-6 sm:p-8 rounded-2xl shadow-lg border border-gray-100">
                    <div class="bg-green-100 w-12 h-12 sm:w-16 sm:h-16 rounded-xl flex items-center justify-center mb-4 sm:mb-6">
                        <i class="fas fa-bell text-green-600 text-xl sm:text-2xl"></i>
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold text-gray-800 mb-2 sm:mb-3">Sistem Antrian Real-time</h3>
                    <p class="text-gray-600 text-sm sm:text-base">
                        Pantau antrian bimbingan secara real-time dengan notifikasi otomatis
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="feature-card bg-white p-6 sm:p-8 rounded-2xl shadow-lg border border-gray-100">
                    <div class="bg-orange-100 w-12 h-12 sm:w-16 sm:h-16 rounded-xl flex items-center justify-center mb-4 sm:mb-6">
                        <i class="fas fa-comments text-orange-600 text-xl sm:text-2xl"></i>
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold text-gray-800 mb-2 sm:mb-3">Feedback Dosen</h3>
                    <p class="text-gray-600 text-sm sm:text-base">
                        Dosen dapat memberikan feedback langsung kepada mahasiswa setelah bimbingan
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="feature-card bg-white p-6 sm:p-8 rounded-2xl shadow-lg border border-gray-100">
                    <div class="bg-indigo-100 w-12 h-12 sm:w-16 sm:h-16 rounded-xl flex items-center justify-center mb-4 sm:mb-6">
                        <i class="fas fa-chart-bar text-indigo-600 text-xl sm:text-2xl"></i>
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold text-gray-800 mb-2 sm:mb-3">Laporan & Statistik</h3>
                    <p class="text-gray-600 text-sm sm:text-base">
                        Dashboard lengkap dengan grafik dan statistik untuk monitoring bimbingan
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="feature-card bg-white p-6 sm:p-8 rounded-2xl shadow-lg border border-gray-100">
                    <div class="bg-pink-100 w-12 h-12 sm:w-16 sm:h-16 rounded-xl flex items-center justify-center mb-4 sm:mb-6">
                        <i class="fas fa-mobile-alt text-pink-600 text-xl sm:text-2xl"></i>
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold text-gray-800 mb-2 sm:mb-3">Responsive Design</h3>
                    <p class="text-gray-600 text-sm sm:text-base">
                        Akses dari berbagai device, desktop maupun mobile dengan tampilan yang optimal
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works - DUAL VIEW: Workflow + Flowchart -->
    <section class="py-12 sm:py-16 md:py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-10 sm:mb-12 md:mb-16">
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-800 mb-2 sm:mb-4">Cara Kerja BimCheck</h2>
                <p class="text-gray-600 text-sm sm:text-base md:text-lg px-4">Proses bimbingan yang sederhana dan efisien</p>
            </div>

            <!-- TAB NAVIGATION -->
            <div class="flex justify-center mb-8 sm:mb-10">
                <div class="inline-flex rounded-xl bg-white shadow-lg p-1">
                    <button onclick="showTab('workflow')" id="tab-workflow" class="tab-btn px-4 sm:px-8 py-2 sm:py-3 rounded-lg font-bold text-sm sm:text-base transition-all duration-300 bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-md">
                        <i class="fas fa-sitemap mr-2"></i>Workflow
                    </button>
                    <button onclick="showTab('flowchart')" id="tab-flowchart" class="tab-btn px-4 sm:px-8 py-2 sm:py-3 rounded-lg font-bold text-sm sm:text-base transition-all duration-300 text-gray-600 hover:bg-gray-100">
                        <i class="fas fa-project-diagram mr-2"></i>Flowchart Detail
                    </button>
                </div>
            </div>

            <!-- WORKFLOW VIEW (Image) -->
            <div id="workflow-view" class="transition-opacity duration-300">
                <div class="flex justify-center">
                    <img src="uploads/img/Workflow.png" alt="Workflow BimCheck" class="w-full max-w-4xl rounded-2xl shadow-xl">
                </div>
            </div>

            <!-- FLOWCHART VIEW (Image) -->
            <div id="flowchart-view" class="hidden transition-opacity duration-300">
                <div class="flex justify-center">
                    <img src="uploads/img/Flowchart.png" alt="BimCheck Complete Flowchart" class="w-full max-w-4xl rounded-2xl shadow-xl">
                </div>
            </div>
        </div>
    </section>

    <!-- Tab Switching Script -->
    <script>
        function showTab(tabName) {
            const workflowView = document.getElementById('workflow-view');
            const flowchartView = document.getElementById('flowchart-view');
            const workflowTab = document.getElementById('tab-workflow');
            const flowchartTab = document.getElementById('tab-flowchart');

            if (tabName === 'workflow') {
                // Show workflow
                workflowView.classList.remove('hidden');
                flowchartView.classList.add('hidden');
                
                // Style tabs
                workflowTab.classList.add('bg-gradient-to-r', 'from-purple-600', 'to-indigo-600', 'text-white', 'shadow-md');
                workflowTab.classList.remove('text-gray-600', 'hover:bg-gray-100');
                
                flowchartTab.classList.remove('bg-gradient-to-r', 'from-purple-600', 'to-indigo-600', 'text-white', 'shadow-md');
                flowchartTab.classList.add('text-gray-600', 'hover:bg-gray-100');
            } else {
                // Show flowchart
                workflowView.classList.add('hidden');
                flowchartView.classList.remove('hidden');
                
                // Style tabs
                flowchartTab.classList.add('bg-gradient-to-r', 'from-purple-600', 'to-indigo-600', 'text-white', 'shadow-md');
                flowchartTab.classList.remove('text-gray-600', 'hover:bg-gray-100');
                
                workflowTab.classList.remove('bg-gradient-to-r', 'from-purple-600', 'to-indigo-600', 'text-white', 'shadow-md');
                workflowTab.classList.add('text-gray-600', 'hover:bg-gray-100');
            }
        }
    </script>

    <!-- CTA Section -->
    <section class="gradient-bg text-white py-12 sm:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-3 sm:mb-4 px-4">Siap Memulai Bimbingan Digital?</h2>
            <p class="text-base sm:text-lg md:text-xl text-purple-100 mb-6 sm:mb-8 px-4">Bergabunglah dengan BimCheck sekarang</p>
            <a href="views/register.php" class="bg-white text-purple-600 px-6 sm:px-8 py-3 sm:py-4 rounded-xl font-bold text-base sm:text-lg hover:bg-gray-100 transition shadow-lg inline-block">
                <i class="fas fa-rocket mr-2"></i> Daftar Gratis
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-gray-300 py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 sm:gap-8">
                <div>
                    <div class="flex items-center mb-3 sm:mb-4">
                        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 p-1.5 sm:p-2 rounded-lg">
                            <i class="fas fa-shield-alt text-white text-sm sm:text-base"></i>
                        </div>
                        <span class="ml-2 sm:ml-3 text-lg sm:text-xl font-bold text-white">BimCheck</span>
                    </div>
                    <p class="text-gray-400 text-sm sm:text-base">
                        Sistem Antrian Bimbingan Digital yang memudahkan proses bimbingan skripsi.
                    </p>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-3 sm:mb-4 text-sm sm:text-base">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="views/login.php" class="hover:text-white transition text-sm sm:text-base">Masuk</a></li>
                        <li><a href="views/register.php" class="hover:text-white transition text-sm sm:text-base">Daftar</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-3 sm:mb-4 text-sm sm:text-base">Kontak</h4>
                    <ul class="space-y-2 text-gray-400 text-sm sm:text-base">
                        <li><i class="fas fa-envelope mr-2"></i> info@bimcheck.ac.id</li>
                        <li><i class="fas fa-phone mr-2"></i> +62 XXX XXXX XXXX</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-6 sm:mt-8 pt-6 sm:pt-8 text-center text-gray-400 text-xs sm:text-sm">
                <p>&copy; <?php echo date('Y'); ?> BimCheck. All rights reserved.</p>
            </div>
        </div>
    </footer>

</body>
</html>
