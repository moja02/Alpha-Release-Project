<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SpotLy - نظام مواقف السيارات الذكي</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-gradient-1: #0f172a;
            --bg-gradient-2: #020617;
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.08);
            --glass-shadow: rgba(0, 0, 0, 0.5);
            --text-color: #f5f5f7;
            --text-muted: #86868b;
            --primary-color: #0071e3;
            --primary-gradient: linear-gradient(135deg, #0071e3, #00a4ff);
            --font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        body {
            background-color: var(--bg-gradient-2);
            color: var(--text-color);
            font-family: var(--font-family);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            overflow-x: hidden;
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
            filter: blur(120px);
            opacity: 0.35;
            animation: move 20s infinite alternate ease-in-out;
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
            animation-duration: 28s;
        }

        .blob-3 {
            width: 400px;
            height: 400px;
            background: #00f6ff;
            top: 25%;
            left: 35%;
            animation-duration: 16s;
        }

        @keyframes move {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(80px, 60px) scale(1.15); }
            100% { transform: translate(-40px, -30px) scale(0.95); }
        }

        .welcome-card {
            width: 100%;
            max-width: 800px;
            padding: 3.5rem 2.5rem;
            border-radius: 32px;
            background: var(--glass-bg);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border: 1px solid var(--glass-border);
            box-shadow: 0 25px 50px -12px var(--glass-shadow);
            text-align: center;
            animation: cardAppear 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes cardAppear {
            0% { opacity: 0; transform: translateY(30px) scale(0.98); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }

        .brand-logo {
            font-size: 3rem;
            font-weight: 800;
            letter-spacing: -1.5px;
            background: linear-gradient(135deg, var(--text-color) 30%, var(--text-muted));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .headline {
            font-size: 1.5rem;
            color: var(--text-color);
            margin-bottom: 1.5rem;
            font-weight: 600;
            line-height: 1.4;
        }

        .subheadline {
            font-size: 1rem;
            color: var(--text-muted);
            margin-bottom: 3rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }

        .btn-start {
            background: var(--primary-gradient);
            border: none;
            color: white;
            border-radius: 16px;
            padding: 1rem 3rem;
            font-weight: 600;
            font-size: 1.15rem;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 8px 25px rgba(0, 113, 227, 0.35);
            text-decoration: none;
            display: inline-block;
        }

        .btn-start:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0, 113, 227, 0.5);
            color: white;
        }

        .btn-start:active {
            transform: translateY(0);
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

    <div class="welcome-card">
        <div class="brand-logo">
            <span>🚗</span> SpotLy
        </div>
        <h1 class="headline">مرحباً بك في منصة إدارة مواقف السيارات الذكية</h1>
        <p class="subheadline">
            منصة متكاملة تسهل على السائقين حجز مواقف السيارات بشكل تفاعلي ذكي، وتوفر للموظفين والمدراء أدوات متطورة لمراقبة الميدان والتدقيق المالي الفوري.
        </p>

        <a href="/login" class="btn-start">
            ابدأ الآن ودخول المنصة 🚀
        </a>
    </div>

</body>
</html>
