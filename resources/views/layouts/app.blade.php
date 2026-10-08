<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - GR_merch</title>
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/Logo-TAG-favicon-16x16px.png') }}?v={{ filemtime(public_path('assets/images/Logo-TAG-favicon-16x16px.png')) }}-2">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
    <style>
        @font-face {
            font-family: 'Toyota Type';
            src: url('{{ asset('assets/fonts/ToyotaType-Regular.otf') }}') format('opentype');
            font-style: normal;
            font-weight: 400;
            font-display: swap;
        }

        @font-face {
            font-family: 'Toyota Type';
            src: url('{{ asset('assets/fonts/ToyotaType-Light.otf') }}') format('opentype');
            font-style: normal;
            font-weight: 300;
            font-display: swap;
        }

        @font-face {
            font-family: 'Toyota Type';
            src: url('{{ asset('assets/fonts/ToyotaType-Book.ttf') }}') format('truetype');
            font-style: normal;
            font-weight: 500;
            font-display: swap;
        }

        @font-face {
            font-family: 'Toyota Type';
            src: url('{{ asset('assets/fonts/ToyotaType-Bold.otf') }}') format('opentype');
            font-style: normal;
            font-weight: 700;
            font-display: swap;
        }

        :root {
            --gazoo-background: url('{{ asset('assets/images/background-gazoo.png') }}');
            --gazoo-panel: rgba(3, 10, 17, 0.88);
            --gazoo-panel-soft: rgba(5, 14, 23, 0.78);
            --gazoo-border: rgba(255, 255, 255, 0.1);
            --gazoo-red: #f70008;
        }

        html,
        body,
        .wrapper {
            min-height: 100%;
        }

        html,
        body,
        .main-sidebar,
        .content-wrapper {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        html::-webkit-scrollbar,
        body::-webkit-scrollbar,
        .main-sidebar::-webkit-scrollbar,
        .content-wrapper::-webkit-scrollbar {
            width: 0;
            height: 0;
            display: none;
        }

        body {
            background: #02080e var(--gazoo-background) center center / cover fixed no-repeat;
            color: #f4f7fa;
            font-family: 'Toyota Type', sans-serif;
        }

        body,
        button,
        input,
        optgroup,
        select,
        textarea,
        .btn,
        .form-control,
        .custom-select,
        .table,
        .nav-link,
        .dropdown-menu {
            font-family: 'Toyota Type', sans-serif;
        }

        .wrapper {
            background: transparent;
        }

        .main-header,
        .main-sidebar,
        .main-footer,
        .content-wrapper {
            background-color: transparent !important;
            background-image: var(--gazoo-background) !important;
            background-attachment: fixed;
            background-position: center center;
            background-size: cover;
            color: #f4f7fa;
        }

        .main-header {
            border-bottom: 1px solid var(--gazoo-border);
            background-blend-mode: overlay;
            background-color: var(--gazoo-panel-soft) !important;
        }

        .main-sidebar {
            background-blend-mode: overlay;
            background-color: var(--gazoo-panel) !important;
            border-right: 1px solid rgba(109, 144, 174, 0.34);
        }

        .main-footer {
            border-top: 1px solid var(--gazoo-border);
            background-blend-mode: overlay;
            background-color: var(--gazoo-panel) !important;
        }

        .content-wrapper {
            background-blend-mode: overlay;
            background-color: rgba(2, 8, 14, 0.58) !important;
        }

        .navbar-light .navbar-nav .nav-link,
        .main-footer,
        .content-header h1,
        .breadcrumb-item,
        .breadcrumb-item a {
            color: #f4f7fa !important;
        }

        .main-header {
            position: fixed !important;
            top: 0;
            right: 0;
            left: 0;
            z-index: 1039;
            display: flex;
            align-items: center;
            min-height: 54px;
            width: 100%;
            margin-left: 0 !important;
            padding: 0 1.25rem;
        }

        body.sidebar-mini .main-header,
        body.sidebar-mini.sidebar-collapse .main-header,
        body.sidebar-mini.sidebar-open .main-header {
            left: 0 !important;
            right: 0 !important;
            width: 100% !important;
            margin-left: 0 !important;
            transform: none !important;
        }

        .main-sidebar {
            position: fixed !important;
            left: 0;
            top: 54px !important;
            bottom: 0;
            height: calc(100vh - 54px) !important;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 1038;
        }

        @media (min-width: 768px) {
            body.sidebar-mini.sidebar-collapse .main-sidebar,
            body.sidebar-mini.sidebar-collapse .main-sidebar::before,
            body.sidebar-mini.sidebar-collapse .main-sidebar:hover,
            body.sidebar-mini.sidebar-collapse .main-sidebar:hover::before {
                width: 4.6rem !important;
            }

            body.sidebar-mini.sidebar-collapse .main-sidebar:hover .brand-link {
                width: 4.6rem !important;
            }

            body.sidebar-mini.sidebar-collapse .main-sidebar:hover .nav-sidebar .nav-link p,
            body.sidebar-mini.sidebar-collapse .main-sidebar:hover .nav-sidebar .nav-header {
                display: none !important;
            }
        }

        .main-sidebar .nav-link,
        .main-sidebar .nav-link p,
        .main-sidebar .nav-header,
        .main-sidebar .brand-link {
            font-family: 'Toyota Type', sans-serif;
            font-weight: 300;
        }

        .content-wrapper {
            min-height: 100vh;
            margin-top: 54px !important;
        }

        .topbar-brand {
            display: flex;
            align-items: center;
            height: 34px;
            margin-right: 0.85rem;
            padding-right: 0.85rem;
            border-right: 1px solid var(--gazoo-border);
        }

        .topbar-brand img {
            width: 106px;
            max-height: 42px;
            object-fit: contain;
        }

        .topbar-menu {
            display: flex;
            align-items: center;
            margin: 0;
        }

        .topbar-menu .nav-link {
            padding: 0.65rem 0.75rem;
            font-size: 1.1rem;
        }

        .topbar-tag {
            display: flex;
            align-items: center;
            height: 54px;
            margin-left: 0;
            margin-right: 0;
            margin-left: 1rem;
            padding-left: 1rem;
            border-left: 1px solid var(--gazoo-border);
        }

        .topbar-tag img {
            width: 102px;
            max-height: 40px;
            object-fit: contain;
        }

        .topbar-user .nav-link {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            min-width: 145px;
            margin: 0.35rem 0;
            padding: 0.3rem 0.55rem 0.3rem 0.35rem !important;
            border: 1px solid rgba(105, 143, 176, 0.45);
            border-radius: 5px;
            background: rgba(5, 14, 23, 0.78);
            cursor: pointer;
        }

        .topbar-user .nav-link::after {
            margin-left: auto;
        }

        .user-avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 27px;
            height: 27px;
            flex: 0 0 27px;
            border-radius: 50%;
            background: var(--gazoo-red);
            color: #ffffff;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .user-copy {
            display: flex;
            min-width: 0;
            flex-direction: column;
            line-height: 1.1;
        }

        .user-name,
        .user-role {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .user-name {
            color: #ffffff;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .user-role {
            margin-top: 0.16rem;
            color: rgba(244, 247, 250, 0.62);
            font-size: 0.68rem;
        }

        .topbar-user .dropdown-menu {
            min-width: 245px;
            margin-top: 0.55rem;
            padding: 0;
            overflow: hidden;
            border: 1px solid rgba(105, 143, 176, 0.62);
            border-radius: 5px;
            background: rgba(4, 12, 20, 0.98);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.4);
        }

        .user-popup-header {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid var(--gazoo-border);
            background: rgba(239, 29, 47, 0.14);
        }

        .user-popup-header strong,
        .user-popup-header small {
            display: block;
        }

        .user-popup-header strong {
            color: #ffffff;
            font-size: 1rem;
        }

        .user-popup-header small {
            margin-top: 0.2rem;
            color: rgba(244, 247, 250, 0.65);
        }

        .user-popup-body {
            padding: 0.8rem 1rem;
        }

        .user-popup-body small {
            display: block;
            margin-bottom: 0.7rem;
            color: rgba(244, 247, 250, 0.65);
        }

        .user-popup-body .btn {
            width: 100%;
        }

        .brand-link {
            position: relative;
            display: flex !important;
            align-items: center;
            min-height: 54px;
            padding: 0.7rem 1rem !important;
            border-bottom: 0 !important;
            color: #ffffff !important;
        }

        .brand-link::after {
            position: absolute;
            right: 0;
            bottom: 25px;
            left: 0;
            height: 1px;
            background: var(--gazoo-border);
            content: '';
        }

        .brand-link img {
            width: 200px;
            height: auto;
            max-width: 100%;
            object-fit: contain;
            transform: translateY(-13px);
        }

        .brand-link .brand-logo-collapsed {
            display: none;
            width: 3.2rem;
            transform: translateY(4px);
        }

        body.sidebar-mini.sidebar-collapse .brand-link .brand-logo-expanded {
            display: none;
        }

        body.sidebar-mini.sidebar-collapse .brand-link .brand-logo-collapsed {
            display: block;
        }

        body.sidebar-mini.sidebar-collapse .brand-link::after {
            bottom: -10px;
        }

        .brand-link .brand-text {
            display: none;
        }

        .nav-sidebar .nav-link {
            color: rgba(244, 247, 250, 0.8);
            transition: background-color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease, color 0.2s ease;
        }

        .nav-sidebar .nav-link:hover {
            background: linear-gradient( to right, rgba(220, 26, 43, 0.984) 0%,  rgba(130, 10, 20, 0.82) 60%,rgba(35, 0, 4, 0.82) 100% ) !important;
            color: #ffffff !important;
        }

        .nav-sidebar .nav-link.active {
            border: 1px solid rgba(255, 53, 64, 0.95);
            background: linear-gradient(90deg, #f20b18 0%, #b00813 42%, #5b050b 100%) !important;
            box-shadow: inset 3px 0 0 #ff858b, 0 0 5px rgba(247, 0, 8, 0.9), 0 0 12px rgba(247, 0, 8, 0.42);
            color: #ffffff !important;
        }

        .nav-header {
            color: rgba(255, 255, 255, 0.45) !important;
        }

        .content .card,
        .content .info-box,
        .content .small-box,
        .content .table {
            background-color: rgba(5, 14, 23, 0.84) !important;
            border-color: var(--gazoo-border) !important;
            color: #f4f7fa;
        }

        .content .small-box {
            min-height: 115px;
            margin-bottom: 1rem;
            overflow: hidden;
            border: 1px solid rgba(105, 143, 176, 0.75) !important;
            border-radius: 6px;
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.18);
        }

        .content .small-box::before,
        .content .card-header::before {
            position: absolute;
            top: 0;
            left: 0;
            width: 7px;
            height: 100%;
            background: var(--gazoo-red);
            content: '';
            transform: skewX(-13deg) translateX(-2px);
        }

        .content .small-box::after {
            position: absolute;
            top: -45px;
            right: -20px;
            width: 110px;
            height: 175px;
            border-left: 2px solid rgba(239, 29, 47, 0.32);
            content: '';
            transform: rotate(38deg);
        }

        .content .small-box .inner {
            position: relative;
            z-index: 1;
            padding: 1rem 1.25rem;
        }

        .content .small-box h3 {
            margin-bottom: 0.35rem;
            font-size: 2rem;
            line-height: 1;
        }

        .content .small-box p {
            margin-bottom: 0;
            font-size: 0.95rem;
        }

        .content .small-box .icon {
            right: 1rem;
            top: 1rem;
            z-index: 1;
            color: rgba(255, 255, 255, 0.9);
        }

        .content .small-box.bg-info,
        .content .small-box.bg-success,
        .content .small-box.bg-warning,
        .content .small-box.bg-danger {
            background-color: rgba(4, 12, 20, 0.88) !important;
        }

        .content .small-box.bg-info { border-left: 2px solid #ee1c2d !important; }
        .content .small-box.bg-success { border-left: 2px solid #71849a !important; }
        .content .small-box.bg-warning { border-color: rgba(255, 174, 0, 0.88) !important; }
        .content .small-box.bg-danger { border-color: rgba(239, 29, 47, 0.88) !important; }

        .content .card {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(105, 143, 176, 0.62) !important;
            border-radius: 6px;
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.16);
        }

        .content .card-header,
        .content .card-footer,
        .content .table thead th {
            background-color: rgba(0, 0, 0, 0.22) !important;
            border-color: var(--gazoo-border) !important;
            color: #ffffff;
        }

        .content .card-header {
            position: relative;
            min-height: 39px;
            padding: 0.65rem 0.85rem 0.65rem 1.35rem;
            background: rgba(3, 12, 20, 0.68) !important;
        }

        .content .card-header .card-title {
            font-size: 1rem;
            font-weight: 700;
        }

        .content .list-group-item {
            border-color: rgba(105, 143, 176, 0.22) !important;
            background: rgba(6, 17, 28, 0.72) !important;
            color: #f4f7fa;
        }

        .content .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(255, 255, 255, 0.025);
        }

        .content-header h1 {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            font-size: 1.65rem;
            font-weight: 700;
        }

        .content-header h1::before {
            display: none;
        }

        .content-header h1 > i {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 31px;
            height: 31px;
            color: #ffffff;
            font-size: 1.15rem;
        }

        .content-header h1::after {
            margin-left: 0.25rem;
            color: var(--gazoo-red);
            content: '/';
        }

        .content .table td,
        .content .table th,
        .content .card-body,
        .content .card-body label,
        .content .info-box-text,
        .content .info-box-number {
            border-color: var(--gazoo-border) !important;
            color: #f4f7fa;
        }

        .content .form-control,
        .content .custom-select {
            background-color: rgba(0, 0, 0, 0.32);
            border-color: var(--gazoo-border);
            color: #ffffff;
        }

        .content .form-control::placeholder {
            color: rgba(255, 255, 255, 0.55);
        }

        .content .custom-file {
            height: calc(2.25rem + 2px);
        }

        .content .custom-file-label {
            height: calc(2.25rem + 2px);
            overflow: hidden;
            border-color: var(--gazoo-border);
            background-color: rgba(0, 0, 0, 0.32);
            color: rgba(255, 255, 255, 0.72);
            line-height: 1.5;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .content .custom-file-label::after {
            height: 100%;
            border-left-color: var(--gazoo-border);
            background-color: rgba(5, 14, 23, 0.92);
            color: #f4f7fa;
        }

        .content .custom-file-input:focus ~ .custom-file-label {
            border-color: var(--gazoo-red);
            box-shadow: 0 0 0 0.2rem rgba(239, 29, 47, 0.2);
        }

        .content .form-control:focus,
        .content .custom-select:focus {
            border-color: var(--gazoo-red);
            box-shadow: 0 0 0 0.2rem rgba(239, 29, 47, 0.2);
        }

        @media (max-width: 767.98px) {
            .main-sidebar,
            .main-header,
            .main-footer,
            .content-wrapper {
                background-attachment: scroll;
            }

            .main-header {
                padding: 0 0.5rem;
            }

            .main-sidebar {
                top: 54px !important;
                height: calc(100vh - 54px) !important;
            }

            .topbar-brand {
                margin-right: 0.25rem;
                padding-right: 0.5rem;
            }

            .topbar-brand img {
                width: 86px;
            }

            .brand-link img {
                width: 160px;
            }

            .topbar-tag {
                display: none;
            }

            .content-header h1 {
                font-size: 1.35rem;
            }

            .content-header h1::before {
                display: none;
            }
        }
    </style>
    @stack('styles')
    <style>
        @media (max-width: 991.98px) {
            .main-sidebar {
                width: 230px !important;
                transform: translateX(-100%);
                transition: transform 0.2s ease;
            }

            body.sidebar-open .main-sidebar {
                transform: translateX(0);
            }

            .content-wrapper,
            .main-footer {
                margin-left: 0 !important;
            }

            .content-wrapper {
                padding-top: 0;
            }

            .main-header {
                left: 0 !important;
                padding-right: 0.65rem;
                padding-left: 0.65rem;
            }

            .topbar-brand {
                margin-right: 0.4rem;
                padding-right: 0.55rem;
            }

            .topbar-brand img {
                width: 92px;
            }

            .topbar-user .nav-link {
                min-width: 0;
            }

            .user-copy {
                max-width: 105px;
            }

            .content-header {
                padding: 0.75rem 0.75rem 0.35rem;
            }

            .content-header h1 {
                font-size: 1.35rem;
            }

            .content {
                padding: 0 0.75rem 1rem;
            }

            .content .container-fluid {
                padding-right: 0;
                padding-left: 0;
            }

            .content .card-header {
                flex-wrap: wrap;
                gap: 0.6rem;
            }

            .content .card-body {
                overflow-x: auto;
            }

            .content .table {
                min-width: 640px;
            }

            .content .table td,
            .content .table th {
                white-space: nowrap;
            }

            .content .form-row {
                margin-right: 0;
                margin-left: 0;
            }

            .content .form-row > [class*="col-"] {
                padding-right: 0;
                padding-left: 0;
            }

            .content .btn {
                max-width: 100%;
            }
        }

        @media (max-width: 575.98px) {
            .main-header {
                min-height: 50px;
            }

            .main-sidebar {
                top: 50px !important;
                height: calc(100vh - 50px) !important;
            }

            .content-wrapper {
                margin-top: 50px !important;
            }

            .topbar-brand img {
                width: 78px;
            }

            .topbar-user .nav-link {
                gap: 0.3rem;
                padding-right: 0.35rem !important;
            }

            .user-copy {
                display: none;
            }

            .user-avatar {
                width: 25px;
                height: 25px;
                flex-basis: 25px;
            }

            .content-header h1 {
                font-size: 1.15rem;
            }

            .content .card-header,
            .content .card-body,
            .content .card-footer {
                padding-right: 0.7rem;
                padding-left: 0.7rem;
            }

            .content .small-box {
                min-height: 100px;
            }

            .content .small-box .inner {
                padding: 0.8rem 0.9rem;
            }

            .content .small-box h3 {
                font-size: 1.55rem;
            }

            .content .small-box p {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <a class="topbar-brand" href="{{ route('dashboard') }}" aria-label="Toyota Gazoo Racing">
            <img src="{{ asset('assets/images/Logo Toyota White.png') }}" alt="Toyota Gazoo Racing">
        </a>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown topbar-user">
                <a class="nav-link dropdown-toggle" href="#" id="userMenu" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    <span class="user-copy">
                        <span class="user-name">{{ auth()->user()->name }}</span>
                        <span class="user-role">{{ auth()->user()->role === 'admin_ho' ? 'Admininistrator' : 'Staff Cabang' }}</span>
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userMenu">
                    <div class="user-popup-header">
                        <strong>{{ auth()->user()->name }}</strong>
                        <small>{{ auth()->user()->email }}</small>
                    </div>
                    <div class="user-popup-body">
                        <small><i class="fas fa-id-badge mr-1"></i> {{ auth()->user()->role === 'admin_ho' ? 'Admininistrator' : 'Staff Cabang' }}</small>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-sign-out-alt mr-1"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </li>
            <li class="nav-item topbar-tag">
                <a href="{{ route('dashboard') }}" aria-label="TAG Tunas Auto Graha">
                    <img src="{{ asset('assets/images/Logo TAG-white.png') }}" alt="TAG Tunas Auto Graha">
                </a>
            </li>
        </ul>
    </nav>

    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a class="nav-link brand-link" data-widget="pushmenu" href="#" role="button" aria-label="Toggle sidebar">
            <img class="brand-logo-expanded" src="{{ asset('assets/images/merchandise.png') }}" alt="GR merch">
            <img class="brand-logo-collapsed" src="{{ asset('assets/images/logo-gr-text.png') }}" alt="GR">
        </a>

        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('items.index') }}" class="nav-link {{ request()->routeIs('items.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-box"></i><p>Data Items</p>
                        </a>
                    </li>
                    @if(auth()->user()->isAdminHo())
                    <li class="nav-item">
                        <a href="{{ route('stockin.index') }}" class="nav-link {{ request()->routeIs('stockin.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-arrow-down"></i><p>Barang Masuk</p>
                        </a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <a href="{{ route('transfers.index') }}" class="nav-link {{ request()->routeIs('transfers.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-exchange-alt"></i><p>{{ auth()->user()->isAdminHo() ? 'Transfer Barang' : 'Transfer Masuk' }}</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('stockout.index') }}" class="nav-link {{ request()->routeIs('stockout.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-arrow-up"></i><p>Barang Keluar</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('history.index') }}" class="nav-link {{ request()->routeIs('history.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-history"></i><p>Histori Transaksi</p>
                        </a>
                    </li>
                    @if(auth()->user()->isAdminHo())
                    <li class="nav-item">
                        <a href="{{ route('approval.index') }}" class="nav-link {{ request()->routeIs('approval.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-check-circle"></i><p>Approval Items</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('cabang.index') }}" class="nav-link {{ request()->routeIs('cabang.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-code-branch"></i><p>Data Cabang</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users-cog"></i><p>Manajemen User</p>
                        </a>
                    </li>
                    @endif
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content -->
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <h1 class="m-0">
                    @if(request()->routeIs('dashboard'))
                        <i class="fas fa-tachometer-alt" aria-hidden="true"></i>
                    @elseif(request()->routeIs('items.*'))
                        <i class="fas fa-box" aria-hidden="true"></i>
                    @elseif(request()->routeIs('stockin.*'))
                        <i class="fas fa-arrow-down" aria-hidden="true"></i>
                    @elseif(request()->routeIs('transfers.*'))
                        <i class="fas fa-exchange-alt" aria-hidden="true"></i>
                    @elseif(request()->routeIs('stockout.*'))
                        <i class="fas fa-arrow-up" aria-hidden="true"></i>
                    @elseif(request()->routeIs('history.*'))
                        <i class="fas fa-history" aria-hidden="true"></i>
                    @elseif(request()->routeIs('approval.*'))
                        <i class="fas fa-check-circle" aria-hidden="true"></i>
                    @elseif(request()->routeIs('cabang.*'))
                        <i class="fas fa-code-branch" aria-hidden="true"></i>
                    @elseif(request()->routeIs('users.*'))
                        <i class="fas fa-users-cog" aria-hidden="true"></i>
                    @else
                        <i class="fas fa-layer-group" aria-hidden="true"></i>
                    @endif
                    @yield('title', 'Dashboard')
                </h1>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        {{ session('error') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    {{-- <footer class="main-footer">
        <strong>&copy; {{ date('Y') }} PT TAG Toyota.</strong> GR_merch - Sistem Penjualan Merchandise GR.
    </footer> --}}
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
@stack('scripts')
</body>
</html>
