<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpotLy - استعادة كلمة المرور</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #f4f6f9;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, sans-serif;
        }
        .auth-card {
            width: 100%;
            max-width: 450px;
        }
    </style>
</head>
<body>

    <div class="auth-card">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-gradient bg-primary text-white p-4 border-0 d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="fw-bold mb-1">🔒 استعادة كلمة المرور</h5>
                    <p class="fs-6 mb-0 text-white text-opacity-75">إعادة تعيين حسابك بأمان عبر التحقق الثنائي</p>
                </div>
                <span class="fs-1">✉️</span>
            </div>

            <div class="card-body p-4">
                
                <div id="requestOtpSection">
                    <p class="text-secondary mb-4">أدخل عنوان بريدك الإلكتروني المسجل لدينا، وسنقوم بإرسال رمز تحقق مؤقت (OTP) يتكون من 6 أرقام.</p>
                    
                    <form id="requestOtpForm">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary">البريد الإلكتروني</label>
                            <div class="input-group input-group-lg shadow-none">
                                <span class="input-group-text bg-light border-end-0">📧</span>
                                <input type="email" class="form-control border-start-0 ps-0" id="userEmailInput" required placeholder="name@example.com">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm rounded-3 py-3" id="sendOtpBtn">
                            إرسال رمز التحقق 🚀
                        </button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <a href="/login" class="text-decoration-none text-primary fw-bold">⬅️ العودة لصفحة تسجيل الدخول</a>
                    </div>
                </div>

                <div id="resetPasswordSection" class="d-none">
                    <div class="alert alert-success border-0 shadow-sm mb-4">
                        ✅ <strong>تم الإرسال:</strong> يرجى إدخال رمز الـ OTP الذي وصلك للتو بالإضافة إلى كلمة المرور الجديدة.
                    </div>

                    <form id="resetPasswordForm">
                        <input type="hidden" id="targetEmailHiddenInput">

                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">رمز التحقق (OTP)</label>
                            <div class="input-group input-group-lg shadow-none">
                                <span class="input-group-text bg-light border-end-0">🔢</span>
                                <input type="text" class="form-control border-start-0 ps-0 text-center fw-bold fs-4" id="otpCodeInput" required maxlength="6" placeholder="••••••">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary">كلمة المرور الجديدة</label>
                            <div class="input-group input-group-lg shadow-none">
                                <span class="input-group-text bg-light border-end-0">🔑</span>
                                <input type="password" class="form-control border-start-0 ps-0" id="newPasswordInput" required minlength="6" placeholder="أدخل كلمة المرور الجديدة">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success btn-lg w-100 fw-bold shadow-sm rounded-3 py-3" id="resetPasswordBtn">
                            تأكيد وتغيير كلمة المرور 💾
                        </button>
                    </form>
                </div>

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
                    // إظهار مؤشر التحميل التفاعلي من SweetAlert أثناء الاتصال بالخادم
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

                    // إرسال البريد الإلكتروني لواجهة الـ API للتحقق وتوليد الرمز
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
                            confirmButtonColor: '#2c3e50'
                        });

                        // إخفاء نموذج طلب الرمز وإظهار نموذج إدخال الرمز وكلمة المرور الجديدة
                        document.getElementById('requestOtpSection').classList.add('d-none');
                        document.getElementById('resetPasswordSection').classList.remove('d-none');
                        
                        // تخزين الإيميل في الحقل المخفي لضمان تمريره تلقائياً في الخطوة التالية
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
                    // إظهار نافذة المعالجة أثناء إرسال البيانات
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

                    // إرسال بيانات التحقق وكلمة المرور الجديدة للخادم للاعتماد النهائي
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
                        // إشعار بالنجاح وتوجيه المستخدم لتسجيل الدخول بكلمة المرور الجديدة
                        Swal.fire({
                            icon: 'success',
                            title: 'تم تغيير كلمة المرور بنجاح! 🔒',
                            text: 'يمكنك الآن تسجيل الدخول باستخدام كلمة المرور الجديدة.',
                            confirmButtonColor: '#2c3e50'
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