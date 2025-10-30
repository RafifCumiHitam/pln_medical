<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title') - PLN Medical System</title>

    {{-- ===== VITE CSS ===== --}}
    @vite([
        'resources/css/app.css',
        'public/css/sb-admin-2.min.css'
    ])

    {{-- ===== FONT & ICONS ===== --}}
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">

    {{-- ===== CHART.JS ===== --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- ===== STACKED EXTRA CSS ===== --}}
    @stack('styles')
</head>

<body id="page-top">
<div id="wrapper">

    {{-- ===== SIDEBAR ===== --}}
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <!-- Brand with 4:3 logo -->
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('dashboard') }}" 
           style="margin-top:10px;">
            <div class="sidebar-brand-icon" 
                 style="height:60px; width:80px; border-radius:12px; border:2px solid #fff; 
                        overflow:hidden; display:flex; align-items:center; justify-content:center; background:#fff;">
                <img src="{{ asset('img/pln.jpg') }}" alt="PLN Medical Logo"
                     style="height:100%; width:100%; object-fit:contain;">
            </div>
            <div class="sidebar-brand-text mx-3" style="margin-top:4px;">PLN Medical</div>
        </a>
        <hr class="sidebar-divider my-0">

        <!-- Dashboard -->
        <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <hr class="sidebar-divider">
        <div class="sidebar-heading">Management</div>

        <!-- Visitors submenu -->
        <li class="nav-item">
            @php
                $visitorsActive = request()->routeIs('visitors.*') ? 'active' : '';
                $visitorsShow = request()->routeIs('visitors.*') ? 'show' : '';
            @endphp
            <a class="nav-link collapsed {{ $visitorsActive }}" href="#" data-toggle="collapse" data-target="#collapseVisitors"
               aria-expanded="{{ $visitorsShow ? 'true' : 'false' }}" aria-controls="collapseVisitors">
                <i class="fas fa-fw fa-users"></i>
                <span>Visitors</span>
            </a>
            <div id="collapseVisitors" class="collapse {{ $visitorsShow }}" aria-labelledby="headingVisitors" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item {{ request()->routeIs('visitors.index') ? 'active' : '' }}" href="{{ route('visitors.index') }}">All Visitors</a>
                    <a class="collapse-item {{ request()->routeIs('visitors.create') ? 'active' : '' }}" href="{{ route('visitors.create') }}">Add Visitor</a>
                </div>
            </div>
        </li>

        <!-- Medicine Stocks submenu -->
        <li class="nav-item">
            @php
                $medActive = request()->routeIs('medicine-stocks.*') || request()->routeIs('medicines.*') ? 'active' : '';
                $medShow = request()->routeIs('medicine-stocks.*') || request()->routeIs('medicines.*') ? 'show' : '';
            @endphp
            <a class="nav-link collapsed {{ $medActive }}" href="#" data-toggle="collapse" data-target="#collapseMedicine"
               aria-expanded="{{ $medShow ? 'true' : 'false' }}" aria-controls="collapseMedicine">
                <i class="fas fa-fw fa-boxes"></i>
                <span>Medicine Stocks</span>
            </a>
            <div id="collapseMedicine" class="collapse {{ $medShow }}" aria-labelledby="headingMedicine" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item {{ request()->routeIs('medicine-stocks.index') ? 'active' : '' }}" href="{{ route('medicine-stocks.index') }}">All Stocks</a>
                    <a class="collapse-item {{ request()->routeIs('medicine-stocks.logs') ? 'active' : '' }}" href="{{ route('medicine-stocks.logs') }}">Logs Medicine</a>
                    <a class="collapse-item {{ request()->routeIs('medicine-stocks.create') ? 'active' : '' }}" href="{{ route('medicine-stocks.create') }}">Add Stock</a>
                    <a class="collapse-item {{ request()->routeIs('medicines.create') ? 'active' : '' }}" href="{{ route('medicines.create') }}">Add Medicine</a>
                </div>
            </div>
        </li>

        <hr class="sidebar-divider">

        <!-- Logout -->
        <li class="nav-item">
            <a class="nav-link" href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fas fa-fw fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                @csrf
            </form>
        </li>

        {{-- Toggle button di sidebar dihapus --}}
        {{-- <div class="text-center d-none d-md-inline">
            <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div> --}}
    </ul>
    {{-- ===== END SIDEBAR ===== --}}

    {{-- ===== CONTENT WRAPPER ===== --}}
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            {{-- ===== TOPBAR ===== --}}
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                {{-- Toggle button di topbar dihapus --}}
                {{-- <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                    <i class="fa fa-bars"></i>
                </button> --}}
                <ul class="navbar-nav ml-auto">
                    <div class="topbar-divider d-none d-sm-block"></div>
                    <li class="nav-item dropdown no-arrow">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                {{ Auth::user()->nama_lengkap ?? 'User' }}
                            </span>
                            <img class="img-profile rounded-circle" src="{{ asset('img/undraw_profile.svg') }}">
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                             aria-labelledby="userDropdown">
                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                Logout
                            </a>
                        </div>
                    </li>
                </ul>
            </nav>
            {{-- ===== END TOPBAR ===== --}}

            <div class="container-fluid">
                @yield('content')
            </div>
        </div>

        {{-- ===== FOOTER ===== --}}
        <footer class="sticky-footer bg-white">
            <div class="container my-auto">
                <div class="copyright text-center my-auto">
                    <span>Copyright &copy; PLN Medical System 2025</span>
                </div>
            </div>
        </footer>
    </div>
    {{-- ===== END CONTENT WRAPPER ===== --}}
</div>

{{-- ===== SCROLL TOP ===== --}}
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

{{-- ===== LOGOUT MODAL ===== --}}
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ready to Leave?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-primary">Logout</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ===== JS GLOBAL (Bootstrap 4.6.2 compatible) ===== --}}
<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>

{{-- ===== SB ADMIN 2 CORE ===== --}}
<script src="{{ asset('js/sb-admin-2.min.js') }}"></script>

{{-- ===== OPTIONAL PLUGINS ===== --}}
<script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

{{-- ===== VITE JS ===== --}}
@vite([
    'resources/js/app.js',
    'public/js/sb-admin-2.min.js'
])

{{-- ===== STACKED EXTRA SCRIPTS ===== --}}
@stack('scripts')

</body>
</html>
