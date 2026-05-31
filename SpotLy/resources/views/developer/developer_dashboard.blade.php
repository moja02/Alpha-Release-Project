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
                    <div class="card-body text-center p-5">
                        <i class="fas fa-tools fa-4x text-muted mb-3"></i>
                        <h4 class="text-secondary">واجهة إدارة الموظفين</h4>
                        <p class="text-muted">هنا سيتم برمجة جدول يعرض الموظفين لتعيينهم على المواقف التي أنشأتها.</p>
                    </div>
                </div>
            </section>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
    let map;
    let marker;
    let developerId = null;

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

    // 3. دالة تشغيل خريطة Leaflet
    function initMap() {
        map = L.map('map').setView([32.8872, 13.1913], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

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
                if(marker) map.removeLayer(marker);
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