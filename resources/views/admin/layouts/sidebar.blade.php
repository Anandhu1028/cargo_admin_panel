<!-- leftbar-tab-menu -->
<div class="startbar d-print-none">
    <!--start brand-->
    <div class="brand">
        <a href="{{ url('dashboard') }}" class="logo">
            <span>
                <img src="{{ asset('assets/images/sidebar_logo.png') }}" alt="logo-small" class="logo-sm">
            </span>
            <span>
                <img src="{{ asset('assets/images/sidebar_logo.png') }}" alt="logo-large" class="logo-lg logo-light ">
                <img src="{{ asset('assets/images/sidebar_logo.png') }}" alt="logo-large" class="logo-lg logo-dark">
            </span>
        </a>
    </div>
    <!--end brand-->

    <!--start startbar-menu-->
    <div class="startbar-menu">
        <div class="startbar-collapse" id="startbarCollapse" data-simplebar>
            <div class="d-flex align-items-start flex-column w-100">
                <!-- Navigation -->
                <ul class="navbar-nav mb-auto w-100">
                    <li class="menu-label mt-2">
                        <span></span>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('admin/dashboard') }}">
                            <i class="iconoir-report-columns menu-icon"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('admin/customers') }}">
                            <i class="fas fa-users menu-icon"></i>
                            <span>Coustomer</span>
                        </a>
                    </li>

                  <li class="nav-item 
                    {{ request()->is('admin/rate-calculator') 
                        || request()->is('admin/rate-calculator/step1') 
                        || request()->is('admin/rate-calculator/step2/*') 
                        || request()->is('admin/step3/*') 
                        || request()->is('admin/rate/*/report/full') 
                        ? 'active' : '' }}">
                        
                    <a class="nav-link 
                        {{ request()->is('admin/rate-calculator') 
                            || request()->is('admin/rate-calculator/step1') 
                            || request()->is('admin/rate-calculator/step2/*') 
                            || request()->is('admin/step3/*') 
                            || request()->is('admin/rate/*/report/full') 
                            ? 'active' : '' }}" 
                    href="{{ url('admin/rate-calculator/step1') }}">
                        <i class="fas fa-calculator menu-icon"></i>
                        <span>Rate Calculator</span>
                    </a>
                </li>

                    <!-- Example of image with asset -->
                    <!-- <div class="update-msg text-center">
                        <div class="d-flex justify-content-center align-items-center thumb-lg update-icon-box rounded-circle mx-auto">
                            <img src="{{ asset('assets/images/extra/gold.png') }}" alt="" height="45">
                        </div>
                        <h5 class="mt-3">Today's <span class="text-white">$2450.00</span></h5>
                        <p class="mb-3 text-muted">Today's best Investment for you.</p>
                        <a href="javascript:void(0);" class="btn text-primary shadow-sm rounded-pill px-3">Invest Now</a>
                    </div> -->

                </ul><!--end navbar-nav--->
            </div>
        </div>
    </div><!--end startbar-menu-->
</div><!--end startbar-->
<div class="startbar-overlay d-print-none"></div>
<!-- end leftbar-tab-menu -->
