<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spotly - لوحة تحكم المطور</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        :root {
            --sidebar-bg: #2c3e50; /* لون مقارب للقائمة في صورتك */
            --sidebar-width: 250px;
            --body-bg: #f4f6f9;
        }

        body {
            background-color: var(--body-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }

        /* 1. تنسيق القائمة الجانبية (يطابق صورتك) */
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--sidebar-bg);
            color: #fff;
            position: fixed;
            top: 0;
            right: 0;
            height: 100vh;
            z-index: 1000;
        }

        .sidebar-brand {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .sidebar-brand h3 {
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .nav-link {
            color: #aebdb8;
            padding: 15px 25px;
            font-size: 1.05rem;
            transition: all 0.3s;
            cursor: pointer;
            border-right: 3px solid transparent;
        }

        .nav-link:hover, .nav-link.active {
            color: #fff;
            background-color: rgba(255,255,255,0.05);
            border-right: 3px solid #f1c40f; /* خط أصفر رفيع للتحديد */
        }

        .nav-link i { margin-left: 10px; width: 20px; text-align: center; }

        /* 2. تنسيق المحتوى الرئيسي والشريط العلوي */
        .main-content {
            margin-right: var(--sidebar-width);
            min-height: 100vh;
        }

        .top-header {
            background: #fff;
            padding: 15px 30px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-weight: bold;
            color: #495057;
            margin: 0;
        }

        /* تنسيق رسالة الترحيب مطابقة لصورتك */
        .welcome-alert {
            background-color: #e0f2f1;
            border: none;
            color: #00695c;
            border-radius: 8px;
            padding: 15px 20px;
        }

        /* البطاقات البيضاء في نظرة عامة */
        .stat-card {
            background: #fff;
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
            border-top: 4px solid;
            transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-3px); }
        .border-t-yellow { border-top-color: #f1c40f; }
        .border-t-green { border-top-color: #2ecc71; }
        .border-t-blue { border-top-color: #3498db; }

        /* خريطة Leaflet */
        #map { height: 450px; width: 100%; border-radius: 8px; border: 1px solid #ced4da; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-brand">
            <h3>Spotly 🚗</h3>
            <span class="badge bg-warning text-dark px-3 py-1 mt-1 fs-6">بوابة المطور</span>
        </div>
        <div class="nav flex-column mt-3">
            <a class="nav-link active" onclick="switchTab('overviewTab', this, 'نظرة عامة 🏠')">
                نظرة عامة
            </a>
            <a class="nav-link" onclick="switchTab('addParkingTab', this, 'إضافة ساحة ميدان 📍')">
                إضافة ساحات (مواقف)
            </a>
            <a class="nav-link" onclick="switchTab('employeesTab', this, 'إدارة موظفي الميدان 👥')">
                إدارة موظفي الميدان
            </a>
            <a class="nav-link" onclick="switchTab('addManagerTab', this, 'إنشاء حسابات المدراء 👤')">
                إنشاء حسابات المدراء
            </a>
        </div>
    </div>

    <div class="main-content">
        <header class="top-header">
            <h5 class="page-title" id="headerTitle">نظرة عامة 🏠</h5>
            <div class="d-flex align-items-center gap-3">
                <span class="fw-bold text-dark">مرحباً، <span id="developerNameDisplay">مدير النظام</span></span>
                <button class="btn btn-outline-danger btn-sm px-4 fw-bold" onclick="logoutDeveloper()">تسجيل الخروج</button>
            </div>
        </header>

        <div class="p-4">
            <div class="welcome-alert mb-4">
                📌 <strong>مرحباً بك في لوحة تحكم المطور:</strong> تتيح لك هذه اللوحة إدارة البنية التحتية للنظام، تحديد المواقف جغرافياً على الخريطة، وإدارة حسابات موظفي الميدان وصلاحياتهم.
            </div>

            <section id="overviewTab" class="content-section">
                <div class="row text-center g-4">
                    <div class="col-md-4">
                        <div class="stat-card border-t-yellow p-4">
                            <h5 class="fw-bold mb-2">إجمالي المواقف المُسجلة</h5>
                            <h2 class="text-secondary mb-0">{{ $parkingsCount }} ساحات</h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card border-t-green p-4">
                            <h5 class="fw-bold mb-2">موظفي الميدان</h5>
                            <h2 class="text-secondary mb-0">{{ $employeesCount }} موظف</h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card border-t-blue p-4">
                            <h5 class="fw-bold mb-2">حالة النظام</h5>
                            <h2 class="text-success mb-0">مستقر 🟢</h2>
                        </div>
                    </div>
                </div>
            </section>

            <section id="addParkingTab" class="content-section d-none">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h5 class="mb-4 text-primary fw-bold"><i class="fas fa-map-marked-alt"></i> تحديد وإنشاء موقف جديد</h5>
                        <form id="addParkingForm">
                            <div class="row">
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
                                    <hr>
                                    <div class="alert alert-warning py-2 mb-3">
                                        <small><i class="fas fa-hand-pointer"></i> انقر على الخريطة لتسجيل الإحداثيات تلقائياً.</small>
                                    </div>
                                    <div class="row g-2 mb-4">
                                        <div class="col-6">
                                            <label class="form-label text-muted small">خط العرض (Lat)</label>
                                            <input type="text" id="latInput" class="form-control bg-light text-center" readonly required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label text-muted small">خط الطول (Lng)</label>
                                            <input type="text" id="lngInput" class="form-control bg-light text-center" readonly required>
                                        </div>
                                    </div>
                                    <button type="button" onclick="submitParking()" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">حفظ وتفعيل الموقف 💾</button>
                                </div>
                                
                                <div class="col-md-8">
                                    <div id="map" class="shadow-sm"></div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

            <section id="employeesTab" class="content-section d-none">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h5 class="mb-4 text-primary fw-bold">
                            <i class="fas fa-users me-1"></i> قائمة موظفي الميدان المسجلين
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
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
                                            <td class="fw-bold">{{ $employee->name }}</td>
                                            <td>{{ $employee->email }}</td>
                                            <td>{{ $employee->phone }}</td>
                                            <td><code class="text-dark">{{ $employee->bank_account_number }}</code></td>
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
                                            <td colspan="6" class="text-center text-muted py-4">لا يوجد موظفي ميدان مسجلين حالياً.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <section id="addManagerTab" class="content-section d-none">
                <div class="row g-4">
                    <!-- نموذج إنشاء حساب مدير جديد -->
                    <div class="col-md-5">
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-4">
                                <h5 class="mb-4 text-primary fw-bold">
                                    <i class="fas fa-user-plus me-1"></i> إنشاء حساب مدير جديد
                                </h5>
                                <form id="addManagerForm">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-secondary">الاسم الكامل</label>
                                        <input type="text" id="managerName" class="form-control form-control-lg" placeholder="مثال: علي محمد" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-secondary">البريد الإلكتروني</label>
                                        <input type="email" id="managerEmail" class="form-control form-control-lg" placeholder="example@spotly.com" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-secondary">رقم الهاتف</label>
                                        <input type="text" id="managerPhone" class="form-control form-control-lg" placeholder="09XXXXXXXX" required>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-secondary">كلمة المرور</label>
                                        <input type="password" id="managerPassword" class="form-control form-control-lg" placeholder="••••••••" required>
                                    </div>
                                    <button type="button" onclick="submitManager()" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">
                                        إنشاء الحساب 💾
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- جدول المدراء الحاليين -->
                    <div class="col-md-7">
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-4">
                                <h5 class="mb-4 text-primary fw-bold">
                                    <i class="fas fa-users-cog me-1"></i> المدراء الحاليون في النظام
                                </h5>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle" id="managersTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th>الاسم</th>
                                                <th>البريد الإلكتروني</th>
                                                <th>رقم الهاتف</th>
                                                <th>الحالة</th>
                                                <th>الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($managers as $manager)
                                                <tr id="manager-row-{{ $manager->id }}">
                                                    <td class="fw-bold">{{ $manager->name }}</td>
                                                    <td>{{ $manager->email }}</td>
                                                    <td>{{ $manager->phone }}</td>
                                                    <td>
                                                        @if($manager->status === 'active')
                                                            <span class="badge bg-success status-badge">نشط</span>
                                                        @else
                                                            <span class="badge bg-danger status-badge">معطل</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary manage-parkings-btn fw-bold ms-1" onclick="openManageParkingsModal({{ $manager->id }})">
                                                            <i class="fas fa-parking me-1"></i> إدارة الساحات
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-secondary toggle-status-btn fw-bold" onclick="toggleManagerStatus({{ $manager->id }}, this)">
                                                            @if($manager->status === 'active')
                                                                تعطيل الحساب
                                                            @else
                                                                تفعيل الحساب
                                                            @endif
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">لا يوجد مدراء مسجلين حالياً.</td>
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

    <!-- نافذة إدارة ربط الساحات بالمدير -->
    <div class="modal fade" id="manageParkingsModal" tabindex="-1" aria-labelledby="manageParkingsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header bg-primary text-white" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h5 class="modal-title fw-bold" id="manageParkingsModalLabel">
                        <i class="fas fa-parking me-1"></i> إدارة ربط الساحات
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
                            <!-- سيتم إدراج الساحات هنا عبر JS -->
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" onclick="saveManagerParkings()" class="btn btn-primary fw-bold px-4" id="saveParkingsBtn">حفظ التغييرات 💾</button>
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
    // قائمة الساحات المسجلة من قاعدة البيانات لعرضها على الخريطة
    const existingParkings = @json($parkingsList);

    // تعريف أيقونة برتقالية مخصصة للساحات الحالية
    const orangeIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-orange.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });

    // 1. التحقق من تسجيل الدخول عند فتح الصفحة
    document.addEventListener('DOMContentLoaded', () => {
        try {
            // سحب البيانات من الذاكرة (سواء كنت تحفظها باسم userData أو developerData)
            const storedData = JSON.parse(localStorage.getItem('userData') || localStorage.getItem('developerData'));

            // السماح للمطور فقط بالدخول
            if (!storedData || storedData.role !== 'developer') {
                Swal.fire({
                    icon: 'error',
                    title: 'غير مصرح',
                    text: 'هذه البوابة مخصصة للمطورين فقط.',
                    allowOutsideClick: false
                }).then(() => {
                    window.location.href = '/login'; 
                });
                return;
            }

            // إذا كان مطوراً، نحفظ الـ ID ونعرض اسمه في الشريط العلوي
            developerId = storedData.accountId || storedData.id;
            document.getElementById('developerNameDisplay').innerText = storedData.name;

        } catch(e) {
            window.location.href = '/login';
        }
    });

    // 2. دالة التبديل بين الأقسام بذكاء
    function switchTab(tabId, element, title) {
        document.querySelectorAll('.content-section').forEach(sec => sec.classList.add('d-none'));
        document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
        
        document.getElementById(tabId).classList.remove('d-none');
        element.classList.add('active');
        document.getElementById('headerTitle').innerText = title;

        if (tabId === 'addParkingTab') {
            if (!map) initMap(); 
            else setTimeout(() => { map.invalidateSize(); }, 300); 
        }
    }

    // 3. دالة تشغيل خريطة Leaflet ورسم الساحات الحالية
    function initMap() {
        map = L.map('map').setView([32.8872, 13.1913], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        // وضع علامات (Markers) للساحات المسجلة مسبقاً
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

        map.on('click', function(e) {
            document.getElementById('latInput').value = e.latlng.lat.toFixed(6);
            document.getElementById('lngInput').value = e.latlng.lng.toFixed(6);

            if (marker) marker.setLatLng(e.latlng);
            else marker = L.marker(e.latlng).addTo(map);
        });
    }

    // 4. دالة الحفظ الفعلي في قاعدة البيانات (Laravel)
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
            const response = await fetch('{{ route("developer.parking.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    name: name,
                    location_park: location_park, //  إرسال القيمة للسيرفر
                    total_capacity: capacity,
                    latitude: lat,
                    longitude: lng
                })
            });

            const data = await response.json();

            if (response.ok) {
                Swal.fire({icon: 'success', title: 'نجاح 🎉', text: data.message});
                document.getElementById('addParkingForm').reset();
                
                // رسم الساحة الجديدة على الخريطة مباشرة وتخزينها
                if (data.parking && data.parking.latitude && data.parking.longitude) {
                    L.marker([data.parking.latitude, data.parking.longitude], {icon: orangeIcon})
                        .addTo(map)
                        .bindPopup(`
                            <div style="direction: rtl; text-align: right; font-family: sans-serif; min-width: 150px;">
                                <h6 class="fw-bold mb-1 text-primary"><i class="fas fa-parking me-1"></i> ${data.parking.name}</h6>
                                <small class="text-muted d-block mb-2">ساحة وقوف فعالة</small>
                                <p class="mb-1 text-dark small"><b>إجمالي السعة:</b> ${data.parking.total_capacity} مركبة</p>
                                <p class="mb-0 text-success small"><b>الشواغر المتوفرة:</b> ${data.parking.available_capacity} مركبة</p>
                            </div>
                        `);
                    
                    existingParkings.push(data.parking);
                }

                if(marker) map.removeLayer(marker);
            } else {
                throw new Error(data.message || 'حدث خطأ أثناء الحفظ.');
            }
        } catch (error) {
            Swal.fire({icon: 'error', title: 'عذراً', text: error.message});
        }
    }

    // دالة إنشاء حساب مدير جديد
    async function submitManager() {
        const name = document.getElementById('managerName').value;
        const email = document.getElementById('managerEmail').value;
        const phone = document.getElementById('managerPhone').value;
        const password = document.getElementById('managerPassword').value;

        if(!name || !email || !phone || !password) {
            Swal.fire({icon: 'warning', title: 'بيانات ناقصة', text: 'يرجى تعبئة جميع الحقول المطلوبة.'});
            return;
        }

        Swal.fire({ title: 'جاري إنشاء الحساب...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

        try {
            const response = await fetch('{{ route("developer.manager.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    name: name,
                    email: email,
                    phone: phone,
                    password: password
                })
            });

            const data = await response.json();

            if (response.ok) {
                Swal.fire({icon: 'success', title: 'نجاح 🎉', text: data.message});
                document.getElementById('addManagerForm').reset();
                
                // إضافة المدير الجديد إلى الجدول ديناميكياً
                const tableBody = document.querySelector('#managersTable tbody');
                if (tableBody.innerHTML.includes('لا يوجد مدراء')) {
                    tableBody.innerHTML = '';
                }
                
                const newRow = `
                    <tr id="manager-row-${data.manager.id}">
                        <td class="fw-bold">${data.manager.name}</td>
                        <td>${data.manager.email}</td>
                        <td>${data.manager.phone}</td>
                        <td><span class="badge bg-success status-badge">نشط</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary manage-parkings-btn fw-bold ms-1" onclick="openManageParkingsModal(${data.manager.id})">
                                <i class="fas fa-parking me-1"></i> إدارة الساحات
                            </button>
                            <button class="btn btn-sm btn-outline-secondary toggle-status-btn fw-bold" onclick="toggleManagerStatus(${data.manager.id}, this)">
                                تعطيل الحساب
                            </button>
                        </td>
                    </tr>
                `;
                tableBody.insertAdjacentHTML('afterbegin', newRow);
            } else {
                throw new Error(data.message || 'حدث خطأ أثناء إنشاء الحساب.');
            }
        } catch (error) {
            Swal.fire({icon: 'error', title: 'عذراً', text: error.message});
        }
    }

    // دالة تعطيل/تفعيل حساب المدير
    async function toggleManagerStatus(managerId, button) {
        Swal.fire({ title: 'جاري تحديث الحالة...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

        try {
            const response = await fetch(`/developer/managers/${managerId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const data = await response.json();

            if (response.ok) {
                Swal.fire({icon: 'success', title: 'تم التحديث', text: data.message, timer: 1500, showConfirmButton: false});
                
                // تحديث واجهة السطر الحالي
                const row = document.getElementById(`manager-row-${managerId}`);
                if (row) {
                    const badge = row.querySelector('.status-badge');
                    if (data.new_status === 'active') {
                        badge.className = 'badge bg-success status-badge';
                        badge.innerText = 'نشط';
                        button.innerText = 'تعطيل الحساب';
                    } else {
                        badge.className = 'badge bg-danger status-badge';
                        badge.innerText = 'معطل';
                        button.innerText = 'تفعيل الحساب';
                    }
                }
            } else {
                throw new Error(data.message || 'حدث خطأ أثناء تحديث حالة الحساب.');
            }
        } catch (error) {
            Swal.fire({icon: 'error', title: 'عذراً', text: error.message});
        }
    }

    // دالة فتح نافذة ربط الساحات بالمدير وجلب البيانات
    async function openManageParkingsModal(managerAccountId) {
        // تهيئة الـ Modal وعرضه
        const modalEl = document.getElementById('manageParkingsModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        // إعادة ضبط حالة الواجهة
        document.getElementById('modalManagerName').innerText = '...';
        document.getElementById('modalManagerAccountId').value = managerAccountId;
        document.getElementById('modalLoadingSpinner').classList.remove('d-none');
        document.getElementById('manageParkingsForm').classList.add('d-none');
        
        const container = document.getElementById('parkingsListContainer');
        container.innerHTML = '';

        try {
            const response = await fetch(`/developer/managers/${managerAccountId}/parkings`);
            const data = await response.json();

            if (response.ok) {
                document.getElementById('modalManagerName').innerText = data.manager_name;
                
                if (data.parkings.length === 0) {
                    container.innerHTML = '<div class="text-center py-4 text-muted">لا توجد ساحات غير مربوطة في النظام حالياً.</div>';
                } else {
                    data.parkings.forEach(parking => {
                        const isChecked = parking.manager_id === data.manager_id ? 'checked' : '';
                        const isLinkedBadge = parking.manager_id === data.manager_id 
                            ? '<span class="badge bg-primary text-white ms-2">مرتبطة بهذا المدير</span>' 
                            : '<span class="badge bg-secondary text-white ms-2">غير مربوطة</span>';
                        
                        const parkingHtml = `
                            <div class="form-check p-3 mb-2 rounded border border-light-subtle bg-white shadow-sm d-flex justify-content-between align-items-center" style="direction: rtl;">
                                <div class="d-flex align-items-center">
                                    <input class="form-check-input ms-3 fs-5" type="checkbox" name="parking_ids[]" value="${parking.id}" id="parking-chk-${parking.id}" ${isChecked}>
                                    <label class="form-check-label fw-bold text-dark fs-6" for="parking-chk-${parking.id}">
                                        ${parking.name}
                                        <small class="text-muted d-block mt-1 fw-normal"><i class="fas fa-map-marker-alt me-1"></i> ${parking.location_park}</small>
                                    </label>
                                </div>
                                <div>
                                    ${isLinkedBadge}
                                </div>
                            </div>
                        `;
                        container.insertAdjacentHTML('beforeend', parkingHtml);
                    });
                }

                document.getElementById('modalLoadingSpinner').classList.add('d-none');
                document.getElementById('manageParkingsForm').classList.remove('d-none');
            } else {
                throw new Error(data.message || 'حدث خطأ أثناء جلب البيانات.');
            }
        } catch (error) {
            bootstrap.Modal.getInstance(modalEl).hide();
            Swal.fire({icon: 'error', title: 'عذراً', text: error.message});
        }
    }

    // دالة حفظ تغييرات ربط الساحات بالمدير
    async function saveManagerParkings() {
        const managerAccountId = document.getElementById('modalManagerAccountId').value;
        const checkedBoxes = document.querySelectorAll('input[name="parking_ids[]"]:checked');
        const parkingIds = Array.from(checkedBoxes).map(cb => parseInt(cb.value));

        Swal.fire({ title: 'جاري حفظ التغييرات...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

        try {
            const response = await fetch(`/developer/managers/${managerAccountId}/parkings`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    parking_ids: parkingIds
                })
            });

            const data = await response.json();

            if (response.ok) {
                Swal.fire({icon: 'success', title: 'نجاح 🎉', text: data.message, timer: 1500, showConfirmButton: false});
                // إغلاق الـ Modal
                const modalEl = document.getElementById('manageParkingsModal');
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) {
                    modalInstance.hide();
                }
            } else {
                throw new Error(data.message || 'حدث خطأ أثناء الحفظ.');
            }
        } catch (error) {
            Swal.fire({icon: 'error', title: 'عذراً', text: error.message});
        }
    }

    // 5. دالة تسجيل الخروج
    function logoutDeveloper() {
        Swal.fire({
            title: 'تسجيل الخروج',
            text: 'هل أنت متأكد أنك تريد المغادرة؟',
            icon: 'question',
            showCancelButton: true,
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