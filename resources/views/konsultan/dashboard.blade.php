<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard Konsultan</h2>
    </x-slot>

    @php
        // Ambil URL foto audiens (null kalau belum punya foto)
        $fotoUrl = fn ($u) => $u->foto_profil ? asset('storage/' . $u->foto_profil) : null;
        $selectedId = $konsultasis->first()?->id;
    @endphp

    <div class="py-8" x-data="tolakModal()">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-xl mb-5 text-sm flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            {{-- Pencarian --}}
            <div class="relative mb-6">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" id="cari-konsultasi" placeholder="Cari permintaan audiens..."
                    class="w-full border border-gray-200 rounded-xl pl-10 pr-4 py-2.5 text-sm bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

                {{-- Daftar permintaan konsultasi (Kiri) --}}
                <div class="lg:col-span-2">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-bold text-gray-800 text-base">Permintaan Konsultasi Terbaru</h3>
                        <span class="text-xs font-semibold text-gray-500 bg-gray-100 px-2.5 py-1 rounded-full">
                            {{ $konsultasis->count() }} Permintaan
                        </span>
                    </div>

                    <div class="space-y-3" id="daftar-konsultasi">
                        @forelse ($konsultasis as $index => $k)
                            @php
                                $fotoAudiens = $fotoUrl($k->audiens);
                                $isInitialActive = ($k->id === $selectedId);
                                $statusBadgeClass = match($k->status) {
                                    'menunggu' => 'bg-amber-100 text-amber-800',
                                    'berlangsung' => 'bg-emerald-100 text-emerald-800',
                                    'selesai' => 'bg-gray-100 text-gray-700',
                                    default => 'bg-gray-100 text-gray-700'
                                };
                            @endphp
                            <div class="konsultasi-item cursor-pointer rounded-xl p-4 transition-all duration-150 border flex items-center gap-3.5 {{ $isInitialActive ? 'bg-blue-600 text-white border-blue-600 shadow-md ring-2 ring-blue-400 ring-offset-2' : 'bg-white text-gray-800 border-gray-200 hover:border-blue-300 hover:shadow-sm' }}"
                                data-id="{{ $k->id }}"
                                data-nama="{{ strtolower($k->audiens->name) }}"
                                onclick="pilihKonsultasi({{ $k->id }})">

                                {{-- Avatar --}}
                                @if ($fotoAudiens)
                                    <img src="{{ $fotoAudiens }}" alt="{{ $k->audiens->name }}"
                                         class="w-11 h-11 rounded-full object-cover shrink-0 {{ $isInitialActive ? 'ring-2 ring-white/80' : 'bg-gray-100' }}">
                                @else
                                    <div class="w-11 h-11 rounded-full flex items-center justify-center font-bold text-sm shrink-0 {{ $isInitialActive ? 'bg-white/20 text-white' : 'bg-blue-100 text-blue-700' }}">
                                        {{ strtoupper(substr($k->audiens->name, 0, 1)) }}
                                    </div>
                                @endif

                                {{-- Informasi --}}
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <p class="font-bold truncate text-sm sm:text-base">{{ $k->audiens->name }}</p>
                                        {{-- Indikator Terpilih --}}
                                        <span class="item-badge-selected text-[10px] font-semibold bg-white/25 text-white px-2 py-0.5 rounded-full {{ $isInitialActive ? '' : 'hidden' }}">
                                            Terpilih ✓
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-xs px-2 py-0.5 rounded-md font-medium text-item-status {{ $isInitialActive ? 'bg-white/20 text-white' : $statusBadgeClass }}">
                                            {{ ucfirst($k->status) }}
                                        </span>
                                        @if ($k->topik || $k->topik_lainnya)
                                            <span class="text-xs truncate text-item-sub {{ $isInitialActive ? 'text-blue-100' : 'text-gray-500' }}">
                                                · {{ $k->topik->nama_topik ?? $k->topik_lainnya }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Waktu & Panah --}}
                                <div class="text-right shrink-0">
                                    <span class="text-xs font-medium block text-item-time {{ $isInitialActive ? 'text-blue-100' : 'text-gray-400' }}">
                                        {{ $k->created_at->format('H.i') }}
                                    </span>
                                    <span class="text-xs mt-1 block opacity-80">
                                        {{ $k->created_at->translatedFormat('d M') }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                                <p class="text-gray-500 text-sm">Belum ada permintaan konsultasi yang masuk.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Detail permintaan & Aksi Konfirmasi (Kanan di desktop, pop-up di mobile) --}}
                <div class="detail-panel lg:sticky lg:top-8" id="detail-panel">
                    <div class="detail-body">
                        <div class="detail-sheet-title mb-3">
                            <h3 class="font-bold text-gray-800 text-base">Detail &amp; Konfirmasi</h3>
                            <button type="button" onclick="tutupDetail()"
                                class="lg:hidden inline-flex items-center justify-center w-9 h-9 rounded-full text-gray-500 hover:bg-gray-100 active:bg-gray-200 transition"
                                aria-label="Tutup detail">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                    @if ($konsultasis->isNotEmpty())
                        <div id="detail-container">
                            @foreach ($konsultasis as $k)
                                @php
                                    $isInitial = ($k->id === $selectedId);
                                    $fotoA = $fotoUrl($k->audiens);
                                    $pesanAwal = $k->pesans->first();
                                @endphp
                                <div id="detail-card-{{ $k->id }}"
                                     class="detail-card bg-white rounded-2xl shadow-sm border border-gray-200 p-5 {{ $isInitial ? '' : 'hidden' }}"
                                     data-id="{{ $k->id }}">

                                    {{-- Header Card --}}
                                    <div class="flex items-center gap-3.5 pb-4 border-b border-gray-100">
                                        <div class="relative shrink-0">
                                            @if ($fotoA)
                                                <img src="{{ $fotoA }}" alt="{{ $k->audiens->name }}"
                                                     class="w-12 h-12 rounded-full object-cover bg-gray-100">
                                            @else
                                                <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-base">
                                                    {{ strtoupper(substr($k->audiens->name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <span class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-green-500 border-2 border-white rounded-full"></span>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="font-bold text-gray-900 text-base truncate">{{ $k->audiens->name }}</p>
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                                <p class="text-xs text-green-700 font-medium">Online</p>
                                            </div>
                                        </div>
                                        <span class="text-xs text-gray-400 font-medium shrink-0">
                                            {{ $k->created_at->format('H.i') }}
                                        </span>
                                    </div>

                                    {{-- Info Topik & Waktu --}}
                                    <div class="mt-4 space-y-3">
                                        <div>
                                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-1">Topik Konsultasi</span>
                                            <span class="inline-block bg-blue-50 text-blue-700 text-xs font-semibold px-3 py-1.5 rounded-lg border border-blue-100">
                                                {{ $k->topik->nama_topik ?? $k->topik_lainnya ?? 'Topik Umum' }}
                                            </span>
                                        </div>

                                        <div>
                                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-1">Isi Pesan / Permintaan</span>
                                            <div class="bg-gray-50 border border-gray-100 rounded-xl p-3 text-sm text-gray-700 leading-relaxed max-h-40 overflow-y-auto">
                                                @if ($pesanAwal && $pesanAwal->isi_pesan)
                                                    {{ $pesanAwal->isi_pesan }}
                                                @else
                                                    <span class="text-gray-400 italic">Belum ada keterangan tertulis.</span>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Status Box --}}
                                        <div class="pt-1">
                                            @if ($k->status === 'menunggu')
                                                <div class="bg-amber-50 border border-amber-200 text-amber-900 rounded-xl p-3 text-xs flex items-start gap-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                    </svg>
                                                    <div>
                                                        <p class="font-bold">Perlu Konfirmasi</p>
                                                        <p class="text-amber-700 mt-0.5">Anda hanya dapat melihat permintaan ini. Harap konfirmasi Terima atau Tolak di bawah.</p>
                                                    </div>
                                                </div>
                                            @elseif ($k->status === 'berlangsung')
                                                <div class="bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-xl p-3 text-xs flex items-center gap-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    <span class="font-semibold">Sudah Diterima · Sedang Berlangsung</span>
                                                </div>
                                            @else
                                                <div class="bg-gray-50 border border-gray-200 text-gray-700 rounded-xl p-3 text-xs">
                                                    <div class="flex items-center gap-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                        <span class="font-medium">Konsultasi Selesai</span>
                                                    </div>
                                                    @if ($k->alasan_tolak)
                                                        <p class="mt-2 text-red-700"><span class="font-semibold">Alasan ditolak:</span> {{ $k->alasan_tolak }}</p>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Tombol Aksi --}}
                                    <div class="mt-5 pt-4 border-t border-gray-100">
                                        @if ($k->status === 'menunggu')
                                            <div class="flex gap-2.5">
                                                {{-- Tombol Tolak --}}
                                                <button type="button"
                                                    @click="bukaTolak('{{ route('konsultasi.tolak', $k->id) }}', @js($k->audiens->name))"
                                                    class="flex-1 w-full bg-red-500 hover:bg-red-600 active:bg-red-700 text-white text-sm font-semibold py-2.5 px-4 rounded-xl transition shadow-sm">
                                                    Tolak
                                                </button>

                                                {{-- Tombol Terima --}}
                                                <form action="{{ route('konsultasi.terima', $k->id) }}" method="POST" class="flex-1">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                        class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold py-2.5 px-4 rounded-xl transition shadow-sm text-center">
                                                        Terima
                                                    </button>
                                                </form>
                                            </div>
                                        @elseif ($k->status === 'berlangsung')
                                            <a href="{{ route('konsultan.obrolan', $k->id) }}"
                                                class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 px-4 rounded-xl transition shadow-sm">
                                                <span>Buka Ruang Obrolan</span>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                                </svg>
                                            </a>
                                        @else
                                            <a href="{{ route('konsultasi.show', $k->id) }}"
                                                class="w-full inline-flex items-center justify-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-semibold py-2.5 px-4 rounded-xl transition">
                                                <span>Lihat Riwayat Obrolan</span>
                                            </a>
                                        @endif
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-white rounded-2xl border border-gray-200 p-6 text-center text-gray-400 text-sm">
                            Pilih salah satu permintaan untuk melihat detail.
                        </div>
                    @endif
                    </div>
                </div>

            </div>
        </div>

        {{-- Modal Tolak Konsultasi --}}
        <div x-cloak x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4" aria-modal="true" role="dialog">
            <div class="absolute inset-0 bg-black/50" @click="tutup()"></div>
            <div class="relative bg-white w-full max-w-md rounded-2xl shadow-xl p-6" @click.stop>
                <h3 class="text-lg font-bold text-gray-900">Tolak Permintaan Konsultasi</h3>
                <p class="text-sm text-gray-600 mt-1">Anda akan menolak permintaan dari <span class="font-semibold" x-text="nama"></span>.</p>
                <form :action="action" method="POST" class="mt-4">
                    @csrf
                    @method('PATCH')
                    <label for="alasan_tolak" class="block text-sm font-medium text-gray-700 mb-1">Alasan penolakan <span class="text-red-500">*</span></label>
                    <textarea id="alasan_tolak" name="alasan_tolak" x-model="alasan" required rows="4" maxlength="1000"
                        class="w-full rounded-xl border-gray-300 focus:border-red-500 focus:ring-red-500 text-sm"
                        placeholder="Tuliskan alasan penolakan agar audiens memahami keputusan Anda..."></textarea>
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" @click="tutup()" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-red-600 hover:bg-red-700">Tolak Konsultasi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function tolakModal() {
            return {
                open: false,
                action: '',
                nama: '',
                alasan: '',
                bukaTolak(action, nama) {
                    this.action = action;
                    this.nama = nama;
                    this.alasan = '';
                    this.open = true;
                    this.$nextTick(() => {
                        const el = document.getElementById('alasan_tolak');
                        if (el) el.focus();
                    });
                },
                tutup() {
                    this.open = false;
                    this.alasan = '';
                }
            };
        }

        function pilihKonsultasi(id) {
            // Update tampilan item kiri
            document.querySelectorAll('.konsultasi-item').forEach(function (item) {
                const isSelected = (item.dataset.id == id);
                const badgeSelected = item.querySelector('.item-badge-selected');
                const textSub = item.querySelector('.text-item-sub');
                const textTime = item.querySelector('.text-item-time');
                const textStatus = item.querySelector('.text-item-status');

                if (isSelected) {
                    item.className = 'konsultasi-item cursor-pointer rounded-xl p-4 transition-all duration-150 border flex items-center gap-3.5 bg-blue-600 text-white border-blue-600 shadow-md ring-2 ring-blue-400 ring-offset-2';
                    if (badgeSelected) badgeSelected.classList.remove('hidden');
                    if (textSub) {
                        textSub.classList.add('text-blue-100');
                        textSub.classList.remove('text-gray-500');
                    }
                    if (textTime) {
                        textTime.classList.add('text-blue-100');
                        textTime.classList.remove('text-gray-400');
                    }
                    if (textStatus) {
                        textStatus.className = 'text-xs px-2 py-0.5 rounded-md font-medium text-item-status bg-white/20 text-white';
                    }
                } else {
                    item.className = 'konsultasi-item cursor-pointer rounded-xl p-4 transition-all duration-150 border flex items-center gap-3.5 bg-white text-gray-800 border-gray-200 hover:border-blue-300 hover:shadow-sm';
                    if (badgeSelected) badgeSelected.classList.add('hidden');
                    if (textSub) {
                        textSub.classList.remove('text-blue-100');
                        textSub.classList.add('text-gray-500');
                    }
                    if (textTime) {
                        textTime.classList.remove('text-blue-100');
                        textTime.classList.add('text-gray-400');
                    }
                    if (textStatus) {
                        const status = item.querySelector('.text-item-status')?.textContent.trim().toLowerCase();
                        let badgeClass = 'bg-gray-100 text-gray-700';
                        if (status === 'menunggu') badgeClass = 'bg-amber-100 text-amber-800';
                        else if (status === 'berlangsung') badgeClass = 'bg-emerald-100 text-emerald-800';
                        textStatus.className = 'text-xs px-2 py-0.5 rounded-md font-medium text-item-status ' + badgeClass;
                    }
                }
            });

            // Tampilkan card detail kanan yang sesuai
            document.querySelectorAll('.detail-card').forEach(function (card) {
                if (card.dataset.id == id) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });

            // Di mobile, detail ditampilkan sebagai pop-up
            if (window.matchMedia('(max-width: 1023.98px)').matches) {
                bukaDetail();
            }
        }

        function bukaDetail() {
            const panel = document.getElementById('detail-panel');
            if (!panel) return;
            panel.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }

        function tutupDetail() {
            const panel = document.getElementById('detail-panel');
            if (!panel) return;
            panel.classList.remove('is-open');
            document.body.style.overflow = '';
        }

        document.addEventListener('DOMContentLoaded', function () {
            const panel = document.getElementById('detail-panel');
            if (panel) {
                panel.addEventListener('click', function (e) {
                    if (e.target === panel) tutupDetail();
                });
            }
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') tutupDetail();
            });

            const userId = {{ auth()->id() }};

            if (window.Echo) {
                window.Echo.private(`user.${userId}`)
                    .listen('.AktivitasKonsultasi', () => {
                        window.location.reload();
                    });
            }

            const inputCari = document.getElementById('cari-konsultasi');
            const daftar = document.getElementById('daftar-konsultasi');
            if (inputCari && daftar) {
                inputCari.addEventListener('input', function () {
                    const kata = this.value.trim().toLowerCase();
                    daftar.querySelectorAll('.konsultasi-item').forEach(function (item) {
                        const match = item.dataset.nama.includes(kata);
                        item.style.display = match ? '' : 'none';
                    });
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
