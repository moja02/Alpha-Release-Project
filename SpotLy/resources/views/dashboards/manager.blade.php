<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpotLy - لوحة تحكم المدير</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { 
            background-color: #f4f6f9; 
            font-family: system-ui, -apple-system, sans-serif; 
            overflow-x: hidden;
        }
        .sidebar {
            height: 100vh;
            background-color: #2c3e50;
            color: white;
            position: fixed;
            right: 0;
            top: 0;
            width: 260px;
            padding-top: 20px;
            box-shadow: -2px 0 5px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        .sidebar .nav-link {
            color: #bdc3c7;
            padding: 12px 20px;
            margin: 4px 10px;
            border-radius: 8px;
            transition: all 0.3s;
            cursor: pointer;
            text-decoration: none;
            display: block;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background-color: #34495e;
            font-weight: bold;
        }
        .main-content {
            margin-right: 260px;
            padding: 25px;
        }
        .dashboard-header {
            background-color: #ffffff;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
            margin-bottom: 25px;
        }
        .card { 
            border-radius: 12px; 
            border: none; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.02), 0 1px 3px rgba(0,0,0,0.08); 
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-2px);
        }
        .card-header { 
            font-weight: bold; 
            background-color: #ffffff; 
            border-bottom: 2px solid #f0f2f5; 
            padding: 15px 20px;
        }
        .stat-card-value {
            font-size: 2.2rem;
            font-weight: 800;
        }
        .table-responsive {
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }
        .table th {
            font-weight: 700;
            background-color: #f8f9fa;
        }
        /* تصميم الجدول الزمني للتدقيق والعمليات */
        .timeline-container {
            position: relative;
            padding-right: 30px;
            border-right: 3px solid #dee2e6;
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
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="text-center mb-4">
            <h4 class="text-white fw-bold">🚗 SpotLy</h4>
            <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold">بوابة المدير</span>
        </div>
        <hr class="border-secondary border-opacity-50 mx-3">
        <nav class="nav flex-column">
            <a class="nav-link active" onclick="switchTab('overviewTab', this)">
                🏠 نظرة عامة
            </a>
            <a class="nav-link" onclick="switchTab('parkingsTab', this)">
                📍 الساحات المدارة
            </a>
            <a class="nav-link" onclick="switchTab('violationsTab', this)">
                🚫 إدارة الحظر والمخالفات
            </a>
            <a class="nav-link" onclick="switchTab('financialReportTab', this)">
                📊 التقرير المالي
            </a>
            <a class="nav-link" onclick="switchTab('profileTab', this)">
                ⚙️ البيانات الشخصية
            </a>
            <a class="nav-link" onclick="switchTab('auditLogTab', this)">
                📜 سجل العمليات والتدقيق المالي
            </a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="dashboard-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-secondary fw-bold" id="pageTitleDisplay">🏠 نظرة عامة</h5>
            <div>
                <span id="managerNameDisplay" class="me-3 fw-bold text-dark">مرحباً، مدير النظام</span>
                <button onclick="logoutManager()" class="btn btn-sm btn-outline-danger px-3 rounded-pill">تسجيل الخروج</button>
            </div>
        </header>

        <!-- تبويب: نظرة عامة -->
        <section id="overviewTab" class="content-section">
            <div class="alert alert-primary border-0 shadow-sm rounded-4 p-4">
                <h5>👋 أهلاً بك مجدداً في لوحة تحكم المدير!</h5>
                <p class="mb-0 text-muted">تتيح لك هذه المنصة الإشراف الكامل على الساحات المسندة إليك، مراقبة مستويات الإشغال والشاغر بشكل لحظي، وتتبع الحجوزات وتقارير الدخول والخروج.</p>
            </div>


            <!-- نبذة سريعة ومعلومات تعريفية -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card p-4">
                        <h5 class="fw-bold mb-3">📌 إرشادات تشغيلية سريعة</h5>
                        <ul>
                            <li class="mb-2">تأكد من تحديث أرقام هواتف الموظفين الميدانيين لتلقي الإشعارات الطارئة.</li>
                            <li class="mb-2">يمكنك مراجعة كافة تفاصيل السعة التشغيلية والشواغر اللحظية عبر تبويب <strong>الساحات المدارة</strong>.</li>

                        </ul>
                    </div>
                </div>
            </div>
        </section>





        <!-- تبويب: الساحات المدارة -->
        <section id="parkingsTab" class="content-section d-none">
            <div class="card">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-dark fw-bold">📍 قائمة ساحات مواقف السيارات المدارة</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive shadow-sm">
                        <table class="table table-hover align-middle mb-0 text-center">
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
                                        <td class="fw-bold text-dark">{{ $parking->parking_name }}</td>
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
                                        <td colspan="7" class="text-muted py-4">لا توجد ساحات مسجلة باسمك حالياً.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- مودال إنشاء حساب موظف -->
        <div class="modal fade" id="createEmployeeModal" tabindex="-1" aria-labelledby="createEmployeeModalLabel" aria-hidden="true" dir="rtl">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 shadow">
                    <div class="modal-header bg-primary text-white border-0 py-3 rounded-top-4">
                        <h5 class="modal-title fw-bold" id="createEmployeeModalLabel">👤 إنشاء حساب موظف ميداني جديد</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <form id="createEmployeeForm">
                            <input type="hidden" id="modalParkingId">
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold text-secondary">الساحة المستهدفة للتعيين</label>
                                <input type="text" class="form-control bg-light text-muted border-0" id="modalParkingName" readonly>
                            </div>

                            <div class="mb-4 bg-light p-3 rounded-3 border-0">
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

                            <!-- قسم: إنشاء حساب موظف جديد -->
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

                            <!-- قسم: اختيار موظف مسجل غير معين -->
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

                            <button type="submit" class="btn btn-primary w-100 fw-bold py-2 mt-4 rounded-3" id="saveEmployeeBtn">
                                حفظ وتعيين الموظف للساحة 🚀
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- تبويب: إدارة الحظر والمخالفات -->
        <section id="violationsTab" class="content-section d-none">
            <div class="card">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-dark fw-bold">🚫 إدارة حسابات السائقين المحظورين</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive shadow-sm">
                        <table class="table table-hover align-middle mb-0 text-center">
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
                                        <td class="fw-bold text-dark">{{ $driver->driver_name }}</td>
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
                                        <td colspan="7" class="text-muted py-4">لا يوجد سائقون محظورون حالياً.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- تبويب: التقرير المالي -->
        <section id="financialReportTab" class="content-section d-none">
            <!-- عنوان التبويب وأزرار تصدير البيانات بتصميم متميز -->
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3 bg-white p-4 rounded-4 shadow-sm border-0">
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

            <!-- كروت الإحصاءات المالية بتصميم جمالي راقٍ وتدرجات لونية ممتازة -->
            <div class="row g-4 mb-4">
                <!-- كرت إجمالي الإيرادات -->
                <div class="col-md-4">
                    <div class="card border-0 text-white shadow-lg p-4" style="background: linear-gradient(135deg, #11998e, #38ef7d); border-radius: 16px;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 fw-bold mb-1">💰 إجمالي إيرادات الساحات</h6>
                                <h2 class="fw-bold mb-0 text-white stat-card-value">{{ number_format($financialData['totalRevenue'], 2) }} <span class="fs-6 fw-normal text-white-50">نقطة</span></h2>
                            </div>
                            <span class="fs-1 opacity-75">📥</span>
                        </div>
                    </div>
                </div>

                <!-- كرت إجمالي التعويضات عند الإلغاء -->
                <div class="col-md-4">
                    <div class="card border-0 text-white shadow-lg p-4" style="background: linear-gradient(135deg, #ff9900, #ff5500); border-radius: 16px;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 fw-bold mb-1">🔄 إجمالي النقاط المسترجعة</h6>
                                <h2 class="fw-bold mb-0 text-white stat-card-value">{{ number_format($financialData['totalRefundedPoints'], 2) }} <span class="fs-6 fw-normal text-white-50">نقطة</span></h2>
                            </div>
                            <span class="fs-1 opacity-75">📤</span>
                        </div>
                    </div>
                </div>

                <!-- كرت نسبة التعويضات الإجمالية -->
                <div class="col-md-4">
                    <div class="card border-0 text-white shadow-lg p-4" style="background: linear-gradient(135deg, #8a2387, #e94057, #f27121); border-radius: 16px;">
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

            <!-- قسم الرسم البياني العمودي لحركة شحن النقاط والإيرادات اليومية في آخر 30 يوماً -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 text-dark fw-bold">📊 أعمدة حركة شحن النقاط والإيرادات اليومية (آخر 30 يوماً)</h5>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 350px; width: 100%;">
                        <canvas id="financialLineChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- جدول المقارنة المالية مع إجمالي أداء النظام بالكامل لتقديم رؤية شاملة للمدير -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 text-dark fw-bold">📊 مقارنة الأداء المالي مع إجمالي النظام</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive shadow-sm rounded-3">
                        <table class="table table-hover align-middle mb-0 text-center">
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

        <!-- تبويب: البيانات الشخصية -->
        <section id="profileTab" class="content-section d-none">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm overflow-hidden">
                        <div class="card-header bg-gradient bg-primary text-white p-4 d-flex align-items-center justify-content-between border-0">
                            <div>
                                <h5 class="fw-bold mb-1">⚙️ إعدادات الحساب والبيانات الشخصية</h5>
                                <p class="fs-6 mb-0 text-white text-opacity-75">إدارة وتحديث بيانات الاتصال وكلمة المرور الخاصة بك</p>
                            </div>
                            <span class="fs-1">🔒</span>
                        </div>

                        <div class="card-body p-4 bg-white">
                            <form id="profileForm">
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-secondary">الاسم الكامل (غير قابل للتعديل)</label>
                                    <div class="input-group input-group-lg shadow-none">
                                        <span class="input-group-text bg-light border-end-0">👤</span>
                                        <input type="text" class="form-control border-start-0 ps-0 bg-light text-muted" id="profileNameDisplay" readonly>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-secondary">رقم الهاتف للتواصل</label>
                                    <div class="input-group input-group-lg shadow-none">
                                        <span class="input-group-text bg-light border-end-0">📞</span>
                                        <input type="text" class="form-control border-start-0 ps-0" id="profilePhoneInput" required placeholder="أدخل رقم هاتفك المحدث">
                                    </div>
                                </div>

                                <hr class="my-4 border-secondary border-opacity-25">

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-secondary">كلمة مرور جديدة</label>
                                    <div class="input-group input-group-lg shadow-none">
                                        <span class="input-group-text bg-light border-end-0">🔑</span>
                                        <input type="password" class="form-control border-start-0 ps-0" id="profilePasswordInput" placeholder="•••••••• (اتركها فارغة إذا لم ترغب بالتغيير)">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm rounded-3 py-3" id="updateProfileBtn">
                                    حفظ التعديلات وتحديث الجلسة 💾
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- تبويب: سجل العمليات والتدقيق المالي -->
        <section id="auditLogTab" class="content-section d-none">
            <!-- كروت الإحصاءات النقدية السريعة -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 text-white shadow-lg p-4" style="background: linear-gradient(135deg, #0f2027, #203a43, #2c5364); border-radius: 16px;">
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
                    <div class="card border-0 text-white shadow-lg p-4" style="background: linear-gradient(135deg, #11998e, #38ef7d); border-radius: 16px;">
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
                    <div class="card border-0 text-white shadow-lg p-4" style="background: linear-gradient(135deg, #f39c12, #d35400); border-radius: 16px;">
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

            <!-- فلاتر البحث والتدقيق -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-3">🔍 أدوات فحص وتصفية سجل العمليات والتدقيق</h5>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-secondary small">تصفية حسب الموظف</label>
                            <select id="filterEmployee" class="form-select border-2 shadow-none" onchange="loadAuditLogs()">
                                <option value="">جميع الموظفين</option>
                                @foreach($managerEmployees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->employee_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-secondary small">نوع العملية</label>
                            <select id="filterOperation" class="form-select border-2 shadow-none" onchange="loadAuditLogs()">
                                <option value="">جميع العمليات</option>
                                <option value="entry">دخول مركبة ⬇️</option>
                                <option value="exit">خروج مركبة ⬆️</option>
                                <option value="recharge">شحن كاش فوري ⚡</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-secondary small">البحث النصي</label>
                            <input type="text" id="searchPlate" class="form-control border-2 shadow-none" placeholder="ابحث برقم اللوحة أو اسم السائق/الموظف..." oninput="loadAuditLogs()">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-secondary w-100 fw-bold py-2 rounded-3 shadow-none" onclick="resetFilters()">❌ إعادة تعيين</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- قسم الجدول الزمني (Timeline) التفاعلي -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-dark fw-bold">📜 سجل الحركات التشغيلية والمالية الفوري</h5>
                </div>
                <div class="card-body p-4">
                    <div class="timeline-container mx-4" id="auditTimeline">
                        <!-- جاري تحميل السجلات برمجياً -->
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
    // إعداد ترويسات طلب Fetch وإضافة توكن الحماية CSRF
    const fetchHeaders = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
    };

    // استخراج بيانات المدير المسجل من التخزين المحلي للمتصفح
    const storedUserData = JSON.parse(localStorage.getItem('userData'));
    const currentManagerAccountId = storedUserData ? (storedUserData.id || storedUserData.account_id) : null;

    // استدعاء البيانات الشخصية وعرض الاسم الكامل للمدير
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

    // التبديل بين التبويبات المختلفة للوحة التحكم
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

    // --- جلب وعرض سجل العمليات والتدقيق المالي للمدير ---
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
                // تحديث بطاقات إجمالي النقدية
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

    // تعبئة البيانات الشخصية من التخزين المحلي
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

    // معالجة تحديث البيانات الشخصية للمدير
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
                confirmButtonColor: '#d33'
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
                    confirmButtonColor: '#2c3e50'
                });

                // تحديث التخزين المحلي بالبيانات الجديدة
                storedUserData.phone = phoneValue;
                localStorage.setItem('userData', JSON.stringify(storedUserData));

                document.getElementById('profilePasswordInput').value = '';
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'فشل التحديث',
                    text: responseData.message || 'حدث خطأ أثناء حفظ التعديلات.',
                    confirmButtonColor: '#d33'
                });
            }
        } catch (networkError) {
            Swal.fire({
                icon: 'error',
                title: 'خطأ اتصال',
                text: 'تعذر الوصول لخادم الشبكة.',
                confirmButtonColor: '#d33'
            });
        } finally {
            submitBtn.disabled = false;
        }
    });

    // تسجيل الخروج ومسح الجلسة
    function logoutManager() {
        Swal.fire({
            title: 'هل تريد تسجيل الخروج؟',
            text: "سيتم إنهاء الجلسة الحالية والعودة لصفحة تسجيل الدخول.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
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
            
            // Remove required attribute from inputs to allow submission
            document.getElementById('employeeNameInput').removeAttribute('required');
            document.getElementById('employeeEmailInput').removeAttribute('required');
            document.getElementById('employeePhoneInput').removeAttribute('required');
            document.getElementById('existingEmployeeSelect').setAttribute('required', 'required');
        } else {
            createSection.classList.remove('d-none');
            selectSection.classList.add('d-none');
            
            // Add required attribute back
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
        
        // Reset assignment type view to 'create'
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
                    confirmButtonColor: '#3085d6'
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
                    confirmButtonColor: '#2c3e50'
                }).then(() => {
                    location.reload();
                });
                employeeModal.hide();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ',
                    text: result.message || 'فشل في إنشاء الحساب.',
                    confirmButtonColor: '#d33'
                });
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'خطأ اتصال',
                text: 'فشل الاتصال بالخادم.',
                confirmButtonColor: '#d33'
            });
        } finally {
            submitBtn.disabled = false;
        }
    });

    // تأكيد وفك ارتباط الموظف عن الساحة
    function confirmUnlinkEmployee(parkingId, parkingName, employeeName) {
        Swal.fire({
            title: 'تأكيد فك الارتباط',
            text: `هل أنت متأكد من فك ارتباط الموظف (${employeeName}) عن ساحة (${parkingName})؟`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
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
                            confirmButtonColor: '#2c3e50'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ',
                            text: resData.message || 'فشل فك ارتباط الموظف.',
                            confirmButtonColor: '#d33'
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطأ اتصال',
                        text: 'تعذر الاتصال بالخادم.',
                        confirmButtonColor: '#d33'
                    });
                }
            }
        });
    }

    // فك الحظر وتصفير المخالفات للسائق
    function confirmUnblockDriver(driverId, driverName) {
        Swal.fire({
            title: 'تأكيد إلغاء الحظر وتصفير المخالفات',
            text: `هل أنت متأكد من تصفير مخالفات السائق (${driverName}) وإعادة تنشيط حسابه؟`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#d33',
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
                            confirmButtonColor: '#2c3e50'
                        });

                        // تحديث القيم والشارات ديناميكياً لتوفير تجربة مستخدم متميزة
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
                            confirmButtonColor: '#d33'
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطأ اتصال',
                        text: 'تعذر الاتصال بالخادم.',
                        confirmButtonColor: '#d33'
                    });
                }
            }
        });
    }

    // تهيئة الرسم البياني الخطي باستخدام مكتبة Chart.js لآخر 30 يوماً
    document.addEventListener('DOMContentLoaded', function() {
        try {
            const chartCanvas = document.getElementById('financialLineChart');
            if (chartCanvas) {
                // قراءة البيانات الممررة من الواجهة الخلفية
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
                                borderColor: '#38ef7d',
                                backgroundColor: 'rgba(56, 239, 125, 0.7)',
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
                                    color: '#f0f2f5'
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
    </script>
</body>
</html>
