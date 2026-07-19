<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spotly - لوحة تحكم المطور</title>
    
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

        .card-header { 
            font-weight: 700; 
            background: transparent; 
            border-bottom: 1px solid var(--glass-border); 
            padding: 20px 25px;
            color: var(--text-color);
        }

        /* stat card styling */
        .stat-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: var(--card-radius);
            box-shadow: 0 10px 30px var(--glass-shadow);
            transition: transform 0.2s;
            border-top: 4px solid;
        }
        .stat-card:hover { transform: translateY(-3px); }
        .border-t-yellow { border-top-color: #ffcc00; }
        .border-t-green { border-top-color: #34c759; }
        .border-t-blue { border-top-color: #0071e3; }

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

        /* Responsive menu */
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
    <div class="sidebar" id="dashboardSidebar">
        <div class="sidebar-brand">
            <h2 class="brand-title">Spotly 🚗</h2>
            <span class="badge bg-warning text-dark px-3 py-1 mt-1 fs-6 fw-bold">بوابة المطور</span>
        </div>
        <div class="nav flex-column mt-3 flex-grow-1">
            <a class="nav-link active" onclick="switchTab('overviewTab', this, 'نظرة عامة 🏠')">
                <i class="fas fa-home me-2"></i> نظرة عامة
            </a>
            <a class="nav-link" onclick="switchTab('addParkingTab', this, 'خريطة الساحات 🗺️')">
                <i class="fas fa-map-marked-alt me-2"></i> عرض خريطة الساحات
            </a>
            <a class="nav-link" onclick="switchTab('employeesTab', this, 'موظفو الميدان 👥')">
                <i class="fas fa-users me-2"></i> عرض موظفي الميدان
            </a>
            <a class="nav-link" onclick="switchTab('addManagerTab', this, 'المدراء المسجلون 👥')">
                <i class="fas fa-user-shield me-2"></i> عرض حسابات المدراء
            </a>
        </div>
    </div>

    <div class="main-content">
        <header class="top-header">
            <div class="d-flex align-items-center gap-3">
                <i class="fas fa-bars menu-toggle" onclick="toggleSidebarMenu()"></i>
                <h5 class="page-title mb-0 fw-bold" id="headerTitle">نظرة عامة 🏠</h5>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="fw-bold text-dark-emphasis">مرحباً، <span id="developerNameDisplay">مدير النظام</span></span>
                <button class="btn btn-outline-danger btn-sm px-4 fw-bold rounded-pill" onclick="logoutDeveloper()">تسجيل الخروج</button>
            </div>
        </header>

        <div class="p-4">
            <div class="alert alert-info border-0 shadow-sm rounded-4 p-4 mb-4">
                📌 <strong>مرحباً بك في لوحة تحكم المطور:</strong> تتيح لك هذه اللوحة إدارة البنية التحتية للنظام، تحديد المواقف جغرافياً على الخريطة، وإدارة حسابات موظفي الميدان وصلاحياتهم.
            </div>

            <!-- SECTION 1: Overview -->
            <section id="overviewTab" class="content-section">
                <div class="row text-center g-4">
                    <div class="col-md-4">
                        <div class="stat-card border-t-yellow p-4">
                            <h5 class="fw-bold mb-2">إجمالي المواقف المُسجلة</h5>
                            <h2 class="text-secondary mb-0 fw-bold">{{ $parkingsCount }} ساحات</h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card border-t-green p-4">
                            <h5 class="fw-bold mb-2">موظفي الميدان</h5>
                            <h2 class="text-secondary mb-0 fw-bold">{{ $employeesCount }} موظف</h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card border-t-blue p-4">
                            <h5 class="fw-bold mb-2">حالة النظام</h5>
                            <h2 class="text-success mb-0 fw-bold">مستقر 🟢</h2>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION 2: Map -->
            <section id="addParkingTab" class="content-section d-none">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h5 class="mb-4 fw-bold text-primary"><i class="fas fa-map-marked-alt"></i> خريطة ساحات ومواقف النظام الكلية</h5>
                        <div class="row">
                            <div class="col-md-12">
                                <div id="map" class="shadow-sm"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION 3: Employees -->
            <section id="employeesTab" class="content-section d-none">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h5 class="mb-4 fw-bold text-primary">
                            <i class="fas fa-users me-1"></i> قائمة موظفي الميدان المسجلين
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle text-center mb-0">
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
                                                    <span class="badge bg-light text-muted"><i class="fas fa-times-circle"></i> غير معين</span>
                                                @endif
                                            </td>
                                            <td>{{ $employee->created_at ? \Carbon\Carbon::parse($employee->created_at)->format('Y-m-d H:i') : '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-5">لا يوجد موظفي ميدان مسجلين حالياً.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION 4: Managers list -->
            <section id="addManagerTab" class="content-section d-none">
                <div class="row g-4">
                    <div class="col-md-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-4">
                                <h5 class="mb-4 fw-bold text-primary">
                                    <i class="fas fa-users-cog me-1"></i> المدراء الحاليون في النظام (عرض فقط)
                                </h5>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle text-center mb-0" id="managersTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th>الاسم</th>
                                                <th>البريد الإلكتروني</th>
                                                <th>رقم الهاتف</th>
                                                <th>الحالة</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($managers as $manager)
                                                <tr id="manager-row-{{ $manager->id }}">
                                                    <td class="fw-bold text-dark-emphasis">{{ $manager->name }}</td>
                                                    <td>{{ $manager->email }}</td>
                                                    <td>{{ $manager->phone }}</td>
                                                    <td>
                                                        @if($manager->status === 'active')
                                                            <span class="badge bg-success status-badge">نشط</span>
                                                        @else
                                                            <span class="badge bg-danger status-badge">معطل</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted py-5">لا يوجد مدراء مسجلين حالياً.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Modal ربط الساحات -->
    <div class="modal fade" id="manageParkingsModal" tabindex="-1" aria-labelledby="manageParkingsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; background: var(--glass-bg); backdrop-filter: blur(25px); border: 1px solid var(--glass-border);">
                <div class="modal-header border-0 py-3 rounded-top-4">
                    <h5 class="modal-title fw-bold text-dark-emphasis" id="manageParkingsModalLabel">
                        <i class="fas fa-parking me-1"></i> إدارة ربط الساحات
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-secondary mb-3">
                        حدد الساحات التي ترغب في ربطها بالمدير: <strong class="text-dark" id="modalManagerName">...</strong>
                    </p>
                    <div id="modalLoadingSpinner" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">جاري التحميل...</span>
                        </div>
                    </div>
                    <form id="manageParkingsForm" class="d-none">
                        <input type="hidden" id="modalManagerAccountId">
                        <div id="parkingsListContainer" style="max-height: 300px; overflow-y: auto; padding-right: 5px;">
                            <!-- Loaded via JS -->
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" onclick="saveManagerParkings()" class="btn btn-primary rounded-pill px-4" id="saveParkingsBtn">حفظ التغييرات 💾</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
    let map;
    let marker;
    let developerId = null;
    const existingParkings = @json($parkingsList);

    const orangeIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-orange.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
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

    document.addEventListener('DOMContentLoaded', () => {
        try {
            const storedData = JSON.parse(localStorage.getItem('userData') || localStorage.getItem('developerData'));

            if (!storedData || (storedData.role !== 'developer' && storedData.role !== 'manager')) {
                Swal.fire({
                    icon: 'error',
                    title: 'غير مصرح',
                    text: 'هذه البوابة مخصصة للمطورين والمدراء فقط.',
                    allowOutsideClick: false
                }).then(() => {
                    window.location.href = '/login'; 
                });
                return;
            }

            developerId = storedData.accountId || storedData.id;
            document.getElementById('developerNameDisplay').innerText = storedData.name;

        } catch(e) {
            window.location.href = '/login';
        }
    });

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
                            <h6 class="fw-bold mb-1 text-primary"><i class="fas fa-parking me-1"></i> ${parking.name}</h6>
                            <small class="text-muted d-block mb-2">ساحة وقوف فعالة</small>
                            <p class="mb-1 text-dark small"><b>إجمالي السعة:</b> ${parking.total_capacity} مركبة</p>
                            <p class="mb-0 text-success small"><b>الشواغر المتوفرة:</b> ${parking.available_capacity} مركبة</p>
                        </div>
                    `);
            }
        });
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