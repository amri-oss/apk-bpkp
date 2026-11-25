<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriKonsultan;
use App\Models\KonsultanProfil;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class KonsultanProfilController extends Controller
{
    public function index(Request $request)
    {
        $profils = KonsultanProfil::with('user', 'kategori')
            ->when($request->filled('search'), function ($query) use ($request) {
                // Buang awalan "bidang" supaya ketik "Bidang APD 2" tetap ketemu
                $kata = trim(preg_replace('/^bidang\s+/i', '', trim($request->search)));

                $query->where(function ($sub) use ($kata) {
                    $sub->whereHas('user', function ($u) use ($kata) {
                            $u->where('name', 'like', "%{$kata}%")
                              ->orWhere('email', 'like', "%{$kata}%");
                        })
                        ->orWhereHas('kategori', function ($k) use ($kata) {
                            $k->where('nama_kategori', 'like', "%{$kata}%");
                        });
                });
            })
            ->latest()
            ->get();

        return view('admin.konsultan-profil.index', compact('profils'));
    }

    public function create()
    {
        $kategoris = KategoriKonsultan::all();

        return view('admin.konsultan-profil.create', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|max:255|unique:users,email',
            'password'    => 'required|string|min:8',
            'kategori_id' => 'required|exists:kategori_konsultans,id',
            'jabatan'     => 'required|string|max:255',
            'bio'         => 'nullable|string',
            'foto_profil' => 'nullable|image|max:2048',
        ]);

        DB::transaction(function () use ($request, $validated) {
            // 1. Buat akun user dengan role konsultan
            $user = new User();
            $user->forceFill([
                'name'              => $validated['name'],
                'email'             => $validated['email'],
                'password'          => Hash::make($validated['password']),
                'role'              => 'konsultan',
                'email_verified_at' => now(),
            ])->save();

            // 2. Simpan foto (kalau ada)
            $fotoPath = $request->hasFile('foto_profil')
                ? $request->file('foto_profil')->store('foto_konsultan', 'public')
                : null;

            // 3. Buat profil konsultan
            KonsultanProfil::create([
                'user_id'     => $user->id,
                'kategori_id' => $validated['kategori_id'],
                'jabatan'     => $validated['jabatan'],
                'bio'         => $validated['bio'] ?? null,
                'foto_profil' => $fotoPath,
                'aktif'       => true,
            ]);
        });

        return redirect()->route('admin.konsultan-profil.index')
            ->with('success', 'Konsultan berhasil ditambahkan.');
    }

    public function edit(KonsultanProfil $konsultanProfil)
    {
        $kategoris = KategoriKonsultan::all();
        return view('admin.konsultan-profil.edit', compact('konsultanProfil', 'kategoris'));
    }

    public function update(Request $request, KonsultanProfil $konsultanProfil)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|max:255|unique:users,email,' . $konsultanProfil->user_id,
            'kategori_id' => 'required|exists:kategori_konsultans,id',
            'jabatan'     => 'required|string|max:255',
            'bio'         => 'nullable|string',
            'foto_profil' => 'nullable|image|max:2048',
            'aktif'       => 'boolean',
        ]);

        DB::transaction(function () use ($request, $validated, $konsultanProfil) {
            $user = $konsultanProfil->user;
            $user->forceFill([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ])->save();

            unset($validated['name'], $validated['email']);
            if ($request->hasFile('foto_profil')) {
                $fotoPath = $request->file('foto_profil')->store('foto_konsultan', 'public');
                foreach (array_unique([$konsultanProfil->foto_profil, $user->foto_profil]) as $fotoLama) {
                    if ($fotoLama && $fotoLama !== $fotoPath) {
                        Storage::disk('public')->delete($fotoLama);
                    }
                }
                $validated['foto_profil'] = $fotoPath;
                $user->forceFill(['foto_profil' => $fotoPath])->save();
            }

            $konsultanProfil->update($validated);
        });

        return redirect()->route('admin.konsultan-profil.index')
            ->with('success', 'Profil konsultan berhasil diperbarui.');
    }

    public function destroy(KonsultanProfil $konsultanProfil)
    {
        $user = $konsultanProfil->user;
        foreach (array_unique([$konsultanProfil->foto_profil, $user?->foto_profil]) as $foto) {
            if ($foto) Storage::disk('public')->delete($foto);
        }
        $user?->delete(); // Profil dan akun harus hilang bersama dari kedua menu.

        return redirect()->route('admin.konsultan-profil.index')
            ->with('success', 'Profil konsultan berhasil dihapus.');
    }
}
