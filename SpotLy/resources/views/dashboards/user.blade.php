<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpotLy - بوابة السائق التفاعلية</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-gradient-1: #e2e8f0;
            --bg-gradient-2: #cbd5e1;
            --glass-bg: rgba(255, 255, 255, 0.45);
            --glass-border: rgba(255, 255, 255, 0.5);
            --glass-shadow: rgba(31, 38, 135, 0.05);
            --text-color: #1d1d1f;
            --text-muted: #6e6e73;
            --card-radius: 24px;
            --primary-color: #0071e3;
            --primary-gradient: linear-gradient(135deg, #0071e3, #00a4ff);
            --input-bg: rgba(255, 255, 255, 0.6);
            --input-border: rgba(0, 0, 0, 0.08);
            --font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg-gradient-1: #0a0a0c;
                --bg-gradient-2: #16161a;
                --glass-bg: rgba(28, 28, 30, 0.5);
                --glass-border: rgba(255, 255, 255, 0.08);
                --glass-shadow: rgba(0, 0, 0, 0.4);
                --text-color: #f5f5f7;
                --text-muted: #86868b;
                --primary-color: #2997ff;
                --primary-gradient: linear-gradient(135deg, #2997ff, #0071e3);
                --input-bg: rgba(255, 255, 255, 0.04);
                --input-border: rgba(255, 255, 255, 0.08);
            }
        }

        body { 
            background-color: var(--bg-gradient-2); 
            color: var(--text-color);
            font-family: var(--font-family); 
            overflow-x: hidden;
            min-height: 100vh;
            margin: 0;
        }

        /* Ambient Fluid Background */
        .ambient-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: -2;
            overflow: hidden;
            background: linear-gradient(180deg, var(--bg-gradient-1), var(--bg-gradient-2));
        }

        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.4;
            animation: move 24s infinite alternate ease-in-out;
        }

        .blob-1 {
            width: 600px;
            height: 600px;
            background: #ff007f;
            top: -150px;
            left: -100px;
        }

        .blob-2 {
            width: 700px;
            height: 700px;
            background: #0071e3;
            bottom: -200px;
            right: -100px;
            animation-duration: 32s;
        }

        .blob-3 {
            width: 400px;
            height: 400px;
            background: #00f6ff;
            top: 20%;
            left: 40%;
            animation-duration: 20s;
        }

        @keyframes move {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(100px, 80px) scale(1.2); }
            100% { transform: translate(-50px, -40px) scale(0.95); }
        }

        /* Sidebar Glass Styling */
        .sidebar {
            height: calc(100vh - 40px);
            background: var(--glass-bg);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border: 1px solid var(--glass-border);
            border-radius: var(--card-radius);
            position: fixed;
            right: 20px;
            top: 20px;
            width: 280px;
            padding: 30px 20px;
            box-shadow: 0 15px 35px var(--glass-shadow);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .sidebar .brand-title {
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, var(--text-color) 30%, var(--text-muted));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-align: center;
            margin-bottom: 0.5rem;
        }

        .sidebar .nav-link {
            color: var(--text-color);
            opacity: 0.7;
            padding: 14px 20px;
            margin: 6px 0;
            border-radius: 16px;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer;
            font-weight: 500;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            opacity: 1;
            background: rgba(255, 255, 255, 0.2);
            box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.4), 0 4px 10px rgba(0, 0, 0, 0.03);
            font-weight: 600;
            transform: translateX(-4px);
        }

        .main-content {
            margin-right: 320px;
            padding: 20px 40px 40px 20px;
            min-height: 100vh;
        }

        /* Glass Header */
        .dashboard-header {
            background: var(--glass-bg);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border: 1px solid var(--glass-border);
            padding: 20px 30px;
            border-radius: var(--card-radius);
            box-shadow: 0 10px 30px var(--glass-shadow);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Glass Cards */
        .card { 
            background: var(--glass-bg);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border: 1px solid var(--glass-border);
            border-radius: var(--card-radius); 
            box-shadow: 0 10px 30px var(--glass-shadow);
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px var(--glass-shadow);
        }

        .card-header { 
            font-weight: 700; 
            background: transparent; 
            border-bottom: 1px solid var(--glass-border); 
            padding: 20px 25px;
            color: var(--text-color);
        }

        /* Parking Spot Cards */
        .spot-card {
            overflow: hidden;
            position: relative;
        }

        .spot-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: var(--primary-gradient);
        }

        /* Inputs and controls */
        .form-control, .form-select {
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 14px;
            color: var(--text-color);
            padding: 12px 18px;
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .form-control:focus, .form-select:focus {
            background: var(--input-bg);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(0, 113, 227, 0.15);
            color: var(--text-color);
        }

        /* Interactive Map */
        #wsmMap {
            box-shadow: 0 10px 30px var(--glass-shadow);
            border: 1px solid var(--glass-border);
        }

        /* Table design */
        .table {
            color: var(--text-color);
            margin-bottom: 0;
        }

        .table > :not(caption) > * > * {
            background: transparent !important;
            border-bottom-color: var(--glass-border) !important;
            padding: 16px 20px;
            color: var(--text-color);
        }

        /* Navigation pills */
        .nav-pills {
            background: rgba(0, 0, 0, 0.05);
            padding: 6px;
            border-radius: 30px;
        }

        .nav-pills .nav-link {
            border-radius: 24px;
            color: var(--text-color);
            font-weight: 600;
            padding: 10px 24px;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .nav-pills .nav-link.active {
            background: white !important;
            color: #1d1d1f !important;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.06);
        }

        /* Custom buttons */
        .btn {
            border-radius: 14px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            box-shadow: 0 8px 20px rgba(0, 113, 227, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(0, 113, 227, 0.3);
        }

        /* Badges */
        .badge {
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 30px;
        }

        /* Responsive hamburger menu for mobile */
        .menu-toggle {
            display: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-color);
        }

        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(320px);
                right: 0;
                top: 0;
                height: 100vh;
                border-radius: 0;
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-right: 0;
                padding: 20px;
            }
            .menu-toggle {
                display: block;
            }
        }
    </style>
