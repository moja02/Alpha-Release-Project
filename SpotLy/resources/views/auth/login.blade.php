<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parking System - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <h3 class="text-center mb-4">🚗 SpotLy Parking</h3>

        <form id="loginForm">
            <div class="mb-3">
                <label for="emailInput" class="form-label">Email address</label>
                <input type="email" class="form-control" id="emailInput" required placeholder="name@example.com">
            </div>
            
            <div class="mb-3">
                <label for="passwordInput" class="form-label">Password</label>
                <input type="password" class="form-control" id="passwordInput" required placeholder="••••••••">
            </div>
            <div class="text-end mt-1 mb-3">
                <a href="/forgot-password" class="text-decoration-none small text-primary fw-bold">نسيت كلمة المرور؟</a>
            </div>

            <button type="submit" class="btn btn-primary w-100" id="submitButton">
                <span>Login</span>
            </button>
        </form>
    </div>

    <script>
        // الاستماع لحدث إرسال النموذج
        document.getElementById('loginForm').addEventListener('submit', async function(event) {
            event.preventDefault();

            const emailValue = document.getElementById('emailInput').value;
            const passwordValue = document.getElementById('passwordInput').value;
            const submitBtn = document.getElementById('submitButton');

            // تعطيل الزر مؤقتاً أثناء الإرسال
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Verifying...';

            
            try {
                // تعليق مضمن: إرسال البيانات إلى مسار الـ API للتحقق من الهوية
                const response = await fetch('/api/accounts/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        email: emailValue,
                        password: passwordValue
                    })
                });

                const responseData = await response.json();

                if (response.ok) {
                    // حفظ بيانات المستخدم في المتصفح
                    localStorage.setItem('authToken', responseData.token);
                    localStorage.setItem('userData', JSON.stringify(responseData.accountData));

                    const userRole = responseData.accountData.role;

                    //  إظهار إشعار SweetAlert تفاعلي عند النجاح مع مؤقت توجيه تلقائي
                    Swal.fire({
                        icon: 'success',
                        title: 'Welcome Back!',
                        text: `Logged in successfully as ${responseData.accountData.name}`,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        // التوجيه التلقائي إلى لوحة التحكم المناسبة للصلاحية
                        window.location.href = '/' + userRole + '-dashboard'; 
                    });

                } else {
                    // إظهار إشعار SweetAlert للخطأ (مثل الحساب المحظور أو كلمة المرور الخاطئة)
                    Swal.fire({
                        icon: 'error',
                        title: 'Login Failed',
                        text: responseData.message || 'Invalid credentials.',
                        confirmButtonColor: '#d33'
                    });
                }

            } catch (error) {
                // التعامل مع أخطاء الشبكة أو توقف الخادم
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: 'Please check your connection or try again later.',
                    confirmButtonColor: '#d33'
                });
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Login';
            }
        });
    </script>
</body>
</html>