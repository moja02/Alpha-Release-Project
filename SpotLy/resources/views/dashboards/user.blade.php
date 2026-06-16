<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpotLy - بوابة السائق التفاعلية</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
        #booking-tabs .nav-link {
            color: rgba(255, 255, 255, 0.75);
            transition: all 0.2s ease-in-out;
            cursor: pointer;
        }
        #booking-tabs .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.15);
        }
        #booking-tabs .nav-link.active {
            color: #1e293b !important;
            background-color: #ffffff !important;
            box-shadow: 0 2px 5px rgba(0,0,0,0.15);
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
            <a class="nav-link" onclick="switchTab('invoicesTab', this)">🧾 فواتير الشحن</a>
            <a class="nav-link" onclick="switchTab('historyTab', this)">📜 سجل الحجوزات</a>
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
                <div class="card-header bg-gradient bg-primary text-white p-4 border-0">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div>
                            <h5 class="fw-bold mb-1">📍 حجز موقف سيارات تفاعلي</h5>
                            <p class="fs-6 mb-0 text-white text-opacity-75">اختر طريقة الحجز المفضلة لديك بالأسفل</p>
                        </div>
                        <ul class="nav nav-pills nav-fill gap-2 border p-1 rounded-pill bg-white bg-opacity-10" id="booking-tabs" style="min-width: 320px;">
                            <li class="nav-item">
                                <button class="nav-link active fw-bold text-white rounded-pill px-4 py-2 border-0" id="tab-manual" type="button" onclick="switchBookingSubTab('manual')">
                                    🔍 بحث يدوي
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link fw-bold text-white rounded-pill px-4 py-2 border-0" id="tab-smart" type="button" onclick="switchBookingSubTab('smart')">
                                    🧠 ترشيح ذكي (API)
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- 1. محتوى البحث اليدوي -->
                <div id="booking-manual-content" style="display: block;">
                    <div class="card-body p-4 bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-bold text-secondary">🗺️ خريطة المواقف المباشرة في النظام</span>
                            <div class="d-flex gap-2">
                                <span class="badge bg-success px-3 py-2 rounded-pill">متاح</span>
                                <span class="badge bg-danger px-3 py-2 rounded-pill">محجوز</span>
                            </div>
                        </div>
                        <div class="row g-3" id="spotsGridContainer">
                            <div class="text-center py-5 text-muted">جاري تحميل خريطة المواقف المباشرة...</div>
                        </div>
                    </div>
                </div>

                <!-- 2. محتوى الترشيح الذكي (واجهة المطور API) -->
                <div id="booking-smart-content" style="display: none;">
                    <div class="card-body p-4 bg-light">
                        <!-- مقابض التحكم بالوزن -->
                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h6 class="fw-bold text-dark mb-0">🧠 تفضيلات الترشيح الذكي (Weighted Sum Model)</h6>
                            </div>
                            <div class="card-body bg-white p-4">
                                <div class="alert alert-light border border-info border-opacity-25 text-dark rounded-3 mb-4 py-2">
                                    💡 <strong>تفاعلي:</strong> قم بسحب أوزان التفضيل حسب رغبتك بالأسفل (المجموع الكلي 100%). وانقر على الخريطة لتحديد مكان وجهتك لتعديل المسافة الجغرافية.
                                </div>
                                <div class="row align-items-center">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <label class="form-label fw-bold text-secondary d-flex justify-content-between mb-2">
                                            <span>📏 القرب الجغرافي للوجهة (المسافة):</span>
                                            <span class="text-primary fw-bold" id="lblDistWeight">50%</span>
                                        </label>
                                        <input type="range" class="form-range" id="wsmDistanceSlider" min="0" max="100" value="50" oninput="adjustWsmSliders('distance')">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-secondary d-flex justify-content-between mb-2">
                                            <span>🚗 وفرة الأماكن (الشواغر):</span>
                                            <span class="text-success fw-bold" id="lblAvailWeight">50%</span>
                                        </label>
                                        <input type="range" class="form-range" id="wsmAvailabilitySlider" min="0" max="100" value="50" oninput="adjustWsmSliders('availability')">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- الخريطة التفاعلية وقائمة التوصيات الجانبية -->
                        <div class="row g-4 mb-4">
                            <!-- حاوية الخريطة -->
                            <div class="col-lg-8">
                                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                                    <div class="card-header bg-white py-3 fw-bold border-bottom">
                                        🗺️ خريطة المواقف الذكية (انقر لتحديد وجهتك 📍)
                                    </div>
                                    <div class="card-body p-3">
                                        <div id="wsmMap" style="height: 420px; border-radius: 12px; z-index: 1;"></div>
                                    </div>
                                </div>
                            </div>
                            <!-- القائمة الجانبية للتوصيات -->
                            <div class="col-lg-4">
                                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                                    <div class="card-header bg-white py-3 fw-bold border-bottom d-flex justify-content-between align-items-center">
                                        <span>⭐ المواقف المقترحة</span>
                                        <span class="badge bg-success rounded-pill px-2 py-1" style="font-size: 0.85rem;">نسبة المطابقة</span>
                                    </div>
                                    <div class="card-body p-0" style="max-height: 440px; overflow-y: auto;" id="wsmRecommendationSidebarList">
                                        <div class="text-center py-5 text-muted">جاري تحميل الترشيحات الذكية...</div>
                                    </div>
                                </div>
                            </div>
                        </div>


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

        <!-- فواتير الشحن المباشر (الضمان) -->
        <section id="invoicesTab" class="content-section d-none">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-gradient bg-success text-white p-4 border-0 d-flex align-items-center justify-content-between">
                    <h5 class="fw-bold mb-0">💵 فواتير الشحن المباشر (إيصالات البوابة)</h5>
                    <button onclick="loadUserDirectRechargeInvoices()" class="btn btn-sm btn-light rounded-pill px-3">تحديث الفواتير</button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>رقم الإيصال</th>
                                    <th>الساحة</th>
                                    <th>الموظف</th>
                                    <th>القيمة (كاش)</th>
                                    <th>تاريخ الشحن</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody id="directRechargeInvoicesTableBody">
                                <tr>
                                    <td colspan="6" class="text-muted py-5">جاري تحميل الفواتير...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- سجل الحجوزات التصفية الشهرية -->
        <section id="historyTab" class="content-section d-none">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold text-dark mb-0">📜 سجل الحجوزات التاريخي</h5>
                </div>
                <div class="card-body bg-light">
                    <!-- فلاتر التصفية -->
                    <div class="row g-3 align-items-end mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-secondary">الشهر</label>
                            <select class="form-select shadow-none" id="historyMonthSelect">
                                <option value="">كل الأشهر</option>
                                <option value="1">يناير (1)</option>
                                <option value="2">فبراير (2)</option>
                                <option value="3">مارس (3)</option>
                                <option value="4">أبريل (4)</option>
                                <option value="5">مايو (5)</option>
                                <option value="6">يونيو (6)</option>
                                <option value="7">يوليو (7)</option>
                                <option value="8">أغسطس (8)</option>
                                <option value="9">سبتمبر (9)</option>
                                <option value="10">أكتوبر (10)</option>
                                <option value="11">نوفمبر (11)</option>
                                <option value="12">ديسمبر (12)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-secondary">السنة</label>
                            <select class="form-select shadow-none" id="historyYearSelect">
                                <!-- سيتم تعبئتها ديناميكياً بالجافا سكربت -->
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button onclick="loadUserBookingHistory()" class="btn btn-primary w-100 fw-bold py-2 rounded-3">
                                تصفية وتحديث 🔍
                            </button>
                        </div>
                    </div>

                    <!-- جدول الحجوزات -->
                    <div class="table-responsive bg-white rounded-3 shadow-sm">
                        <table class="table table-hover mb-0 align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>رقم الحجز</th>
                                    <th>الساحة</th>
                                    <th>نوع الحجز</th>
                                    <th>تاريخ الدخول</th>
                                    <th>تاريخ الخروج</th>
                                    <th>الحالة</th>
                                    <th>تاريخ الحجز</th>
                                </tr>
                            </thead>
                            <tbody id="bookingHistoryTableBody">
                                <tr>
                                    <td colspan="7" class="text-muted py-5">جاري تحميل سجل الحجوزات...</td>
                                </tr>
                            </tbody>
                        </table>
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

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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
                } else if (sectionIdValue === 'invoicesTab') {
                    loadUserDirectRechargeInvoices();
                } else if (sectionIdValue === 'historyTab') {
                    initializeHistoryYearSelect();
                    loadUserBookingHistory();
                }

            } catch (exception) {
                console.error("خطأ في التبديل وتحديث البيانات", exception);
            }
        }

        // معالجة رفع إيصال التحويل البنكي للشحن مع تضمين معرف الساحة
        document.getElementById('rechargeRequestForm').addEventListener('submit', async function(event) {
            try {
                event.preventDefault();
                
                //  قراءة معرف الساحة، المبلغ، والملف من الواجهة
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
                    //  بناء حزمة البيانات (FormData) لتشمل parkingId الإلزامي
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

        // التبديل بين البحث اليدوي والترشيح الذكي داخل حجز الموقف التفاعلي
        window.switchBookingSubTab = function(target) {
            const tabManual = document.getElementById('tab-manual');
            const tabSmart = document.getElementById('tab-smart');
            const manualContent = document.getElementById('booking-manual-content');
            const smartContent = document.getElementById('booking-smart-content');

            if (target === 'manual') {
                tabManual.classList.add('active');
                tabSmart.classList.remove('active');
                manualContent.style.display = 'block';
                smartContent.style.display = 'none';
                loadLiveSpotsGrid(); // تحديث المواقف المباشرة
            } else {
                tabSmart.classList.add('active');
                tabManual.classList.remove('active');
                manualContent.style.display = 'none';
                smartContent.style.display = 'block';
                
                // تهيئة الخريطة لأول مرة أو تحديث حجمها لتفادي مشاكل الأبعاد
                if (!wsmMap) {
                    setTimeout(() => {
                        initWsmMap();
                    }, 100);
                } else {
                    setTimeout(() => {
                        wsmMap.invalidateSize();
                    }, 100);
                }
            }
        };

        // متغيرات خوارزمية المجموع الموزون (WSM) للترشيح الذكي
        let wsmMap = null;
        let wsmMapMarker = null;
        let wsmMarkersList = [];
        let wsmLat = 32.8872;
        let wsmLng = 13.1913;
        let wsmDistWeight = 0.5;
        let wsmAvailWeight = 0.5;
        let wsmParkingsData = [];

        // 1. دالة حساب المسافة الجغرافية بالكيلومتر بين نقطتين (Haversine Formula)
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371; // نصف قطر الأرض بالكيلومتر
            const dLat = deg2rad(lat2 - lat1);
            const dLon = deg2rad(lon2 - lon1);
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                      Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
                      Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return R * c;
        }
        function deg2rad(deg) {
            return deg * (Math.PI / 180);
        }

        // 2. تحديث وتعديل أوزان Sliders بطريقة تفاعلية ومجموع 100%
        window.adjustWsmSliders = function(source) {
            const distSlider = document.getElementById('wsmDistanceSlider');
            const availSlider = document.getElementById('wsmAvailabilitySlider');
            
            if (source === 'distance') {
                availSlider.value = 100 - parseInt(distSlider.value);
            } else {
                distSlider.value = 100 - parseInt(availSlider.value);
            }
            
            // تحديث بطاقات الأرقام
            document.getElementById('lblDistWeight').innerText = distSlider.value + '%';
            document.getElementById('lblAvailWeight').innerText = availSlider.value + '%';
            
            wsmDistWeight = parseInt(distSlider.value) / 100;
            wsmAvailWeight = parseInt(availSlider.value) / 100;
            
            updateWsmApiUrlDisplay();
            updateWsmCalculations();
        };

        // 3. تهيئة خريطة الـ WSM الذكية
        window.initWsmMap = async function() {
            try {
                if (wsmMap) return;
                
                const container = document.getElementById('wsmMap');
                if (!container) return;

                // تهيئة الخريطة وتوسيطها عند موقع الجامعة
                wsmMap = L.map('wsmMap').setView([wsmLat, wsmLng], 13);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(wsmMap);

                // تعريف الأيقونات بتنسيق CSS دائري مميز
                window.greenIcon = L.divIcon({
                    html: '<div style="background-color: #2ecc71; width: 28px; height: 28px; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 10px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 11px; font-family: sans-serif;">P</div>',
                    className: 'custom-div-icon',
                    iconSize: [28, 28],
                    iconAnchor: [14, 14]
                });
                
                window.yellowIcon = L.divIcon({
                    html: '<div style="background-color: #f1c40f; width: 28px; height: 28px; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 10px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 11px; font-family: sans-serif;">P</div>',
                    className: 'custom-div-icon',
                    iconSize: [28, 28],
                    iconAnchor: [14, 14]
                });
                
                window.redIcon = L.divIcon({
                    html: '<div style="background-color: #e74c3c; width: 28px; height: 28px; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 10px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 11px; font-family: sans-serif;">P</div>',
                    className: 'custom-div-icon',
                    iconSize: [28, 28],
                    iconAnchor: [14, 14]
                });

                window.userDestIcon = L.divIcon({
                    html: '<div style="background-color: #3498db; width: 34px; height: 34px; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 12px rgba(52, 152, 219, 0.6); display: flex; align-items: center; justify-content: center; color: white; font-size: 14px;">📍</div>',
                    className: 'custom-div-icon-dest',
                    iconSize: [34, 34],
                    iconAnchor: [17, 17]
                });

                // إضافة علامة السائق الزرقاء القابلة للسحب
                wsmMapMarker = L.marker([wsmLat, wsmLng], {
                    icon: userDestIcon,
                    draggable: true
                }).addTo(wsmMap).bindPopup('<div class="text-center font-bold">وجهتك المستهدفة / موقعك 📍</div>').openPopup();

                wsmMapMarker.on('dragend', function() {
                    const pos = wsmMapMarker.getLatLng();
                    wsmLat = pos.lat;
                    wsmLng = pos.lng;
                    updateWsmApiUrlDisplay();
                    updateWsmCalculations();
                });

                // نقرة على الخريطة لتحديث الوجهة
                wsmMap.on('click', function(e) {
                    wsmLat = e.latlng.lat;
                    wsmLng = e.latlng.lng;
                    wsmMapMarker.setLatLng(e.latlng);
                    updateWsmApiUrlDisplay();
                    updateWsmCalculations();
                });

                // جلب المواقف وتعبئة البيانات محلياً
                const response = await fetch('/api/parkings/spots');
                const res = await response.json();
                if (response.ok && res.status === 'success') {
                    wsmParkingsData = res.data;
                    updateWsmCalculations();
                }
            } catch (error) {
                console.error("خطأ في تشغيل خريطة WSM", error);
            }
        };

        // 4. تنفيذ الخوارزمية محلياً وإصدار النتائج
        window.updateWsmCalculations = function() {
            if (!wsmParkingsData || wsmParkingsData.length === 0) return;

            // حساب المسافات بالكيلومتر
            let dataCopy = wsmParkingsData.map(p => {
                const distance = calculateDistance(wsmLat, wsmLng, p.latitude, p.longitude);
                return { ...p, computed_distance: distance };
            });

            // الحصول على القيم للتطبيع
            const distances = dataCopy.map(p => p.computed_distance);
            const maxDist = Math.max(...distances) || 1;
            const minDist = Math.min(...distances) || 0;
            const distRange = maxDist - minDist || 1;

            // تطبيق معادلة WSM
            dataCopy.forEach(p => {
                const normDist = (maxDist - p.computed_distance) / distRange;
                const normAvail = p.total_capacity > 0 ? (p.available_capacity / p.total_capacity) : 0;
                
                const score = (wsmDistWeight * normDist) + (wsmAvailWeight * normAvail);
                p.wsm_score = score;
                p.match_percentage = Math.round(score * 100);
            });

            // فرز تنازلي حسب الدرجة الأعلى
            dataCopy.sort((a, b) => b.wsm_score - a.wsm_score);

            // تحديث العلامات والقائمة
            updateWsmMapMarkers(dataCopy);
            updateWsmRecommendationList(dataCopy);
        };

        // 5. رسم وتلوين علامات المواقف على الخريطة
        window.updateWsmMapMarkers = function(sortedData) {
            if (!wsmMap) return;

            // تنظيف المواقع السابقة
            wsmMarkersList.forEach(m => wsmMap.removeLayer(m));
            wsmMarkersList = [];

            sortedData.forEach((p, idx) => {
                if (!p.latitude || !p.longitude) return;

                // أخضر للأول، أصفر للثاني والثالث (متاحين)، أحمر للبقية أو الممتلئ
                let markerIcon = yellowIcon;
                if (p.available_capacity === 0) {
                    markerIcon = redIcon;
                } else if (idx === 0) {
                    markerIcon = greenIcon;
                } else if (idx >= 3) {
                    markerIcon = redIcon;
                }

                const marker = L.marker([p.latitude, p.longitude], { icon: markerIcon }).addTo(wsmMap);
                
                const popupContent = `
                    <div style="direction: rtl; text-align: right; font-family: sans-serif; min-width: 170px; line-height: 1.4;">
                        <h6 class="fw-bold mb-1 text-dark">${p.name}</h6>
                        <span class="badge bg-success text-white mb-2">تطابق: ${p.match_percentage}%</span>
                        <p class="mb-1 text-muted small">📍 <b>المسافة:</b> ${p.computed_distance.toFixed(2)} كم</p>
                        <p class="mb-2 text-muted small">🚗 <b>الشاغر:</b> ${p.available_capacity} / ${p.total_capacity}</p>
                        ${p.available_capacity > 0 
                            ? `<button onclick="initiateSpotReservation(${p.id}, '${p.name}', ${p.available_capacity})" class="btn btn-sm btn-primary w-100 fw-bold py-1">حجز فوري 🚀</button>` 
                            : '<span class="badge bg-danger w-100 d-block text-center py-1">ممتلئ بالكامل</span>'}
                    </div>
                `;
                marker.bindPopup(popupContent);
                wsmMarkersList.push(marker);
            });
        };

        // 6. تحديث قائمة المقترحات الجانبية
        window.updateWsmRecommendationList = function(sortedData) {
            const list = document.getElementById('wsmRecommendationSidebarList');
            if (!list) return;

            list.innerHTML = '';

            sortedData.forEach((p, idx) => {
                let badgeStyle = 'bg-warning text-dark';
                if (p.available_capacity === 0) {
                    badgeStyle = 'bg-danger text-white';
                } else if (idx === 0) {
                    badgeStyle = 'bg-success text-white';
                } else if (idx >= 3) {
                    badgeStyle = 'bg-secondary text-white';
                }

                const isAvailable = p.available_capacity > 0;

                const itemHtml = `
                    <div class="p-3 border-bottom list-group-item-action transition-all" style="cursor: pointer;" onclick="focusParkingOnWsmMap(${p.latitude}, ${p.longitude}, '${p.name}')">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark" style="font-size: 0.9rem;">${p.name}</span>
                            <span class="badge ${badgeStyle} rounded-pill px-2 py-1" style="font-size: 0.75rem;">${p.match_percentage}%</span>
                        </div>
                        <small class="text-muted d-block mb-2">📍 ${p.location_park} (${p.computed_distance.toFixed(2)} كم)</small>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-secondary fw-semibold">الشاغر: ${p.available_capacity} / ${p.total_capacity}</small>
                            ${isAvailable 
                                ? `<button onclick="event.stopPropagation(); initiateSpotReservation(${p.id}, '${p.name}', ${p.available_capacity})" class="btn btn-sm btn-primary px-3 py-1 rounded-pill fw-bold" style="font-size: 0.75rem;">حجز 🚀</button>` 
                                : '<span class="badge bg-danger rounded-pill px-2 py-1" style="font-size: 0.7rem;">ممتلئ</span>'}
                        </div>
                    </div>
                `;
                list.insertAdjacentHTML('beforeend', itemHtml);
            });
        };

        // 7. التركيز على الموقف على الخريطة
        window.focusParkingOnWsmMap = function(lat, lng, name) {
            if (wsmMap) {
                wsmMap.setView([lat, lng], 15);
                const marker = wsmMarkersList.find(m => m.getLatLng().lat === lat && m.getLatLng().lng === lng);
                if (marker) {
                    marker.openPopup();
                }
            }
        };

        // 8. تحديث نص استعلام الـ API للمطورين
        window.updateWsmApiUrlDisplay = function() {
            const display = document.getElementById('wsmApiUrlDisplay');
            if (display) {
                display.value = `/api/parkings/recommend?latitude=${wsmLat.toFixed(6)}&longitude=${wsmLng.toFixed(6)}&sort_by=wsm&w_dist=${wsmDistWeight.toFixed(2)}&w_avail=${wsmAvailWeight.toFixed(2)}`;
            }
        };

        // 9. إرسال طلب الـ API الفعلي للسيرفر لعرض استجابة المطور (JSON)
        window.sendSmartRecommendationRequest = async function() {
            const btnSend = document.getElementById('btnSendSmartApi');
            btnSend.disabled = true;
            btnSend.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> جاري الطلب...';

            const startTime = performance.now();
            const url = document.getElementById('wsmApiUrlDisplay').value;

            try {
                const response = await fetch(url);
                const data = await response.json();
                const endTime = performance.now();
                const latency = Math.round(endTime - startTime);

                // إظهار لوحة المطور وتحديث بيانات الاستجابة
                document.getElementById('apiResponsePanel').classList.remove('d-none');
                document.getElementById('apiResponseStatus').innerText = `${response.status} ${response.statusText || (response.ok ? 'OK' : 'Error')}`;
                
                const statusBadge = document.getElementById('apiResponseStatus').parentElement;
                if (response.ok) {
                    statusBadge.className = 'badge bg-success px-3 py-2 rounded-pill';
                } else {
                    statusBadge.className = 'badge bg-danger px-3 py-2 rounded-pill';
                }

                document.getElementById('apiResponseTime').innerText = latency;
                document.getElementById('apiResponseBody').innerText = JSON.stringify(data, null, 4);

            } catch (error) {
                console.error("خطأ أثناء إرسال طلب الـ API", error);
                document.getElementById('apiResponsePanel').classList.remove('d-none');
                document.getElementById('apiResponseStatus').innerText = '500 Error';
                document.getElementById('apiResponseStatus').parentElement.className = 'badge bg-danger px-3 py-2 rounded-pill';
                document.getElementById('apiResponseBody').innerText = JSON.stringify({
                    status: "error",
                    message: error.message || "Internal Server Error"
                }, null, 4);
            } finally {
                btnSend.disabled = false;
                btnSend.innerHTML = 'إرسال الطلب ⚡';
            }
        };

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

                // عرض نافذة مخصصة تحتوي على خيارات الحجز المطلوبة في السيناريو
                const { value: formValues } = await Swal.fire({
                    title: `حجز موقف في (${spotNameValue})`,
                    html: `
                        <div class="text-start mt-3">
                            <div class="form-check mb-3 p-3 bg-light rounded-3 border">
                                <input class="form-check-input ms-2" type="radio" name="bookingType" id="typeInitial" value="initial" checked onchange="toggleTimeInputs(false)">
                                <label class="form-check-label fw-bold text-primary" for="typeInitial">
                                    ⏱️ حجز مبدئي (مهلة 30 دقيقة للوصول)
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
                        text: bookingDataValues.type === 'initial' ? 'تم تأمين موقفك لـ 30 دقيقة القادمة.' : 'تم تأكيد حجزك الفعلي وخصم التكلفة.',
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
                    headers: { 'Accept': 'application/json' } 
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

        // --- سجل الحجوزات والشحن المباشر ---
        function initializeHistoryYearSelect() {
            const yearSelect = document.getElementById('historyYearSelect');
            if (yearSelect && yearSelect.options.length === 0) {
                const currentYear = new Date().getFullYear();
                for (let y = currentYear; y >= currentYear - 5; y--) {
                    const opt = document.createElement('option');
                    opt.value = y;
                    opt.textContent = y;
                    yearSelect.appendChild(opt);
                }
            }
        }

        async function loadUserBookingHistory() {
            try {
                if (!currentUserData || !currentUserData.accountId) return;

                const monthVal = document.getElementById('historyMonthSelect').value;
                const yearVal = document.getElementById('historyYearSelect').value;

                let url = `/api/bookings/history?userId=${currentUserData.accountId}`;
                if (monthVal) url += `&month=${monthVal}`;
                if (yearVal) url += `&year=${yearVal}`;

                const response = await fetch(url);
                const resultData = await response.json();
                const tableBody = document.getElementById('bookingHistoryTableBody');

                if (tableBody && response.ok && resultData.status === 'success') {
                    tableBody.innerHTML = '';

                    if (resultData.data.length > 0) {
                        resultData.data.forEach(booking => {
                            let typeLabel = booking.type === 'initial' ? '⏱️ مبدئي' : '✅ فعلي';
                            let statusBadgeClass = 'bg-secondary';
                            let statusLabel = booking.status;

                            if (booking.status === 'Pending') {
                                statusBadgeClass = 'bg-warning text-dark';
                                statusLabel = 'قيد الانتظار';
                            } else if (booking.status === 'Active') {
                                statusBadgeClass = 'bg-success text-white';
                                statusLabel = 'نشط';
                            } else if (booking.status === 'Cancelled') {
                                statusBadgeClass = 'bg-danger text-white';
                                statusLabel = 'ملغي';
                            } else if (booking.status === 'Completed') {
                                statusBadgeClass = 'bg-info text-white';
                                statusLabel = 'مكتمل';
                            } else if (booking.status === 'Expired') {
                                statusBadgeClass = 'bg-dark text-white';
                                statusLabel = 'منتهي الصلاحية';
                            }

                            const startDate = booking.start_time 
                                ? new Date(booking.start_time).toLocaleString('ar-LY', { hour: '2-digit', minute: '2-digit', day: 'numeric', month: 'numeric' })
                                : '--';
                            const endDate = booking.end_time 
                                ? new Date(booking.end_time).toLocaleString('ar-LY', { hour: '2-digit', minute: '2-digit', day: 'numeric', month: 'numeric' })
                                : '--';
                            const createdAt = new Date(booking.created_at).toLocaleString('ar-LY', {
                                year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
                            });

                            tableBody.innerHTML += `
                                <tr>
                                    <td class="fw-bold">#${booking.id}</td>
                                    <td>${booking.parking_name}</td>
                                    <td class="fw-bold">${typeLabel}</td>
                                    <td class="small text-muted">${startDate}</td>
                                    <td class="small text-muted">${endDate}</td>
                                    <td><span class="badge ${statusBadgeClass} rounded-pill px-3 py-1">${statusLabel}</span></td>
                                    <td class="small text-muted">${createdAt}</td>
                                </tr>
                            `;
                        });
                    } else {
                        tableBody.innerHTML = '<tr><td colspan="7" class="text-muted py-5">لا توجد حجوزات تطابق خيارات التصفية المحددة.</td></tr>';
                    }
                }
            } catch (exception) {
                console.error("خطأ أثناء جلب سجل الحجوزات", exception);
            }
        }

        async function loadUserDirectRechargeInvoices() {
            try {
                if (!currentUserData || !currentUserData.accountId) return;

                const response = await fetch(`/api/recharges/invoices?userId=${currentUserData.accountId}`);
                const resultData = await response.json();
                const tableBody = document.getElementById('directRechargeInvoicesTableBody');

                if (tableBody && response.ok && resultData.status === 'success') {
                    tableBody.innerHTML = '';

                    if (resultData.data.length > 0) {
                        resultData.data.forEach(invoice => {
                            const dateValue = new Date(invoice.created_at).toLocaleString('ar-LY', {
                                year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
                            });

                            const invoiceJson = JSON.stringify(invoice).replace(/"/g, '&quot;');

                            tableBody.innerHTML += `
                                <tr>
                                    <td class="fw-bold text-dark">#${invoice.id}</td>
                                    <td>${invoice.parking_name}</td>
                                    <td>${invoice.employee_name}</td>
                                    <td class="fw-bold text-success">${invoice.cash_value} د.ل</td>
                                    <td class="small text-muted">${dateValue}</td>
                                    <td>
                                        <button onclick="showInvoiceDetail('${invoiceJson}')" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1">
                                            📄 عرض وتفاصيل
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        tableBody.innerHTML = '<tr><td colspan="6" class="text-muted py-5">لا توجد إيصالات شحن نقدي مباشر عند البوابة في سجلك.</td></tr>';
                    }
                }
            } catch (exception) {
                console.error("خطأ أثناء جلب فواتير الشحن المباشر", exception);
            }
        }

        window.showInvoiceDetail = function(invoiceJsonStr) {
            try {
                const invoice = JSON.parse(invoiceJsonStr.replace(/&quot;/g, '"'));
                const formattedDate = new Date(invoice.created_at).toLocaleString('ar-LY', {
                    year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
                });

                Swal.fire({
                    title: '🧾 إيصال شحن رصيد نقدي (ضمان مالي)',
                    html: `
                        <div id="printable-receipt" style="font-family: 'Courier New', Courier, monospace; border: 1px dashed #ccc; padding: 20px; background-color: #fff; text-align: right; direction: rtl;">
                            <div style="text-align: center; margin-bottom: 15px;">
                                <h4 style="margin: 0; font-weight: bold; color: #1a1a1a;">🚗 تطبيق SpotLy</h4>
                                <p style="margin: 5px 0; font-size: 0.85rem; color: #666;">إيصال شحن رصيد بوابة (نقدي)</p>
                                <p style="margin: 0; font-size: 0.85rem; color: #666;">--------------------------------</p>
                            </div>
                            <div style="font-size: 0.9rem; line-height: 1.6; color: #333;">
                                <p style="margin: 5px 0;"><b>رقم الفاتورة:</b> <span style="font-weight: bold;">#${invoice.id}</span></p>
                                <p style="margin: 5px 0;"><b>الساحة / الموقف:</b> ${invoice.parking_name}</p>
                                <p style="margin: 5px 0;"><b>الموظف المسؤول:</b> ${invoice.employee_name}</p>
                                <p style="margin: 5px 0;"><b>رقم لوحة المركبة:</b> ${invoice.plate_number || '--'}</p>
                                <p style="margin: 5px 0;"><b>تاريخ العملية:</b> ${formattedDate}</p>
                                <p style="margin: 0; font-size: 0.85rem; color: #666;">--------------------------------</p>
                                <div style="text-align: center; margin-top: 15px; margin-bottom: 10px; background-color: #f9f9f9; padding: 10px; border-radius: 5px;">
                                    <h5 style="margin: 0; color: #2ecc71; font-weight: bold; font-size: 1.15rem;">
                                        القيمة المستلمة: ${invoice.cash_value} د.ل
                                    </h5>
                                </div>
                                <p style="margin: 0; font-size: 0.85rem; color: #666; text-align: center;">شكراً لاستخدامكم SpotLy!</p>
                            </div>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: '🖨️ طباعة الإيصال',
                    cancelButtonText: 'إغلاق',
                    confirmButtonColor: '#27ae60',
                    cancelButtonColor: '#7f8c8d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const printWindow = window.open('', '_blank');
                        printWindow.document.write(`
                            <html>
                            <head>
                                <title>طباعة إيصال شحن #${invoice.id}</title>
                                <style>
                                    body { font-family: sans-serif; direction: rtl; text-align: right; padding: 20px; }
                                    #printable-receipt { border: 1px dashed #ccc; padding: 20px; max-width: 400px; margin: auto; }
                                    h3 { margin: 0; }
                                    @media print {
                                        body { padding: 0; }
                                        #printable-receipt { border: none; max-width: 100%; }
                                    }
                                </style>
                            </head>
                            <body onload="window.print(); window.close();">
                                <div id="printable-receipt">
                                    <div style="text-align: center; margin-bottom: 15px;">
                                        <h3 style="margin: 0;">🚗 تطبيق SpotLy</h3>
                                        <p style="margin: 5px 0; font-size: 0.85rem;">إيصال شحن رصيد بوابة (نقدي)</p>
                                        <p style="margin: 0;">--------------------------------</p>
                                    </div>
                                    <div style="font-size: 0.9rem; line-height: 1.6;">
                                        <p style="margin: 5px 0;"><b>رقم الفاتورة:</b> #${invoice.id}</p>
                                        <p style="margin: 5px 0;"><b>الساحة / الموقف:</b> ${invoice.parking_name}</p>
                                        <p style="margin: 5px 0;"><b>الموظف المسؤول:</b> ${invoice.employee_name}</p>
                                        <p style="margin: 5px 0;"><b>رقم لوحة المركبة:</b> ${invoice.plate_number || '--'}</p>
                                        <p style="margin: 5px 0;"><b>تاريخ العملية:</b> ${formattedDate}</p>
                                        <p style="margin: 0;">--------------------------------</p>
                                        <div style="text-align: center; margin-top: 15px; margin-bottom: 10px; background-color: #f9f9f9; padding: 10px; border-radius: 5px;">
                                            <h4 style="margin: 0; color: #2ecc71;">
                                                القيمة المستلمة: ${invoice.cash_value} د.ل
                                            </h4>
                                        </div>
                                        <p style="margin: 0; text-align: center;">شكراً لاستخدامكم SpotLy!</p>
                                    </div>
                                </div>
                            </body>
                            </html>
                        `);
                        printWindow.document.close();
                    }
                });
            } catch (err) {
                console.error(err);
                Swal.fire('خطأ', 'فشل في تحميل تفاصيل الإيصال.', 'error');
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