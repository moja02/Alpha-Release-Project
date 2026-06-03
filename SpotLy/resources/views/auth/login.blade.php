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
                const response = await fetch('/web-login', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        email: emailValue,
                        password: passwordValue
                    })
                });

                const responseData = await response.json();

                // التحقق من نجاح الاستجابة
                if (response.ok && responseData.status === 'success') {
                    
                    // 1. حفظ بيانات المستخدم
                    localStorage.setItem('authToken', responseData.token);
                    localStorage.setItem('userData', JSON.stringify(responseData.accountData));

                    // 2. إظهار الإشعار والانتظار حتى ينتهي
                    Swal.fire({
                        icon: 'success',
                        title: 'Welcome Back!',
                        text: `Logged in successfully as ${responseData.accountData.name}`,
                        timer: 1000,
                        showConfirmButton: false
                    }).then(() => {
                        // 3. الانتقال يتم فقط بعد إغلاق الإشعار أو انتهاء المؤقت
                        if (responseData.redirect) {
                            window.location.replace(responseData.redirect);
                        } else {
                            // مسار احتياطي
                            const userRole = responseData.accountData.role;
                            const routes = {
                                'developer': '/developer/dashboard',
                                'employee': '/employee-dashboard',
                                'user': '/home'
                            };
                            window.location.replace(routes[userRole] || '/home');
                        }
                    });

                } else {
                    // إظهار الخطأ
                    Swal.fire({
                        icon: 'error',
                        title: 'Login Failed',
                        text: responseData.message || 'Invalid credentials.',
                        confirmButtonColor: '#d33'
                    });
                }

            } catch (error) {
                // التعامل مع أخطاء الشبكة أو توقف الخادم
                console.error('Login Error:', error); // طباعة الخطأ الفعلي في المتصفح لمعرفته
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