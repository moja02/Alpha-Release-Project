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
        /* تنسيق القائمة الجانبية اليمنى */
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
        /* تنسيق منطقة المحتوى الرئيسي لتترك مساحة للقائمة الجانبية */
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
                🏠 نظرة عامة
            </a>
            <a class="nav-link" onclick="switchTab('profileTab', this)">
                ⚙️ البيانات الشخصية
            </a>
            <a class="nav-link" onclick="switchTab('createUserTab', this)">
                👤 إضافة مستخدم (سائق)
            </a>
            <a class="nav-link" onclick="switchTab('verifyVehicleTab', this)">
                🚘 التحقق من السيارات
            </a>
            <a class="nav-link" onclick="switchTab('rechargeWalletTab', this)">
                💳 شحن المحافظ
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
            <div class="card" style="max-width: 600px;">
                <div class="card-header text-dark">
                    ⚙️ إعدادات الحساب والبيانات الشخصية
                </div>
                <div class="card-body">
                    <div id="profileAlert" class="alert d-none" role="alert"></div>
                    <form id="profileForm">
                        <div class="mb-3">
                            <label class="form-label">الاسم الكامل (غير قابل للتعديل)</label>
                            <input type="text" class="form-control bg-light" id="profileNameDisplay" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">رقم الهاتف</label>
                            <input type="text" class="form-control" id="profilePhoneInput" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">رقم الحساب المصرفي</label>
                            <input type="text" class="form-control" id="profileBankInput">
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="form-label">كلمة مرور جديدة (اتركها فارغة إذا كنت لا تريد التغيير)</label>
                            <input type="password" class="form-control" id="profilePasswordInput" placeholder="••••••••">
                        </div>
                        <button type="submit" class="btn btn-dark w-100" id="updateProfileBtn">حفظ التغييرات الجديدة</button>
                    </form>
                </div>
            </div>
        </section>

        <section id="createUserTab" class="content-section d-none">
            <div class="card" style="max-width: 700px;">
                <div class="card-header text-primary">
                    👤 تسجيل مستخدم جديد (سائق)
                </div>
                <div class="card-body">
                    <div id="createUserAlert" class="alert d-none" role="alert"></div>
                    <form id="createUserForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">الاسم الكامل</label>
                                <input type="text" class="form-control" id="userNameInput" required placeholder="أدخل اسم السائق">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">البريد الإلكتروني</label>
                                <input type="email" class="form-control" id="userEmailInput" required placeholder="name@example.com">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">رقم الهاتف</label>
                                <input type="text" class="form-control" id="userPhoneInput" required placeholder="مثال: 0912345678">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">رقم لوحة السيارة</label>
                                <input type="text" class="form-control" id="userPlateInput" placeholder="مثال: 5-12345" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mt-2" id="createUserBtn">تسجيل السائق وتوليد رمز الدخول</button>
                    </form>
                </div>
            </div>
        </section>

        <section id="verifyVehicleTab" class="content-section d-none">
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
        </section>

        <section id="rechargeWalletTab" class="content-section d-none">
            <div class="card" style="max-width: 550px;">
                <div class="card-header text-warning bg-transparent border-bottom-0 pt-3">
                    💳 شحن محافظ المستخدمين المباشر
                </div>
                <div class="card-body">
                    <div id="rechargeWalletAlert" class="alert d-none" role="alert"></div>
                    <form id="rechargeWalletForm">
                        <div class="mb-3">
                            <label class="form-label text-secondary fw-bold">معرف الحساب الأساسي (Account ID)</label>
                            <input type="number" class="form-control" id="targetUserIdInput" required placeholder="أدخل ID المستخدم (مثال: 1)">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-secondary fw-bold d-block">النقاط المقترحة الجاهزة</label>
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm fast-amount-btn" onclick="setRechargeAmount(5)">5 نقاط</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm fast-amount-btn" onclick="setRechargeAmount(10)">10 نقاط</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm fast-amount-btn" onclick="setRechargeAmount(15)">15 نقطة</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm fast-amount-btn" onclick="setRechargeAmount(20)">20 نقطة</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm fast-amount-btn" onclick="setRechargeAmount(25)">25 نقطة</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm fast-amount-btn" onclick="setRechargeAmount(50)">50 نقطة</button>
                            </div>
                            
                            <label class="form-label text-secondary fw-bold mt-2">أو إدخال النقاط مخصصة (الحد الأدنى 3)</label>
                            <input type="number" class="form-control" id="pointsAmountInput" min="3" placeholder="أدخل عدد النقاط يدوياً" required>
                        </div>

                        <button type="submit" class="btn btn-warning w-100 fw-bold mt-2" id="rechargeWalletBtn">تنفيذ الشحن وإضافة الرصيد</button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <script>
        //  قراءة بيانات الجلسة عند تحميل الصفحة لعرض اسم الموظف
        document.addEventListener('DOMContentLoaded', function() {
            // تطبيق قاعدة try/catch الإلزامية لجميع الدوال
            try {
                const userDataString = localStorage.getItem('userData');
                if (userDataString) {
                    const userDataObject = JSON.parse(userDataString);
                    document.getElementById('employeeNameDisplay').innerText = 'مرحباً، ' + userDataObject.name;
                } else {
                    window.location.href = '/login';
                }
            } catch (exception) {
                console.error("خطأ في تهيئة الصفحة", exception);
            }
        });

        // التنقل بين الأقسام وإظهار القسم المطلوب فقط بناءً على اختيار القائمة
        
        function switchTab(sectionId, clickedLink) {
            
            try {
                // 1. إخفاء كافة الأقسام النشطة في واجهة المستخدم
                const allSections = document.querySelectorAll('.content-section');
                allSections.forEach(section => section.classList.add('d-none'));

                // 2. إظهار القسم المستهدف (Section) بناءً على المعرف الممرر
                const targetSection = document.getElementById(sectionId);
                if (targetSection) {
                    targetSection.classList.remove('d-none');
                }

                // 3. إزالة فئة التفعيل (active) من كافة روابط القائمة الجانبية
                const allLinks = document.querySelectorAll('.sidebar .nav-link');
                allLinks.forEach(link => link.classList.remove('active'));

                // 4. تفعيل الرابط الذي تم الضغط عليه وتحديث عنوان الصفحة العلوي
                clickedLink.classList.add('active');
                const titleDisplay = document.getElementById('pageTitleDisplay');
                titleDisplay.innerText = clickedLink.innerText.trim();

                // ---  فحص إذا كان القسم المختار هو قسم البيانات الشخصية ---
                if (sectionId === 'profileTab') {
                    //  استدعاء دالة تحميل البيانات الشخصية لتعبئة الحقول
                    loadProfileData();
                }

            } catch (exception) {
                // تسجيل الخطأ في حال حدوث خلل أثناء التبديل بين الأقسام
                console.error("خطأ أثناء التبديل بين الأقسام", exception);
            }
        }

        /**
         * دالة مساعدة لجلب بيانات الموظف من الـ LocalStorage وتوزيعها على الحقول
         */
        function loadProfileData() {
            try {
                
                const userDataString = localStorage.getItem('userData');
                
                if (userDataString) {
                    const userData = JSON.parse(userDataString);
                    
                    // تعبئة حقول نموذج الملف الشخصي بالبيانات الحالية
                    document.getElementById('profileNameDisplay').value = userData.name;
                    document.getElementById('profilePhoneInput').value = userData.phone;
                    
                    // التحقق من وجود بيانات الملف الشخصي (Profile) وتعبئة الحساب المصرفي
                    if (userData.profile && userData.profile.bank_account_number) {
                        document.getElementById('profileBankInput').value = userData.profile.bank_account_number;
                    }
                }
            } catch (exception) {
                console.error("خطأ أثناء تحميل بيانات الملف الشخصي", exception);
            }
        }

        //  دالة تسجيل الخروج ومسح الجلسة
        function logoutEmployee() {

            try {
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
                    if (result.isConfirmed) {
                        localStorage.clear();
                        window.location.href = '/login';
                    }
                });
            } catch (exception) {
                console.error("خطأ أثناء تسجيل الخروج", exception);
            }
        }

        // --- معالجة إرسال نموذج إضافة مستخدم (سائق) ---
        document.getElementById('createUserForm').addEventListener('submit', async function(event) {
            event.preventDefault();
            
            // تطبيق شروط التكويد: استخدام CamelCase لأسماء المتغيرات (إزالة متغير الباسوورد)
            const userNameValue = document.getElementById('userNameInput').value;
            const userEmailValue = document.getElementById('userEmailInput').value;
            const userPhoneValue = document.getElementById('userPhoneInput').value;
            const userPlateValue = document.getElementById('userPlateInput').value;
            const submitButton = document.getElementById('createUserBtn');

            submitButton.disabled = true;

            try {
                // تعليق مضمن: إرسال البيانات بدون كلمة مرور ليقوم الخادم بتوليدها برمجياً
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
                    // إشعار نجاح يبلغ الموظف أن الرمز أرسل للسائق مباشرة
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
                    document.getElementById('createUserForm').reset();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطأ في التسجيل',
                        text: responseData.message || 'تأكد من عدم تكرار البريد الإلكتروني.',
                        confirmButtonColor: '#d33'
                    });
                }
            } catch (networkError) {
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ في الاتصال',
                    text: 'تعذر الوصول للخادم المحلي.',
                    confirmButtonColor: '#d33'
                });
            } finally {
                submitButton.disabled = false;
            }
        });

        // --- معالجة نموذج التحقق الميداني من السيارات ---
        document.getElementById('verifyVehicleForm').addEventListener('submit', async function(event) {
            event.preventDefault();
            
            const verifyPlateValue = document.getElementById('verifyPlateInput').value;
            const alertContainer = document.getElementById('verifyVehicleAlert');
            alertContainer.classList.add('d-none');

            try {
                //  محاكاة فحص اللوحة بانتظار ربطها بوحدة الحجوزات
                alertContainer.className = 'alert alert-info';
                alertContainer.innerText = 'جاري التحقق من اللوحة (' + verifyPlateValue + ') ومطابقتها ميدانياً...';
                alertContainer.classList.remove('d-none');
            } catch (exception) {
                console.error(exception);
            }
        });

        // --- معالجة نموذج شحن المحافظ ---
        document.getElementById('rechargeWalletForm').addEventListener('submit', async function(event) {
            event.preventDefault();
            
            const targetUserValue = document.getElementById('targetUserIdInput').value;
            const pointsValue = document.getElementById('pointsAmountInput').value;
            const alertContainer = document.getElementById('rechargeWalletAlert');
            alertContainer.classList.add('d-none');

            try {
                //  محاكاة أمر الشحن المباشر
                alertContainer.className = 'alert alert-success';
                alertContainer.innerText = 'تم تجهيز أمر شحن (' + pointsValue + ' نقاط) للمستخدم رقم (' + targetUserValue + ') بنجاح!';
                alertContainer.classList.remove('d-none');
            } catch (exception) {
                console.error(exception);
            }
        });

        // تعليق مضمن: دالة مساعدة لتعبئة حقل النقاط تلقائياً عند الضغط على الخيارات الجاهزة
        function setRechargeAmount(selectedAmount) {
            // الالتزام بقاعدة try/catch الإلزامية لجميع الدوال
            try {
                // استخدام متغيرات CamelCase
                const pointsInputElement = document.getElementById('pointsAmountInput');
                pointsInputElement.value = selectedAmount;

                // إزالة التنسيق النشط من كافة الأزرار السريعة
                const allFastButtons = document.querySelectorAll('.fast-amount-btn');
                allFastButtons.forEach(button => {
                    button.classList.remove('btn-secondary', 'text-white');
                    button.classList.add('btn-outline-secondary');
                });

                // تمييز الزر المضغوط حالياً لإعطاء تغذية بصرية للموظف
                const clickedButton = event.target;
                clickedButton.classList.remove('btn-outline-secondary');
                clickedButton.classList.add('btn-secondary', 'text-white');

            } catch (exception) {
                console.error("خطأ في تحديث قيمة الشحن السريع", exception);
            }
        }
        document.getElementById('profileForm').addEventListener('submit', async function(event) {
            event.preventDefault();
            
            const alertDiv = document.getElementById('profileAlert');
            const updateBtn = document.getElementById('updateProfileBtn');
            const userData = JSON.parse(localStorage.getItem('userData'));

            updateBtn.disabled = true;

            try {
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
                    // إخفاء الـ alert العادي واستخدام SweetAlert بدلاً منه
                    alertDiv.classList.add('d-none');
                    
                    // تحديث البيانات في LocalStorage
                    userData.phone = result.updatedData.phone;
                    if(userData.profile) userData.profile.bank_account_number = result.updatedData.bankAccountNumber;
                    localStorage.setItem('userData', JSON.stringify(userData));

                    // إشعار نجاح منبثق
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
                alertDiv.classList.add('d-none');
                Swal.fire({
                    icon: 'error',
                    title: 'فشل التحديث',
                    text: error.message,
                    confirmButtonColor: '#d33'
                });
            }
        });
    </script>
</body>
</html>