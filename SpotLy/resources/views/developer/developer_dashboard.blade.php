<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spotly - لوحة تحكم المطور والإدارة العليا</title>
    
    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
                --glass-bg: rgba(28, 28, 30, 0.55);
                --glass-border: rgba(255, 255, 255, 0.08);
                --glass-shadow: rgba(0, 0, 0, 0.4);
                --text-color: #f5f5f7;
                --text-muted: #86868b;
                --primary-color: #2997ff;
                --primary-gradient: linear-gradient(135deg, #2997ff, #0071e3);
                --input-bg: rgba(255, 255, 255, 0.05);
                --input-border: rgba(255, 255, 255, 0.1);
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

        .blob-1 { width: 600px; height: 600px; background: #ff007f; top: -150px; left: -100px; }
        .blob-2 { width: 700px; height: 700px; background: #0071e3; bottom: -200px; right: -100px; animation-duration: 32s; }
        .blob-3 { width: 400px; height: 400px; background: #00f6ff; top: 20%; left: 40%; animation-duration: 20s; }

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
            opacity: 0.75;
            padding: 13px 18px;
            margin: 4px 0;
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
            background: rgba(255, 255, 255, 0.25);
            box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.4), 0 4px 10px rgba(0, 0, 0, 0.03);
            font-weight: 600;
            transform: translateX(-4px);
            color: var(--text-color);
        }

        .sidebar .nav-link.active {
            background: var(--primary-gradient);
            color: white !important;
            box-shadow: 0 8px 20px rgba(0, 113, 227, 0.3);
        }

        .main-content {
            margin-right: 320px;
            padding: 20px 40px 40px 20px;
            min-height: 100vh;
        }

        /* Glass Header */
        .top-header {
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

        /* stat card styling */
        .stat-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: var(--card-radius);
            box-shadow: 0 10px 30px var(--glass-shadow);
            transition: transform 0.25s ease;
            border-top: 4px solid;
            padding: 20px 18px;
        }
        .stat-card:hover { transform: translateY(-3px); }
        .border-t-blue { border-top-color: #0071e3; }
        .border-t-yellow { border-top-color: #ffcc00; }
        .border-t-green { border-top-color: #34c759; }
        .border-t-purple { border-top-color: #af52de; }
        .border-t-red { border-top-color: #ff3b30; }
        .border-t-cyan { border-top-color: #5ac8fa; }

        /* Map Leaflet */
        #map { 
            height: 550px; 
            width: 100%; 
            border-radius: 16px; 
            border: 1px solid var(--glass-border); 
            z-index: 1;
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

        /* Form Controls */
        .form-control, .form-select {
            background: var(--input-bg) !important;
            border: 1px solid var(--input-border) !important;
            color: var(--text-color) !important;
            border-radius: 14px !important;
            padding: 10px 16px !important;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color) !important;
            box-shadow: 0 0 0 3px rgba(0, 113, 227, 0.2) !important;
        }

        /* Custom buttons */
        .btn {
            border-radius: 14px;
            padding: 10px 22px;
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

        .badge {
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 30px;
        }

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
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-right: 0; padding: 20px; }
            .menu-toggle { display: block; }
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

    <!-- Floating Sidebar Navigation -->
    <div class="sidebar" id="dashboardSidebar">
        <div class="sidebar-brand">
            <h2 class="brand-title">Spotly 🚗</h2>
            <span class="badge bg-warning text-dark px-3 py-1 mt-1 fs-6 fw-bold">بوابة المطور والمدير العام</span>
        </div>
        <div class="nav flex-column mt-3 flex-grow-1">
            <a class="nav-link active" onclick="switchTab('overviewTab', this, 'لوحة التحكم الرئيسية 🏠')">
                <i class="fas fa-home me-2"></i> نظرة عامة
            </a>
            <a class="nav-link" onclick="switchTab('parkingMgmtTab', this, 'إدارة المواقف والفروع 🅿️')">
                <i class="fas fa-parking me-2"></i> إدارة المواقف والفروع
            </a>
            <a class="nav-link" onclick="switchTab('managerMgmtTab', this, 'إدارة المدراء المسؤولين 🛡️')">
                <i class="fas fa-user-shield me-2"></i> إدارة حسابات المدراء
            </a>
            <a class="nav-link" onclick="switchTab('aiAssistantTab', this, 'AI Assistant 🤖')">
                <i class="fas fa-robot me-2"></i> AI Assistant
            </a>
            <a class="nav-link" onclick="switchTab('reportsTab', this, 'قسم التقارير والتصدير 📑')">
                <i class="fas fa-file-export me-2"></i> التقارير والتصدير
            </a>
            <a class="nav-link" onclick="switchTab('addParkingTab', this, 'خريطة الساحات 🗺️')">
                <i class="fas fa-map-marked-alt me-2"></i> عرض خريطة الساحات
            </a>
            <a class="nav-link" onclick="switchTab('employeesTab', this, 'موظفو الميدان 👥')">
                <i class="fas fa-users me-2"></i> عرض موظفي الميدان
            </a>
        </div>
    </div>

    <div class="main-content">
        <!-- Top Glass Header -->
        <header class="top-header">
            <div class="d-flex align-items-center gap-3">
                <i class="fas fa-bars menu-toggle" onclick="toggleSidebarMenu()"></i>
                <h5 class="page-title mb-0 fw-bold" id="headerTitle">لوحة التحكم الرئيسية 🏠</h5>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="fw-bold text-dark-emphasis">مرحباً، <span id="developerNameDisplay">مدير النظام</span></span>
                <button class="btn btn-outline-danger btn-sm px-4 fw-bold rounded-pill" onclick="logoutDeveloper()">تسجيل الخروج</button>
            </div>
        </header>

        <div class="p-3">
            <div class="alert alert-info border-0 shadow-sm rounded-4 p-4 mb-4">
                📌 <strong>مرحباً بك في لوحة تحكم Spotly الشاملة:</strong> تتيح لك هذه اللوحة إدارة الفروع ومواقف السيارات، وتعيين المدراء وتحديث الحقول الميدانية تلقائياً.
            </div>

            <!-- SECTION 1: Overview & Stat Cards -->
            <section id="overviewTab" class="content-section">
                <!-- Stat Cards Grid -->
                <div class="row text-center g-3 mb-4">
                    <div class="col-md-4 col-lg-4">
                        <div class="stat-card border-t-purple">
                            <h6 class="fw-bold text-muted small mb-2"><i class="fas fa-parking me-1 text-secondary"></i> عدد الفروع</h6>
                            <h4 class="text-secondary mb-0 fw-bold">{{ $parkingsCount }} <small class="fs-6">مواقف</small></h4>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-4">
                        <div class="stat-card border-t-red">
                            <h6 class="fw-bold text-muted small mb-2"><i class="fas fa-user-shield me-1 text-danger"></i> عدد المدراء</h6>
                            <h4 class="text-danger mb-0 fw-bold">{{ $managersCount }} <small class="fs-6">مدير</small></h4>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-4">
                        <div class="stat-card border-t-cyan">
                            <h6 class="fw-bold text-muted small mb-2"><i class="fas fa-bolt me-1 text-info"></i> الحجوزات النشطة</h6>
                            <h4 class="text-info mb-0 fw-bold">{{ $activeBookingsCount }} <small class="fs-6">حجز</small></h4>
                        </div>
                    </div>
                </div>

                <!-- Occupancy Chart Overview -->
                <div class="row g-4 mb-4">
                    <div class="col-lg-12">
                        <div class="card p-4">
                            <h6 class="fw-bold text-primary mb-3"><i class="fas fa-pie-chart me-2"></i> مقارنة الإشغال والسعة الكلية</h6>
                            <div style="height: 280px; display: flex; align-items: center; justify-content: center;">
                                <canvas id="occupancyDoughnutChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Branch Summary Table -->
                <div class="card p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-primary mb-0"><i class="fas fa-building me-2"></i> ملخص أداء الفروع والمواقف</h6>
                        <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="switchTab('parkingMgmtTab', document.querySelectorAll('.sidebar .nav-link')[1], 'إدارة المواقف والفروع 🅿️')">
                            عرض كافة التفاصيل <i class="fas fa-arrow-left ms-1"></i>
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>اسم الفرع / الموقف</th>
                                    <th>السعة / الشواغر المتاحة</th>
                                    <th>نسبة الإشغال</th>
                                    <th>المدير المسؤول</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($branchPerformance as $b)
                                    <tr>
                                        <td class="fw-bold text-dark-emphasis">{{ $b['name'] }}</td>
                                        <td><span class="badge bg-secondary">{{ $b['available_capacity'] }} متاحة من {{ $b['total_capacity'] }}</span></td>
                                        <td>
                                            <div class="progress rounded-pill bg-light" style="height: 10px; min-width: 100px;">
                                                <div class="progress-bar bg-success rounded-pill" style="width: {{ $b['occupancy_rate'] }}%;"></div>
                                            </div>
                                            <small class="text-muted">{{ $b['occupancy_rate'] }}%</small>
                                        </td>
                                        <td><span class="badge bg-info text-dark">{{ $b['manager_name'] }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- SECTION 3: Parking Lots Management (CRUD & Details) -->
            <section id="parkingMgmtTab" class="content-section d-none">
                <div class="card p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="fw-bold text-primary mb-1"><i class="fas fa-parking me-2"></i> نظام إدارة الفروع ومواقف السيارات (CRUD)</h5>
                            <p class="text-muted small mb-0">إضافة، تعديل السعة، حذف، وعرض تفاصيل مواقف السيارات</p>
                        </div>
                        <button class="btn btn-success rounded-pill fw-bold" data-bs-toggle="modal" data-bs-target="#createParkingModal">
                            <i class="fas fa-plus-circle me-1"></i> إضافة موقف جديد
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>اسم الموقف</th>
                                    <th>الموقع / العنوان</th>
                                    <th>السعة الكلية</th>
                                    <th>الأماكن المتاحة</th>
                                    <th>الأماكن المشغولة</th>
                                    <th>المدير المسؤول</th>
                                    <th>الإجراءات التفصيلية</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($parkingsList as $parking)
                                    @php
                                        $occupied = max(0, $parking->total_capacity - $parking->available_capacity);
                                    @endphp
                                    <tr>
                                        <td class="fw-bold text-dark-emphasis">{{ $parking->name }}</td>
                                        <td>{{ $parking->location_park }}</td>
                                        <td><span class="badge bg-secondary fs-6">{{ $parking->total_capacity }}</span></td>
                                        <td><span class="badge bg-success fs-6">{{ $parking->available_capacity }}</span></td>
                                        <td><span class="badge bg-warning text-dark fs-6">{{ $occupied }}</span></td>
                                        <td>
                                            @if($parking->manager && $parking->manager->account)
                                                <span class="badge bg-info text-dark fw-bold"><i class="fas fa-user-shield me-1"></i> {{ $parking->manager->account->name }}</span>
                                            @else
                                                <span class="badge bg-light text-muted">غير معين</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-info rounded-pill px-3 me-1" 
                                                    onclick="viewParkingDetails({{ $parking->id }}, '{{ addslashes($parking->name) }}', '{{ addslashes($parking->location_park) }}', {{ $parking->total_capacity }}, {{ $parking->available_capacity }}, '{{ addslashes($parking->manager && $parking->manager->account ? $parking->manager->account->name : 'غير معين') }}', '{{ addslashes($parking->employee ? $parking->employee->account->name ?? 'موظف بوابات' : 'غير معين') }}')">
                                                <i class="fas fa-eye"></i> عرض
                                            </button>
                                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3 me-1" 
                                                    onclick="openEditParkingModal({{ $parking->id }}, '{{ addslashes($parking->name) }}', '{{ addslashes($parking->location_park) }}', {{ $parking->total_capacity }}, {{ $parking->available_capacity }})">
                                                <i class="fas fa-edit"></i> تعديل
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger rounded-pill px-3" 
                                                    onclick="deleteParkingLot({{ $parking->id }})">
                                                <i class="fas fa-trash"></i> حذف
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-muted py-4">لا توجد مواقف مسجلة حالياً.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- SECTION 4: Manager Management & Assignment -->
            <section id="managerMgmtTab" class="content-section d-none">
                <div class="card p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="fw-bold text-primary mb-1"><i class="fas fa-user-shield me-2"></i> إدارة المدراء المسؤولين وربط الفروع</h5>
                            <p class="text-muted small mb-0">إضافة مدراء، تعديل بياناتهم، وتحديث الربط التلقائي بحقل (manager_id)</p>
                        </div>
                        <button class="btn btn-primary rounded-pill fw-bold" data-bs-toggle="modal" data-bs-target="#createManagerModal">
                            <i class="fas fa-user-plus me-1"></i> إضافة مدير جديد
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>الاسم الكامل</th>
                                    <th>البريد الإلكتروني</th>
                                    <th>رقم الهاتف</th>
                                    <th>الفروع المسؤولة عنها</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات والتعيين</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($managers as $m)
                                    <tr id="manager-row-{{ $m->id }}">
                                        <td class="fw-bold text-dark-emphasis">{{ $m->account ? $m->account->name : 'مدير' }}</td>
                                        <td>{{ $m->account ? $m->account->email : '-' }}</td>
                                        <td>{{ $m->account ? $m->account->phone : '-' }}</td>
                                        <td>
                                            @if($m->parkings && $m->parkings->count() > 0)
                                                @foreach($m->parkings as $mp)
                                                    <span class="badge bg-primary me-1"><i class="fas fa-parking me-1"></i> {{ $mp->name }}</span>
                                                @endforeach
                                            @else
                                                <span class="badge bg-light text-muted">لا يوجد مواقف معينة</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($m->status === 'active')
                                                <span class="badge bg-success">نشط</span>
                                            @else
                                                <span class="badge bg-danger">معطل</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-info rounded-pill px-3 me-1" 
                                                    onclick="viewManagerDetails('{{ addslashes($m->account ? $m->account->name : 'مدير') }}', '{{ addslashes($m->account ? $m->account->email : '-') }}', '{{ addslashes($m->account ? $m->account->phone : '-') }}', '{{ addslashes($m->status) }}')">
                                                <i class="fas fa-id-card"></i> التفاصيل
                                            </button>
                                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3 me-1" 
                                                    onclick="openAssignParkingsModal({{ $m->account_id ? $m->account_id : $m->id }}, '{{ addslashes($m->account ? $m->account->name : 'المدير') }}')">
                                                <i class="fas fa-link"></i> تعيين / تغيير الفرع
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning rounded-pill px-3 me-1" 
                                                    onclick="toggleManagerActivation({{ $m->account_id ? $m->account_id : $m->id }})">
                                                <i class="fas fa-power-off"></i> تغيير الحالة
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger rounded-pill px-3" 
                                                    onclick="deleteManagerAccount({{ $m->id }})">
                                                <i class="fas fa-trash"></i> حذف
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-muted py-4">لا يوجد مدراء مسجلين حالياً.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
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
                            <select class="form-select" id="aiReportScopeSelect">
                                <option value="all" selected>🏢 All Parking Yards & Branches (الكل)</option>
                                <option value="top">🔥 High Volume Hubs (ميدان الشهداء & برج طرابلس)</option>
                                <option value="coastal">🌊 Coastal & Harbor Sector (طريق الشط & ميناء طرابلس)</option>
                            </select>
                        </div>
                        <div class="col-md-4 col-lg-4">
                            <label class="form-label fw-bold text-secondary small">Timeframe Horizon</label>
                            <select class="form-select" id="aiReportTimeframeSelect">
                                <option value="current_quarter" selected>📅 Current Quarter Q3 2026</option>
                                <option value="last_30_days">📆 Past 30 Days Performance</option>
                                <option value="year_to_date">📊 Year-to-Date (YTD 2026)</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-lg-4 text-md-end">
                            <button type="button" class="btn btn-primary btn-lg w-100 w-md-auto px-4 py-3 fw-bold rounded-3 shadow-sm d-inline-flex align-items-center justify-content-center gap-2" id="generateAiReportBtn" onclick="generateAiReport()">
                                <i class="fas fa-wand-magic-sparkles"></i>
                                <span>Generate AI Report</span>
                            </button>
                        </div>
                    </div>

                    <!-- Loading State Placeholder (Hidden initially) -->
                    <div id="aiReportLoadingState" class="d-none text-center py-5">
                        <div class="spinner-border text-indigo mb-3" role="status" style="width: 3.5rem; height: 3.5rem; color: #6366f1;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">Synthesizing Neural AI Financial Model...</h5>
                        <p class="text-muted small mb-0">Analyzing revenue streams, expense allocations, branch utilization rates, and profit vectors...</p>
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
                            <button class="btn btn-sm btn-outline-light rounded-pill px-3" onclick="clearAiChatHistory('')">
                                <i class="fas fa-trash-alt me-1"></i> Clear Chat
                            </button>
                        </div>
                    </div>

                    <!-- Chat Messages Container -->
                    <div class="card-body p-4" style="background: #f8fafc; min-height: 380px; max-height: 480px; overflow-y: auto;" id="aiChatMessagesContainer">
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
                            <button class="btn btn-xs btn-outline-primary rounded-pill bg-white text-dark small py-1 px-3 shadow-none border" onclick="sendSuggestedQuestion('💡 كم إجمالي الإيرادات هذا الشهر؟', '')">💡 كم إجمالي الإيرادات هذا الشهر؟</button>
                            <button class="btn btn-xs btn-outline-primary rounded-pill bg-white text-dark small py-1 px-3 shadow-none border" onclick="sendSuggestedQuestion('🅿️ ما هو معدل الإشغال اليوم؟', '')">🅿️ ما هو معدل الإشغال اليوم؟</button>
                            <button class="btn btn-xs btn-outline-primary rounded-pill bg-white text-dark small py-1 px-3 shadow-none border" onclick="sendSuggestedQuestion('🏆 ما هي الساحات الأعلى أداءً؟', '')">🏆 ما هي الساحات الأعلى أداءً؟</button>
                            <button class="btn btn-xs btn-outline-primary rounded-pill bg-white text-dark small py-1 px-3 shadow-none border" onclick="sendSuggestedQuestion('🎯 ما هي الساحة التي تحتاج إلى تحسين؟', '')">🎯 ما هي الساحة التي تحتاج إلى تحسين؟</button>
                            <button class="btn btn-xs btn-outline-primary rounded-pill bg-white text-dark small py-1 px-3 shadow-none border" onclick="sendSuggestedQuestion('📊 اعرض تحليل الأرباح والمصروفات', '')">📊 اعرض تحليل الأرباح والمصروفات</button>
                        </div>
                    </div>

                    <!-- Typing Indicator -->
                    <div id="aiChatTypingIndicator" class="d-none px-4 py-2 bg-white text-muted small border-top">
                        <span class="spinner-grow spinner-grow-sm me-2 text-indigo" role="status" style="color: #6366f1;"></span>
                        <em>SpotLy AI is querying live database analytics...</em>
                    </div>

                    <!-- Chat Input Controls -->
                    <div class="p-3 bg-white border-top">
                        <form id="aiChatForm" onsubmit="handleAiChatSubmit(event, '')" class="d-flex gap-2 align-items-center">
                            <input type="text" id="aiChatInput" class="form-control rounded-pill px-4 py-2 shadow-none border" placeholder="اختر سؤالاً من المقترحات أعلاه..." autocomplete="off">
                            <button type="submit" id="aiChatSendBtn" class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                                <i class="fas fa-paper-plane text-white"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- AI Report Display Component (Hidden until button click) -->
                <div id="aiReportDisplayComponent" class="d-none mt-2">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: #f8fafc; border: 1px solid #e2e8f0 !important;">
                            
                            <!-- Report Top Header Banner -->
                            <div class="p-4 bg-white border-bottom d-flex justify-content-between align-items-center flex-wrap gap-3">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="badge bg-indigo-subtle text-indigo px-3 py-1 rounded-pill fw-bold" style="background: #e0e7ff; color: #4338ca;">
                                            🤖 AI-Generated Report
                                        </span>
                                        <span class="text-muted small">Generated on: <strong id="reportTimestampDisplay">July 27, 2026 - 11:55 AM</strong></span>
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
                                        <p id="aiExecutiveSummaryText" class="text-dark leading-relaxed mb-4 fs-6">
                                            Loading live database analytics...
                                        </p>
                                        <!-- Key Metrics Highlights Row -->
                                        <div class="row g-3 text-center">
                                            <div class="col-6 col-md-3">
                                                <div class="p-3 rounded-4 bg-light border">
                                                    <span class="text-muted small d-block mb-1">Total Net Revenue</span>
                                                    <h4 id="aiTotalNetRevenueVal" class="fw-bold text-success mb-0">0.00 <small class="fs-6">LYD</small></h4>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="p-3 rounded-4 bg-light border">
                                                    <span class="text-muted small d-block mb-1">Gross Profit Margin</span>
                                                    <h4 id="aiGrossMarginVal" class="fw-bold text-primary mb-0">0.0%</h4>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="p-3 rounded-4 bg-light border">
                                                    <span class="text-muted small d-block mb-1">Avg Occupancy Index</span>
                                                    <h4 id="aiOccupancyRateVal" class="fw-bold text-warning text-dark mb-0">0.0%</h4>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="p-3 rounded-4 bg-light border">
                                                    <span class="text-muted small d-block mb-1">Health Score</span>
                                                    <h4 id="aiHealthScoreVal" class="fw-bold text-indigo mb-0" style="color: #6366f1;">0 / 100</h4>
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
                                                            <strong id="aiTotalReservationsVal" class="text-dark">0 Bookings</strong>
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
                                                        <h5 id="aiTotalExpensesVal" class="fw-bold text-danger mb-0">0.00 LYD</h5>
                                                    </div>
                                                    <div class="d-flex align-items-center justify-content-between bg-white p-3 rounded-3 border">
                                                        <span class="fw-bold text-secondary small">Net Profit Retained:</span>
                                                        <h4 id="aiNetProfitVal" class="fw-bold text-success mb-0">0.00 LYD</h4>
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
                                                <h6 id="aiTopBranchName" class="fw-bold text-success mb-1">Loading...</h6>
                                                <span class="text-muted small">Highest gross revenue and capacity utilization</span>
                                            </div>
                                            <div class="row g-2 text-center">
                                                <div class="col-6">
                                                    <div class="p-2 bg-light rounded border">
                                                        <span class="text-muted x-small d-block">Gross Revenue</span>
                                                        <strong id="aiTopBranchRevenue" class="text-primary">0.00 LYD</strong>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="p-2 bg-light rounded border">
                                                        <span class="text-muted x-small d-block">Occupancy Rate</span>
                                                        <strong id="aiTopBranchOccupancy" class="text-success">0%</strong>
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
                                                <h6 id="aiLowestBranchName" class="fw-bold text-dark mb-1">Loading...</h6>
                                                <span class="text-muted small">Candidate for promotional pricing and dynamic campaign boost</span>
                                            </div>
                                            <div class="row g-2 text-center">
                                                <div class="col-6">
                                                    <div class="p-2 bg-light rounded border">
                                                        <span class="text-muted x-small d-block">Gross Revenue</span>
                                                        <strong id="aiLowestBranchRevenue" class="text-secondary">0.00 LYD</strong>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="p-2 bg-light rounded border">
                                                        <span class="text-muted x-small d-block">Occupancy Rate</span>
                                                        <strong id="aiLowestBranchOccupancy" class="text-warning text-dark">0%</strong>
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
                                                <tbody id="aiBranchStatsTableBody">
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
                                    <div id="aiRecommendationsContainer" class="row g-3">
                                        <div class="col-12 text-muted">Generating real business intelligence recommendations...</div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </section>

            <!-- SECTION 5: Reports & Export Section -->
            <section id="reportsTab" class="content-section d-none">
                <div class="card p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="fw-bold text-primary mb-1"><i class="fas fa-file-export me-2"></i> قسم التقارير الماليّة والتصدير الشامل</h5>
                            <p class="text-muted small mb-0">إنشاء وتصدير التقارير المالية وحركة المواقف بفرز زمني بصيغة PDF و Excel مباشرة</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-success rounded-pill fw-bold" onclick="exportReportToExcel()">
                                <i class="fas fa-file-excel me-1"></i> تصدير Excel
                            </button>
                            <button class="btn btn-danger rounded-pill fw-bold" onclick="exportReportToPDF()">
                                <i class="fas fa-file-pdf me-1"></i> تصدير PDF
                            </button>
                        </div>
                    </div>

                    <!-- Report Filter Form -->
                    <form id="reportsFilterForm" class="row g-3 align-items-end mb-4">
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">من تاريخ:</label>
                            <input type="date" class="form-control rounded-3" id="repStartDate">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">إلى تاريخ:</label>
                            <input type="date" class="form-control rounded-3" id="repEndDate">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">تصفية بحسب الفرع:</label>
                            <select class="form-select rounded-3" id="repParkingId">
                                <option value="">كافة الفروع والمواقف</option>
                                @foreach($parkingsList as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-primary w-100 rounded-3 fw-bold" onclick="generateExportableReport()">
                                <i class="fas fa-search me-1"></i> جلب التقرير
                            </button>
                        </div>
                    </form>

                    <!-- Exportable Report Table Area -->
                    <div id="exportableReportArea" class="table-responsive p-3 bg-light rounded-4 border">
                        <table class="table align-middle text-center" id="reportsMainTable">
                            <thead class="table-light">
                                <tr>
                                    <th>الفرع / الموقف</th>
                                    <th>إجمالي الحجوزات</th>
                                    <th>الإيرادات الكلية</th>
                                    <th>المصاريف / المستردات</th>
                                    <th>صافي الأرباح</th>
                                    <th>المدير المسؤول</th>
                                </tr>
                            </thead>
                            <tbody id="reportsTableBody">
                                @foreach($branchPerformance as $b)
                                    <tr>
                                        <td class="fw-bold text-dark-emphasis">{{ $b['name'] }}</td>
                                        <td>{{ $b['bookings_count'] }} حجز</td>
                                        <td class="text-primary fw-bold">{{ number_format($b['gross_revenue'], 2) }} د.ل</td>
                                        <td class="text-warning fw-bold">{{ number_format($b['refunds'], 2) }} د.ل</td>
                                        <td class="text-success fw-bold">{{ number_format($b['net_revenue'], 2) }} د.ل</td>
                                        <td><span class="badge bg-info text-dark">{{ $b['manager_name'] }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- SECTION 6: Map Tab -->
            <section id="addParkingTab" class="content-section d-none">
                <div class="card p-4 mb-4">
                    <h5 class="mb-4 fw-bold text-primary"><i class="fas fa-map-marked-alt me-2"></i> خريطة موقع فروع ومواقف النظام الجغرافية</h5>
                    <div id="map" class="shadow-sm"></div>
                </div>
            </section>

            <!-- SECTION 7: Employees Tab -->
            <section id="employeesTab" class="content-section d-none">
                <div class="card p-4 mb-4">
                    <h5 class="mb-4 fw-bold text-primary"><i class="fas fa-users me-2"></i> قائمة موظفي الميدان المسجلين</h5>
                    <div class="table-responsive">
                        <table class="table align-middle text-center mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>الاسم</th>
                                    <th>البريد الإلكتروني</th>
                                    <th>رقم الهاتف</th>
                                    <th>رقم الحساب المصرفي (IBAN)</th>
                                    <th>الموقف المرتبط</th>
                                    <th>تاريخ التسجيل</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employeesList as $employee)
                                    <tr>
                                        <td class="fw-bold text-dark-emphasis">{{ $employee->name }}</td>
                                        <td>{{ $employee->email }}</td>
                                        <td>{{ $employee->phone }}</td>
                                        <td><code class="text-dark bg-light p-2 rounded-3" style="direction: ltr; display: inline-block;">{{ $employee->bank_account_number }}</code></td>
                                        <td>
                                            @if($employee->parking_name)
                                                <span class="badge bg-info text-dark fw-bold"><i class="fas fa-parking"></i> {{ $employee->parking_name }}</span>
                                            @else
                                                <span class="badge bg-light text-muted">غير معين</span>
                                            @endif
                                        </td>
                                        <td>{{ $employee->created_at ? \Carbon\Carbon::parse($employee->created_at)->format('Y-m-d H:i') : '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-muted py-4">لا يوجد موظفي ميدان مسجلين حالياً.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- MODAL 1: Create Parking -->
    <div class="modal fade" id="createParkingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 py-3">
                    <h5 class="modal-title fw-bold text-primary"><i class="fas fa-plus-circle me-1"></i> إضافة فرع / موقف سيارات جديد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="createParkingForm" onsubmit="submitCreateParking(event)">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">اسم الموقف / الفرع:</label>
                            <input type="text" class="form-control rounded-3" id="createParkingName" required placeholder="مثال: موقف الميدان الرئيسي">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">الموقع / العنوان:</label>
                            <input type="text" class="form-control rounded-3" id="createParkingLocation" required placeholder="مثال: وسط المدينة - الشارع الرئيسي">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">السعة الاستيعابية الكلية:</label>
                            <input type="number" class="form-control rounded-3" id="createParkingCapacity" min="1" required placeholder="100">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">خط العرض (Latitude):</label>
                                <input type="number" step="any" class="form-control rounded-3" id="createParkingLat" placeholder="32.8872">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">خط الطول (Longitude):</label>
                                <input type="number" step="any" class="form-control rounded-3" id="createParkingLng" placeholder="13.1913">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4">حفظ الفُـرع 💾</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL 2: Edit Parking -->
    <div class="modal fade" id="editParkingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 py-3">
                    <h5 class="modal-title fw-bold text-primary"><i class="fas fa-edit me-1"></i> تعديل بيانات الموقف والسعة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editParkingForm" onsubmit="submitEditParking(event)">
                    <input type="hidden" id="editParkingId">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">اسم الموقف:</label>
                            <input type="text" class="form-control rounded-3" id="editParkingName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">الموقع / العنوان:</label>
                            <input type="text" class="form-control rounded-3" id="editParkingLocation" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">السعة الكلية المعدلة:</label>
                            <input type="number" class="form-control rounded-3" id="editParkingCapacity" min="1" required>
                            <div class="form-text text-warning fw-bold mt-1" id="editCapacityNotice"></div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">تحديث بيانات الفرع 💾</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL 3: Create Manager -->
    <div class="modal fade" id="createManagerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 py-3">
                    <h5 class="modal-title fw-bold text-primary"><i class="fas fa-user-plus me-1"></i> إضافة مدير فرع جديد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="createManagerForm" onsubmit="submitCreateManager(event)">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">الاسم الكامل:</label>
                            <input type="text" class="form-control rounded-3" id="createManagerName" required placeholder="مثال: أحمد علي">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">البريد الإلكتروني:</label>
                            <input type="email" class="form-control rounded-3" id="createManagerEmail" required placeholder="manager@spotly.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">رقم الهاتف:</label>
                            <input type="text" class="form-control rounded-3" id="createManagerPhone" required placeholder="0911234567">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">كلمة المرور:</label>
                            <input type="password" class="form-control rounded-3" id="createManagerPassword" minlength="6" required placeholder="******">
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">حفظ المدير 💾</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL 4: Manage Manager Parkings Assignment -->
    <div class="modal fade" id="manageParkingsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 py-3">
                    <h5 class="modal-title fw-bold text-primary"><i class="fas fa-link me-1"></i> تعيين / تعديل الفروع المسؤولة عنها</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted mb-3">حدد الفروع والمواقف التي ترغب في ربط حقل <code class="text-primary">manager_id</code> بها للمدير: <strong class="text-dark fs-6" id="modalManagerName">...</strong></p>
                    <div id="modalLoadingSpinner" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                    <form id="manageParkingsForm" class="d-none">
                        <input type="hidden" id="modalManagerAccountId">
                        <div id="parkingsListContainer" style="max-height: 280px; overflow-y: auto; padding-right: 5px;">
                            <!-- Loaded via JS -->
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" onclick="saveManagerParkings()" class="btn btn-primary rounded-pill px-4">تحديث حقل manager_id 💾</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <script>
    let map;
    let developerId = null;
    const existingParkings = @json($parkingsList);
    const monthlyTrends = @json($monthlyFinancialTrends);
    const dailyTrends = @json($dailyFinancialTrends);
    const branchPerformance = @json($branchPerformance);

    const orangeIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-orange.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });

    let overviewFinancialChart, occupancyDoughnutChart, detailedAnalyticsChart, branchComparisonBarChart;

    document.addEventListener('DOMContentLoaded', () => {
        try {
            const storedData = JSON.parse(localStorage.getItem('userData') || localStorage.getItem('developerData'));

            if (!storedData || (storedData.role !== 'developer' && storedData.role !== 'manager' && storedData.role !== 'admin')) {
                Swal.fire({
                    icon: 'error',
                    title: 'غير مصرح',
                    text: 'هذه البوابة مخصصة للمطورين والمدراء فقط.',
                    allowOutsideClick: false
                }).then(() => { window.location.href = '/login'; });
                return;
            }

            developerId = storedData.accountId || storedData.id;
            document.getElementById('developerNameDisplay').innerText = storedData.name;

            initCharts();
        } catch(e) {
            window.location.href = '/login';
        }
    });

    function toggleSidebarMenu() {
        document.getElementById('dashboardSidebar').classList.toggle('active');
    }

    function switchTab(tabId, element, title) {
        document.querySelectorAll('.content-section').forEach(sec => sec.classList.add('d-none'));
        document.querySelectorAll('.sidebar .nav-link').forEach(link => link.classList.remove('active'));
        
        document.getElementById(tabId).classList.remove('d-none');
        element.classList.add('active');
        document.getElementById('headerTitle').innerText = title;

        if (window.innerWidth <= 991) {
            document.getElementById('dashboardSidebar').classList.remove('active');
        }

        if (tabId === 'addParkingTab') {
            if (!map) initMap(); 
            else setTimeout(() => { map.invalidateSize(); }, 300); 
        }
    }

    function initCharts() {
        Chart.defaults.font.family = 'Inter';

        // Occupancy Doughnut Chart
        const ctxDoughnut = document.getElementById('occupancyDoughnutChart').getContext('2d');
        occupancyDoughnutChart = new Chart(ctxDoughnut, {
            type: 'doughnut',
            data: {
                labels: ['الأماكن المشغولة', 'الأماكن المتاحة'],
                datasets: [{
                    data: [{{ $occupiedSpots }}, {{ $availableCapacity }}],
                    backgroundColor: ['#af52de', '#34c759'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '70%' }
        });
    }

    function initMap() {
        map = L.map('map').setView([32.8872, 13.1913], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);

        existingParkings.forEach(parking => {
            if (parking.latitude && parking.longitude) {
                L.marker([parking.latitude, parking.longitude], {icon: orangeIcon})
                    .addTo(map)
                    .bindPopup(`
                        <div style="direction: rtl; text-align: right; font-family: sans-serif;">
                            <h6 class="fw-bold mb-1 text-primary"><i class="fas fa-parking me-1"></i> ${parking.name}</h6>
                            <p class="mb-1 text-dark small"><b>السعة الإجمالية:</b> ${parking.total_capacity} مركبة</p>
                            <p class="mb-0 text-success small"><b>الأماكن المتاحة:</b> ${parking.available_capacity} مركبة</p>
                        </div>
                    `);
            }
        });
    }

    // CRUD & API Functions
    async function submitCreateParking(e) {
        e.preventDefault();
        const payload = {
            name: document.getElementById('createParkingName').value,
            location_park: document.getElementById('createParkingLocation').value,
            total_capacity: parseInt(document.getElementById('createParkingCapacity').value),
            latitude: parseFloat(document.getElementById('createParkingLat').value) || 32.8872,
            longitude: parseFloat(document.getElementById('createParkingLng').value) || 13.1913
        };

        try {
            const response = await fetch('/developer/parkings/store', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            if (result.status === 'success') {
                Swal.fire('تم بنجاح', 'تمت إضافة موقف السيارات بنجاح', 'success').then(() => location.reload());
            } else {
                Swal.fire('خطأ', result.message || 'فشل حفظ الموقف', 'error');
            }
        } catch (err) { Swal.fire('خطأ', 'فشلت العملية', 'error'); }
    }

    function openEditParkingModal(id, name, location, totalCap, availableCap) {
        const occupied = totalCap - availableCap;
        document.getElementById('editParkingId').value = id;
        document.getElementById('editParkingName').value = name;
        document.getElementById('editParkingLocation').value = location;
        document.getElementById('editParkingCapacity').value = totalCap;
        document.getElementById('editCapacityNotice').innerText = `عدد المشغول حالياً: ${occupied} (السعة المعدلة يجب ألا تقل عن ${occupied}).`;
        
        const modal = new bootstrap.Modal(document.getElementById('editParkingModal'));
        modal.show();
    }

    async function submitEditParking(e) {
        e.preventDefault();
        const id = document.getElementById('editParkingId').value;
        const payload = {
            name: document.getElementById('editParkingName').value,
            location_park: document.getElementById('editParkingLocation').value,
            total_capacity: parseInt(document.getElementById('editParkingCapacity').value)
        };

        try {
            const response = await fetch(`/api/v1/super-admin/parkings/${id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();

            if (response.ok && result.status === 'success') {
                Swal.fire('تم التحديث', 'تم تعديل بيانات الساحة بنجاح', 'success').then(() => location.reload());
            } else {
                Swal.fire('تنبيه الأمان', result.message || 'فشل تحديث السعة', 'warning');
            }
        } catch (err) { Swal.fire('خطأ', 'حدث خطأ أثناء التعديل', 'error'); }
    }

    function viewParkingDetails(id, name, location, totalCap, availCap, managerName, empName) {
        const occupied = totalCap - availCap;
        Swal.fire({
            title: `تفاصيل موقف: ${name}`,
            html: `
                <div class="text-end font-sans">
                    <p><b>العنوان/الموقع:</b> ${location}</p>
                    <p><b>السعة الإجمالية:</b> <span class="badge bg-secondary">${totalCap}</span></p>
                    <p><b>الأماكن المتاحة:</b> <span class="badge bg-success">${availCap}</span></p>
                    <p><b>الأماكن المشغولة:</b> <span class="badge bg-warning text-dark">${occupied}</span></p>
                    <p><b>المدير المسؤول:</b> <span class="badge bg-info text-dark">${managerName}</span></p>
                    <p><b>موظف البوابات:</b> <span class="badge bg-light text-dark">${empName}</span></p>
                </div>
            `,
            icon: 'info',
            confirmButtonText: 'حسناً'
        });
    }

    async function deleteParkingLot(id) {
        const confirm = await Swal.fire({
            title: 'تأكيد الحذف',
            text: 'هل أنت متأكد من حذف موقف السيارات هذا؟',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم، احذف',
            cancelButtonText: 'إلغاء'
        });

        if (confirm.isConfirmed) {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const response = await fetch(`/developer/parkings/${id}`, {
                    method: 'DELETE',
                    headers: { 
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                const result = await response.json();
                if (response.ok && result.status === 'success') {
                    Swal.fire('تم الحذف', 'تم حذف الساحة بنجاح', 'success').then(() => location.reload());
                } else {
                    Swal.fire('تعذر الحذف', result.message || 'لا يمكن حذف الموقف لوجود حجوزات جارية', 'error');
                }
            } catch (err) { Swal.fire('خطأ', 'حدث خطأ أثناء الحذف', 'error'); }
        }
    }

    async function submitCreateManager(e) {
        e.preventDefault();
        const payload = {
            name: document.getElementById('createManagerName').value,
            email: document.getElementById('createManagerEmail').value,
            phone: document.getElementById('createManagerPhone').value,
            password: document.getElementById('createManagerPassword').value
        };

        try {
            const response = await fetch('/developer/managers/store', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            if (result.status === 'success') {
                Swal.fire('تم الإنشاء', 'تم إنشاء حساب المدير بنجاح', 'success').then(() => location.reload());
            } else {
                Swal.fire('خطأ', result.message || 'فشل إنشاء حساب المدير', 'error');
            }
        } catch (err) { Swal.fire('خطأ', 'حدث خطأ أثناء الاتصال', 'error'); }
    }

    function viewManagerDetails(name, email, phone, status) {
        Swal.fire({
            title: `بيانات المدير: ${name}`,
            html: `
                <div class="text-end font-sans">
                    <p><b>البريد الإلكتروني:</b> ${email}</p>
                    <p><b>رقم الهاتف:</b> ${phone}</p>
                    <p><b>حالة الحساب:</b> <span class="badge ${status === 'active' ? 'bg-success' : 'bg-danger'}">${status === 'active' ? 'نشط' : 'معطل'}</span></p>
                </div>
            `,
            icon: 'info',
            confirmButtonText: 'إغلاق'
        });
    }

    async function openAssignParkingsModal(accountId, managerName) {
        document.getElementById('modalManagerName').innerText = managerName;
        document.getElementById('modalManagerAccountId').value = accountId;
        
        const modal = new bootstrap.Modal(document.getElementById('manageParkingsModal'));
        modal.show();

        document.getElementById('modalLoadingSpinner').classList.remove('d-none');
        document.getElementById('manageParkingsForm').classList.add('d-none');

        try {
            const response = await fetch(`/developer/managers/${accountId}/parkings`, { headers: { 'Accept': 'application/json' } });
            const result = await response.json();

            if (result.status === 'success') {
                const container = document.getElementById('parkingsListContainer');
                container.innerHTML = '';

                if (result.parkings.length === 0) {
                    container.innerHTML = '<p class="text-muted text-center py-3">لا توجد مواقف متاحة للربط.</p>';
                } else {
                    result.parkings.forEach(p => {
                        const isChecked = (p.manager_id == result.manager_id) ? 'checked' : '';
                        container.innerHTML += `
                            <div class="form-check p-3 rounded-3 mb-2 border bg-light">
                                <input class="form-check-input ms-2" type="checkbox" name="parking_ids[]" value="${p.id}" id="chk_p_${p.id}" ${isChecked}>
                                <label class="form-check-label fw-bold text-dark" for="chk_p_${p.id}">
                                    ${p.name} <small class="text-muted">(${p.location_park})</small>
                                </label>
                            </div>
                        `;
                    });
                }
                document.getElementById('modalLoadingSpinner').classList.add('d-none');
                document.getElementById('manageParkingsForm').classList.remove('d-none');
            }
        } catch (e) { Swal.fire('خطأ', 'فشل جلب بيانات تعيين الساحات', 'error'); }
    }

    async function saveManagerParkings() {
        const accountId = document.getElementById('modalManagerAccountId').value;
        const selected = Array.from(document.querySelectorAll('input[name="parking_ids[]"]:checked')).map(c => parseInt(c.value));

        try {
            const response = await fetch(`/developer/managers/${accountId}/parkings`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ parking_ids: selected })
            });
            const result = await response.json();
            if (result.status === 'success') {
                Swal.fire('تم الربط', 'تم تحديث حقل manager_id بنجاح', 'success').then(() => location.reload());
            } else { Swal.fire('خطأ', result.message || 'فشل الحفظ', 'error'); }
        } catch (e) { Swal.fire('خطأ', 'حدث خطأ أثناء الحفظ', 'error'); }
    }

    async function toggleManagerActivation(accountId) {
        try {
            const response = await fetch(`/developer/managers/${accountId}/toggle-status`, {
                method: 'POST',
                headers: { 'Accept': 'application/json' }
            });
            const result = await response.json();
            if (result.status === 'success') {
                Swal.fire('تم التغيير', result.message, 'success').then(() => location.reload());
            } else { Swal.fire('خطأ', result.message || 'فشل تغيير الحالة', 'error'); }
        } catch (e) { Swal.fire('خطأ', 'حدث خطأ في الشبكة', 'error'); }
    }

    async function deleteManagerAccount(managerId) {
        const confirm = await Swal.fire({
            title: 'حذف حساب المدير',
            text: 'هل أنت متأكد من حذف حساب هذا المدير من النظام؟',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم، احذف',
            cancelButtonText: 'إلغاء'
        });

        if (confirm.isConfirmed) {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const response = await fetch(`/developer/managers/${managerId}`, {
                    method: 'DELETE',
                    headers: { 
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                const result = await response.json();
                if (response.ok && result.status === 'success') {
                    Swal.fire('تم الحذف', 'تم حذف المدير بنجاح', 'success').then(() => location.reload());
                } else { Swal.fire('خطأ', result.message || 'فشل حذف المدير', 'error'); }
            } catch (err) { Swal.fire('خطأ', 'حدث خطأ أثناء الحذف', 'error'); }
        }
    }

    // Reports Export Functions (Excel & PDF)
    async function generateExportableReport() {
        const startDate = document.getElementById('repStartDate').value;
        const endDate = document.getElementById('repEndDate').value;
        const parkingId = document.getElementById('repParkingId').value;

        let url = `/api/v1/super-admin/dashboard/financial-reports?`;
        if (startDate) url += `start_date=${startDate}&`;
        if (endDate) url += `end_date=${endDate}&`;
        if (parkingId) url += `parking_id=${parkingId}&`;

        try {
            Swal.showLoading();
            const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const result = await response.json();
            Swal.close();

            if (result.status === 'success') {
                const tbody = document.getElementById('reportsTableBody');
                tbody.innerHTML = '';

                if (result.data.revenue_by_parking.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="6" class="text-muted py-4">لا توجد بيانات مطابقة لتحديد التقرير.</td></tr>`;
                } else {
                    result.data.revenue_by_parking.forEach(row => {
                        tbody.innerHTML += `
                            <tr>
                                <td class="fw-bold text-dark-emphasis">${row.parking_name}</td>
                                <td>${row.bookings_count} حجز</td>
                                <td class="text-primary fw-bold">${row.gross_revenue.toFixed(2)} د.ل</td>
                                <td class="text-warning fw-bold">${row.refunds.toFixed(2)} د.ل</td>
                                <td class="text-success fw-bold">${row.net_revenue.toFixed(2)} د.ل</td>
                                <td><span class="badge bg-info text-dark">مباشر</span></td>
                            </tr>
                        `;
                    });
                }
            }
        } catch(e) { Swal.fire('خطأ', 'تعذر جلب بيانات التقرير', 'error'); }
    }

    function exportReportToExcel() {
        const table = document.getElementById('reportsMainTable');
        const wb = XLSX.utils.table_to_book(table, { sheet: "التقارير المالية" });
        XLSX.writeFile(wb, `spotly_financial_report_${new Date().toISOString().slice(0,10)}.xlsx`);
    }

    function exportReportToPDF() {
        const element = document.getElementById('exportableReportArea');
        const opt = {
            margin:       0.5,
            filename:     `spotly_financial_report_${new Date().toISOString().slice(0,10)}.pdf`,
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2 },
            jsPDF:        { unit: 'in', format: 'letter', orientation: 'landscape' }
        };
        html2pdf().set(opt).from(element).save();
    }

    async function generateAiReport() {
        const btn = document.getElementById('generateAiReportBtn');
        const loadingState = document.getElementById('aiReportLoadingState');
        const reportComponent = document.getElementById('aiReportDisplayComponent');
        const scope = document.getElementById('aiReportScopeSelect')?.value || 'all';
        const timeframe = document.getElementById('aiReportTimeframeSelect')?.value || 'current_quarter';

        if (!btn || !loadingState || !reportComponent) return;

        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status"></span> Querying Database & Synthesizing AI Report...`;
        loadingState.classList.remove('d-none');
        reportComponent.classList.add('d-none');

        try {
            const response = await fetch(`/developer/ai-financial-report?scope=${scope}&timeframe=${timeframe}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();

            loadingState.classList.add('d-none');

            if (data.status === 'success') {
                renderAiReportData(data, '');
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
        const timeElem = document.getElementById(prefix + 'reportTimestampDisplay');
        if (timeElem) timeElem.innerText = data.generated_at || new Date().toLocaleString();

        // 1. Executive Summary
        const execText = document.getElementById(prefix + 'aiExecutiveSummaryText');
        if (execText) execText.innerText = data.executive_summary;

        const netRevElem = document.getElementById(prefix + 'aiTotalNetRevenueVal');
        if (netRevElem) netRevElem.innerHTML = `${Number(data.metrics.total_revenue).toLocaleString('en-US', {minimumFractionDigits: 2})} <small class="fs-6">LYD</small>`;

        const marginElem = document.getElementById(prefix + 'aiGrossMarginVal');
        if (marginElem) marginElem.innerText = `${data.metrics.gross_margin}%`;

        const occElem = document.getElementById(prefix + 'aiOccupancyRateVal');
        if (occElem) occElem.innerText = `${data.metrics.occupancy_rate}%`;

        const healthElem = document.getElementById(prefix + 'aiHealthScoreVal');
        if (healthElem) healthElem.innerText = `${data.metrics.health_score} / 100`;

        // 2. Revenue Performance
        const reservationsElem = document.getElementById(prefix + 'aiTotalReservationsVal');
        if (reservationsElem) reservationsElem.innerText = `${data.metrics.total_reservations} Bookings`;

        // 3. Expense Analysis
        const expElem = document.getElementById(prefix + 'aiTotalExpensesVal');
        if (expElem) expElem.innerHTML = `${Number(data.metrics.total_expenses).toLocaleString('en-US', {minimumFractionDigits: 2})} LYD`;

        const profitElem = document.getElementById(prefix + 'aiNetProfitVal');
        if (profitElem) profitElem.innerHTML = `${Number(data.metrics.net_profit).toLocaleString('en-US', {minimumFractionDigits: 2})} LYD`;

        // 4. Top Performing Branch
        const topName = document.getElementById(prefix + 'aiTopBranchName');
        if (topName) topName.innerText = data.top_branch.name;

        const topRev = document.getElementById(prefix + 'aiTopBranchRevenue');
        if (topRev) topRev.innerText = `${Number(data.top_branch.gross_revenue).toLocaleString('en-US', {minimumFractionDigits: 2})} LYD`;

        const topOcc = document.getElementById(prefix + 'aiTopBranchOccupancy');
        if (topOcc) topOcc.innerText = `${data.top_branch.occupancy_rate}%`;

        // 5. Lowest Performing Branch
        const lowName = document.getElementById(prefix + 'aiLowestBranchName');
        if (lowName) lowName.innerText = data.lowest_branch.name;

        const lowRev = document.getElementById(prefix + 'aiLowestBranchRevenue');
        if (lowRev) lowRev.innerText = `${Number(data.lowest_branch.gross_revenue).toLocaleString('en-US', {minimumFractionDigits: 2})} LYD`;

        const lowOcc = document.getElementById(prefix + 'aiLowestBranchOccupancy');
        if (lowOcc) lowOcc.innerText = `${data.lowest_branch.occupancy_rate}%`;

        // 6. Branch Statistics Table
        const tbody = document.getElementById(prefix + 'aiBranchStatsTableBody');
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
        const recsContainer = document.getElementById(prefix + 'aiRecommendationsContainer');
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

    let aiChatHistory = [];

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
                    history: aiChatHistory
                })
            });

            const data = await response.json();

            if (indicator) indicator.classList.add('d-none');

            if (data.status === 'success') {
                appendAiChatMessage(container, data.reply, data.source, data.timestamp);
                aiChatHistory.push({ user: userMessage, assistant: data.reply });
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
        aiChatHistory = [];
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

    function logoutDeveloper() {
        Swal.fire({
            title: 'تسجيل الخروج',
            text: 'هل أنت متأكد أنك تريد المغادرة؟',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0071e3',
            cancelButtonColor: '#8e8e93',
            confirmButtonText: 'نعم، خروج',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                localStorage.removeItem('userData');
                localStorage.removeItem('developerData');
                window.location.href = '/login';
            }
        });
    }
</script>
</body>
</html>