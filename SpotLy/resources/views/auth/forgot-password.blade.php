<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpotLy - استعادة كلمة المرور</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-gradient-1: #e2e8f0;
            --bg-gradient-2: #cbd5e1;
            --glass-bg: rgba(255, 255, 255, 0.4);
            --glass-border: rgba(255, 255, 255, 0.45);
            --glass-shadow: rgba(31, 38, 135, 0.08);
            --text-color: #1d1d1f;
            --text-muted: #6e6e73;
            --primary-color: #0071e3;
            --success-color: #34c759;
            --font-family: 'Inter', system-ui, -apple-system, sans-serif;
            --input-bg: rgba(255, 255, 255, 0.5);
            --input-border: rgba(0, 0, 0, 0.08);
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg-gradient-1: #0a0a0c;
                --bg-gradient-2: #18181c;
                --glass-bg: rgba(28, 28, 30, 0.45);
                --glass-border: rgba(255, 255, 255, 0.08);
                --glass-shadow: rgba(0, 0, 0, 0.5);
                --text-color: #f5f5f7;
                --text-muted: #86868b;
                --primary-color: #2997ff;
                --input-bg: rgba(255, 255, 255, 0.04);
                --input-border: rgba(255, 255, 255, 0.08);
            }
        }

        body {
            background-color: var(--bg-gradient-2);
            color: var(--text-color);
            font-family: var(--font-family);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin: 0;
            padding: 20px;
        }

        /* Ambient Fluid Background */
        .ambient-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: -1;
            overflow: hidden;
            background: linear-gradient(180deg, var(--bg-gradient-1), var(--bg-gradient-2));
        }

        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.45;
            animation: move 20s infinite alternate ease-in-out;
        }

        .blob-1 {
            width: 500px;
            height: 500px;
            background: #ff007f;
            top: -100px;
            left: -100px;
        }

        .blob-2 {
            width: 600px;
            height: 600px;
            background: #0071e3;
            bottom: -150px;
            right: -100px;
            animation-duration: 28s;
        }

        .blob-3 {
            width: 350px;
            height: 350px;
            background: #00f6ff;
            top: 25%;
            left: 35%;
            animation-duration: 16s;
        }

        @keyframes move {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(80px, 60px) scale(1.15); }
            100% { transform: translate(-40px, -30px) scale(0.9); }
        }

        /* Premium Glassmorphism Card */
        .auth-card {
            width: 100%;
            max-width: 460px;
            border-radius: 28px;
            background: var(--glass-bg);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid var(--glass-border);
            box-shadow: 0 25px 50px -12px var(--glass-shadow);
            overflow: hidden;
            animation: cardAppear 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes cardAppear {
            0% { opacity: 0; transform: translateY(30px) scale(0.98); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }

        .card-header-glass {
            padding: 2.25rem 2.25rem 1.5rem;
            border-bottom: 1px solid var(--glass-border);
        }

        .card-body-glass {
            padding: 2.25rem;
        }

        .brand-title {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-subtitle {
            font-size: 0.9rem;
            color: var(--text-muted);
            line-height: 1.5;
            font-weight: 400;
        }

        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.5rem;
            padding-right: 2px;
        }

        .form-control {
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 12px;
            color: var(--text-color);
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);
            text-align: right;
        }

        .form-control:focus {
            background: var(--input-bg);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(0, 113, 227, 0.15);
            color: var(--text-color);
            outline: none;
        }

        .form-control::placeholder {
            color: var(--text-muted);
            opacity: 0.6;
        }

        .input-group-text-glass {
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            color: var(--text-color);
            border-radius: 0 12px 12px 0;
            padding: 0.75rem 1rem;
            font-size: 1.1rem;
        }

        .form-control-with-icon {
            border-radius: 12px 0 0 12px !important;
            border-right: none !important;
        }

        .btn-primary-glass {
            background: linear-gradient(135deg, var(--primary-color), #00a4ff);
            border: none;
            color: white;
            border-radius: 12px;
            padding: 0.85rem;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 4px 12px rgba(0, 113, 227, 0.25);
            width: 100%;
        }

        .btn-primary-glass:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 113, 227, 0.35);
            color: white;
        }

        .btn-success-glass {
            background: linear-gradient(135deg, var(--success-color), #30d158);
            border: none;
            color: white;
            border-radius: 12px;
            padding: 0.85rem;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 4px 12px rgba(52, 199, 89, 0.25);
            width: 100%;
        }

        .btn-success-glass:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(52, 199, 89, 0.35);
            color: white;
        }

        .btn-glass:active {
            transform: translateY(0);
        }

        .return-link {
            font-size: 0.85rem;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: opacity 0.2s;
        }

        .return-link:hover {
            opacity: 0.8;
            text-decoration: underline;
        }

        /* Subtle loading spinner */
        .spinner-inline {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
            vertical-align: middle;
            margin-left: 8px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .alert-glass {
            background: rgba(52, 199, 89, 0.12);
            border: 1px solid rgba(52, 199, 89, 0.24);
            color: var(--text-color);
            border-radius: 12px;
            padding: 1rem;
            font-size: 0.9rem;
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

    <div class="auth-card">
        <div class="card-header-glass">
            <h5 class="brand-title">🔒 استعادة كلمة المرور</h5>
            <p class="brand-subtitle mb-0">إعادة تعيين حسابك بأمان عبر التحقق الثنائي</p>
        </div>

        <div class="card-body-glass">
            
            <div id="requestOtpSection">
                <p class="text-secondary mb-4 small">أدخل عنوان بريدك الإلكتروني المسجل لدينا، وسنقوم بإرسال رمز تحقق مؤقت (OTP) يتكون من 6 أرقام.</p>
                
                <form id="requestOtpForm">
                    <div class="mb-4">
                        <label class="form-label">البريد الإلكتروني</label>
                        <div class="input-group">
                            <span class="input-group-text-glass">📧</span>
                            <input type="email" class="form-control form-control-with-icon" id="userEmailInput" required placeholder="name@example.com">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary-glass btn-glass py-3" id="sendOtpBtn">
                        إرسال رمز التحقق 🚀
                    </button>
                </form>
                
                <div class="text-center mt-4">
                    <a href="/login" class="return-link">⬅️ العودة لصفحة تسجيل الدخول</a>
                </div>
            </div>

            <div id="resetPasswordSection" class="d-none">
                <div class="alert-glass mb-4">
                    ✅ <strong>تم الإرسال:</strong> يرجى إدخال رمز الـ OTP الذي وصلك للتو بالإضافة إلى كلمة المرور الجديدة.
                </div>

                <form id="resetPasswordForm">
                    <input type="hidden" id="targetEmailHiddenInput">

                    <div class="mb-3">
                        <label class="form-label">رمز التحقق (OTP)</label>
                        <div class="input-group">
                            <span class="input-group-text-glass">🔢</span>
                            <input type="text" class="form-control form-control-with-icon text-center fw-bold fs-4" id="otpCodeInput" required maxlength="6" placeholder="••••••" style="direction: ltr;">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">كلمة المرور الجديدة</label>
                        <div class="input-group">
                            <span class="input-group-text-glass">🔑</span>
                            <input type="password" class="form-control form-control-with-icon" id="newPasswordInput" required minlength="6" placeholder="أدخل كلمة المرور الجديدة">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success-glass btn-glass py-3" id="resetPasswordBtn">
                        تأكيد وتغيير كلمة المرور 💾
                    </button>
                </form>
            </div>

        </div>
    </div>

    <script>
        // ---  معالجة إرسال طلب توليد وإرسال رمز الـ OTP ---
        document.getElementById('requestOtpForm').addEventListener('submit', async function(event) {
            
            try {
                event.preventDefault();
                
                const emailInputValue = document.getElementById('userEmailInput').value;
                const sendBtnElement = document.getElementById('sendOtpBtn');
                
                sendBtnElement.disabled = true;

                try {
                    Swal.fire({
                        title: 'جاري إرسال الرمز...',
                        text: 'يرجى الانتظار لحين توليد رمز الـ OTP وإرساله لبريدك',
                        allowOutsideClick: false,
                        didOpen: () => {
                            try {
                                Swal.showLoading();
                            } catch (innerException) {
                                console.error(innerException);
                            }
                        }
                    });

                    const response = await fetch('/api/auth/forgot-password/send-otp', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ email: emailInputValue })
                    });

                    const responseData = await response.json();

                    if (response.ok) {
                        Swal.fire({
                            icon: 'success',
                            title: 'تم الإرسال!',
                            text: 'تم إرسال رمز OTP المكون من 6 أرقام إلى بريدك الإلكتروني بنجاح.',
                            confirmButtonColor: '#0071e3'
                        });

                        document.getElementById('requestOtpSection').classList.add('d-none');
                        document.getElementById('resetPasswordSection').classList.remove('d-none');
                        
                        document.getElementById('targetEmailHiddenInput').value = emailInputValue;
                    } else {
                        throw new Error(responseData.message || 'البريد الإلكتروني غير مسجل في النظام.');
                    }
                } catch (apiError) {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطأ',
                        text: apiError.message,
                        confirmButtonColor: '#d33'
                    });
                } finally {
                    sendBtnElement.disabled = false;
                }
            } catch (exception) {
                console.error(exception);
            }
        });

        // ---  معالجة إرسال الرمز وكلمة المرور الجديدة للاعتماد ---
        document.getElementById('resetPasswordForm').addEventListener('submit', async function(event) {
            try {
                event.preventDefault();
                
                const targetEmailValue = document.getElementById('targetEmailHiddenInput').value;
                const otpCodeValue = document.getElementById('otpCodeInput').value;
                const newPasswordValue = document.getElementById('newPasswordInput').value;
                const resetBtnElement = document.getElementById('resetPasswordBtn');

                resetBtnElement.disabled = true;

                try {
                    Swal.fire({
                        title: 'جاري التحقق وإعادة التعيين...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            try {
                                Swal.showLoading();
                            } catch (innerException) {
                                console.error(innerException);
                            }
                        }
                    });

                    const response = await fetch('/api/auth/forgot-password/reset', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            email: targetEmailValue,
                            otpCode: otpCodeValue,
                            newPassword: newPasswordValue
                        })
                    });

                    const responseData = await response.json();

                    if (response.ok) {
                        Swal.fire({
                            icon: 'success',
                            title: 'تم تغيير كلمة المرور بنجاح! 🔒',
                            text: 'يمكنك الآن تسجيل الدخول باستخدام كلمة المرور الجديدة.',
                            confirmButtonColor: '#0071e3'
                        }).then(() => {
                            try {
                                window.location.href = '/login';
                            } catch (innerException) {
                                console.error(innerException);
                            }
                        });
                    } else {
                        throw new Error(responseData.message || 'رمز التحقق غير صحيح أو منتهي الصلاحية.');
                    }
                } catch (apiError) {
                    Swal.fire({
                        icon: 'error',
                        title: 'فشل إعادة التعيين',
                        text: apiError.message,
                        confirmButtonColor: '#d33'
                    });
                } finally {
                    resetBtnElement.disabled = false;
                }
            } catch (exception) {
                console.error(exception);
            }
        });
    </script>
</body>
</html>