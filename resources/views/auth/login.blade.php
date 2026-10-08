<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GR_merch</title>
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/Logo-TAG-favicon-16x16px.png') }}?v={{ filemtime(public_path('assets/images/Logo-TAG-favicon-16x16px.png')) }}-2">
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-dark: #070d17;
            --bg-panel: rgba(16, 28, 39, 0.82);
            --bg-panel-strong: rgba(17, 27, 39, 0.98);
            --card-stroke: rgba(255, 255, 255, 0.18);
            --text-main: #f3f5f7;
            --text-soft: rgba(255,255,255,0.72);
            --red-main: #f74040;
            --red-deep: #e11a1a;
            --line-soft: rgba(255,255,255,0.18);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Toyota Type", sans-serif;
            background: url("{{ asset('assets/images/gr-background.png') }}") center center / 100% auto no-repeat fixed;
            color: var(--text-main);
            min-height: 100vh;
            overflow: hidden;
        }

        .login-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1.55fr 1fr;
            position: relative;
            overflow: hidden;
            padding: 22px 28px 20px 36px;
        }

        .login-shell::before,
        .login-shell::after {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
        }

        .login-shell::before {
            background: linear-gradient(135deg, transparent 0 60%, rgba(255, 58, 58, 0.18) 60% 66%, transparent 66% 100%);
            transform: skewX(-20deg) translateX(14%);
        }

        .login-shell::after {
            background: linear-gradient(140deg, transparent 0 76%, rgba(255,255,255,0.08) 76% 77%, transparent 77% 100%);
            transform: skewX(-20deg) translateX(14%);
        }

        .left-panel, .right-panel {
            position: relative;
            z-index: 1;
        }

        .left-panel {
            padding: 76px 24px 0 6px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        .brand-wrap {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 3;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 58px;
            padding: 30px 24px;
            width: 100%;
            border-bottom: 1px solid rgba(255,255,255,0.16);
            background: rgba(4, 10, 16, 0.38);
        }

        .brand-block {
            display: flex;
            align-items: center;
            height: 100%;
        }

        .toyota-block {
            justify-content: flex-start;
        }

        .tag-block {
            justify-content: flex-end;
        }

        .brand-wrap .toyota-mark {
            width: 104px;
            height: 36px;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }

        .brand-wrap .tag-mark {
            width: 102px;
            height: 34px;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }

        .brand-wrap .gr-mark {
            display: flex;
            flex-direction: column;
            line-height: 1;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            font-size: 18px;
        }

        .brand-wrap .gr-mark span:first-child {
            color: #fff;
            font-size: 36px;
        }

        .brand-wrap .gr-mark span:last-child {
            color: var(--red-main);
            font-size: 18px;
        }

        .brand-wrap .gr-mark .gr-red {
            color: var(--red-main);
        }

        .hero-copy {
            max-width: 560px;
            padding-left: 18px;
            margin-top: 12px;
        }

        .hero-copy h1 {
            font-size: clamp(2.5rem, 2.2vw, 4rem);
            font-weight: 800;
            letter-spacing: -0.06em;
            margin: 0 0 8px;
            color: #fff;
        }

        .hero-copy .subtitle {
            margin: 0 0 10px;
            font-size: 1.1rem;
            color: var(--text-soft);
            font-weight: 600;
        }

        .hero-copy .desc {
            max-width: 480px;
            color: rgba(255,255,255,0.72);
            font-size: 1.05rem;
            line-height: 1.7;
            margin-bottom: 18px;
        }

        .product-visual {
            position: relative;
            width: min(560px, 100%);
            height: 470px;
            margin-top: 20px;
            border-radius: 20px;
            overflow: hidden;
            background: rgba(0,0,0,0.12);
        }

        .product-visual::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(0,0,0,0.04), rgba(0,0,0,0.28));
            z-index: 1;
        }

        .product-visual .bg-image {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.95;
            filter: brightness(0.7) contrast(1.05);
        }

        .product-visual .hat, .product-visual .bottle {
            position: absolute;
            z-index: 2;
            filter: drop-shadow(0 22px 22px rgba(0,0,0,0.5));
        }

        .product-visual .hat {
            width: 330px;
            left: 20px;
            bottom: 58px;
            transform: rotate(-8deg);
        }

        .product-visual .bottle {
            width: 180px;
            right: 80px;
            bottom: 36px;
            transform: rotate(10deg);
        }

        .right-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px 16px 10px 0;
        }

        .login-card {
            width: min(100%, 430px);
            background: linear-gradient(180deg, rgba(10,15,20,0.98), rgba(5,8,12,0.95));
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 15px;
            box-shadow: 0 28px 60px rgba(0,0,0,0.28);
            backdrop-filter: blur(5px);
            padding: 20px 20px 18px;
        }
        .card-logo {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            line-height: 1;
        }

        .card-logo img {
            width: 114px;
            height: 114px;
            object-fit: contain;
            display: block;
        }

        .login-card h5 {
            text-align: center;
            font-family: "Toyota Type", sans-serif;
            font-weight: 700;
            font-size: clamp(1.6rem, 1.8vw, 2.4rem);
            letter-spacing: -0.04em;
            color: #fff;
            line-height: 0.3;
        }

        .login-card .subtext {
            margin: 0 0 18px;
            font-family: "Toyota Type", sans-serif;
            font-weight: 300;
            text-align: center;
            color: rgba(255,255,255,0.75);
            font-size: 0.9rem;
            line-height: 1.55;
        }

        .form-wrap {
            width: 100%;
        }

        .input-group {
            display: flex;
            align-items: center;
            border: none;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            margin-bottom: 14px;
            overflow: hidden;
        }

        .input-group input {
            flex: 1;
            border: none;
            background: transparent;
            color: #f8fafc;
            padding: 10px 14px 10px 1px;
            font-family: "Toyota Type", sans-serif;
            font-weight: 300;
            font-size: 13px;
            outline: none;
        }

        .input-group i {
            width: 42px;
            text-align: center;
            color: rgba(255,255,255,0.45);
            font-size: 0.95rem;
        }

        .input-group input::placeholder {
            color: rgba(255,255,255,0.45);
        }

        .input-group input:focus {
            box-shadow: none;
            background: transparent;
        }

        .login-btn {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: none;
            border-radius: 18px;
            background: rgb(158, 2, 12);
            color: #fff;
            font-family: "Toyota Type", sans-serif;
            font-weight: 300;
            font-size: 0.90rem;
            padding: 10px 16px;
            margin-top: 6px;
            margin-bottom: 20px;
        }

        .login-btn:hover {
            opacity: 0.96;
        }

        .login-btn i {
            font-size: 0.90rem;`
        }

        .brand-footer {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            margin-top: 18px;
            padding-top: 14px;
            border-top: 1px solid rgba(255,255,255,0.12);
            color: rgba(255,255,255,0.75);
            font-family: "Toyota Type", sans-serif;
            font-weight: 200;
            font-size: 0.9rem;
        }

        .page-footer {
            position: fixed;
            right: 0;
            bottom: 0;
            left: 0;
            z-index: 4;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 24px;
            padding: 20px 28px 20px 36px;
            border-top: 1px solid rgba(255,255,255,0.14);
            background: rgba(4, 10, 16, 0.72);
            color: rgba(255,255,255,0.72);
            font-family: "Toyota Type", sans-serif;
            font-weight: 300;
            font-size: 0.90rem;
            line-height: 1.45;
        }

        .page-footer-left,
        .page-footer-right {
            max-width: 48%;
        }

        .page-footer-right {
            text-align: right;
        }

        .page-footer-links {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 4px 14px;
            margin-top: 2px;
        }

        @media (max-width: 980px) {
            body {
                overflow: auto;
            }

            .login-shell {
                grid-template-columns: 1fr;
                padding: 24px 20px 30px;
            }

            .left-panel {
                padding-right: 0;
                padding: 72px 0 0;
            }

            .brand-wrap {
                justify-content: space-between;
                align-items: center;
                padding-left: 16px;
                padding-right: 16px;
            }

            .brand-block {
                height: 100%;
            }

            .hero-copy {
                text-align: center;
                margin: 0 auto;
                padding-left: 0;
            }

            .product-visual {
                height: 260px;
            }

            .right-panel {
                align-items: center;
                padding: 20px 0 58px;
            }

            .page-footer {
                align-items: stretch;
                flex-direction: column;
                gap: 4px;
                padding: 8px 16px 10px;
            }

            .page-footer-left,
            .page-footer-right {
                max-width: 100%;
                text-align: center;
            }

            .page-footer-links {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="login-shell">
        <div class="left-panel">
            <div class="brand-wrap" aria-label="Toyota and TAG brands">
                <div class="brand-block toyota-block">
                    <img src="{{ asset('assets/images/Logo Toyota White.png') }}" alt="Toyota" class="toyota-mark">
                </div>

                <div class="brand-block tag-block">
                    <img src="{{ asset('assets/images/Logo TAG-White.png') }}" alt="TAG logo" class="tag-mark">
                </div>
            </div>

            {{-- <div class="hero-copy">
                <h1>GR Merchandise</h1>
                <div class="subtitle">PT. TAG Toyota</div>
                <div class="desc">Silakan login untuk mengakses sistem penjualan merchandise resmi Toyota GR PT. TAG Toyota.</div>
            </div> --}}
        </div>

        <div class="right-panel">
            <div class="login-card">
                <div class="card-logo">
                    <img src="{{ asset('assets/images/logo-gr-text.png') }}" alt="GR logo">
                </div>

                <h5>Selamat Datang</h5>
                <p class="subtext pt-1">Silakan login untuk mengakses sistem penjualan <br> GR Merch</p>

                @if($errors->any())
                    <div class="alert alert-danger mb-3">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST" class="form-wrap">
                    @csrf

                    <div class="input-group">
                        <i class="fa fa-user" aria-hidden="true"></i>
                        <input type="text" name="username" placeholder="Username" value="{{ old('username') }}" required autofocus>
                    </div>

                    <div class="input-group">
                        <i class="fa fa-lock" aria-hidden="true"></i>
                        <input type="password" name="password" placeholder="Password" required>
                    </div>

                    <button type="submit" class="login-btn">
                        <i class="fa fa-sign-in-alt"></i>
                        <span>Login</span>
                    </button>
                </form>

            </div>
        </div>
    </div>

    <footer class="page-footer">
        <div class="page-footer-left">
            &copy; 2026 Tunas Auto Graha. All Right Reserved
        </div>

        <div class="page-footer-right">
            <div class="page-footer-links">
                <span>Syarat &amp; Ketentuan Pengguna</span>
                <span>Kebijakan Privasi</span>
            </div>
        </div>
    </footer>
</body>
</html>
