<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Obrolan</h2>
    </x-slot>

    @php
        // Ambil URL foto audiens (null kalau belum punya foto)
        $fotoUrl = fn ($u) => $u->foto_profil ? asset('storage/' . $u->foto_profil) : null;
    @endphp

    <div class="py-3 px-3 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full flex-1 flex flex-col min-h-0" style="height: calc(100vh - 165px); min-height: 560px;">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-1 min-h-0 w-full h-full" style="height: 100%;">

            {{-- Kolom kiri: daftar obrolan --}}
            <div class="border-r border-gray-200 flex flex-col bg-white shrink-0 {{ $selected ? 'hidden sm:flex' : 'flex' }}"
                 style="width: 320px; min-width: 320px; max-width: 320px; height: 100%;">

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
                        <a href="{{ route('konsultan.obrolan', $k->id) }}"
                            class="konsultasi-item flex items-center gap-3 px-4 py-3.5 transition {{ $isSelected ? 'bg-blue-50/70 border-l-4 border-blue-600' : 'hover:bg-gray-50' }}"
                            data-nama="{{ strtolower($k->audiens->name) }}">
                            
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
                    @empty
                        <p class="text-gray-400 text-sm text-center p-6">Belum ada obrolan.</p>
                    @endforelse
                </div>
            </div>

            {{-- Kolom kanan: ruang chat --}}
            <div class="flex-1 flex flex-col bg-white min-w-0 {{ $selected ? 'flex' : 'hidden sm:flex' }}"
                 style="flex: 1 1 0%; min-width: 0; height: 100%;">
                @if ($selected)
                    @php $fotoSelected = $fotoUrl($selected->audiens); @endphp
                    
                    {{-- Header Chat --}}
                    <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-3 bg-white shrink-0">
                        <a href="{{ route('konsultan.obrolan') }}" class="sm:hidden text-gray-400 hover:text-gray-600 mr-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                        @if ($fotoSelected)
                            <img src="{{ $fotoSelected }}" alt="{{ $selected->audiens->name }}"
                                 class="w-10 h-10 rounded-full object-cover bg-gray-200 shrink-0">
                        @else
                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm shrink-0">
                                {{ strtoupper(substr($selected->audiens->name, 0, 1)) }}
                            </div>
                        @endif
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="font-bold text-gray-900 leading-tight truncate">{{ $selected->audiens->name }}</p>
                                @if ($selected->topik || $selected->topik_lainnya)
                                    <span class="text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-100 px-2.5 py-0.5 rounded-full whitespace-nowrap">
                                        {{ $selected->topik->nama_topik ?? $selected->topik_lainnya }}
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                <p class="text-xs text-green-700 font-medium">Online</p>
                            </div>
                        </div>
                        <a href="{{ route('konsultasi.show', $selected->id) }}"
                            class="ml-auto text-gray-400 hover:text-blue-600 text-xs font-medium hover:underline flex items-center gap-1 shrink-0">
                            <span>Buka chat penuh</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </div>

                    {{-- Isi Chat (Scrollable Area) --}}
                    <div class="flex-1 overflow-y-auto p-4 sm:p-5 flex flex-col gap-2.5 bg-slate-50" id="chat-box" style="flex: 1 1 0%; min-height: 0;">
                        {{-- Kartu Topik --}}
                        @if ($selected->topik || $selected->topik_lainnya)
                            <div class="flex justify-center mb-2">
                                <div class="flex items-center gap-1.5 bg-white border border-gray-200 shadow-xs rounded-full px-4 py-1.5 text-xs text-gray-700 font-medium">
                                    <span>📌</span>
                                    <span>Topik: <strong>{{ $selected->topik->nama_topik ?? $selected->topik_lainnya }}</strong></span>
                                </div>
                            </div>
                        @endif

                        {{-- Daftar Pesan --}}
                        @foreach ($selected->pesans as $pesan)
                            <div class="w-full flex {{ $pesan->pengirim_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                                <div class="max-w-xs sm:max-w-md px-4 py-2.5 rounded-2xl shadow-xs {{ $pesan->pengirim_id === auth()->id() ? 'bg-blue-600 text-white rounded-br-sm' : 'bg-white text-gray-800 border border-gray-200 rounded-bl-sm' }}">
                                    @if ($pesan->isi_pesan)
                                        <p class="text-sm leading-relaxed">{{ $pesan->isi_pesan }}</p>
                                    @endif
                                    <p class="text-[10px] opacity-75 mt-1 text-right">{{ $pesan->created_at->format('H.i') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Bagian Bawah: Proteksi Konfirmasi vs Form Kirim Pesan --}}
                    @if ($selected->status === 'menunggu')
                        {{-- Status Menunggu: Belum Dikonfirmasi -> Konsultan tidak bisa kirim pesan, hanya konfirmasi --}}
                        <div class="p-4 border-t border-amber-200 bg-amber-50 shrink-0">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs sm:text-sm font-bold text-amber-900">Permintaan Belum Dikonfirmasi</p>
                                        <p class="text-[11px] sm:text-xs text-amber-700">Anda hanya dapat melihat permintaan ini. Silakan konfirmasi untuk mulai mengirim pesan.</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <form action="{{ route('konsultasi.tolak', $selected->id) }}" method="POST"
                                          onsubmit="return confirm('Apakah Anda yakin ingin menolak permintaan konsultasi dari {{ $selected->audiens->name }}?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="px-4 py-2 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                                            Tolak
                                        </button>
                                    </form>
                                    <form action="{{ route('konsultasi.terima', $selected->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="px-5 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-semibold rounded-xl shadow-xs transition flex items-center gap-1.5">
                                            <span>Terima Permintaan</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @elseif ($selected->status === 'selesai')
                        <div class="p-3.5 bg-gray-50 border-t border-gray-200 text-center text-xs text-gray-500 font-medium shrink-0">
                            Konsultasi ini sudah selesai. Anda tidak dapat mengirim pesan lagi.
                        </div>
                    @else
                        {{-- Status Berlangsung: SUDAH AKTIF -> Form Kirim Pesan Muncul --}}
                        <form id="form-pesan" class="p-3 border-t border-gray-200 flex gap-2.5 bg-white shrink-0">
                            <input type="text" id="input-pesan" autocomplete="off"
                                class="flex-1 bg-gray-100 border-none rounded-full px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition"
                                placeholder="Kirim Pesan ....">
                            <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold px-6 rounded-full transition shadow-xs">
                                Kirim
                            </button>
                        </form>
                    @endif
                @else
                    <div class="flex-1 flex items-center justify-center text-gray-400 text-sm">
                        Pilih salah satu obrolan di sebelah kiri.
                    </div>
                @endif
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

            @if ($selected)
            const konsultasiId = {{ $selected->id }};
            const userId = {{ auth()->id() }};
            const chatBox = document.getElementById('chat-box');
            const form = document.getElementById('form-pesan');
            const input = document.getElementById('input-pesan');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            function tambahBubble(isiPesan, waktu, pengirimId) {
                if (!chatBox) return;
                const milikSendiri = pengirimId === userId;
                const wrapper = document.createElement('div');
                wrapper.className = 'w-full flex ' + (milikSendiri ? 'justify-end' : 'justify-start');
                wrapper.innerHTML = `
                    <div class="max-w-xs sm:max-w-md px-4 py-2.5 rounded-2xl shadow-xs ${milikSendiri ? 'bg-blue-600 text-white rounded-br-sm' : 'bg-white text-gray-800 border border-gray-200 rounded-bl-sm'}">
                        <p class="text-sm leading-relaxed"></p>
                        <p class="text-[10px] opacity-75 mt-1 text-right"></p>
                    </div>`;
                wrapper.querySelector('p.text-sm').textContent = isiPesan;
                wrapper.querySelector('p.text-\\[10px\\]').textContent = waktu;
                chatBox.appendChild(wrapper);
                chatBox.scrollTop = chatBox.scrollHeight;
            }

            if (form && input) {
                form.addEventListener('submit', async function (e) {
                    e.preventDefault();
                    const isiPesan = input.value.trim();
                    if (!isiPesan) return;

                    input.value = '';

                    const formData = new FormData();
                    formData.append('isi_pesan', isiPesan);

                    try {
                        const res = await fetch(`/konsultasi/${konsultasiId}/pesan`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Socket-ID': window.Echo ? window.Echo.socketId() : '',
                            },
                            body: formData,
                        });

                        const data = await res.json();
                        if (data.success) {
                            tambahBubble(isiPesan, 'Baru saja', userId);
                        } else if (data.message) {
                            alert(data.message);
                        }
                    } catch (err) {
                        console.error('Error saat mengirim pesan:', err);
                    }
                });
            }

            if (window.Echo) {
                window.Echo.private(`konsultasi.${konsultasiId}`)
                    .listen('.PesanDikirim', (e) => {
                        if (e.pengirim_id === userId) return;
                        tambahBubble(e.isi_pesan, e.waktu, e.pengirim_id);
                    });
            }

            if (chatBox) {
                chatBox.scrollTop = chatBox.scrollHeight;
            }
            @endif
        });
    </script>
    @endpush
</x-app-layout>
