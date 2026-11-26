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
                        class="bg-green-600 hover:bg-green-700 text-white font-semibold text-sm px-6 py-3 rounded-lg shrink-0">
                        Cari
                    </button>
                </form>

                <div class="flex gap-2 mb-6 overflow-x-auto pb-1 -mx-1 px-1 sm:flex-wrap sm:overflow-visible sm:pb-0 sm:mx-0 sm:px-0">
                    <a href="{{ route('audiens.pilih-konsultan', array_filter(['search' => request('search')])) }}"
                        class="shrink-0 px-4 py-2 rounded-full text-sm font-semibold whitespace-nowrap {{ !request('kategori_id') ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Semua
                    </a>
                    @foreach ($kategoris as $kat)
                        <a href="{{ route('audiens.pilih-konsultan', array_filter(['search' => request('search'), 'kategori_id' => $kat->id])) }}"
                            class="shrink-0 px-4 py-2 rounded-full text-sm font-semibold whitespace-nowrap {{ request('kategori_id') == $kat->id ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-700 hover:bg-blue-200' }}">
                            {{ $kat->nama_kategori }}
                        </a>
                    @endforeach
                </div>

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

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 sm:gap-6">
                    @forelse ($konsultans as $kp)
                        @php
                            // Hanya topik yang sesuai bidang konsultan ini
                            $topikBidang = $topiks->where('kategori_id', $kp->kategori_id);
                        @endphp
                        <div class="flex flex-col h-full">
                            <form action="{{ route('audiens.konsultasi.store', $kp->user->id) }}" method="POST" class="flex flex-col h-full">
                                @csrf

                                <div class="bg-white rounded-t-xl shadow-sm border border-gray-200 p-4 sm:p-5 flex flex-col items-center text-center flex-1">
                                    <div class="w-full max-w-[220px] sm:max-w-none mb-4">
                                        @if ($kp->foto_profil)
                                            <img src="{{ asset('storage/' . $kp->foto_profil) }}"
                                                style="width:100%;aspect-ratio:1/1;object-fit:cover;"
                                                class="rounded-lg"
                                                onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.classList.remove('hidden');this.nextElementSibling.classList.add('flex');">
                                            <div style="width:100%;aspect-ratio:1/1;"
                                                class="hidden rounded-lg bg-gray-100 items-center justify-center text-gray-400 font-semibold text-3xl">
                                                {{ strtoupper(substr($kp->user->name, 0, 1)) }}
                                            </div>
                                        @else
                                            <div style="width:100%;aspect-ratio:1/1;"
                                                class="rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 font-semibold text-3xl">
                                                {{ strtoupper(substr($kp->user->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <p class="font-bold text-gray-800 leading-snug">{{ $kp->user->name }}</p>
                                    <p class="text-sm text-blue-600 font-medium mb-4">Bidang {{ $kp->kategori->nama_kategori }}</p>

                                    <div class="w-full text-left mt-auto">
                                        <label class="block text-xs font-medium text-gray-700 mb-1.5">
                                            Topik konsultasi <span class="text-red-600">(wajib)</span>
                                        </label>
                                        <select name="topik_id" required
                                            onchange="const form=this.closest('form'); const custom=form.querySelector('.topik-lainnya-input'); custom.classList.toggle('hidden', this.value !== 'lainnya'); custom.required=this.value === 'lainnya';"
                                            class="w-full text-sm border border-gray-300 rounded-md px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-blue-400">
                                            <option value="">-- Pilih topik --</option>
                                            @foreach ($topikBidang as $t)
                                                <option value="{{ $t->id }}" @selected(old('topik_id') == $t->id)>{{ $t->nama_topik }}</option>
                                            @endforeach
                                            <option value="lainnya" @selected(old('topik_id') === 'lainnya')>Lainnya...</option>
                                        </select>
                                        <input type="text" name="topik_lainnya" value="{{ old('topik_lainnya') }}" placeholder="Tulis topik Anda..."
                                            @required(old('topik_id') === 'lainnya')
                                            class="topik-lainnya-input {{ old('topik_id') === 'lainnya' ? '' : 'hidden' }} w-full text-sm border border-gray-300 rounded-md px-3 py-2.5 mt-2 focus:outline-none focus:ring-1 focus:ring-blue-400">
                                        @error('topik_id')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                        @error('topik_lainnya')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                        <label class="block text-xs font-medium text-gray-700 mt-3 mb-1.5">
                                            Deskripsi konsultasi <span class="text-red-600">(wajib)</span>
                                        </label>
                                        <textarea name="deskripsi" rows="4" required maxlength="2000"
                                            placeholder="Jelaskan pertanyaan atau masalah yang ingin dikonsultasikan..."
                                            class="w-full text-sm border border-gray-300 rounded-md px-3 py-2.5 resize-y min-h-[96px] focus:outline-none focus:ring-1 focus:ring-blue-400">{{ old('deskripsi') }}</textarea>
                                        @error('deskripsi')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <button type="submit"
                                    class="w-full bg-green-600 hover:bg-green-700 text-white text-sm font-semibold py-3 rounded-b-xl -mt-px">
                                    KONSUL
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="text-gray-400 col-span-full text-center py-10">Tidak ada konsultan ditemukan.</p>
                    @endforelse
                </div>

            </div>
        </div>
    </div>

</x-app-layout>