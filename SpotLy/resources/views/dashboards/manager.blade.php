<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpotLy - لوحة تحكم المدير</title>
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
            <a class="nav-link" onclick="switchTab('profileTab', this)">
                ⚙️ البيانات الشخصية
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

                        </ul>
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
        } catch (exception) {
            console.error("خطأ أثناء التبديل بين التبويبات", exception);
        }
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
    </script>
</body>
</html>
