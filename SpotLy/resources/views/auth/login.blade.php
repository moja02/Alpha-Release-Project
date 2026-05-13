<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parking System - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        
        <div id="alertMessage" class="alert d-none" role="alert"></div>

        <form id="loginForm">
            <div class="mb-3">
                <label for="emailInput" class="form-label">Email address</label>
                <input type="email" class="form-control" id="emailInput" required >
            </div>
            
            <div class="mb-3">
                <label for="passwordInput" class="form-label">Password</label>
                <input type="password" class="form-control" id="passwordInput" required >
            </div>

            <button type="submit" class="btn btn-primary w-100" id="submitButton">
                <span>Login</span>
            </button>
        </form>
    </div>

    <script>
        // الاستماع لحدث إرسال النموذج
        document.getElementById('loginForm').addEventListener('submit', async function(event) {
            // منع إعادة تحميل الصفحة الافتراضي
            event.preventDefault();

            
            const emailValue = document.getElementById('emailInput').value;
            const passwordValue = document.getElementById('passwordInput').value;
            const alertDiv = document.getElementById('alertMessage');
            const submitBtn = document.getElementById('submitButton');

            // إخفاء التنبيهات السابقة وتعطيل الزر مؤقتاً أثناء الإرسال
            alertDiv.classList.add('d-none');
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Verifying...';

            
            try {
                //  إرسال البيانات إلى مسار الـ API الذي برمجناه سابقاً
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

                // التحقق من حالة الرد
                if (response.ok) {
                    // عرض رسالة نجاح خضراء
                    alertDiv.className = 'alert alert-success';
                    alertDiv.innerText = 'Login successful! Redirecting...';
                    alertDiv.classList.remove('d-none');

                    //  حفظ بيانات المستخدم في المتصفح لتستخدمها لوحات التحكم
                    localStorage.setItem('authToken', responseData.token);
                    localStorage.setItem('userData', JSON.stringify(responseData.accountData));

                    // توجيه المستخدم بعد ثانيتين (يمكنكم لاحقاً تغيير الرابط للوحة التحكم الفعلية)
                    setTimeout(() => {
                        const userRole = responseData.accountData.role;
                        alert(`Welcome ${responseData.accountData.name}! You are logged in as: ${userRole}`);
                        window.location.href = '/' + userRole + '-dashboard'; 
                    }, 1500);

                } else {
                    // التعامل مع الأخطاء (مثل الباسوورد الخطأ أو الحساب المحظور 403)
                    alertDiv.className = 'alert alert-danger';
                    alertDiv.innerText = responseData.message || 'Invalid credentials.';
                    alertDiv.classList.remove('d-none');
                }

            } catch (error) {
                // عرض خطأ في حال توقف السيرفر
                alertDiv.className = 'alert alert-danger';
                alertDiv.innerText = 'Network error. Please try again later.';
                alertDiv.classList.remove('d-none');
            } finally {
                // إعادة تفعيل الزر
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Login';
            }
        });
    </script>
</body>
</html>