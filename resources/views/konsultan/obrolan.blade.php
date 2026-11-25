<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Obrolan</h2>
    </x-slot>

    @php
        // Ambil URL foto audiens (null kalau belum punya foto)
        $fotoUrl = fn ($u) => $u->foto_profil ? asset('storage/' . $u->foto_profil) : null;
    @endphp

    <div class="chat-page">
        <div class="chat-page-container max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 w-full">
            <div class="chat-panel bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex w-full h-full"
                 style="display: flex; flex-direction: row; height: 100%; min-height: 0;">

                {{-- Kolom kiri: daftar obrolan (Lebar 320px) --}}
                <div class="border-r border-gray-200 flex flex-col bg-white shrink-0 {{ $selected ? 'hidden sm:flex' : 'flex' }}"
                     style="width: 320px; min-width: 320px; max-width: 320px; height: 100%; min-height: 0;">

                    {{-- Kotak Pencarian --}}
                    <div class="p-3.5 border-b border-gray-100 shrink-0">
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </span>
                            <input type="text" id="cari-obrolan" placeholder="Cari obrolan..."
                                class="w-full border border-gray-200 rounded-xl pl-9 pr-3 py-2 text-sm bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                        </div>
                    </div>

                    {{-- List Obrolan --}}
                    <div class="flex-1 overflow-y-auto divide-y divide-gray-100" id="daftar-obrolan" style="flex: 1 1 0%; min-height: 0;">
                        @forelse ($konsultasis as $k)
                            @php
                                $fotoAudiens = $fotoUrl($k->audiens);
                                $isSelected = $selected && $selected->id === $k->id;
                                $pesanTerakhir = $k->pesans->last();
                            @endphp
                            <div class="konsultasi-item group relative flex items-center {{ $isSelected ? 'bg-blue-50/70 border-l-4 border-blue-600' : 'hover:bg-gray-50' }}"
                                data-nama="{{ strtolower($k->audiens->name) }}">
                                <a href="{{ route('konsultan.obrolan', $k->id) }}"
                                    class="flex items-center gap-3 px-4 py-3.5 transition flex-1 min-w-0">

                                    {{-- Avatar --}}
                                    @if ($fotoAudiens)
                                        <img src="{{ $fotoAudiens }}" alt="{{ $k->audiens->name }}"
                                             class="w-11 h-11 shrink-0 rounded-full object-cover bg-gray-200 {{ $isSelected ? 'ring-2 ring-blue-500' : '' }}">
                                    @else
                                        <div class="w-11 h-11 shrink-0 rounded-full {{ $isSelected ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-700' }} flex items-center justify-center font-bold text-sm">
                                            {{ strtoupper(substr($k->audiens->name, 0, 1)) }}
                                        </div>
                                    @endif

                                    {{-- Informasi --}}
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between mb-0.5">
                                            <p class="font-bold text-gray-900 text-sm truncate">{{ $k->audiens->name }}</p>
                                            <span class="text-[11px] text-gray-400 shrink-0 font-medium ml-1">
                                                {{ $pesanTerakhir ? $pesanTerakhir->created_at->format('H.i') : $k->created_at->format('H.i') }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            @if ($k->status === 'menunggu')
                                                <span class="text-xs font-bold text-amber-600 shrink-0">Menunggu</span>
                                            @elseif ($k->status === 'berlangsung')
                                                <span class="text-xs font-bold text-emerald-600 shrink-0">Aktif</span>
                                            @else
                                                <span class="text-xs font-bold text-gray-500 shrink-0">Selesai</span>
                                            @endif
                                            <span class="text-xs text-gray-500 truncate flex-1">
                                                {{ $pesanTerakhir?->isi_pesan ?? ($k->topik->nama_topik ?? $k->topik_lainnya ?? 'Belum ada pesan') }}
                                            </span>
                                        </div>
                                    </div>
                                </a>

                                <form action="{{ route('konsultasi.destroy', $k->id) }}" method="POST"
                                    onsubmit="return confirm('Sembunyikan konsultasi dengan {{ $k->audiens->name }} dari daftar Anda? Data chat tetap tersimpan di sistem.')"
                                    class="shrink-0 pr-3">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="w-8 h-8 flex items-center justify-center rounded-full text-gray-300 hover:text-red-600 hover:bg-red-50 opacity-0 group-hover:opacity-100 focus:opacity-100 transition"
                                        title="Sembunyikan konsultasi">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        @empty
                            <p class="text-gray-400 text-sm text-center p-6">Belum ada obrolan.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Kolom kanan: ruang chat langsung --}}
                <div class="flex-1 flex flex-col bg-white min-w-0 {{ $selected ? 'flex' : 'hidden sm:flex' }}"
                     style="flex: 1 1 0%; min-width: 0; height: 100%; min-height: 0;">
                    @if ($selected)
                        @include('partials._chat-konsultasi', ['konsultasi' => $selected, 'showBack' => true])
                    @else
                        <div class="flex-1 flex items-center justify-center text-gray-400 text-sm p-6 text-center">
                            <div>
                                <p class="text-gray-600 font-semibold mb-1">Pilih salah satu obrolan</p>
                                <p class="text-gray-400 text-xs">Pilih percakapan dari daftar di sebelah kiri untuk melihat pesan.</p>
                            </div>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const inputCari = document.getElementById('cari-obrolan');
            const daftar = document.getElementById('daftar-obrolan');
            if (inputCari && daftar) {
                inputCari.addEventListener('input', function () {
                    const kata = this.value.trim().toLowerCase();
                    daftar.querySelectorAll('.konsultasi-item').forEach(function (item) {
                        item.style.display = item.dataset.nama.includes(kata) ? '' : 'none';
                    });
                });
            }

            const userId = {{ auth()->id() }};
            if (window.Echo) {
                window.Echo.private(`user.${userId}`)
                    .listen('.AktivitasKonsultasi', () => {
                        window.location.reload();
                    });
            }
        });
    </script>
    @endpush
</x-app-layout>
