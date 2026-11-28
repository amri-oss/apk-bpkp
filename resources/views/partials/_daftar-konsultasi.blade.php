{{-- 
    Partial daftar konsultasi.
    Dipakai bareng oleh:
    - resources/views/audiens/konsultasi-saya.blade.php
    - resources/views/konsultan/obrolan.blade.php

    Variabel:
    - $konsultasis
    - $sisi
    - $rutePilihKonsultan (opsional)
    - $rutaChat (opsional, default 'konsultasi.show')
    - $aktifId (opsional, id konsultasi yang sedang dibuka)
--}}

<div class="wa-chat-list h-full">

    @forelse ($konsultasis as $k)

        @php
            $lawanBicara = $k->{$sisi};
            $pesanTerakhir = $k->pesanTerakhirUntuk(auth()->id());
            $belumDibaca = $k->jumlahBelumDibacaUntuk(auth()->id());
            $waktuTerakhir = $pesanTerakhir?->created_at ?? $k->created_at;
            $sedangDibuka = isset($aktifId) && (int) $aktifId === $k->id;
            $statusLabel = ucfirst($k->status ?? 'menunggu');
            $statusColor = match($k->status) {
                'berlangsung' => 'text-[#00a884]',
                'menunggu' => 'text-[#b45309]',
                'selesai' => 'text-[#667781]',
                default => 'text-[#667781]',
            };
        @endphp

        <div class="group relative wa-chat-item {{ $sedangDibuka ? 'is-active' : '' }}"
             data-cari="{{ strtolower(($lawanBicara->name ?? '') . ' ' . $k->label_konsultasi) }}">
            <a href="{{ route($rutaChat ?? 'konsultasi.show', $k->id) }}"
                class="flex items-center gap-1.5 min-w-0" style="padding: 4px 8px 4px 8px;">

                <div class="relative shrink-0">
                    <div class="w-8 h-8 rounded-full bg-[#e9edef] text-[#374151] flex items-center justify-center font-semibold text-[10px] shrink-0">
                        {{ strtoupper(substr($lawanBicara->name ?? '?', 0, 1)) }}
                    </div>
                    @if ($belumDibaca > 0)
                        <span class="wa-unread-badge absolute -top-0.5 -right-0.5 min-w-[16px] h-[16px] px-1 rounded-full text-[9px] font-bold flex items-center justify-center text-white">
                            {{ $belumDibaca > 9 ? '9+' : $belumDibaca }}
                        </span>
                    @endif
                </div>

                <div class="min-w-0 flex-1 mr-8">
                    <div class="flex items-center justify-between gap-2">
                        <p class="wa-chat-name font-medium text-[12px] leading-tight truncate">{{ $lawanBicara->name ?? 'Pengguna' }}</p>
                        @if ($waktuTerakhir)
                            <span class="wa-chat-meta text-[9px] leading-tight shrink-0 {{ $belumDibaca > 0 ? 'text-[#00a884]' : '' }}">
                                {{ $waktuTerakhir->isToday() ? $waktuTerakhir->format('H:i') : $waktuTerakhir->format('d/m') }}
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2 mt-0.5 min-w-0">
                        <p class="wa-chat-preview text-[10px] leading-tight truncate flex-1">
                            @if ($pesanTerakhir)
                                {{ $pesanTerakhir->isi_pesan ?: '📎 File' }}
                            @else
                                <span class="italic">Belum ada pesan</span>
                            @endif
                        </p>
                    </div>

                    <div class="flex items-center justify-between gap-2 mt-0.5 min-w-0">
                        <p class="text-[9px] leading-tight font-medium shrink-0 {{ $statusColor }}">{{ $statusLabel }}</p>
                        <p class="wa-chat-id text-[9px] leading-tight text-[#667781] truncate"
                           title="ID Konsul: {{ $k->label_konsultasi }}">
                            ID: {{ $k->label_konsultasi }}
                        </p>
                    </div>
                </div>
            </a>

            <form
                action="{{ route('konsultasi.destroy', $k->id) }}"
                method="POST"
                onsubmit="return confirm('Sembunyikan konsultasi dengan {{ $lawanBicara->name ?? 'pengguna' }} dari daftar Anda? Data chat tetap tersimpan di sistem.')"
                onclick="event.stopPropagation();"
                class="absolute right-2 top-1/2 -translate-y-1/2">
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    class="w-8 h-8 flex items-center justify-center rounded-full text-[#667781] hover:text-[#f87171] hover:bg-[#e9edef] sm:opacity-0 sm:group-hover:opacity-100 focus:opacity-100 transition"
                    title="Sembunyikan konsultasi">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </form>
        </div>
    @empty

        <div class="h-full flex items-center justify-center p-8 text-center wa-chat-list">
            <div>
                <div class="w-14 h-14 mx-auto rounded-full bg-[#f0f2f5] text-[#00a884] flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8-1.13 0-2.21-.18-3.2-.51L3 21l1.55-3.85C3.57 15.9 3 14.02 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
                <p class="text-[#111b21] font-medium mb-1">Belum ada konsultasi</p>
                @if (isset($rutePilihKonsultan))
                    <p class="text-[#667781] text-sm mb-4">Mulai percakapan dengan konsultan untuk keperluanmu.</p>
                    <a href="{{ route($rutePilihKonsultan) }}"
                        class="inline-block bg-[#00a884] hover:bg-[#008069] text-white text-sm font-semibold px-5 py-2 rounded-lg transition">
                        Mulai konsultasi baru
                    </a>
                @else
                    <p class="text-[#667781] text-sm">Belum ada audiens yang mengajukan konsultasi.</p>
                @endif
            </div>
        </div>

    @endforelse

</div>