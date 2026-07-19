<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpotLy - لوحة تحكم الموظف</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        }

        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            opacity: 1;
            background: rgba(255, 255, 255, 0.2);
            box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.4), 0 4px 10px rgba(0, 0, 0, 0.03);
            font-weight: 600;
            transform: translateX(-4px);
        }

        .main-content {
            margin-right: 320px;
            padding: 20px 40px 40px 20px;
            min-height: 100vh;
        }

        /* Glass Header */
        .dashboard-header {
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

        /* Inputs and controls */
        .form-control, .form-select {
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 14px;
            color: var(--text-color);
            padding: 12px 18px;
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .form-control:focus, .form-select:focus {
            background: var(--input-bg);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(0, 113, 227, 0.15);
            color: var(--text-color);
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

        /* Navigation pills */
        .nav-pills {
            background: rgba(0, 0, 0, 0.05);
            padding: 6px;
            border-radius: 30px;
        }

        .nav-pills .nav-link {
            border-radius: 24px;
            color: var(--text-color);
            font-weight: 600;
            padding: 10px 24px;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .nav-pills .nav-link.active {
            background: white !important;
            color: #1d1d1f !important;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.06);
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
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(0, 113, 227, 0.3);
        }

        /* Badges */
        .badge {
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 30px;
        }

        /* Responsive hamburger menu for mobile */
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
    <aside class="sidebar" id="dashboardSidebar">
        <div class="text-center mb-4">
            <h2 class="brand-title">🚗 SpotLy</h2>
            <span class="badge bg-warning text-dark px-3 py-1 rounded-pill">بوابة الموظف</span>
        </div>
        <hr class="border-secondary border-opacity-25 my-3">
        <nav class="nav flex-column flex-grow-1">
            <a class="nav-link active" onclick="switchTab('overviewTab', this)"><i class="fas fa-home"></i> نظرة عامة</a>
            <a class="nav-link" onclick="switchTab('profileTab', this)"><i class="fas fa-user-cog"></i> البيانات الشخصية</a>
            <a class="nav-link" onclick="switchTab('createUserTab', this)"><i class="fas fa-user-plus"></i> إضافة مستخدم (سائق)</a>
            <a class="nav-link" onclick="switchTab('verifyVehicleTab', this)"><i class="fas fa-traffic-light"></i> إدارة الميدان</a>
            <a class="nav-link" onclick="switchTab('rechargeWalletTab', this)"><i class="fas fa-wallet"></i> شحن المحافظ</a>
            <a class="nav-link" onclick="switchTab('transferRequestsTab', this)"><i class="fas fa-file-invoice-dollar"></i> طلبات التحويل البنكي</a>
        </nav>
    </aside>

    <main class="main-content">
        <!-- Dashboard Glass Header -->
        <header class="dashboard-header">
            <div class="d-flex align-items-center gap-3">
                <i class="fas fa-bars menu-toggle" onclick="toggleSidebarMenu()"></i>
                <h5 class="mb-0 fw-bold" id="pageTitleDisplay">🏠 نظرة عامة</h5>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span id="employeeNameDisplay" class="fw-bold text-dark-emphasis"></span>
                <button onclick="logoutEmployee()" class="btn btn-sm btn-outline-danger px-4 rounded-pill">تسجيل الخروج</button>
            </div>
        </header>

        <!-- Tab Content Sections -->
        
        <!-- SECTION 1: Overview -->
        <section id="overviewTab" class="content-section">
            <div class="alert alert-info border-0 shadow-sm rounded-4 p-4 mb-4">
                📌 <strong>مرحباً بك في لوحة التحكم الميدانية:</strong> 
                تتيح لك هذه اللوحة إدارة حسابات السائقين الجدد، شحن الأرصدة المباشر للمستخدمين، وإدخال السيارات والتحقق من الحجوزات عند المَدخل.
            </div>
            
            <div class="row g-4 mt-2">
                <div class="col-md-4">
                    <div class="card p-4 border-start border-primary border-4 h-100">
                        <div class="d-flex align-items-center gap-3">
                            <span class="fs-1 text-primary opacity-50"><i class="fas fa-user-plus"></i></span>
                            <div>
                                <h6 class="text-muted mb-1">إضافة السائقين</h6>
                                <p class="mb-0 small text-muted">إنشاء حسابات وتخصيص بيانات اللوحة</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-4 border-start border-success border-4 h-100">
                        <div class="d-flex align-items-center gap-3">
                            <span class="fs-1 text-success opacity-50"><i class="fas fa-traffic-light"></i></span>
                            <div>
                                <h6 class="text-muted mb-1">التحقق الميداني</h6>
                                <p class="mb-0 small text-muted">فحص الحجوزات الفعلية ومطابقتها</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-4 border-start border-warning border-4 h-100">
                        <div class="d-flex align-items-center gap-3">
                            <span class="fs-1 text-warning opacity-50"><i class="fas fa-wallet"></i></span>
                            <div>
                                <h6 class="text-muted mb-1">المحافظ الرقمية</h6>
                                <p class="mb-0 small text-muted">تنفيذ أوامر الشحن المباشر للأرصدة</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION 2: Profile Settings -->
        <section id="profileTab" class="content-section d-none">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-xl-7">
                    <div class="card">
                        <div class="card-header bg-gradient text-dark p-4 border-0 d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="fw-bold mb-1">⚙️ إعدادات الحساب والبيانات الشخصية</h5>
                                <p class="fs-6 mb-0 text-muted">إدارة بيانات الاتصال والحساب المصرفي الخاص بك</p>
                            </div>
                            <span class="fs-1">🔒</span>
                        </div>

                        <div class="card-body p-4">
                            <div id="profileAlert" class="alert d-none" role="alert"></div>

                            <form id="profileForm">
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-secondary">الاسم الكامل (غير قابل للتعديل)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">👤</span>
                                        <input type="text" class="form-control border-start-0 ps-0 bg-light text-muted" id="profileNameDisplay" readonly>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-secondary">رقم الهاتف للتواصل</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">📞</span>
                                        <input type="text" class="form-control border-start-0 ps-0" id="profilePhoneInput" required placeholder="أدخل رقم هاتفك المحدث">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-secondary">رقم الحساب المصرفي (IBAN / رقم الحساب)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">🏦</span>
                                        <input type="text" class="form-control border-start-0 ps-0" id="profileBankInput" placeholder="أدخل بيانات حسابك المصرفي">
                                    </div>
                                </div>

                                <hr class="my-4 border-secondary border-opacity-25">

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-secondary">كلمة مرور جديدة</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">🔑</span>
                                        <input type="password" class="form-control border-start-0 ps-0" id="profilePasswordInput" placeholder="•••••••• (اتركها فارغة إذا لم ترغب بالتغيير)">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold py-3" id="updateProfileBtn">
                                    حفظ التعديلات وتحديث الجلسة 💾
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION 3: Create Driver -->
        <section id="createUserTab" class="content-section d-none">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-8">
                    <div class="card">
                        <div class="card-header bg-gradient text-dark p-4 border-0 d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="fw-bold mb-1">👤 تسجيل سائق جديد في النظام</h5>
                                <p class="fs-6 mb-0 text-muted">إنشاء حساب تشغيلي وتخصيص بيانات المركبة</p>
                            </div>
                            <span class="fs-1">🚗</span>
                        </div>

                        <div class="card-body p-4">
                            <div id="createUserAlert" class="alert d-none" role="alert"></div>

                            <form id="createUserForm">
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-secondary">الاسم الكامل</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">📝</span>
                                            <input type="text" class="form-control border-start-0 ps-0" id="userNameInput" required placeholder="أدخل اسم السائق">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-secondary">البريد الإلكتروني</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">📧</span>
                                            <input type="email" class="form-control border-start-0 ps-0" id="userEmailInput" required placeholder="name@example.com">
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-secondary">رقم الهاتف</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">📱</span>
                                            <input type="text" class="form-control border-start-0 ps-0" id="userPhoneInput" required placeholder="مثال: 0912345678">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-secondary">رقم لوحة السيارة</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">🏷️</span>
                                            <input type="text" class="form-control border-start-0 ps-0" id="userPlateInput" required placeholder="مثال: 5-12345">
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold py-3" id="createUserBtn">
                                    تسجيل السائق وتوليد بيانات الدخول 🚀
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION 4: Field Management -->
        <section id="verifyVehicleTab" class="content-section d-none">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card">
                        <div class="card-header bg-gradient text-dark p-4 d-flex justify-content-between align-items-center border-0 flex-wrap gap-3">
                            <div>
                                <h5 class="fw-bold mb-1">🚦 إدارة دخول وخروج المركبات</h5>
                                <p class="fs-6 mb-0 text-muted">التحقق الميداني من المشتركين والزوار</p>
                            </div>
                            <span class="badge bg-light text-primary fs-5 px-3 py-2 shadow-sm rounded-pill">
                                الشاغر: <span id="availableSpotsDisplay" class="badge bg-primary">جاري التحميل...</span> موقف
                            </span>
                        </div>
                        
                        <div class="card-body p-4">
                            <ul class="nav nav-pills mb-4 nav-fill gap-2" id="field-tabs">
                                <li class="nav-item">
                                    <button class="nav-link active fw-bold border-0" id="tab-users" type="button" onclick="switchFieldTab('users')">
                                        👤 المشتركون (نظام الحجز)
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link fw-bold border-0" id="tab-guests" type="button" onclick="switchFieldTab('guests')">
                                        🚶‍♂️ الزوار (دفع فوري)
                                    </button>
                                </li>
                            </ul>

                            <div class="bg-light bg-opacity-25 p-4 rounded-4 border" style="min-height: 200px;">
                                
                                <!-- User Check-in -->
                                <div id="content-users" style="display: block;">
                                    <div class="alert alert-info mb-4 border-0 rounded-3">
                                        <i class="fas fa-info-circle"></i> أدخل رقم لوحة المشترك للتحقق من حجزه لتأكيد الدخول، أو تسجيل خروجه.
                                    </div>
                                    
                                    <label class="form-label fw-bold text-secondary mb-2">رقم لوحة المشترك:</label>
                                    
                                    <div class="row g-3">
                                        <div class="col-lg-8 col-md-7">
                                            <input type="text" id="subscriberPlateInput" class="form-control form-control-lg text-center" placeholder="مثال: 5-12345">
                                        </div>
                                        <div class="col-lg-2 col-md-3">
                                            <button type="button" class="btn btn-primary btn-lg fw-bold w-100" onclick="processUserFieldAction('entry')">دخول ⬇️</button>
                                        </div>
                                        <div class="col-lg-2 col-md-2">
                                            <button type="button" class="btn btn-secondary btn-lg fw-bold w-100" onclick="processUserFieldAction('exit')">خروج ⬆️</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Guest Check-in -->
                                <div id="content-guests" style="display: none;">
                                    <div class="alert alert-warning mb-4 text-dark border-0 rounded-3">
                                        <i class="fas fa-exclamation-triangle"></i> الزوار ليس لديهم حساب. سيتم فتح تذكرة وتحديد وقت الخروج.
                                    </div>
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-5">
                                            <label class="form-label fw-bold text-secondary mb-1">رقم اللوحة:</label>
                                            <input type="text" id="guestPlateInput" class="form-control form-control-lg text-center" placeholder="أدخل رقم اللوحة">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold text-secondary mb-1">وقت الخروج المتوقع:</label>
                                            <input type="time" id="guestExpectedExitInput" class="form-control form-control-lg text-center">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-warning btn-lg fw-bold text-dark w-100" onclick="registerGuestEntry()">دخول 🎫</button>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-danger btn-lg fw-bold w-100" onclick="processGuestExit()">خروج 💰</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION 5: Instant Wallet Recharge -->
        <section id="rechargeWalletTab" class="content-section d-none">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-xl-7">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-gradient text-dark p-4 border-0 d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="fw-bold mb-1">💳 الشحن الفوري للمحفظة الرقمية</h5>
                                <p class="fs-6 mb-0 text-muted">إضافة رصيد نقاط مباشرة إلى حساب السائق</p>
                            </div>
                            <span class="fs-1">⚡</span>
                        </div>

                        <div class="card-body p-4">
                            <form id="rechargeWalletForm">
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-secondary mb-2">👤 المستفيد (معرف الحساب - Account ID)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">ID</span>
                                        <input type="number" class="form-control border-start-0 ps-0" id="targetUserIdInput" required placeholder="أدخل رقم ID الخاص بالسائق (مثال: 1)">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-secondary mb-3 d-block">✨ باقات الشحن السريعة المقترحة</label>
                                    <div class="row g-2">
                                        <div class="col-4">
                                            <input type="radio" class="btn-check fast-amount-radio" name="fastPoints" id="points5" autocomplete="off" onchange="updateCustomAmount(5)">
                                            <label class="btn btn-outline-warning w-100 p-3 rounded-3 text-dark fw-bold border-2" for="points5">
                                                <span class="fs-4 d-block">5</span>
                                                <small class="text-muted">نقاط</small>
                                            </label>
                                        </div>
                                        <div class="col-4">
                                            <input type="radio" class="btn-check fast-amount-radio" name="fastPoints" id="points10" autocomplete="off" onchange="updateCustomAmount(10)">
                                            <label class="btn btn-outline-warning w-100 p-3 rounded-3 text-dark fw-bold border-2" for="points10">
                                                <span class="fs-4 d-block">10</span>
                                                <small class="text-muted">نقاط</small>
                                            </label>
                                        </div>
                                        <div class="col-4">
                                            <input type="radio" class="btn-check fast-amount-radio" name="fastPoints" id="points15" autocomplete="off" onchange="updateCustomAmount(15)">
                                            <label class="btn btn-outline-warning w-100 p-3 rounded-3 text-dark fw-bold border-2" for="points15">
                                                <span class="fs-4 d-block">15</span>
                                                <small class="text-muted">نقطة</small>
                                            </label>
                                        </div>
                                        <div class="col-4">
                                            <input type="radio" class="btn-check fast-amount-radio" name="fastPoints" id="points20" autocomplete="off" onchange="updateCustomAmount(20)">
                                            <label class="btn btn-outline-warning w-100 p-3 rounded-3 text-dark fw-bold border-2" for="points20">
                                                <span class="fs-4 d-block">20</span>
                                                <small class="text-muted">نقطة</small>
                                            </label>
                                        </div>
                                        <div class="col-4">
                                            <input type="radio" class="btn-check fast-amount-radio" name="fastPoints" id="points25" autocomplete="off" onchange="updateCustomAmount(25)">
                                            <label class="btn btn-outline-warning w-100 p-3 rounded-3 text-dark fw-bold border-2" for="points25">
                                                <span class="fs-4 d-block">25</span>
                                                <small class="text-muted">نقطة</small>
                                            </label>
                                        </div>
                                        <div class="col-4">
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

                                <button type="submit" class="btn btn-warning btn-lg w-100 fw-bold py-3" id="rechargeWalletBtn">
                                    تنفيذ الشحن وإضافة الرصيد للمحفظة 🚀
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION 6: Wire Transfer Review -->
        <section id="transferRequestsTab" class="content-section d-none">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>📄 طلبات الشحن بانتظار المراجعة</span>
                    <button class="btn btn-sm btn-primary rounded-pill px-3" onclick="loadPendingRequests()">تحديث 🔄</button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover text-center align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID المستخدم</th>
                                    <th>الاسم</th>
                                    <th>المبلغ (نقاط)</th>
                                    <th>الإيصال</th>
                                    <th>الإجراء</th>
                                </tr>
                            </thead>
                            <tbody id="pendingRequestsTable">
                                <tr>
                                    <td colspan="5" class="text-muted py-5">لا توجد طلبات شحن معلقة حالياً.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
    const fetchHeaders = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
    };
    const storedUserData = JSON.parse(localStorage.getItem('userData'));
    const currentEmployeeId = storedUserData ? (storedUserData.id || storedUserData.account_id) : null;

    function getValidEmployeeId() {
        try {
            const data = JSON.parse(localStorage.getItem('userData'));
            return data ? (data.accountId || (data.profile && data.profile.account_id)) : null;
        } catch(e) { 
            return null; 
        }
    }

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

    document.addEventListener('DOMContentLoaded', function() {
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

            if (window.innerWidth <= 991) {
                document.getElementById('dashboardSidebar').classList.remove('active');
            }

            if (sectionId === 'profileTab') {
                loadProfileData();
            }
            if (sectionId === 'transferRequestsTab') {
                loadPendingRequests();
            }
        } catch (exception) {
            console.error("خطأ أثناء التبديل بين الأقسام", exception);
        }
    }

    function switchFieldTab(target) {
        document.getElementById('tab-users').classList.remove('active');
        document.getElementById('tab-guests').classList.remove('active');
        document.getElementById('content-users').style.display = 'none';
        document.getElementById('content-guests').style.display = 'none';
        
        if(target === 'users') {
            document.getElementById('tab-users').classList.add('active');
            document.getElementById('content-users').style.display = 'block';
        } else {
            document.getElementById('tab-guests').classList.add('active');
            document.getElementById('content-guests').style.display = 'block';
        }
    }

    function loadProfileData() {
        try {
            const userDataString = localStorage.getItem('userData');
            if (userDataString) {
                const userData = JSON.parse(userDataString);
                
                document.getElementById('profileNameDisplay').value = userData.name;
                document.getElementById('profilePhoneInput').value = userData.phone || '';
                
                if (userData.profile && userData.profile.bank_account_number) {
                    document.getElementById('profileBankInput').value = userData.profile.bank_account_number;
                }
            }
        } catch (exception) {
            console.error("خطأ أثناء تحميل بيانات الملف الشخصي", exception);
        }
    }

    function logoutEmployee() {
        try {
            Swal.fire({
                title: 'هل تريد تسجيل الخروج؟',
                text: "سيتم إنهاء جلستك الحالية في النظام",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#0071e3',
                cancelButtonColor: '#8e8e93',
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

    document.getElementById('createUserForm').addEventListener('submit', async function(event) {
        try {
            event.preventDefault();
            
            const userNameValue = document.getElementById('userNameInput').value;
            const userEmailValue = document.getElementById('userEmailInput').value;
            const userPhoneValue = document.getElementById('userPhoneInput').value;
            const userPlateValue = document.getElementById('userPlateInput').value;
            const submitButton = document.getElementById('createUserBtn');

            submitButton.disabled = true;

            try {
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
                    Swal.fire({
                        icon: 'success',
                        title: 'تم تسجيل السائق بنجاح',
                        html: `
                            <div class="text-end mt-3">
                                <p><strong>ID الحساب:</strong> ${responseData.accountId}</p>
                                <p class="text-success">✅ تم إرسال رمز الدخول العشوائي إلى بريد السائق بنجاح.</p>
                            </div>
                        `,
                        confirmButtonColor: '#0071e3'
                    });
                    document.getElementById('createUserForm').reset();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطأ في التسجيل',
                        text: responseData.message || 'تأكد من عدم تكرار البريد الإلكتروني.',
                        confirmButtonColor: '#ff3b30'
                    });
                }
            } catch (networkError) {
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ في الاتصال',
                    text: 'تعذر الوصول للخادم المحلي.',
                    confirmButtonColor: '#ff3b30'
                });
            } finally {
                submitButton.disabled = false;
            }
        } catch (exception) {
            console.error(exception);
        }
    });

    async function updateParkingCapacity() {
        const employeeId = getValidEmployeeId(); 
        if (!employeeId) return;

        try {
            const response = await fetch(`/field/parking/capacity?user_id=${employeeId}`);
            const data = await response.json();
            document.getElementById('availableSpotsDisplay').innerText = data.capacity ;
        } catch (error) {
            console.error('خطأ في جلب الشواغر:', error);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateParkingCapacity();
    });

    async function registerGuestEntry() {
        const plateInputElement = document.getElementById('guestPlateInput');
        const expectedExitElement = document.getElementById('guestExpectedExitInput');

        if (!plateInputElement || !plateInputElement.value.trim()) {
            Swal.fire({ icon: 'warning', title: 'تنبيه', text: 'يرجى إدخال رقم اللوحة.' }); return;
        }
        if (!expectedExitElement || !expectedExitElement.value) {
            Swal.fire({ icon: 'warning', title: 'تنبيه', text: 'يرجى تحديد وقت الخروج المتوقع.' }); return;
        }

        Swal.fire({ title: 'جاري تسجيل الدخول...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

        try {
            const response = await fetch('/field/guest/entry', {
                method: 'POST',
                headers: fetchHeaders,
                credentials: 'same-origin',
                body: JSON.stringify({ 
                    plate_number: plateInputElement.value.trim(),
                    expected_exit_time: expectedExitElement.value,
                    user_id: getValidEmployeeId()
                })
            });
            const data = await response.json();
            if (response.ok && data.status === 'success') {
                Swal.fire({ icon: 'success', title: 'تم الدخول', text: data.message });
                plateInputElement.value = ''; expectedExitElement.value = ''; updateCapacityUI(-1);
                updateParkingCapacity();
            } else { throw new Error(data.message || 'فشل تسجيل الدخول.'); }
        } catch (error) { Swal.fire({ icon: 'error', title: 'خطأ', text: error.message }); }
    }

    async function processGuestExit() {
        const plateInputElement = document.getElementById('guestPlateInput');
        if (!plateInputElement || !plateInputElement.value.trim()) {
            Swal.fire({ icon: 'warning', title: 'تنبيه', text: 'يرجى إدخال رقم اللوحة لحساب التكلفة.' }); return;
        }

        const plateNumber = plateInputElement.value.trim();
        Swal.fire({ title: 'جاري حساب التكلفة...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

        try {
            const response = await fetch('/field/guest/exit', {
                method: 'POST',
                headers: fetchHeaders,
                credentials: 'same-origin',
                body: JSON.stringify({ plate_number: plateNumber, user_id:getValidEmployeeId() })
            });
            const data = await response.json();
            if (response.ok && data.status === 'success') {
                Swal.fire({
                    icon: 'info', title: 'فاتورة خروج زائر 🧾',
                    html: `<div class="text-end fs-5 mt-3"><p><b>رقم اللوحة:</b> <span class="text-primary" dir="ltr">${plateNumber}</span></p><p><b>المدة المحسوبة:</b> ${data.duration} ساعة</p><hr><h3 class="text-danger">المطلوب دفعه: ${data.cost} د.ل</h3></div>`,
                    confirmButtonText: 'تم استلام المبلغ نقداً ✔️', confirmButtonColor: '#34c759'
                }).then(() => { plateInputElement.value = ''; updateCapacityUI(1); });
                updateParkingCapacity();
            } else { throw new Error(data.message || 'فشل حساب التكلفة.'); }
        } catch (error) { Swal.fire({ icon: 'error', title: 'خطأ', text: error.message }); }
    }

    function updateCapacityUI(change) {
        const capacityElement = document.getElementById('availableSpotsCount');
        if (capacityElement && !isNaN(capacityElement.innerText)) {
            capacityElement.innerText = parseInt(capacityElement.innerText) + change;
        }
    }

    function processUserFieldAction(actionType) {
        const plateInputElement = document.getElementById('subscriberPlateInput'); 
        if (!plateInputElement || !plateInputElement.value.trim()) {
            Swal.fire({ icon: 'warning', title: 'تنبيه', text: 'يرجى إدخال رقم اللوحة.' });
            return;
        }

        const plateNumber = plateInputElement.value.trim();

        if (actionType === 'entry') {
            executeBackendRequest(plateNumber, 'entry', null);
        } else {
            Swal.fire({
                title: 'تأكيد الخروج',
                text: 'هل أنت متأكد من تسجيل الخروج؟',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'نعم، خروج ⬆️',
                confirmButtonColor: '#ff3b30',
                cancelButtonColor: '#8e8e93'
            }).then((result) => {
                if (result.isConfirmed) executeBackendRequest(plateNumber, 'exit', null);
            });
        }
    }

    async function executeBackendRequest(plateNumber, actionType, expectedTime) {
        let employeeId = null;
        try {
            const rawData = localStorage.getItem('userData');
            if (!rawData) { Swal.fire({ icon: 'warning', title: 'تنبيه', text: 'يرجى تسجيل الدخول من جديد.' }); return; }
            const storedUserData = JSON.parse(rawData);
            employeeId = storedUserData.accountId || (storedUserData.profile && storedUserData.profile.account_id);
            if (!employeeId) { Swal.fire({ icon: 'error', title: 'خطأ', text: 'المعرف مفقود.' }); return; }
        } catch (e) { console.error(e); }

        Swal.fire({ title: 'جاري التحقق...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

        try {
            const response = await fetch('/field/user/action', {
                method: 'POST',
                headers: fetchHeaders,
                credentials: 'same-origin',
                body: JSON.stringify({
                    plate_number: plateNumber,
                    action_type: actionType,
                    expected_exit_time: expectedTime,
                    user_id: employeeId 
                })
            });
            
            const data = await response.json();

            if (data.status === 'requires_time') {
                Swal.fire({
                    title: 'حجز مبدئي',
                    html: `<b>${data.message}</b><br><br>الرجاء إدخال <b>الوقت المتوقع</b>:`,
                    input: 'time',
                    inputAttributes: { required: true },
                    showCancelButton: true,
                    confirmButtonText: 'تحقق و الدخول ⬇️',
                    confirmButtonColor: '#0071e3',
                    cancelButtonColor: '#8e8e93'
                }).then((result) => {
                    if (result.isConfirmed && result.value) {
                        executeBackendRequest(plateNumber, 'entry', result.value);
                    }
                });
                return;
            }

            if (response.ok && data.status === 'success') {
                Swal.fire({ icon: 'success', title: 'نجاح 🎉', text: data.message });
                document.getElementById('subscriberPlateInput').value = ''; 
                updateParkingCapacity();
            } else {
                throw new Error(data.message || 'فشلت العملية.');
            }
        } catch (error) {
            Swal.fire({ icon: 'error', title: 'تنبيه', text: error.message });
        }
    }

    document.getElementById('profileForm').addEventListener('submit', async function(event) {
        try {
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
                    if (alertDiv) alertDiv.classList.add('d-none');
                    
                    userData.phone = result.updatedData.phone;
                    if (userData.profile) userData.profile.bank_account_number = result.updatedData.bankAccountNumber;
                    localStorage.setItem('userData', JSON.stringify(userData));

                    Swal.fire({
                        icon: 'success',
                        title: 'تم التحديث!',
                        text: 'تم حفظ بياناتك الشخصية بنجاح.',
                        confirmButtonColor: '#0071e3'
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
                    confirmButtonColor: '#ff3b30'
                });
            } finally {
                updateBtn.disabled = false;
            }
        } catch (exception) {
            console.error(exception);
        }
    });

    function updateCustomAmount(amount) {
        const amountInput = document.getElementById('pointsAmountInput');
        if (amountInput) {
            amountInput.value = amount;
        }
    }

    function clearRadioSelection() {
        const radios = document.querySelectorAll('.fast-amount-radio');
        radios.forEach(radio => {
            radio.checked = false;
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const formElement = document.getElementById('rechargeWalletForm');
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

                        const response = await fetch('/api/recharges/direct', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                userId: targetUserIdValue,
                                amount: pointsAmountValue,
                                employee_id: getValidEmployeeId()
                            })
                        });

                        const resultData = await response.json();

                        if (response.ok && resultData.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'تم الشحن بنجاح! 💳',
                                html: `تمت إضافة <b>${pointsAmountValue}</b> نقاط إلى رصيد السائق (ID: ${targetUserIdValue}) فوراً.`,
                                confirmButtonColor: '#0071e3'
                            });

                            formElement.reset();
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
                            confirmButtonColor: '#ff3b30'
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

    async function verifyRechargeRequest(requestIdValue, actionType) {
        try {
            let rejectionReasonValue = '';

            if (actionType === 'reject') {
                const { value: textValue, isConfirmed: isRejectConfirmed } = await Swal.fire({
                    title: 'رفض طلب الشحن',
                    input: 'textarea',
                    inputLabel: 'سبب الرفض (سيتم إرساله للسائق كإشعار)',
                    inputPlaceholder: 'أدخل سبب الرفض هنا (مثال: الصورة غير واضحة، المبلغ غير صحيح)...',
                    showCancelButton: true,
                    confirmButtonColor: '#ff3b30',
                    cancelButtonColor: '#8e8e93',
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

                if (!isRejectConfirmed) return;
                rejectionReasonValue = textValue;
            } else {
                const { isConfirmed: isApproveConfirmed } = await Swal.fire({
                    title: 'اعتماد الشحن',
                    text: 'هل أنت متأكد من صحة الإيصال وإضافة النقاط لمحفظة السائق؟',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#34c759',
                    cancelButtonColor: '#8e8e93',
                    confirmButtonText: 'نعم، اعتماد وإضافة النقاط',
                    cancelButtonText: 'تراجع'
                });
                
                if (!isApproveConfirmed) return;
            }

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
                    confirmButtonColor: '#0071e3'
                });
                
                loadPendingRequests();
            } else {
                throw new Error(resultData.message || 'حدث خطأ أثناء معالجة الطلب.');
            }
        } catch (exception) {
            Swal.fire('خطأ', exception.message, 'error');
            console.error(exception);
        }
    }
    </script>
</body>
</html>