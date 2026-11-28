<x-app-layout>
    <div class="chat-page">
        <div class="chat-page-container w-full h-full px-0">
            <div class="wa-shell chat-panel flex flex-col lg:flex-row w-full h-full" style="min-height:0; border-radius: 0;">

                @php $tampilDaftar = request()->boolean('daftar'); @endphp
                {{-- Sidebar daftar obrolan --}}
                <div class="wa-sidebar flex flex-col {{ ($selected && !$tampilDaftar) ? 'hidden lg:flex' : 'flex' }} w-full lg:w-[280px] xl:w-[280px] lg:min-w-[280px] xl:min-w-[280px] lg:max-w-[280px] min-h-0">
                    <div class="wa-sidebar-header shrink-0">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <h2 class="text-[#172b45] text-xl font-semibold leading-tight">Obrolan</h2>
                                <p class="text-[#64748b] text-xs mt-0.5">Konsultan</p>
                            </div>
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-3 flex items-center text-[#64748b] pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </span>
                            <input type="text" id="cari-obrolan" placeholder="Cari atau mulai chat baru"
                                   class="wa-search w-full rounded-lg pl-9 pr-3 py-2 text-sm">
                        </div>
                    </div>

                    <div class="flex-1 min-h-0 overflow-y-auto" id="daftar-obrolan">
                        @include('partials._daftar-konsultasi', [
                            'konsultasis' => $konsultasis,
                            'sisi' => 'audiens',
                            'rutaChat' => 'konsultan.obrolan',
                            'aktifId' => $selected->id ?? null,
                        ])
                    </div>
                </div>

                {{-- Panel chat --}}
                <div class="{{ ($selected && !$tampilDaftar) ? 'flex' : 'hidden lg:flex' }} flex-1 min-w-0 min-h-0 flex-col">
                    @if ($selected)
                        @include('partials._chat-konsultasi', ['konsultasi' => $selected, 'showBack' => true])
                    @else
                        <div class="wa-empty flex-1 flex items-center justify-center text-center p-8 sm:p-12">
                            <div>
                                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-white text-[#1E3A8A] flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8-1.13 0-2.21-.18-3.2-.51L3 21l1.55-3.85C3.57 15.9 3 14.02 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                </div>
                                <p class="text-[#172b45] text-lg font-medium mb-1">Obrolan</p>
                                <p class="text-[#64748b] text-sm">Pilih percakapan dari daftar untuk melihat pesan.</p>
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
                    daftar.querySelectorAll('.wa-chat-item').forEach(function (item) {
                        const teks = (item.dataset.cari || item.querySelector('.wa-chat-name')?.textContent || '').toLowerCase();
                        item.style.display = teks.includes(kata) ? '' : 'none';
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
