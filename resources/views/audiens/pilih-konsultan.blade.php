<x-app-layout>

    {{-- Hero (gambar gedung + floating card) --}}
    <div class="relative">
        <div class="relative w-full h-80 sm:h-96 overflow-hidden">
            <img src="{{ asset('images/gedung-bpkp.jpg') }}" alt="Gedung BPKP" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/30 to-transparent flex items-center">
                <div class="max-w-6xl mx-auto px-6 w-full">
                    <h1 class="text-white text-2xl sm:text-3xl font-bold mb-2">
                        Selamat datang, {{ Auth::user()->name }} !
                    </h1>
                    <p class="text-white/90 text-base sm:text-lg">
                        Pilih konsultan dan mulai konsultasi
                    </p>
                </div>
            </div>
        </div>

        {{-- Kartu putih mengambang berisi pencarian, filter, dan daftar konsultan --}}
        <div class="max-w-6xl mx-auto px-6 -mt-16 relative z-10 pb-10">
            <div class="bg-white rounded-2xl shadow-lg p-6 sm:p-8">

                <form action="{{ route('audiens.pilih-konsultan') }}" method="GET" class="flex flex-col sm:flex-row gap-3 mb-6">
                    @if (request('kategori_id'))
                        <input type="hidden" name="kategori_id" value="{{ request('kategori_id') }}">
                    @endif
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama..."
                        class="flex-1 w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="submit"
                        class="bg-[#1E3A8A] hover:bg-[#172554] text-white font-semibold text-sm px-6 py-3 rounded-[20px] shrink-0 transition">
                        Cari
                    </button>
                </form>

                {{-- Banner deskripsi kategori aktif --}}
                @php
                    $kategoriAktif = $kategoris->firstWhere('id', (int) request('kategori_id'));
                @endphp
                @if ($kategoriAktif && $kategoriAktif->keterangan)
                    <div class="mb-6 bg-blue-50 border border-blue-100 text-blue-800 text-sm rounded-lg px-4 py-3">
                        <span class="font-semibold">{{ $kategoriAktif->nama_kategori }}:</span>
                        {{ $kategoriAktif->keterangan }}
                    </div>
                @endif

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-4" x-data="konsulModal()">
                    @forelse ($konsultans as $kp)
                        @php
                            $topikBidang = $topiks->where('kategori_id', $kp->kategori_id);
                            $fotoKonsultan = $kp->foto_profil ? asset('storage/' . $kp->foto_profil) : null;
                            $topikOptions = $topikBidang->map(fn($t) => ['id' => $t->id, 'nama' => $t->nama_topik])->values();
                        @endphp
                        <div class="flex flex-col h-full bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition">
                            <div class="p-3 sm:p-4 flex flex-col items-center text-center flex-1">
                                <div class="w-full max-w-[132px] sm:max-w-[150px] mb-3">
                                    @if ($fotoKonsultan)
                                        <img src="{{ $fotoKonsultan }}" alt="{{ $kp->user->name }}"
                                            style="width:100%;aspect-ratio:1/1;object-fit:contain;object-position:center;"
                                            class="rounded-lg bg-gray-100"
                                            onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.classList.remove('hidden');this.nextElementSibling.classList.add('flex');">
                                        <div style="width:100%;aspect-ratio:1/1;"
                                            class="hidden rounded-lg bg-gray-100 items-center justify-center text-gray-400 font-semibold text-2xl">
                                            {{ strtoupper(substr($kp->user->name, 0, 1)) }}
                                        </div>
                                    @else
                                        <div style="width:100%;aspect-ratio:1/1;"
                                            class="rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 font-semibold text-2xl">
                                            {{ strtoupper(substr($kp->user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <p class="font-semibold text-gray-800 text-sm leading-snug line-clamp-2 min-h-[2.5rem]">{{ $kp->user->name }}</p>
                                <p class="text-xs text-blue-600 font-medium mt-1 line-clamp-2">Bidang {{ $kp->kategori->nama_kategori }}</p>
                            </div>
                            <button type="button"
                                @click="bukaKonsul({
                                    action: @js(route('audiens.konsultasi.store', $kp->user->id)),
                                    nama: @js($kp->user->name),
                                    bidang: @js($kp->kategori->nama_kategori),
                                    foto: @js($fotoKonsultan),
                                    inisial: @js(strtoupper(substr($kp->user->name, 0, 1))),
                                    topiks: @js($topikOptions),
                                })"
                                class="w-full bg-[#1E3A8A] hover:bg-[#172554] active:bg-[#1E3A8A] text-white text-xs sm:text-sm font-semibold py-2.5 rounded-[20px] transition">
                                KONSUL
                            </button>
                        </div>
                    @empty
                        <p class="text-gray-400 col-span-full text-center py-10">Tidak ada konsultan ditemukan.</p>
                    @endforelse

                    {{-- Popup form konsultasi --}}
                    <div x-cloak x-show="open" class="fixed inset-0 z-[80] flex items-end sm:items-center justify-center p-0 sm:p-4" aria-modal="true" role="dialog">
                        <div class="absolute inset-0 bg-black/50" @click="tutup()"></div>
                        <div class="relative bg-white w-full sm:max-w-lg rounded-t-2xl sm:rounded-2xl shadow-xl max-h-[92dvh] overflow-y-auto" @click.stop>
                            <div class="sticky top-0 bg-white px-5 py-4 border-b border-gray-100 flex items-center justify-between rounded-t-2xl z-10">
                                <div class="flex items-center gap-3 min-w-0">
                                    <template x-if="foto">
                                        <img :src="foto" :alt="nama" class="w-10 h-10 rounded-full object-cover bg-gray-100 shrink-0">
                                    </template>
                                    <template x-if="!foto">
                                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm shrink-0" x-text="inisial"></div>
                                    </template>
                                    <div class="min-w-0">
                                        <h3 class="text-base font-bold text-gray-900 truncate" x-text="nama"></h3>
                                        <p class="text-xs text-blue-600 font-medium truncate" x-text="'Bidang ' + bidang"></p>
                                    </div>
                                </div>
                                <button type="button" @click="tutup()" class="shrink-0 inline-flex items-center justify-center w-9 h-9 rounded-full text-gray-500 hover:bg-gray-100 transition" aria-label="Tutup">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <form :action="action" method="POST" class="p-5">
                                @csrf
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Topik konsultasi <span class="text-red-600">*</span></label>
                                        <select name="topik_id" x-model="topikId" required @change="topikLainnya = (topikId === 'lainnya')"
                                            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <option value="">-- Pilih topik --</option>
                                            <template x-for="t in topiks" :key="t.id">
                                                <option :value="t.id" x-text="t.nama"></option>
                                            </template>
                                            <option value="lainnya">Lainnya...</option>
                                        </select>
                                        @error('topik_id')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div x-show="topikId === 'lainnya'" x-cloak>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Topik lainnya <span class="text-red-600">*</span></label>
                                        <input type="text" name="topik_lainnya" x-model="topikLainnyaText" :required="topikId === 'lainnya'" maxlength="255"
                                            placeholder="Tulis topik Anda..."
                                            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        @error('topik_lainnya')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Deskripsi konsultasi <span class="text-red-600">*</span></label>
                                        <textarea name="deskripsi" x-model="deskripsi" rows="4" required maxlength="2000"
                                            placeholder="Jelaskan pertanyaan atau masalah yang ingin dikonsultasikan..."
                                            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5 resize-y min-h-[110px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                                        @error('deskripsi')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="flex gap-2 mt-5">
                                    <button type="button" @click="tutup()" class="flex-1 px-4 py-2.5 rounded-[20px] text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition">Batal</button>
                                    <button type="submit" class="flex-1 px-4 py-2.5 rounded-[20px] text-sm font-semibold text-white bg-[#1E3A8A] hover:bg-[#172554] transition">Kirim Konsultasi</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function konsulModal() {
            return {
                open: false,
                action: '',
                nama: '',
                bidang: '',
                foto: null,
                inisial: '',
                topiks: [],
                topikId: '',
                topikLainnya: false,
                topikLainnyaText: '',
                deskripsi: '',
                bukaKonsul(data) {
                    this.action = data.action;
                    this.nama = data.nama;
                    this.bidang = data.bidang;
                    this.foto = data.foto;
                    this.inisial = data.inisial;
                    this.topiks = data.topiks || [];
                    this.topikId = '';
                    this.topikLainnya = false;
                    this.topikLainnyaText = '';
                    this.deskripsi = '';
                    this.open = true;
                },
                tutup() {
                    this.open = false;
                }
            };
        }
    </script>
    @endpush

</x-app-layout>