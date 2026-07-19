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
            <a class="nav-link" onclick="switchTab('addParkingTab', this)"><i class="fas fa-map-pin"></i> إضافة ساحة ميدان</a>
            <a class="nav-link" onclick="switchTab('violationsTab', this)"><i class="fas fa-ban"></i> إدارة الحظر والمخالفات</a>
            <a class="nav-link" onclick="switchTab('financialReportTab', this)"><i class="fas fa-chart-bar"></i> التقرير المالي</a>
            <a class="nav-link" onclick="switchTab('profileTab', this)"><i class="fas fa-user-cog"></i> البيانات الشخصية</a>
            <a class="nav-link" onclick="switchTab('auditLogTab', this)"><i class="fas fa-history"></i> سجل العمليات والتدقيق</a>
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
                <p class="mb-0 text-muted">تتيح لك هذه المنصة الإشراف الكامل على الساحات المسندة إليك، مراقبة مستويات الإشغال والشاغر بشكل لحظي، وتتبع الحجوزات وتقارير الدخول والخروج.</p>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-12">
                    <div class="card p-4">
                        <h5 class="fw-bold mb-3">📌 إرشادات تشغيلية سريعة</h5>
                        <ul class="mb-0">
                            <li class="mb-2">تأكد من تحديث أرقام هواتف الموظفين الميدانيين لتلقي الإشعارات الطارئة.</li>
                            <li class="mb-0">يمكنك مراجعة كافة تفاصيل السعة التشغيلية والشواغر اللحظية عبر تبويب <strong>الساحات المدارة</strong>.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION 2: Managed Parkings -->
        <section id="parkingsTab" class="content-section d-none">
            <div class="card">
                <div class="card-header bg-gradient p-4 border-0">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-list"></i> قائمة ساحات مواقف السيارات المدارة</h5>
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
                                    <th>الموظف المسؤول</th>
                                    <th>هاتف الموظف</th>
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
                                        <td>{{ $parking->employee_name ?? 'غير معين' }}</td>
                                        <td>
                                            @if($parking->employee_phone)
                                                <span dir="ltr">{{ $parking->employee_phone }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($parking->employee_id)
                                                <button onclick="confirmUnlinkEmployee({{ $parking->id }}, '{{ addslashes($parking->parking_name) }}', '{{ addslashes($parking->employee_name ?? 'الموظف') }}')" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                    🔓 إلغاء ربط الموظف
                                                </button>
                                            @else
                                                <button onclick="openCreateEmployeeModal({{ $parking->id }}, '{{ addslashes($parking->parking_name) }}')" class="btn btn-sm btn-primary rounded-pill px-3">
                                                    👤 إنشاء حساب موظف
                                                </button>
                                            @endif
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
        </section>

        <!-- SECTION 3: Add Parking Area -->
        <section id="addParkingTab" class="content-section d-none">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h5 class="mb-4 fw-bold text-primary"><i class="fas fa-map-marked-alt"></i> تحديد وإنشاء موقف جديد</h5>
                    <form id="addParkingForm">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-secondary">اسم الموقف أو الساحة</label>
                                    <input type="text" id="parkingName" class="form-control form-control-lg" placeholder="مثال: موقف الجامعة الشمالي" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-secondary">وصف موقع الساحة (location_park)</label>
                                    <input type="text" id="parkingLocation" class="form-control form-control-lg" placeholder="مثال: بجوار البوابة الرئيسية" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-secondary">السعة الكلية للمركبات</label>
                                    <input type="number" id="parkingCapacity" class="form-control form-control-lg" placeholder="مثال: 150" required>
                                </div>
                                <hr class="border-secondary border-opacity-25">
                                <div class="alert alert-warning py-2 mb-3">
                                    <small><i class="fas fa-hand-pointer"></i> انقر على الخريطة لتسجيل الإحداثيات تلقائياً.</small>
                                </div>
                                <div class="row g-2 mb-4">
                                    <div class="col-6">
                                        <label class="form-label text-muted small">خط العرض (Lat)</label>
                                        <input type="text" id="latInput" class="form-control bg-light text-center" readonly required style="direction: ltr;">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label text-muted small">خط الطول (Lng)</label>
                                        <input type="text" id="lngInput" class="form-control bg-light text-center" readonly required style="direction: ltr;">
                                    </div>
                                </div>
                                <button type="button" onclick="submitParking()" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">حفظ وتفعيل الموقف 💾</button>
                            </div>
                            
                            <div class="col-md-8">
                                <div id="map" style="height: 480px; width: 100%; border-radius: 16px; border: 1px solid var(--glass-border); z-index: 1;"></div>
                            </div>
                        </div>
                    </form>
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

        <!-- SECTION 4: Block & Violation Management -->
        <section id="violationsTab" class="content-section d-none">
            <div class="card">
                <div class="card-header bg-gradient p-4 border-0">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-ban"></i> إدارة حسابات السائقين المحظورين</h5>
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
                                        <td><span class="badge bg-warning text-dark px-3 py-1" id="driver-violations-{{ $driver->id }}">{{ $driver->fake_booking_count }}</span></td>
                                        <td>
                                            @if($driver->status === 'blocked')
                                                <span class="badge bg-danger px-3 py-1" id="driver-status-{{ $driver->id }}">محظور</span>
                                            @else
                                                <span class="badge bg-success px-3 py-1" id="driver-status-{{ $driver->id }}">نشط</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button onclick="confirmUnblockDriver({{ $driver->id }}, '{{ addslashes($driver->driver_name) }}')" class="btn btn-sm btn-success rounded-pill px-3" id="unblock-btn-{{ $driver->id }}">
                                                ✅ فك الحظر وتصفير المخالفات
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-muted py-5">لا يوجد سائقون محظورون حالياً.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION 5: Financial Report -->
        <section id="financialReportTab" class="content-section d-none">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3 bg-white bg-opacity-75 p-4 rounded-4 shadow-sm border border-white border-opacity-50">
                <div>
                    <h5 class="fw-bold text-dark mb-1">📊 التقارير المالية والإحصائية للساحات</h5>
                    <p class="text-muted mb-0 small">تتبع وحلل الإيرادات والتعويضات اليومية لساحاتك بشكل دقيق</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('manager.reports.export', ['format' => 'csv']) }}" class="btn btn-success btn-sm fw-bold px-3 py-2 rounded-pill shadow-sm d-flex align-items-center gap-2">
                        <span>📥</span> تحميل التقرير بصيغة CSV
                    </a>
                    <a href="{{ route('manager.reports.export', ['format' => 'json']) }}" class="btn btn-dark btn-sm fw-bold px-3 py-2 rounded-pill shadow-sm d-flex align-items-center gap-2" style="background-color: #2c3e50; border-color: #2c3e50;">
                        <span>📥</span> تحميل التقرير بصيغة JSON
                    </a>
                </div>
            </div>

            <!-- Financial Statistics Cards -->
            <div class="row g-4 mb-4">
                <!-- Total Revenue -->
                <div class="col-md-4">
                    <div class="card border-0 text-white shadow-lg p-4 h-100" style="background: linear-gradient(135deg, #11998e, #38ef7d); border-radius: 20px;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 fw-bold mb-1">💰 إجمالي إيرادات الساحات</h6>
                                <h2 class="fw-bold mb-0 text-white stat-card-value">{{ number_format($financialData['totalRevenue'], 2) }} <span class="fs-6 fw-normal text-white-50">نقطة</span></h2>
                            </div>
                            <span class="fs-1 opacity-75">📥</span>
                        </div>
                    </div>
                </div>

                <!-- Total Refunds -->
                <div class="col-md-4">
                    <div class="card border-0 text-white shadow-lg p-4 h-100" style="background: linear-gradient(135deg, #ff9900, #ff5500); border-radius: 20px;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 fw-bold mb-1">🔄 إجمالي النقاط المسترجعة</h6>
                                <h2 class="fw-bold mb-0 text-white stat-card-value">{{ number_format($financialData['totalRefundedPoints'], 2) }} <span class="fs-6 fw-normal text-white-50">نقطة</span></h2>
                            </div>
                            <span class="fs-1 opacity-75">📤</span>
                        </div>
                    </div>
                </div>

                <!-- Refund percentage -->
                <div class="col-md-4">
                    <div class="card border-0 text-white shadow-lg p-4 h-100" style="background: linear-gradient(135deg, #8a2387, #e94057, #f27121); border-radius: 20px;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 fw-bold mb-1">📈 نسبة التعويضات الإجمالية</h6>
                                <h2 class="fw-bold mb-0 text-white stat-card-value">{{ $financialData['compensationPercentage'] }} <span class="fs-6 fw-normal text-white-50">%</span></h2>
                            </div>
                            <span class="fs-1 opacity-75">📊</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daily performance charts -->
            <div class="card mb-4">
                <div class="card-header p-4 border-0">
                    <h5 class="mb-0 fw-bold">📊 أعمدة حركة شحن النقاط والإيرادات اليومية (آخر 30 يوماً)</h5>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 350px; width: 100%;">
                        <canvas id="financialLineChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Comparison Table -->
            <div class="card mb-4">
                <div class="card-header p-4 border-0">
                    <h5 class="mb-0 fw-bold">📊 مقارنة الأداء المالي مع إجمالي النظام</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-center mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>نطاق التقرير المالي</th>
                                    <th>إجمالي الإيرادات (نقاط معتمدة)</th>
                                    <th>إجمالي التعويضات المسترجعة (عند الإلغاء)</th>
                                    <th>نسبة التعويضات الإجمالية</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-success fw-bold">
                                    <td class="text-dark">📍 الساحات المدارة التابعة لك</td>
                                    <td class="text-success">{{ number_format($financialData['totalRevenue'], 2) }} نقطة</td>
                                    <td class="text-danger">{{ number_format($financialData['totalRefundedPoints'], 2) }} نقطة</td>
                                    <td>
                                        <span class="badge bg-success px-3 py-2 rounded-pill">{{ $financialData['compensationPercentage'] }} %</span>
                                    </td>
                                </tr>
                                <tr class="table-light">
                                    <td class="text-muted">🌐 إجمالي النظام بالكامل</td>
                                    <td class="text-muted">{{ number_format($financialData['systemTotalRevenue'], 2) }} نقطة</td>
                                    <td class="text-muted">{{ number_format($financialData['systemTotalRefundedPoints'], 2) }} نقطة</td>
                                    <td>
                                        <span class="badge bg-secondary px-3 py-2 rounded-pill">{{ $financialData['systemCompensationPercentage'] }} %</span>
                                    </td>
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
    const currentManagerAccountId = storedUserData ? (storedUserData.id || storedUserData.account_id) : null;

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
            if (storedUserData) {
                document.getElementById('managerNameDisplay').innerText = 'مرحباً، ' + storedUserData.name;
            } else {
                window.location.href = '/login';
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

            if (sectionId === 'addParkingTab') {
                if (!map) initMap();
                else setTimeout(() => { map.invalidateSize(); }, 300);
            }
            if (sectionId === 'profileTab') {
                loadProfileData();
            }
            if (sectionId === 'auditLogTab') {
                loadAuditLogs();
            }
        } catch (exception) {
            console.error("خطأ أثناء التبديل بين التبويبات", exception);
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

    function openCreateEmployeeModal(parkingId, parkingName) {
        document.getElementById('modalParkingId').value = parkingId;
        document.getElementById('modalParkingName').value = parkingName;
        document.getElementById('createEmployeeForm').reset();
        
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
        
        let requestData = {
            parking_id: parkingId,
            assignment_type: assignmentType
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

    document.addEventListener('DOMContentLoaded', function() {
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
    </script>
</body>
</html>
