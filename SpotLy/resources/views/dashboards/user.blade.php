<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpotLy - بوابة السائق التفاعلية</title>
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
            background-color: #1e293b;
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
            color: #94a3b8;
            padding: 12px 20px;
            margin: 4px 10px;
            border-radius: 8px;
            transition: all 0.3s;
            cursor: pointer;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background-color: #334155;
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
        .spot-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="text-center mb-4">
            <h4 class="text-white fw-bold">🚗 SpotLy</h4>
            <span class="badge bg-primary text-white px-3 py-1 rounded-pill">بوابة السائق</span>
        </div>
        <hr class="border-secondary border-opacity-50 mx-3">
        <nav class="nav flex-column">
            <a class="nav-link active" onclick="switchTab('overviewTab', this)">🏠 نظرة عامة</a>
            <a class="nav-link" onclick="switchTab('bookingTab', this)">🚗 حجز موقف تفاعلي</a>
            <a class="nav-link" onclick="switchTab('walletTab', this)">💳 المحفظة وطلب الشحن</a>
            <a class="nav-link" onclick="switchTab('profileTab', this)">⚙️ الإعدادات الشخصية</a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="dashboard-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-secondary fw-bold" id="pageTitleDisplay">🏠 نظرة عامة</h5>
            <div>
                <span id="userNameDisplay" class="me-3 fw-bold text-dark"></span>
                <button onclick="logoutUser()" class="btn btn-sm btn-outline-danger px-3 rounded-pill">تسجيل الخروج</button>
            </div>
        </header>

        <section id="overviewTab" class="content-section">
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card p-3 text-center border-0 shadow-sm rounded-4 border-start border-success border-4">
                        <h6 class="text-muted mb-1">حالة الحساب</h6>
                        <p class="fs-5 fw-bold mb-0 text-success" id="statusDisplay">نشط</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3 text-center border-0 shadow-sm rounded-4 border-start border-warning border-4 position-relative">
                        <h6 class="text-muted mb-1">رصيد المحفظة</h6>
                        <p class="fs-5 fw-bold mb-0 text-warning">
                            <span id="balanceDisplay">0</span> نقطة
                            <button onclick="fetchWalletBalance()" class="btn btn-sm btn-link text-warning p-0 ms-2" title="تحديث الرصيد">
                                🔄
                            </button>
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3 text-center border-0 shadow-sm rounded-4 border-start border-danger border-4">
                        <h6 class="text-muted mb-1">مخالفات عدم الحضور</h6>
                        <p class="fs-5 fw-bold mb-0 text-danger"><span id="fakeBookingDisplay">0</span> / 3</p>
                    </div>
                </div>
            </div>

            <div id="quickActiveTicketAlert" class="alert alert-primary border-0 shadow-sm rounded-4 d-none mb-4">
                <div class="d-flex align-items-center">
                    <span class="fs-3 me-3">🎟️</span>
                    <div>
                        <h6 class="fw-bold mb-1">لديك حجز نشط حالياً!</h6>
                        <p class="mb-0 small">يمكنك عرض تفاصيل التذكرة من تبويب "حجز موقف".</p>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 fw-bold">🏷️ معلومات المركبة المسجلة</div>
                <div class="card-body">
                    <p class="mb-0 fs-5 text-dark">رقم اللوحة التشغيلية: <span id="plateDisplay" class="text-primary fw-bold ms-2">--</span></p>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mt-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold">🔔 سجل الإشعارات والتنبيهات الأخير</span>
                    <button onclick="loadDashboardNotifications()" class="btn btn-sm btn-link text-decoration-none p-0">تحديث السجل 🔄</button>
                </div>
                <div class="card-body p-0">
                    <div id="dashboardNotificationLog" class="list-group list-group-flush" style="max-height: 350px; overflow-y: auto;">
                        <div class="text-center py-5 text-muted">جاري جلب آخر التنبيهات...</div>
                    </div>
                </div>
            </div>
        </section>

        <section id="bookingTab" class="content-section d-none">
            
            <div id="activeTicketSection" class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 d-none" style="display: none;">
                <div class="card-body p-4 bg-white">
                    <div class="row align-items-center text-center text-md-start g-3">
                        <div class="col-md-4 border-end-md">
                            <span class="text-muted d-block mb-1">رقم الموقف المحجوز</span>
                            <h2 class="fw-bold text-dark display-6 mb-0" id="ticketSpotNumber">--</h2>
                        </div>
                        <div class="col-md-4 border-end-md">
                            <span class="text-muted d-block mb-1">طريقة الدفع المعتمدة</span>
                            <h4 class="fw-bold text-primary mb-0" id="ticketPaymentMethod">--</h4>
                        </div>
                        <div class="col-md-4 text-center">
                            <span class="text-muted d-block mb-2">حالة التذكرة</span>
                            <span class="badge bg-success rounded-pill px-4 py-2 fs-6 pb-1 animate-pulse">نشط وقيد الانتظار</span>
                        </div>
                    </div>
                    <hr class="my-4 border-light">
                    <div class="d-flex flex-wrap gap-2 justify-content-center">
                        <button onclick="requestChangeSpot()" class="btn btn-outline-primary rounded-pill px-4">🔄 تبديل الساحة</button>
                        <button onclick="cancelCurrentBooking()" class="btn btn-outline-danger rounded-pill px-4">❌ إلغاء الحجز</button>
                    </div>
                </div>
            </div>

            <div id="bookingSpotsGridSection" class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-gradient bg-primary text-white p-4 border-0 d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="fw-bold mb-1">📍 خريطة المواقف المباشرة</h5>
                        <p class="fs-6 mb-0 text-white text-opacity-75">اضغط على الموقف المتاح باللون الأخضر لإتمام عملية الحجز</p>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="badge bg-success px-3 py-2 rounded-pill">متاح</span>
                        <span class="badge bg-danger px-3 py-2 rounded-pill">محجوز</span>
                    </div>
                </div>

                <div class="card-body p-4 bg-light">
                    <div class="row g-3" id="spotsGridContainer">
                        <div class="text-center py-5 text-muted">جاري تحميل خريطة المواقف المباشرة...</div>
                    </div>
                </div>
            </div>
        </section>

        <section id="walletTab" class="content-section d-none">
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                        <div class="card-header bg-gradient bg-warning text-dark p-4 border-0 d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="fw-bold mb-1">📄 طلب شحن الرصيد</h5>
                            </div>
                            <span class="fs-2">💳</span>
                        </div>
                        <div class="card-body p-4">
                            <form id="rechargeRequestForm">
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-secondary">الساحة المستهدفة للشحن</label>
                                    <select class="form-select form-select-lg shadow-none" id="targetParkingSelect" required>
                                        <option value="" selected disabled>اختر الساحة التي حولت إليها...</option>
                                        </select>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-secondary">عدد النقاط المطلوب</label>
                                    <input type="number" class="form-control form-control-lg shadow-none" id="rechargeAmountInput" min="5" placeholder="الحد الأدنى 5 نقاط" required>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-secondary">صورة إيصال التحويل</label>
                                    <input type="file" class="form-control form-control-lg shadow-none" id="receiptFileInput" accept="image/*" required>
                                </div>
                                <button type="submit" class="btn btn-warning btn-lg w-100 fw-bold shadow-sm rounded-3 py-3" id="submitRechargeBtn">
                                    إرسال الطلب للمراجعة 🚀
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                        <div class="card-header bg-gradient bg-info text-white p-4 border-0 d-flex align-items-center justify-content-between">
                            <h5 class="fw-bold mb-0">📜 سجل طلبات الشحن السابقة</h5>
                            <button onclick="loadUserRechargeHistory()" class="btn btn-sm btn-light rounded-pill px-3">تحديث السجل</button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle text-center">
                                    <thead class="table-light">
                                        <tr>
                                            <th>النقاط</th>
                                            <th>تاريخ الطلب</th>
                                            <th>الحالة</th>
                                        </tr>
                                    </thead>
                                    <tbody id="rechargeHistoryTableBody">
                                        <tr>
                                            <td colspan="3" class="text-muted py-5">جاري تحميل السجل...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
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
                                <h5 class="fw-bold mb-1">⚙️ تحديث البيانات الشخصية</h5>
                                <p class="fs-6 mb-0 text-white text-opacity-75">إدارة بيانات الاتصال وتأمين حسابك</p>
                            </div>
                            <span class="fs-1">🔒</span>
                        </div>
                        <div class="card-body p-4">
                            <form id="profileForm">
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-secondary">الاسم الكامل</label>
                                    <input type="text" class="form-control form-control-lg bg-light text-muted" id="profileNameDisplay" readonly>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-secondary">رقم الهاتف</label>
                                    <input type="text" class="form-control form-control-lg shadow-none" id="profilePhoneInput" required>
                                </div>
                                <hr class="my-4 border-secondary border-opacity-25">
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-secondary">كلمة مرور جديدة (اختياري)</label>
                                    <input type="password" class="form-control form-control-lg shadow-none" id="profilePasswordInput" placeholder="•••••••• (اتركها فارغة إذا لم ترغب بالتغيير)">
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm rounded-3 py-3" id="updateProfileBtn">
                                    حفظ التعديلات الشخصية 💾
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        let currentUserData = null;

        // ---  تهيئة الصفحة وقراءة بيانات الجلسة ---
        document.addEventListener('DOMContentLoaded', function() {
            
            try {
                const userDataString = localStorage.getItem('userData');
                if (userDataString) {
                    currentUserData = JSON.parse(userDataString);
                    initializeDriverDashboard();
                } else {
                    window.location.href = '/login';
                }
            } catch (exception) {
                console.error("خطأ في تهيئة لوحة السائق", exception);
            }
        });

        // ---  توزيع بيانات السائق على عناصر الواجهة ---
        function initializeDriverDashboard() {
            try {
                document.getElementById('userNameDisplay').innerText = 'السائق: ' + currentUserData.name;
                
                if (currentUserData.profile) {
                    document.getElementById('fakeBookingDisplay').innerText = currentUserData.profile.fake_booking_count || 0;
                    document.getElementById('plateDisplay').innerText = currentUserData.profile.plate_number || '--';
                    
                    const statusVal = currentUserData.profile.status;
                    const statusElem = document.getElementById('statusDisplay');
                    statusElem.innerText = statusVal === 'active' ? 'نشط' : 'محظور';
                    statusElem.className = statusVal === 'active' ? 'fs-5 fw-bold mb-0 text-success' : 'fs-5 fw-bold mb-0 text-danger';
                }

                //  جلب الرصيد وفحص الحجوزات فور الدخول للنظام
                fetchWalletBalance();
                checkActiveBookingForOverview();
                loadDashboardNotifications(); // FR4: عرض السجل فور الدخول
            } catch (exception) {
                console.error(exception);
            }
        }

        // ---  جلب رصيد المحفظة الرقمية ---
        async function fetchWalletBalance() {
            try {
                // التأكد من وجود بيانات المستخدم وصلاحية المعرف
                if (!currentUserData || !currentUserData.accountId) return;

                //  طلب الرصيد من مسار API المحفظة لضمان جلب البيانات من جدول wallets
                const apiResponse = await fetch('/api/wallet/balance?userId=' + currentUserData.accountId);
                const resultData = await apiResponse.json();

                if (apiResponse.ok && resultData.status === 'success') {
                    const balanceElement = document.getElementById('balanceDisplay');
                    
                    // تأثير بصري بسيط عند تحديث الرقم
                    balanceElement.style.opacity = '0.5';
                    
                    setTimeout(() => {
                        try {
                            balanceElement.innerText = resultData.balance;
                            balanceElement.style.opacity = '1';
                        } catch (innerException) {
                            console.error(innerException);
                        }
                    }, 300);
                }
            } catch (exception) {
                console.error("خطأ أثناء تحديث رصيد المحفظة", exception);
            }
        }

        // --- دالة تحديث إحصائيات السائق (المخالفات وحالة الحساب) ---
        async function refreshDriverStats() {
            try {
                if (!currentUserData || !currentUserData.accountId) return;

                const response = await fetch('/api/accounts/stats?userId=' + currentUserData.accountId);
                const data = await response.json();

                if (response.ok && data.status === 'success') {
                    // 1. تحديث رقم المخالفات في الواجهة
                    const fakeBookingElem = document.getElementById('fakeBookingDisplay');
                    if (fakeBookingElem && fakeBookingElem.innerText != data.fake_booking_count) {
                        fakeBookingElem.innerText = data.fake_booking_count;
                        // تأثير بصري بسيط عند زيادة المخالفة
                        fakeBookingElem.parentElement.classList.add('animate-pulse');
                        setTimeout(() => fakeBookingElem.parentElement.classList.remove('animate-pulse'), 1000);
                    }

                    // 2. تحديث حالة الحساب (نشط / محظور)
                    const statusElem = document.getElementById('statusDisplay');
                    if (statusElem) {
                        statusElem.innerText = data.account_status === 'active' ? 'نشط' : 'محظور';
                        statusElem.className = data.account_status === 'active' ? 'fs-5 fw-bold mb-0 text-success' : 'fs-5 fw-bold mb-0 text-danger';
                    }

                    // 3. تحديث التخزين المحلي (localStorage) بيش تقعد البيانات متزامنة
                    if(currentUserData.profile) {
                        currentUserData.profile.fake_booking_count = data.fake_booking_count;
                        currentUserData.profile.status = data.account_status;
                        localStorage.setItem('userData', JSON.stringify(currentUserData));
                    }

                    // 4. طرد السائق فوراً إذا تم حظره!
                    if (data.account_status === 'blocked') {
                        Swal.fire({
                            title: 'تم حظر الحساب!',
                            text: 'لقد تجاوزت الحد الأقصى للمخالفات (3 مرات). سيتم تسجيل خروجك الآن.',
                            icon: 'error',
                            confirmButtonText: 'حسناً',
                            allowOutsideClick: false
                        }).then(() => {
                            localStorage.clear();
                            window.location.href = '/login';
                        });
                    }
                }
            } catch (exception) {
                console.error("خطأ في تحديث إحصائيات السائق", exception);
            }
        }

        // دالة جلب السجل
        async function loadUserRechargeHistory() {
            try {
                const response = await fetch('/api/recharges/user-requests?userId=' + currentUserData.accountId);
                const resultData = await response.json();
                const tableBodyElement = document.getElementById('rechargeHistoryTableBody');

                if (tableBodyElement && response.ok && resultData.status === 'success') {
                    tableBodyElement.innerHTML = '';

                    if (resultData.data.length > 0) {
                        resultData.data.forEach(requestItem => {
                            try {
                                let statusBadgeClass = 'bg-warning text-dark';
                                let statusLabel = 'قيد المراجعة';

                                if (requestItem.status === 'Approved') {
                                    statusBadgeClass = 'bg-success text-white';
                                    statusLabel = 'تم الاعتماد';
                                } else if (requestItem.status === 'Rejected') {
                                    statusBadgeClass = 'bg-danger text-white';
                                    statusLabel = 'مرفوض';
                                }

                                const requestDateValue = new Date(requestItem.created_at).toLocaleDateString('ar-LY', {
                                    year: 'numeric', month: 'short', day: 'numeric'
                                });

                                tableBodyElement.innerHTML += `
                                    <tr>
                                        <td class="fw-bold text-dark">${requestItem.requested_points}</td>
                                        <td class="text-muted">${requestDateValue}</td>
                                        <td><span class="badge ${statusBadgeClass} rounded-pill px-3 py-1">${statusLabel}</span></td>
                                    </tr>
                                `;
                            } catch (innerException) {
                                console.error(innerException);
                            }
                        });
                    } else {
                        tableBodyElement.innerHTML = '<tr><td colspan="3" class="text-muted py-5">لا توجد طلبات شحن سابقة في سجلك.</td></tr>';
                    }
                }
            } catch (exception) {
                console.error(exception);
            }
        }
        // دالة جلب كافة المواقف وتعبئة قائمة الشحن المنسدلة ديناميكياً
        async function loadParkingOptionsForRecharge() {
            try {
                // استدعاء واجهة البرمجيات لجلب الساحات المربوطة بالموظفين
                const response = await fetch('/api/parkings/spots');
                const resultData = await response.json();
                const selectElement = document.getElementById('targetParkingSelect');
                
                if (selectElement && response.ok && resultData.status === 'success') {
                    // تفريغ القائمة وتجهيزها للاختيار
                    selectElement.innerHTML = '<option value="" selected disabled>اختر الساحة التي حولت لرقم حسابها...</option>';
                    
                    //  تكرار البيانات الواردة من الباك إند وتوليد خيارات القائمة
                    resultData.data.forEach(parkingItem => {
                        try {
                            const optionElement = document.createElement('option');
                            optionElement.value = parkingItem.id;
                            // عرض اسم الساحة وموقعها لتسهيل التعرف عليها من قبل السائق
                            optionElement.textContent = `${parkingItem.name} (${parkingItem.location_park})`;
                            selectElement.appendChild(optionElement);
                        } catch (innerException) {
                            console.error(innerException);
                        }
                    });
                }
            } catch (exception) {
                console.error("خطأ في تحميل قائمة الساحات للشحن", exception);
            }
        }
        // --- دالة فحص التذكرة النشطة (لإظهار التنبيه في الصفحة الرئيسية) ---
        async function checkActiveBookingForOverview() {
            try {
                const response = await fetch('/api/bookings/active?userId=' + currentUserData.accountId);
                const result = await response.json();
                const alertElement = document.getElementById('quickActiveTicketAlert');

                if (response.ok && result.hasActiveBooking) {
                    alertElement.classList.remove('d-none');
                } else {
                    alertElement.classList.add('d-none');
                }
            } catch (exception) {
                console.error(exception);
            }
        }
        // ---  التبديل الديناميكي بين التبويبات ---
        function switchTab(sectionIdValue, clickedLinkElement) {
            try {
                const allSections = document.querySelectorAll('.content-section');
                allSections.forEach(sectionItem => {
                    try {
                        sectionItem.classList.add('d-none');
                    } catch (innerException) {
                        console.error(innerException);
                    }
                });

                const targetSection = document.getElementById(sectionIdValue);
                if (targetSection) {
                    targetSection.classList.remove('d-none');
                }

                const allNavLinks = document.querySelectorAll('.sidebar .nav-link');
                allNavLinks.forEach(linkItem => {
                    try {
                        linkItem.classList.remove('active');
                    } catch (innerException) {
                        console.error(innerException);
                    }
                });

                clickedLinkElement.classList.add('active');
                document.getElementById('pageTitleDisplay').innerText = clickedLinkElement.innerText.trim();

                //  تنفيذ تحديثات البيانات بناءً على القسم النشط
                if (sectionIdValue === 'overviewTab') {
                    fetchWalletBalance(); // تحديث الرصيد فور العودة للرئيسية
                    checkActiveBookingForOverview(); // فحص الحجوزات
                    refreshDriverStats(); // تحديث المخالفات عند العودة للرئيسية
                    loadDashboardNotifications(); // تحديث السجل عند العودة للرئيسية

                } else if (sectionIdValue === 'profileTab') {
                    loadProfileData();
                } else if (sectionIdValue === 'bookingTab') {
                    checkActiveTicketAndLoadGrid();
                } else if (sectionIdValue === 'walletTab') {
                    loadUserRechargeHistory();
                    loadParkingOptionsForRecharge();
                }

            } catch (exception) {
                console.error("خطأ في التبديل وتحديث البيانات", exception);
            }
        }

        // معالجة رفع إيصال التحويل البنكي للشحن مع تضمين معرف الساحة
        document.getElementById('rechargeRequestForm').addEventListener('submit', async function(event) {
            try {
                event.preventDefault();
                
                // تعليق مضمن: قراءة معرف الساحة، المبلغ، والملف من الواجهة
                const parkingIdValue = document.getElementById('targetParkingSelect').value;
                const amountInputValue = document.getElementById('rechargeAmountInput').value;
                const fileInputValue = document.getElementById('receiptFileInput').files[0];
                const submitButtonElement = document.getElementById('submitRechargeBtn');

                // تحقق إضافي لمنع الإرسال إذا نسي السائق اختيار الساحة
                if (!parkingIdValue) {
                    Swal.fire('تنبيه هام', 'يرجى اختيار الساحة المستهدفة من القائمة قبل الإرسال.', 'warning');
                    return;
                }

                submitButtonElement.disabled = true;

                try {
                    // تعليق مضمن: بناء حزمة البيانات (FormData) لتشمل parkingId الإلزامي
                    const formDataPayload = new FormData();
                    formDataPayload.append('userId', currentUserData.accountId);
                    formDataPayload.append('parkingId', parkingIdValue); 
                    formDataPayload.append('amount', amountInputValue);
                    formDataPayload.append('receipt', fileInputValue);

                    Swal.fire({
                        title: 'جاري رفع الإيصال...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            try {
                                Swal.showLoading();
                            } catch (innerException) {
                                console.error(innerException);
                            }
                        }
                    });

                    // إرسال الطلب إلى الخادم
                    const response = await fetch('/api/recharges/request', {
                        method: 'POST',
                        headers: { 'Accept': 'application/json' },
                        body: formDataPayload
                    });

                    const resultData = await response.json();

                    if (response.ok) {
                        Swal.fire('تم الإرسال بنجاح', 'تم توجيه طلبك للموظف المسؤول عن الساحة.', 'success');
                        
                        // تصفير النموذج وتحديث جدول السجل فوراً
                        document.getElementById('rechargeRequestForm').reset();
                        loadUserRechargeHistory();
                    } else {
                        throw new Error(resultData.message || 'فشل رفع الإيصال.');
                    }
                } catch (exception) {
                    Swal.fire('خطأ', exception.message, 'error');
                } finally {
                    submitButtonElement.disabled = false;
                }
            } catch (exception) {
                console.error(exception);
            }
        });

        /*
        |--------------------------------------------------------------------------
        | النواة التشغيلية: دوال إدارة الحجز والمواقف التفاعلية (FR3)
        |--------------------------------------------------------------------------
        */
        // متغير عام لتخزين رقم الحجز النشط لكي نستخدمه في دوال الإلغاء والتبديل
        let activeBookingId = null;

        // ---  فحص التذكرة النشطة وتحميل شبكة المواقف ---
        async function checkActiveTicketAndLoadGrid() {
            // 1. الإخفاء الاستباقي (Pre-emptive Hide): نغلق التذكرة فوراً قبل أي شيء
            const activeTicketCard = document.getElementById('activeTicketSection');
            const gridMapCard = document.getElementById('bookingSpotsGridSection');

            if (activeTicketCard) {
                activeTicketCard.classList.add('d-none');
                activeTicketCard.style.setProperty('display', 'none', 'important');
            }

            try {
                // التأكد من وجود بيانات المستخدم لتجنب أخطاء توقف السكربت
                if (!currentUserData || !currentUserData.accountId) {
                    throw new Error("بيانات المستخدم غير مكتملة");
                }

                // 2. الاتصال بالباك إند
                const response = await fetch('/api/bookings/active?userId=' + currentUserData.accountId);
                const resultData = await response.json();

                // 3. اتخاذ القرار
                if (response.ok && resultData.status === 'success' && resultData.hasActiveBooking) {
                    const bookingRecord = resultData.bookingData;
                    activeBookingId = bookingRecord.id;
                    
                    document.getElementById('ticketSpotNumber').innerText = bookingRecord.parking_name || '--';
                    document.getElementById('ticketPaymentMethod').innerText = bookingRecord.type === 'initial' ? '⏱️ حجز مبدئي (مؤقت)' : '✅ حجز فعلي';

                    // إظهار التذكرة وإخفاء الخريطة لأن هناك حجز فعلي
                    if (activeTicketCard) {
                        activeTicketCard.classList.remove('d-none');
                        activeTicketCard.style.setProperty('display', 'block', 'important');
                    }
                    if (gridMapCard) {
                        gridMapCard.classList.add('d-none');
                        gridMapCard.style.setProperty('display', 'none', 'important');
                    }
                } else {
                    // لا يوجد حجز: نصفر المتغير ونظهر الخريطة (التذكرة مخفية مسبقاً في الخطوة 1)
                    activeBookingId = null;

                    if (gridMapCard) {
                        gridMapCard.classList.remove('d-none');
                        gridMapCard.style.setProperty('display', 'block', 'important');
                    }
                    loadLiveSpotsGrid(); // تحميل بيانات المواقف المتاحة
                }
            } catch (exception) {
                console.error("خطأ في فحص التذكرة النشطة:", exception);
                // في حالة حدوث أي خطأ برمجي، نعرض الخريطة كإجراء احتياطي (Fallback)
                if (gridMapCard) {
                    gridMapCard.classList.remove('d-none');
                    gridMapCard.style.setProperty('display', 'block', 'important');
                }
                loadLiveSpotsGrid();
            }
        }

        // ---  رسم خريطة ساحات الوقوف التفاعلية بناءً على السعة ---
        async function loadLiveSpotsGrid() {
            try {
                const response = await fetch('/api/parkings/spots');
                const resultData = await response.json();
                const gridContainerElement = document.getElementById('spotsGridContainer');

                if (gridContainerElement && response.ok && resultData.status === 'success') {
                    gridContainerElement.innerHTML = '';

                    resultData.data.forEach(parkingArea => {
                        try {
                            const isAreaAvailable = parkingArea.available_capacity > 0;
                            const areaBadgeClass = isAreaAvailable ? 'bg-success' : 'bg-danger';
                            const areaStatusLabel = isAreaAvailable ? 'متاح للحجز' : 'ممتلئ بالكامل';
                            const areaOpacityStyle = isAreaAvailable ? 'opacity: 1;' : 'opacity: 0.6; cursor: not-allowed;';
                            
                            // التأكد من وجود رقم حساب أو عرض رسالة تنبيه
                            const bankAccountDisplay = parkingArea.employee_bank_account || 'غير متوفر حالياً';

                            gridContainerElement.innerHTML += `
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="card spot-card text-center p-3 border-0 shadow-sm rounded-4 h-100" 
                                         style="${areaOpacityStyle} transition: all 0.3s; ${isAreaAvailable ? 'cursor: pointer;' : ''}"
                                         onclick="initiateSpotReservation(${parkingArea.id}, '${parkingArea.name}', ${parkingArea.available_capacity})">
                                        <div class="card-body p-3">
                                            <span class="display-6 d-block mb-3">${isAreaAvailable ? '🅿️' : '⛔'}</span>
                                            <h4 class="fw-bold text-dark mb-1">${parkingArea.name}</h4>
                                            <p class="text-muted small mb-3"><i class="me-1">📍</i> ${parkingArea.location_park}</p>
                                            
                                            <div class="alert alert-light border-0 py-2 mb-3 rounded-3" style="background-color: #f8fafc;">
                                                <small class="text-muted d-block mb-1">الحساب المصرفي للتحويل:</small>
                                                <span class="fw-bold text-primary" style="letter-spacing: 1px;">${bankAccountDisplay}</span>
                                            </div>

                                            <div class="d-flex justify-content-center align-items-center gap-2">
                                                <span class="badge ${areaBadgeClass} rounded-pill px-3 py-2">${areaStatusLabel}</span>
                                                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                                                    السعة: ${parkingArea.available_capacity} / ${parkingArea.total_capacity}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        } catch (innerException) {
                            console.error(innerException);
                        }
                    });
                }
            } catch (exception) {
                console.error("خطأ في تحميل خريطة المواقف", exception);
            }
        }

        // دالة مساعدة لإظهار/إخفاء حقول الوقت بناءً على اختيار السائق
        window.toggleTimeInputs = function(isActualSelected) {
            const timeInputsDiv = document.getElementById('actualTimeInputs');
            if (timeInputsDiv) {
                isActualSelected ? timeInputsDiv.classList.remove('d-none') : timeInputsDiv.classList.add('d-none');
            }
        };

        // ---  بدء إجراءات الحجز التفاعلي  ---
        async function initiateSpotReservation(spotIdValue, spotNameValue, availableCapacity) {
            try {
                // إصلاح المشكلة: الاعتماد على السعة الرقمية بدلاً من حالة نصية
                if (availableCapacity <= 0) {
                    Swal.fire({
                        icon: 'warning', title: 'الموقف ممتلئ', text: 'عذراً، هذه الساحة لا تحتوي على أماكن شاغرة حالياً.', confirmButtonColor: '#2c3e50'
                    });
                    return;
                }

                // تعليق مضمن: عرض نافذة مخصصة تحتوي على خيارات الحجز المطلوبة في السيناريو
                const { value: formValues } = await Swal.fire({
                    title: `حجز موقف في (${spotNameValue})`,
                    html: `
                        <div class="text-start mt-3">
                            <div class="form-check mb-3 p-3 bg-light rounded-3 border">
                                <input class="form-check-input ms-2" type="radio" name="bookingType" id="typeInitial" value="initial" checked onchange="toggleTimeInputs(false)">
                                <label class="form-check-label fw-bold text-primary" for="typeInitial">
                                    ⏱️ حجز مبدئي (مهلة 20 دقيقة للوصول)
                                </label>
                                <small class="d-block text-muted mt-1">يضمن لك مكاناً مؤقتاً لحين وصولك للموقع.</small>
                            </div>
                            
                            <div class="form-check mb-3 p-3 bg-light rounded-3 border">
                                <input class="form-check-input ms-2" type="radio" name="bookingType" id="typeActual" value="actual" onchange="toggleTimeInputs(true)">
                                <label class="form-check-label fw-bold text-success" for="typeActual">
                                    ✅ حجز فعلي (يتم الخصم من المحفظة)
                                </label>
                                <small class="d-block text-muted mt-1">تحديد وقت الدخول والخروج مسبقاً وتأكيد الدفع.</small>
                            </div>

                            <div id="actualTimeInputs" class="d-none bg-white p-3 rounded-3 border shadow-sm mt-2">
                                <label class="form-label small fw-bold text-secondary">وقت وتاريخ الدخول:</label>
                                <input type="datetime-local" id="swalStartTime" class="form-control mb-3 shadow-none">
                                <label class="form-label small fw-bold text-secondary">وقت وتاريخ الخروج:</label>
                                <input type="datetime-local" id="swalEndTime" class="form-control shadow-none">
                            </div>
                        </div>
                    `,
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonText: 'تأكيد الحجز 🚀',
                    cancelButtonText: 'تراجع',
                    confirmButtonColor: '#2c3e50',
                    cancelButtonColor: '#d33',
                    preConfirm: () => {
                        try {
                            const typeSelected = document.querySelector('input[name="bookingType"]:checked').value;
                            
                            if (typeSelected === 'actual') {
                                const startVal = document.getElementById('swalStartTime').value;
                                const endVal = document.getElementById('swalEndTime').value;
                                
                                if (!startVal || !endVal) {
                                    Swal.showValidationMessage('يرجى إدخال وقتي الدخول والخروج لإتمام الحجز الفعلي');
                                    return false;
                                }
                                if (new Date(startVal) >= new Date(endVal)) {
                                    Swal.showValidationMessage('وقت الخروج يجب أن يكون بعد وقت الدخول بشكل منطقي');
                                    return false;
                                }
                                return { type: 'actual', startTime: startVal, endTime: endVal };
                            }
                            return { type: 'initial' };
                        } catch (innerException) {
                            console.error(innerException);
                        }
                    }
                });

                if (formValues) {
                    executeBookingRequest(spotIdValue, formValues);
                }

            } catch (exception) {
                console.error("خطأ في نافذة الحجز", exception);
            }
        }

        // ---  إرسال طلب الاعتماد النهائي وإصدار التذكرة ---
        async function executeBookingRequest(targetSpotIdValue, bookingDataValues) {
            try {
                Swal.fire({
                    title: 'جاري تسجيل الحجز...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                // تجهيز حزمة البيانات للإرسال
                const payloadData = {
                    userId: currentUserData.accountId,
                    parkingId: targetSpotIdValue,
                    bookingType: bookingDataValues.type
                };

                if (bookingDataValues.type === 'actual') {
                    payloadData.startTime = bookingDataValues.startTime;
                    payloadData.endTime = bookingDataValues.endTime;
                }

                const response = await fetch('/api/bookings/create', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(payloadData)
                });

                const responseData = await response.json();

                if (response.ok && responseData.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'تم الحجز بنجاح! 🎟️',
                        text: bookingDataValues.type === 'initial' ? 'تم تأمين موقفك لـ 20 دقيقة القادمة.' : 'تم تأكيد حجزك الفعلي وخصم التكلفة.',
                        confirmButtonColor: '#2c3e50'
                    }).then(() => {
                        fetchWalletBalance();
                        checkActiveTicketAndLoadGrid();
                    });
                } else {
                    throw new Error(responseData.message || 'تعذر إتمام عملية الحجز.');
                }
            } catch (exception) {
                Swal.fire({ icon: 'error', title: 'فشل الحجز', text: exception.message, confirmButtonColor: '#d33' });
            }
        }

        // دالة لجلب الساحات المتاحة وتعبئة القائمة المنسدلة
        async function loadParkingOptions() {
            try {
                const response = await fetch('/api/parkings/spots');
                const resultData = await response.json();
                const selectElement = document.getElementById('targetParkingSelect');
                
                if (selectElement && response.ok) {
                    selectElement.innerHTML = '<option value="" selected disabled>اختر الساحة التي حولت إليها...</option>';
                    resultData.data.forEach(parking => {
                        selectElement.innerHTML += `<option value="${parking.id}">${parking.name}</option>`;
                    });
                }
            } catch (exception) { console.error(exception); }
        }


        // وظيفة إلغاء الحجز الحالي مع شروط الوقت
        async function cancelCurrentBooking() {
            try {
                // التأكد من وجود رقم الحجز قبل إرسال الطلب
                if (!activeBookingId) {
                    Swal.fire('خطأ', 'لم يتم التعرف على رقم الحجز النشط. يرجى تحديث الصفحة.', 'error');
                    return;
                }

                const { isConfirmed } = await Swal.fire({
                    title: 'تأكيد الإلغاء',
                    text: 'هل أنت متأكد من رغبتك في إلغاء الحجز؟ سيتم تطبيق سياسة الاسترجاع (100% قبل 30 دقيقة، 50% خلال الـ 30 دقيقة الأخيرة).',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'نعم، إلغاء الحجز',
                    cancelButtonText: 'تراجع',
                    confirmButtonColor: '#d33'
                });

                if (!isConfirmed) return;

                // إظهار حالة التحميل
                Swal.fire({
                    title: 'جاري الإلغاء...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                const response = await fetch('/api/bookings/cancel', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'Accept': 'application/json' // ضروري لاستقبال أخطاء لارافيل بوضوح
                    },
                    body: JSON.stringify({ bookingId: activeBookingId })
                });

                const result = await response.json();

                if (response.ok && result.status === 'success') {
                    // 1. إخفاء إجباري وفوري للتذكرة من الواجهة باستخدام CSS
                    const ticketSection = document.getElementById('activeTicketSection');
                    if (ticketSection) {
                        ticketSection.style.setProperty('display', 'none', 'important');
                        ticketSection.classList.add('d-none');
                    }

                    // 2. إظهار رسالة النجاح
                    Swal.fire('تم الإلغاء', result.message, 'success');
                    
                    // 3. تصفير المتغير وتحديث البيانات
                    activeBookingId = null;
                    checkActiveTicketAndLoadGrid();
                    fetchWalletBalance();
                } else {
                    Swal.fire('خطأ', result.message || 'حدث خطأ أثناء محاولة الإلغاء.', 'error');
                }
            } catch (exception) { 
                console.error("خطأ في الإلغاء:", exception); 
                Swal.fire('خطأ', 'تعذر الاتصال بالخادم.', 'error');
            }
        }

        // وظيفة طلب تبديل الساحة
        async function requestChangeSpot() {
            try {
                const response = await fetch('/api/parkings/spots');
                const result = await response.json();
                
                let optionsHtml = '';
                result.data.forEach(p => {
                    if (p.available_capacity > 0) {
                        optionsHtml += `<option value="${p.id}">${p.name} (متاح: ${p.available_capacity})</option>`;
                    }
                });

                const { value: newParkingId } = await Swal.fire({
                    title: 'اختر الساحة البديلة',
                    html: `<select id="swalNewParking" class="form-select">${optionsHtml}</select>`,
                    showCancelButton: true,
                    confirmButtonText: 'تأكيد التبديل',
                    preConfirm: () => document.getElementById('swalNewParking').value
                });

                if (newParkingId) {
                    const res = await fetch('/api/bookings/change-spot', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ bookingId: activeBookingId, newParkingId: newParkingId })
                    });

                    if (res.ok) {
                        Swal.fire('نجاح', 'تم تبديل الموقف بنجاح.', 'success');
                        checkActiveTicketAndLoadGrid();
                    } else {
                        const err = await res.json();
                        Swal.fire('خطأ', err.message, 'error');
                    }
                }
            } catch (e) { console.error(e); }
        }


        /*
        |--------------------------------------------------------------------------
        | دوال الإعدادات والمحفظة وتسجيل الخروج
        |--------------------------------------------------------------------------
        */

        function loadProfileData() {
            try {
                document.getElementById('profileNameDisplay').value = currentUserData.name;
                document.getElementById('profilePhoneInput').value = currentUserData.phone || '';
            } catch (exception) {
                console.error("خطأ في تحميل بيانات الملف الشخصي", exception);
            }
        }

        function logoutUser() {
            try {
                Swal.fire({
                    title: 'تسجيل الخروج',
                    text: "هل ترغب في مغادرة بوابة السائق؟",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'نعم، تسجيل الخروج',
                    cancelButtonText: 'إلغاء'
                }).then((result) => {
                    try {
                        if (result.isConfirmed) {
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


        // معالجة تحديث البيانات الشخصية للسائق
        document.getElementById('profileForm').addEventListener('submit', async function(event) {
            try {
                event.preventDefault();
                
                const submitButtonElement = document.getElementById('updateProfileBtn');
                submitButtonElement.disabled = true;

                try {
                    const response = await fetch('/api/accounts/update-profile', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            accountId: currentUserData.accountId,
                            phone: document.getElementById('profilePhoneInput').value,
                            password: document.getElementById('profilePasswordInput').value
                        })
                    });

                    const resultData = await response.json();

                    if (response.ok) {
                        currentUserData.phone = resultData.updatedData.phone;
                        localStorage.setItem('userData', JSON.stringify(currentUserData));
                        Swal.fire('تم التحديث!', 'تم حفظ بيانات الاتصال وتأمين الحساب بنجاح.', 'success');
                    } else {
                        throw new Error(resultData.message);
                    }
                } catch (exception) {
                    Swal.fire('خطأ في التحديث', exception.message, 'error');
                } finally {
                    submitButtonElement.disabled = false;
                }
            } catch (exception) {
                console.error(exception);
            }
        });

        // --- FR4: دالة جلب وعرض سجل الإشعارات في لوحة التحكم ---
        async function loadDashboardNotifications() {
            try {
                if (!currentUserData || !currentUserData.accountId) return;

                const response = await fetch('/api/notifications?userId=' + currentUserData.accountId, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' } // 👈 هذا السطر يمنع ظهور خطأ HTML نهائياً
                });
                
                const resultData = await response.json();
                const logContainer = document.getElementById('dashboardNotificationLog');

                if (logContainer && response.ok && resultData.status === 'success') {
                    logContainer.innerHTML = '';

                    if (resultData.data.length === 0) {
                        logContainer.innerHTML = '<div class="text-center py-5 text-muted small">لا توجد تنبيهات مسجلة في حسابك حالياً.</div>';
                        return;
                    }

                    resultData.data.slice(0, 10).forEach(item => {
                        try {
                            let icon = '📩';
                            let borderClass = 'border-start border-4 border-info';
                            
                            if (item.type.includes('Rejected') || item.type.includes('Expired') || item.type.includes('Blocked')) {
                                icon = '⚠️';
                                borderClass = 'border-start border-4 border-danger';
                            } else if (item.type.includes('Approved') || item.type.includes('Confirmed')) {
                                icon = '✅';
                                borderClass = 'border-start border-4 border-success';
                            }

                            const timeAgo = new Date(item.created_at).toLocaleString('ar-LY', {
                                month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
                            });

                            logContainer.innerHTML += `
                                <div class="list-group-item list-group-item-action p-3 ${borderClass} bg-white shadow-sm mb-2 rounded-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="d-flex align-items-center">
                                            <span class="fs-4 me-3">${icon}</span>
                                            <div>
                                                <p class="mb-1 fw-bold text-dark" style="font-size: 0.95rem;">${item.message}</p>
                                                <small class="text-muted" style="font-size: 0.8rem;">${timeAgo}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        } catch (e) { console.error(e); }
                    });
                } else {
                    console.error("الباك إند أرجع خطأ:", resultData.message);
                }
            } catch (exception) {
                console.error("خطأ في جلب سجل التنبيهات", exception);
            }
        }
        // --- مشغل أوتوماتيكي صامت لتنظيف الحجوزات المنتهية (يعمل كل دقيقة) ---
        setInterval(async () => {
            try {
                // 1. تحديث الإحصائيات (المخالفات)
                refreshDriverStats();

                // 2. فحص الحجوزات المنتهية
                const response = await fetch('/api/bookings/cleanup-expired', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' }
                });
                
                const result = await response.json();
                
                if (response.ok && result.message.includes('معالجة') && !result.message.includes('0')) {
                    checkActiveTicketAndLoadGrid();
                }
            } catch (exception) {}
        }, 60000);
    </script>
</body>
</html>