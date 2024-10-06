<nav class="navbar navbar-expand navbar-light navbar-bg">
    <a class="sidebar-toggle js-sidebar-toggle">
        <i class="hamburger align-self-center"></i>
    </a>

    <div class="navbar-collapse collapse">
        <ul class="navbar-nav navbar-align">
            <!-- Notifications -->
            @php
    $notifications = auth()->user()->unreadNotifications;
    $user = auth()->user();
@endphp

<li class="nav-item dropdown">
    <a class="nav-icon dropdown-toggle" href="#" id="alertsDropdown" data-bs-toggle="dropdown">
        <div class="position-relative">
            <i class="align-middle" data-feather="bell"></i>
            <span class="indicator">{{ $notifications->count() }}</span>
        </div>
    </a>
    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end py-0" aria-labelledby="alertsDropdown">
        <div class="dropdown-menu-header">
            {{ $notifications->count() }} New Notifications
        </div>
        <div class="list-group">
            @foreach($notifications as $notification)
                <a href="#" class="list-group-item">
                    <div class="row g-0 align-items-center">
                        <div class="col-2">
                            <i class="text-primary" data-feather="info"></i>
                        </div>
                        <div class="col-10">
                            <div class="text-dark">{{ $notification->data['message'] }}</div>
                            <div class="text-muted small mt-1">{{ $notification->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="dropdown-menu-footer">
            <a href="#" class="text-muted">Show all notifications</a>
        </div>
    </div>
</li>


            <!-- Messages -->
            <li class="nav-item dropdown">
                <a class="nav-icon dropdown-toggle" href="#" id="messagesDropdown" data-bs-toggle="dropdown">
                    <div class="position-relative">
                        <i class="align-middle" data-feather="message-square"></i>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end py-0" aria-labelledby="messagesDropdown">
                    <div class="dropdown-menu-header">
                        1 New Message
                    </div>
                    <div class="list-group">
                        <a href="#" class="list-group-item">
                            <div class="row g-0 align-items-center">
                                <div class="col-2">
                                    <img src="img/avatars/avatar-1.jpg" class="avatar img-fluid rounded-circle" alt="User Avatar">
                                </div>
                                <div class="col-10 ps-2">
                                    <div class="text-dark">Financial Advisor</div>
                                    <div class="text-muted small mt-1">Great job on saving 20% of your income this month. Let’s schedule a call to discuss investment options.</div>
                                    <div class="text-muted small mt-1">30m ago</div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="dropdown-menu-footer">
                        <a href="#" class="text-muted">Show all messages</a>
                    </div>
                </div>
            </li>

            <!-- User Profile -->
            <li class="nav-item dropdown">
                <a class="nav-icon dropdown-toggle d-inline-block d-sm-none" href="#" data-bs-toggle="dropdown">
                    <i class="align-middle" data-feather="settings"></i>
                </a>

                <a class="nav-link dropdown-toggle d-none d-sm-inline-block" href="#" data-bs-toggle="dropdown">
                    <img src="img/avatars/avatar.jpg" class="avatar img-fluid rounded me-1" alt="John Doe" /> 
                    <span class="text-dark">{{$user->name}}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="profile.html">
                        <i class="align-middle me-1" data-feather="user"></i> Profile
                    </a>
                    <a class="dropdown-item" href="monthly-report.html">
                        <i class="align-middle me-1" data-feather="pie-chart"></i> Monthly Report
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="settings.html">
                        <i class="align-middle me-1" data-feather="settings"></i> Settings
                    </a>
                    <a class="dropdown-item" href="help.html">
                        <i class="align-middle me-1" data-feather="help-circle"></i> Help Center
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#">Log out</a>
                </div>
            </li>
        </ul>
    </div>
</nav>
