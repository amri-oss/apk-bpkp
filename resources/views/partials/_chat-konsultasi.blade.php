<div class="chat-panel wa-main flex flex-col h-full w-full overflow-hidden" style="display: flex; flex-direction: column; height: 100%; min-height: 0;">
    @php
        $audiensMenungguPersetujuan = auth()->id() === $konsultasi->audiens_id
            && $konsultasi->status === 'menunggu';
        $konsultanBelumKonfirmasi = auth()->id() === $konsultasi->konsultan_id
            && $konsultasi->status === 'menunggu';
        $lawanNama = auth()->user()->id === $konsultasi->audiens_id ? $konsultasi->konsultan->name : $konsultasi->audiens->name;
        $lawanInisial = strtoupper(substr($lawanNama, 0, 1));
        $statusLabel = match($konsultasi->status) {
            'menunggu' => 'Menunggu',
            'berlangsung' => 'Aktif',
            'selesai' => 'Selesai',
            default => ucfirst($konsultasi->status),
        };
        $statusColor = match($konsultasi->status) {
            'berlangsung' => 'text-[#00a884]',
            'menunggu' => 'text-[#b45309]',
            'selesai' => 'text-[#667781]',
            default => 'text-[#667781]',
        };
    @endphp

    <div class="wa-main-header px-4 sm:px-5 py-2.5 sm:py-3 shrink-0">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
                @if ($showBack ?? false)
                    <a href="{{ auth()->user()->role === 'konsultan' ? route('konsultan.obrolan', ['daftar' => 1]) : route('audiens.konsultasi-saya') }}"
                       class="sm:hidden shrink-0 inline-flex items-center justify-center w-9 h-9 -ml-1 rounded-full text-[#54656f] hover:bg-[#e9edef] transition"
                       aria-label="Kembali ke daftar">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                @endif
                <div class="w-10 h-10 shrink-0 rounded-full bg-[#e9edef] text-[#374151] flex items-center justify-center font-semibold">
                    {{ $lawanInisial }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 min-w-0">
                        <h2 class="wa-title font-medium text-[15px] sm:text-base leading-tight truncate">{{ $lawanNama }}</h2>
                        @if ($konsultasi->topik || $konsultasi->topik_lainnya)
                            <span class="hidden sm:inline-flex text-[11px] font-medium bg-white text-[#667781] px-2 py-0.5 rounded-full whitespace-nowrap max-w-[220px] truncate border border-[#e9edef]">
                                {{ $konsultasi->topik->nama_topik ?? $konsultasi->topik_lainnya }}
                            </span>
                        @endif
                    </div>
                    <p class="text-xs {{ $statusColor }} font-medium mt-0.5">{{ $statusLabel }}</p>
                </div>
            </div>

            @if (!in_array($konsultasi->status, ['menunggu'], true))
                <div class="hidden sm:flex items-center gap-1 shrink-0">
                    @if ($konsultasi->status !== 'selesai')
                        <form action="{{ route('konsultasi.selesai', $konsultasi->id) }}" method="POST"
                              onsubmit="return confirm('Tandai konsultasi ini sebagai selesai?')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="wa-action-btn wa-action-btn-success">Tandai Selesai</button>
                        </form>
                    @endif
                    <button type="button" data-hapus-semua class="wa-action-btn wa-action-btn-danger inline-flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Hapus Semua
                    </button>
                </div>
            @endif
        </div>

        @if ($konsultasi->topik || $konsultasi->topik_lainnya)
            <div class="sm:hidden mt-2">
                <span class="inline-flex text-[11px] font-medium bg-white text-[#667781] px-2 py-0.5 rounded-full max-w-full truncate border border-[#e9edef]">
                    {{ $konsultasi->topik->nama_topik ?? $konsultasi->topik_lainnya }}
                </span>
            </div>
        @endif

        @if (!in_array($konsultasi->status, ['menunggu'], true))
            <div class="flex sm:hidden items-center gap-2 mt-2.5 pt-2.5 border-t border-[#e9edef]">
                @if ($konsultasi->status !== 'selesai')
                    <form action="{{ route('konsultasi.selesai', $konsultasi->id) }}" method="POST" class="flex-1"
                          onsubmit="return confirm('Tandai konsultasi ini sebagai selesai?')">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 text-xs font-semibold text-[#008069] bg-[#dcfce7] hover:bg-[#bbf7d0] px-3 py-2 rounded-lg transition">
                            Tandai Selesai
                        </button>
                    </form>
                @endif
                <button type="button" data-hapus-semua
                    class="{{ $konsultasi->status !== 'selesai' ? 'flex-1' : 'w-full' }} inline-flex items-center justify-center gap-1.5 text-xs font-semibold text-[#f87171] bg-[#fee2e2] hover:bg-[#fecaca] px-3 py-2 rounded-lg transition">
                    Hapus Semua
                </button>
            </div>
        @endif
    </div>

    <div class="wa-thread flex-1 overflow-y-auto px-3 sm:px-4 lg:px-6 py-3 flex flex-col gap-2 min-h-0" id="chat-box" style="flex: 1 1 0%; min-height: 0;">

        @if ($konsultasi->topik || $konsultasi->topik_lainnya)
            <div class="flex justify-center mb-1">
                <div class="wa-topic-chip rounded-lg px-3 py-1.5 text-[11px] sm:text-xs font-medium shadow-sm">
                    Topik: {{ $konsultasi->topik->nama_topik ?? $konsultasi->topik_lainnya }}
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
                    <button class="btn-reply shrink-0 w-7 h-7 flex items-center justify-center rounded-full text-[#667781] opacity-0 group-hover:opacity-100 hover:text-[#111b21] hover:bg-[#e9edef] transition text-base">
                        ↩
                    </button>
                @endif

                <div class="pesan-bubble max-w-[88%] sm:max-w-[72%] lg:max-w-[60%] px-3 py-2 shadow-sm select-none {{ $pesan->pengirim_id === auth()->id() ? 'wa-bubble-out' : 'wa-bubble-in' }}"
                    style="-webkit-touch-callout: none; -webkit-user-select: none; touch-action: pan-y;">

                    @if ($pesan->balasKe)
                        <div class="mb-1.5 pl-2 py-1 border-l-2 rounded {{ $pesan->pengirim_id === auth()->id() ? 'border-[#00a884]/40 bg-[#d9fdd3]' : 'border-[#667781]/30 bg-[#f0f2f5]' }} text-xs">
                            <p class="font-semibold {{ $pesan->pengirim_id === auth()->id() ? 'text-[#008069]' : 'text-[#008069]' }}">{{ $pesan->balasKe->pengirim->name }}</p>
                            <p class="truncate text-[#374151]/90">{{ $pesan->balasKe->isi_pesan }}</p>
                        </div>
                    @endif

                    @if ($pesan->file_path)
                        <a href="{{ asset('storage/' . $pesan->file_path) }}" target="_blank"
                            class="flex items-center gap-2 mb-1.5 px-3 py-2 rounded-lg {{ $pesan->pengirim_id === auth()->id() ? 'bg-[#d9fdd3]' : 'bg-[#f0f2f5]' }}">
                            <span>📎</span>
                            <span class="text-xs truncate text-[#111b21]">{{ $pesan->file_nama }}</span>
                        </a>
                    @endif

                    @if ($pesan->isi_pesan)
                        <p class="text-[14.5px] leading-relaxed pesan-isi whitespace-pre-wrap break-words">{{ $pesan->isi_pesan }}</p>
                    @endif
                    <p class="text-[11px] wa-bubble-meta mt-1 text-right">
                        {{ $pesan->created_at->format('H:i') }}
                        @if ($pesan->pengirim_id === auth()->id())
                            <span class="status-baca {{ $pesan->dibaca_at ? 'text-[#53bdeb]' : '' }}">{{ $pesan->dibaca_at ? ' ✓✓' : ' ✓' }}</span>
                        @endif
                    </p>
                </div>
            </div>
        @endforeach
    </div>

    @if ($audiensMenungguPersetujuan)
        <div class="border-t border-[#e9edef] bg-[#f0f2f5] px-5 py-4 text-center shrink-0" style="flex-shrink: 0;">
            <p class="text-sm font-semibold text-[#111b21]">Menunggu persetujuan konsultan</p>
            <p class="mt-1 text-xs text-[#667781]">Deskripsi konsultasi Anda sudah dikirim. Kolom pesan akan tersedia setelah konsultasi diterima.</p>
        </div>
    @elseif ($konsultanBelumKonfirmasi)
        <div class="border-t border-[#e9edef] bg-[#f0f2f5] px-5 py-4 shrink-0" style="flex-shrink: 0;" x-data="{ tolakOpen: false, alasanTolak: '' }">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-[#111b21] flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#b45309]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Konfirmasi Permintaan Konsultasi
                    </p>
                    <p class="mt-0.5 text-xs text-[#667781]">Anda hanya dapat melihat pesan permintaan ini. Silakan konfirmasi untuk mulai merespon dan membalas pesan.</p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" @click="tolakOpen = true; $nextTick(() => $refs.alasanTolak.focus())"
                        class="px-4 py-2 bg-[#fee2e2] hover:bg-[#fecaca] text-[#f87171] text-xs font-semibold rounded-lg transition">
                        Tolak
                    </button>
                    <form action="{{ route('konsultasi.terima', $konsultasi->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                            class="px-4 py-2 bg-[#00a884] hover:bg-[#008069] text-white text-xs font-semibold rounded-lg transition">
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
            <div class="px-4 pt-2.5 pb-2 wa-input-bar shrink-0" style="flex-shrink: 0;">
                <div class="flex items-center justify-center gap-2 py-2 mb-2 bg-white border border-[#e9edef] rounded-xl text-sm text-[#667781]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Konsultasi ini sudah selesai. Anda tidak dapat mengirim pesan lagi.
                </div>
                @if ($konsultasi->alasan_tolak)
                    <div class="mb-2 bg-[#fee2e2] border border-[#fca5a5] text-[#f87171] rounded-xl px-3 py-2 text-sm">
                        <span class="font-semibold">Alasan penolakan:</span> {{ $konsultasi->alasan_tolak }}
                    </div>
                @endif
            </div>
        @endif

        <div class="wa-input-bar px-3 sm:px-4 pt-2.5 pb-2.5 shrink-0" style="flex-shrink: 0;">
            <div id="file-preview" class="hidden mb-2 bg-white border-l-4 border-[#00a884] px-3 py-2 rounded-lg flex justify-between items-center">
                <p class="text-xs text-[#374151] truncate" id="file-preview-nama"></p>
                <button type="button" id="btn-batal-file" class="text-[#667781] hover:text-[#f87171] text-sm ml-2">✕</button>
            </div>

            <div id="reply-preview" class="hidden mb-2 bg-white border-l-4 border-[#00a884] px-3 py-2 rounded-lg flex justify-between items-start">
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-[#00a884]" id="reply-nama"></p>
                    <p class="text-xs text-[#667781] truncate" id="reply-isi"></p>
                </div>
                <button type="button" id="btn-batal-reply" class="text-[#667781] hover:text-[#f87171] text-sm ml-2 shrink-0">✕</button>
            </div>

            <form id="form-pesan" class="flex gap-2 items-center">
                <label for="input-file"
                    class="cursor-pointer w-10 h-10 shrink-0 flex items-center justify-center rounded-full text-[#667781] hover:text-[#111b21] hover:bg-[#e9edef] transition {{ $konsultasi->status === 'selesai' ? 'opacity-40 pointer-events-none' : '' }}"
                    title="Kirim file/foto">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                    </svg>
                </label>
                <input type="file" id="input-file" class="hidden" {{ $konsultasi->status === 'selesai' ? 'disabled' : '' }}>
                <input type="text" id="input-pesan" autocomplete="off"
                    class="wa-input flex-1 rounded-full px-4 py-2.5 text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                    placeholder="Tulis pesan..."
                    {{ $konsultasi->status === 'selesai' ? 'disabled' : '' }}>
                <button type="submit"
                    class="wa-send-btn w-10 h-10 shrink-0 flex items-center justify-center rounded-full transition disabled:opacity-50 disabled:cursor-not-allowed"
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
        const btnHapusSemuaList = document.querySelectorAll('[data-hapus-semua]');
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
            inner.className = 'pesan-bubble max-w-[88%] sm:max-w-[72%] lg:max-w-[60%] px-3 py-2 shadow-sm select-none ' +
                (milikSendiri ? 'wa-bubble-out' : 'wa-bubble-in');
            inner.style.webkitTouchCallout = 'none';
            inner.style.webkitUserSelect = 'none';
            inner.style.touchAction = 'pan-y';

            if (balasKe) {
                const kutipan = document.createElement('div');
                kutipan.className = 'mb-1.5 pl-2 py-1 border-l-2 rounded ' +
                    (milikSendiri ? 'border-[#00a884]/40 bg-[#d9fdd3]' : 'border-[#667781]/30 bg-[#f0f2f5]') + ' text-xs';
                const pNama = document.createElement('p');
                pNama.className = 'font-semibold ' + (milikSendiri ? 'text-[#008069]' : 'text-[#008069]');
                pNama.textContent = balasKe.pengirim_nama;
                const pIsi = document.createElement('p');
                pIsi.className = 'truncate text-[#374151]/90';
                pIsi.textContent = balasKe.isi_pesan || '[File]';
                kutipan.appendChild(pNama);
                kutipan.appendChild(pIsi);
                inner.appendChild(kutipan);
            }

            if (fileUrl) {
                const a = document.createElement('a');
                a.href = fileUrl;
                a.target = '_blank';
                a.className = 'flex items-center gap-2 mb-1.5 px-3 py-2 rounded-lg ' +
                    (milikSendiri ? 'bg-[#d9fdd3]' : 'bg-[#f0f2f5]');
                const span1 = document.createElement('span');
                span1.textContent = '📎';
                const span2 = document.createElement('span');
                span2.className = 'text-xs truncate text-[#111b21]';
                span2.textContent = fileNama || 'File';
                a.appendChild(span1);
                a.appendChild(span2);
                inner.appendChild(a);
            }

            if (isiPesan) {
                const p1 = document.createElement('p');
                p1.className = 'text-[14.5px] leading-relaxed pesan-isi whitespace-pre-wrap break-words';
                p1.textContent = isiPesan;
                inner.appendChild(p1);
            }

            const p2 = document.createElement('p');
            p2.className = 'text-[11px] wa-bubble-meta mt-1 text-right';
            p2.textContent = waktu;
            if (milikSendiri) {
                const statusBaca = document.createElement('span');
                statusBaca.className = 'status-baca';
                statusBaca.textContent = ' ✓';
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

        if (btnHapusSemuaList.length) {
            btnHapusSemuaList.forEach((btn) => {
                btn.addEventListener('click', async () => {
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
                    el.textContent = ' ✓✓';
                    el.classList.add('text-[#53bdeb]');
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

        function gulirKeBawah(force) {
            if (!chatBox) return;
            const dekatBawah = (chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight) < 120;
            if (force || dekatBawah) chatBox.scrollTop = chatBox.scrollHeight;
        }

        gulirKeBawah(true);
        requestAnimationFrame(() => gulirKeBawah(true));
        setTimeout(() => gulirKeBawah(true), 120);
        setTimeout(() => gulirKeBawah(true), 450);
        window.addEventListener('load', () => gulirKeBawah(true));

        // Polling fallback: pesan baru terlihat tanpa refresh (Reverb mungkin tidak aktif)
        let lastId = 0;
        document.querySelectorAll('#chat-box [data-id]').forEach(el => {
            const v = parseInt(el.dataset.id, 10);
            if (!isNaN(v) && v > lastId) lastId = v;
        });

        async function pollPesan() {
            if (document.hidden) return;
            try {
                const res = await fetch(`/konsultasi/${konsultasiId}/pesan?after=${lastId}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) return;
                const data = await res.json();
                if (data.status && data.status !== '{{ $konsultasi->status }}') {
                    window.location.reload();
                    return;
                }
                if (Array.isArray(data.pesans)) {
                    data.pesans.forEach(p => {
                        if (document.getElementById(`pesan-${p.id}`)) {
                            if (p.id > lastId) lastId = p.id;
                            return;
                        }
                        buatBubble(p.id, p.isi_pesan, p.waktu, p.pengirim_id, p.pengirim_nama, p.balas_ke, p.file_url, p.file_nama, p.file_tipe);
                        if (p.id > lastId) lastId = p.id;
                    });
                    if (data.pesans.length) gulirKeBawah(false);
                }
            } catch (e) { /* silent */ }
        }

        setInterval(pollPesan, 4000);
        document.addEventListener('visibilitychange', () => { if (!document.hidden) pollPesan(); });
    });
</script>
@endpush