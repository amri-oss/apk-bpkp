@php
    $user = Auth::user();
    $role = $user->role;

    // Menu per role, sesuai routes/web.php
    $menus = [
        'admin' => [
            ['label' => 'Dashboard',          'route' => 'admin.dashboard',              'active' => 'admin.dashboard'],
            ['label' => 'Kategori Konsultan', 'route' => 'admin.kategori.index',         'active' => 'admin.kategori.*'],
            ['label' => 'Topik Konsultasi',   'route' => 'admin.topik.index',            'active' => 'admin.topik.*'],
            ['label' => 'Kelola Konsultan',   'route' => 'admin.konsultan-profil.index', 'active' => 'admin.konsultan-profil.*'],
            ['label' => 'Akun Pengguna',      'route' => 'admin.users.index',            'active' => 'admin.users.*'],
            ['label' => 'Cetak Laporan',      'route' => 'admin.laporan.index',          'active' => 'admin.laporan.*'],
            ['label' => 'Pantau Percakapan',  'route' => 'admin.pemantauan.index',       'active' => 'admin.pemantauan.*'],
        ],
        'konsultan' => [
            ['label' => 'Dashboard', 'route' => 'konsultan.dashboard', 'active' => 'konsultan.dashboard'],
            ['label' => 'Obrolan',   'route' => 'konsultan.obrolan',   'active' => 'konsultan.obrolan', 'params' => ['daftar' => 1]],
        ],
        'audiens' => [
            ['label' => 'Dashboard',       'route' => 'audiens.dashboard',       'active' => 'audiens.dashboard'],
            ['label' => 'Pilih Konsultan', 'route' => 'audiens.pilih-konsultan', 'active' => 'audiens.pilih-konsultan'],
            ['label' => 'Konsultasi Saya', 'route' => 'audiens.konsultasi-saya', 'active' => 'audiens.konsultasi-saya'],
        ],
    ];

    $menuItems = $menus[$role] ?? $menus['audiens'];

    // Foto avatar: konsultan dari profil konsultan, lainnya dari akun user
    $profilKonsultan = $role === 'konsultan' ? ($user->konsultanProfil ?? null) : null;
    $fotoNav = $profilKonsultan?->foto_profil
        ? asset('storage/' . $profilKonsultan->foto_profil)
        : ($user->foto_profil ? asset('storage/' . $user->foto_profil) : null);
@endphp

<style>
    .nav-biru-item {
        display: block;
        padding: 15px 18px;
        color: #dce6f0;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .015em;
        text-decoration: none;
        white-space: nowrap;
        border-bottom: 2px solid transparent;
        transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
    }
    .nav-biru-item:hover { background: rgba(255,255,255,.06); color: #ffffff; }
    .nav-biru-item.aktif { background: rgba(255,255,255,.07); color: #ffffff; border-bottom-color: var(--app-accent); }

    .nav-topbar {
        max-width: 80rem;
        margin: 0 auto;
        padding: 12px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .nav-topbar .nav-logo { height: 42px; width: auto; }
    .nav-actions { display: flex; align-items: center; gap: 20px; }

    /* Mobile: samakan padding dengan konten (16px) dan rapatkan bar */
    @media (max-width: 639.98px) {
        .nav-topbar { padding: 8px 16px; }
        .nav-topbar .nav-logo { height: 34px; }
        .nav-actions { gap: 10px; }
    }
</style>

<nav x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false" class="sticky top-0 z-50 bg-white border-b border-slate-200">

    {{-- Baris atas: logo, judul, lonceng, avatar --}}
    <div class="nav-topbar">

        <a href="{{ route('dashboard') }}" style="display: flex; align-items: center; gap: 12px; text-decoration: none;">
            <img src="{{ asset('images/logo-bpkp.png') }}" alt="Logo BPKP" class="nav-logo">
            <span class="hidden sm:block" style="font-size: 14px; font-weight: 700; color: #111827;">
                BPKP Perwakilan Sulawesi Selatan
            </span>
        </a>

        <div class="nav-actions">

            {{-- Lonceng --}}
            <button type="button" title="Notifikasi"
                    style="background: none; border: 0; cursor: pointer; padding: 4px; color: #111827;">
                <svg style="width: 22px; height: 22px;" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                </svg>
            </button>

            {{-- Avatar + nama (dropdown profil) --}}
            <div class="hidden sm:block">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button type="button" style="display: flex; align-items: center; gap: 12px; background: none; border: 0; cursor: pointer; padding: 0;">
                            @if ($fotoNav)
                                <img src="{{ $fotoNav }}" alt="{{ $user->name }}"
                                     style="width: 46px; height: 46px; border-radius: 9999px; object-fit: cover; background: #D1D5DB;">
                            @else
                                <span style="width: 46px; height: 46px; border-radius: 9999px; background: #D1D5DB; display: flex; align-items: center; justify-content: center; color: #111827;">
                                    <svg style="width: 22px; height: 22px;" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                                    </svg>
                                </span>
                            @endif

                            <span style="font-size: 17px; font-weight: 700; color: #111827;">{{ $user->name }}</span>

                            <svg style="width: 16px; height: 16px; color: #6B7280;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            {{-- Hamburger (mobile) --}}
            <button @click="open = ! open" class="sm:hidden" type="button"
                    style="background: none; border: 0; cursor: pointer; padding: 6px; color: #6B7280;">
                <svg style="width: 26px; height: 26px;" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Bar biru menu (desktop) --}}
    <div class="hidden sm:block" style="background: var(--app-brand); box-shadow: 0 2px 6px rgba(15,23,42,0.12);">
        <div style="max-width: 80rem; margin: 0 auto; padding: 0 24px; display: flex; flex-wrap: wrap;">
            @foreach ($menuItems as $item)
                <a href="{{ route($item['route'], $item['params'] ?? []) }}"
                   class="nav-biru-item {{ request()->routeIs($item['active']) ? 'aktif' : '' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Menu mobile --}}
    <div x-cloak
         :class="{'block': open, 'hidden': ! open}"
         class="hidden sm:hidden absolute left-0 right-0 top-full shadow-xl border-t border-white/10"
         style="background: var(--app-brand); max-height: calc(100dvh - 74px); overflow-y: auto;">
        @foreach ($menuItems as $item)
            <a href="{{ route($item['route'], $item['params'] ?? []) }}"
               class="nav-biru-item {{ request()->routeIs($item['active']) ? 'aktif' : '' }}">
                {{ $item['label'] }}
            </a>
        @endforeach

        <div style="border-top: 1px solid rgba(255,255,255,0.25); padding: 12px 0;">
            <div style="padding: 0 24px 8px 24px; color: #ffffff;">
                <div style="font-weight: 700; font-size: 14px;">{{ $user->name }}</div>
                <div style="font-size: 12px; opacity: 0.85;">{{ $user->email }}</div>
            </div>

            <a href="{{ route('profile.edit') }}" class="nav-biru-item">Profile</a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <a href="{{ route('logout') }}" class="nav-biru-item"
                   onclick="event.preventDefault(); this.closest('form').submit();">
                    Log Out
                </a>
            </form>
        </div>
    </div>
</nav>
