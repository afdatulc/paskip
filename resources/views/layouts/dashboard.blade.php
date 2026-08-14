<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'PASKIP') }} - @yield('title')</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <style>
        :root {
            --primary-color: #0F172A;
            --secondary-color: #334155;
            --bg-dark: #0F172A;
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 78px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #F8FAFC;
        }

        /* ===== SIDEBAR ===== */
        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: var(--bg-dark);
            color: #fff;
            z-index: 1000;
            transition: width 0.3s cubic-bezier(.4, 0, .2, 1);
            overflow-x: hidden;
            overflow-y: auto;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        #sidebar::-webkit-scrollbar {
            display: none;
        }

        #sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        #content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            background-color: #F8FAFC;
            transition: margin-left 0.3s cubic-bezier(.4, 0, .2, 1);
        }

        #sidebar.collapsed~#content {
            margin-left: var(--sidebar-collapsed-width);
        }

        .main-content {
            flex: 1;
        }

        /* Sidebar Header */
        .sidebar-header {
            padding: 1.2rem 1.2rem;
            display: flex;
            align-items: center;
            white-space: nowrap;
            height: 70px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            transition: all 0.3s cubic-bezier(.4, 0, .2, 1);
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #fff;
            overflow: hidden;
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            transition: all 0.3s;
        }

        .sidebar-brand .brand-icon {
            width: 36px;
            height: 36px;
            min-width: 36px;
            background: rgba(67, 97, 238, 0.15);
            border: 1px solid rgba(67, 97, 238, 0.25);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            margin-right: 10px;
            transition: all 0.3s;
        }

        .sidebar-brand .brand-text {
            transition: opacity 0.2s, width 0.3s;
            opacity: 1;
        }

        #sidebar.collapsed .sidebar-header {
            padding: 1.2rem 0;
            justify-content: center;
        }

        #sidebar.collapsed .sidebar-brand {
            justify-content: center;
            width: 100%;
        }

        #sidebar.collapsed .sidebar-brand .brand-icon {
            margin-right: 0 !important;
            background: rgba(67, 97, 238, 0.25);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
        }

        #sidebar.collapsed .sidebar-brand .brand-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }

        /* Section Labels */
        .sidebar-section-label {
            padding: 0.5rem 1.5rem 0.3rem;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255, 255, 255, 0.25);
            white-space: nowrap;
            overflow: hidden;
            transition: opacity 0.2s;
        }

        #sidebar.collapsed .sidebar-section-label {
            opacity: 0;
            height: 0;
            padding: 0;
            margin: 0;
        }

        /* Nav Links */
        .nav-link {
            color: rgba(255, 255, 255, 0.6);
            padding: 0.75rem 1.2rem;
            display: flex;
            align-items: center;
            border-radius: 10px;
            margin: 0.15rem 0.8rem;
            transition: all 0.2s ease;
            font-size: 0.88rem;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            position: relative;
        }

        .nav-link i {
            width: 22px;
            min-width: 22px;
            font-size: 1.05rem;
            margin-right: 12px;
            text-align: center;
            transition: transform 0.2s;
        }

        .nav-link .link-text {
            transition: opacity 0.2s;
            opacity: 1;
        }

        #sidebar.collapsed .nav-link .link-text {
            opacity: 0;
            width: 0;
        }

        #sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 0.75rem;
            margin: 0.15rem 0.6rem;
        }

        #sidebar.collapsed .nav-link i {
            margin-right: 0;
            font-size: 1.15rem;
        }

        .nav-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.05);
            transform: translateX(3px);
        }

        #sidebar.collapsed .nav-link:hover {
            transform: none;
        }

        .nav-link:hover i {
            transform: scale(1.1);
        }

        .nav-link.active {
            color: #fff;
            background: var(--primary-color) !important;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        }

        /* Tooltip for collapsed sidebar */
        #sidebar.collapsed .nav-link {
            position: relative;
        }

        #sidebar.collapsed .nav-link::after {
            content: attr(data-title);
            position: absolute;
            left: calc(100% + 14px);
            top: 50%;
            transform: translateY(-50%);
            background: #2b2d42;
            color: #fff;
            padding: 0.4rem 0.9rem;
            border-radius: 8px;
            font-size: 0.8rem;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.15s;
            z-index: 9999;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        }

        #sidebar.collapsed .nav-link:hover::after {
            opacity: 1;
        }

        /* Sub Menu Links */
        .nav-link .arrow-icon {
            transition: transform 0.3s;
            font-size: 0.8rem;
            opacity: 0.6;
        }

        .nav-link[aria-expanded="true"] .arrow-icon {
            transform: rotate(180deg);
        }
        
        .sub-menu {
            background: rgba(0, 0, 0, 0.15);
            border-radius: 10px;
            margin: 0.2rem 0.8rem;
            padding: 0.3rem 0;
            overflow: hidden;
        }
        
        #sidebar.collapsed .nav-link[data-bs-toggle="collapse"] {
            pointer-events: none;
        }
        
        #sidebar.collapsed .collapse {
            display: none !important;
        }
        
        #sidebar.collapsed .arrow-icon {
            display: none;
        }
        
        .sub-link {
            padding: 0.5rem 1rem 0.5rem 3.2rem;
            color: rgba(255, 255, 255, 0.6);
            display: block;
            text-decoration: none;
            font-size: 0.82rem;
            transition: all 0.2s;
            position: relative;
        }
        
        .sub-link::before {
            content: '';
            position: absolute;
            left: 2rem;
            top: 50%;
            transform: translateY(-50%);
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transition: all 0.2s;
        }
        
        .sub-link:hover, .sub-link.active {
            color: #fff;
            background: var(--secondary-color);
        }
        
        .sub-link:hover::before, .sub-link.active::before {
            background: #fff;
            box-shadow: 0 0 5px rgba(255, 255, 255, 0.8);
        }

        /* Sidebar Divider */
        .sidebar-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            margin: 0.6rem 1.2rem;
        }

        #sidebar.collapsed .sidebar-divider {
            margin: 0.6rem 0.6rem;
        }

        /* ===== GENERAL ===== */
        .card {
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .card-header {
            background: #fff;
            border-bottom: 1px solid #E2E8F0;
            padding: 1.5rem;
            font-weight: 700;
            color: #0F172A;
        }

        .user-profile-btn {
            background: #fff;
            border: 1px solid rgba(0, 0, 0, 0.05);
            padding: 0.5rem 1rem;
            border-radius: 12px;
            transition: all 0.2s;
        }

        .user-profile-btn:hover {
            background: #f8f9fa;
            border-color: rgba(0, 0, 0, 0.1);
        }

        .stat-card {
            padding: 1.5rem;
            border-radius: 16px;
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            right: -20px;
            bottom: -20px;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .bg-green {
            background: #0F766E; /* Muted Teal */
        }

        .bg-yellow {
            background: #B45309; /* Muted Amber */
        }

        .bg-red {
            background: #BE123C; /* Muted Crimson */
        }

        .bg-blue {
            background: #1E293B; /* Slate 800 */
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 0.6rem 1.8rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.4);
        }

        footer {
            padding: 2rem 0;
            margin-top: auto;
            font-size: 0.85rem;
            color: #adb5bd;
            font-weight: 500;
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            #sidebar {
                width: var(--sidebar-collapsed-width);
            }

            #sidebar .sidebar-brand .brand-text {
                opacity: 0;
                width: 0;
                overflow: hidden;
            }

            #sidebar .nav-link .link-text {
                opacity: 0;
                width: 0;
            }

            #sidebar .nav-link {
                justify-content: center;
                padding: 0.75rem;
                margin: 0.15rem 0.6rem;
            }

            #sidebar .nav-link i {
                margin-right: 0;
                font-size: 1.15rem;
            }

            #sidebar .sidebar-section-label {
                opacity: 0;
                height: 0;
                padding: 0;
                margin: 0;
            }

            #sidebar .sidebar-divider {
                margin: 0.6rem 0.6rem;
            }

            #content {
                margin-left: var(--sidebar-collapsed-width);
            }

            #sidebar.expanded {
                width: var(--sidebar-width);
            }

            #sidebar.expanded .sidebar-brand .brand-text {
                opacity: 1;
                width: auto;
            }

            #sidebar.expanded .nav-link .link-text {
                opacity: 1;
                width: auto;
            }

            #sidebar.expanded .nav-link {
                justify-content: flex-start;
                padding: 0.75rem 1.2rem;
                margin: 0.15rem 0.8rem;
            }

            #sidebar.expanded .nav-link i {
                margin-right: 12px;
                font-size: 1.05rem;
            }

            #sidebar.expanded .sidebar-section-label {
                opacity: 1;
                height: auto;
                padding: 0.5rem 1.5rem 0.3rem;
            }

            #sidebar.expanded .sidebar-divider {
                margin: 0.6rem 1.2rem;
            }
        }
    </style>
    @yield('styles')
