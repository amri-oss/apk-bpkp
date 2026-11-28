<?php

namespace App\Http\Controllers\Audiens;

use App\Http\Controllers\Controller;
use App\Http\Controllers\KonsultasiController;
use App\Models\KategoriKonsultan;
use App\Models\Konsultasi;
use App\Models\KonsultanProfil;
use App\Models\Topik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        return view('audiens.dashboard');
    }

    public function pilihKonsultan(Request $request)
    {
        $konsultans = KonsultanProfil::with('user', 'kategori')
            ->where('aktif', true)
            ->when($request->filled('kategori_id'), function ($query) use ($request) {
                $query->where('kategori_id', $request->kategori_id);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%');
                });
            })
            ->get();

        $kategoris = KategoriKonsultan::orderBy('nama_kategori')->get();
        $topiks = Topik::orderBy('nama_topik')->get();

        return view('audiens.pilih-konsultan', compact('konsultans', 'kategoris', 'topiks'));
    }

    public function konsultasiSaya(?Konsultasi $konsultasi = null)
    {
        $user = Auth::user();

        $konsultasis = $user->konsultasiSebagaiAudiens()
            ->where('disembunyikan_oleh_audiens', false)
            ->with(['konsultan', 'topik', 'pesans'])
            ->get();

        // Urutkan berdasarkan pesan terakhir supaya percakapan yang paling
        // aktif selalu muncul paling atas, termasuk yang sudah selesai.
        $konsultasis = Konsultasi::urutkanBerdasarkanAktivitas($konsultasis, $user->id);

        $konsultasiAktif = null;

        // Kalau belum pilih obrolan, otomatis tampilkan yang paling baru.
        // Kecuali ?daftar=1 (tombol kembali di mobile) yang hanya ingin daftar.
        $dipilih = $konsultasi ?? (request()->boolean('daftar') ? null : $konsultasis->first());

        if ($dipilih) {
            abort_unless($dipilih->audiens_id === $user->id, 403);

            $dipilih->load([
                'pesans' => function ($query) use ($user, $dipilih) {
                    $query->where(function ($q) use ($user) {
                        $q->where('disembunyikan_oleh_pengirim', false)
                            ->orWhere('pengirim_id', '!=', $user->id);
                    });

                    if ($dipilih->dibersihkan_audiens_pada) {
                        $query->where('created_at', '>', $dipilih->dibersihkan_audiens_pada);
                    }

                    $query->orderBy('created_at');
                },
                'pesans.pengirim',
                'pesans.balasKe.pengirim',
                'audiens',
                'konsultan',
                'topik',
            ]);

            app(KonsultasiController::class)->tandaiDibaca($dipilih, $user->id);

            $konsultasiAktif = $dipilih;

            // Pakai instance yang sama di daftar agar badge belum dibaca langsung hilang
            $konsultasis = $konsultasis
                ->map(fn ($item) => $item->id === $dipilih->id ? $dipilih : $item)
                ->values();
        }

        return view('audiens.konsultasi-saya', compact('konsultasis', 'konsultasiAktif'));
    }
}