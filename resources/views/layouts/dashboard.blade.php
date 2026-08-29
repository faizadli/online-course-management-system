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
<body class="font-sans bg-gray-50 text-gray-900 antialiased">
    <div class="flex min-h-screen">
        {{-- Mobile overlay --}}
        <div id="sidebar-overlay" class="fixed inset-0 bg-black/30 z-40 hidden lg:hidden"></div>

        {{-- Sidebar --}}
        <aside id="dashboard-sidebar" class="fixed lg:static inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-100 flex flex-col transition-transform duration-300 -translate-x-full lg:translate-x-0">
            <div class="p-6">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-brand rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-800" fill="currentColor" viewBox="0 0 20 20"><path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3z"/></svg>
                    </div>
                    <span class="text-xl font-bold">SkillForge</span>
                </a>
            </div>

            <nav class="flex-1 px-4 space-y-1 custom-scrollbar overflow-y-auto">
                <p class="px-3 pt-4 pb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Menu</p>
                @php
                $menuItems = [
                    ['label' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'active' => true, 'badge' => null],
                    ['label' => 'Courses', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', 'active' => false, 'badge' => '12'],
                    ['label' => 'Resources', 'icon' => 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z', 'active' => false, 'badge' => null],
                    ['label' => 'Calendar', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'active' => false, 'badge' => null],
                    ['label' => 'Analytics', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'active' => false, 'badge' => null],
                    ['label' => 'Live Class', 'icon' => 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z', 'active' => false, 'badge' => '3'],
                ];
                @endphp
                @foreach($menuItems as $item)
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-300 {{ $item['active'] ? 'bg-brand-light text-brand-dark' : 'text-gray-600 hover:bg-gray-50 hover:text-brand-dark' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $item['icon'] }}"/></svg>
                    {{ $item['label'] }}
                    @if($item['badge'])
                    <span class="ml-auto text-xs font-bold bg-gray-900 text-white w-5 h-5 rounded-full flex items-center justify-center">{{ $item['badge'] }}</span>
                    @endif
                </a>
                @endforeach

                <p class="px-3 pt-6 pb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">General</p>
                @foreach(['Profile', 'Settings', 'Help Center'] as $link)
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-brand-dark transition-all duration-300">
                    {{ $link }}
                </a>
                @endforeach
                <a href="{{ route('home') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-red-500 hover:bg-red-50 transition-all duration-300">
                    Log Out
                </a>
            </nav>

            {{-- Download App CTA --}}
            <div class="p-4 m-4 rounded-2xl bg-gradient-to-br from-gray-900 to-gray-800 text-white">
                <p class="text-sm font-semibold">Download our mobile app</p>
                <p class="text-xs text-gray-400 mt-1">Learn on the go, anytime.</p>
                <button class="mt-3 w-full py-2 bg-brand text-gray-800 text-sm font-semibold rounded-xl hover:bg-brand-dark transition-all duration-300 hover:scale-[1.02]">
                    Download App
                </button>
            </div>
        </aside>

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Top Header --}}
            <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-gray-100">
                <div class="flex items-center gap-4 px-4 sm:px-6 lg:px-8 h-16">
                    <button id="sidebar-toggle" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>

                    <div class="flex-1 min-w-0">
                        <div class="relative">
                            <svg class="absolute left-3 sm:left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" placeholder="Search task..." class="w-full pl-9 sm:pl-11 pr-3 sm:pr-4 py-2 sm:py-2.5 bg-gray-50 border border-gray-100 rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand transition-all duration-300">
                        </div>
                    </div>

                    <div class="flex items-center gap-1 sm:gap-2 shrink-0">
                        <button class="relative p-2 rounded-full hover:bg-gray-100 transition-colors duration-300 hidden sm:block">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        </button>
                        <button class="relative p-2 rounded-full hover:bg-gray-100 transition-colors duration-300">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
                        </button>
                        <div class="flex items-center gap-2 sm:gap-3 ml-1 sm:ml-2 pl-2 sm:pl-4 border-l border-gray-200">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=80&h=80&fit=crop&crop=face" alt="User" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full object-cover ring-2 ring-brand/20">
                            <div class="hidden md:block">
                                <p class="text-sm font-semibold">Hasan Mahmud</p>
                                <p class="text-xs text-gray-400">hasan@skillforge.com</p>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Page Content --}}
            <main class="flex-1 p-4 sm:p-6 lg:p-8 custom-scrollbar overflow-y-auto">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
