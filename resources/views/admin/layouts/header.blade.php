<div class="topbar d-print-none">
    <div class="container-fluid">
        <nav class="topbar-custom d-flex justify-content-between" id="topbar-custom">    
            <ul class="topbar-item list-unstyled d-inline-flex align-items-center mb-0">                        
                <li>
                    <button class="nav-link mobile-menu-btn nav-icon" id="togglemenu">
                        <i class="iconoir-menu"></i>
                    </button>
                </li> 
                <li class="hide-phone app-search">
                    <form role="search" action="#" method="get">
                        <input type="search" name="search" class="form-control top-search mb-0" placeholder="Search here...">
                        <button type="submit"><i class="iconoir-search"></i></button>
                    </form>
                </li>                    
            </ul>

            <ul class="topbar-item list-unstyled d-inline-flex align-items-center mb-0">

                <!-- Language dropdown -->
                <li class="dropdown">
                    <a class="nav-link dropdown-toggle arrow-none nav-icon" data-bs-toggle="dropdown" href="#" role="button"
                        aria-haspopup="false" aria-expanded="false" data-bs-offset="0,19">
                        <img src="{{ asset('assets/images/flags/us_flag.jpg') }}" alt="English" class="thumb-sm rounded-circle">
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="#"><img src="{{ asset('assets/images/flags/us_flag.jpg') }}" alt="" height="15" class="me-2">English</a>
                        <a class="dropdown-item" href="#"><img src="{{ asset('assets/images/flags/spain_flag.jpg') }}" alt="" height="15" class="me-2">Spanish</a>
                        <a class="dropdown-item" href="#"><img src="{{ asset('assets/images/flags/germany_flag.jpg') }}" alt="" height="15" class="me-2">German</a>
                        <a class="dropdown-item" href="#"><img src="{{ asset('assets/images/flags/french_flag.jpg') }}" alt="" height="15" class="me-2">French</a>
                    </div>
                </li>

                <!-- Light/Dark mode toggle -->
                <li class="topbar-item">
                    <a class="nav-link nav-icon" href="javascript:void(0);" id="light-dark-mode">
                        <i class="iconoir-half-moon dark-mode"></i>
                        <i class="iconoir-sun-light light-mode"></i>
                    </a>                    
                </li>

                <!-- Notifications -->
                <li class="dropdown topbar-item">
                    <a class="nav-link dropdown-toggle arrow-none nav-icon" data-bs-toggle="dropdown" href="#" role="button"
                        aria-haspopup="false" aria-expanded="false" data-bs-offset="0,19">
                        <i class="iconoir-bell"></i>
                        <span class="alert-badge"></span>
                    </a>
                    <div class="dropdown-menu stop dropdown-menu-end dropdown-lg py-0">
                        <h5 class="dropdown-item-text m-0 py-3 d-flex justify-content-between align-items-center">
                            Notifications 
                            <a href="#" class="badge text-body-tertiary badge-pill">
                                <i class="iconoir-plus-circle fs-4"></i>
                            </a>
                        </h5>

                        <!-- Tabs -->
                        <ul class="nav nav-tabs nav-tabs-custom nav-success nav-justified mb-1" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link mx-0 active" data-bs-toggle="tab" href="#All" role="tab">All 
                                    <span class="badge bg-primary-subtle text-primary badge-pill ms-1">24</span>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link mx-0" data-bs-toggle="tab" href="#Projects" role="tab">Projects</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link mx-0" data-bs-toggle="tab" href="#Teams" role="tab">Team</a>
                            </li>
                        </ul>

                        <div class="ms-0" style="max-height:230px;" data-simplebar>
                            <div class="tab-content" id="myTabContent">
                                <!-- All notifications -->
                                <div class="tab-pane fade show active" id="All" role="tabpanel">
                                    <a href="#" class="dropdown-item py-3">
                                        <small class="float-end text-muted ps-2">2 min ago</small>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 bg-primary-subtle text-primary thumb-md rounded-circle">
                                                <i class="iconoir-wolf fs-4"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-2 text-truncate">
                                                <h6 class="my-0 fw-normal text-dark fs-13">Your order is placed</h6>
                                                <small class="text-muted mb-0">Dummy text of the printing and industry.</small>
                                            </div>
                                        </div>
                                    </a>
                                    <!-- Add your other notifications similarly -->
                                </div>
                            </div>
                        </div>

                        <a href="{{ url('admin/notifications') }}" class="dropdown-item text-center text-dark fs-13 py-2">
                            View All <i class="fi-arrow-right"></i>
                        </a>
                    </div>
                </li>

                <!-- User dropdown -->
                <li class="dropdown topbar-item">
                    <a class="nav-link dropdown-toggle arrow-none nav-icon" data-bs-toggle="dropdown" href="#" role="button"
                        aria-haspopup="false" aria-expanded="false" data-bs-offset="0,19">
                       <span class="thumb-md rounded-circle bg-light d-flex justify-content-center align-items-center shadow-sm" 
                            style="width:40px; height:40px;">
                            <i class="fas fa-user text-secondary fs-5"></i>
                        </span>

                    </a>
                    <div class="dropdown-menu dropdown-menu-end py-0">
                        <div class="d-flex align-items-center dropdown-item py-2 bg-secondary-subtle">
                            
                            <div class="flex-grow-1 ms-2 text-truncate align-self-center">
                                
                            </div>
                        </div>
                        <div class="dropdown-divider mt-0"></div>

                        <small class="text-muted px-2 pb-1 d-block">Account</small>
                        

                       <form method="POST" action="{{ route('logout') }}" id="logout-form" class="d-inline">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger bg-transparent border-0 w-100 text-start">
                                <i class="las la-power-off fs-18 me-1 align-text-bottom"></i> Logout
                            </button>
                        </form>

                    </div>
                </li>
            </ul><!--end topbar-nav-->
        </nav>
    </div>
</div>
