@php
$variant = $variant ?? 'solid';
$navItems = [
    ['label' => 'Home', 'route' => 'home'],
    ['label' => 'Courses', 'route' => 'courses'],
    ['label' => 'About', 'route' => 'about'],
];
@endphp

<header id="landing-nav"
    class="fixed top-0 left-0 right-0 z-50 transition-all duration-500 {{ $variant === 'solid' ? 'nav-scrolled' : '' }}"
    @if($variant === 'hero') data-hero-nav="true" @endif>
    <nav class="w-full max-w-[1600px] mx-auto px-5 sm:px-8 md:px-12 lg:px-14 xl:px-16">
        <div class="flex items-center justify-between h-16 lg:h-20">
            <a href="{{ route('home') }}" class="nav-logo font-display text-xl sm:text-2xl font-bold tracking-tight">
                SkillForge
            </a>

            <div class="hidden lg:flex items-center gap-8">
                @foreach($navItems as $item)
                <a href="{{ route($item['route']) }}"
                    class="nav-link text-sm font-medium hover:text-brand {{ request()->routeIs($item['route']) ? 'nav-link-active' : '' }}">
                    {{ $item['label'] }}
                </a>
                @endforeach
            </div>

            <div class="flex items-center gap-1 sm:gap-2">
                <a href="{{ route('wishlist') }}" class="nav-action-btn nav-icon p-2 rounded-full hidden sm:flex relative" aria-label="Wishlist">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    <span class="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-coral text-[10px] font-bold text-white">3</span>
                </a>
                <a href="{{ route('cart') }}" class="nav-action-btn nav-icon p-2 rounded-full hidden sm:flex relative" aria-label="Cart">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                    <span class="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-coral text-[10px] font-bold text-white">2</span>
                </a>
                <a href="{{ route('login') }}" class="nav-action-btn nav-icon p-2 rounded-full hidden sm:flex" aria-label="Login">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </a>
                <button id="mobile-menu-toggle" class="nav-action-btn nav-toggle lg:hidden p-2 rounded-full" aria-label="Menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>

        <div id="mobile-menu" class="lg:hidden">
            <div class="py-4 space-y-1">
                @foreach($navItems as $item)
                <a href="{{ route($item['route']) }}"
                    class="nav-link block px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs($item['route']) ? 'nav-link-active' : '' }}">
                    {{ $item['label'] }}
                </a>
                @endforeach
                <a href="{{ route('login') }}" class="nav-link block px-4 py-3 text-sm font-medium rounded-xl">Login</a>
                <a href="{{ route('register') }}" class="nav-link block px-4 py-3 text-sm font-medium rounded-xl">Register</a>
                <a href="{{ route('wishlist') }}" class="nav-link block px-4 py-3 text-sm font-medium rounded-xl">Wishlist</a>
                <a href="{{ route('cart') }}" class="nav-link block px-4 py-3 text-sm font-medium rounded-xl">Cart</a>
            </div>
        </div>
    </nav>
</header>
