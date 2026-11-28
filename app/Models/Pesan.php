<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pesan extends Model
{
    use HasFactory;

    protected $fillable = [
        'konsultasi_id',
        'pengirim_id',
        'isi_pesan',
        'disembunyikan_oleh_pengirim',
        'balas_ke_id',
        'file_path',
        'file_nama',
        'file_tipe',
        'dibaca_at',
    ];

    protected $casts = [
        'dibaca_at' => 'datetime',
    ];

    /**
     * Ikutkan status baca di JSON supaya polling frontend bisa langsung
     * menggambar ceklis dua tanpa menunggu permintaan berikutnya.
     */
    protected $appends = ['dibaca'];

    public function getDibacaAttribute(): bool
    {
        return $this->dibaca_at !== null;
    }

    public function konsultasi()
    {
        return $this->belongsTo(Konsultasi::class);
    }

    public function pengirim()
    {
        return $this->belongsTo(User::class, 'pengirim_id');
    }

    public function balasKe()
    {
        return $this->belongsTo(Pesan::class, 'balas_ke_id');
    }

    /**
     * Pesan yang belum dibaca oleh pengguna tertentu.
     *
     * Hanya menghitung pesan dari lawan bicara pada konsultasi yang masih
     * terlihat oleh pengguna (tidak disembunyikan) dan belum dibersihkan.
     */
    public function scopeBelumDibacaOleh(Builder $query, $user): Builder
    {
        $userId = $user instanceof User ? $user->id : (int) $user;

        return $query
            ->whereNull('pesans.dibaca_at')
            ->where('pesans.disembunyikan_oleh_pengirim', false)
            ->where('pesans.pengirim_id', '!=', $userId)
            ->whereExists(function ($sub) use ($userId) {
                $sub->selectRaw('1')
                    ->from('konsultasis')
                    ->whereColumn('konsultasis.id', 'pesans.konsultasi_id')
                    ->where(function ($sisi) use ($userId) {
                        $sisi->where(function ($q) use ($userId) {
                            $q->where('konsultasis.audiens_id', $userId)
                                ->where('konsultasis.disembunyikan_oleh_audiens', false)
                                ->where(function ($batas) {
                                    $batas->whereNull('konsultasis.dibersihkan_audiens_pada')
                                        ->orWhereColumn('pesans.created_at', '>', 'konsultasis.dibersihkan_audiens_pada');
                                });
                        })->orWhere(function ($q) use ($userId) {
                            $q->where('konsultasis.konsultan_id', $userId)
                                ->where('konsultasis.disembunyikan_oleh_konsultan', false)
                                ->where(function ($batas) {
                                    $batas->whereNull('konsultasis.dibersihkan_konsultan_pada')
                                        ->orWhereColumn('pesans.created_at', '>', 'konsultasis.dibersihkan_konsultan_pada');
                                });
                        });
                    });
            });
    }

    public function isGambar(): bool
    {
        if ($this->file_tipe && str_starts_with($this->file_tipe, 'image/')) {
            return true;
        }

        if ($this->file_nama) {
            $ext = strtolower(pathinfo($this->file_nama, PATHINFO_EXTENSION));
            return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        }

        return false;
    }
}