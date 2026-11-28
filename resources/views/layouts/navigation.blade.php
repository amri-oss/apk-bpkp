@php
    $user = Auth::user();
    $role = $user->role;

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

    $profilKonsultan = $role === 'konsultan' ? ($user->konsultanProfil ?? null) : null;
    $fotoNav = $profilKonsultan?->foto_profil
        ? asset('storage/' . $profilKonsultan->foto_profil)
        : ($user->foto_profil ? asset('storage/' . $user->foto_profil) : null);

    $roleLabel = match ($role) {
        'admin' => 'Admin',
        'konsultan' => 'Konsultan',
        'audiens' => 'Audiens',
        default => ucfirst((string) $role),
    };

    // Notifikasi chat masuk (hanya untuk peserta konsultasi)
    $totalBelumDibaca = 0;
    $ringkasanNotif = collect();

    if ($role !== 'admin') {
        $queryBelumDibaca = \App\Models\Pesan::belumDibacaOleh($user);
        $totalBelumDibaca = (clone $queryBelumDibaca)->count();

        if ($totalBelumDibaca > 0) {
            $ringkasan = (clone $queryBelumDibaca)
                ->selectRaw('pesans.konsultasi_id as konsultasi_id, COUNT(*) as jumlah, MAX(pesans.created_at) as terakhir')
                ->groupBy('pesans.konsultasi_id')
                ->orderByRaw('MAX(pesans.created_at) DESC')
                ->limit(5)
                ->get();

            $daftarKonsultasi = \App\Models\Konsultasi::with(['audiens', 'konsultan'])
                ->whereIn('id', $ringkasan->pluck('konsultasi_id'))
                ->get()
                ->keyBy('id');

            $ringkasanNotif = $ringkasan->map(function ($baris) use ($daftarKonsultasi, $role) {
                $konsultasi = $daftarKonsultasi->get($baris->konsultasi_id);

                if (!$konsultasi) {
                    return null;
                }

                $lawan = $role === 'konsultan' ? $konsultasi->audiens : $konsultasi->konsultan;

                return [
                    'nama' => $lawan->name ?? 'Pengguna',
                    'inisial' => strtoupper(substr($lawan->name ?? '?', 0, 1)),
                    'jumlah' => (int) $baris->jumlah,
                    'waktu' => \Illuminate\Support\Carbon::parse($baris->terakhir),
                    'url' => $role === 'konsultan'
                        ? route('konsultan.obrolan', $konsultasi->id)
                        : route('audiens.konsultasi-saya', $konsultasi->id),
                ];
            })->filter()->values();
        }
    }
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
</style>

