<?php

namespace App\Http\Controllers\Audiens;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Konsultasi;
use App\Models\KonsultanProfil;
use App\Models\Pesan;
use App\Models\Topik;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KonsultasiController extends Controller
{
    public function store(Request $request, User $konsultan)
    {
        abort_if($konsultan->role !== 'konsultan', 404);

        $profil = KonsultanProfil::where('user_id', $konsultan->id)
            ->where('aktif', true)
            ->firstOrFail();

        $request->validate([
            'topik_id' => [
                'required',
                function ($attribute, $value, $fail) use ($profil) {
                    if ($value === 'lainnya') {
                        return;
                    }

                    $isTopicForConsultant = is_numeric($value)
                        && Topik::whereKey($value)
                            ->where('kategori_id', $profil->kategori_id)
                            ->exists();

                    if (!$isTopicForConsultant) {
                        $fail('Pilih topik konsultasi yang tersedia.');
                    }
                },
            ],
            'topik_lainnya' => 'required_if:topik_id,lainnya|nullable|string|max:255',
            'deskripsi' => 'required|string|max:2000',
        ], [
            'topik_id.required' => 'Topik konsultasi wajib dipilih.',
            'topik_lainnya.required_if' => 'Tuliskan topik konsultasi Anda.',
            'deskripsi.required' => 'Deskripsi konsultasi wajib diisi.',
        ]);

        $topikId = $request->topik_id === 'lainnya' ? null : (int) $request->topik_id;
        $topikLainnya = $request->topik_id === 'lainnya'
            ? trim((string) $request->topik_lainnya)
            : null;

        $konsultasi = DB::transaction(function () use ($request, $konsultan, $topikId, $topikLainnya) {
            $konsultasi = Konsultasi::create([
                'audiens_id'    => Auth::id(),
                'konsultan_id'  => $konsultan->id,
                'status'        => 'menunggu',
                'topik_id'      => $topikId,
                'topik_lainnya' => $topikLainnya,
            ]);

            Pesan::create([
                'konsultasi_id' => $konsultasi->id,
                'pengirim_id' => Auth::id(),
                'isi_pesan' => trim($request->deskripsi),
            ]);

            return $konsultasi;
        });

        ActivityLog::catat(
            Auth::id(),
            'Mulai Konsultasi',
            Auth::user()->name . ' memulai konsultasi dengan ' . $konsultan->name
        );

        return redirect()->route('audiens.konsultasi-saya', $konsultasi->id);
    }
}