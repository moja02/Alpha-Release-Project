<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpotLy - Sign In</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
            --primary-gradient: linear-gradient(135deg, #0071e3, #00a4ff);
            --input-bg: rgba(255, 255, 255, 0.5);
            --input-border: rgba(0, 0, 0, 0.08);
            --font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
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
                --primary-gradient: linear-gradient(135deg, #2997ff, #0071e3);
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
        .login-card {
            width: 100%;
            max-width: 440px;
            padding: 2.75rem;
            border-radius: 28px;
            background: var(--glass-bg);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid var(--glass-border);
            box-shadow: 0 25px 50px -12px var(--glass-shadow);
            transform: translateY(0);
            transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            animation: cardAppear 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes cardAppear {
            0% { opacity: 0; transform: translateY(30px) scale(0.98); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }

        .brand-logo {
            font-size: 2.2rem;
            font-weight: 700;
            letter-spacing: -1px;
            background: linear-gradient(135deg, var(--text-color) 30%, var(--text-muted));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-align: center;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .brand-subtitle {
            font-size: 0.9rem;
            color: var(--text-muted);
            text-align: center;
            margin-bottom: 2.25rem;
            font-weight: 400;
        }

        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.5rem;
            padding-left: 2px;
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

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            border-radius: 12px;
            padding: 0.85rem;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 4px 12px rgba(0, 113, 227, 0.25);
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 113, 227, 0.35);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-primary:disabled {
            background: var(--text-muted);
            box-shadow: none;
        }

        .forgot-link {
            font-size: 0.85rem;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .forgot-link:hover {
            text-decoration: underline;
            color: var(--primary-color);
        }

        /* Subtle loading spinner overlay on button */
        .spinner-inline {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
            vertical-align: middle;
            margin-right: 8px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
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

    <div class="login-card">
        <h1 class="brand-logo">
            <span>🚗</span> SpotLy
        </h1>
        <p class="brand-subtitle">Premium Intelligent Parking Platform</p>

        <form id="loginForm">
            <div class="mb-3">
                <label for="emailInput" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="emailInput" required placeholder="name@example.com">
            </div>
            
            <div class="mb-3">
                <label for="passwordInput" class="form-label">Password</label>
                <input type="password" class="form-control" id="passwordInput" required placeholder="••••••••">
            </div>

            <div class="text-end mt-1 mb-4">
                <a href="/forgot-password" class="forgot-link">Forgot Password?</a>
            </div>

            <button type="submit" class="btn btn-primary w-100" id="submitButton">
                <span>Login</span>
            </button>
        </form>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(event) {
            event.preventDefault();

            const emailValue = document.getElementById('emailInput').value;
            const passwordValue = document.getElementById('passwordInput').value;
            const submitBtn = document.getElementById('submitButton');

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-inline"></span> Verifying...';

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

                if (response.ok && responseData.status === 'success') {
                    localStorage.setItem('authToken', responseData.token);
                    localStorage.setItem('userData', JSON.stringify(responseData.accountData));

                    Swal.fire({
                        icon: 'success',
                        title: 'Welcome Back!',
                        text: `Logged in successfully as ${responseData.accountData.name}`,
                        timer: 1000,
                        showConfirmButton: false,
                        background: 'rgba(255, 255, 255, 0.9)',
                        backdrop: 'rgba(0,0,0,0.2)'
                    }).then(() => {
                        if (responseData.redirect) {
                            window.location.replace(responseData.redirect);
                        } else {
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
                    Swal.fire({
                        icon: 'error',
                        title: 'Login Failed',
                        text: responseData.message || 'Invalid credentials.',
                        confirmButtonColor: '#0071e3'
                    });
                }

            } catch (error) {
                console.error('Login Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: 'Please check your connection or try again later.',
                    confirmButtonColor: '#ff3b30'
                });
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Login';
            }
        });
    </script>
</body>
</html>