<nav x-data="{ mobileOpen: false, notifOpen: false, profileOpen: false }"
     @click.outside="mobileOpen = false; notifOpen = false; profileOpen = false"
     @keydown.escape.window="mobileOpen = false; notifOpen = false; profileOpen = false"
     class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/95 backdrop-blur">

    {{-- Top bar --}}
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8">
        {{-- Logo --}}
        <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3 no-underline">
            <img src="{{ asset('images/logo-bpkp.png') }}" alt="Logo BPKP" class="h-9 w-auto shrink-0 sm:h-10">
            <div class="min-w-0 hidden sm:block">
                <p class="truncate text-sm font-bold leading-tight text-slate-900">BPKP Perwakilan Sulawesi Selatan</p>
                <p class="text-[11px] font-medium tracking-wide text-slate-500">LONTARA</p>
            </div>
        </a>

        {{-- Right actions --}}
        <div class="flex items-center gap-2 sm:gap-3">
            {{-- Notifications --}}
            <div class="relative">
                <button type="button"
                        @click="notifOpen = !notifOpen; profileOpen = false"
                        class="relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-[#60A5FA]/50 hover:text-[#1E3A8A] focus:outline-none focus:ring-2 focus:ring-[#60A5FA]/30"
                        aria-haspopup="menu" :aria-expanded="notifOpen.toString()" aria-label="Notifikasi">
                    @if ($totalBelumDibaca > 0)
                        <span class="absolute -right-1 -top-1 inline-flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold leading-none text-white ring-2 ring-white">
                            {{ $totalBelumDibaca > 99 ? '99+' : $totalBelumDibaca }}
                        </span>
                    @endif
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                    </svg>
                </button>

                <div x-cloak x-show="notifOpen"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-80 max-w-[calc(100vw-2rem)] origin-top-right rounded-2xl border border-slate-200 bg-white shadow-xl ring-1 ring-black/5"
                     style="display:none" role="menu">
                    <div class="border-b border-slate-100 px-4 py-3">
                        <h6 class="text-sm font-semibold text-slate-900">Notifikasi</h6>
                    </div>
                    <div class="max-h-72 overflow-auto py-1">
                        @forelse ($ringkasanNotif as $notif)
                            <a href="{{ $notif['url'] }}" class="flex items-start gap-3 px-4 py-3 transition hover:bg-slate-50">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#1E3A8A]/10 text-sm font-semibold text-[#1E3A8A]">
                                    {{ $notif['inisial'] }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-slate-900">{{ $notif['nama'] }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        {{ $notif['jumlah'] }} pesan belum dibaca
                                        · {{ $notif['waktu']->isToday() ? $notif['waktu']->format('H:i') : $notif['waktu']->format('d/m') }}
                                    </p>
                                </div>
                                <span class="mt-1 inline-flex h-[18px] min-w-[18px] shrink-0 items-center justify-center rounded-full bg-[#1E3A8A] px-1 text-[10px] font-bold leading-none text-white">
                                    {{ $notif['jumlah'] > 99 ? '99+' : $notif['jumlah'] }}
                                </span>
                            </a>
                        @empty
                            <div class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#1E3A8A]/10 text-[#1E3A8A]">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-slate-900">Belum ada notifikasi baru</p>
                                    <p class="mt-0.5 text-xs text-slate-500">Aktivitas terbaru akan muncul di sini.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                    <div class="border-t border-slate-100 px-4 py-2.5 text-center">
                        @if ($ringkasanNotif->isNotEmpty())
                            <a href="{{ $role === 'konsultan' ? route('konsultan.obrolan', ['daftar' => 1]) : route('audiens.konsultasi-saya') }}"
                               class="text-xs font-semibold text-[#1E3A8A] hover:underline">Lihat semua obrolan</a>
                        @else
                            <span class="text-xs font-medium text-slate-400">Pusat notifikasi</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Profile dropdown --}}
            <div class="relative hidden sm:block">
                <button type="button"
                        @click="profileOpen = !profileOpen; notifOpen = false"
                        class="flex items-center gap-2 rounded-full border border-slate-200 bg-white py-1 pl-1 pr-3 shadow-sm transition hover:border-[#60A5FA]/50 focus:outline-none focus:ring-2 focus:ring-[#60A5FA]/30"
                        aria-haspopup="menu" :aria-expanded="profileOpen.toString()" aria-label="Menu profil">
                    <span class="relative h-9 w-9 shrink-0 overflow-hidden rounded-full bg-slate-200">
                        @if ($fotoNav)
                            <img src="{{ $fotoNav }}" alt="{{ $user->name }}" class="h-full w-full object-cover" onerror="this.remove()">
                        @endif
                        <span class="absolute inset-0 flex items-center justify-center text-sm font-semibold text-[#1E3A8A]">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    </span>
                    <span class="hidden max-w-[140px] truncate text-sm font-semibold text-slate-800 md:block">{{ $user->name }}</span>
                    <svg class="h-4 w-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>

                <div x-cloak x-show="profileOpen"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-64 origin-top-right rounded-2xl border border-slate-200 bg-white shadow-xl ring-1 ring-black/5"
                     style="display:none" role="menu">
                    <div class="flex items-center gap-3 border-b border-slate-100 px-4 py-3">
                        <span class="relative h-10 w-10 shrink-0 overflow-hidden rounded-full bg-slate-200">
                            @if ($fotoNav)
                                <img src="{{ $fotoNav }}" alt="{{ $user->name }}" class="h-full w-full object-cover" onerror="this.remove()">
                            @endif
                            <span class="absolute inset-0 flex items-center justify-center text-sm font-semibold text-[#1E3A8A]">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ $user->name }}</p>
                            <p class="truncate text-xs text-slate-500">{{ $roleLabel }}</p>
                        </div>
                    </div>
                    <div class="py-1">
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 transition hover:bg-slate-50 hover:text-[#1E3A8A]">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                            Profile
                        </a>
                    </div>
                    <div class="border-t border-slate-100 p-2">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-100">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3.25h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21.25h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Mobile hamburger --}}
            <button type="button"
                    @click="mobileOpen = !mobileOpen; notifOpen = false; profileOpen = false"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-[#60A5FA]/50 hover:text-[#1E3A8A] sm:hidden"
                    aria-label="Menu">
                <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <path :class="{'hidden': mobileOpen, 'inline-flex': !mobileOpen}" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path :class="{'hidden': !mobileOpen, 'inline-flex': mobileOpen}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Role menu bar (desktop) --}}
    <div class="hidden sm:block" style="background: var(--app-brand); box-shadow: 0 2px 6px rgba(15,23,42,0.12);">
        <div class="mx-auto flex max-w-7xl flex-wrap px-6 lg:px-8">
            @foreach ($menuItems as $item)
                <a href="{{ route($item['route'], $item['params'] ?? []) }}"
                   class="nav-biru-item {{ request()->routeIs($item['active']) ? 'aktif' : '' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Mobile menu --}}
    <div x-cloak x-show="mobileOpen"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-1"
         class="absolute left-0 right-0 top-full border-t border-white/10 shadow-xl sm:hidden"
         style="display:none; background: var(--app-brand); max-height: calc(100dvh - 74px); overflow-y: auto;">
        @foreach ($menuItems as $item)
            <a href="{{ route($item['route'], $item['params'] ?? []) }}"
               class="nav-biru-item {{ request()->routeIs($item['active']) ? 'aktif' : '' }}">
                {{ $item['label'] }}
            </a>
        @endforeach

        <div style="border-top: 1px solid rgba(255,255,255,0.2); padding: 12px 0;">
            <div class="flex items-center gap-3 px-6 pb-3 text-white">
                <span class="relative h-10 w-10 shrink-0 overflow-hidden rounded-full bg-white/15">
                    @if ($fotoNav)
                        <img src="{{ $fotoNav }}" alt="{{ $user->name }}" class="h-full w-full object-cover" onerror="this.remove()">
                    @endif
                    <span class="absolute inset-0 flex items-center justify-center text-sm font-semibold text-white">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                </span>
                <div class="min-w-0">
                    <div class="truncate text-sm font-bold">{{ $user->name }}</div>
                    <div class="truncate text-xs opacity-80">{{ $user->email }}</div>
                </div>
            </div>
            <a href="{{ route('profile.edit') }}" class="nav-biru-item">Profile</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-biru-item w-full text-left" style="background:none;border:0;cursor:pointer;">
                    Log Out
                </button>
            </form>
        </div>
    </div>
</nav>
