<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpotLy - لوحة تحكم المدير</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
            <a class="nav-link" onclick="switchTab('parkingsTab', this)">
                📍 الساحات المدارة
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
    </script>
</body>
</html>
