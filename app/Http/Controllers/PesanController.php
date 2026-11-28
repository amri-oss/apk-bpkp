<?php

namespace App\Http\Controllers;

use App\Events\AktivitasKonsultasi;
use App\Events\PesanDikirim;
use App\Models\ActivityLog;
use App\Models\Konsultasi;
use App\Models\Pesan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PesanController extends Controller
{
    public function index(Request $request, Konsultasi $konsultasi)
    {
        $user = Auth::user();

        abort_unless(
            $konsultasi->audiens_id === $user->id || $konsultasi->konsultan_id === $user->id,
            403
        );

        $after = (int) $request->query('after', 0);

        $query = $konsultasi->pesans()
            ->where('id', '>', $after)
            ->where(function ($q) use ($user) {
                $q->where('disembunyikan_oleh_pengirim', false)
                    ->orWhere('pengirim_id', '!=', $user->id);
            })
            ->with(['pengirim', 'balasKe.pengirim'])
            ->orderBy('id');

        if ($user->id === $konsultasi->audiens_id && $konsultasi->dibersihkan_audiens_pada) {
            $query->where('created_at', '>', $konsultasi->dibersihkan_audiens_pada);
        }

        if ($user->id === $konsultasi->konsultan_id && $konsultasi->dibersihkan_konsultan_pada) {
            $query->where('created_at', '>', $konsultasi->dibersihkan_konsultan_pada);
        }

        $pesans = $query->get()->map(fn ($p) => [
            'id' => $p->id,
            'isi_pesan' => $p->isi_pesan,
            'pengirim_id' => $p->pengirim_id,
            'pengirim_nama' => $p->pengirim->name,
            'waktu' => $p->created_at->format('H:i'),
            'file_url' => $p->file_path ? asset('storage/' . $p->file_path) : null,
            'file_nama' => $p->file_nama,
            'file_tipe' => $p->file_tipe,
            'dibaca' => $p->dibaca_at !== null,
            'balas_ke' => $p->balasKe ? [
                'id' => $p->balasKe->id,
                'isi_pesan' => $p->balasKe->isi_pesan,
                'pengirim_nama' => $p->balasKe->pengirim->name,
            ] : null,
        ]);

        return response()->json([
            'pesans' => $pesans,
            'status' => $konsultasi->status,
            // ID pesan milik sendiri terakhir yang sudah dibaca lawan bicara,
            // dipakai frontend untuk memperbarui ceklis dua tanpa reload.
            'dibaca_hingga' => (int) $konsultasi->pesans()
                ->where('pengirim_id', $user->id)
                ->whereNotNull('dibaca_at')
                ->max('id'),
        ]);
    }

    /**
     * Tandai semua pesan lawan bicara pada konsultasi ini sebagai sudah dibaca.
     */
    public function dibaca(Konsultasi $konsultasi)
    {
        $user = Auth::user();

        abort_unless(
            $konsultasi->audiens_id === $user->id || $konsultasi->konsultan_id === $user->id,
            403
        );

        app(KonsultasiController::class)->tandaiDibaca($konsultasi->load('pesans'), $user->id);

        return response()->json(['success' => true]);
    }

    public function store(Request $request, Konsultasi $konsultasi)
    {
        $user = Auth::user();

        abort_unless(
            $konsultasi->audiens_id === $user->id || $konsultasi->konsultan_id === $user->id,
            403
        );

        if ($konsultasi->status === 'selesai') {
            return response()->json([
                'success' => false,
                'message' => 'Konsultasi ini sudah selesai, tidak bisa mengirim pesan.',
            ], 403);
        }

        if ($konsultasi->status === 'menunggu') {
            $pesanTunggu = $user->id === $konsultasi->konsultan_id
                ? 'Harap terima permintaan konsultasi terlebih dahulu sebelum membalas.'
                : 'Permintaan konsultasi Anda masih menunggu konfirmasi dari konsultan.';

            return response()->json([
                'success' => false,
                'message' => $pesanTunggu,
            ], 403);
        }

        $validated = $request->validate([
            'isi_pesan' => 'nullable|string|max:2000',
            'balas_ke_id' => 'nullable|exists:pesans,id',
            'file' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,zip',
        ]);

        if (empty($validated['isi_pesan']) && !$request->hasFile('file')) {
            return response()->json(['success' => false, 'message' => 'Pesan atau file wajib diisi.'], 422);
        }

        if (!empty($validated['balas_ke_id'])) {
            $pesanAsal = Pesan::find($validated['balas_ke_id']);
            abort_if(!$pesanAsal || $pesanAsal->konsultasi_id !== $konsultasi->id, 422);
        }

        $dataPesan = [
            'konsultasi_id' => $konsultasi->id,
            'pengirim_id' => $user->id,
            'isi_pesan' => $validated['isi_pesan'] ?? null,
            'balas_ke_id' => $validated['balas_ke_id'] ?? null,
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('chat_files', 'public');

            $dataPesan['file_path'] = $path;
            $dataPesan['file_nama'] = $file->getClientOriginalName();
            $dataPesan['file_tipe'] = $file->getMimeType();
        }

        $pesan = Pesan::create($dataPesan);
        $pesan->load('balasKe.pengirim');

        $updateData = [];

        if ($user->id === $konsultasi->audiens_id && $konsultasi->disembunyikan_oleh_konsultan) {
            $updateData['disembunyikan_oleh_konsultan'] = false;
        }

        if ($user->id === $konsultasi->konsultan_id && $konsultasi->disembunyikan_oleh_audiens) {
            $updateData['disembunyikan_oleh_audiens'] = false;
        }

        if (!empty($updateData)) {
            $konsultasi->update($updateData);
        }

        ActivityLog::catat($user->id, 'Kirim Pesan', $user->name . ' mengirim pesan di konsultasi #' . $konsultasi->id);

        try {
            broadcast(new PesanDikirim($pesan))->toOthers();
        } catch (\Throwable $e) {
            report($e);
        }

        $penerimaId = $user->id === $konsultasi->audiens_id ? $konsultasi->konsultan_id : $konsultasi->audiens_id;
        try {
            broadcast(new AktivitasKonsultasi($penerimaId, $konsultasi->id, 'pesan'));
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'success' => true,
            'id' => $pesan->id,
            'file_url' => $pesan->file_path ? asset('storage/' . $pesan->file_path) : null,
            'file_nama' => $pesan->file_nama,
            'file_tipe' => $pesan->file_tipe,
            'balas_ke' => $pesan->balasKe ? [
                'id' => $pesan->balasKe->id,
                'isi_pesan' => $pesan->balasKe->isi_pesan,
                'pengirim_nama' => $pesan->balasKe->pengirim->name,
            ] : null,
        ]);
    }

    public function destroy(Konsultasi $konsultasi, Pesan $pesan)
    {
        $user = Auth::user();

        abort_unless($pesan->konsultasi_id === $konsultasi->id, 404);
        abort_unless($pesan->pengirim_id === $user->id, 403);

        $pesan->update(['disembunyikan_oleh_pengirim' => true]);

        return response()->json(['success' => true]);
    }

    public function clear(Konsultasi $konsultasi)
    {
        $user = Auth::user();

        abort_unless(
            $konsultasi->audiens_id === $user->id || $konsultasi->konsultan_id === $user->id,
            403
        );

        if ($user->id === $konsultasi->audiens_id) {
            $konsultasi->update(['dibersihkan_audiens_pada' => now()]);
        } else {
            $konsultasi->update(['dibersihkan_konsultan_pada' => now()]);
        }

        return response()->json(['success' => true]);
    }
}