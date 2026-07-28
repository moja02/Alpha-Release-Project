<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpotLy - لوحة تحكم المدير</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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
            text-decoration: none;
        }

        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            opacity: 1;
            background: rgba(255, 255, 255, 0.2);
            box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.4), 0 4px 10px rgba(0, 0, 0, 0.03);
            font-weight: 600;
            transform: translateX(-4px);
            color: var(--text-color);
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

        .stat-card-value {
            font-size: 2.2rem;
            font-weight: 800;
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
        #map {
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
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(0, 113, 227, 0.3);
            color: white;
        }

        /* Badges */
        .badge {
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 30px;
        }

        /* Timeline Audit */
        .timeline-container {
            position: relative;
            padding-right: 30px;
            border-right: 3px solid var(--glass-border);
        }
        .timeline-item {
            position: relative;
            margin-bottom: 25px;
        }
        .timeline-badge {
            position: absolute;
            right: -46px;
            top: 5px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.1rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            z-index: 2;
        }
        .bg-entry { background: linear-gradient(135deg, #2ecc71, #27ae60); }
        .bg-exit { background: linear-gradient(135deg, #e74c3c, #c0392b); }
        .bg-recharge { background: linear-gradient(135deg, #f1c40f, #f39c12); }

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
            <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold">بوابة المدير</span>
        </div>
        <hr class="border-secondary border-opacity-25 my-3">
        <nav class="nav flex-column flex-grow-1">
            <a class="nav-link active" onclick="switchTab('overviewTab', this)"><i class="fas fa-home"></i> نظرة عامة</a>
            <a class="nav-link" onclick="switchTab('parkingsTab', this)"><i class="fas fa-list"></i> الساحات المدارة</a>
            <a class="nav-link" onclick="switchTab('aiAssistantTab', this)"><i class="fas fa-robot me-1"></i> AI Assistant</a>
            <a class="nav-link" onclick="switchTab('violationsTab', this)"><i class="fas fa-ban"></i> إدارة الحظر والمخالفات</a>
            <a class="nav-link" onclick="switchTab('financialReportTab', this)"><i class="fas fa-chart-bar"></i> التقرير المالي</a>
            <a class="nav-link" onclick="switchTab('profileTab', this)"><i class="fas fa-user-cog"></i> البيانات الشخصية</a>
            <a class="nav-link" onclick="switchTab('auditLogTab', this)"><i class="fas fa-history"></i> سجل العمليات والتدقيق</a>
            <a class="nav-link" onclick="switchTab('shiftsTab', this)"><i class="fas fa-clock"></i> سجل مناوبات الموظفين</a>
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
                <span id="managerNameDisplay" class="fw-bold text-dark-emphasis">مرحباً، مدير النظام</span>
                <button onclick="logoutManager()" class="btn btn-sm btn-outline-danger px-4 rounded-pill">تسجيل الخروج</button>
            </div>
        </header>

        <!-- SECTION 1: Overview -->
        <section id="overviewTab" class="content-section">
            <div class="alert alert-primary border-0 shadow-sm rounded-4 p-4 mb-4">
                <h5>👋 أهلاً بك مجدداً في لوحة تحكم المدير!</h5>
                <p class="mb-0 text-muted">تتيح لك هذه المنصة الإشراف الكامل على الساحات المسندة إليك، مراقبة مستويات الإشغال والشاغر بشكل لحظي، وإدارة طاقم الموظفين وتعيينهم للفترات الصباحية والمسائية.</p>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-12">
                    <div class="card p-4 shadow-sm border-0 rounded-4">
                        <h5 class="fw-bold mb-3">📌 إرشادات تشغيلية سريعة</h5>
                        <ul class="mb-0">
                            <li class="mb-2">يمكنك إضافة وإنشاء حسابات لأكثر من موظف لساحة المواقف الواحدة وتوزيعهم بين <strong>الفترة الصباحية</strong> و<strong>الفترة المسائية</strong> عبر تبويب <strong>الساحات المدارة</strong>.</li>
                            <li class="mb-0">تأكد من تحديث أرقام هواتف وتفاصيل الموظفين الميدانيين لتسهيل التواصل الميداني وإرسال بيانات الدخول لبريدهم.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION 2: Managed Parkings -->
        <section id="parkingsTab" class="content-section d-none">
            <div class="card mb-4">
                <div class="card-header bg-gradient p-4 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-list"></i> قائمة ساحات مواقف السيارات المدارة</h5>
                    <span class="badge bg-primary px-3 py-2 rounded-pill">عدد الساحات المدارة: {{ count($parkingsList) }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-center mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>اسم الساحة</th>
                                    <th>وصف الموقع</th>
                                    <th>السعة الكلية</th>
                                    <th>الشاغرة حالياً</th>
                                    <th>طاقم الموظفين الميدانيين والورديات</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($parkingsList as $parking)
                                    <tr>
                                        <td class="fw-bold text-dark-emphasis">{{ $parking->parking_name }}</td>
                                        <td>{{ $parking->location_park }}</td>
                                        <td><span class="badge bg-secondary px-3 py-1">{{ $parking->total_capacity }}</span></td>
                                        <td>
                                            @if($parking->available_capacity > 0)
                                                <span class="badge bg-success px-3 py-1">{{ $parking->available_capacity }}</span>
                                            @else
                                                <span class="badge bg-danger px-3 py-1">ممتلئة بالكامل</span>
                                            @endif
                                        </td>
                                        <td style="min-width: 280px;">
                                            @php
                                                $morningStaff = isset($parking->staff) ? $parking->staff->where('shift_role', 'الوردية الصباحية')->first() : null;
                                                $eveningStaff = isset($parking->staff) ? $parking->staff->where('shift_role', 'الوردية المسائية')->first() : null;
                                            @endphp
                                            
                                            <!-- Morning Shift Employee -->
                                            <div class="mb-2 text-start bg-light p-2 rounded-3 border">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <span class="badge bg-warning text-dark font-normal"><i class="fas fa-sun me-1"></i> الوردية الصباحية</span>
                                                    @if($morningStaff)
                                                        <button onclick="unlinkEmployeeFromStaff({{ $morningStaff->employee_id }})" class="btn btn-sm text-danger p-0" title="حذف موظف الوردية الصباحية"><i class="fas fa-times-circle"></i></button>
                                                    @endif
                                                </div>
                                                @if($morningStaff)
                                                    <strong class="text-dark d-block mt-1">{{ $morningStaff->employee_name }}</strong>
                                                    <small class="text-muted" dir="ltr">{{ $morningStaff->employee_phone }}</small>
                                                @else
                                                    <span class="small text-muted d-block mt-1">غير معين</span>
                                                    <button onclick="openCreateEmployeeModal({{ $parking->id }}, '{{ addslashes($parking->parking_name) }}', 'الوردية الصباحية')" class="btn btn-sm btn-outline-warning text-dark w-100 rounded-3 mt-1 py-1 font-normal">
                                                        + إضافة موظف للوردية الصباحية
                                                    </button>
                                                @endif
                                            </div>

                                            <!-- Evening Shift Employee -->
                                            <div class="mb-2 text-start bg-light p-2 rounded-3 border">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <span class="badge bg-dark text-white font-normal"><i class="fas fa-moon me-1"></i> الوردية المسائية</span>
                                                    @if($eveningStaff)
                                                        <button onclick="unlinkEmployeeFromStaff({{ $eveningStaff->employee_id }})" class="btn btn-sm text-danger p-0" title="حذف موظف الوردية المسائية"><i class="fas fa-times-circle"></i></button>
                                                    @endif
                                                </div>
                                                @if($eveningStaff)
                                                    <strong class="text-dark d-block mt-1">{{ $eveningStaff->employee_name }}</strong>
                                                    <small class="text-muted" dir="ltr">{{ $eveningStaff->employee_phone }}</small>
                                                @else
                                                    <span class="small text-muted d-block mt-1">غير معين</span>
                                                    <button onclick="openCreateEmployeeModal({{ $parking->id }}, '{{ addslashes($parking->parking_name) }}', 'الوردية المسائية')" class="btn btn-sm btn-outline-dark text-dark w-100 rounded-3 mt-1 py-1 font-normal">
                                                        + إضافة موظف للوردية المسائية
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-1">
                                                @if($parking->latitude && $parking->longitude)
                                                    <button onclick="focusParkingOnMap({{ $parking->latitude }}, {{ $parking->longitude }}, '{{ addslashes($parking->parking_name) }}')" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                        📍 الخريطة
                                                    </button>
                                                @endif
                                                @if($parking->employee_id)
                                                    <button onclick="confirmUnlinkEmployee({{ $parking->id }}, '{{ addslashes($parking->parking_name) }}', '{{ addslashes($parking->employee_name ?? 'الموظف') }}')" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                        🔓 إلغاء ربط الموظف
                                                    </button>
                                                @else
                                                    <button onclick="openCreateEmployeeModal({{ $parking->id }}, '{{ addslashes($parking->parking_name) }}')" class="btn btn-sm btn-primary rounded-pill px-3">
                                                        👤 إنشاء حساب موظف
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-muted py-5">لا توجد ساحات مسجلة باسمك حالياً.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Managed Parkings Interactive Leaflet Map Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header p-4 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-map-marked-alt"></i> خريطة مواقع الساحات المدارة الخاصة بك</h5>
                    <small class="text-muted">تعرض الخريطة الساحات التابعة لحسابك كمدير فقط</small>
                </div>
                <div class="card-body p-4">
                    <div id="managedParkingsMap" style="height: 420px; width: 100%; border-radius: 16px; border: 1px solid var(--glass-border); z-index: 1;"></div>
                </div>
            </div>
        </section>

        <!-- SECTION: AI Assistant -->
        <section id="aiAssistantTab" class="content-section d-none">
            <!-- Hero / Header Card -->
            <div class="card border-0 mb-4 text-white shadow-lg overflow-hidden position-relative" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4338ca 100%); border-radius: 20px;">
                <div class="card-body p-4 p-lg-5 position-relative" style="z-index: 2;">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold" style="font-size: 0.85rem;">
                            ✨ AI Powered Insights • Autonomous Analytics
                        </span>
                        <span class="badge bg-white bg-opacity-20 text-white px-3 py-2 rounded-pill fw-bold" style="font-size: 0.85rem;">
                            v2.4 Neural Model
                        </span>
                    </div>
                    <h2 class="fw-bold text-white mb-2 display-6">AI Assistant & Financial Intelligence</h2>
                    <p class="text-white-50 mb-0 max-w-2xl fs-6">
                        Automated predictive analysis, revenue trajectory forecasting, expense optimization, and data-driven branch recommendations for SpotLy parking network.
                    </p>
                </div>
            </div>

            <!-- AI Financial Analyst Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 p-lg-5 mb-4 position-relative" style="background: var(--card-bg, #ffffff); border: 1px solid var(--glass-border, rgba(255,255,255,0.15)) !important;">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-3 border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-4 p-3 d-flex align-items-center justify-content-center text-white" style="width: 56px; height: 56px; background: linear-gradient(135deg, #6366f1, #8b5cf6); box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);">
                            <i class="fas fa-brain fs-3"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-1 text-dark">AI Financial Analyst</h4>
                            <p class="text-muted mb-0 small">Generate comprehensive AI-driven performance audit reports and financial strategic recommendations</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <select id="managerAiReportScopeSelect" class="form-select form-select-sm rounded-pill px-3 shadow-none border-secondary-subtle">
                            <option value="all">Scope: All Assigned Yards</option>
                            <option value="top">Scope: High Capacity Yards</option>
                        </select>
                        <select id="managerAiReportTimeframeSelect" class="form-select form-select-sm rounded-pill px-3 shadow-none border-secondary-subtle">
                            <option value="current_quarter">Timeframe: Current Quarter (Q3 2026)</option>
                            <option value="last_30_days">Timeframe: Last 30 Days</option>
                            <option value="year_to_date">Timeframe: Year to Date (YTD)</option>
                        </select>
                    </div>
                </div>

                <div class="text-center py-4 bg-light rounded-4 border p-4 mb-4">
                    <h5 class="fw-bold text-dark mb-2">Ready to Synthesize Financial Intelligence</h5>
                    <p class="text-muted small mb-4 max-w-xl mx-auto">
                        Click the button below to query live database metrics, calculate total revenue, expenses, net margins, occupancy rates, and generate strategic recommendations.
                    </p>
                    <button id="generateManagerAiReportBtn" class="btn btn-lg btn-primary rounded-pill px-5 py-3 fw-bold shadow-lg text-white" onclick="generateManagerAiReport()">
                        <i class="fas fa-wand-magic-sparkles me-2"></i> <span>Generate AI Report</span>
                    </button>
                </div>

                <!-- Loading State Spinner (Hidden by default) -->
                <div id="aiManagerReportLoadingState" class="d-none text-center py-5">
                    <div class="spinner-border text-indigo mb-3" style="width: 3rem; height: 3rem; color: #6366f1;" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Synthesizing Real Database Intelligence...</h5>
                    <p class="text-muted small mb-0">Querying transactions, calculating yields, and generating recommendations...</p>
                </div>

                <!-- AI Chat Assistant (ChatGPT-like Interface) -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden" style="background: var(--card-bg, #ffffff); border: 1px solid var(--glass-border, rgba(255,255,255,0.15)) !important;">
                    <div class="p-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle p-2 d-flex align-items-center justify-content-center text-white" style="width: 44px; height: 44px; background: linear-gradient(135deg, #3b82f6, #8b5cf6);">
                                <i class="fas fa-comments fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-white mb-0">SpotLy AI Chat Assistant</h5>
                                <span class="badge bg-success bg-opacity-20 text-success px-2 py-1 rounded-pill small">🟢 Live Intelligence • Ask in Arabic / English</span>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-light rounded-pill px-3" onclick="clearAiChatHistory('manager')">
                                <i class="fas fa-trash-alt me-1"></i> Clear Chat
                            </button>
                        </div>
                    </div>

                    <!-- Chat Messages Container -->
                    <div class="card-body p-4" style="background: #f8fafc; min-height: 380px; max-height: 480px; overflow-y: auto;" id="managerAiChatMessagesContainer">
                        <!-- Welcome AI Message -->
                        <div class="d-flex gap-3 mb-4">
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0" style="width: 38px; height: 38px; background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                                <i class="fas fa-robot small"></i>
                            </div>
                            <div class="p-3 rounded-4 bg-white shadow-sm border border-slate-200 text-dark max-w-xl">
                                <p class="mb-2 fw-bold text-indigo" style="color: #4338ca;">👋 أهلاً بك! أنا المساعد الذكي لنظام SpotLy</p>
                                <p class="mb-2 small leading-relaxed">
                                    يمكنك سؤالي باللغة العربية أو الإنجليزية عن أداء الساحات، الإيرادات المباشرة، نسب الإشغال، الساحات المتصدرة، أو طلب تحليلات المصروفات وتوصيات التحسين.
                                </p>
                                <span class="text-muted x-small">System Assistant • Just now</span>
                            </div>
                        </div>
                    </div>

                    <!-- Suggested Question Chips -->
                    <div class="px-4 py-2 bg-light border-top border-bottom">
                        <span class="text-muted x-small fw-bold d-block mb-2"><i class="fas fa-lightbulb me-1 text-warning"></i> أسئلة مقترحة وسريعة (Suggested Questions):</span>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-xs btn-outline-primary rounded-pill bg-white text-dark small py-1 px-3 shadow-none border" onclick="sendSuggestedQuestion('💡 كم إجمالي الإيرادات هذا الشهر؟', 'manager')">💡 كم إجمالي الإيرادات هذا الشهر؟</button>
                            <button class="btn btn-xs btn-outline-primary rounded-pill bg-white text-dark small py-1 px-3 shadow-none border" onclick="sendSuggestedQuestion('🅿️ ما هو معدل الإشغال اليوم؟', 'manager')">🅿️ ما هو معدل الإشغال اليوم؟</button>
                            <button class="btn btn-xs btn-outline-primary rounded-pill bg-white text-dark small py-1 px-3 shadow-none border" onclick="sendSuggestedQuestion('🏆 ما هي الساحة الأعلى أداءً؟', 'manager')">🏆 ما هي الساحة الأعلى أداءً؟</button>
                            <button class="btn btn-xs btn-outline-primary rounded-pill bg-white text-dark small py-1 px-3 shadow-none border" onclick="sendSuggestedQuestion('🎯 ما هي الساحة التي تحتاج إلى تحسين؟', 'manager')">🎯 ما هي الساحة التي تحتاج إلى تحسين؟</button>
                            <button class="btn btn-xs btn-outline-primary rounded-pill bg-white text-dark small py-1 px-3 shadow-none border" onclick="sendSuggestedQuestion('📊 اعرض تحليل الأرباح والمصروفات', 'manager')">📊 اعرض تحليل الأرباح والمصروفات</button>
                        </div>
                    </div>

                    <!-- Typing Indicator -->
                    <div id="managerAiChatTypingIndicator" class="d-none px-4 py-2 bg-white text-muted small border-top">
                        <span class="spinner-grow spinner-grow-sm me-2 text-indigo" role="status" style="color: #6366f1;"></span>
                        <em>SpotLy AI is querying live database analytics...</em>
                    </div>

                    <!-- Chat Input Controls -->
                    <div class="p-3 bg-white border-top">
                        <form id="managerAiChatForm" onsubmit="handleAiChatSubmit(event, 'manager')" class="d-flex gap-2 align-items-center">
                            <input type="text" id="managerAiChatInput" class="form-control rounded-pill px-4 py-2 shadow-none border" placeholder="اسأل المساعد الذكي بأي سؤال... (Ask AI anything in Arabic or English)" autocomplete="off">
                            <button type="submit" id="managerAiChatSendBtn" class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                                <i class="fas fa-paper-plane text-white"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- AI Report Display Component -->
                <div id="aiManagerReportDisplayComponent" class="d-none mt-2">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: #f8fafc; border: 1px solid #e2e8f0 !important;">
                        
                        <!-- Report Top Header Banner -->
                        <div class="p-4 bg-white border-bottom d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-indigo-subtle text-indigo px-3 py-1 rounded-pill fw-bold" style="background: #e0e7ff; color: #4338ca;">
                                        🤖 AI-Generated Report
                                    </span>
                                    <span class="text-muted small">Generated on: <strong id="managerReportTimestampDisplay">July 27, 2026 - 11:55 AM</strong></span>
                                </div>
                                <h4 class="fw-bold text-dark mb-0">SpotLy Comprehensive Executive & Financial AI Audit Report</h4>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="window.print()">
                                    <i class="fas fa-print me-1"></i> Print / PDF
                                </button>
                            </div>
                        </div>

                        <div class="card-body p-4 p-lg-5">

                            <!-- SECTION 1: Executive Summary -->
                            <div class="mb-5">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="badge bg-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="fas fa-chart-line text-white"></i>
                                    </span>
                                    <h5 class="fw-bold text-dark mb-0">1. Executive Summary (الملخص التنفيذي)</h5>
                                </div>
                                <div class="card border-0 p-4 rounded-4 shadow-sm mb-4" style="background: #ffffff;">
                                    <p id="managerAiExecutiveSummaryText" class="text-dark leading-relaxed mb-4 fs-6">
                                        Loading live database analytics...
                                    </p>
                                    <div class="row g-3 text-center">
                                        <div class="col-6 col-md-3">
                                            <div class="p-3 rounded-4 bg-light border">
                                                <span class="text-muted small d-block mb-1">Total Net Revenue</span>
                                                <h4 id="managerAiTotalNetRevenueVal" class="fw-bold text-success mb-0">0.00 <small class="fs-6">LYD</small></h4>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="p-3 rounded-4 bg-light border">
                                                <span class="text-muted small d-block mb-1">Gross Profit Margin</span>
                                                <h4 id="managerAiGrossMarginVal" class="fw-bold text-primary mb-0">0.0%</h4>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="p-3 rounded-4 bg-light border">
                                                <span class="text-muted small d-block mb-1">Avg Occupancy Index</span>
                                                <h4 id="managerAiOccupancyRateVal" class="fw-bold text-warning text-dark mb-0">0.0%</h4>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="p-3 rounded-4 bg-light border">
                                                <span class="text-muted small d-block mb-1">Health Score</span>
                                                <h4 id="managerAiHealthScoreVal" class="fw-bold text-indigo mb-0" style="color: #6366f1;">0 / 100</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION 2: Revenue Performance -->
                            <div class="mb-5">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="badge bg-success rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="fas fa-coins text-white"></i>
                                    </span>
                                    <h5 class="fw-bold text-dark mb-0">2. Revenue Performance (تحليل الإيرادات)</h5>
                                </div>
                                <div class="card border-0 p-4 rounded-4 shadow-sm mb-4" style="background: #ffffff;">
                                    <div class="row g-4 align-items-center">
                                        <div class="col-md-7">
                                            <h6 class="fw-bold text-secondary mb-2">Revenue Streams Breakdown</h6>
                                            <p class="text-muted small mb-3">Real-time revenue sources aggregated from database booking costs, cashier cash logs, and recharge points.</p>
                                            
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between small fw-bold mb-1">
                                                    <span>App Spot Reservations (الترشيح الذكي)</span>
                                                    <span class="text-primary">57% Share</span>
                                                </div>
                                                <div class="progress rounded-pill" style="height: 10px;">
                                                    <div class="progress-bar bg-primary" role="progressbar" style="width: 57%"></div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between small fw-bold mb-1">
                                                    <span>On-site Cashier & Gate Collection (كشك التحصيل)</span>
                                                    <span class="text-success">27.5% Share</span>
                                                </div>
                                                <div class="progress rounded-pill" style="height: 10px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: 27.5%"></div>
                                                </div>
                                            </div>

                                            <div class="mb-0">
                                                <div class="d-flex justify-content-between small fw-bold mb-1">
                                                    <span>Digital Wallet Pre-charges & Subscriptions</span>
                                                    <span class="text-purple" style="color: #8b5cf6;">15.5% Share</span>
                                                </div>
                                                <div class="progress rounded-pill" style="height: 10px;">
                                                    <div class="progress-bar" role="progressbar" style="width: 15.5%; background: #8b5cf6;"></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-5 border-start">
                                            <div class="p-3 bg-light rounded-4">
                                                <h6 class="fw-bold text-dark mb-3">Key Performance Metrics</h6>
                                                <ul class="list-group list-group-flush bg-transparent">
                                                    <li class="list-group-item bg-transparent d-flex justify-content-between px-0 py-2 small">
                                                        <span class="text-muted">Total Reservations Count:</span>
                                                        <strong id="managerAiTotalReservationsVal" class="text-dark">0 Bookings</strong>
                                                    </li>
                                                    <li class="list-group-item bg-transparent d-flex justify-content-between px-0 py-2 small">
                                                        <span class="text-muted">Peak Hours Yield Multiplier:</span>
                                                        <strong class="text-success">+34% vs Off-Peak</strong>
                                                    </li>
                                                    <li class="list-group-item bg-transparent d-flex justify-content-between px-0 py-2 small">
                                                        <span class="text-muted">Digital Wallet Penetration:</span>
                                                        <strong class="text-indigo" style="color: #6366f1;">Active</strong>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION 3: Expense Analysis -->
                            <div class="mb-5">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="badge bg-danger rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="fas fa-receipt text-white"></i>
                                    </span>
                                    <h5 class="fw-bold text-dark mb-0">3. Expense Analysis (تحليل المصروفات التشغيلية)</h5>
                                </div>
                                <div class="card border-0 p-4 rounded-4 shadow-sm mb-4" style="background: #ffffff;">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <h6 class="fw-bold text-secondary mb-3">Operational Cost Allocation</h6>
                                            <ul class="list-unstyled mb-0">
                                                <li class="d-flex align-items-center justify-content-between p-3 rounded-3 mb-2 bg-light">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="badge bg-warning text-dark rounded-circle">👥</span>
                                                        <span class="fw-bold text-dark small">Shift Cashiers & Staff Payroll</span>
                                                    </div>
                                                    <span class="fw-bold text-danger">62% of expenses</span>
                                                </li>
                                                <li class="d-flex align-items-center justify-content-between p-3 rounded-3 mb-2 bg-light">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="badge bg-info text-dark rounded-circle">⚙️</span>
                                                        <span class="fw-bold text-dark small">Hardware & Gate Systems Maintenance</span>
                                                    </div>
                                                    <span class="fw-bold text-danger">22% of expenses</span>
                                                </li>
                                                <li class="d-flex align-items-center justify-content-between p-3 rounded-3 mb-0 bg-light">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="badge bg-secondary text-white rounded-circle">⚡</span>
                                                        <span class="fw-bold text-dark small">Utilities, Connectivity & Overhead</span>
                                                    </div>
                                                    <span class="fw-bold text-danger">16% of expenses</span>
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="h-100 p-4 rounded-4 bg-danger bg-opacity-10 border border-danger-subtle d-flex flex-column justify-content-center">
                                                <h6 class="fw-bold text-danger mb-2"><i class="fas fa-shield-halved me-1"></i> Expense & Net Margin Breakdown</h6>
                                                <div class="d-flex align-items-center justify-content-between bg-white p-3 rounded-3 border mb-2">
                                                    <span class="fw-bold text-secondary small">Total Operational Expenses:</span>
                                                    <h5 id="managerAiTotalExpensesVal" class="fw-bold text-danger mb-0">0.00 LYD</h5>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between bg-white p-3 rounded-3 border">
                                                    <span class="fw-bold text-secondary small">Net Profit Retained:</span>
                                                    <h4 id="managerAiNetProfitVal" class="fw-bold text-success mb-0">0.00 LYD</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION 4 & 5: Top & Lowest Performing Branches Row -->
                            <div class="row g-4 mb-5">
                                <!-- SECTION 4: Top Performing Branch -->
                                <div class="col-md-6">
                                    <div class="card border-0 p-4 rounded-4 shadow-sm h-100 border-top border-4 border-success" style="background: #ffffff;">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-success rounded-circle p-2">🥇</span>
                                                <h5 class="fw-bold text-dark mb-0">4. Top Performing Branch</h5>
                                            </div>
                                            <span class="badge bg-warning text-dark fw-bold">Rank #1</span>
                                        </div>
                                        <div class="p-3 bg-success bg-opacity-10 rounded-3 mb-3 border border-success-subtle">
                                            <h6 id="managerAiTopBranchName" class="fw-bold text-success mb-1">Loading...</h6>
                                            <span class="text-muted small">Highest gross revenue and capacity utilization</span>
                                        </div>
                                        <div class="row g-2 text-center">
                                            <div class="col-6">
                                                <div class="p-2 bg-light rounded border">
                                                    <span class="text-muted x-small d-block">Gross Revenue</span>
                                                    <strong id="managerAiTopBranchRevenue" class="text-primary">0.00 LYD</strong>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="p-2 bg-light rounded border">
                                                    <span class="text-muted x-small d-block">Occupancy Rate</span>
                                                    <strong id="managerAiTopBranchOccupancy" class="text-success">0%</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- SECTION 5: Lowest Performing Branch -->
                                <div class="col-md-6">
                                    <div class="card border-0 p-4 rounded-4 shadow-sm h-100 border-top border-4 border-warning" style="background: #ffffff;">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-warning text-dark rounded-circle p-2">🎯</span>
                                                <h5 class="fw-bold text-dark mb-0">5. Lowest Performing Branch</h5>
                                            </div>
                                            <span class="badge bg-warning text-dark fw-bold">Optimization Needed</span>
                                        </div>
                                        <div class="p-3 bg-warning bg-opacity-10 rounded-3 mb-3 border border-warning-subtle">
                                            <h6 id="managerAiLowestBranchName" class="fw-bold text-dark mb-1">Loading...</h6>
                                            <span class="text-muted small">Candidate for promotional pricing and dynamic campaign boost</span>
                                        </div>
                                        <div class="row g-2 text-center">
                                            <div class="col-6">
                                                <div class="p-2 bg-light rounded border">
                                                    <span class="text-muted x-small d-block">Gross Revenue</span>
                                                    <strong id="managerAiLowestBranchRevenue" class="text-secondary">0.00 LYD</strong>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="p-2 bg-light rounded border">
                                                    <span class="text-muted x-small d-block">Occupancy Rate</span>
                                                    <strong id="managerAiLowestBranchOccupancy" class="text-warning text-dark">0%</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION 6: Occupancy Analysis & Branch Statistics Table -->
                            <div class="mb-5">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="badge bg-info text-dark rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="fas fa-building text-white"></i>
                                    </span>
                                    <h5 class="fw-bold text-dark mb-0">6. Occupancy Analysis & Branch Breakdown (تحليل معدل الإشغال والساحات)</h5>
                                </div>
                                <div class="card border-0 p-4 rounded-4 shadow-sm mb-4" style="background: #ffffff;">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle text-center mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-start">Branch / Yard Name</th>
                                                    <th>Total Capacity</th>
                                                    <th>Occupancy Rate</th>
                                                    <th>Gross Revenue</th>
                                                    <th>Net Profit</th>
                                                    <th>Performance Rank</th>
                                                </tr>
                                            </thead>
                                            <tbody id="managerAiBranchStatsTableBody">
                                                <tr><td colspan="6" class="text-muted py-3">Loading real system database statistics...</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION 7: Strategic Recommendations -->
                            <div class="mb-0">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="badge bg-warning text-dark rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="fas fa-lightbulb text-dark"></i>
                                    </span>
                                    <h5 class="fw-bold text-dark mb-0">7. AI Strategic Recommendations (التوصيات والاستراتيجيات الذكية)</h5>
                                </div>
                                <div id="managerAiRecommendationsContainer" class="row g-3">
                                    <div class="col-12 text-muted">Generating real business intelligence recommendations...</div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Create Employee Account Modal -->
        <div class="modal fade" id="createEmployeeModal" tabindex="-1" aria-labelledby="createEmployeeModalLabel" aria-hidden="true" dir="rtl">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 shadow" style="background: var(--glass-bg); backdrop-filter: blur(25px); border: 1px solid var(--glass-border);">
                    <div class="modal-header border-0 py-3 rounded-top-4">
                        <h5 class="modal-title fw-bold text-dark-emphasis" id="createEmployeeModalLabel">👤 إنشاء حساب موظف ميداني جديد</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <form id="createEmployeeForm">
                            <input type="hidden" id="modalParkingId">
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold text-secondary">الساحة المستهدفة للتعيين</label>
                                <input type="text" class="form-control bg-light text-muted border-0" id="modalParkingName" readonly>
                            </div>

                            <div class="mb-4 bg-light bg-opacity-25 p-3 rounded-3 border">
                                <label class="form-label fw-bold text-secondary mb-2">طريقة التعيين</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="assignment_type" id="assignTypeCreate" value="create" checked onchange="toggleAssignmentType()">
                                        <label class="form-check-label fw-bold text-dark" for="assignTypeCreate">👤 إنشاء حساب جديد</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="assignment_type" id="assignTypeSelect" value="select" onchange="toggleAssignmentType()">
                                        <label class="form-check-label fw-bold text-dark" for="assignTypeSelect">📋 اختيار موظف مسجل</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Form: Create employee -->
                            <div id="createNewEmployeeSection">
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-secondary">اسم الموظف الكامل</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">👤</span>
                                        <input type="text" class="form-control" id="employeeNameInput" required placeholder="أدخل اسم الموظف">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold text-secondary">البريد الإلكتروني</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">📧</span>
                                        <input type="email" class="form-control" id="employeeEmailInput" required placeholder="name@example.com">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold text-secondary">رقم الهاتف</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">📞</span>
                                        <input type="text" class="form-control" id="employeePhoneInput" required placeholder="مثال: 0912345678">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold text-secondary">فترة وردية العمل</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">⏰</span>
                                        <select class="form-select" id="employeeShiftRoleInput">
                                            <option value="الوردية الصباحية" selected>🌅 الوردية الصباحية (الفترة الصباحية)</option>
                                            <option value="الوردية المسائية">🌃 الوردية المسائية (الفترة المسائية)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold text-secondary">رقم الحساب المصرفي (IBAN - اختياري)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">🏦</span>
                                        <input type="text" class="form-control" id="employeeBankInput" placeholder="أدخل رقم الحساب المصرفي">
                                    </div>
                                </div>

                                <div class="alert alert-info border-0 rounded-3 mb-0 py-2 small">
                                    💡 سيقوم النظام تلقائياً بتوليد كلمة مرور عشوائية وإرسالها إلى البريد الإلكتروني للموظف الجديد.
                                </div>
                            </div>

                            <!-- Form: Select existing employee -->
                            <div id="selectExistingEmployeeSection" class="d-none">
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-secondary">اختر موظفاً من النظام</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">📋</span>
                                        <select class="form-select" id="existingEmployeeSelect">
                                            <option value="" selected disabled>اختر موظفاً غير معين لساحة...</option>
                                            @foreach($unassignedEmployees as $emp)
                                                <option value="{{ $emp->id }}">{{ $emp->employee_name }} ({{ $emp->employee_phone }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-text text-muted small mt-2">
                                        تتضمن هذه القائمة الموظفين الميدانيين المسجلين في النظام والذين لا يشرفون على أي ساحة حالياً.
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 fw-bold py-3 mt-4 rounded-3" id="saveEmployeeBtn">
                                حفظ وتعيين الموظف للساحة 🚀
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION: AI Assistant -->
        <section id="aiAssistantTab" class="content-section d-none">
            <!-- Hero / Header Card -->
            <div class="card border-0 mb-4 text-white shadow-lg overflow-hidden position-relative" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4338ca 100%); border-radius: 20px;">
                <div class="card-body p-4 p-lg-5 position-relative" style="z-index: 2;">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold" style="font-size: 0.85rem;">
                            ✨ AI Powered Insights • Autonomous Analytics
                        </span>
                        <span class="badge bg-white bg-opacity-20 text-white px-3 py-2 rounded-pill fw-bold" style="font-size: 0.85rem;">
                            v2.4 Neural Model
                        </span>
                    </div>
                    <h2 class="fw-bold text-white mb-2 display-6">AI Assistant & Financial Intelligence</h2>
                    <p class="text-white-50 mb-0 max-w-2xl fs-6">
                        Automated predictive analysis, revenue trajectory forecasting, expense optimization, and data-driven branch recommendations for SpotLy parking network.
                    </p>
                </div>
                <!-- Decorative Ambient Glow -->
                <div class="position-absolute end-0 bottom-0 top-0 w-50 opacity-25 pointer-events-none" style="background: radial-gradient(circle, rgba(129, 140, 248, 0.4) 0%, rgba(0,0,0,0) 70%);"></div>
            </div>

            <!-- AI Financial Analyst Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 p-lg-5 mb-4 position-relative" style="background: var(--card-bg, #ffffff); border: 1px solid var(--glass-border, rgba(255,255,255,0.15)) !important;">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-3 border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-4 p-3 d-flex align-items-center justify-content-center text-white" style="width: 56px; height: 56px; background: linear-gradient(135deg, #6366f1, #8b5cf6); box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);">
                            <i class="fas fa-brain fs-3"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-1 text-dark">AI Financial Analyst</h4>
                            <p class="text-muted mb-0 small">Generate comprehensive AI-driven performance audit reports and financial strategic recommendations</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">
                            🟢 Engine Ready
                        </span>
                    </div>
                </div>

                <!-- Controls Row -->
                <div class="row g-3 align-items-end mb-4 bg-light bg-opacity-50 p-3 rounded-4 border">
                    <div class="col-md-5 col-lg-4">
                        <label class="form-label fw-bold text-secondary small">Analysis Scope</label>
                        <select class="form-select" id="aiManagerScopeSelect">
                            <option value="all" selected>🏢 All Assigned Parking Yards (جميع الساحات)</option>
                            <option value="primary">🔥 Primary Hub (ساحة ميدان الشهداء)</option>
                        </select>
                    </div>
                    <div class="col-md-4 col-lg-4">
                        <label class="form-label fw-bold text-secondary small">Timeframe Horizon</label>
                        <select class="form-select" id="aiManagerTimeframeSelect">
                            <option value="current_quarter" selected>📅 Current Quarter Q3 2026</option>
                            <option value="last_30_days">📆 Past 30 Days Performance</option>
                            <option value="year_to_date">📊 Year-to-Date (YTD 2026)</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-lg-4 text-md-end">
                        <button type="button" class="btn btn-primary btn-lg w-100 w-md-auto px-4 py-3 fw-bold rounded-3 shadow-sm d-inline-flex align-items-center justify-content-center gap-2" id="generateManagerAiReportBtn" onclick="generateManagerAiReport()">
                            <i class="fas fa-wand-magic-sparkles"></i>
                            <span>Generate AI Report</span>
                        </button>
                    </div>
                </div>

                <!-- Loading State Placeholder (Hidden initially) -->
                <div id="aiManagerReportLoadingState" class="d-none text-center py-5">
                    <div class="spinner-border text-indigo mb-3" role="status" style="width: 3.5rem; height: 3.5rem; color: #6366f1;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Synthesizing Neural AI Financial Model...</h5>
                    <p class="text-muted small mb-0">Analyzing revenue streams, expense allocations, branch utilization rates, and profit vectors...</p>
                </div>

                <!-- AI Report Display Component (Hidden until button click) -->
                <div id="aiManagerReportDisplayComponent" class="d-none mt-2">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: #f8fafc; border: 1px solid #e2e8f0 !important;">
                        
                        <!-- Report Top Header Banner -->
                        <div class="p-4 bg-white border-bottom d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-indigo-subtle text-indigo px-3 py-1 rounded-pill fw-bold" style="background: #e0e7ff; color: #4338ca;">
                                        🤖 AI-Generated Report
                                    </span>
                                    <span class="text-muted small">Generated on: <strong id="managerReportTimestampDisplay">July 27, 2026 - 11:55 AM</strong></span>
                                </div>
                                <h4 class="fw-bold text-dark mb-0">SpotLy Comprehensive Executive & Financial AI Audit Report</h4>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="window.print()">
                                    <i class="fas fa-print me-1"></i> Print / PDF
                                </button>
                            </div>
                        </div>

                        <div class="card-body p-4 p-lg-5">

                            <!-- SECTION 1: Executive Summary -->
                            <div class="mb-5">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="badge bg-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="fas fa-chart-line text-white"></i>
                                    </span>
                                    <h5 class="fw-bold text-dark mb-0">1. Executive Summary (الملخص التنفيذي)</h5>
                                </div>
                                <div class="card border-0 p-4 rounded-4 shadow-sm mb-4" style="background: #ffffff;">
                                    <p class="text-dark leading-relaxed mb-4 fs-6">
                                        SpotLy’s financial trajectory for the current evaluation period demonstrates <strong>exceptional growth (+22.4% YoY)</strong>, driven by accelerated electronic wallet transaction adoption and enhanced occupancy rates across core Tripoli commercial centers. Total net revenue reached <strong>48,250.00 LYD</strong> with an average gross margin of <strong>78.4%</strong>. Capacity utilization across top-tier parking yards remained optimal at <strong>84.2%</strong> during peak business hours (10:00 AM – 02:00 PM).
                                    </p>
                                    <!-- Key Metrics Highlights Row -->
                                    <div class="row g-3 text-center">
                                        <div class="col-6 col-md-3">
                                            <div class="p-3 rounded-4 bg-light border">
                                                <span class="text-muted small d-block mb-1">Total Net Revenue</span>
                                                <h4 class="fw-bold text-success mb-0">48,250.00 <small class="fs-6">LYD</small></h4>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="p-3 rounded-4 bg-light border">
                                                <span class="text-muted small d-block mb-1">Gross Profit Margin</span>
                                                <h4 class="fw-bold text-primary mb-0">78.4%</h4>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="p-3 rounded-4 bg-light border">
                                                <span class="text-muted small d-block mb-1">Avg Occupancy Index</span>
                                                <h4 class="fw-bold text-warning text-dark mb-0">84.2%</h4>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="p-3 rounded-4 bg-light border">
                                                <span class="text-muted small d-block mb-1">Health & Safety Score</span>
                                                <h4 class="fw-bold text-indigo mb-0" style="color: #6366f1;">94 / 100</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION 2: Revenue Analysis -->
                            <div class="mb-5">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="badge bg-success rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="fas fa-coins text-white"></i>
                                    </span>
                                    <h5 class="fw-bold text-dark mb-0">2. Revenue Analysis (تحليل الإيرادات)</h5>
                                </div>
                                <div class="card border-0 p-4 rounded-4 shadow-sm mb-4" style="background: #ffffff;">
                                    <div class="row g-4 align-items-center mb-4">
                                        <div class="col-md-7">
                                            <h6 class="fw-bold text-secondary mb-2">Revenue Streams Breakdown</h6>
                                            <p class="text-muted small mb-3">Revenue sources remain strongly diversified across direct cashier entries, instant interactive app reservations, and digital wallet pre-charges.</p>
                                            
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between small fw-bold mb-1">
                                                    <span>Peak Hours Spot Reservations (الترشيح الذكي)</span>
                                                    <span class="text-primary">27,500.00 LYD (57%)</span>
                                                </div>
                                                <div class="progress rounded-pill" style="height: 10px;">
                                                    <div class="progress-bar bg-primary" role="progressbar" style="width: 57%"></div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between small fw-bold mb-1">
                                                    <span>On-site Cashier & Gate Collection (كشك التحصيل)</span>
                                                    <span class="text-success">13,250.00 LYD (27.5%)</span>
                                                </div>
                                                <div class="progress rounded-pill" style="height: 10px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: 27.5%"></div>
                                                </div>
                                            </div>

                                            <div class="mb-0">
                                                <div class="d-flex justify-content-between small fw-bold mb-1">
                                                    <span>Digital Wallet Pre-charges & Subscriptions</span>
                                                    <span class="text-purple" style="color: #8b5cf6;">7,500.00 LYD (15.5%)</span>
                                                </div>
                                                <div class="progress rounded-pill" style="height: 10px;">
                                                    <div class="progress-bar" role="progressbar" style="width: 15.5%; background: #8b5cf6;"></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-5 border-start">
                                            <div class="p-3 bg-light rounded-4">
                                                <h6 class="fw-bold text-dark mb-3">Key Revenue Metrics</h6>
                                                <ul class="list-group list-group-flush bg-transparent">
                                                    <li class="list-group-item bg-transparent d-flex justify-content-between px-0 py-2 small">
                                                        <span class="text-muted">Average Revenue per Slot (ARPU):</span>
                                                        <strong class="text-dark">689.20 LYD / slot</strong>
                                                    </li>
                                                    <li class="list-group-item bg-transparent d-flex justify-content-between px-0 py-2 small">
                                                        <span class="text-muted">Peak Hours Yield Multiplier:</span>
                                                        <strong class="text-success">+34% vs Off-Peak</strong>
                                                    </li>
                                                    <li class="list-group-item bg-transparent d-flex justify-content-between px-0 py-2 small">
                                                        <span class="text-muted">Digital Payment Growth:</span>
                                                        <strong class="text-indigo" style="color: #6366f1;">+42.1% MoM</strong>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION 3: Expense Analysis -->
                            <div class="mb-5">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="badge bg-danger rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="fas fa-receipt text-white"></i>
                                    </span>
                                    <h5 class="fw-bold text-dark mb-0">3. Expense Analysis (تحليل المصروفات التشغيلية)</h5>
                                </div>
                                <div class="card border-0 p-4 rounded-4 shadow-sm mb-4" style="background: #ffffff;">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <h6 class="fw-bold text-secondary mb-3">Operational Cost Allocation</h6>
                                            <ul class="list-unstyled mb-0">
                                                <li class="d-flex align-items-center justify-content-between p-3 rounded-3 mb-2 bg-light">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="badge bg-warning text-dark rounded-circle">👥</span>
                                                        <span class="fw-bold text-dark small">Shift Cashiers & Staff Payroll</span>
                                                    </div>
                                                    <span class="fw-bold text-danger">6,450.00 LYD (62%)</span>
                                                </li>
                                                <li class="d-flex align-items-center justify-content-between p-3 rounded-3 mb-2 bg-light">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="badge bg-info text-dark rounded-circle">⚙️</span>
                                                        <span class="fw-bold text-dark small">Hardware & Gate Systems Maintenance</span>
                                                    </div>
                                                    <span class="fw-bold text-danger">2,300.00 LYD (22%)</span>
                                                </li>
                                                <li class="d-flex align-items-center justify-content-between p-3 rounded-3 mb-0 bg-light">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="badge bg-secondary text-white rounded-circle">⚡</span>
                                                        <span class="fw-bold text-dark small">Utilities, Connectivity & Overhead</span>
                                                    </div>
                                                    <span class="fw-bold text-danger">1,660.00 LYD (16%)</span>
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="h-100 p-4 rounded-4 bg-danger bg-opacity-10 border border-danger-subtle d-flex flex-column justify-content-center">
                                                <h6 class="fw-bold text-danger mb-2"><i class="fas fa-shield-halved me-1"></i> Cost Efficiency Ratio</h6>
                                                <p class="text-dark small mb-3">
                                                    Total operating expenditure stands at <strong>10,410.00 LYD</strong>, representing only <strong>21.6%</strong> of total revenue. The cost-to-revenue ratio is well within optimal industry benchmarks (&lt; 30%).
                                                </p>
                                                <div class="d-flex align-items-center justify-content-between bg-white p-3 rounded-3 border">
                                                    <span class="fw-bold text-secondary small">Net Profit Retained:</span>
                                                    <h4 class="fw-bold text-success mb-0">37,840.00 LYD</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION 4: Branch Performance -->
                            <div class="mb-5">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="badge bg-info text-dark rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="fas fa-building text-white"></i>
                                    </span>
                                    <h5 class="fw-bold text-dark mb-0">4. Branch Performance (أداء الساحات والفروع)</h5>
                                </div>
                                <div class="card border-0 p-4 rounded-4 shadow-sm mb-4" style="background: #ffffff;">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle text-center mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-start">Branch / Yard Name</th>
                                                    <th>Total Capacity</th>
                                                    <th>Occupancy Rate</th>
                                                    <th>Gross Revenue</th>
                                                    <th>Net Profit</th>
                                                    <th>Performance Rank</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="text-start fw-bold text-dark">
                                                        <i class="fas fa-parking text-primary me-2"></i> ساحة ميدان الشهداء التفاعلية
                                                    </td>
                                                    <td>60 slots</td>
                                                    <td><span class="badge bg-success">92.5% (High)</span></td>
                                                    <td class="fw-bold text-primary">18,400.00 LYD</td>
                                                    <td class="fw-bold text-success">14,720.00 LYD</td>
                                                    <td><span class="badge bg-warning text-dark fw-bold">🥇 #1 Top Performer</span></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-start fw-bold text-dark">
                                                        <i class="fas fa-parking text-primary me-2"></i> ساحة برج طرابلس وذات العماد
                                                    </td>
                                                    <td>70 slots</td>
                                                    <td><span class="badge bg-success">88.0% (High)</span></td>
                                                    <td class="fw-bold text-primary">15,200.00 LYD</td>
                                                    <td class="fw-bold text-success">12,160.00 LYD</td>
                                                    <td><span class="badge bg-secondary text-white fw-bold">🥈 #2 Performer</span></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-start fw-bold text-dark">
                                                        <i class="fas fa-parking text-primary me-2"></i> ساحة المدار - طريق الشط
                                                    </td>
                                                    <td>80 slots</td>
                                                    <td><span class="badge bg-primary">81.2% (Optimal)</span></td>
                                                    <td class="fw-bold text-primary">14,650.00 LYD</td>
                                                    <td class="fw-bold text-success">10,960.00 LYD</td>
                                                    <td><span class="badge bg-info text-dark fw-bold">🥉 #3 Performer</span></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-start fw-bold text-dark">
                                                        <i class="fas fa-parking text-primary me-2"></i> ساحة مستشفى الخضراء والعيادات
                                                    </td>
                                                    <td>45 slots</td>
                                                    <td><span class="badge bg-warning text-dark">78.0% (Moderate)</span></td>
                                                    <td class="fw-bold text-primary">8,200.00 LYD</td>
                                                    <td class="fw-bold text-success">6,560.00 LYD</td>
                                                    <td><span class="badge bg-light text-dark fw-bold">#4 Stable</span></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION 5: Recommendations -->
                            <div class="mb-0">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="badge bg-warning text-dark rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="fas fa-lightbulb text-dark"></i>
                                    </span>
                                    <h5 class="fw-bold text-dark mb-0">5. AI Strategic Recommendations (التوصيات والاستراتيجيات)</h5>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="card border-0 p-4 rounded-4 shadow-sm h-100 border-start border-4 border-primary" style="background: #ffffff;">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <h6 class="fw-bold text-primary mb-0">⚡ Implement Dynamic Surge Pricing</h6>
                                                <span class="badge bg-danger">High Priority</span>
                                            </div>
                                            <p class="text-muted small mb-0">
                                                Apply a +15% dynamic rate adjustment during peak morning commercial activity (10:00 AM – 02:00 PM) at <strong>ساحة ميدان الشهداء</strong> to boost peak margin yield by an estimated +3,200 LYD monthly.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="card border-0 p-4 rounded-4 shadow-sm h-100 border-start border-4 border-success" style="background: #ffffff;">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <h6 class="fw-bold text-success mb-0">👥 Smart Shift Re-allocation</h6>
                                                <span class="badge bg-primary">Medium Priority</span>
                                            </div>
                                            <p class="text-muted small mb-0">
                                                Re-assign 2 field cashiers from off-peak evening shifts at residential yards to high-demand evening commercial centers (ساحة قرقارش وسوق الثلاثاء) to reduce gate latency by 45%.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="card border-0 p-4 rounded-4 shadow-sm h-100 border-start border-4 border-purple" style="background: #ffffff;">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <h6 class="fw-bold text-purple mb-0" style="color: #8b5cf6;">💳 Digital Wallet Recharge Incentives</h6>
                                                <span class="badge bg-success">Growth Focus</span>
                                            </div>
                                            <p class="text-muted small mb-0">
                                                Offer a 5% bonus credit on user wallet recharges exceeding 100 LYD to decrease cash transaction friction and lower overall cashier management cost.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="card border-0 p-4 rounded-4 shadow-sm h-100 border-start border-4 border-info" style="background: #ffffff;">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <h6 class="fw-bold text-info text-dark mb-0">🅿️ Off-Peak Night Passes</h6>
                                                <span class="badge bg-secondary">Optimization</span>
                                            </div>
                                            <p class="text-muted small mb-0">
                                                Monetize unutilized night capacity (10:00 PM – 06:00 AM) by introducing discounted night parking passes for nearby hotel guests and event venues.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- SECTION 4: Block & Violation Management -->
        <section id="violationsTab" class="content-section d-none">
            <div class="card">
                <div class="card-header bg-gradient p-4 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-ban"></i> إدارة الحظر والمخالفات لسائقي السيارات</h5>
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">الحظر التلقائي عند 5 مخالفات (بعد حجزين ناجحين على الأقل)</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-center mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>اسم السائق</th>
                                    <th>البريد الإلكتروني</th>
                                    <th>رقم الهاتف</th>
                                    <th>رقم اللوحة</th>
                                    <th>إجمالي الحجوزات</th>
                                    <th>الحجوزات الناجحة</th>
                                    <th>عدد المخالفات</th>
                                    <th>حالة الحساب</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($blockedUsers as $driver)
                                    <tr id="driver-row-{{ $driver->id }}">
                                        <td class="fw-bold text-dark-emphasis">{{ $driver->driver_name }}</td>
                                        <td>{{ $driver->driver_email }}</td>
                                        <td><span dir="ltr">{{ $driver->driver_phone }}</span></td>
                                        <td><span class="badge bg-secondary px-3 py-1">{{ $driver->plate_number }}</span></td>
                                        <td><span class="badge bg-info text-dark px-3 py-1">{{ $driver->total_bookings ?? 0 }}</span></td>
                                        <td><span class="badge bg-success px-3 py-1">{{ $driver->successful_bookings ?? 0 }}</span></td>
                                        <td><span class="badge bg-warning text-dark px-3 py-1" id="driver-violations-{{ $driver->id }}">{{ $driver->fake_booking_count }}</span></td>
                                        <td>
                                            @if($driver->status === 'blocked')
                                                <span class="badge bg-danger px-3 py-1" id="driver-status-{{ $driver->id }}">محظور</span>
                                            @else
                                                <span class="badge bg-success px-3 py-1" id="driver-status-{{ $driver->id }}">نشط</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-1">
                                                <button onclick="recordDriverViolation({{ $driver->id }}, '{{ addslashes($driver->driver_name) }}')" class="btn btn-sm btn-outline-warning rounded-pill px-3">
                                                    ⚠️ تسجيل مخالفة
                                                </button>
                                                @if($driver->status === 'blocked' || $driver->fake_booking_count > 0)
                                                    <button onclick="confirmUnblockDriver({{ $driver->id }}, '{{ addslashes($driver->driver_name) }}')" class="btn btn-sm btn-success rounded-pill px-3" id="unblock-btn-{{ $driver->id }}">
                                                        ✅ فك الحظر
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-muted py-5">لا يوجد سائقون مسجلون حالياً.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION 5: Comprehensive Financial & Operational Report -->
        <section id="financialReportTab" class="content-section d-none">
            <!-- Filter Bar & Export Controls Card -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 border-bottom pb-3">
                        <div>
                            <h5 class="fw-bold text-dark mb-1"><i class="fas fa-chart-line text-primary me-2"></i>التقرير المالي والتشغيلي المتقدم</h5>
                            <p class="text-muted mb-0 small">تصفية وتحليل الحركة المالية والإشغيلية لساحاتك المدارة مع إمكانية التصدير المباشر</p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a id="exportPdfBtn" href="{{ route('manager.reports.export', ['format' => 'pdf', 'period' => 'this_month']) }}" class="btn btn-danger btn-sm fw-bold px-3 py-2 rounded-pill shadow-sm d-flex align-items-center gap-2">
                                <i class="fas fa-file-pdf"></i> تحميل التقرير PDF
                            </a>
                            <a id="exportExcelBtn" href="{{ route('manager.reports.export', ['format' => 'excel', 'period' => 'this_month']) }}" class="btn btn-success btn-sm fw-bold px-3 py-2 rounded-pill shadow-sm d-flex align-items-center gap-2">
                                <i class="fas fa-file-excel"></i> تحميل التقرير Excel
                            </a>
                        </div>
                    </div>

                    <!-- Filter Options Form -->
                    <form id="reportFilterForm" class="row g-3 align-items-end" onsubmit="applyReportFilter(event)">
                        <div class="col-md-5">
                            <label class="form-label fw-bold text-secondary small">الفلاتر الزمنية السريعة</label>
                            <div class="btn-group w-100" role="group">
                                <button type="button" class="btn btn-outline-primary" onclick="setQuickPeriod('today', this)">اليوم</button>
                                <button type="button" class="btn btn-outline-primary" onclick="setQuickPeriod('this_week', this)">هذا الأسبوع</button>
                                <button type="button" class="btn btn-outline-primary active" onclick="setQuickPeriod('this_month', this)">هذا الشهر</button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-secondary small">من تاريخ</label>
                            <input type="date" id="filterStartDate" class="form-control" onchange="resetQuickPeriodBtns()">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-secondary small">إلى تاريخ</label>
                            <input type="date" id="filterEndDate" class="form-control" onchange="resetQuickPeriodBtns()">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100 fw-bold">تطبيق</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Financial Metrics Row (4 Cards) -->
            <h6 class="fw-bold text-secondary mb-3"><i class="fas fa-coins me-1"></i> المؤشرات المالية</h6>
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 text-white shadow-sm p-4 h-100" style="background: linear-gradient(135deg, #11998e, #38ef7d); border-radius: 16px;">
                        <h6 class="text-white-50 fw-bold mb-1">💰 إجمالي الإيرادات</h6>
                        <h3 class="fw-bold mb-0 text-white" id="valTotalRevenue">{{ number_format($financialData['totalRevenue'], 2) }} <span class="fs-6 font-normal">د.ل</span></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 text-white shadow-sm p-4 h-100" style="background: linear-gradient(135deg, #eb3b5a, #fa8231); border-radius: 16px;">
                        <h6 class="text-white-50 fw-bold mb-1">🧾 إجمالي المصاريف</h6>
                        <h3 class="fw-bold mb-0 text-white" id="valTotalExpenses">0.00 <span class="fs-6 font-normal">د.ل</span></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 text-white shadow-sm p-4 h-100" style="background: linear-gradient(135deg, #20bf6b, #0fb9b1); border-radius: 16px;">
                        <h6 class="text-white-50 fw-bold mb-1">❇️ صافي الأرباح</h6>
                        <h3 class="fw-bold mb-0 text-white" id="valNetProfit">{{ number_format($financialData['totalRevenue'], 2) }} <span class="fs-6 font-normal">د.ل</span></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 text-white shadow-sm p-4 h-100" style="background: linear-gradient(135deg, #4b7bec, #3867d6); border-radius: 16px;">
                        <h6 class="text-white-50 fw-bold mb-1">💵 متوسط الدخل اليومي</h6>
                        <h3 class="fw-bold mb-0 text-white" id="valAvgDailyIncome">{{ number_format($financialData['totalRevenue'] / 30, 2) }} <span class="fs-6 font-normal">د.ل</span></h3>
                    </div>
                </div>
            </div>

            <!-- Operational Metrics Row (4 Cards) -->
            <h6 class="fw-bold text-secondary mb-3"><i class="fas fa-car me-1"></i> المؤشرات التشغيلية ونسبة الإشغال</h6>
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 text-white shadow-sm p-4 h-100" style="background: linear-gradient(135deg, #8854d0, #a55eea); border-radius: 16px;">
                        <h6 class="text-white-50 fw-bold mb-1">🚗 عدد السيارات الداخلة</h6>
                        <h3 class="fw-bold mb-0 text-white" id="valCarsEntered">0 <span class="fs-6 font-normal">سيارة</span></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 text-white shadow-sm p-4 h-100" style="background: linear-gradient(135deg, #fa8231, #f7b731); border-radius: 16px;">
                        <h6 class="text-white-50 fw-bold mb-1">🚙 عدد السيارات الخارجة</h6>
                        <h3 class="fw-bold mb-0 text-white" id="valCarsExited">0 <span class="fs-6 font-normal">سيارة</span></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 text-white shadow-sm p-4 h-100" style="background: linear-gradient(135deg, #2d98da, #45aaf2); border-radius: 16px;">
                        <h6 class="text-white-50 fw-bold mb-1">⏱️ إجمالي ساعات الوقوف</h6>
                        <h3 class="fw-bold mb-0 text-white" id="valTotalParkingHours">0.0 <span class="fs-6 font-normal">ساعة</span></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 text-white shadow-sm p-4 h-100" style="background: linear-gradient(135deg, #fd9644, #e67e22); border-radius: 16px;">
                        <h6 class="text-white-50 fw-bold mb-1">📈 نسبة إشغال الموقف</h6>
                        <h3 class="fw-bold mb-0 text-white" id="valOccupancyRate">0.0 <span class="fs-6 font-normal">%</span></h3>
                    </div>
                </div>
            </div>        </section>

        <!-- SECTION 6: Personal Settings -->
        <section id="profileTab" class="content-section d-none">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm overflow-hidden">
                        <div class="card-header bg-gradient text-dark p-4 d-flex align-items-center justify-content-between border-0">
                            <div>
                                <h5 class="fw-bold mb-1">⚙️ إعدادات الحساب والبيانات الشخصية</h5>
                                <p class="fs-6 mb-0 text-muted">إدارة وتحديث بيانات الاتصال وكلمة المرور الخاصة بك</p>
                            </div>
                            <span class="fs-1">🔒</span>
                        </div>

                        <div class="card-body p-4 bg-white bg-opacity-70">
                            <form id="profileForm">
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-secondary">الاسم الكامل (غير قابل للتعديل)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">👤</span>
                                        <input type="text" class="form-control border-start-0 ps-0 bg-light text-muted" id="profileNameDisplay" readonly>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-secondary">رقم الهاتف للتواصل</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">📞</span>
                                        <input type="text" class="form-control border-start-0 ps-0" id="profilePhoneInput" required placeholder="أدخل رقم هاتفك المحدث">
                                    </div>
                                </div>

                                <hr class="my-4 border-secondary border-opacity-25">

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-secondary">كلمة مرور جديدة</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">🔑</span>
                                        <input type="password" class="form-control border-start-0 ps-0" id="profilePasswordInput" placeholder="•••••••• (اتركها فارغة إذا لم ترغب بالتغيير)">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold py-3" id="updateProfileBtn">
                                    حفظ التعديلات وتحديث الجلسة 💾
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION 7: Operations Audit Log -->
        <section id="auditLogTab" class="content-section d-none">
            <!-- Operations Cash Statistics cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 text-white shadow-lg p-4 h-100" style="background: linear-gradient(135deg, #0f2027, #203a43, #2c5364); border-radius: 20px;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 fw-bold mb-1">🪙 إجمالي النقدية المستلمة (كاش)</h6>
                                <h2 class="fw-bold mb-0 text-white"><span id="summaryTotalCash">0.00</span> <span class="fs-6 fw-normal text-white-50">د.ل</span></h2>
                            </div>
                            <span class="fs-1 opacity-75">💼</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 text-white shadow-lg p-4 h-100" style="background: linear-gradient(135deg, #11998e, #38ef7d); border-radius: 20px;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 fw-bold mb-1">🚗 كاش الزوار غير المشتركين (Guests)</h6>
                                <h2 class="fw-bold mb-0 text-white"><span id="summaryGuestCash">0.00</span> <span class="fs-6 fw-normal text-white-50">د.ل</span></h2>
                            </div>
                            <span class="fs-1 opacity-75">🎫</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 text-white shadow-lg p-4 h-100" style="background: linear-gradient(135deg, #f39c12, #d35400); border-radius: 20px;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 fw-bold mb-1">⚡ كاش عمليات الشحن المباشر</h6>
                                <h2 class="fw-bold mb-0 text-white"><span id="summaryRechargeCash">0.00</span> <span class="fs-6 fw-normal text-white-50">د.ل</span></h2>
                            </div>
                            <span class="fs-1 opacity-75">💳</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Operations timeline filtering tools -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-3">🔍 أدوات فحص وتصفية سجل العمليات والتدقيق</h5>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-secondary small">تصفية حسب الموظف</label>
                            <select id="filterEmployee" class="form-select" onchange="loadAuditLogs()">
                                <option value="">جميع الموظفين</option>
                                @foreach($managerEmployees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->employee_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-secondary small">نوع العملية</label>
                            <select id="filterOperation" class="form-select" onchange="loadAuditLogs()">
                                <option value="">جميع العمليات</option>
                                <option value="entry">دخول مركبة ⬇️</option>
                                <option value="exit">خروج مركبة ⬆️</option>
                                <option value="recharge">شحن كاش فوري ⚡</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-secondary small">البحث النصي</label>
                            <input type="text" id="searchPlate" class="form-control" placeholder="ابحث برقم اللوحة أو اسم السائق/الموظف..." oninput="loadAuditLogs()">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-secondary w-100 fw-bold py-2 rounded-3" onclick="resetFilters()">❌ إعادة تعيين</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Live Operations Timeline -->
            <div class="card border-0 shadow-sm">
                <div class="card-header p-4 border-0">
                    <h5 class="mb-0 fw-bold">📜 سجل الحركات التشغيلية والمالية الفوري</h5>
                </div>
                <div class="card-body p-4">
                    <div class="timeline-container mx-4" id="auditTimeline">
                        <!-- Loaded via AJAX -->
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION 8: Shift Clock-in/Out Monitoring Log -->
        <section id="shiftsTab" class="content-section d-none">
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3 border-bottom pb-3">
                        <div>
                            <h5 class="fw-bold text-dark mb-1"><i class="fas fa-clock text-primary me-2"></i>سجل مناوبات ومواظبة الموظفين الميدانيين (Shift Attendance Monitor)</h5>
                            <p class="text-muted mb-0 small">مراقبة توقيتات الدخول والانصراف وحالة المناوبات الحالية لكل موظف</p>
                        </div>
                        <button onclick="loadShiftLogs()" class="btn btn-outline-primary btn-sm rounded-pill px-4 fw-bold">تحديث السجل 🔄</button>
                    </div>

                    <div class="row g-3 align-items-end mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-secondary small">تصفية حسب الموظف</label>
                            <select id="shiftFilterEmployee" class="form-select" onchange="loadShiftLogs()">
                                <option value="">جميع الموظفين</option>
                                @foreach($managerEmployees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->employee_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-secondary small">حالة المناوبة</label>
                            <select id="shiftFilterStatus" class="form-select" onchange="loadShiftLogs()">
                                <option value="">جميع الحالات</option>
                                <option value="active">مناوبة نشطة جارية 🟢</option>
                                <option value="completed">مناوبة مكتملة 🔴</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header p-4 border-0">
                    <h5 class="mb-0 fw-bold">⏱️ جدول تفاصيل حضور وانصراف الموظفين</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-center mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>الموظف الميداني</th>
                                    <th>الساحة المسندة</th>
                                    <th>وقت بدء المناوبة (Clock-In)</th>
                                    <th>وقت إنهاء المناوبة (Clock-Out)</th>
                                    <th>مدة المناوبة</th>
                                    <th>الحالة الحالية</th>
                                </tr>
                            </thead>
                            <tbody id="shiftLogsTableBody">
                                <tr>
                                    <td colspan="6" class="text-muted py-5">جاري جلب بيانات المناوبات...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
    const fetchHeaders = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
    };

    let map;
    let marker;
    const existingParkings = @json($parkingsList);

    const orangeIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-orange.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });

    const storedUserData = JSON.parse(localStorage.getItem('userData'));
    const currentManagerAccountId = (storedUserData && (storedUserData.id || storedUserData.account_id)) ? (storedUserData.id || storedUserData.account_id) : {{ auth()->id() ?? 'null' }};

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

    document.addEventListener('DOMContentLoaded', function() {
        try {
            if (storedUserData && storedUserData.name) {
                document.getElementById('managerNameDisplay').innerText = 'مرحباً، ' + storedUserData.name;
            }
        } catch (exception) {
            console.error("خطأ في قراءة بيانات الجلسة", exception);
        }
    });

    function switchTab(sectionId, clickedLink) {
        try {
            const allSections = document.querySelectorAll('.content-section');
            allSections.forEach(section => {
                section.classList.add('d-none');
            });

            const targetSection = document.getElementById(sectionId);
            if (targetSection) {
                targetSection.classList.remove('d-none');
            }

            const allLinks = document.querySelectorAll('.sidebar .nav-link');
            allLinks.forEach(link => {
                link.classList.remove('active');
            });

            clickedLink.classList.add('active');
            
            const titleDisplay = document.getElementById('pageTitleDisplay');
            titleDisplay.innerText = clickedLink.innerText.trim();

            if (window.innerWidth <= 991) {
                document.getElementById('dashboardSidebar').classList.remove('active');
            }

            if (sectionId === 'parkingsTab') {
                initManagedParkingsMap();
            }
            if (sectionId === 'profileTab') {
                loadProfileData();
            }
            if (sectionId === 'auditLogTab') {
                loadAuditLogs();
            }
            if (sectionId === 'shiftsTab') {
                loadShiftLogs();
            }
        } catch (exception) {
            console.error("خطأ أثناء التبديل بين التبويبات", exception);
        }
    }

    async function loadShiftLogs() {
        const empId = document.getElementById('shiftFilterEmployee').value;
        const status = document.getElementById('shiftFilterStatus').value;
        const tbody = document.getElementById('shiftLogsTableBody');
        tbody.innerHTML = `<tr><td colspan="6" class="text-muted py-4"><div class="spinner-border spinner-border-sm text-primary"></div> جاري التحميل...</td></tr>`;

        try {
            const response = await fetch(`/manager/shifts/data?employee_id=${empId}&status=${status}`, { method: 'GET', headers: fetchHeaders });
            const result = await response.json();

            if (response.ok && result.status === 'success') {
                tbody.innerHTML = '';
                if (result.data.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="6" class="text-muted py-5">لا توجد سجلات مناوبات مسجلة حالياً.</td></tr>`;
                    return;
                }

                result.data.forEach(shift => {
                    let statusBadge = shift.status === 'active' 
                        ? `<span class="badge bg-success px-3 py-1">مناوبة نشطة 🟢</span>`
                        : `<span class="badge bg-secondary px-3 py-1">مكتملة 🔴</span>`;

                    tbody.innerHTML += `
                        <tr>
                            <td class="fw-bold text-dark-emphasis">${shift.employee_name} <br><small class="text-muted" dir="ltr">${shift.employee_phone || ''}</small></td>
                            <td><span class="badge bg-light text-dark border px-3 py-1">${shift.parking_name || 'غير معين'}</span></td>
                            <td class="fw-bold text-success" dir="ltr">${shift.clock_in_formatted}</td>
                            <td class="fw-bold text-danger" dir="ltr">${shift.clock_out_formatted}</td>
                            <td><span class="badge bg-info text-dark px-3 py-1">${shift.duration_label}</span></td>
                            <td>${statusBadge}</td>
                        </tr>
                    `;
                });
            } else {
                throw new Error(result.message || 'فشل جلب سجل المناوبات.');
            }
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-danger py-4">⚠️ خطأ: ${err.message}</td></tr>`;
        }
    }

    async function loadAuditLogs() {
        const employeeId = document.getElementById('filterEmployee').value;
        const operationType = document.getElementById('filterOperation').value;
        const search = document.getElementById('searchPlate').value;

        const timelineElement = document.getElementById('auditTimeline');
        timelineElement.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">جاري التحميل...</span>
                </div>
                <p class="text-muted mt-2">جاري جلب السجلات والتدقيق المالي...</p>
            </div>
        `;

        try {
            let url = `/manager/audit-logs/data?employee_id=${employeeId}&operation_type=${operationType}&search=${encodeURIComponent(search)}`;
            const response = await fetch(url, {
                method: 'GET',
                headers: fetchHeaders
            });
            const result = await response.json();

            if (response.ok && result.status === 'success') {
                document.getElementById('summaryTotalCash').innerText = parseFloat(result.summary.totalCash).toFixed(2);
                document.getElementById('summaryGuestCash').innerText = parseFloat(result.summary.totalGuestExitCash).toFixed(2);
                document.getElementById('summaryRechargeCash').innerText = parseFloat(result.summary.totalRechargeCash).toFixed(2);

                timelineElement.innerHTML = '';
                
                if (result.data.length === 0) {
                    timelineElement.innerHTML = `
                        <div class="alert alert-warning text-center border-0 py-5">
                            🔍 لا توجد سجلات مطابقة للخيارات المحددة في هذا الموقف.
                        </div>
                    `;
                    return;
                }

                result.data.forEach(log => {
                    let badgeClass = 'bg-entry';
                    let opName = 'دخول سيارة ⬇️';
                    let opColor = 'text-success';
                    let icon = '⬇️';
                    
                    if (log.operation_type === 'exit') {
                        badgeClass = 'bg-exit';
                        opName = 'خروج سيارة ⬆️';
                        opColor = 'text-danger';
                        icon = '⬆️';
                    } else if (log.operation_type === 'recharge') {
                        badgeClass = 'bg-recharge';
                        opName = 'شحن كاش فوري ⚡';
                        opColor = 'text-warning text-dark';
                        icon = '⚡';
                    }

                    let cashDisplay = '';
                    if (parseFloat(log.cash_value) > 0) {
                        cashDisplay = `<span class="badge bg-success fs-6 fw-bold px-3 py-2">المبلغ المستلم: ${parseFloat(log.cash_value).toFixed(2)} د.ل</span>`;
                    } else {
                        cashDisplay = `<span class="badge bg-secondary px-2 py-1">لا توجد قيمة نقدية</span>`;
                    }

                    let plateDisplay = log.plate_number ? `<span class="badge bg-dark px-2 py-1 fs-6">${log.plate_number}</span>` : `<span class="text-muted">غير محدد</span>`;
                    
                    let driverDisplay = '';
                    if (log.driver_name) {
                        driverDisplay = `<div class="small text-muted mt-1">👤 السائق: <b>${log.driver_name}</b> (حساب ID: ${log.driver_account_id})</div>`;
                    }

                    let formattedDate = new Date(log.created_at).toLocaleString('ar-LY', {
                        year: 'numeric', month: 'long', day: 'numeric',
                        hour: '2-digit', minute: '2-digit', second: '2-digit'
                    });

                    timelineElement.innerHTML += `
                        <div class="timeline-item">
                            <div class="timeline-badge ${badgeClass}">${icon}</div>
                            <div class="card shadow-sm border-0 mb-3 overflow-hidden">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <div>
                                            <h6 class="fw-bold mb-1 ${opColor}">${opName}</h6>
                                            <div class="small text-dark">👮 الموظف: <b>${log.employee_name}</b> | 📍 الموقف: <b>${log.parking_name}</b></div>
                                        </div>
                                        <div class="text-end">
                                            <div class="small text-muted mb-1">${formattedDate}</div>
                                            ${cashDisplay}
                                        </div>
                                    </div>
                                    <hr class="my-2 border-opacity-10">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <div>
                                            <span class="small text-muted">🏷️ رقم اللوحة:</span> ${plateDisplay}
                                            ${driverDisplay}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
            } else {
                throw new Error(result.message || 'فشل جلب البيانات.');
            }
        } catch (error) {
            timelineElement.innerHTML = `
                <div class="alert alert-danger text-center border-0 py-4">
                    ⚠️ خطأ: ${error.message}
                </div>
            `;
        }
    }

    function resetFilters() {
        document.getElementById('filterEmployee').value = '';
        document.getElementById('filterOperation').value = '';
        document.getElementById('searchPlate').value = '';
        loadAuditLogs();
    }

    function loadProfileData() {
        try {
            if (storedUserData) {
                document.getElementById('profileNameDisplay').value = storedUserData.name;
                document.getElementById('profilePhoneInput').value = storedUserData.phone || '';
            }
        } catch (exception) {
            console.error("خطأ في تحميل بيانات الملف الشخصي", exception);
        }
    }

    document.getElementById('profileForm').addEventListener('submit', async function(event) {
        event.preventDefault();

        const phoneValue = document.getElementById('profilePhoneInput').value;
        const passwordValue = document.getElementById('profilePasswordInput').value;
        const submitBtn = document.getElementById('updateProfileBtn');

        if (!currentManagerAccountId) {
            Swal.fire({
                icon: 'error',
                title: 'خطأ',
                text: 'جلسة المستخدم غير صالحة. يرجى تسجيل الدخول مجدداً.',
                confirmButtonColor: '#ff3b30'
            });
            return;
        }

        submitBtn.disabled = true;

        try {
            const response = await fetch('/api/accounts/update-profile', {
                method: 'POST',
                headers: fetchHeaders,
                body: JSON.stringify({
                    accountId: currentManagerAccountId,
                    phone: phoneValue,
                    password: passwordValue || null
                })
            });

            const responseData = await response.json();

            if (response.ok && responseData.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'تم التحديث بنجاح',
                    text: 'تم تعديل بيانات ملفك الشخصي بنجاح.',
                    confirmButtonColor: '#0071e3'
                });

                storedUserData.phone = phoneValue;
                localStorage.setItem('userData', JSON.stringify(storedUserData));

                document.getElementById('profilePasswordInput').value = '';
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'فشل التحديث',
                    text: responseData.message || 'حدث خطأ أثناء حفظ التعديلات.',
                    confirmButtonColor: '#ff3b30'
                });
            }
        } catch (networkError) {
            Swal.fire({
                icon: 'error',
                title: 'خطأ اتصال',
                text: 'تعذر الوصول لخادم الشبكة.',
                confirmButtonColor: '#ff3b30'
            });
        } finally {
            submitBtn.disabled = false;
        }
    });

    function logoutManager() {
        Swal.fire({
            title: 'هل تريد تسجيل الخروج؟',
            text: "سيتم إنهاء الجلسة الحالية والعودة لصفحة تسجيل الدخول.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0071e3',
            cancelButtonColor: '#8e8e93',
            confirmButtonText: 'نعم، تسجيل الخروج',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                localStorage.clear();
                window.location.href = '/login';
            }
        });
    }

    let employeeModal;

    function toggleAssignmentType() {
        const typeSelect = document.getElementById('assignTypeSelect').checked;
        const createSection = document.getElementById('createNewEmployeeSection');
        const selectSection = document.getElementById('selectExistingEmployeeSection');
        
        if (typeSelect) {
            createSection.classList.add('d-none');
            selectSection.classList.remove('d-none');
            
            document.getElementById('employeeNameInput').removeAttribute('required');
            document.getElementById('employeeEmailInput').removeAttribute('required');
            document.getElementById('employeePhoneInput').removeAttribute('required');
            document.getElementById('existingEmployeeSelect').setAttribute('required', 'required');
        } else {
            createSection.classList.remove('d-none');
            selectSection.classList.add('d-none');
            
            document.getElementById('employeeNameInput').setAttribute('required', 'required');
            document.getElementById('employeeEmailInput').setAttribute('required', 'required');
            document.getElementById('employeePhoneInput').setAttribute('required', 'required');
            document.getElementById('existingEmployeeSelect').removeAttribute('required');
        }
    }

    function openCreateEmployeeModal(parkingId, parkingName, defaultShift = 'الوردية الصباحية') {
        document.getElementById('modalParkingId').value = parkingId;
        document.getElementById('modalParkingName').value = parkingName;
        document.getElementById('createEmployeeForm').reset();
        
        const shiftRoleInput = document.getElementById('employeeShiftRoleInput');
        if (shiftRoleInput) {
            shiftRoleInput.value = defaultShift;
        }
        
        document.getElementById('assignTypeCreate').checked = true;
        toggleAssignmentType();
        
        if (!employeeModal) {
            employeeModal = new bootstrap.Modal(document.getElementById('createEmployeeModal'));
        }
        employeeModal.show();
    }

    document.getElementById('createEmployeeForm').addEventListener('submit', async function(event) {
        event.preventDefault();
        
        const parkingId = document.getElementById('modalParkingId').value;
        const assignmentType = document.querySelector('input[name="assignment_type"]:checked').value;
        const shiftRoleInput = document.getElementById('employeeShiftRoleInput');
        const selectedShiftRole = shiftRoleInput ? shiftRoleInput.value : 'الوردية الصباحية';

        let requestData = {
            parking_id: parkingId,
            assignment_type: assignmentType,
            shift_role: selectedShiftRole
        };

        if (assignmentType === 'select') {
            const employeeId = document.getElementById('existingEmployeeSelect').value;
            if (!employeeId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'تنبيه',
                    text: 'الرجاء اختيار موظف من القائمة.',
                    confirmButtonColor: '#0071e3'
                });
                return;
            }
            requestData.employee_id = employeeId;
        } else {
            requestData.name = document.getElementById('employeeNameInput').value;
            requestData.email = document.getElementById('employeeEmailInput').value;
            requestData.phone = document.getElementById('employeePhoneInput').value;
            requestData.bank_account_number = document.getElementById('employeeBankInput').value || null;
        }

        const submitBtn = document.getElementById('saveEmployeeBtn');
        submitBtn.disabled = true;

        try {
            const response = await fetch('/manager/employees/store', {
                method: 'POST',
                headers: fetchHeaders,
                body: JSON.stringify(requestData)
            });

            const result = await response.json();

            if (response.ok && result.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'نجاح العملية',
                    text: result.message || 'تم تعيين الموظف للساحة بنجاح.',
                    confirmButtonColor: '#0071e3'
                }).then(() => {
                    location.reload();
                });
                employeeModal.hide();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ',
                    text: result.message || 'فشل في إنشاء الحساب.',
                    confirmButtonColor: '#ff3b30'
                });
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'خطأ اتصال',
                text: 'فشل الاتصال بالخادم.',
                confirmButtonColor: '#ff3b30'
            });
        } finally {
            submitBtn.disabled = false;
        }
    });

    function confirmUnlinkEmployee(parkingId, parkingName, employeeName) {
        Swal.fire({
            title: 'تأكيد فك الارتباط',
            text: `هل أنت متأكد من فك ارتباط الموظف (${employeeName}) عن ساحة (${parkingName})؟`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff3b30',
            cancelButtonColor: '#8e8e93',
            confirmButtonText: 'نعم، فك الارتباط',
            cancelButtonText: 'إلغاء'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const response = await fetch('/manager/parkings/unlink', {
                        method: 'POST',
                        headers: fetchHeaders,
                        body: JSON.stringify({
                            parking_id: parkingId
                        })
                    });

                    const resData = await response.json();

                    if (response.ok && resData.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'تم فك الارتباط بنجاح',
                            text: resData.message,
                            confirmButtonColor: '#0071e3'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ',
                            text: resData.message || 'فشل فك ارتباط الموظف.',
                            confirmButtonColor: '#ff3b30'
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطأ اتصال',
                        text: 'تعذر الاتصال بالخادم.',
                        confirmButtonColor: '#ff3b30'
                    });
                }
            }
        });
    }

    function recordDriverViolation(driverId, driverName) {
        Swal.fire({
            title: `تسجيل مخالفة على السائق: ${driverName}`,
            text: "هل تريد تسجيل مخالفة على هذا السائق؟",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff9500',
            cancelButtonColor: '#8e8e93',
            confirmButtonText: 'نعم، سجل المخالفة',
            cancelButtonText: 'إلغاء'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const response = await fetch('/manager/users/violation', {
                        method: 'POST',
                        headers: fetchHeaders,
                        body: JSON.stringify({
                            user_id: driverId,
                            reason: 'مخالفة تعليمات الساحة'
                        })
                    });

                    const resData = await response.json();

                    if (resData.status === 'ignored') {
                        Swal.fire({
                            icon: 'info',
                            title: 'تنبيه خوارزمية الحظر',
                            text: resData.message,
                            confirmButtonColor: '#0071e3'
                        });
                    } else if (resData.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'تم تسجيل المخالفة',
                            text: resData.message,
                            confirmButtonColor: '#0071e3'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ',
                            text: resData.message || 'فشل تسجيل المخالفة.',
                            confirmButtonColor: '#ff3b30'
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطأ اتصال',
                        text: 'تعذر الاتصال بالخادم.',
                        confirmButtonColor: '#ff3b30'
                    });
                }
            }
        });
    }

    function confirmUnblockDriver(driverId, driverName) {
        Swal.fire({
            title: 'تأكيد إلغاء الحظر وتصفير المخالفات',
            text: `هل أنت متأكد من تصفير مخالفات السائق (${driverName}) وإعادة تنشيط حسابه؟`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#34c759',
            cancelButtonColor: '#ff3b30',
            confirmButtonText: 'نعم، فك الحظر والتصفير',
            cancelButtonText: 'إلغاء'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const response = await fetch('/manager/users/unblock', {
                        method: 'POST',
                        headers: fetchHeaders,
                        body: JSON.stringify({
                            user_id: driverId
                        })
                    });

                    const resData = await response.json();

                    if (response.ok && resData.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'نجاح العملية',
                            text: resData.message,
                            confirmButtonColor: '#0071e3'
                        });

                        const violationsBadge = document.getElementById(`driver-violations-${driverId}`);
                        const statusBadge = document.getElementById(`driver-status-${driverId}`);
                        const unblockBtn = document.getElementById(`unblock-btn-${driverId}`);

                        if (violationsBadge) {
                            violationsBadge.innerText = '0';
                        }
                        if (statusBadge) {
                            statusBadge.className = 'badge bg-success px-3 py-1';
                            statusBadge.innerText = 'نشط';
                        }
                        if (unblockBtn) {
                            unblockBtn.disabled = true;
                            unblockBtn.className = 'btn btn-sm btn-outline-secondary rounded-pill px-3';
                            unblockBtn.innerText = 'تم التصفير';
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ',
                            text: resData.message || 'فشل إلغاء حظر السائق.',
                            confirmButtonColor: '#ff3b30'
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطأ اتصال',
                        text: 'تعذر الاتصال بالخادم.',
                        confirmButtonColor: '#ff3b30'
                    });
                }
            }
        });
    }

    let currentReportPeriod = 'this_month';
    let simulationChartInstance = null;

    function setQuickPeriod(period, btnEl) {
        currentReportPeriod = period;
        document.querySelectorAll('#reportFilterForm .btn-group .btn').forEach(b => b.classList.remove('active'));
        if (btnEl) btnEl.classList.add('active');
        document.getElementById('filterStartDate').value = '';
        document.getElementById('filterEndDate').value = '';
        fetchAndRenderReportData();
    }

    function resetQuickPeriodBtns() {
        currentReportPeriod = 'custom';
        document.querySelectorAll('#reportFilterForm .btn-group .btn').forEach(b => b.classList.remove('active'));
    }

    function applyReportFilter(e) {
        if (e) e.preventDefault();
        fetchAndRenderReportData();
    }

    async function fetchAndRenderReportData() {
        const startDate = document.getElementById('filterStartDate').value;
        const endDate = document.getElementById('filterEndDate').value;

        let queryParams = new URLSearchParams();
        queryParams.set('format', 'json');

        if (startDate || endDate) {
            currentReportPeriod = 'custom';
            if (startDate) queryParams.set('start_date', startDate);
            if (endDate) queryParams.set('end_date', endDate);
        } else {
            queryParams.set('period', currentReportPeriod);
        }

        // Update Download PDF and Excel links
        let pdfParams = new URLSearchParams(queryParams);
        pdfParams.set('format', 'pdf');
        document.getElementById('exportPdfBtn').href = `/manager/reports/export?${pdfParams.toString()}`;

        let excelParams = new URLSearchParams(queryParams);
        excelParams.set('format', 'excel');
        document.getElementById('exportExcelBtn').href = `/manager/reports/export?${excelParams.toString()}`;

        try {
            const response = await fetch(`/manager/reports/export?${queryParams.toString()}`);
            const resData = await response.json();

            if (resData.status === 'success' && resData.data) {
                const data = resData.data;

                // Update Financial KPI cards
                if (data.financial) {
                    document.getElementById('valTotalRevenue').innerText = `${parseFloat(data.financial.total_revenue || 0).toFixed(2)} د.ل`;
                    document.getElementById('valTotalExpenses').innerText = `${parseFloat(data.financial.total_expenses || 0).toFixed(2)} د.ل`;
                    document.getElementById('valNetProfit').innerText = `${parseFloat(data.financial.net_profit || 0).toFixed(2)} د.ل`;
                    document.getElementById('valAvgDailyIncome').innerText = `${parseFloat(data.financial.avg_daily_income || 0).toFixed(2)} د.ل`;
                }

                // Update Operational KPI cards
                if (data.operational) {
                    document.getElementById('valCarsEntered').innerText = `${data.operational.cars_entered || 0} سيارة`;
                    document.getElementById('valCarsExited').innerText = `${data.operational.cars_exited || 0} سيارة`;
                    document.getElementById('valTotalParkingHours').innerText = `${parseFloat(data.operational.total_parking_hours || 0).toFixed(1)} ساعة`;
                    document.getElementById('valOccupancyRate').innerText = `${parseFloat(data.operational.occupancy_rate || 0).toFixed(1)}%`;
                }
            }
        } catch (err) {
            console.error('Error fetching report metrics:', err);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        fetchAndRenderReportData();
        try {
            const chartCanvas = document.getElementById('financialLineChart');
            if (chartCanvas) {
                const chartLabels = {!! json_encode($financialData['dailyLabels']) !!};
                const revenueData = {!! json_encode($financialData['dailyRevenue']) !!};
                const countData = {!! json_encode($financialData['dailyCount']) !!};

                const ctx = chartCanvas.getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: chartLabels,
                        datasets: [
                            {
                                label: '💰 الإيرادات اليومية (نقاط)',
                                data: revenueData,
                                borderColor: '#34c759',
                                backgroundColor: 'rgba(52, 199, 89, 0.7)',
                                borderWidth: 1
                            },
                            {
                                label: '🔄 عدد عمليات الشحن المعتمدة',
                                data: countData,
                                borderColor: '#ff9900',
                                backgroundColor: 'rgba(255, 153, 0, 0.7)',
                                borderWidth: 1,
                                yAxisID: 'y1'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    font: {
                                        family: 'system-ui, sans-serif',
                                        weight: 'bold'
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        family: 'system-ui, sans-serif'
                                    }
                                }
                            },
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                title: {
                                    display: true,
                                    text: 'قيمة النقاط (💰)',
                                    font: {
                                        family: 'system-ui, sans-serif',
                                        weight: 'bold'
                                    }
                                },
                                grid: {
                                    color: 'rgba(255, 255, 255, 0.08)'
                                }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                title: {
                                    display: true,
                                    text: 'عدد العمليات (🔄)',
                                    font: {
                                        family: 'system-ui, sans-serif',
                                        weight: 'bold'
                                    }
                                },
                                grid: {
                                    drawOnChartArea: false,
                                },
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }
        } catch (exception) {
            console.error("خطأ أثناء تهيئة الرسم البياني الخطي للتقرير المالي:", exception);
        }
    });

    function initMap() {
        map = L.map('map').setView([32.8872, 13.1913], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        existingParkings.forEach(parking => {
            if (parking.latitude && parking.longitude) {
                L.marker([parking.latitude, parking.longitude], {icon: orangeIcon})
                    .addTo(map)
                    .bindPopup(`
                        <div style="direction: rtl; text-align: right; font-family: sans-serif; min-width: 150px;">
                            <h6 class="fw-bold mb-1 text-primary"><i class="fas fa-parking me-1"></i> ${parking.parking_name}</h6>
                            <small class="text-muted d-block mb-2">ساحة وقوف فعالة</small>
                            <p class="mb-1 text-dark small"><b>إجمالي السعة:</b> ${parking.total_capacity} مركبة</p>
                            <p class="mb-0 text-success small"><b>الشواغر المتوفرة:</b> ${parking.available_capacity} مركبة</p>
                        </div>
                    `);
            }
        });

        map.on('click', function(e) {
            document.getElementById('latInput').value = e.latlng.lat.toFixed(6);
            document.getElementById('lngInput').value = e.latlng.lng.toFixed(6);

            if (marker) marker.setLatLng(e.latlng);
            else marker = L.marker(e.latlng).addTo(map);
        });
    }

    let managedParkingsMapObj = null;
    let managedParkingsMarkers = [];

    function initManagedParkingsMap() {
        if (managedParkingsMapObj) {
            setTimeout(() => { managedParkingsMapObj.invalidateSize(); }, 300);
            return;
        }

        const mapContainer = document.getElementById('managedParkingsMap');
        if (!mapContainer) return;

        let defaultLat = 32.8872;
        let defaultLng = 13.1913;

        if (existingParkings.length > 0 && existingParkings[0].latitude && existingParkings[0].longitude) {
            defaultLat = parseFloat(existingParkings[0].latitude);
            defaultLng = parseFloat(existingParkings[0].longitude);
        }

        managedParkingsMapObj = L.map('managedParkingsMap').setView([defaultLat, defaultLng], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(managedParkingsMapObj);

        existingParkings.forEach(parking => {
            if (parking.latitude && parking.longitude) {
                const pLat = parseFloat(parking.latitude);
                const pLng = parseFloat(parking.longitude);

                const m = L.marker([pLat, pLng], { icon: orangeIcon })
                    .addTo(managedParkingsMapObj)
                    .bindPopup(`
                        <div style="direction: rtl; text-align: right; font-family: sans-serif; min-width: 160px;">
                            <h6 class="fw-bold mb-1 text-primary"><i class="fas fa-parking me-1"></i> ${parking.parking_name}</h6>
                            <small class="text-muted d-block mb-2">📍 ${parking.location_park}</small>
                            <p class="mb-1 text-dark small"><b>السعة الكلية:</b> ${parking.total_capacity} مركبة</p>
                            <p class="mb-1 text-success small"><b>الشواغر المتوفرة:</b> ${parking.available_capacity} مركبة</p>
                            <p class="mb-0 text-secondary small"><b>الموظف المسؤول:</b> ${parking.employee_name || 'غير معين'}</p>
                        </div>
                    `);

                managedParkingsMarkers.push({ id: parking.id, lat: pLat, lng: pLng, marker: m });
            }
        });

        if (managedParkingsMarkers.length > 1) {
            const group = new L.featureGroup(managedParkingsMarkers.map(m => m.marker));
            managedParkingsMapObj.fitBounds(group.getBounds().pad(0.2));
        }
    }

    function focusParkingOnMap(lat, lng, parkingName) {
        if (!managedParkingsMapObj) {
            initManagedParkingsMap();
        }
        const targetLat = parseFloat(lat);
        const targetLng = parseFloat(lng);
        managedParkingsMapObj.setView([targetLat, targetLng], 16, { animate: true });

        const item = managedParkingsMarkers.find(m => Math.abs(m.lat - targetLat) < 0.0001 && Math.abs(m.lng - targetLng) < 0.0001);
        if (item) {
            item.marker.openPopup();
        }

        const mapEl = document.getElementById('managedParkingsMap');
        if (mapEl) {
            mapEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    async function submitParking() {
        const name = document.getElementById('parkingName').value;
        const location_park = document.getElementById('parkingLocation').value; 
        const capacity = document.getElementById('parkingCapacity').value;
        const lat = document.getElementById('latInput').value;
        const lng = document.getElementById('lngInput').value;

        if(!name || !location_park || !capacity || !lat || !lng) {
            Swal.fire({icon: 'warning', title: 'بيانات ناقصة', text: 'يرجى تعبئة جميع الحقول وتحديد الموقع على الخريطة.'});
            return;
        }

        Swal.fire({ title: 'جاري الحفظ...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

        try {
            const response = await fetch('/manager/parkings/store', {
                method: 'POST',
                headers: fetchHeaders,
                body: JSON.stringify({
                    name: name,
                    location_park: location_park,
                    total_capacity: capacity,
                    latitude: lat,
                    longitude: lng
                })
            });

            const data = await response.json();

            if (response.ok) {
                Swal.fire({icon: 'success', title: 'نجاح 🎉', text: data.message}).then(() => {
                    location.reload();
                });
            } else {
                throw new Error(data.message || 'حدث خطأ أثناء حفظ الساحة.');
            }
        } catch (error) {
            Swal.fire({icon: 'error', title: 'عذراً', text: error.message});
        }
    }

    async function generateManagerAiReport() {
        const btn = document.getElementById('generateManagerAiReportBtn');
        const loadingState = document.getElementById('aiManagerReportLoadingState');
        const reportComponent = document.getElementById('aiManagerReportDisplayComponent');
        const scope = document.getElementById('managerAiReportScopeSelect')?.value || 'all';
        const timeframe = document.getElementById('managerAiReportTimeframeSelect')?.value || 'current_quarter';

        if (!btn || !loadingState || !reportComponent) return;

        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status"></span> Querying Database & Synthesizing AI Report...`;
        loadingState.classList.remove('d-none');
        reportComponent.classList.add('d-none');

        try {
            const response = await fetch(`/manager/ai-financial-report?scope=${scope}&timeframe=${timeframe}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();

            loadingState.classList.add('d-none');

            if (data.status === 'success') {
                renderAiReportData(data, 'manager');
                reportComponent.classList.remove('d-none');
                reportComponent.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                Swal.fire('خطأ', data.message || 'فشل توليد التقرير الذكي.', 'error');
            }
        } catch (e) {
            loadingState.classList.add('d-none');
            Swal.fire('خطأ اتصال', 'تعذر الاتصال بمركز التحليل الذكي للنظام.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = `<i class="fas fa-wand-magic-sparkles"></i> <span>Generate AI Report</span>`;
        }
    }

    function renderAiReportData(data, prefix = '') {
        const p = prefix ? (prefix + 'Ai') : 'ai';
        const pLower = prefix ? prefix : '';

        const timeElem = document.getElementById(pLower ? `${pLower}ReportTimestampDisplay` : 'reportTimestampDisplay');
        if (timeElem) timeElem.innerText = data.generated_at || new Date().toLocaleString();

        // 1. Executive Summary
        const execText = document.getElementById(`${p}ExecutiveSummaryText`);
        if (execText) execText.innerText = data.executive_summary;

        const netRevElem = document.getElementById(`${p}TotalNetRevenueVal`);
        if (netRevElem) netRevElem.innerHTML = `${Number(data.metrics.total_revenue).toLocaleString('en-US', {minimumFractionDigits: 2})} <small class="fs-6">LYD</small>`;

        const marginElem = document.getElementById(`${p}GrossMarginVal`);
        if (marginElem) marginElem.innerText = `${data.metrics.gross_margin}%`;

        const occElem = document.getElementById(`${p}OccupancyRateVal`);
        if (occElem) occElem.innerText = `${data.metrics.occupancy_rate}%`;

        const healthElem = document.getElementById(`${p}HealthScoreVal`);
        if (healthElem) healthElem.innerText = `${data.metrics.health_score} / 100`;

        // 2. Revenue Performance
        const reservationsElem = document.getElementById(`${p}TotalReservationsVal`);
        if (reservationsElem) reservationsElem.innerText = `${data.metrics.total_reservations} Bookings`;

        // 3. Expense Analysis
        const expElem = document.getElementById(`${p}TotalExpensesVal`);
        if (expElem) expElem.innerHTML = `${Number(data.metrics.total_expenses).toLocaleString('en-US', {minimumFractionDigits: 2})} LYD`;

        const profitElem = document.getElementById(`${p}NetProfitVal`);
        if (profitElem) profitElem.innerHTML = `${Number(data.metrics.net_profit).toLocaleString('en-US', {minimumFractionDigits: 2})} LYD`;

        // 4. Top Performing Branch
        const topName = document.getElementById(`${p}TopBranchName`);
        if (topName) topName.innerText = data.top_branch.name;

        const topRev = document.getElementById(`${p}TopBranchRevenue`);
        if (topRev) topRev.innerText = `${Number(data.top_branch.gross_revenue).toLocaleString('en-US', {minimumFractionDigits: 2})} LYD`;

        const topOcc = document.getElementById(`${p}TopBranchOccupancy`);
        if (topOcc) topOcc.innerText = `${data.top_branch.occupancy_rate}%`;

        // 5. Lowest Performing Branch
        const lowName = document.getElementById(`${p}LowestBranchName`);
        if (lowName) lowName.innerText = data.lowest_branch.name;

        const lowRev = document.getElementById(`${p}LowestBranchRevenue`);
        if (lowRev) lowRev.innerText = `${Number(data.lowest_branch.gross_revenue).toLocaleString('en-US', {minimumFractionDigits: 2})} LYD`;

        const lowOcc = document.getElementById(`${p}LowestBranchOccupancy`);
        if (lowOcc) lowOcc.innerText = `${data.lowest_branch.occupancy_rate}%`;

        // 6. Branch Statistics Table
        const tbody = document.getElementById(`${p}BranchStatsTableBody`);
        if (tbody && data.branch_statistics) {
            tbody.innerHTML = '';
            data.branch_statistics.forEach((b, idx) => {
                const rankBadge = idx === 0 
                    ? '<span class="badge bg-warning text-dark fw-bold">🥇 #1 Top Performer</span>'
                    : (idx === 1 ? '<span class="badge bg-secondary text-white fw-bold">🥈 #2 Performer</span>'
                    : (idx === 2 ? '<span class="badge bg-info text-dark fw-bold">🥉 #3 Performer</span>' : `<span class="badge bg-light text-dark">#${idx + 1} Stable</span>`));

                tbody.innerHTML += `
                    <tr>
                        <td class="text-start fw-bold text-dark">
                            <i class="fas fa-parking text-primary me-2"></i> ${b.name}
                        </td>
                        <td>${b.total_capacity} slots</td>
                        <td><span class="badge ${b.occupancy_rate >= 80 ? 'bg-success' : (b.occupancy_rate >= 50 ? 'bg-primary' : 'bg-warning text-dark')}">${b.occupancy_rate}%</span></td>
                        <td class="fw-bold text-primary">${Number(b.gross_revenue).toLocaleString('en-US', {minimumFractionDigits: 2})} LYD</td>
                        <td class="fw-bold text-success">${Number(b.net_profit).toLocaleString('en-US', {minimumFractionDigits: 2})} LYD</td>
                        <td>${rankBadge}</td>
                    </tr>
                `;
            });
        }

        // 7. Strategic Recommendations
        const recsContainer = document.getElementById(`${p}RecommendationsContainer`);
        if (recsContainer && data.recommendations) {
            recsContainer.innerHTML = '';
            data.recommendations.forEach(r => {
                recsContainer.innerHTML += `
                    <div class="col-md-6">
                        <div class="card border-0 p-4 rounded-4 shadow-sm h-100 border-start border-4 ${r.border_class}" style="background: #ffffff;">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="fw-bold ${r.title_class} mb-0">${r.title}</h6>
                                <span class="badge ${r.badge_class}">${r.priority}</span>
                            </div>
                            <p class="text-muted small mb-0">${r.description}</p>
                        </div>
                    </div>
                `;
            });
        }
    }

    let managerAiChatHistory = [];

    async function handleAiChatSubmit(event, prefix = '') {
        if (event) event.preventDefault();

        const inputElem = document.getElementById(prefix ? `${prefix}AiChatInput` : 'aiChatInput');
        const container = document.getElementById(prefix ? `${prefix}AiChatMessagesContainer` : 'aiChatMessagesContainer');
        const indicator = document.getElementById(prefix ? `${prefix}AiChatTypingIndicator` : 'aiChatTypingIndicator');
        const sendBtn = document.getElementById(prefix ? `${prefix}AiChatSendBtn` : 'aiChatSendBtn');

        if (!inputElem || !container) return;

        const userMessage = inputElem.value.trim();
        if (!userMessage) return;

        inputElem.value = '';
        appendUserChatMessage(container, userMessage);
        container.scrollTop = container.scrollHeight;

        if (indicator) indicator.classList.remove('d-none');
        if (sendBtn) sendBtn.disabled = true;

        try {
            const endpoint = prefix === 'manager' ? '/manager/ai-chat' : '/developer/ai-chat';
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    message: userMessage,
                    history: managerAiChatHistory
                })
            });

            const data = await response.json();

            if (indicator) indicator.classList.add('d-none');

            if (data.status === 'success') {
                appendAiChatMessage(container, data.reply, data.source, data.timestamp);
                managerAiChatHistory.push({ user: userMessage, assistant: data.reply });
            } else {
                appendAiChatMessage(container, '⚠️ ' + (data.message || 'Error processing request.'), 'System Error', new Date().toLocaleTimeString());
            }
        } catch (e) {
            if (indicator) indicator.classList.add('d-none');
            appendAiChatMessage(container, '⚠️ Connection error. Please check network.', 'System Error', new Date().toLocaleTimeString());
        } finally {
            if (sendBtn) sendBtn.disabled = false;
            container.scrollTop = container.scrollHeight;
        }
    }

    function sendSuggestedQuestion(question, prefix = '') {
        const inputElem = document.getElementById(prefix ? `${prefix}AiChatInput` : 'aiChatInput');
        if (inputElem) {
            inputElem.value = question;
            handleAiChatSubmit(null, prefix);
        }
    }

    function clearAiChatHistory(prefix = '') {
        managerAiChatHistory = [];
        const container = document.getElementById(prefix ? `${prefix}AiChatMessagesContainer` : 'aiChatMessagesContainer');
        if (container) {
            container.innerHTML = `
                <div class="d-flex gap-3 mb-4">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0" style="width: 38px; height: 38px; background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                        <i class="fas fa-robot small"></i>
                    </div>
                    <div class="p-3 rounded-4 bg-white shadow-sm border border-slate-200 text-dark max-w-xl">
                        <p class="mb-2 fw-bold text-indigo" style="color: #4338ca;">👋 تم مسح سجل المحادثة. كيف يمكنني مساعدتك الآن؟</p>
                        <p class="mb-2 small leading-relaxed">
                            اسأل عن الإيرادات، معدل الإشغال، الساحات المتصدرة، أو تحليلات التكاليف والتوصيات.
                        </p>
                        <span class="text-muted x-small">System Assistant • Just now</span>
                    </div>
                </div>
            `;
        }
    }

    function appendUserChatMessage(container, message) {
        const timeStr = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        const html = `
            <div class="d-flex gap-3 justify-content-end mb-4">
                <div class="p-3 rounded-4 text-white shadow-sm max-w-xl" style="background: linear-gradient(135deg, #0071e3, #34c759) !important;">
                    <p class="mb-1 small leading-relaxed fw-semibold">${escapeHtml(message)}</p>
                    <span class="text-white-50 x-small d-block text-end">${timeStr}</span>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0" style="width: 38px; height: 38px; background: #0071e3;">
                    <i class="fas fa-user small"></i>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
    }

    function appendAiChatMessage(container, reply, source = 'SpotLy AI Engine', timeStr = '') {
        const formattedReply = escapeHtml(reply).replace(/\n/g, '<br>');
        const html = `
            <div class="d-flex gap-3 mb-4">
                <div class="rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0" style="width: 38px; height: 38px; background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                    <i class="fas fa-robot small"></i>
                </div>
                <div class="p-3 rounded-4 bg-white shadow-sm border border-slate-200 text-dark max-w-xl">
                    <div class="small leading-relaxed text-dark">${formattedReply}</div>
                    <span class="text-muted x-small d-block mt-2">${source} • ${timeStr || new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</span>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
    }

    function escapeHtml(str) {
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }
    </script>
</body>
</html>
