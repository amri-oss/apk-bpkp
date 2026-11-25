<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Konsultasi Saya</h2>
    </x-slot>

    <div class="chat-page">
        <div class="chat-page-container max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="chat-page-grid grid grid-cols-1 lg:grid-cols-3 gap-5">

                <div class="{{ $konsultasiAktif ? 'hidden lg:flex' : 'flex' }} lg:col-span-1 flex-col min-h-0 overflow-y-auto">
                    <a href="{{ route('audiens.pilih-konsultan') }}"
                        class="flex items-center justify-center gap-2 w-full mb-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Konsultasi Baru
                    </a>

                    @include('partials._daftar-konsultasi', [
                        'konsultasis' => $konsultasis,
                        'sisi' => 'konsultan',
                        'rutePilihKonsultan' => 'audiens.pilih-konsultan',
                        'rutaChat' => 'audiens.konsultasi-saya',
                        'aktifId' => $konsultasiAktif->id ?? null,
                    ])
                </div>

                <div class="{{ $konsultasiAktif ? 'block' : 'hidden lg:block' }} lg:col-span-2 min-h-0">
                    @if ($konsultasiAktif)
                        @include('partials._chat-konsultasi', ['konsultasi' => $konsultasiAktif, 'showBack' => true])
                    @else
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm flex h-full min-h-0 items-center justify-center text-center p-12">
                            <div>
                                <p class="text-gray-500 mb-1">Pilih konsultasi di samping</p>
                                <p class="text-gray-400 text-sm">untuk mulai atau melanjutkan percakapan.</p>
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