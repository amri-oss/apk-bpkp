<?php

namespace App\Http\Controllers;

use App\Events\AktivitasKonsultasi;
use App\Events\PesanDibaca;
use App\Models\Konsultasi;
use App\Models\Pesan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class KonsultasiController extends Controller
{
    public function show(Konsultasi $konsultasi)
    {
        $user = Auth::user();

        abort_unless(
            $konsultasi->audiens_id === $user->id || $konsultasi->konsultan_id === $user->id,
            403
        );

        $batasWaktu = $user->id === $konsultasi->audiens_id
            ? $konsultasi->dibersihkan_audiens_pada
            : $konsultasi->dibersihkan_konsultan_pada;

        $konsultasi->load(['pesans' => function ($query) use ($user, $batasWaktu) {
            $query->where(function ($q) use ($user) {
                $q->where('disembunyikan_oleh_pengirim', false)
                  ->orWhere('pengirim_id', '!=', $user->id);
            });

            if ($batasWaktu) {
                $query->where('created_at', '>', $batasWaktu);
            }

            $query->orderBy('created_at');
        }, 'pesans.pengirim', 'pesans.balasKe.pengirim', 'audiens', 'konsultan']);

        $this->tandaiDibaca($konsultasi, $user->id);

        return view('konsultasi.show', compact('konsultasi'));
    }

    public function destroy(Konsultasi $konsultasi)
    {
        $user = Auth::user();

        $isParticipant = $konsultasi->audiens_id === $user->id || $konsultasi->konsultan_id === $user->id;
        $isAdmin = $user->role === 'admin';

        abort_unless($isParticipant || $isAdmin, 403);

        // Hanya admin yang boleh menghapus data dari database.
        if ($isAdmin) {
            $konsultasi->load('pesans');
            foreach ($konsultasi->pesans as $pesan) {
                if ($pesan->file_path) {
                    Storage::disk('public')->delete($pesan->file_path);
                }
            }
            $konsultasi->pesans()->delete();
            $konsultasi->delete();

            return redirect()->route('admin.pemantauan.index')->with('success', 'Konsultasi berhasil dihapus permanen dari database.');
        }

        if ($konsultasi->audiens_id === $user->id) {
            $konsultasi->update(['disembunyikan_oleh_audiens' => true]);
        } else {
            $konsultasi->update(['disembunyikan_oleh_konsultan' => true]);
        }

        return back()->with('success', 'Konsultasi disembunyikan dari daftar Anda. Data tetap tersimpan di sistem.');
    }

    public function terima(Request $request, Konsultasi $konsultasi)
    {
        $user = Auth::user();

        abort_unless($konsultasi->konsultan_id === $user->id, 403);

        $konsultasi->update(['status' => 'berlangsung']);

        try {
            broadcast(new AktivitasKonsultasi($konsultasi->audiens_id, $konsultasi->id, 'status'))->toOthers();
        } catch (\Throwable $e) {
            report($e);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Permintaan konsultasi telah diterima.']);
        }

        return redirect()->route('konsultan.obrolan', $konsultasi->id)
            ->with('success', 'Permintaan konsultasi telah diterima. Anda dapat memulai obrolan.');
    }

    public function tolak(Request $request, Konsultasi $konsultasi)
    {
        $user = Auth::user();

        abort_unless($konsultasi->konsultan_id === $user->id, 403);

        $validated = $request->validate([
            'alasan_tolak' => 'required|string|max:1000',
        ], [
            'alasan_tolak.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $konsultasi->update([
            'status' => 'selesai',
            'alasan_tolak' => $validated['alasan_tolak'],
            'disembunyikan_oleh_konsultan' => true,
        ]);

        try {
            broadcast(new AktivitasKonsultasi($konsultasi->audiens_id, $konsultasi->id, 'status'))->toOthers();
        } catch (\Throwable $e) {
            report($e);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Permintaan konsultasi telah ditolak.']);
        }

        return redirect()->route('konsultan.dashboard')
            ->with('success', 'Permintaan konsultasi telah ditolak.');
    }

    public function selesaikan(Konsultasi $konsultasi)
    {
        $user = Auth::user();

        abort_unless(
            $konsultasi->audiens_id === $user->id || $konsultasi->konsultan_id === $user->id,
            403
        );

        $konsultasi->update(['status' => 'selesai']);

        $penerimaId = $user->id === $konsultasi->audiens_id
            ? $konsultasi->konsultan_id
            : $konsultasi->audiens_id;
        try {
            broadcast(new AktivitasKonsultasi($penerimaId, $konsultasi->id, 'status'));
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('success', 'Konsultasi ditandai selesai.');
    }

    public function tandaiDibaca(Konsultasi $konsultasi, $userId)
    {
        $belumDibaca = $konsultasi->pesans
            ->where('pengirim_id', '!=', $userId)
            ->whereNull('dibaca_at');

        if ($belumDibaca->isNotEmpty()) {
            Pesan::whereIn('id', $belumDibaca->pluck('id'))->update(['dibaca_at' => now()]);
            try {
                broadcast(new PesanDibaca($konsultasi->id, $userId))->toOthers();
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}