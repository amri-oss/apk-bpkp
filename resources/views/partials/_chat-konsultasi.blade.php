<div class="chat-panel bg-white flex flex-col h-full w-full overflow-hidden" style="display: flex; flex-direction: column; height: 100%; min-height: 0;">
    @php
        $audiensMenungguPersetujuan = auth()->id() === $konsultasi->audiens_id
            && $konsultasi->status === 'menunggu';
        $konsultanBelumKonfirmasi = auth()->id() === $konsultasi->konsultan_id
            && $konsultasi->status === 'menunggu';
    @endphp

    <div class="flex justify-between items-center px-5 py-3 border-b border-gray-100">
        <div class="flex items-center gap-3">
            @if ($showBack ?? false)
                <a href="{{ auth()->user()->role === 'konsultan' ? route('konsultan.obrolan') : route('audiens.konsultasi-saya') }}" class="sm:hidden text-sm font-medium text-blue-600 hover:text-blue-800">
                    ← Daftar
                </a>
            @endif
            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-semibold">
                {{ strtoupper(substr(auth()->user()->id === $konsultasi->audiens_id ? $konsultasi->konsultan->name : $konsultasi->audiens->name, 0, 1)) }}
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="font-semibold text-lg text-gray-800 leading-tight">
                        {{ auth()->user()->id === $konsultasi->audiens_id ? $konsultasi->konsultan->name : $konsultasi->audiens->name }}
                    </h2>
                    @if ($konsultasi->topik || $konsultasi->topik_lainnya)
                        <span class="text-[11px] font-medium bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full whitespace-nowrap">
                            {{ $konsultasi->topik->nama_topik ?? $konsultasi->topik_lainnya }}
                        </span>
                    @endif
                </div>
                @if ($konsultasi->status === 'selesai')
                    <span class="text-xs font-medium text-green-600">Selesai</span>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if (!in_array($konsultasi->status, ['menunggu', 'selesai'], true))
                <form action="{{ route('konsultasi.selesai', $konsultasi->id) }}" method="POST"
                      onsubmit="return confirm('Tandai konsultasi ini sebagai selesai?')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="text-xs font-medium text-green-600 hover:bg-green-50 px-3 py-1.5 rounded-lg transition">
                        Tandai Selesai
                    </button>
                </form>
            @endif
            @if ($konsultasi->status !== 'menunggu')
                <button id="btn-hapus-semua"
                    class="flex items-center gap-1.5 text-red-500 text-sm font-medium px-3 py-1.5 rounded-lg hover:bg-red-50 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Hapus Semua
                </button>
            @endif
        </div>
    </div>

    <div class="px-4 py-2 bg-blue-50/60 border-b border-blue-100">
        <p class="text-xs text-blue-600 text-center">
            Geser kanan (atau klik ikon ↩ di laptop) untuk membalas &middot; klik kanan/tekan-tahan pesan sendiri untuk menghapus
        </p>
    </div>

    <div class="flex-1 overflow-y-auto p-4 sm:p-5 flex flex-col gap-3 bg-gray-50 min-h-0" id="chat-box" style="flex: 1 1 0%; min-height: 0;">

        {{-- Kartu topik --}}
        @if ($konsultasi->topik || $konsultasi->topik_lainnya)
            <div class="flex justify-center mb-2">
                <div class="flex items-center gap-1.5 bg-white border border-gray-200 shadow-sm rounded-full px-4 py-1.5">
                    <span class="text-sm">📌</span>
                    <span class="text-xs font-medium text-gray-600">
                        Topik: {{ $konsultasi->topik->nama_topik ?? $konsultasi->topik_lainnya }}
                    </span>
                </div>
            </div>
        @endif

        @foreach ($konsultasi->pesans as $pesan)
            <div id="pesan-{{ $pesan->id }}"
                class="w-full flex items-end gap-2 group {{ $pesan->pengirim_id === auth()->id() ? 'justify-end' : 'justify-start' }}"
                data-id="{{ $pesan->id }}"
                data-isi="{{ $pesan->isi_pesan }}"
                data-nama="{{ $pesan->pengirim->name }}"
                data-milik-sendiri="{{ $pesan->pengirim_id === auth()->id() ? '1' : '0' }}">

                @if (!$audiensMenungguPersetujuan && !$konsultanBelumKonfirmasi && $konsultasi->status !== 'selesai')
                    <button class="btn-reply shrink-0 w-7 h-7 flex items-center justify-center rounded-full text-gray-400 opacity-0 group-hover:opacity-100 hover:text-blue-600 hover:bg-white transition text-base">
                        ↩
                    </button>
                @endif

                <div class="pesan-bubble max-w-xs sm:max-w-sm px-4 py-2.5 shadow-sm select-none {{ $pesan->pengirim_id === auth()->id() ? 'bg-blue-600 text-white rounded-2xl rounded-br-sm' : 'bg-white text-gray-800 border border-gray-200 rounded-2xl rounded-bl-sm' }}"
                    style="-webkit-touch-callout: none; -webkit-user-select: none; touch-action: pan-y;">

                    @if ($pesan->balasKe)
                        <div class="mb-1.5 pl-2 py-1 border-l-2 rounded {{ $pesan->pengirim_id === auth()->id() ? 'border-blue-300 bg-blue-500/40' : 'border-gray-300 bg-gray-50' }} text-xs opacity-90">
                            <p class="font-semibold">{{ $pesan->balasKe->pengirim->name }}</p>
                            <p class="truncate">{{ $pesan->balasKe->isi_pesan }}</p>
                        </div>
                    @endif

                    @if ($pesan->file_path)
                        @if ($pesan->isGambar())
                            <a href="{{ asset('storage/' . $pesan->file_path) }}" target="_blank">
                                <img src="{{ asset('storage/' . $pesan->file_path) }}" class="rounded-lg max-w-full mb-1.5" alt="{{ $pesan->file_nama }}"
                                onerror="window.retryGambar(this)">
                            </a>
                        @else
                            <a href="{{ asset('storage/' . $pesan->file_path) }}" target="_blank"
                                class="flex items-center gap-2 mb-1.5 px-3 py-2 rounded-lg {{ $pesan->pengirim_id === auth()->id() ? 'bg-blue-700/60' : 'bg-gray-100' }}">
                                <span>📎</span>
                                <span class="text-xs truncate">{{ $pesan->file_nama }}</span>
                            </a>
                        @endif
                    @endif

                    @if ($pesan->isi_pesan)
                        <p class="text-sm leading-relaxed pesan-isi">{{ $pesan->isi_pesan }}</p>
                    @endif
                    <p class="text-[10px] opacity-70 mt-1 text-right">
                        {{ $pesan->created_at->format('H:i') }}
                        @if ($pesan->pengirim_id === auth()->id())
                            <span class="status-baca">{{ $pesan->dibaca_at ? ' · Dibaca' : '' }}</span>
                        @endif
                    </p>
                </div>
            </div>
        @endforeach
    </div>

    @if ($audiensMenungguPersetujuan)
        <div class="border-t border-blue-100 bg-blue-50 px-5 py-4 text-center shrink-0" style="flex-shrink: 0;">
            <p class="text-sm font-semibold text-blue-800">Menunggu persetujuan konsultan</p>
            <p class="mt-1 text-xs text-blue-700">Deskripsi konsultasi Anda sudah dikirim. Kolom pesan akan tersedia setelah konsultasi diterima.</p>
        </div>
    @elseif ($konsultanBelumKonfirmasi)
        <div class="border-t border-amber-200 bg-amber-50 px-5 py-4 shrink-0" style="flex-shrink: 0;" x-data="{ tolakOpen: false, alasanTolak: '' }">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-amber-900 flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Konfirmasi Permintaan Konsultasi
                    </p>
                    <p class="mt-0.5 text-xs text-amber-700">Anda hanya dapat melihat pesan permintaan ini. Silakan konfirmasi untuk mulai merespon dan membalas pesan.</p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" @click="tolakOpen = true; $nextTick(() => $refs.alasanTolak.focus())"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                        Tolak
                    </button>
                    <form action="{{ route('konsultasi.terima', $konsultasi->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                            Terima Permintaan
                        </button>
                    </form>
                </div>
            </div>

            <div x-cloak x-show="tolakOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" aria-modal="true" role="dialog">
                <div class="absolute inset-0 bg-black/50" @click="tolakOpen = false"></div>
                <div class="relative bg-white w-full max-w-md rounded-2xl shadow-xl p-6" @click.stop>
                    <h3 class="text-lg font-bold text-gray-900">Tolak Permintaan Konsultasi</h3>
                    <p class="text-sm text-gray-600 mt-1">Anda akan menolak permintaan dari <span class="font-semibold">{{ $konsultasi->audiens->name }}</span>.</p>
                    <form action="{{ route('konsultasi.tolak', $konsultasi->id) }}" method="POST" class="mt-4">
                        @csrf
                        @method('PATCH')
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alasan penolakan <span class="text-red-500">*</span></label>
                        <textarea name="alasan_tolak" x-ref="alasanTolak" x-model="alasanTolak" required rows="4" maxlength="1000"
                            class="w-full rounded-xl border-gray-300 focus:border-red-500 focus:ring-red-500 text-sm"
                            placeholder="Tuliskan alasan penolakan agar audiens memahami keputusan Anda..."></textarea>
                        <div class="flex justify-end gap-2 mt-4">
                            <button type="button" @click="tolakOpen = false" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200">Batal</button>
                            <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-red-600 hover:bg-red-700">Tolak Konsultasi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @else
        @if ($konsultasi->status === 'selesai')
            <div class="px-4 pt-2.5 pb-2 bg-white border-t border-gray-100 shrink-0" style="flex-shrink: 0;">
                <div class="flex items-center justify-center gap-2 py-2 mb-2 bg-gray-100 rounded-xl text-sm text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Konsultasi ini sudah selesai. Anda tidak dapat mengirim pesan lagi.
                </div>
                @if ($konsultasi->alasan_tolak)
                    <div class="mb-2 bg-red-50 border border-red-200 text-red-800 rounded-xl px-3 py-2 text-sm">
                        <span class="font-semibold">Alasan penolakan:</span> {{ $konsultasi->alasan_tolak }}
                    </div>
                @endif
            </div>
        @endif

        <div class="px-4 pt-2.5 pb-2.5 bg-white border-t border-gray-200 shrink-0" style="flex-shrink: 0;">
            <div id="file-preview" class="hidden mb-2 bg-green-50 border-l-4 border-green-500 px-3 py-2 rounded-lg flex justify-between items-center">
                <p class="text-xs text-gray-600 truncate" id="file-preview-nama"></p>
                <button type="button" id="btn-batal-file" class="text-gray-400 hover:text-red-500 text-sm ml-2">✕</button>
            </div>

            <div id="reply-preview" class="hidden mb-2 bg-blue-50 border-l-4 border-blue-500 px-3 py-2 rounded-lg flex justify-between items-start">
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-blue-600" id="reply-nama"></p>
                    <p class="text-xs text-gray-500 truncate" id="reply-isi"></p>
                </div>
                <button type="button" id="btn-batal-reply" class="text-gray-400 hover:text-red-500 text-sm ml-2 shrink-0">✕</button>
            </div>

            <form id="form-pesan" class="flex gap-2 items-center">
                <label for="input-file"
                    class="cursor-pointer w-10 h-10 shrink-0 flex items-center justify-center rounded-full text-gray-500 hover:text-blue-600 hover:bg-gray-100 transition text-lg {{ $konsultasi->status === 'selesai' ? 'opacity-40 pointer-events-none' : '' }}"
                    title="Kirim file/foto">
                    📎
                </label>
                <input type="file" id="input-file" class="hidden" {{ $konsultasi->status === 'selesai' ? 'disabled' : '' }}>
                <input type="text" id="input-pesan" autocomplete="off"
                    class="flex-1 bg-gray-100 border-none rounded-full px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    placeholder="Tulis pesan..."
                    {{ $konsultasi->status === 'selesai' ? 'disabled' : '' }}>
                <button type="submit"
                    class="w-10 h-10 shrink-0 flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white rounded-full transition disabled:opacity-50 disabled:cursor-not-allowed"
                    {{ $konsultasi->status === 'selesai' ? 'disabled' : '' }}>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5m0 0l-6 6m6-6l6 6" />
                    </svg>
                </button>
            </form>
        </div>
    @endif

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const konsultasiId = {{ $konsultasi->id }};
        const statusSelesai = {{ $konsultasi->status === 'selesai' ? 'true' : 'false' }};

        window.retryGambar = function (img) {
            const percobaan = parseInt(img.dataset.percobaan || '0', 10);
            if (percobaan >= 5) return;

            img.dataset.percobaan = percobaan + 1;
            setTimeout(() => {
                const urlAsli = img.src.split('?')[0];
                img.src = urlAsli + '?retry=' + Date.now();
            }, 1000);
        };
        const userId = {{ auth()->id() }};
        const namaSaya = '{{ auth()->user()->name }}';
        const chatBox = document.getElementById('chat-box');
        const form = document.getElementById('form-pesan');
        const input = document.getElementById('input-pesan');
        const inputFile = document.getElementById('input-file');
        const filePreview = document.getElementById('file-preview');
        const filePreviewNama = document.getElementById('file-preview-nama');
        const btnBatalFile = document.getElementById('btn-batal-file');
        const btnHapusSemua = document.getElementById('btn-hapus-semua');
        if (!form) {
            if (window.Echo) {
                window.Echo.private(`konsultasi.${konsultasiId}`)
                    .listen('.PesanDikirim', (e) => {
                        if (e.pengirim_id !== userId) window.location.reload();
                    });
                window.Echo.private(`user.${userId}`)
                    .listen('.AktivitasKonsultasi', (e) => {
                        if (e && e.tipe === 'pesan' && Number(e.konsultasi_id) === Number(konsultasiId)) {
                            return;
                        }
                        window.location.reload();
                    });
            }
            return;
        }
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        const replyPreview = document.getElementById('reply-preview');
        const replyNama = document.getElementById('reply-nama');
        const replyIsi = document.getElementById('reply-isi');
        const btnBatalReply = document.getElementById('btn-batal-reply');

        let replyTarget = null;

        function setReply(id, isi, nama) {
            replyTarget = { id, isi, nama };
            replyNama.textContent = 'Membalas ' + nama;
            replyIsi.textContent = isi || '[File]';
            replyPreview.classList.remove('hidden');
            input.focus();
        }

        function batalReply() {
            replyTarget = null;
            replyPreview.classList.add('hidden');
        }

        btnBatalReply.addEventListener('click', batalReply);

        inputFile.addEventListener('change', function () {
            if (inputFile.files.length > 0) {
                filePreviewNama.textContent = '📎 ' + inputFile.files[0].name;
                filePreview.classList.remove('hidden');
            } else {
                filePreview.classList.add('hidden');
            }
        });

        btnBatalFile.addEventListener('click', function () {
            inputFile.value = '';
            filePreview.classList.add('hidden');
        });

        function isGambarFile(tipe, nama) {
            if (tipe && tipe.startsWith('image/')) return true;
            if (nama) {
                const ext = nama.split('.').pop().toLowerCase();
                return ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);
            }
            return false;
        }

        function buatBubble(id, isiPesan, waktu, pengirimId, namaPengirim, balasKe, fileUrl, fileNama, fileTipe) {
            const milikSendiri = pengirimId === userId;

            const wrapper = document.createElement('div');
            wrapper.id = `pesan-${id}`;
            wrapper.className = 'w-full flex items-end gap-2 group ' +
                (milikSendiri ? 'justify-end' : 'justify-start');
            wrapper.dataset.id = id;
            wrapper.dataset.isi = isiPesan || '';
            wrapper.dataset.nama = namaPengirim;
            wrapper.dataset.milikSendiri = milikSendiri ? '1' : '0';

            const btnReply = document.createElement('button');
            btnReply.className = 'btn-reply shrink-0 w-7 h-7 flex items-center justify-center rounded-full text-gray-400 opacity-0 group-hover:opacity-100 hover:text-blue-600 hover:bg-white transition text-base';
            btnReply.textContent = '↩';
            btnReply.addEventListener('click', () => setReply(id, isiPesan, namaPengirim));
            wrapper.appendChild(btnReply);

            const inner = document.createElement('div');
            inner.className = 'pesan-bubble max-w-xs sm:max-w-sm px-4 py-2.5 shadow-sm select-none ' +
                (milikSendiri ? 'bg-blue-600 text-white rounded-2xl rounded-br-sm' : 'bg-white text-gray-800 border border-gray-200 rounded-2xl rounded-bl-sm');
            inner.style.webkitTouchCallout = 'none';
            inner.style.webkitUserSelect = 'none';
            inner.style.touchAction = 'pan-y';

            if (balasKe) {
                const kutipan = document.createElement('div');
                kutipan.className = 'mb-1.5 pl-2 py-1 border-l-2 rounded ' +
                    (milikSendiri ? 'border-blue-300 bg-blue-500/40' : 'border-gray-300 bg-gray-50') + ' text-xs opacity-90';
                const pNama = document.createElement('p');
                pNama.className = 'font-semibold';
                pNama.textContent = balasKe.pengirim_nama;
                const pIsi = document.createElement('p');
                pIsi.className = 'truncate';
                pIsi.textContent = balasKe.isi_pesan || '[File]';
                kutipan.appendChild(pNama);
                kutipan.appendChild(pIsi);
                inner.appendChild(kutipan);
            }

            if (fileUrl) {
                if (isGambarFile(fileTipe, fileNama)) {
                    const a = document.createElement('a');
                    a.href = fileUrl;
                    a.target = '_blank';
                    const img = document.createElement('img');
                    img.src = fileUrl;
                    img.className = 'rounded-lg max-w-full mb-1.5';
                    img.alt = fileNama || '';
                    img.onerror = function () { window.retryGambar(img); };
                    a.appendChild(img);
                    inner.appendChild(a);
                } else {
                    const a = document.createElement('a');
                    a.href = fileUrl;
                    a.target = '_blank';
                    a.className = 'flex items-center gap-2 mb-1.5 px-3 py-2 rounded-lg ' +
                        (milikSendiri ? 'bg-blue-700/60' : 'bg-gray-100');
                    const span1 = document.createElement('span');
                    span1.textContent = '📎';
                    const span2 = document.createElement('span');
                    span2.className = 'text-xs truncate';
                    span2.textContent = fileNama || 'File';
                    a.appendChild(span1);
                    a.appendChild(span2);
                    inner.appendChild(a);
                }
            }

            if (isiPesan) {
                const p1 = document.createElement('p');
                p1.className = 'text-sm leading-relaxed pesan-isi';
                p1.textContent = isiPesan;
                inner.appendChild(p1);
            }

            const p2 = document.createElement('p');
            p2.className = 'text-[10px] opacity-70 mt-1 text-right';
            p2.textContent = waktu;
            if (milikSendiri) {
                const statusBaca = document.createElement('span');
                statusBaca.className = 'status-baca';
                p2.appendChild(statusBaca);
            }
            inner.appendChild(p2);

            wrapper.appendChild(inner);

            if (milikSendiri) {
                pasangEventHapus(wrapper);
            } else {
                pasangEventKlikKananReply(wrapper);
            }
            pasangEventSwipeReply(wrapper);

            chatBox.appendChild(wrapper);
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        async function hapusPesan(id) {
            if (!confirm('Hapus pesan ini?')) return;

            await fetch(`/konsultasi/${konsultasiId}/pesan/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Socket-ID': window.Echo.socketId(),
                },
            });

            const el = document.getElementById(`pesan-${id}`);
            if (el) el.remove();
        }

        function pasangEventHapus(el) {
            let sudahDiproses = false;
            let timerTekan;

            function prosesHapus() {
                if (sudahDiproses) return;
                sudahDiproses = true;
                hapusPesan(el.dataset.id);
                setTimeout(() => { sudahDiproses = false; }, 1500);
            }

            el.addEventListener('contextmenu', function (e) {
                e.preventDefault();
                e.stopPropagation();
                prosesHapus();
            });

            el.addEventListener('touchstart', function () {
                timerTekan = setTimeout(() => {
                    prosesHapus();
                }, 600);
            }, { passive: true });

            el.addEventListener('touchend', function () {
                clearTimeout(timerTekan);
            });

            el.addEventListener('touchmove', function () {
                clearTimeout(timerTekan);
            });
        }

        function pasangEventKlikKananReply(el) {
            el.addEventListener('contextmenu', function (e) {
                e.preventDefault();
                e.stopPropagation();
                setReply(el.dataset.id, el.dataset.isi, el.dataset.nama);
            });
        }

        function pasangEventSwipeReply(el) {
            const bubble = el.querySelector('.pesan-bubble');
            let startX = 0;
            let startY = 0;
            let geser = false;

            el.addEventListener('touchstart', function (e) {
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
                geser = false;
            }, { passive: true });

            el.addEventListener('touchmove', function (e) {
                const deltaX = e.touches[0].clientX - startX;
                const deltaY = e.touches[0].clientY - startY;

                if (Math.abs(deltaX) > Math.abs(deltaY) && deltaX > 10) {
                    geser = true;
                    const geseran = Math.min(deltaX, 60);
                    bubble.style.transform = `translateX(${geseran}px)`;
                }
            }, { passive: true });

            el.addEventListener('touchend', function () {
                bubble.style.transform = '';
                if (geser) {
                    setReply(el.dataset.id, el.dataset.isi, el.dataset.nama);
                }
                geser = false;
            });
        }

        document.querySelectorAll('[data-id]').forEach(function (el) {
            if (el.dataset.milikSendiri === '1') {
                pasangEventHapus(el);
            } else {
                pasangEventKlikKananReply(el);
            }
            pasangEventSwipeReply(el);

            const btnReply = el.querySelector('.btn-reply');
            if (btnReply) {
                btnReply.addEventListener('click', () => setReply(el.dataset.id, el.dataset.isi, el.dataset.nama));
            }
        });

        if (btnHapusSemua) {
            btnHapusSemua.addEventListener('click', async () => {
                if (!confirm('Hapus SEMUA chat di percakapan ini (hanya untuk kamu)? Tindakan ini tidak bisa dibatalkan.')) return;

                await fetch(`/konsultasi/${konsultasiId}/pesan`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Socket-ID': window.Echo.socketId(),
                    },
                });

                chatBox.innerHTML = '';
            });
        }

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            if (statusSelesai) return;

            const isiPesan = input.value.trim();
            const file = inputFile.files[0] || null;

            if (!isiPesan && !file) return;

            const balasKeIdSaatIni = replyTarget ? replyTarget.id : null;
            const balasKeSaatIni = replyTarget ? { isi_pesan: replyTarget.isi, pengirim_nama: replyTarget.nama } : null;

            input.value = '';
            inputFile.value = '';
            filePreview.classList.add('hidden');
            batalReply();

            // Optimistic UI: tampilkan pesan langsung sebelum server merespons
            const tempId = 'temp-' + Date.now();
            const tempFileUrl = file ? URL.createObjectURL(file) : null;
            const tempFileNama = file ? file.name : null;
            const tempFileTipe = file ? file.type : null;
            buatBubble(tempId, isiPesan, 'Baru saja', userId, namaSaya, balasKeSaatIni, tempFileUrl, tempFileNama, tempFileTipe);

            const formData = new FormData();
            if (isiPesan) formData.append('isi_pesan', isiPesan);
            if (balasKeIdSaatIni) formData.append('balas_ke_id', balasKeIdSaatIni);
            if (file) formData.append('file', file);

            try {
                const res = await fetch(`/konsultasi/${konsultasiId}/pesan`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Socket-ID': window.Echo.socketId(),
                    },
                    body: formData,
                });
                const data = await res.json();
                if (data && data.success) {
                    // Ganti bubble sementara dengan data dari server
                    const tempEl = document.getElementById(`pesan-${tempId}`);
                    if (tempEl) tempEl.remove();
                    buatBubble(data.id, isiPesan, 'Baru saja', userId, namaSaya, data.balas_ke || balasKeSaatIni, data.file_url, data.file_nama, data.file_tipe);
                }
            } catch (err) {
                console.error('Gagal mengirim:', err);
                // Biarkan bubble sementara tetap ada
            }
        });

        if (window.Echo) {
            window.Echo.private(`konsultasi.${konsultasiId}`)
                .listen('.PesanDikirim', (e) => {
                    if (e.pengirim_id === userId) return;
                    buatBubble(e.id, e.isi_pesan, e.waktu, e.pengirim_id, e.pengirim_nama, e.balas_ke, e.file_url, e.file_nama, e.file_tipe);
                })
                .listen('.PesanDibaca', (e) => {
                    if (e.pembaca_id === userId) return;
                    document.querySelectorAll('[data-milik-sendiri="1"] .status-baca').forEach(el => {
                    el.textContent = ' · Dibaca';
                    });
                });

            window.Echo.private(`user.${userId}`)
                .listen('.AktivitasKonsultasi', (e) => {
                    if (e && e.tipe === 'pesan' && Number(e.konsultasi_id) === Number(konsultasiId)) {
                        return;
                    }
                    window.location.reload();
                });
        }

        chatBox.scrollTop = chatBox.scrollHeight;
    });
</script>
@endpush