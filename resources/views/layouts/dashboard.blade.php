@php
    $role = $role ?? 'student';
    $authUser = auth()->user();
    $dashboardConfig = config("dashboard.roles.{$role}");
    $menuItems = $dashboardConfig['menu'] ?? [];
    $user = $authUser ? [
        'name' => $authUser->name,
        'email' => $authUser->email,
        'avatar' => 'photo-1472099645785-5658abf4ff4e',
    ] : ($dashboardConfig['user'] ?? ['name' => 'User', 'email' => '', 'avatar' => 'photo-1472099645785-5658abf4ff4e']);
    $searchPlaceholder = $dashboardConfig['search_placeholder'] ?? 'Search...';
    $profileRoute = $dashboardConfig['profile_route'] ?? null;
    $settingsRoute = $dashboardConfig['settings_route'] ?? null;
    $currentRoute = request()->route()?->getName();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard — SkillForge')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans bg-gray-50 text-gray-900 antialiased overflow-hidden">
    <div class="flex h-screen overflow-hidden">
        <div id="sidebar-overlay" class="fixed inset-0 bg-black/30 z-40 hidden lg:hidden"></div>

        <aside id="dashboard-sidebar" class="fixed lg:sticky top-0 left-0 z-50 w-64 h-screen shrink-0 bg-white border-r border-gray-100 flex flex-col transition-transform duration-300 -translate-x-full lg:translate-x-0">
            <div class="p-6">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-brand rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-800" fill="currentColor" viewBox="0 0 20 20"><path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3z"/></svg>
                    </div>
                    <span class="text-xl font-bold">SkillForge</span>
                </a>
            </div>

            <nav class="flex-1 px-4 space-y-1 custom-scrollbar overflow-y-auto">
                <p class="px-3 pt-2 pb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Menu</p>
                @foreach($menuItems as $item)
                @php
                    $href = isset($item['route']) && $item['route'] ? route($item['route']) : '#';
                    $activePattern = $item['active'] ?? (($item['route'] ?? null) ? $item['route'].'*' : null);
                    $isActive = is_array($activePattern)
                        ? request()->routeIs(...$activePattern)
                        : ($activePattern && request()->routeIs($activePattern));
                @endphp
                <a href="{{ $href }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-300 {{ $isActive ? 'bg-brand-light text-brand-dark' : 'text-gray-600 hover:bg-gray-50 hover:text-brand-dark' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $item['icon'] }}"/></svg>
                    {{ $item['label'] }}
                    @if(!empty($item['badge']))
                    <span class="ml-auto text-xs font-bold bg-gray-900 text-white min-w-[1.25rem] h-5 px-1.5 rounded-full flex items-center justify-center">{{ $item['badge'] }}</span>
                    @endif
                </a>
                @endforeach

                <p class="px-3 pt-6 pb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">General</p>
                @if($profileRoute)
                <a href="{{ route($profileRoute) }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs($profileRoute) ? 'bg-brand-light text-brand-dark' : 'text-gray-600 hover:bg-gray-50 hover:text-brand-dark' }}">Profile</a>
                @else
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-brand-dark transition-all duration-300">Profile</a>
                @endif
                @if($settingsRoute)
                <a href="{{ route($settingsRoute) }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-300 {{ request()->routeIs($settingsRoute) ? 'bg-brand-light text-brand-dark' : 'text-gray-600 hover:bg-gray-50 hover:text-brand-dark' }}">Settings</a>
                @else
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-brand-dark transition-all duration-300">Settings</a>
                @endif
                <form action="{{ route('logout') }}" method="post">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-red-500 hover:bg-red-50 transition-all duration-300 text-left">
                        Log Out
                    </button>
                </form>
            </nav>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 min-h-0">
            <header class="shrink-0 z-30 bg-white/80 backdrop-blur-md border-b border-gray-100">
                <div class="flex items-center gap-4 px-4 sm:px-6 lg:px-8 h-16">
                    <button id="sidebar-toggle" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors duration-300" aria-label="Toggle sidebar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>

                    <div class="flex-1 min-w-0">
                        <div class="relative">
                            <svg class="absolute left-3 sm:left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" placeholder="{{ $searchPlaceholder }}" class="w-full pl-9 sm:pl-11 pr-3 sm:pr-4 py-2 sm:py-2.5 bg-gray-50 border border-gray-100 rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand transition-all duration-300">
                        </div>
                    </div>

                    <div class="flex items-center gap-1 sm:gap-2 shrink-0">
                        <button class="relative p-2 rounded-full hover:bg-gray-100 transition-colors duration-300" aria-label="Notifications">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
                        </button>
                        <div class="flex items-center gap-2 sm:gap-3 ml-1 sm:ml-2 pl-2 sm:pl-4 border-l border-gray-200">
                            <img src="https://images.unsplash.com/{{ $user['avatar'] }}?w=80&h=80&fit=crop&crop=face" alt="{{ $user['name'] }}" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full object-cover ring-2 ring-brand/20">
                            <div class="hidden md:block">
                                <p class="text-sm font-semibold">{{ $user['name'] }}</p>
                                <p class="text-xs text-gray-400">{{ $user['email'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 min-h-0 p-4 sm:p-6 lg:p-8 custom-scrollbar overflow-y-auto">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
