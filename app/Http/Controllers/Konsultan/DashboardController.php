<?php

namespace App\Http\Controllers\Konsultan;

use App\Http\Controllers\Controller;
use App\Http\Controllers\KonsultasiController;
use App\Models\Konsultasi;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $konsultasis = Auth::user()->konsultasiSebagaiKonsultan()
            ->where('disembunyikan_oleh_konsultan', false)
            ->with(['audiens', 'topik', 'pesans'])
            ->latest()
            ->get();

        return view('konsultan.dashboard', compact('konsultasis'));
    }

    public function obrolan(?Konsultasi $konsultasi = null)
    {
        $user = Auth::user();

        $konsultasis = $user->konsultasiSebagaiKonsultan()
            ->where('disembunyikan_oleh_konsultan', false)
            ->with(['audiens', 'topik', 'pesans'])
            ->get();

        // Pastikan konsultasi yang dibuka memang milik konsultan ini
        if ($konsultasi && $konsultasi->konsultan_id !== $user->id) {
            $konsultasi = null;
        }

        // Urutkan berdasarkan pesan terakhir supaya percakapan yang paling
        // aktif selalu muncul paling atas, termasuk yang sudah selesai.
        $konsultasis = Konsultasi::urutkanBerdasarkanAktivitas($konsultasis, $user->id);

        // Kalau belum pilih obrolan, otomatis tampilkan yang paling baru.
        // Kecuali ?daftar=1 (tombol kembali di mobile) yang hanya ingin daftar.
        $selected = $konsultasi ?? (request()->boolean('daftar') ? null : $konsultasis->first());

        if ($selected) {
            $selected->load([
                'pesans' => function ($query) use ($user, $selected) {
                    $query->where(function ($q) use ($user) {
                        $q->where('disembunyikan_oleh_pengirim', false)
                            ->orWhere('pengirim_id', '!=', $user->id);
                    });

                    if ($selected->dibersihkan_konsultan_pada) {
                        $query->where('created_at', '>', $selected->dibersihkan_konsultan_pada);
                    }

                    $query->orderBy('created_at');
                },
                'pesans.pengirim',
                'pesans.balasKe.pengirim',
                'audiens',
                'konsultan',
                'topik',
            ]);

            // Tandai pesan audiens sudah dibaca konsultan (ceklis dua di sisi audiens)
            app(KonsultasiController::class)->tandaiDibaca($selected, $user->id);

            // Pakai instance yang sama di daftar agar badge belum dibaca langsung hilang
            $konsultasis = $konsultasis
                ->map(fn ($item) => $item->id === $selected->id ? $selected : $item)
                ->values();
        }

        return view('konsultan.obrolan', compact('konsultasis', 'selected'));
    }
}