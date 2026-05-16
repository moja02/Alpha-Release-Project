<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpotLy - لوحة تحكم الموظف</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        }
        .sidebar .nav-link {
            color: #bdc3c7;
            padding: 12px 20px;
            margin: 4px 10px;
            border-radius: 8px;
            transition: all 0.3s;
            cursor: pointer;
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
            border-radius: 10px; 
            border: none; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.06); 
        }
        .card-header { 
            font-weight: bold; 
            background-color: #ffffff; 
            border-bottom: 2px solid #f0f2f5; 
            padding: 15px 20px;
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="text-center mb-4">
            <h4 class="text-white fw-bold">🚗 SpotLy</h4>
            <span class="badge bg-warning text-dark">بوابة الموظف</span>
        </div>
        <hr class="border-secondary border-opacity-50 mx-3">
        <nav class="nav flex-column">
            <a class="nav-link active" onclick="switchTab('overviewTab', this)">
                 نظرة عامة
            </a>
            <a class="nav-link" onclick="switchTab('profileTab', this)">
                 البيانات الشخصية
            </a>
            <a class="nav-link" onclick="switchTab('createUserTab', this)">
                 إضافة مستخدم (سائق)
            </a>
            <!-- <a class="nav-link" onclick="switchTab('verifyVehicleTab', this)"> يتم العمل عليها لاحقا لانها ليست جزء من المتطلبات ال4 الحالية
                 التحقق من السيارات
            </a> -->
            <a class="nav-link" onclick="switchTab('rechargeWalletTab', this)">
                 شحن المحافظ
            </a>
            <a class="nav-link" onclick="switchTab('transferRequestsTab', this)">
                 طلبات التحويل البنكي
            </a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="dashboard-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-secondary" id="pageTitleDisplay">🏠 نظرة عامة</h5>
            <div>
                <span id="employeeNameDisplay" class="me-3 fw-bold text-dark"></span>
                <button onclick="logoutEmployee()" class="btn btn-sm btn-outline-danger">تسجيل الخروج</button>
            </div>
        </header>

        <section id="overviewTab" class="content-section">
            <div class="alert alert-info border-0 shadow-sm">
                📌 <strong>مرحباً بك في لوحة التحكم الميدانية:</strong> 
                تتيح لك هذه اللوحة إدارة حسابات السائقين الجدد، شحن الأرصدة المباشر للمستخدمين، وإدخال السيارات والتحقق من الحجوزات عند المَدخل.
            </div>
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card p-3 text-center border-start border-primary border-4">
                        <h6 class="text-muted">إضافة السائقين</h6>
                        <p class="fs-6 mb-0">إنشاء حسابات وتخصيص بيانات اللوحة</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3 text-center border-start border-success border-4">
                        <h6 class="text-muted">التحقق الميداني</h6>
                        <p class="fs-6 mb-0">فحص الحجوزات الفعلية ومطابقتها</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3 text-center border-start border-warning border-4">
                        <h6 class="text-muted">المحافظ الرقمية</h6>
                        <p class="fs-6 mb-0">تنفيذ أوامر الشحن المباشر للأرصدة</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="profileTab" class="content-section d-none">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-xl-7">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-gradient bg-primary text-white p-4 border-0 d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="fw-bold mb-1">⚙️ إعدادات الحساب والبيانات الشخصية</h5>
                                <p class="fs-6 mb-0 text-white text-opacity-75">إدارة بيانات الاتصال والحساب المصرفي الخاص بك</p>
                            </div>
                            <span class="fs-1">🔒</span>
                        </div>

                        <div class="card-body p-4">
                            <div id="profileAlert" class="alert d-none" role="alert"></div>

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

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-secondary">رقم الحساب المصرفي (IBAN / رقم الحساب)</label>
                                    <div class="input-group input-group-lg shadow-none">
                                        <span class="input-group-text bg-light border-end-0">🏦</span>
                                        <input type="text" class="form-control border-start-0 ps-0" id="profileBankInput" placeholder="أدخل بيانات حسابك المصرفي">
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

        <section id="createUserTab" class="content-section d-none">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-8">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-gradient bg-primary text-white p-4 border-0 d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="fw-bold mb-1">👤 تسجيل سائق جديد في النظام</h5>
                                <p class="fs-6 mb-0 text-white text-opacity-75">إنشاء حساب تشغيلي وتخصيص بيانات المركبة</p>
                            </div>
                            <span class="fs-1">🚗</span>
                        </div>

                        <div class="card-body p-4">
                            <div id="createUserAlert" class="alert d-none" role="alert"></div>

                            <form id="createUserForm">
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-secondary">الاسم الكامل</label>
                                        <div class="input-group input-group-lg shadow-none">
                                            <span class="input-group-text bg-light border-end-0">📝</span>
                                            <input type="text" class="form-control border-start-0 ps-0" id="userNameInput" required placeholder="أدخل اسم السائق">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-secondary">البريد الإلكتروني</label>
                                        <div class="input-group input-group-lg shadow-none">
                                            <span class="input-group-text bg-light border-end-0">📧</span>
                                            <input type="email" class="form-control border-start-0 ps-0" id="userEmailInput" required placeholder="name@example.com">
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-secondary">رقم الهاتف</label>
                                        <div class="input-group input-group-lg shadow-none">
                                            <span class="input-group-text bg-light border-end-0">📱</span>
                                            <input type="text" class="form-control border-start-0 ps-0" id="userPhoneInput" required placeholder="مثال: 0912345678">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-secondary">رقم لوحة السيارة</label>
                                        <div class="input-group input-group-lg shadow-none">
                                            <span class="input-group-text bg-light border-end-0">🏷️</span>
                                            <input type="text" class="form-control border-start-0 ps-0" id="userPlateInput" required placeholder="مثال: 5-12345">
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm rounded-3 py-3" id="createUserBtn">
                                    تسجيل السائق وتوليد بيانات الدخول 🚀
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- <section id="verifyVehicleTab" class="content-section d-none"> يتم العمل عليها لاحقا لانها ليست جزء من المتطلبات ال4 الحالية
            <div class="card" style="max-width: 500px;">
                <div class="card-header text-success">
                    🚘 التحقق من السيارات عند المَدخل
                </div>
                <div class="card-body">
                    <div id="verifyVehicleAlert" class="alert d-none" role="alert"></div>
                    <form id="verifyVehicleForm">
                        <div class="mb-3">
                            <label class="form-label">رقم لوحة السيارة</label>
                            <input type="text" class="form-control" id="verifyPlateInput" placeholder="أدخل رقم اللوحة لفحص الحجز الفعلي" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100" id="verifyVehicleBtn">فحص حالة الحجز الميداني</button>
                    </form>
                </div>
            </div>
        </section> -->

        <section id="rechargeWalletTab" class="content-section d-none">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-xl-7">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-gradient bg-warning text-dark p-4 border-0 d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="fw-bold mb-1">💳 الشحن الفوري للمحفظة الرقمية</h5>
                                <p class="fs-6 mb-0 text-dark text-opacity-75">إضافة رصيد نقاط مباشرة إلى حساب السائق</p>
                            </div>
                            <span class="fs-1">⚡</span>
                        </div>

                        <div class="card-body p-4">
                            <form id="rechargeWalletForm">
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-secondary mb-2">👤 المستفيد (معرف الحساب - Account ID)</label>
                                    <div class="input-group input-group-lg shadow-none">
                                        <span class="input-group-text bg-light border-end-0">ID</span>
                                        <input type="number" class="form-control border-start-0 ps-0" id="targetUserIdInput" required placeholder="أدخل رقم ID الخاص بالسائق (مثال: 1)">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-secondary mb-3 d-block">✨ باقات الشحن السريعة المقترحة</label>
                                    <div class="row g-2">
                                        <div class="col-4 col-sm-4">
                                            <input type="radio" class="btn-check fast-amount-radio" name="fastPoints" id="points5" autocomplete="off" onchange="updateCustomAmount(5)">
                                            <label class="btn btn-outline-warning w-100 p-3 rounded-3 text-dark fw-bold border-2" for="points5">
                                                <span class="fs-4 d-block">5</span>
                                                <small class="text-muted">نقاط</small>
                                            </label>
                                        </div>
                                        <div class="col-4 col-sm-4">
                                            <input type="radio" class="btn-check fast-amount-radio" name="fastPoints" id="points10" autocomplete="off" onchange="updateCustomAmount(10)">
                                            <label class="btn btn-outline-warning w-100 p-3 rounded-3 text-dark fw-bold border-2" for="points10">
                                                <span class="fs-4 d-block">10</span>
                                                <small class="text-muted">نقاط</small>
                                            </label>
                                        </div>
                                        <div class="col-4 col-sm-4">
                                            <input type="radio" class="btn-check fast-amount-radio" name="fastPoints" id="points15" autocomplete="off" onchange="updateCustomAmount(15)">
                                            <label class="btn btn-outline-warning w-100 p-3 rounded-3 text-dark fw-bold border-2" for="points15">
                                                <span class="fs-4 d-block">15</span>
                                                <small class="text-muted">نقطة</small>
                                            </label>
                                        </div>
                                        <div class="col-4 col-sm-4">
                                            <input type="radio" class="btn-check fast-amount-radio" name="fastPoints" id="points20" autocomplete="off" onchange="updateCustomAmount(20)">
                                            <label class="btn btn-outline-warning w-100 p-3 rounded-3 text-dark fw-bold border-2" for="points20">
                                                <span class="fs-4 d-block">20</span>
                                                <small class="text-muted">نقطة</small>
                                            </label>
                                        </div>
                                        <div class="col-4 col-sm-4">
                                            <input type="radio" class="btn-check fast-amount-radio" name="fastPoints" id="points25" autocomplete="off" onchange="updateCustomAmount(25)">
                                            <label class="btn btn-outline-warning w-100 p-3 rounded-3 text-dark fw-bold border-2" for="points25">
                                                <span class="fs-4 d-block">25</span>
                                                <small class="text-muted">نقطة</small>
                                            </label>
                                        </div>
                                        <div class="col-4 col-sm-4">
                                            <input type="radio" class="btn-check fast-amount-radio" name="fastPoints" id="points50" autocomplete="off" onchange="updateCustomAmount(50)">
                                            <label class="btn btn-outline-warning w-100 p-3 rounded-3 text-dark fw-bold border-2" for="points50">
                                                <span class="fs-4 d-block">50</span>
                                                <small class="text-muted">نقطة</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-secondary mb-2" for="pointsAmountInput">✏️ أو إدخال رصيد مخصص (الحد الأدنى 3 نقاط)</label>
                                    <input type="number" class="form-control form-control-lg bg-light" id="pointsAmountInput" min="3" placeholder="أدخل عدد النقاط يدوياً" required oninput="clearRadioSelection()">
                                </div>

                                <button type="submit" class="btn btn-warning btn-lg w-100 fw-bold shadow-sm rounded-3 py-3" id="rechargeWalletBtn">
                                    تنفيذ الشحن وإضافة الرصيد للمحفظة 🚀
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="transferRequestsTab" class="content-section d-none">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>📄 طلبات الشحن بانتظار المراجعة</span>
                    <button class="btn btn-sm btn-primary" onclick="loadPendingRequests()">تحديث القائمة</button>
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID المستخدم</th>
                                <th>الاسم</th>
                                <th>المبلغ (نقاط)</th>
                                <th>الإيصال</th>
                                <th>الإجراء</th>
                            </tr>
                        </thead>
                        <tbody id="pendingRequestsTable">
                            </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <script>
    // ---  قراءة بيانات الجلسة عند تحميل الصفحة لعرض اسم الموظف ---
    document.addEventListener('DOMContentLoaded', function() {
        
        try {
            const userDataString = localStorage.getItem('userData');
            if (userDataString) {
                const userDataObject = JSON.parse(userDataString);
                // تحديث واجهة المستخدم باسم الموظف الميداني الفعلي
                document.getElementById('employeeNameDisplay').innerText = 'مرحباً، ' + userDataObject.name;
            } else {
                // طرد المستخدم وإعادته لصفحة الدخول في حال عدم وجود جلسة نشطة
                window.location.href = '/login';
            }
        } catch (exception) {
            console.error("خطأ في تهيئة الصفحة", exception);
        }
    });

    // ---  التنقل بين الأقسام وإظهار القسم المطلوب فقط بناءً على اختيار القائمة ---
    function switchTab(sectionId, clickedLink) {
        try {
            // إخفاء كافة الأقسام النشطة في منطقة المحتوى الرئيسي
            const allSections = document.querySelectorAll('.content-section');
            allSections.forEach(section => {
                try {
                    section.classList.add('d-none');
                } catch (innerException) {
                    console.error(innerException);
                }
            });

            // إظهار القسم المستهدف (Section) بناءً على المعرف الممرر
            const targetSection = document.getElementById(sectionId);
            if (targetSection) {
                targetSection.classList.remove('d-none');
            }

            // إزالة فئة التفعيل (active) بصرياً من كافة روابط القائمة الجانبية
            const allLinks = document.querySelectorAll('.sidebar .nav-link');
            allLinks.forEach(link => {
                try {
                    link.classList.remove('active');
                } catch (innerException) {
                    console.error(innerException);
                }
            });

            // تفعيل الرابط الذي تم الضغط عليه وتحديث عنوان الصفحة العلوي
            clickedLink.classList.add('active');
            const titleDisplay = document.getElementById('pageTitleDisplay');
            titleDisplay.innerText = clickedLink.innerText.trim();

            // فحص إذا كان القسم المختار هو قسم البيانات الشخصية لتحميلها من الجلسة تلقائياً
            if (sectionId === 'profileTab') {
                loadProfileData();
            }

            // فحص إذا كان القسم المختار هو طلبات التحويل لتحديث القائمة من السيرفر مباشرة
            if (sectionId === 'transferRequestsTab') {
                loadPendingRequests();
            }
        } catch (exception) {
            console.error("خطأ أثناء التبديل بين الأقسام", exception);
        }
    }

    // ---  دالة مساعدة لجلب بيانات الموظف من الـ LocalStorage وتوزيعها على الحقول ---
    function loadProfileData() {
        try {
            const userDataString = localStorage.getItem('userData');
            if (userDataString) {
                const userData = JSON.parse(userDataString);
                
                // تعبئة حقول نموذج الملف الشخصي بالبيانات الحالية
                document.getElementById('profileNameDisplay').value = userData.name;
                document.getElementById('profilePhoneInput').value = userData.phone || '';
                
                // التحقق من وجود بيانات الملف الشخصي (Profile) وتعبئة الحساب المصرفي
                if (userData.profile && userData.profile.bank_account_number) {
                    document.getElementById('profileBankInput').value = userData.profile.bank_account_number;
                }
            }
        } catch (exception) {
            console.error("خطأ أثناء تحميل بيانات الملف الشخصي", exception);
        }
    }

    // ---  دالة تسجيل الخروج ومسح الجلسة المحلية بالكامل ---
    function logoutEmployee() {
        try {
            // إظهار نافذة منبثقة تفاعلية لتأكيد رغبة الموظف في المغادرة
            Swal.fire({
                title: 'هل تريد تسجيل الخروج؟',
                text: "سيتم إنهاء جلستك الحالية في النظام",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'نعم، تسجيل الخروج',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                try {
                    if (result.isConfirmed) {
                        // مسح التخزين المحلي وإعادة التوجيه لبوابة تسجيل الدخول
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

    // ---  معالجة إرسال نموذج إضافة مستخدم جديد (سائق) ---
    document.getElementById('createUserForm').addEventListener('submit', async function(event) {
        try {
            // منع إعادة تحميل الصفحة الافتراضي
            event.preventDefault();
            
            
            const userNameValue = document.getElementById('userNameInput').value;
            const userEmailValue = document.getElementById('userEmailInput').value;
            const userPhoneValue = document.getElementById('userPhoneInput').value;
            const userPlateValue = document.getElementById('userPlateInput').value;
            const submitButton = document.getElementById('createUserBtn');

            // تعطيل زر الإرسال مؤقتاً لتجنب تكرار الطلبات
            submitButton.disabled = true;

            try {
                // إرسال البيانات بدون كلمة مرور ليقوم الخادم (Laravel) بتوليدها وتشفيرها برمجياً
                const apiResponse = await fetch('/api/accounts/create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        name: userNameValue,
                        email: userEmailValue,
                        phone: userPhoneValue,
                        role: 'user',
                        plateNumber: userPlateValue
                    })
                });

                const responseData = await apiResponse.json();

                if (apiResponse.ok) {
                    // إشعار نجاح يبلغ الموظف أن الرمز السري أرسل لبريد السائق مباشرة
                    Swal.fire({
                        icon: 'success',
                        title: 'تم تسجيل السائق بنجاح',
                        html: `
                            <div class="text-end mt-3">
                                <p><strong>ID الحساب:</strong> ${responseData.accountId}</p>
                                <p class="text-success">✅ تم إرسال رمز الدخول العشوائي إلى بريد السائق بنجاح.</p>
                            </div>
                        `,
                        confirmButtonColor: '#2c3e50'
                    });
                    // تصفير الحقول بالكامل لاستقبال تسجيل جديد
                    document.getElementById('createUserForm').reset();
                } else {
                    // التعامل مع أخطاء المدخلات مثل تكرار البريد الإلكتروني
                    Swal.fire({
                        icon: 'error',
                        title: 'خطأ في التسجيل',
                        text: responseData.message || 'تأكد من عدم تكرار البريد الإلكتروني.',
                        confirmButtonColor: '#d33'
                    });
                }
            } catch (networkError) {
                // عرض تنبيه في حال انقطاع الاتصال بالخادم
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ في الاتصال',
                    text: 'تعذر الوصول للخادم المحلي.',
                    confirmButtonColor: '#d33'
                });
            } finally {
                // إعادة تفعيل الزر في جميع الأحوال
                submitButton.disabled = false;
            }
        } catch (exception) {
            console.error(exception);
        }
    });

    // ---  معالجة نموذج التحقق الميداني من السيارات ومطابقتها ---
    /** 
    document.getElementById('verifyVehicleForm').addEventListener('submit', async function(event) {
        try {
            event.preventDefault();
            
            const verifyPlateValue = document.getElementById('verifyPlateInput').value;
            const alertContainer = document.getElementById('verifyVehicleAlert');
            alertContainer.classList.add('d-none');

            // محاكاة الفحص الميداني بانتظار الربط بوحدة الحجوزات 
            alertContainer.className = 'alert alert-info';
            alertContainer.innerText = 'جاري التحقق من اللوحة (' + verifyPlateValue + ') ومطابقتها ميدانياً...';
            alertContainer.classList.remove('d-none');
        } catch (exception) {
            console.error(exception);
        }
    });
    */
    // ---  معالجة إرسال نموذج تحديث الملف الشخصي للموظف ---
    document.getElementById('profileForm').addEventListener('submit', async function(event) {
        try {
            event.preventDefault();
            
            const alertDiv = document.getElementById('profileAlert');
            const updateBtn = document.getElementById('updateProfileBtn');
            const userData = JSON.parse(localStorage.getItem('userData'));

            updateBtn.disabled = true;

            try {
                // إرسال طلب التحديث إلى المتحكم المنفصل (ProfileController)
                const response = await fetch('/api/accounts/update-profile', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        accountId: userData.accountId,
                        phone: document.getElementById('profilePhoneInput').value,
                        password: document.getElementById('profilePasswordInput').value,
                        bankAccountNumber: document.getElementById('profileBankInput').value
                    })
                });

                const result = await response.json();

                if (response.ok) {
                    if (alertDiv) alertDiv.classList.add('d-none');
                    
                    // تحديث الجلسة المحلية (LocalStorage) لتعكس التغييرات فوراً في الواجهة
                    userData.phone = result.updatedData.phone;
                    if (userData.profile) userData.profile.bank_account_number = result.updatedData.bankAccountNumber;
                    localStorage.setItem('userData', JSON.stringify(userData));

                    Swal.fire({
                        icon: 'success',
                        title: 'تم التحديث!',
                        text: 'تم حفظ بياناتك الشخصية بنجاح.',
                        confirmButtonColor: '#2c3e50'
                    });
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                if (alertDiv) alertDiv.classList.add('d-none');
                Swal.fire({
                    icon: 'error',
                    title: 'فشل التحديث',
                    text: error.message,
                    confirmButtonColor: '#d33'
                });
            } finally {
                updateBtn.disabled = false;
            }
        } catch (exception) {
            console.error(exception);
        }
    });

    /*
    دوال الشحن الفوري
    */

    // دالة لتحديث القيمة يدوياً عند اختيار باقة سريعة
        function updateCustomAmount(amount) {
            const amountInput = document.getElementById('pointsAmountInput');
            if (amountInput) {
                amountInput.value = amount;
            }
        }

        // دالة لإلغاء تحديد الأزرار الدائرية (Radio) عند الكتابة اليدوية
        function clearRadioSelection() {
            const radios = document.querySelectorAll('.fast-amount-radio');
            radios.forEach(radio => {
                radio.checked = false;
            });
        }

    // معالجة إرسال نموذج الشحن الفوري وربطه بـ RechargeController المنفصل
    // ننتظر حتى يكتمل تحميل كل عناصر HTML في الصفحة
    document.addEventListener('DOMContentLoaded', function() {
        
        const formElement = document.getElementById('rechargeWalletForm');
        
        // تأكد أن الفورم موجود فعلاً في الصفحة قبل إضافة الحدث
        if (formElement) {
            formElement.addEventListener('submit', async function(event) {
                try {
                    event.preventDefault();
                    
                    const targetUserIdValue = document.getElementById('targetUserIdInput').value;
                    const pointsAmountValue = document.getElementById('pointsAmountInput').value;
                    const submitRechargeBtn = document.getElementById('rechargeWalletBtn');

                    submitRechargeBtn.disabled = true;

                    try {
                        Swal.fire({
                            title: 'جاري معالجة الشحن...',
                            html: `إضافة <b>${pointsAmountValue}</b> نقاط للحساب رقم <b>${targetUserIdValue}</b>`,
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // توجيه الطلب إلى مسار الشحن الفوري المخصص
                        const response = await fetch('/api/recharges/direct', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                userId: targetUserIdValue,
                                amount: pointsAmountValue
                            })
                        });

                        const resultData = await response.json();

                        if (response.ok && resultData.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'تم الشحن بنجاح! 💳',
                                html: `تمت إضافة <b>${pointsAmountValue}</b> نقاط إلى رصيد السائق (ID: ${targetUserIdValue}) فوراً.`,
                                confirmButtonColor: '#2c3e50'
                            });

                            formElement.reset(); // استخدام المتغير الآمن هنا
                            if (typeof clearRadioSelection === "function") {
                                clearRadioSelection();
                            }
                        } else {
                            throw new Error(resultData.message || 'فشل تنفيذ عملية الشحن. تأكد من صحة معرف السائق.');
                        }

                    } catch (exception) {
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ في العملية',
                            text: exception.message || 'حدث خطأ أثناء محاولة شحن الرصيد.',
                            confirmButtonColor: '#d33'
                        });
                    } finally {
                        submitRechargeBtn.disabled = false;
                    }
                } catch (exception) {
                    console.error(exception);
                }
            });
        }
    });

    /*
    |--------------------------------------------------------------------------
    | دوال إدارة طلبات التحويل البنكي (FR2)
    |--------------------------------------------------------------------------
    */

    // دالة جلب الطلبات المعلقة وتعبئة الجدول
        async function loadPendingRequests() {
            try {
                const userDataString = localStorage.getItem('userData');
                if (!userDataString) return;
                
                const userDataObj = JSON.parse(userDataString);
                const employeeIdValue = userDataObj.profile.id; 

                const response = await fetch('/api/recharges/pending?employeeId=' + employeeIdValue);
                const resultData = await response.json();
                const tableBodyElement = document.getElementById('pendingRequestsTable');
                
                if (tableBodyElement && response.ok && resultData.status === 'success') {
                    tableBodyElement.innerHTML = '';
                    
                    if (resultData.data.length === 0) {
                        tableBodyElement.innerHTML = '<tr><td colspan="5" class="text-muted py-4">لا توجد طلبات شحن معلقة حالياً.</td></tr>';
                        return;
                    }

                    resultData.data.forEach(reqItem => {
                        try {
                            tableBodyElement.innerHTML += `
                                <tr>
                                    <td class="fw-bold">${reqItem.user_id}</td>
                                    <td>${reqItem.user_name}</td>
                                    <td><span class="badge bg-warning text-dark fs-6">${reqItem.requested_points}</span></td>
                                    <td><a href="/storage/${reqItem.receipt_file}" target="_blank" class="btn btn-sm btn-outline-info rounded-pill px-3">📄 عرض الإيصال</a></td>
                                    <td>
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button class="btn btn-sm btn-success px-3 rounded-pill fw-bold" onclick="verifyRechargeRequest(${reqItem.id}, 'approve')">✅ اعتماد</button>
                                            <button class="btn btn-sm btn-danger px-3 rounded-pill fw-bold" onclick="verifyRechargeRequest(${reqItem.id}, 'reject')">❌ رفض</button>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        } catch (innerException) {
                            console.error(innerException);
                        }
                    });
                }
            } catch (exception) { 
                console.error("خطأ في تحميل الطلبات", exception); 
            }
        }

    // دالة معالجة قرار الموظف (اعتماد أو رفض الطلب)
        async function verifyRechargeRequest(requestIdValue, actionType) {
            try {
                let rejectionReasonValue = '';

                //  إذا كان الإجراء هو الرفض، نطلب من الموظف إدخال السبب
                if (actionType === 'reject') {
                    const { value: textValue, isConfirmed: isRejectConfirmed } = await Swal.fire({
                        title: 'رفض طلب الشحن',
                        input: 'textarea',
                        inputLabel: 'سبب الرفض (سيتم إرساله للسائق كإشعار)',
                        inputPlaceholder: 'أدخل سبب الرفض هنا (مثال: الصورة غير واضحة، المبلغ غير صحيح)...',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'تأكيد الرفض',
                        cancelButtonText: 'إلغاء',
                        inputValidator: (inputValue) => {
                            try {
                                if (!inputValue || inputValue.trim() === '') {
                                    return 'يجب إدخال سبب الرفض لإشعار السائق!';
                                }
                            } catch (innerException) {
                                console.error(innerException);
                            }
                        }
                    });

                    if (!isRejectConfirmed) return; // خروج إذا ضغط الموظف على إلغاء
                    rejectionReasonValue = textValue;
                } else {
                    // نافذة تأكيد الاعتماد
                    const { isConfirmed: isApproveConfirmed } = await Swal.fire({
                        title: 'اعتماد الشحن',
                        text: 'هل أنت متأكد من صحة الإيصال وإضافة النقاط لمحفظة السائق؟',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'نعم، اعتماد وإضافة النقاط',
                        cancelButtonText: 'تراجع'
                    });
                    
                    if (!isApproveConfirmed) return;
                }

                // عرض نافذة التحميل
                Swal.fire({
                    title: 'جاري المعالجة...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        try {
                            Swal.showLoading();
                        } catch (innerException) {
                            console.error(innerException);
                        }
                    }
                });

                // إرسال القرار للباك إند
                const response = await fetch('/api/recharges/verify', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({
                        requestId: requestIdValue,
                        action: actionType,
                        rejectionReason: rejectionReasonValue
                    })
                });

                const resultData = await response.json();

                if (response.ok && resultData.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'تمت العملية',
                        text: actionType === 'approve' ? 'تم اعتماد الشحن وإضافة النقاط بنجاح.' : 'تم رفض الطلب وإرسال الإشعار للسائق.',
                        confirmButtonColor: '#2c3e50'
                    });
                    
                    loadPendingRequests(); // إعادة تحميل الجدول لإخفاء الطلب المعالج
                } else {
                    throw new Error(resultData.message || 'حدث خطأ أثناء معالجة الطلب.');
                }

            } catch (exception) {
                Swal.fire('خطأ', exception.message, 'error');
                console.error(exception);
            }
        }

    // عرض نافذة منبثقة لأخذ سبب الرفض الإلزامي من الموظف لتضمينه في إشعار السائق
    function rejectRequest(requestId) {
        try {
            Swal.fire({
                title: 'سبب الرفض',
                input: 'text',
                inputPlaceholder: 'أدخل سبب رفض إيصال التحويل...',
                showCancelButton: true,
                confirmButtonText: 'تأكيد الرفض',
                cancelButtonText: 'إلغاء',
                inputValidator: (value) => {
                    try {
                        if (!value) {
                            return 'يجب كتابة سبب الرفض لإعلام السائق!';
                        }
                    } catch (innerException) {
                        console.error(innerException);
                    }
                }
            }).then((result) => {
                try {
                    if (result.isConfirmed) {
                        verifyRequest(requestId, 'reject', result.value);
                    }
                } catch (innerException) {
                    console.error(innerException);
                }
            });
        } catch (exception) {
            console.error("خطأ في نافذة الرفض", exception);
        }
    }
</script>
</body>
</html>