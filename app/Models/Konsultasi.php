<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Konsultasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'audiens_id',
        'konsultan_id',
        'status',
        'alasan_tolak',
        'topik_id',
        'topik_lainnya',
        'disembunyikan_oleh_audiens',
        'disembunyikan_oleh_konsultan',
        'dibersihkan_audiens_pada',
        'dibersihkan_konsultan_pada',
    ];

    protected $casts = [
        'dibersihkan_audiens_pada' => 'datetime',
        'dibersihkan_konsultan_pada' => 'datetime',
    ];

    public function audiens()
    {
        return $this->belongsTo(User::class, 'audiens_id');
    }

    public function konsultan()
    {
        return $this->belongsTo(User::class, 'konsultan_id');
    }

    public function topik()
    {
        return $this->belongsTo(Topik::class);
    }

    public function pesans()
    {
        return $this->hasMany(Pesan::class)->orderBy('created_at');
    }

    public function pesanTerakhir()
    {
        return $this->hasOne(Pesan::class)->latestOfMany();
    }

    /**
     * Pesan yang masih terlihat oleh pengguna tertentu.
     *
     * Memakai relasi yang sudah dimuat agar tidak menambah query, lalu
     * menyaring pesan yang disembunyikan pengirim sendiri dan pesan sebelum
     * percakapan dibersihkan (Hapus Semua).
     */
    public function pesanTerlihatUntuk($userId): \Illuminate\Support\Collection
    {
        $userId = (int) $userId;

        $pesans = $this->relationLoaded('pesans') ? $this->pesans : $this->pesans()->get();

        $batasBersih = $userId === (int) $this->audiens_id
            ? $this->dibersihkan_audiens_pada
            : $this->dibersihkan_konsultan_pada;

        return $pesans
            ->filter(function (Pesan $pesan) use ($userId, $batasBersih) {
                if ($pesan->disembunyikan_oleh_pengirim && (int) $pesan->pengirim_id === $userId) {
                    return false;
                }

                if ($batasBersih && $pesan->created_at && $pesan->created_at->lte($batasBersih)) {
                    return false;
                }

                return true;
            })
            ->sortBy(fn (Pesan $pesan) => $pesan->created_at?->getTimestamp() ?? 0)
            ->values();
    }

    public function pesanTerakhirUntuk($userId): ?Pesan
    {
        return $this->pesanTerlihatUntuk($userId)->last();
    }

    public function jumlahBelumDibacaUntuk($userId): int
    {
        $userId = (int) $userId;

        return $this->pesanTerlihatUntuk($userId)
            ->filter(fn (Pesan $pesan) => (int) $pesan->pengirim_id !== $userId && $pesan->dibaca_at === null)
            ->count();
    }

    /**
     * Urutkan daftar konsultasi berdasarkan aktivitas terakhir (pesan terakhir).
     */
    public static function urutkanBerdasarkanAktivitas(\Illuminate\Support\Collection $konsultasis, $userId): \Illuminate\Support\Collection
    {
        return $konsultasis
            ->sortByDesc(function (Konsultasi $konsultasi) use ($userId) {
                $pesanTerakhir = $konsultasi->pesanTerakhirUntuk($userId);

                return $pesanTerakhir?->created_at?->getTimestamp()
                    ?? $konsultasi->created_at?->getTimestamp()
                    ?? 0;
            })
            ->values();
    }
}