</head>

<body>

    <div id="sidebar" class="d-flex flex-column">
        <div class="sidebar-header">
            <a href="/" class="sidebar-brand">
                <div class="brand-icon">
                    <i class="fas fa-shield-halved text-primary"></i>
                </div>
                <span class="brand-text">PASKIP</span>
            </a>
        </div>

        <div class="flex-grow-1">
            <nav class="mt-4">
                <a href="{{ route('dashboard') }}"
                    class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" data-title="Dashboard">
                    <i class="fas fa-gauge-high"></i> <span class="link-text">Dashboard</span>
                </a>

                {{-- Perencanaan Strategis --}}
                @if(auth()->user()->isAdminOrPimpinan())
                    @php $isRenstraActive = request()->routeIs('renstra.*') || request()->routeIs('pk-tahunan.*') || request()->routeIs('evaluasi-tahunan.*'); @endphp
                    <a class="nav-link {{ $isRenstraActive ? '' : 'collapsed' }}" data-bs-toggle="collapse" href="#collapseRenstra" role="button" aria-expanded="{{ $isRenstraActive ? 'true' : 'false' }}" aria-controls="collapseRenstra" data-title="Perencanaan">
                        <i class="fas fa-compass"></i> <span class="link-text">Perencanaan Strategis</span>
                        <i class="fas fa-chevron-down ms-auto arrow-icon"></i>
                    </a>
                    <div class="collapse {{ $isRenstraActive ? 'show' : '' }}" id="collapseRenstra">
                        <div class="sub-menu">
                            <a href="{{ route('renstra.index') }}" class="sub-link {{ request()->routeIs('renstra.*') ? 'active' : '' }}">Renstra</a>
                            <a href="{{ route('pk-tahunan.index') }}" class="sub-link {{ request()->routeIs('pk-tahunan.*') ? 'active' : '' }}">PK Tahunan</a>
                            <a href="{{ route('evaluasi-tahunan.index') }}" class="sub-link {{ request()->routeIs('evaluasi-tahunan.*') ? 'active' : '' }}">Evaluasi Tahunan</a>
                        </div>
                    </div>
                @endif

                {{-- Master Data --}}
                @php 
                    $isMasterActive = request()->routeIs('indikator.*') || request()->routeIs('anggaran.*') || request()->routeIs('kegiatan-master.*') || request()->routeIs('output-master.*') || request()->routeIs('pegawai.*') || request()->routeIs('tabel-ro.*'); 
                @endphp
                <a class="nav-link {{ $isMasterActive ? '' : 'collapsed' }}" data-bs-toggle="collapse" href="#collapseMaster" role="button" aria-expanded="{{ $isMasterActive ? 'true' : 'false' }}" aria-controls="collapseMaster" data-title="Master Data">
                    <i class="fas fa-database"></i> <span class="link-text">Master Data</span>
                    <i class="fas fa-chevron-down ms-auto arrow-icon"></i>
                </a>
                <div class="collapse {{ $isMasterActive ? 'show' : '' }}" id="collapseMaster">
                    <div class="sub-menu">
                        <a href="{{ route('indikator.index') }}" class="sub-link {{ request()->routeIs('indikator.*') ? 'active' : '' }}">{{ auth()->user()->isAdmin() ? 'Master Indikator' : 'Indikator Saya' }}</a>
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('anggaran.index') }}" class="sub-link {{ request()->routeIs('anggaran.*') ? 'active' : '' }}">Master Anggaran</a>
                        @endif
                        <a href="{{ route('kegiatan-master.index') }}" class="sub-link {{ request()->routeIs('kegiatan-master.*') ? 'active' : '' }}">{{ auth()->user()->isAdmin() ? 'Master Kegiatan' : 'Kegiatan Saya' }}</a>
                        <a href="{{ route('output-master.index') }}" class="sub-link {{ request()->routeIs('output-master.*') ? 'active' : '' }}">{{ auth()->user()->isAdmin() ? 'Master Output' : 'Output Saya' }}</a>
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('pegawai.index') }}" class="sub-link {{ request()->routeIs('pegawai.*') ? 'active' : '' }}">Master Pegawai</a>
                            <a href="{{ route('tabel-ro.index') }}" class="sub-link {{ request()->routeIs('tabel-ro.*') ? 'active' : '' }}">Master RO</a>
                        @endif
                    </div>
                </div>

                {{-- Pengisian FRA --}}
                @php 
                    $isFraActive = request()->routeIs('capaian-kinerja.*') || request()->routeIs('evaluasi-kinerja.*') || request()->routeIs('analisis-kendala-baru.*') || request()->routeIs('pelaksanaan-rtl-baru.*');
                @endphp
                <a class="nav-link {{ $isFraActive ? '' : 'collapsed' }}" data-bs-toggle="collapse" href="#collapseFra" role="button" aria-expanded="{{ $isFraActive ? 'true' : 'false' }}" aria-controls="collapseFra" data-title="Pengisian FRA">
                    <i class="fas fa-pen-to-square"></i> <span class="link-text">Pengisian FRA</span>
                    <i class="fas fa-chevron-down ms-auto arrow-icon"></i>
                </a>
                <div class="collapse {{ $isFraActive ? 'show' : '' }}" id="collapseFra">
                    <div class="sub-menu">
                        <a href="{{ route('capaian-kinerja.index') }}" class="sub-link {{ request()->routeIs('capaian-kinerja.*') ? 'active' : '' }}">Capaian Kinerja</a>
                        <a href="{{ route('evaluasi-kinerja.index') }}" class="sub-link {{ request()->routeIs('evaluasi-kinerja.*') ? 'active' : '' }}">Evaluasi Kinerja</a>
                        <a href="{{ route('analisis-kendala-baru.index') }}" class="sub-link {{ request()->routeIs('analisis-kendala-baru.*') ? 'active' : '' }}">Pengisian Analisis & Kendala (Baru)</a>
                        <a href="{{ route('pelaksanaan-rtl-baru.index') }}" class="sub-link {{ request()->routeIs('pelaksanaan-rtl-baru.*') ? 'active' : '' }}">Pelaksanaan RTL (Baru)</a>
                    </div>
                </div>

                {{-- Monitoring --}}
                @php 
                    $isMonitoringActive = request()->routeIs('monitoring-capaian.*') || request()->routeIs('monitoring-rtl.*') || request()->routeIs('monitoring-manajerial.*') || request()->routeIs('analisis.*');
                @endphp
                <a class="nav-link {{ $isMonitoringActive ? '' : 'collapsed' }}" data-bs-toggle="collapse" href="#collapseMonitoring" role="button" aria-expanded="{{ $isMonitoringActive ? 'true' : 'false' }}" aria-controls="collapseMonitoring" data-title="Monitoring">
                    <i class="fas fa-display"></i> <span class="link-text">Monitoring</span>
                    <i class="fas fa-chevron-down ms-auto arrow-icon"></i>
                </a>
                <div class="collapse {{ $isMonitoringActive ? 'show' : '' }}" id="collapseMonitoring">
                    <div class="sub-menu">
                        <a href="{{ route('monitoring-capaian.index') }}" class="sub-link {{ request()->routeIs('monitoring-capaian.*') ? 'active' : '' }}">Monitoring Capaian</a>
                        <a href="{{ route('monitoring-rtl.index') }}" class="sub-link {{ request()->routeIs('monitoring-rtl.*') ? 'active' : '' }}">Monitoring RTL</a>
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('monitoring-manajerial.index') }}" class="sub-link {{ request()->routeIs('monitoring-manajerial.*') ? 'active' : '' }}">Monitoring Manajerial</a>
                            <a href="{{ route('analisis.index') }}" class="sub-link {{ request()->routeIs('analisis.*') ? 'active' : '' }}">Analisis & Kendala</a>
                        @endif
                    </div>
                </div>

                {{-- Lainnya --}}
                @php 
                    $isLainnyaActive = request()->routeIs('rekap.capaian') || request()->routeIs('template.word.*');
                @endphp
                <a class="nav-link {{ $isLainnyaActive ? '' : 'collapsed' }}" data-bs-toggle="collapse" href="#collapseLainnya" role="button" aria-expanded="{{ $isLainnyaActive ? 'true' : 'false' }}" aria-controls="collapseLainnya" data-title="Lainnya">
                    <i class="fas fa-bars-staggered"></i> <span class="link-text">Lainnya</span>
                    <i class="fas fa-chevron-down ms-auto arrow-icon"></i>
                </a>
                <div class="collapse {{ $isLainnyaActive ? 'show' : '' }}" id="collapseLainnya">
                    <div class="sub-menu">
                        <a href="{{ route('rekap.capaian') }}" class="sub-link {{ request()->routeIs('rekap.capaian') ? 'active' : '' }}">Rekap Capaian Kinerja</a>
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('template.word.index') }}" class="sub-link {{ request()->routeIs('template.word.*') ? 'active' : '' }}">Template Word</a>
                        @endif
                    </div>
                </div>
            </nav>
        </div>
    </div>

    <div id="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-white bg-white shadow-sm border rounded-3 p-2 text-dark d-flex align-items-center justify-content-center" id="sidebarToggle" title="Toggle Sidebar" style="width: 38px; height: 38px;">
                    <i class="fas fa-bars"></i>
                </button>
                <h4 class="fw-bold mb-0">@yield('title')</h4>
            </div>
            <div class="user-info dropdown">
                <div class="user-profile-btn d-flex align-items-center" data-bs-toggle="dropdown"
                    style="cursor: pointer;">
                    <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name }}&background=4361ee&color=fff"
                        class="rounded-circle me-3 shadow-sm" width="35" alt="avatar">
                    <div class="me-2 d-none d-md-block">
                        <div class="fw-bold text-dark small" style="line-height: 1.2;">{{ Auth::user()->name }}</div>
                        <div class="text-muted extra-small" style="font-size: 0.7rem;">
                            {{ ucfirst(Auth::user()->role ?? 'User') }}
                        </div>
                    </div>
                    <i class="fas fa-chevron-down text-muted ms-1" style="font-size: 0.7rem;"></i>
                </div>

                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 mt-2 py-2"
                    style="min-width: 200px;">
                    <li class="px-3 py-2 border-bottom mb-2 d-md-none text-center">
                        <div class="fw-bold text-dark">{{ Auth::user()->name }}</div>
                        <div class="small text-muted">{{ ucfirst(Auth::user()->role ?? 'User') }}</div>
                    </li>
                    <li>
                        <a class="dropdown-item py-2 px-3 rounded-3 mx-2 w-auto" href="#" data-bs-toggle="modal"
                            data-bs-target="#modalProfile">
                            <i class="fas fa-user-circle me-2 text-primary"></i> Profil Saya
                        </a>
                    </li>
                    @if(auth()->user()->isAdmin())
                    <li>
                        <a class="dropdown-item py-2 px-3 rounded-3 mx-2 w-auto" href="#" data-bs-toggle="modal"
                            data-bs-target="#modalSettings">
                            <i class="fas fa-cog me-2 text-warning"></i> Pengaturan Periode
                        </a>
                    </li>
                    @endif
                    <li>
                        <hr class="dropdown-divider mx-3 opacity-50">
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item py-2 px-3 rounded-3 mx-2 w-auto text-danger">
                                <i class="fas fa-right-from-bracket me-2"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>

        @if (session('success'))
            <div
                class="alert alert-success border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center p-3 animate__animated animate__fadeIn">
                <i class="fas fa-check-circle me-2 fs-5"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div
                class="alert alert-danger border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center p-3 animate__animated animate__fadeIn">
                <i class="fas fa-exclamation-circle me-2 fs-5"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        <div class="main-content">
            @yield('content')
        </div>

        <footer class="text-center pt-5">
            <hr class="opacity-10 mb-4">
            &copy; {{ date('Y') }} <span class="text-primary fw-bold">PASKIP</span> - Patrol SAKIP
        </footer>
    </div>

    <!-- Modal Profil Saya -->
    <div class="modal fade" id="modalProfile" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Detail Profil Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formProfile">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body p-4">
                        <div class="text-center mb-4">
                            <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name }}&background=4361ee&color=fff&size=128"
                                class="rounded-circle shadow-sm mb-3 border border-4 border-white" width="80"
                                alt="avatar">
                            <h5 class="fw-bold mb-0" id="profile_display_name">{{ Auth::user()->name }}</h5>
                            <p class="text-muted small mb-0">{{ Auth::user()->email }}</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold extra-small text-muted mb-1">NAMA LENGKAP</label>
                                <input type="text" name="name"
                                    class="form-control form-control-sm rounded-3 shadow-none border-light-subtle"
                                    value="{{ Auth::user()->name }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold extra-small text-muted mb-1">EMAIL</label>
                                <input type="email" name="email"
                                    class="form-control form-control-sm rounded-3 shadow-none border-light-subtle"
                                    value="{{ Auth::user()->email }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold extra-small text-muted mb-1">NOMOR HP / WA</label>
                                <input type="text" name="no_hp"
                                    class="form-control form-control-sm rounded-3 shadow-none border-light-subtle"
                                    value="{{ Auth::user()->pegawai->no_hp ?? '' }}" placeholder="08xxxxxxxxxx">
                            </div>

                            <div class="col-12 border-top pt-3 mt-4">
                                <div class="alert alert-light border-0 small py-2 mb-3">
                                    <i class="fas fa-key me-2 text-primary"></i> <span class="fw-bold">Ganti
                                        Password</span> (Kosongkan jika tidak ingin diubah)
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold extra-small text-muted mb-1">PASSWORD BARU</label>
                                <input type="password" name="password"
                                    class="form-control form-control-sm rounded-3 shadow-none border-light-subtle">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold extra-small text-muted mb-1">KONFIRMASI
                                    PASSWORD</label>
                                <input type="password" name="password_confirmation"
                                    class="form-control form-control-sm rounded-3 shadow-none border-light-subtle">
                            </div>

                            <div class="col-12 border-top pt-3 mt-4">
                                <div class="bg-light rounded-4 p-3 border-0">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <span class="text-muted extra-small fw-bold d-block mb-0">Role Akses</span>
                                            <span
                                                class="badge bg-primary rounded-pill extra-small px-3">{{ strtoupper(Auth::user()->role ?? 'USER') }}</span>
                                        </div>
                                        <div class="col-6">
                                            <span class="text-muted extra-small fw-bold d-block mb-0">Status Akun</span>
                                            <span class="text-success extra-small fw-bold"><i class="fas fa-circle me-1"
                                                    style="font-size: 0.5rem;"></i> Active</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 justify-content-center pb-4">
                        <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm"
                            id="btnUpdateProfile">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Pengaturan Periode (Admin Only) -->
    @if(auth()->user()->isAdmin())
    <div class="modal fade" id="modalSettings" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <form action="{{ route('settings.store') }}" method="POST" class="modal-content border-0 shadow-lg rounded-4">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark"><i class="fas fa-cog text-warning me-2"></i>Pengaturan Periode</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Tahun Default</label>
                        <select name="default_tahun" class="form-select rounded-3">
                            @for($i = date('Y') - 2; $i <= date('Y') + 2; $i++)
                                <option value="{{ $i }}" {{ \App\Models\Setting::get('default_tahun', date('Y')) == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Triwulan Default</label>
                        <select name="default_triwulan" class="form-select rounded-3">
                            @for($i = 1; $i <= 4; $i++)
                                <option value="{{ $i }}" {{ \App\Models\Setting::get('default_triwulan', ceil(date('n')/3)) == $i ? 'selected' : '' }}>Q{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="text-muted extra-small">
                        *Pengaturan ini akan menjadi periode default bagi seluruh pengguna saat membuka halaman.
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4 justify-content-center">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- TinyMCE Rich Text Editor -->
    <script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js" referrerpolicy="origin"></script>

    <!-- JS -->

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            // DataTables Language ID
            window.DATATABLES_ID = {
                "sEmptyTable": "Tidak ada data yang tersedia pada tabel ini",
                "sProcessing": "Sedang memproses...",
                "sLengthMenu": "Tampilkan _MENU_ entri",
                "sZeroRecords": "Tidak ditemukan data yang sesuai",
                "sSearch": "Cari:",
                "sInfo": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                "sInfoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
                "sInfoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
                "sInfoPostFix": "",
                "sUrl": "",
                "oPaginate": {
                    "sFirst": "Pertama",
                    "sPrevious": "Sebelumnya",
                    "sNext": "Selanjutnya",
                    "sLast": "Terakhir"
                }
            };

            // Toastr Configuration
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "3000"
            };

            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });

            // Update Profile AJAX
            $('#formProfile').on('submit', function(e) {
                e.preventDefault();
                const btn = $('#btnUpdateProfile');
                btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

                $.ajax({
                    url: "{{ route('profile.update') }}",
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        toastr.success(response.message);
                        $('#modalProfile').modal('hide');
                        btn.prop('disabled', false).text('Simpan Perubahan');

                        // Digital Update UI
                        $('#profile_display_name').text(response.user.name);
                        $('div.fw-bold.text-dark.small').text(response.user
                            .name); // update header

                        // Clear password fields
                        $('input[name="password"]').val('');
                        $('input[name="password_confirmation"]').val('');
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).text('Simpan Perubahan');
                        const errors = xhr.responseJSON.errors;
                        if (errors) {
                            Object.values(errors).forEach(err => toastr.error(err[0]));
                        } else {
                            toastr.error('Terjadi kesalahan saat memperbarui profil.');
                        }
                    }
                });
            });
            // TinyMCE Global Initialization
            window.initTinyMCE = function(selector) {
                tinymce.init({
                    selector: selector,
                    height: 350,
                    menubar: false,
                    plugins: 'lists charmap visualblocks code fullscreen help wordcount advlist',
                    toolbar: 'undo redo | blocks fontsize | bold italic underline strikethrough | forecolor backcolor | superscript subscript | alignleft aligncenter alignright alignjustify | bullist numlist | charmap formula | removeformat | fullscreen',
                    toolbar_mode: 'sliding',
                    content_style: 'body { font-family:Outfit,Helvetica,Arial,sans-serif; font-size:14px }',


                    setup: function(editor) {
                        editor.ui.registry.addButton('formula', {
                            text: 'Formula',
                            icon: 'character-count',
                            onAction: function() {
                                editor.windowManager.open({
                                    title: 'Insert LaTeX Formula',
                                    size: 'normal',
                                    body: {
                                        type: 'panel',
                                        items: [{
                                            type: 'textarea',
                                            name: 'latex',
                                            label: 'LaTeX Syntax (e.g. \\frac{X}{Y} \\times 100%)'
                                        },
                                        {
                                            type: 'htmlpanel',
                                            html: '<div style="margin-top:15px; font-size:12px; font-weight:bold; color:#6c757d; margin-bottom:8px;">PREVIEW</div><div id="latex-preview" style="min-height:80px; padding:15px; border:1px solid #dee2e6; border-radius:8px; background:#fff; display:flex; align-items:center; justify-content:center; overflow-x:auto;"><em>Ketik syntax lalu tekan Preview atau klik di luar kotak.</em></div>'
                                        }]
                                    },
                                    buttons: [{
                                            type: 'custom',
                                            name: 'previewBtn',
                                            text: 'Preview',
                                            primary: false
                                        },
                                        {
                                            type: 'cancel',
                                            text: 'Close'
                                        },
                                        {
                                            type: 'submit',
                                            text: 'Insert',
                                            primary: true
                                        }
                                    ],
                                    onAction: function(api, details) {
                                        if (details.name === 'previewBtn') {
                                            const data = api.getData();
                                            const previewEl = document.getElementById('latex-preview');
                                            if (previewEl && window.MathJax) {
                                                previewEl.innerHTML = '\\(' + (data.latex || '') + '\\)';
                                                if (MathJax.typesetPromise) {
                                                    MathJax.typesetPromise([previewEl]).catch(err => console.error(err));
                                                }
                                            }
                                        }
                                    },
                                    onChange: function(api) {
                                        const data = api.getData();
                                        const previewEl = document.getElementById('latex-preview');
                                        if (previewEl && window.MathJax) {
                                            previewEl.innerHTML = '\\(' + (data.latex || '') + '\\)';
                                            if (MathJax.typesetPromise) {
                                                MathJax.typesetPromise([previewEl]).catch(err => console.error(err));
                                            }
                                        }
                                    },
                                    onSubmit: function(api) {
                                        const data = api.getData();
                                        if (data.latex) {
                                            let rawLatex = data.latex.trim();
                                            if (rawLatex.startsWith('$$') && rawLatex.endsWith('$$')) {
                                                rawLatex = rawLatex.substring(2, rawLatex.length - 2).trim();
                                            } else if (rawLatex.startsWith('\\(') && rawLatex.endsWith('\\)')) {
                                                rawLatex = rawLatex.substring(2, rawLatex.length - 2).trim();
                                            } else if (rawLatex.startsWith('\\[') && rawLatex.endsWith('\\]')) {
                                                rawLatex = rawLatex.substring(2, rawLatex.length - 2).trim();
                                            }

                                            const encodedLatex = encodeURIComponent(rawLatex);
                                            const imgHtml = `<img class="latex-math" src="https://latex.codecogs.com/svg.image?${encodedLatex}" data-latex="${rawLatex}" alt="math formula" style="vertical-align: middle; cursor: pointer;" />&nbsp;`;
                                            editor.insertContent(imgHtml);
                                        }
                                        api.close();
                                    }
                                });
                            }
                        });
                        editor.on('change', function() {
                            editor.save();
                        });
                    }
                });
            };

            // Fix TinyMCE focus problem in Bootstrap Modal (for TinyMCE 7)
            document.addEventListener('focusin', (e) => {
                if (e.target.closest(
                        ".tox-tinymce, .tox-tinymce-aux, .moxman-window, .tam-assetmanager-root"
                        ) !== null) {
                    e.stopImmediatePropagation();
                }
            });

        });

        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarToggle');
        const SIDEBAR_KEY = 'sidebar_collapsed';

        // Restore sidebar state from localStorage
        if (localStorage.getItem(SIDEBAR_KEY) === 'true') {
            sidebar.classList.add('collapsed');
            toggleBtn.querySelector('i').classList.replace('fa-bars', 'fa-bars-staggered');
        }

        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem(SIDEBAR_KEY, isCollapsed);
            const icon = this.querySelector('i');
            if (isCollapsed) {
                icon.classList.replace('fa-bars', 'fa-bars-staggered');
            } else {
                icon.classList.replace('fa-bars-staggered', 'fa-bars');
            }
        });
    </script>

    @yield('scripts')
</body>

</html>