</head>
<body>

    <!-- Ambient animated background blobs -->
    <div class="ambient-bg">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <!-- Right-aligned Floating Sidebar -->
    <aside class="sidebar" id="dashboardSidebar">
        <div class="text-center mb-4">
            <h2 class="brand-title">🚗 SpotLy</h2>
            <span class="badge bg-primary text-white px-3 py-1 rounded-pill">بوابة السائق</span>
        </div>
        <hr class="border-secondary border-opacity-25 my-3">
        <nav class="nav flex-column flex-grow-1">
            <a class="nav-link active" onclick="switchTab('overviewTab', this)"><i class="fas fa-home"></i> نظرة عامة</a>
            <a class="nav-link" onclick="switchTab('bookingTab', this)"><i class="fas fa-parking"></i> حجز موقف تفاعلي</a>
            <a class="nav-link" onclick="switchTab('walletTab', this)"><i class="fas fa-wallet"></i> المحفظة والشحن</a>
            <a class="nav-link" onclick="switchTab('invoicesTab', this)"><i class="fas fa-file-invoice-dollar"></i> فواتير الشحن</a>
            <a class="nav-link" onclick="switchTab('historyTab', this)"><i class="fas fa-history"></i> سجل الحجوزات</a>
            <a class="nav-link" onclick="switchTab('profileTab', this)"><i class="fas fa-user-cog"></i> الإعدادات الشخصية</a>
        </nav>
    </aside>

    <main class="main-content">
        <!-- Dashboard Glass Header -->
        <header class="dashboard-header">
            <div class="d-flex align-items-center gap-3">
                <i class="fas fa-bars menu-toggle" onclick="toggleSidebarMenu()"></i>
                <h5 class="mb-0 fw-bold" id="pageTitleDisplay">🏠 نظرة عامة</h5>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span id="userNameDisplay" class="fw-bold text-dark-emphasis"></span>
                <button onclick="logoutUser()" class="btn btn-sm btn-outline-danger px-4 rounded-pill">تسجيل الخروج</button>
            </div>
        </header>

        <!-- Tab Content Sections -->
        
        <!-- SECTION 1: Overview Dashboard -->
        <section id="overviewTab" class="content-section">
            <div class="row g-4 mb-4">
                <!-- Wallet Card -->
                <div class="col-md-4">
                    <div class="card p-4 border-start border-warning border-4 position-relative">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-muted mb-1">رصيد المحفظة</h6>
                                <h3 class="fw-bold mb-0 text-warning">
                                    <span id="balanceDisplay">0</span> <span style="font-size: 1rem;">نقطة</span>
                                </h3>
                            </div>
                            <button onclick="fetchWalletBalance()" class="btn btn-sm btn-light rounded-circle p-2 shadow-sm" title="تحديث الرصيد">
                                <i class="fas fa-sync-alt text-warning"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Statistics Card: Status -->
                <div class="col-md-4">
                    <div class="card p-4 border-start border-success border-4">
                        <h6 class="text-muted mb-1">حالة الحساب</h6>
                        <h3 class="fw-bold mb-0 text-success" id="statusDisplay">نشط</h3>
                    </div>
                </div>
                <!-- Statistics Card: Violations -->
                <div class="col-md-4">
                    <div class="card p-4 border-start border-danger border-4">
                        <h6 class="text-muted mb-1">مخالفات عدم الحضور</h6>
                        <h3 class="fw-bold mb-0 text-danger"><span id="fakeBookingDisplay">0</span> / 3</h3>
                    </div>
                </div>
            </div>

            <!-- Active Booking Notification Alert -->
            <div id="quickActiveTicketAlert" class="alert alert-primary border-0 shadow-sm rounded-4 d-none mb-4 p-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center">
                        <span class="fs-2 me-3">🎟️</span>
                        <div>
                            <h6 class="fw-bold mb-1">لديك حجز نشط حالياً!</h6>
                            <p class="mb-0 small text-muted">يمكنك عرض تفاصيل التذكرة من تبويب "حجز موقف".</p>
                        </div>
                    </div>
                    <button onclick="switchTab('bookingTab', document.querySelector('[onclick*=\'bookingTab\']'))" class="btn btn-sm btn-primary rounded-pill">عرض التذكرة</button>
                </div>
            </div>

            <div class="row g-4">
                <!-- Vehicle Info Widget -->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header"><i class="fas fa-car-side me-2"></i> معلومات المركبة المسجلة</div>
                        <div class="card-body p-4 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted d-block small mb-1">رقم اللوحة التشغيلية</span>
                                <h3 class="fw-bold text-primary mb-0" id="plateDisplay">--</h3>
                            </div>
                            <span class="fs-1 text-muted opacity-25"><i class="fas fa-id-card"></i></span>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Widget -->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-bell me-2"></i> سجل الإشعارات والتنبيهات الأخير</span>
                            <button onclick="loadDashboardNotifications()" class="btn btn-sm btn-link text-decoration-none p-0 text-primary fw-bold">تحديث 🔄</button>
                        </div>
                        <div class="card-body p-0">
                            <div id="dashboardNotificationLog" class="list-group list-group-flush p-3" style="max-height: 350px; overflow-y: auto;">
                                <div class="text-center py-5 text-muted">جاري جلب آخر التنبيهات...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION 2: Booking Spots Grid & Map -->
        <section id="bookingTab" class="content-section d-none">
            <!-- Active Ticket Card -->
            <div id="activeTicketSection" class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 d-none">
                <div class="card-body p-4 bg-white bg-opacity-75">
                    <div class="row align-items-center text-center text-md-start g-3">
                        <div class="col-md-4 border-end-md">
                            <span class="text-muted d-block mb-1">رقم الموقف المحجوز</span>
                            <h2 class="fw-bold text-dark display-6 mb-0" id="ticketSpotNumber">--</h2>
                        </div>
                        <div class="col-md-4 border-end-md">
                            <span class="text-muted d-block mb-1">طريقة الدفع المعتمدة</span>
                            <h4 class="fw-bold text-primary mb-0" id="ticketPaymentMethod">--</h4>
                        </div>
                        <div class="col-md-4 text-center">
                            <span class="text-muted d-block mb-2">حالة التذكرة</span>
                            <span class="badge bg-success rounded-pill px-4 py-2 fs-6 animate-pulse">نشط وقيد الانتظار</span>
                        </div>
                    </div>
                    <hr class="my-4 border-light">
                    <div class="d-flex flex-wrap gap-2 justify-content-center">
                        <button onclick="requestChangeSpot()" class="btn btn-outline-primary rounded-pill px-4">🔄 تبديل الساحة</button>
                        <button onclick="cancelCurrentBooking()" class="btn btn-outline-danger rounded-pill px-4">❌ إلغاء الحجز</button>
                    </div>
                </div>
            </div>

            <!-- Booking Section Grid / Tabs -->
            <div id="bookingSpotsGridSection" class="card">
                <div class="card-header bg-gradient p-4 border-0">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div>
                            <h5 class="fw-bold mb-1"><i class="fas fa-map-marker-alt"></i> حجز موقف سيارات تفاعلي</h5>
                            <p class="fs-6 mb-0 text-muted">اختر طريقة الحجز المفضلة لديك بالأسفل</p>
                        </div>
                        <ul class="nav nav-pills nav-fill gap-2 bg-light border p-1 rounded-pill" id="booking-tabs" style="min-width: 320px;">
                            <li class="nav-item">
                                <button class="nav-link active fw-bold rounded-pill px-4 py-2 border-0" id="tab-manual" type="button" onclick="switchBookingSubTab('manual')">
                                    🔍 بحث يدوي
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link fw-bold rounded-pill px-4 py-2 border-0" id="tab-smart" type="button" onclick="switchBookingSubTab('smart')">
                                    🧠 ترشيح ذكي
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- 1. Manual Grid Search Content -->
                <div id="booking-manual-content" style="display: block;">
                    <div class="card-body p-4 bg-light bg-opacity-25">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="fw-bold text-secondary">🗺️ خريطة المواقف المباشرة في النظام</span>
                            <div class="d-flex gap-2">
                                <span class="badge bg-success px-3 py-2 rounded-pill">متاح</span>
                                <span class="badge bg-danger px-3 py-2 rounded-pill">ممتلئ</span>
                            </div>
                        </div>
                        <!-- Responsive card list -->
                        <div class="row g-4" id="spotsGridContainer">
                            <div class="text-center py-5 text-muted">جاري تحميل خريطة المواقف المباشرة...</div>
                        </div>
                    </div>
                </div>

                <!-- 2. Smart Recommendations (WSM API) -->
                <div id="booking-smart-content" style="display: none;">
                    <div class="card-body p-4 bg-light bg-opacity-25">
                        <!-- WSM Slider Inputs -->
                        <div class="card mb-4">
                            <div class="card-header border-bottom">
                                <h6 class="fw-bold mb-0">🧠 تفضيلات الترشيح الذكي (Weighted Sum Model)</h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="alert alert-info py-2 rounded-3 mb-4">
                                    💡 <strong>تفاعلي:</strong> قم بسحب أوزان التفضيل حسب رغبتك بالأسفل (المجموع الكلي 100%). وانقر على الخريطة لتحديد مكان وجهتك لتعديل المسافة الجغرافية.
                                </div>
                                <div class="row align-items-center g-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-secondary d-flex justify-content-between mb-2">
                                            <span>📏 القرب الجغرافي للوجهة (المسافة):</span>
                                            <span class="text-primary fw-bold" id="lblDistWeight">50%</span>
                                        </label>
                                        <input type="range" class="form-range" id="wsmDistanceSlider" min="0" max="100" value="50" oninput="adjustWsmSliders('distance')">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-secondary d-flex justify-content-between mb-2">
                                            <span>🚗 وفرة الأماكن (الشواغر):</span>
                                            <span class="text-success fw-bold" id="lblAvailWeight">50%</span>
                                        </label>
                                        <input type="range" class="form-range" id="wsmAvailabilitySlider" min="0" max="100" value="50" oninput="adjustWsmSliders('availability')">
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <label class="form-label fw-bold text-muted small">عنوان الاستدعاء البرمجي (Developer API Url)</label>
                                    <div class="input-group">
                                        <input type="text" id="wsmApiUrlDisplay" class="form-control text-start bg-light" readonly style="direction: ltr;">
                                        <button class="btn btn-primary" type="button" id="btnSendSmartApi" onclick="sendSmartRecommendationRequest()">إرسال الطلب ⚡</button>
                                    </div>
                                </div>
                                <!-- WSM JSON Response Panel -->
                                <div class="card mt-4 d-none" id="apiResponsePanel">
                                    <div class="card-header bg-dark text-white py-2 d-flex justify-content-between align-items-center">
                                        <small class="fw-bold">JSON API Response</small>
                                        <div>
                                            <span class="badge bg-success me-2" id="apiResponseStatus">200 OK</span>
                                            <span class="badge bg-light text-dark"><span id="apiResponseTime">0</span> ms</span>
                                        </div>
                                    </div>
                                    <div class="card-body bg-dark text-white p-3">
                                        <pre id="apiResponseBody" style="max-height: 250px; overflow-y: auto; font-family: monospace; font-size: 0.85rem;" dir="ltr" class="text-start"></pre>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recommendations list & map container -->
                        <div class="row g-4">
                            <!-- Interactive Map -->
                            <div class="col-lg-8">
                                <div class="card h-100">
                                    <div class="card-header fw-bold border-bottom">🗺️ خريطة المواقف الذكية (انقر لتحديد وجهتك 📍)</div>
                                    <div class="card-body p-3">
                                        <div id="wsmMap" style="height: 450px; border-radius: 16px; z-index: 1;"></div>
                                    </div>
                                </div>
                            </div>
                            <!-- Recommendations List -->
                            <div class="col-lg-4">
                                <div class="card h-100">
                                    <div class="card-header fw-bold border-bottom d-flex justify-content-between align-items-center">
                                        <span>⭐ المواقف المقترحة</span>
                                        <span class="badge bg-success rounded-pill px-2 py-1" style="font-size: 0.85rem;">نسبة المطابقة</span>
                                    </div>
                                    <div class="card-body p-0" style="max-height: 450px; overflow-y: auto;" id="wsmRecommendationSidebarList">
                                        <div class="text-center py-5 text-muted">جاري تحميل الترشيحات الذكية...</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION 3: Wallet Recharge -->
        <section id="walletTab" class="content-section d-none">
            <div class="row g-4">
                <!-- File upload request -->
                <div class="col-lg-5">
                    <div class="card h-100">
                        <div class="card-header bg-gradient text-dark p-4 border-0 d-flex align-items-center justify-content-between">
                            <h5 class="fw-bold mb-0">📄 طلب شحن الرصيد</h5>
                            <span class="fs-2">💳</span>
                        </div>
                        <div class="card-body p-4">
                            <form id="rechargeRequestForm">
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-secondary">الساحة المستهدفة للشحن</label>
                                    <select class="form-select form-select-lg" id="targetParkingSelect" required>
                                        <option value="" selected disabled>اختر الساحة التي حولت إليها...</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-secondary">عدد النقاط المطلوب</label>
                                    <input type="number" class="form-control form-control-lg" id="rechargeAmountInput" min="5" placeholder="الحد الأدنى 5 نقاط" required>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-secondary">صورة إيصال التحويل</label>
                                    <input type="file" class="form-control form-control-lg" id="receiptFileInput" accept="image/*" required>
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold py-3 mt-2" id="submitRechargeBtn">
                                    إرسال الطلب للمراجعة 🚀
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Recharge History -->
                <div class="col-lg-7">
                    <div class="card h-100">
                        <div class="card-header p-4 border-0 d-flex align-items-center justify-content-between">
                            <h5 class="fw-bold mb-0">📜 سجل طلبات الشحن السابقة</h5>
                            <button onclick="loadUserRechargeHistory()" class="btn btn-sm btn-light rounded-pill px-3">تحديث 🔄</button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle text-center">
                                    <thead class="table-light">
                                        <tr>
                                            <th>النقاط</th>
                                            <th>تاريخ الطلب</th>
                                            <th>الحالة</th>
                                        </tr>
                                    </thead>
                                    <tbody id="rechargeHistoryTableBody">
                                        <tr>
                                            <td colspan="3" class="text-muted py-5">جاري تحميل السجل...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION 4: Invoices -->
        <section id="invoicesTab" class="content-section d-none">
            <div class="card">
                <div class="card-header p-4 border-0 d-flex align-items-center justify-content-between">
                    <h5 class="fw-bold mb-0">💵 فواتير الشحن المباشر (إيصالات البوابة)</h5>
                    <button onclick="loadUserDirectRechargeInvoices()" class="btn btn-sm btn-light rounded-pill px-3">تحديث 🔄</button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>رقم الإيصال</th>
                                    <th>الساحة</th>
                                    <th>الموظف</th>
                                    <th>القيمة (كاش)</th>
                                    <th>تاريخ الشحن</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody id="directRechargeInvoicesTableBody">
                                <tr>
                                    <td colspan="6" class="text-muted py-5">جاري تحميل الفواتير...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION 5: Booking History -->
        <section id="historyTab" class="content-section d-none">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="fw-bold text-dark mb-0">📜 سجل الحجوزات التاريخي</h5>
                </div>
                <div class="card-body bg-light bg-opacity-25 p-4">
                    <!-- Filters -->
                    <div class="row g-3 align-items-end mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-secondary">الشهر</label>
                            <select class="form-select" id="historyMonthSelect">
                                <option value="">كل الأشهر</option>
                                <option value="1">يناير (1)</option>
                                <option value="2">فبراير (2)</option>
                                <option value="3">مارس (3)</option>
                                <option value="4">أبريل (4)</option>
                                <option value="5">مايو (5)</option>
                                <option value="6">يونيو (6)</option>
                                <option value="7">يوليو (7)</option>
                                <option value="8">أغسطس (8)</option>
                                <option value="9">سبتمبر (9)</option>
                                <option value="10">أكتوبر (10)</option>
                                <option value="11">نوفمبر (11)</option>
                                <option value="12">ديسمبر (12)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-secondary">السنة</label>
                            <select class="form-select" id="historyYearSelect"></select>
                        </div>
                        <div class="col-md-4">
                            <button onclick="loadUserBookingHistory()" class="btn btn-primary w-100 fw-bold py-2 rounded-3">
                                تصفية وتحديث 🔍
                            </button>
                        </div>
                    </div>

                    <!-- History Table -->
                    <div class="table-responsive bg-white rounded-3 shadow-sm">
                        <table class="table table-hover mb-0 align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>رقم الحجز</th>
                                    <th>الساحة</th>
                                    <th>نوع الحجز</th>
                                    <th>تاريخ الدخول</th>
                                    <th>تاريخ الخروج</th>
                                    <th>الحالة</th>
                                    <th>تاريخ الحجز</th>
                                </tr>
                            </thead>
                            <tbody id="bookingHistoryTableBody">
                                <tr>
                                    <td colspan="7" class="text-muted py-5">جاري تحميل سجل الحجوزات...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION 6: Personal Settings -->
        <section id="profileTab" class="content-section d-none">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-xl-7">
                    <div class="card h-100">
                        <div class="card-header bg-gradient text-dark p-4 border-0 d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="fw-bold mb-1">⚙️ البيانات الشخصية</h5>
                                <p class="fs-6 mb-0 text-muted">إدارة بيانات الاتصال وتأمين حسابك</p>
                            </div>
                            <span class="fs-1">🔒</span>
                        </div>
                        <div class="card-body p-4">
                            <form id="profileForm">
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-secondary">الاسم الكامل</label>
                                    <input type="text" class="form-control form-control-lg bg-light text-muted" id="profileNameDisplay" readonly>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-secondary">رقم الهاتف</label>
                                    <input type="text" class="form-control form-control-lg" id="profilePhoneInput" required>
                                </div>
                                <hr class="my-4 border-secondary border-opacity-25">
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-secondary">كلمة مرور جديدة (اختياري)</label>
                                    <input type="password" class="form-control form-control-lg" id="profilePasswordInput" placeholder="•••••••• (اتركها فارغة إذا لم ترغب بالتغيير)">
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold py-3" id="updateProfileBtn">
                                    حفظ التعديلات الشخصية 💾
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let currentUserData = null;

        // ---  تهيئة الصفحة وقراءة بيانات الجلسة ---
        document.addEventListener('DOMContentLoaded', function() {
            try {
                const userDataString = localStorage.getItem('userData');
                if (userDataString) {
                    currentUserData = JSON.parse(userDataString);
                    initializeDriverDashboard();
                } else {
                    window.location.href = '/login';
                }
            } catch (exception) {
                console.error("خطأ في تهيئة لوحة السائق", exception);
            }
        });

        // Toggle mobile sidebar
        function toggleSidebarMenu() {
            const sidebar = document.getElementById('dashboardSidebar');
            sidebar.classList.toggle('active');
        }

        // Close sidebar if clicked outside (mobile)
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('dashboardSidebar');
            const menuToggle = document.querySelector('.menu-toggle');
            if (window.innerWidth <= 991 && sidebar.classList.contains('active') && !sidebar.contains(e.target) && e.target !== menuToggle) {
                sidebar.classList.remove('active');
            }
        });

        // ---  توزيع بيانات السائق على عناصر الواجهة ---
        function initializeDriverDashboard() {
            try {
                document.getElementById('userNameDisplay').innerText = 'السائق: ' + currentUserData.name;
                
                if (currentUserData.profile) {
                    document.getElementById('fakeBookingDisplay').innerText = currentUserData.profile.fake_booking_count || 0;
                    document.getElementById('plateDisplay').innerText = currentUserData.profile.plate_number || '--';
                    
                    const statusVal = currentUserData.profile.status;
                    const statusElem = document.getElementById('statusDisplay');
                    statusElem.innerText = statusVal === 'active' ? 'نشط' : 'محظور';
                    statusElem.className = statusVal === 'active' ? 'fw-bold mb-0 text-success' : 'fw-bold mb-0 text-danger';
                }

                fetchWalletBalance();
                checkActiveBookingForOverview();
                loadDashboardNotifications();
            } catch (exception) {
                console.error(exception);
            }
        }

        // ---  جلب رصيد المحفظة الرقمية ---
        async function fetchWalletBalance() {
            try {
                if (!currentUserData || !currentUserData.accountId) return;

                const apiResponse = await fetch('/api/wallet/balance?userId=' + currentUserData.accountId);
                const resultData = await apiResponse.json();

                if (apiResponse.ok && resultData.status === 'success') {
                    const balanceElement = document.getElementById('balanceDisplay');
                    balanceElement.style.opacity = '0.5';
                    setTimeout(() => {
                        try {
                            balanceElement.innerText = resultData.balance;
                            balanceElement.style.opacity = '1';
                        } catch (innerException) {
                            console.error(innerException);
                        }
                    }, 300);
                }
            } catch (exception) {
                console.error("خطأ أثناء تحديث رصيد المحفظة", exception);
            }
        }

        // --- دالة تحديث إحصائيات السائق (المخالفات وحالة الحساب) ---
        async function refreshDriverStats() {
            try {
                if (!currentUserData || !currentUserData.accountId) return;

                const response = await fetch('/api/accounts/stats?userId=' + currentUserData.accountId);
                const data = await response.json();

                if (response.ok && data.status === 'success') {
                    const fakeBookingElem = document.getElementById('fakeBookingDisplay');
                    if (fakeBookingElem && fakeBookingElem.innerText != data.fake_booking_count) {
                        fakeBookingElem.innerText = data.fake_booking_count;
                        fakeBookingElem.parentElement.classList.add('animate-pulse');
                        setTimeout(() => fakeBookingElem.parentElement.classList.remove('animate-pulse'), 1000);
                    }

                    const statusElem = document.getElementById('statusDisplay');
                    if (statusElem) {
                        statusElem.innerText = data.account_status === 'active' ? 'نشط' : 'محظور';
                        statusElem.className = data.account_status === 'active' ? 'fw-bold mb-0 text-success' : 'fw-bold mb-0 text-danger';
                    }

                    if(currentUserData.profile) {
                        currentUserData.profile.fake_booking_count = data.fake_booking_count;
                        currentUserData.profile.status = data.account_status;
                        localStorage.setItem('userData', JSON.stringify(currentUserData));
                    }

                    if (data.account_status === 'blocked') {
                        Swal.fire({
                            title: 'تم حظر الحساب!',
                            text: 'لقد تجاوزت الحد الأقصى للمخالفات (3 مرات). سيتم تسجيل خروجك الآن.',
                            icon: 'error',
                            confirmButtonText: 'حسناً',
                            allowOutsideClick: false
                        }).then(() => {
                            localStorage.clear();
                            window.location.href = '/login';
                        });
                    }
                }
            } catch (exception) {
                console.error("خطأ في تحديث إحصائيات السائق", exception);
            }
        }

        // دالة جلب السجل
        async function loadUserRechargeHistory() {
            try {
                const response = await fetch('/api/recharges/user-requests?userId=' + currentUserData.accountId);
                const resultData = await response.json();
                const tableBodyElement = document.getElementById('rechargeHistoryTableBody');

                if (tableBodyElement && response.ok && resultData.status === 'success') {
                    tableBodyElement.innerHTML = '';

                    if (resultData.data.length > 0) {
                        resultData.data.forEach(requestItem => {
                            try {
                                let statusBadgeClass = 'bg-warning text-dark';
                                let statusLabel = 'قيد المراجعة';

                                if (requestItem.status === 'Approved') {
                                    statusBadgeClass = 'bg-success text-white';
                                    statusLabel = 'تم الاعتماد';
                                } else if (requestItem.status === 'Rejected') {
                                    statusBadgeClass = 'bg-danger text-white';
                                    statusLabel = 'مرفوض';
                                }

                                const requestDateValue = new Date(requestItem.created_at).toLocaleDateString('ar-LY', {
                                    year: 'numeric', month: 'short', day: 'numeric'
                                });

                                tableBodyElement.innerHTML += `
                                    <tr>
                                        <td class="fw-bold text-dark">${requestItem.requested_points}</td>
                                        <td class="text-muted">${requestDateValue}</td>
                                        <td><span class="badge ${statusBadgeClass} rounded-pill px-3 py-1">${statusLabel}</span></td>
                                    </tr>
                                `;
                            } catch (innerException) {
                                console.error(innerException);
                            }
                        });
                    } else {
                        tableBodyElement.innerHTML = '<tr><td colspan="3" class="text-muted py-5">لا توجد طلبات شحن سابقة في سجلك.</td></tr>';
                    }
                }
            } catch (exception) {
                console.error(exception);
            }
        }

        // دالة جلب كافة المواقف وتعبئة قائمة الشحن المنسدلة ديناميكياً
        async function loadParkingOptionsForRecharge() {
            try {
                const response = await fetch('/api/parkings/spots');
                const resultData = await response.json();
                const selectElement = document.getElementById('targetParkingSelect');
                
                if (selectElement && response.ok && resultData.status === 'success') {
                    selectElement.innerHTML = '<option value="" selected disabled>اختر الساحة التي حولت لرقم حسابها...</option>';
                    resultData.data.forEach(parkingItem => {
                        try {
                            const optionElement = document.createElement('option');
                            optionElement.value = parkingItem.id;
                            optionElement.textContent = `${parkingItem.name} (${parkingItem.location_park})`;
                            selectElement.appendChild(optionElement);
                        } catch (innerException) {
                            console.error(innerException);
                        }
                    });
                }
            } catch (exception) {
                console.error("خطأ في تحميل قائمة الساحات للشحن", exception);
            }
        }

        // --- دالة فحص التذكرة النشطة (لإظهار التنبيه في الصفحة الرئيسية) ---
        async function checkActiveBookingForOverview() {
            try {
                const response = await fetch('/api/bookings/active?userId=' + currentUserData.accountId);
                const result = await response.json();
                const alertElement = document.getElementById('quickActiveTicketAlert');

                if (response.ok && result.hasActiveBooking) {
                    alertElement.classList.remove('d-none');
                } else {
                    alertElement.classList.add('d-none');
                }
            } catch (exception) {
                console.error(exception);
            }
        }

        // ---  التبديل الديناميكي بين التبويبات ---
        function switchTab(sectionIdValue, clickedLinkElement) {
            try {
                const allSections = document.querySelectorAll('.content-section');
                allSections.forEach(sectionItem => {
                    sectionItem.classList.add('d-none');
                });

                const targetSection = document.getElementById(sectionIdValue);
                if (targetSection) {
                    targetSection.classList.remove('d-none');
                }

                const allNavLinks = document.querySelectorAll('.sidebar .nav-link');
                allNavLinks.forEach(linkItem => {
                    linkItem.classList.remove('active');
                });

                clickedLinkElement.classList.add('active');
                document.getElementById('pageTitleDisplay').innerText = clickedLinkElement.innerText.trim();

                // Close mobile menu sidebar if switching tabs
                const sidebar = document.getElementById('dashboardSidebar');
                if (window.innerWidth <= 991) {
                    sidebar.classList.remove('active');
                }

                if (sectionIdValue === 'overviewTab') {
                    fetchWalletBalance();
                    checkActiveBookingForOverview();
                    refreshDriverStats();
                    loadDashboardNotifications();
                } else if (sectionIdValue === 'profileTab') {
                    loadProfileData();
                } else if (sectionIdValue === 'bookingTab') {
                    checkActiveTicketAndLoadGrid();
                } else if (sectionIdValue === 'walletTab') {
                    loadUserRechargeHistory();
                    loadParkingOptionsForRecharge();
                } else if (sectionIdValue === 'invoicesTab') {
                    loadUserDirectRechargeInvoices();
                } else if (sectionIdValue === 'historyTab') {
                    initializeHistoryYearSelect();
                    loadUserBookingHistory();
                }
            } catch (exception) {
                console.error("خطأ في التبديل وتحديث البيانات", exception);
            }
        }

        // معالجة رفع إيصال التحويل البنكي للشحن
        document.getElementById('rechargeRequestForm').addEventListener('submit', async function(event) {
            try {
                event.preventDefault();
                
                const parkingIdValue = document.getElementById('targetParkingSelect').value;
                const amountInputValue = document.getElementById('rechargeAmountInput').value;
                const fileInputValue = document.getElementById('receiptFileInput').files[0];
                const submitButtonElement = document.getElementById('submitRechargeBtn');

                if (!parkingIdValue) {
                    Swal.fire('تنبيه هام', 'يرجى اختيار الساحة المستهدفة من القائمة قبل الإرسال.', 'warning');
                    return;
                }

                submitButtonElement.disabled = true;

                try {
                    const formData = new FormData();
                    formData.append('userId', currentUserData.accountId);
                    formData.append('parkingId', parkingIdValue);
                    formData.append('amount', amountInputValue);
                    formData.append('receiptFile', fileInputValue);

                    Swal.fire({
                        title: 'جاري رفع البيانات...',
                        text: 'يرجى عدم إغلاق الصفحة لحين استكمال رفع صورة الإيصال بنجاح.',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    const response = await fetch('/api/recharges/submit', {
                        method: 'POST',
                        body: formData
                    });

                    const resultData = await response.json();

                    if (response.ok && resultData.status === 'success') {
                        Swal.fire('تم الإرسال بنجاح', 'تم توجيه طلبك للموظف المسؤول عن الساحة.', 'success');
                        document.getElementById('rechargeRequestForm').reset();
                        loadUserRechargeHistory();
                    } else {
                        throw new Error(resultData.message || 'فشل رفع الإيصال.');
                    }
                } catch (exception) {
                    Swal.fire('خطأ', exception.message, 'error');
                } finally {
                    submitButtonElement.disabled = false;
                }
            } catch (exception) {
                console.error(exception);
            }
        });

        let activeBookingId = null;

        // ---  فحص التذكرة النشطة وتحميل شبكة المواقف ---
        async function checkActiveTicketAndLoadGrid() {
            const activeTicketCard = document.getElementById('activeTicketSection');
            const gridMapCard = document.getElementById('bookingSpotsGridSection');

            if (activeTicketCard) {
                activeTicketCard.classList.add('d-none');
                activeTicketCard.style.setProperty('display', 'none', 'important');
            }

            try {
                if (!currentUserData || !currentUserData.accountId) {
                    throw new Error("بيانات المستخدم غير مكتملة");
                }

                const response = await fetch('/api/bookings/active?userId=' + currentUserData.accountId);
                const resultData = await response.json();

                if (response.ok && resultData.status === 'success' && resultData.hasActiveBooking) {
                    const bookingRecord = resultData.bookingData;
                    activeBookingId = bookingRecord.id;
                    
                    document.getElementById('ticketSpotNumber').innerText = bookingRecord.parking_name || '--';
                    document.getElementById('ticketPaymentMethod').innerText = bookingRecord.type === 'initial' ? '⏱️ حجز مبدئي (مؤقت)' : '✅ حجز فعلي';

                    if (activeTicketCard) {
                        activeTicketCard.classList.remove('d-none');
                        activeTicketCard.style.setProperty('display', 'block', 'important');
                    }
                    if (gridMapCard) {
                        gridMapCard.classList.add('d-none');
                        gridMapCard.style.setProperty('display', 'none', 'important');
                    }
                } else {
                    activeBookingId = null;

                    if (gridMapCard) {
                        gridMapCard.classList.remove('d-none');
                        gridMapCard.style.setProperty('display', 'block', 'important');
                    }
                    loadLiveSpotsGrid();
                }
            } catch (exception) {
                console.error("خطأ في فحص التذكرة النشطة:", exception);
                if (gridMapCard) {
                    gridMapCard.classList.remove('d-none');
                    gridMapCard.style.setProperty('display', 'block', 'important');
                }
                loadLiveSpotsGrid();
            }
        }

        // ---  رسم خريطة ساحات الوقوف التفاعلية بناءً على السعة ---
        async function loadLiveSpotsGrid() {
            try {
                const response = await fetch('/api/parkings/spots');
                const resultData = await response.json();
                const gridContainerElement = document.getElementById('spotsGridContainer');

                if (gridContainerElement && response.ok && resultData.status === 'success') {
                    gridContainerElement.innerHTML = '';

                    resultData.data.forEach(parkingArea => {
                        try {
                            const isAreaAvailable = parkingArea.available_capacity > 0;
                            const areaBadgeClass = isAreaAvailable ? 'bg-success' : 'bg-danger';
                            const areaStatusLabel = isAreaAvailable ? 'متاح للحجز' : 'ممتلئ بالكامل';
                            const areaOpacityStyle = isAreaAvailable ? 'opacity: 1;' : 'opacity: 0.6; cursor: not-allowed;';
                            const bankAccountDisplay = parkingArea.employee_bank_account || 'غير متوفر حالياً';

                            gridContainerElement.innerHTML += `
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="card spot-card text-center p-3 border-0 h-100" 
                                         style="${areaOpacityStyle} ${isAreaAvailable ? 'cursor: pointer;' : ''}"
                                         onclick="initiateSpotReservation(${parkingArea.id}, '${parkingArea.name}', ${parkingArea.available_capacity})">
                                        <div class="card-body p-3 d-flex flex-column justify-content-between h-100">
                                            <div>
                                                <span class="display-6 d-block mb-3">${isAreaAvailable ? '🅿️' : '⛔'}</span>
                                                <h4 class="fw-bold mb-1">${parkingArea.name}</h4>
                                                <p class="text-muted small mb-3"><i class="me-1">📍</i> ${parkingArea.location_park}</p>
                                                
                                                <div class="alert alert-light border-0 py-2 mb-3 rounded-3" style="background-color: var(--input-bg);">
                                                    <small class="text-muted d-block mb-1">الحساب المصرفي للتحويل:</small>
                                                    <span class="fw-bold text-primary" style="letter-spacing: 1px;">${bankAccountDisplay}</span>
                                                </div>
                                            </div>
                                            
                                            <div>
                                                <div class="d-flex justify-content-center align-items-center gap-2 mb-3">
                                                    <span class="badge ${areaBadgeClass} rounded-pill px-3 py-2">${areaStatusLabel}</span>
                                                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                                                        الشاغر: ${parkingArea.available_capacity} / ${parkingArea.total_capacity}
                                                    </span>
                                                </div>
                                                <button class="btn btn-primary w-100 rounded-pill py-2" ${isAreaAvailable ? '' : 'disabled'}>احجز الآن 🚀</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        } catch (innerException) {
                            console.error(innerException);
                        }
                    });
                }
            } catch (exception) {
                console.error("خطأ في تحميل خريطة المواقف", exception);
            }
        }

        // التبديل بين البحث اليدوي والترشيح الذكي داخل حجز الموقف التفاعلي
        window.switchBookingSubTab = function(target) {
            const tabManual = document.getElementById('tab-manual');
            const tabSmart = document.getElementById('tab-smart');
            const manualContent = document.getElementById('booking-manual-content');
            const smartContent = document.getElementById('booking-smart-content');

            if (target === 'manual') {
                tabManual.classList.add('active');
                tabSmart.classList.remove('active');
                manualContent.style.display = 'block';
                smartContent.style.display = 'none';
                loadLiveSpotsGrid();
            } else {
                tabSmart.classList.add('active');
                tabManual.classList.remove('active');
                manualContent.style.display = 'none';
                smartContent.style.display = 'block';
                
                if (!wsmMap) {
                    setTimeout(() => {
                        initWsmMap();
                    }, 100);
                } else {
                    setTimeout(() => {
                        wsmMap.invalidateSize();
                    }, 100);
                }
            }
        };

        // متغيرات خوارزمية المجموع الموزون (WSM) للترشيح الذكي
        let wsmMap = null;
        let wsmMapMarker = null;
        let wsmMarkersList = [];
        let wsmLat = 32.8872;
        let wsmLng = 13.1913;
        let wsmDistWeight = 0.5;
        let wsmAvailWeight = 0.5;
        let wsmParkingsData = [];

        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371;
            const dLat = deg2rad(lat2 - lat1);
            const dLon = deg2rad(lon2 - lon1);
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                      Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
                      Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return R * c;
        }
        function deg2rad(deg) {
            return deg * (Math.PI / 180);
        }

        window.adjustWsmSliders = function(source) {
            const distSlider = document.getElementById('wsmDistanceSlider');
            const availSlider = document.getElementById('wsmAvailabilitySlider');
            
            if (source === 'distance') {
                availSlider.value = 100 - parseInt(distSlider.value);
            } else {
                distSlider.value = 100 - parseInt(availSlider.value);
            }
            
            document.getElementById('lblDistWeight').innerText = distSlider.value + '%';
            document.getElementById('lblAvailWeight').innerText = availSlider.value + '%';
            
            wsmDistWeight = parseInt(distSlider.value) / 100;
            wsmAvailWeight = parseInt(availSlider.value) / 100;
            
            updateWsmApiUrlDisplay();
            updateWsmCalculations();
        };

        window.initWsmMap = async function() {
            try {
                if (wsmMap) return;
                
                const container = document.getElementById('wsmMap');
                if (!container) return;

                wsmMap = L.map('wsmMap').setView([wsmLat, wsmLng], 13);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(wsmMap);

                window.greenIcon = L.divIcon({
                    html: '<div style="background-color: #34c759; width: 28px; height: 28px; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 10px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 11px; font-family: sans-serif;">P</div>',
                    className: 'custom-div-icon',
                    iconSize: [28, 28],
                    iconAnchor: [14, 14]
                });
                
                window.yellowIcon = L.divIcon({
                    html: '<div style="background-color: #ffcc00; width: 28px; height: 28px; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 10px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 11px; font-family: sans-serif;">P</div>',
                    className: 'custom-div-icon',
                    iconSize: [28, 28],
                    iconAnchor: [14, 14]
                });
                
                window.redIcon = L.divIcon({
                    html: '<div style="background-color: #ff3b30; width: 28px; height: 28px; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 10px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 11px; font-family: sans-serif;">P</div>',
                    className: 'custom-div-icon',
                    iconSize: [28, 28],
                    iconAnchor: [14, 14]
                });

                window.userDestIcon = L.divIcon({
                    html: '<div style="background-color: #0071e3; width: 34px; height: 34px; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 12px rgba(0, 113, 227, 0.6); display: flex; align-items: center; justify-content: center; color: white; font-size: 14px;">📍</div>',
                    className: 'custom-div-icon-dest',
                    iconSize: [34, 34],
                    iconAnchor: [17, 17]
                });

                wsmMapMarker = L.marker([wsmLat, wsmLng], {
                    icon: userDestIcon,
                    draggable: true
                }).addTo(wsmMap).bindPopup('<div class="text-center font-bold">وجهتك المستهدفة / موقعك 📍</div>').openPopup();

                wsmMapMarker.on('dragend', function() {
                    const pos = wsmMapMarker.getLatLng();
                    wsmLat = pos.lat;
                    wsmLng = pos.lng;
                    updateWsmApiUrlDisplay();
                    updateWsmCalculations();
                });

                wsmMap.on('click', function(e) {
                    wsmLat = e.latlng.lat;
                    wsmLng = e.latlng.lng;
                    wsmMapMarker.setLatLng(e.latlng);
                    updateWsmApiUrlDisplay();
                    updateWsmCalculations();
                });

                const response = await fetch('/api/parkings/spots');
                const res = await response.json();
                if (response.ok && res.status === 'success') {
                    wsmParkingsData = res.data;
                    updateWsmCalculations();
                }
            } catch (error) {
                console.error("خطأ في تشغيل خريطة WSM", error);
            }
        };

        window.updateWsmCalculations = function() {
            if (!wsmParkingsData || wsmParkingsData.length === 0) return;

            let dataCopy = wsmParkingsData.map(p => {
                const lat = parseFloat(p.latitude);
                const lng = parseFloat(p.longitude);
                const distance = (!isNaN(lat) && !isNaN(lng) && lat !== 0) ? calculateDistance(wsmLat, wsmLng, lat, lng) : 1.0;
                return { ...p, computed_distance: isNaN(distance) ? 1.0 : distance };
            });

            const distances = dataCopy.map(p => p.computed_distance);
            const maxDist = Math.max(...distances) || 1;
            const minDist = Math.min(...distances) || 0;
            const distRange = (maxDist - minDist) || 1;

            dataCopy.forEach(p => {
                const normDist = distRange > 0 ? ((maxDist - p.computed_distance) / distRange) : 1;
                const totalCap = parseInt(p.total_capacity) || 0;
                const availCap = parseInt(p.available_capacity) || 0;
                const normAvail = totalCap > 0 ? (availCap / totalCap) : 0;
                
                const score = (wsmDistWeight * normDist) + (wsmAvailWeight * normAvail);
                p.wsm_score = isNaN(score) ? 0 : score;
                p.match_percentage = Math.min(100, Math.max(0, Math.round(p.wsm_score * 100)));
            });

            dataCopy.sort((a, b) => b.wsm_score - a.wsm_score);

            updateWsmMapMarkers(dataCopy);
            updateWsmRecommendationList(dataCopy);
        };

        window.updateWsmMapMarkers = function(sortedData) {
            if (!wsmMap) return;

            wsmMarkersList.forEach(m => wsmMap.removeLayer(m));
            wsmMarkersList = [];

            sortedData.forEach((p, idx) => {
                const lat = parseFloat(p.latitude);
                const lng = parseFloat(p.longitude);
                if (isNaN(lat) || isNaN(lng) || lat === 0) return;

                let markerIcon = yellowIcon;
                if (p.available_capacity === 0) {
                    markerIcon = redIcon;
                } else if (idx === 0) {
                    markerIcon = greenIcon;
                } else if (idx >= 3) {
                    markerIcon = redIcon;
                }

                const marker = L.marker([lat, lng], { icon: markerIcon }).addTo(wsmMap);
                const safeName = (p.name || '').replace(/['"\\]/g, '\\$&');
                
                const popupContent = `
                    <div style="direction: rtl; text-align: right; font-family: sans-serif; min-width: 170px; line-height: 1.4;">
                        <h6 class="fw-bold mb-1 text-dark">${p.name}</h6>
                        <span class="badge bg-success text-white mb-2">تطابق: ${p.match_percentage}%</span>
                        <p class="mb-1 text-muted small">📍 <b>المسافة:</b> ${(p.computed_distance || 0).toFixed(2)} كم</p>
                        <p class="mb-2 text-muted small">🚗 <b>الشاغر:</b> ${p.available_capacity} / ${p.total_capacity}</p>
                        ${p.available_capacity > 0 
                            ? `<button onclick="initiateSpotReservation(${p.id}, '${safeName}', ${p.available_capacity})" class="btn btn-sm btn-primary w-100 fw-bold py-1">حجز فوري 🚀</button>` 
                            : '<span class="badge bg-danger w-100 d-block text-center py-1">ممتلئ بالكامل</span>'}
                    </div>
                `;
                marker.bindPopup(popupContent);
                wsmMarkersList.push(marker);
            });
        };

        window.updateWsmRecommendationList = function(sortedData) {
            const list = document.getElementById('wsmRecommendationSidebarList');
            if (!list) return;

            list.innerHTML = '';

            sortedData.forEach((p, idx) => {
                let badgeStyle = 'bg-warning text-dark';
                if (p.available_capacity === 0) {
                    badgeStyle = 'bg-danger text-white';
                } else if (idx === 0) {
                    badgeStyle = 'bg-success text-white';
                } else if (idx >= 3) {
                    badgeStyle = 'bg-secondary text-white';
                }

                const isAvailable = p.available_capacity > 0;
                const safeName = (p.name || '').replace(/['"\\]/g, '\\$&');
                const lat = parseFloat(p.latitude) || wsmLat;
                const lng = parseFloat(p.longitude) || wsmLng;

                const itemHtml = `
                    <div class="p-3 border-bottom list-group-item-action transition-all" style="cursor: pointer;" onclick="focusParkingOnWsmMap(${lat}, ${lng}, '${safeName}')">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark" style="font-size: 0.9rem;">${p.name}</span>
                            <span class="badge ${badgeStyle} rounded-pill px-2 py-1" style="font-size: 0.75rem;">${p.match_percentage}%</span>
                        </div>
                        <small class="text-muted d-block mb-2">📍 ${p.location_park} (${(p.computed_distance || 0).toFixed(2)} كم)</small>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-secondary fw-semibold">الشاغر: ${p.available_capacity} / ${p.total_capacity}</small>
                            ${isAvailable 
                                ? `<button onclick="event.stopPropagation(); initiateSpotReservation(${p.id}, '${safeName}', ${p.available_capacity})" class="btn btn-sm btn-primary px-3 py-1 rounded-pill fw-bold" style="font-size: 0.75rem;">حجز 🚀</button>` 
                                : '<span class="badge bg-danger rounded-pill px-2 py-1" style="font-size: 0.7rem;">ممتلئ</span>'}
                        </div>
                    </div>
                `;
                list.insertAdjacentHTML('beforeend', itemHtml);
            });
        };

        window.focusParkingOnWsmMap = function(lat, lng, name) {
            if (wsmMap) {
                wsmMap.setView([lat, lng], 15);
                const marker = wsmMarkersList.find(m => m.getLatLng().lat === lat && m.getLatLng().lng === lng);
                if (marker) {
                    marker.openPopup();
                }
            }
        };

        window.updateWsmApiUrlDisplay = function() {
            const display = document.getElementById('wsmApiUrlDisplay');
            if (display) {
                display.value = `/api/parkings/recommend?latitude=${wsmLat.toFixed(6)}&longitude=${wsmLng.toFixed(6)}&sort_by=wsm&w_dist=${wsmDistWeight.toFixed(2)}&w_avail=${wsmAvailWeight.toFixed(2)}`;
            }
        };

        window.sendSmartRecommendationRequest = async function() {
            const btnSend = document.getElementById('btnSendSmartApi');
            btnSend.disabled = true;
            btnSend.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> جاري الطلب...';

            const startTime = performance.now();
            const url = document.getElementById('wsmApiUrlDisplay').value;

            try {
                const response = await fetch(url);
                const data = await response.json();
                const endTime = performance.now();
                const latency = Math.round(endTime - startTime);

                document.getElementById('apiResponsePanel').classList.remove('d-none');
                document.getElementById('apiResponseStatus').innerText = `${response.status} ${response.statusText || (response.ok ? 'OK' : 'Error')}`;
                
                const statusBadge = document.getElementById('apiResponseStatus').parentElement;
                if (response.ok) {
                    statusBadge.className = 'badge bg-success px-3 py-2 rounded-pill';
                } else {
                    statusBadge.className = 'badge bg-danger px-3 py-2 rounded-pill';
                }

                document.getElementById('apiResponseTime').innerText = latency;
                document.getElementById('apiResponseBody').innerText = JSON.stringify(data, null, 4);

            } catch (error) {
                console.error("خطأ أثناء إرسال طلب الـ API", error);
                document.getElementById('apiResponsePanel').classList.remove('d-none');
                document.getElementById('apiResponseStatus').innerText = '500 Error';
                document.getElementById('apiResponseStatus').parentElement.className = 'badge bg-danger px-3 py-2 rounded-pill';
                document.getElementById('apiResponseBody').innerText = JSON.stringify({
                    status: "error",
                    message: error.message || "Internal Server Error"
                }, null, 4);
            } finally {
                btnSend.disabled = false;
                btnSend.innerHTML = 'إرسال الطلب ⚡';
            }
        };

        window.toggleTimeInputs = function(isActualSelected) {
            const timeInputsDiv = document.getElementById('actualTimeInputs');
            if (timeInputsDiv) {
                isActualSelected ? timeInputsDiv.classList.remove('d-none') : timeInputsDiv.classList.add('d-none');
            }
        };

        async function initiateSpotReservation(spotIdValue, spotNameValue, availableCapacity) {
            try {
                if (availableCapacity <= 0) {
                    Swal.fire({
                        icon: 'warning', title: 'الموقف ممتلئ', text: 'عذراً، هذه الساحة لا تحتوي على أماكن شاغرة حالياً.', confirmButtonColor: '#0071e3'
                    });
                    return;
                }

                const { value: formValues } = await Swal.fire({
                    title: `حجز موقف في (${spotNameValue})`,
                    html: `
                        <div class="text-start mt-3">
                            <div class="form-check mb-3 p-3 bg-light rounded-3 border">
                                <input class="form-check-input ms-2" type="radio" name="bookingType" id="typeInitial" value="initial" checked onchange="toggleTimeInputs(false)">
                                <label class="form-check-label fw-bold text-primary" for="typeInitial">
                                    ⏱️ حجز مبدئي (مهلة 30 دقيقة للوصول)
                                </label>
                                <small class="d-block text-muted mt-1">يضمن لك مكاناً مؤقتاً لحين وصولك للموقع.</small>
                            </div>
                            
                            <div class="form-check mb-3 p-3 bg-light rounded-3 border">
                                <input class="form-check-input ms-2" type="radio" name="bookingType" id="typeActual" value="actual" onchange="toggleTimeInputs(true)">
                                <label class="form-check-label fw-bold text-success" for="typeActual">
                                    ✅ حجز فعلي (يتم الخصم من المحفظة)
                                </label>
                                <small class="d-block text-muted mt-1">تحديد وقت الدخول والخروج مسبقاً وتأكيد الدفع.</small>
                            </div>

                            <div id="actualTimeInputs" class="d-none bg-white p-3 rounded-3 border shadow-sm mt-2">
                                <label class="form-label small fw-bold text-secondary">وقت وتاريخ الدخول:</label>
                                <input type="datetime-local" id="swalStartTime" class="form-control mb-3 shadow-none">
                                <label class="form-label small fw-bold text-secondary">وقت وتاريخ الخروج:</label>
                                <input type="datetime-local" id="swalEndTime" class="form-control shadow-none">
                            </div>
                        </div>
                    `,
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonText: 'تأكيد الحجز 🚀',
                    cancelButtonText: 'تراجع',
                    confirmButtonColor: '#0071e3',
                    cancelButtonColor: '#ff3b30',
                    preConfirm: () => {
                        try {
                            const typeSelected = document.querySelector('input[name="bookingType"]:checked').value;
                            
                            if (typeSelected === 'actual') {
                                const startVal = document.getElementById('swalStartTime').value;
                                const endVal = document.getElementById('swalEndTime').value;
                                
                                if (!startVal || !endVal) {
                                    Swal.showValidationMessage('يرجى إدخال وقتي الدخول والخروج لإتمام الحجز الفعلي');
                                    return false;
                                }
                                if (new Date(startVal) >= new Date(endVal)) {
                                    Swal.showValidationMessage('وقت الخروج يجب أن يكون بعد وقت الدخول بشكل منطقي');
                                    return false;
                                }
                                return { type: 'actual', startTime: startVal, endTime: endVal };
                            }
                            return { type: 'initial' };
                        } catch (innerException) {
                            console.error(innerException);
                        }
                    }
                });

                if (formValues) {
                    executeBookingRequest(spotIdValue, formValues);
                }
            } catch (exception) {
                console.error("خطأ في نافذة الحجز", exception);
            }
        }

        async function executeBookingRequest(targetSpotIdValue, bookingDataValues) {
            try {
                Swal.fire({
                    title: 'جاري تسجيل الحجز...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                const payloadData = {
                    userId: currentUserData.accountId,
                    parkingId: targetSpotIdValue,
                    bookingType: bookingDataValues.type
                };

                if (bookingDataValues.type === 'actual') {
                    payloadData.startTime = bookingDataValues.startTime;
                    payloadData.endTime = bookingDataValues.endTime;
                }

                const response = await fetch('/api/bookings/create', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(payloadData)
                });

                const responseData = await response.json();

                if (response.ok && responseData.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'تم الحجز بنجاح! 🎟️',
                        text: bookingDataValues.type === 'initial' ? 'تم تأمين موقفك لـ 30 دقيقة القادمة.' : 'تم تأكيد حجزك الفعلي وخصم التكلفة.',
                        confirmButtonColor: '#0071e3'
                    }).then(() => {
                        fetchWalletBalance();
                        checkActiveTicketAndLoadGrid();
                    });
                } else {
                    throw new Error(responseData.message || 'تعذر إتمام عملية الحجز.');
                }
            } catch (exception) {
                Swal.fire({ icon: 'error', title: 'فشل الحجز', text: exception.message, confirmButtonColor: '#ff3b30' });
            }
        }

        async function loadParkingOptions() {
            try {
                const response = await fetch('/api/parkings/spots');
                const resultData = await response.json();
                const selectElement = document.getElementById('targetParkingSelect');
                
                if (selectElement && response.ok) {
                    selectElement.innerHTML = '<option value="" selected disabled>اختر الساحة التي حولت إليها...</option>';
                    resultData.data.forEach(parking => {
                        selectElement.innerHTML += `<option value="${parking.id}">${parking.name}</option>`;
                    });
                }
            } catch (exception) { console.error(exception); }
        }

        async function cancelCurrentBooking() {
            try {
                if (!activeBookingId) {
                    Swal.fire('خطأ', 'لم يتم التعرف على رقم الحجز النشط. يرجى تحديث الصفحة.', 'error');
                    return;
                }

                const { isConfirmed } = await Swal.fire({
                    title: 'تأكيد الإلغاء',
                    text: 'هل أنت متأكد من رغبتك في إلغاء الحجز؟ سيتم تطبيق سياسة الاسترجاع (100% قبل 30 دقيقة، 50% خلال الـ 30 دقيقة الأخيرة).',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'نعم، إلغاء الحجز',
                    cancelButtonText: 'تراجع',
                    confirmButtonColor: '#ff3b30',
                    cancelButtonColor: '#8e8e93'
                });

                if (!isConfirmed) return;

                Swal.fire({
                    title: 'جاري الإلغاء...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                const response = await fetch('/api/bookings/cancel', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ bookingId: activeBookingId })
                });

                const result = await response.json();

                if (response.ok && result.status === 'success') {
                    const ticketSection = document.getElementById('activeTicketSection');
                    if (ticketSection) {
                        ticketSection.style.setProperty('display', 'none', 'important');
                        ticketSection.classList.add('d-none');
                    }

                    Swal.fire('تم الإلغاء', result.message, 'success');
                    
                    activeBookingId = null;
                    checkActiveTicketAndLoadGrid();
                    fetchWalletBalance();
                } else {
                    Swal.fire('خطأ', result.message || 'حدث خطأ أثناء محاولة الإلغاء.', 'error');
                }
            } catch (exception) { 
                console.error("خطأ في الإلغاء:", exception); 
                Swal.fire('خطأ', 'تعذر الاتصال بالخادم.', 'error');
            }
        }

        async function requestChangeSpot() {
            try {
                const response = await fetch('/api/parkings/spots');
                const result = await response.json();
                
                let optionsHtml = '';
                result.data.forEach(p => {
                    if (p.available_capacity > 0) {
                        optionsHtml += `<option value="${p.id}">${p.name} (متاح: ${p.available_capacity})</option>`;
                    }
                });

                const { value: newParkingId } = await Swal.fire({
                    title: 'اختر الساحة البديلة',
                    html: `<select id="swalNewParking" class="form-select">${optionsHtml}</select>`,
                    showCancelButton: true,
                    confirmButtonText: 'تأكيد التبديل',
                    preConfirm: () => document.getElementById('swalNewParking').value
                });

                if (newParkingId) {
                    const res = await fetch('/api/bookings/change-spot', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ bookingId: activeBookingId, newParkingId: newParkingId })
                    });

                    if (res.ok) {
                        Swal.fire('نجاح', 'تم تبديل الموقف بنجاح.', 'success');
                        checkActiveTicketAndLoadGrid();
                    } else {
                        const err = await res.json();
                        Swal.fire('خطأ', err.message, 'error');
                    }
                }
            } catch (e) { console.error(e); }
        }

        function loadProfileData() {
            try {
                document.getElementById('profileNameDisplay').value = currentUserData.name;
                document.getElementById('profilePhoneInput').value = currentUserData.phone || '';
            } catch (exception) {
                console.error("خطأ في تحميل بيانات الملف الشخصي", exception);
            }
        }

        function logoutUser() {
            try {
                Swal.fire({
                    title: 'تسجيل الخروج',
                    text: "هل ترغب في مغادرة بوابة السائق؟",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#0071e3',
                    cancelButtonColor: '#8e8e93',
                    confirmButtonText: 'نعم، تسجيل الخروج',
                    cancelButtonText: 'إلغاء'
                }).then((result) => {
                    try {
                        if (result.isConfirmed) {
                            localStorage.clear();
                            window.location.href = '/login';
                        }
                    } catch (innerException) {
                        console.error(innerException);
                    }
                });
            } catch (exception) {
                console.error("خطأ أثناء تسجيل الخروج", exception);
            }
        }

        document.getElementById('profileForm').addEventListener('submit', async function(event) {
            try {
                event.preventDefault();
                
                const submitButtonElement = document.getElementById('updateProfileBtn');
                submitButtonElement.disabled = true;

                try {
                    const response = await fetch('/api/accounts/update-profile', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            accountId: currentUserData.accountId,
                            phone: document.getElementById('profilePhoneInput').value,
                            password: document.getElementById('profilePasswordInput').value
                        })
                    });

                    const resultData = await response.json();

                    if (response.ok) {
                        currentUserData.phone = resultData.updatedData.phone;
                        localStorage.setItem('userData', JSON.stringify(currentUserData));
                        Swal.fire('تم التحديث!', 'تم حفظ بيانات الاتصال وتأمين الحساب بنجاح.', 'success');
                    } else {
                        throw new Error(resultData.message);
                    }
                } catch (exception) {
                    Swal.fire('خطأ في التحديث', exception.message, 'error');
                } finally {
                    submitButtonElement.disabled = false;
                }
            } catch (exception) {
                console.error(exception);
            }
        });

        // --- FR4: سجل الإشعارات ---
        async function loadDashboardNotifications() {
            try {
                if (!currentUserData || !currentUserData.accountId) return;

                const response = await fetch('/api/notifications?userId=' + currentUserData.accountId, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' } 
                });
                
                const resultData = await response.json();
                const logContainer = document.getElementById('dashboardNotificationLog');

                if (logContainer && response.ok && resultData.status === 'success') {
                    logContainer.innerHTML = '';

                    if (resultData.data.length === 0) {
                        logContainer.innerHTML = '<div class="text-center py-5 text-muted small">لا توجد تنبيهات مسجلة في حسابك حالياً.</div>';
                        return;
                    }

                    resultData.data.slice(0, 10).forEach(item => {
                        try {
                            let icon = '📩';
                            let borderClass = 'border-start border-4 border-info';
                            
                            if (item.type.includes('Rejected') || item.type.includes('Expired') || item.type.includes('Blocked')) {
                                icon = '⚠️';
                                borderClass = 'border-start border-4 border-danger';
                            } else if (item.type.includes('Approved') || item.type.includes('Confirmed')) {
                                icon = '✅';
                                borderClass = 'border-start border-4 border-success';
                            }

                            const timeAgo = new Date(item.created_at).toLocaleString('ar-LY', {
                                month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
                            });

                            logContainer.innerHTML += `
                                <div class="list-group-item list-group-item-action p-3 ${borderClass} bg-white bg-opacity-50 shadow-sm mb-2 rounded-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="d-flex align-items-center">
                                            <span class="fs-4 me-3">${icon}</span>
                                            <div>
                                                <p class="mb-1 fw-bold text-dark-emphasis" style="font-size: 0.95rem;">${item.message}</p>
                                                <small class="text-muted" style="font-size: 0.8rem;">${timeAgo}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        } catch (e) { console.error(e); }
                    });
                }
            } catch (exception) {
                console.error("خطأ في جلب سجل التنبيهات", exception);
            }
        }

        // --- سجل الحجوزات والشحن المباشر ---
        function initializeHistoryYearSelect() {
            const yearSelect = document.getElementById('historyYearSelect');
            if (yearSelect && yearSelect.options.length === 0) {
                const currentYear = new Date().getFullYear();
                for (let y = currentYear; y >= currentYear - 5; y--) {
                    const opt = document.createElement('option');
                    opt.value = y;
                    opt.textContent = y;
                    yearSelect.appendChild(opt);
                }
            }
        }

        async function loadUserBookingHistory() {
            try {
                if (!currentUserData || !currentUserData.accountId) return;

                const monthVal = document.getElementById('historyMonthSelect').value;
                const yearVal = document.getElementById('historyYearSelect').value;

                let url = `/api/bookings/history?userId=${currentUserData.accountId}`;
                if (monthVal) url += `&month=${monthVal}`;
                if (yearVal) url += `&year=${yearVal}`;

                const response = await fetch(url);
                const resultData = await response.json();
                const tableBody = document.getElementById('bookingHistoryTableBody');

                if (tableBody && response.ok && resultData.status === 'success') {
                    tableBody.innerHTML = '';

                    if (resultData.data.length > 0) {
                        resultData.data.forEach(booking => {
                            let typeLabel = booking.type === 'initial' ? '⏱️ مبدئي' : '✅ فعلي';
                            let statusBadgeClass = 'bg-secondary';
                            let statusLabel = booking.status;

                            if (booking.status === 'Pending') {
                                statusBadgeClass = 'bg-warning text-dark';
                                statusLabel = 'قيد الانتظار';
                            } else if (booking.status === 'Active') {
                                statusBadgeClass = 'bg-success text-white';
                                statusLabel = 'نشط';
                            } else if (booking.status === 'Cancelled') {
                                statusBadgeClass = 'bg-danger text-white';
                                statusLabel = 'ملغي';
                            } else if (booking.status === 'Completed') {
                                statusBadgeClass = 'bg-info text-white';
                                statusLabel = 'مكتمل';
                            } else if (booking.status === 'Expired') {
                                statusBadgeClass = 'bg-dark text-white';
                                statusLabel = 'منتهي الصلاحية';
                            }

                            const startDate = booking.start_time 
                                ? new Date(booking.start_time).toLocaleString('ar-LY', { hour: '2-digit', minute: '2-digit', day: 'numeric', month: 'numeric' })
                                : '--';
                            const endDate = booking.end_time 
                                ? new Date(booking.end_time).toLocaleString('ar-LY', { hour: '2-digit', minute: '2-digit', day: 'numeric', month: 'numeric' })
                                : '--';
                            const createdAt = new Date(booking.created_at).toLocaleString('ar-LY', {
                                year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
                            });

                            tableBody.innerHTML += `
                                <tr>
                                    <td class="fw-bold">#${booking.id}</td>
                                    <td>${booking.parking_name}</td>
                                    <td class="fw-bold">${typeLabel}</td>
                                    <td class="small text-muted">${startDate}</td>
                                    <td class="small text-muted">${endDate}</td>
                                    <td><span class="badge ${statusBadgeClass} rounded-pill px-3 py-1">${statusLabel}</span></td>
                                    <td class="small text-muted">${createdAt}</td>
                                </tr>
                            `;
                        });
                    } else {
                        tableBody.innerHTML = '<tr><td colspan="7" class="text-muted py-5">لا توجد حجوزات تطابق خيارات التصفية المحددة.</td></tr>';
                    }
                }
            } catch (exception) {
                console.error("خطأ أثناء جلب سجل الحجوزات", exception);
            }
        }

        async function loadUserDirectRechargeInvoices() {
            try {
                if (!currentUserData || !currentUserData.accountId) return;

                const response = await fetch(`/api/recharges/invoices?userId=${currentUserData.accountId}`);
                const resultData = await response.json();
                const tableBody = document.getElementById('directRechargeInvoicesTableBody');

                if (tableBody && response.ok && resultData.status === 'success') {
                    tableBody.innerHTML = '';

                    if (resultData.data.length > 0) {
                        resultData.data.forEach(invoice => {
                            const dateValue = new Date(invoice.created_at).toLocaleString('ar-LY', {
                                year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
                            });

                            const invoiceJson = JSON.stringify(invoice).replace(/"/g, '&quot;');

                            tableBody.innerHTML += `
                                <tr>
                                    <td class="fw-bold text-dark">#${invoice.id}</td>
                                    <td>${invoice.parking_name}</td>
                                    <td>${invoice.employee_name}</td>
                                    <td class="fw-bold text-success">${invoice.cash_value} د.ل</td>
                                    <td class="small text-muted">${dateValue}</td>
                                    <td>
                                        <button onclick="showInvoiceDetail('${invoiceJson}')" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1">
                                            📄 عرض وتفاصيل
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        tableBody.innerHTML = '<tr><td colspan="6" class="text-muted py-5">لا توجد إيصالات شحن نقدي مباشر عند البوابة في سجلك.</td></tr>';
                    }
                }
            } catch (exception) {
                console.error("خطأ أثناء جلب فواتير الشحن المباشر", exception);
            }
        }

        window.showInvoiceDetail = function(invoiceJsonStr) {
            try {
                const invoice = JSON.parse(invoiceJsonStr.replace(/&quot;/g, '"'));
                const formattedDate = new Date(invoice.created_at).toLocaleString('ar-LY', {
                    year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
                });

                Swal.fire({
                    title: '🧾 إيصال شحن رصيد نقدي (ضمان مالي)',
                    html: `
                        <div id="printable-receipt" style="font-family: 'Courier New', Courier, monospace; border: 1px dashed #ccc; padding: 20px; background-color: #fff; text-align: right; direction: rtl;">
                            <div style="text-align: center; margin-bottom: 15px;">
                                <h4 style="margin: 0; font-weight: bold; color: #1a1a1a;">🚗 تطبيق SpotLy</h4>
                                <p style="margin: 5px 0; font-size: 0.85rem; color: #666;">إيصال شحن رصيد بوابة (نقدي)</p>
                                <p style="margin: 0; font-size: 0.85rem; color: #666;">--------------------------------</p>
                            </div>
                            <div style="font-size: 0.9rem; line-height: 1.6; color: #333;">
                                <p style="margin: 5px 0;"><b>رقم الفاتورة:</b> <span style="font-weight: bold;">#${invoice.id}</span></p>
                                <p style="margin: 5px 0;"><b>الساحة / الموقف:</b> ${invoice.parking_name}</p>
                                <p style="margin: 5px 0;"><b>الموظف المسؤول:</b> ${invoice.employee_name}</p>
                                <p style="margin: 5px 0;"><b>رقم لوحة المركبة:</b> ${invoice.plate_number || '--'}</p>
                                <p style="margin: 5px 0;"><b>تاريخ العملية:</b> ${formattedDate}</p>
                                <p style="margin: 0; font-size: 0.85rem; color: #666;">--------------------------------</p>
                                <div style="text-align: center; margin-top: 15px; margin-bottom: 10px; background-color: #f9f9f9; padding: 10px; border-radius: 5px;">
                                    <h5 style="margin: 0; color: #2ecc71; font-weight: bold; font-size: 1.15rem;">
                                        القيمة المستلمة: ${invoice.cash_value} د.ل
                                    </h5>
                                </div>
                                <p style="margin: 0; font-size: 0.85rem; color: #666; text-align: center;">شكراً لاستخدامكم SpotLy!</p>
                            </div>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: '🖨️ طباعة الإيصال',
                    cancelButtonText: 'إغلاق',
                    confirmButtonColor: '#27ae60',
                    cancelButtonColor: '#7f8c8d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const printWindow = window.open('', '_blank');
                        printWindow.document.write(`
                            <html>
                            <head>
                                <title>طباعة إيصال شحن #${invoice.id}</title>
                                <style>
                                    body { font-family: sans-serif; direction: rtl; text-align: right; padding: 20px; }
                                    #printable-receipt { border: 1px dashed #ccc; padding: 20px; max-width: 400px; margin: auto; }
                                    h3 { margin: 0; }
                                    @media print {
                                        body { padding: 0; }
                                        #printable-receipt { border: none; max-width: 100%; }
                                    }
                                </style>
                            </head>
                            <body onload="window.print(); window.close();">
                                <div id="printable-receipt">
                                    <div style="text-align: center; margin-bottom: 15px;">
                                        <h3 style="margin: 0;">🚗 تطبيق SpotLy</h3>
                                        <p style="margin: 5px 0; font-size: 0.85rem;">إيصال شحن رصيد بوابة (نقدي)</p>
                                        <p style="margin: 0;">--------------------------------</p>
                                    </div>
                                    <div style="font-size: 0.9rem; line-height: 1.6;">
                                        <p style="margin: 5px 0;"><b>رقم الفاتورة:</b> #${invoice.id}</p>
                                        <p style="margin: 5px 0;"><b>الساحة / الموقف:</b> ${invoice.parking_name}</p>
                                        <p style="margin: 5px 0;"><b>الموظف المسؤول:</b> ${invoice.employee_name}</p>
                                        <p style="margin: 5px 0;"><b>رقم لوحة المركبة:</b> ${invoice.plate_number || '--'}</p>
                                        <p style="margin: 5px 0;"><b>تاريخ العملية:</b> ${formattedDate}</p>
                                        <p style="margin: 0;">--------------------------------</p>
                                        <div style="text-align: center; margin-top: 15px; margin-bottom: 10px; background-color: #f9f9f9; padding: 10px; border-radius: 5px;">
                                            <h4 style="margin: 0; color: #2ecc71;">
                                                القيمة المستلمة: ${invoice.cash_value} د.ل
                                            </h4>
                                        </div>
                                        <p style="margin: 0; text-align: center;">شكراً لاستخدامكم SpotLy!</p>
                                    </div>
                                </div>
                            </body>
                            </html>
                        `);
                        printWindow.document.close();
                    }
                });
            } catch (err) {
                console.error(err);
                Swal.fire('خطأ', 'فشل في تحميل تفاصيل الإيصال.', 'error');
            }
        }

        setInterval(async () => {
            try {
                refreshDriverStats();

                const response = await fetch('/api/bookings/cleanup-expired', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' }
                });
                
                const result = await response.json();
                
                if (response.ok && result.message.includes('معالجة') && !result.message.includes('0')) {
                    checkActiveTicketAndLoadGrid();
                }
            } catch (exception) {}
        }, 60000);
    </script>
</body>
</html>