<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل دخول الإدارة - كن تاجرا</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Cairo', sans-serif !important; }
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 50%, #1e293b 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: #fff;
            border-radius: 1.5rem;
            box-shadow: 0 25px 60px rgba(0,0,0,.3);
            padding: 3rem 2.5rem;
            width: 100%;
            max-width: 420px;
        }
        .brand-icon {
            width: 70px; height: 70px;
            background: linear-gradient(135deg, #2563EB, #10B981);
            border-radius: 1rem;
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem; margin: 0 auto 1rem;
        }
    </style>
</head>
<body>
<div class="login-card">
    <div class="brand-icon">🏪</div>
    <h4 class="text-center fw-bold mb-1">كن تاجرا</h4>
    <p class="text-center text-muted mb-4 small">لوحة إدارة المنصة</p>

    @if ($errors->any())
        <div class="alert alert-danger border-0 mb-3">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="/admin/login">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-bold small">رقم الهاتف</label>
            <input type="text" name="phone" class="form-control form-control-lg"
                   placeholder="07xxxxxxxxx" value="{{ old('phone') }}" autofocus required>
        </div>
        <div class="mb-4">
            <label class="form-label fw-bold small">كلمة المرور</label>
            <input type="password" name="password" class="form-control form-control-lg"
                   placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 btn-lg fw-bold">
            دخول لوحة التحكم
        </button>
    </form>
</div>
</body>
</html>
