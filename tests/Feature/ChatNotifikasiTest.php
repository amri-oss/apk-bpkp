<?php

namespace Tests\Feature;

use App\Models\Konsultasi;
use App\Models\Pesan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatNotifikasiTest extends TestCase
{
    use RefreshDatabase;

    private function pasangan(): array
    {
        $audiens = User::factory()->create(['role' => 'audiens', 'name' => 'Audiens Uji']);
        $konsultan = User::factory()->create(['role' => 'konsultan', 'name' => 'Konsultan Uji']);

        return [$audiens, $konsultan];
    }

    private function buatKonsultasi(User $audiens, User $konsultan, string $status = 'berlangsung', ?string $dibuat = null): Konsultasi
    {
        $konsultasi = Konsultasi::create([
            'audiens_id' => $audiens->id,
            'konsultan_id' => $konsultan->id,
            'status' => $status,
        ]);

        if ($dibuat) {
            $konsultasi->forceFill(['created_at' => $dibuat])->save();
        }

        return $konsultasi;
    }

    private function buatPesan(Konsultasi $konsultasi, User $pengirim, string $isi, ?string $waktu = null): Pesan
    {
        $pesan = Pesan::create([
            'konsultasi_id' => $konsultasi->id,
            'pengirim_id' => $pengirim->id,
            'isi_pesan' => $isi,
        ]);

        if ($waktu) {
            $pesan->forceFill(['created_at' => $waktu])->save();
        }

        return $pesan;
    }

    public function test_lonceng_notifikasi_menampilkan_jumlah_pesan_belum_dibaca(): void
    {
        [$audiens, $konsultan] = $this->pasangan();

        $pertama = $this->buatKonsultasi($audiens, $konsultan);
        $this->buatPesan($pertama, $audiens, 'Pesan satu');
        $this->buatPesan($pertama, $audiens, 'Pesan dua');

        $kedua = $this->buatKonsultasi($audiens, $konsultan);
        $this->buatPesan($kedua, $audiens, 'Pesan tiga');

        $html = $this->actingAs($konsultan)
            ->get(route('konsultan.dashboard'))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression('/-right-1 -top-1[^>]*>\s*3\s*</', $html);
    }

    public function test_notifikasi_menampilkan_respon_konsultasi_dan_id_konsul(): void
    {
        $audiens = User::factory()->create(['role' => 'audiens', 'name' => 'Rizka']);
        $konsultan = User::factory()->create(['role' => 'konsultan', 'name' => 'Konsultan Uji']);

        $disetujui = $this->buatKonsultasi($audiens, $konsultan, 'berlangsung');
        $ditolak = $this->buatKonsultasi($audiens, $konsultan, 'selesai');
        $ditolak->update(['alasan_tolak' => 'Dokumen belum lengkap']);
        $selesai = $this->buatKonsultasi($audiens, $konsultan, 'selesai');

        $html = $this->actingAs($audiens)
            ->get(route('audiens.konsultasi-saya'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Konsultasi disetujui', $html);
        $this->assertStringContainsString('Konsultasi ditolak', $html);
        $this->assertStringContainsString('Konsultasi selesai', $html);
        $this->assertStringContainsString('ID Konsul: Rizka - ' . $disetujui->id, $html);
        $this->assertStringContainsString('ID Konsul: Rizka - ' . $ditolak->id, $html);
        $this->assertStringContainsString('ID Konsul: Rizka - ' . $selesai->id, $html);
        $this->assertStringContainsString('Dokumen belum lengkap', $html);
    }

    public function test_sidebar_dan_header_menampilkan_id_konsul(): void
    {
        $audiens = User::factory()->create(['role' => 'audiens', 'name' => 'Rizka']);
        $konsultan = User::factory()->create(['role' => 'konsultan', 'name' => 'Konsultan Uji']);

        $konsultasi = $this->buatKonsultasi($audiens, $konsultan);

        $this->actingAs($audiens)
            ->get(route('audiens.konsultasi-saya', $konsultasi))
            ->assertOk()
            ->assertSee('ID: Rizka - ' . $konsultasi->id)
            ->assertSee('ID Konsul: Rizka - ' . $konsultasi->id);
    }

    public function test_dashboard_konsultan_menampilkan_id_konsul(): void
    {
        $audiens = User::factory()->create(['role' => 'audiens', 'name' => 'Audiens Uji']);
        $konsultan = User::factory()->create(['role' => 'konsultan', 'name' => 'Konsultan Uji']);

        $konsultasi = $this->buatKonsultasi($audiens, $konsultan, 'menunggu');

        $this->actingAs($konsultan)
            ->get(route('konsultan.dashboard'))
            ->assertOk()
            ->assertSee('ID: Audiens Uji - ' . $konsultasi->id)
            ->assertSee('ID Konsul:')
            ->assertSee('Audiens Uji - ' . $konsultasi->id);
    }

    public function test_membuka_obrolan_konsultan_menandai_pesan_terbaca_dan_badge_hilang(): void
    {
        [$audiens, $konsultan] = $this->pasangan();

        $konsultasi = $this->buatKonsultasi($audiens, $konsultan);
        $pesanSatu = $this->buatPesan($konsultasi, $audiens, 'Mohon arahan');
        $pesanDua = $this->buatPesan($konsultasi, $audiens, 'Mohon arahan lanjutan');

        $this->actingAs($konsultan)
            ->get(route('konsultan.obrolan', $konsultasi))
            ->assertOk()
            ->assertDontSee('wa-unread-badge');

        $this->assertNotNull($pesanSatu->fresh()->dibaca_at);
        $this->assertNotNull($pesanDua->fresh()->dibaca_at);

        $konsultasi->refresh()->load('pesans');
        $this->assertSame(0, $konsultasi->jumlahBelumDibacaUntuk($konsultan->id));
    }

    public function test_audiens_melihat_ceklis_dua_setelah_pesan_dibaca_konsultan(): void
    {
        [$audiens, $konsultan] = $this->pasangan();

        $konsultasi = $this->buatKonsultasi($audiens, $konsultan);
        $pesan = $this->buatPesan($konsultasi, $audiens, 'Pertanyaan audiens');

        $this->actingAs($konsultan)
            ->get(route('konsultan.obrolan', $konsultasi))
            ->assertOk();

        $this->assertNotNull($pesan->fresh()->dibaca_at);

        $this->actingAs($audiens)
            ->get(route('audiens.konsultasi-saya', $konsultasi))
            ->assertOk()
            ->assertSee('✓✓');
    }

    public function test_daftar_obrolan_diurutkan_berdasarkan_pesan_terakhir(): void
    {
        [$audiens, $konsultan] = $this->pasangan();

        $lama = $this->buatKonsultasi($audiens, $konsultan, 'selesai', now()->subDays(5)->toDateTimeString());
        $this->buatPesan($lama, $audiens, 'Pesan lama', now()->subDays(5)->toDateTimeString());

        $baru = $this->buatKonsultasi($audiens, $konsultan, 'selesai', now()->subDays(1)->toDateTimeString());
        $this->buatPesan($baru, $konsultan, 'Pesan paling baru', now()->subMinutes(5)->toDateTimeString());

        $this->actingAs($audiens)
            ->get(route('audiens.konsultasi-saya'))
            ->assertOk()
            ->assertSeeInOrder(['Pesan paling baru', 'Pesan lama']);
    }

    public function test_polling_mengirim_status_dibaca_untuk_ceklis_dua(): void
    {
        [$audiens, $konsultan] = $this->pasangan();

        $konsultasi = $this->buatKonsultasi($audiens, $konsultan);
        $pesan = $this->buatPesan($konsultasi, $audiens, 'Pertanyaan audiens');

        $this->actingAs($konsultan)->get(route('konsultan.obrolan', $konsultasi))->assertOk();

        $json = $this->actingAs($audiens)
            ->getJson(route('pesan.index', $konsultasi))
            ->assertOk()
            ->json();

        $this->assertSame($pesan->id, $json['dibaca_hingga']);
        $this->assertTrue($json['pesans'][0]['dibaca']);
    }

    public function test_endpoint_tandai_dibaca_hanya_untuk_peserta_konsultasi(): void
    {
        [$audiens, $konsultan] = $this->pasangan();
        $orangLuar = User::factory()->create(['role' => 'audiens']);

        $konsultasi = $this->buatKonsultasi($audiens, $konsultan);
        $pesan = $this->buatPesan($konsultasi, $audiens, 'Pertanyaan audiens');

        $this->actingAs($orangLuar)
            ->patchJson(route('pesan.dibaca', $konsultasi))
            ->assertForbidden();

        $this->assertNull($pesan->fresh()->dibaca_at);

        $this->actingAs($konsultan)
            ->patchJson(route('pesan.dibaca', $konsultasi))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertNotNull($pesan->fresh()->dibaca_at);
    }

    public function test_tombol_kembali_di_mobile_menampilkan_daftar_tanpa_membuka_obrolan(): void
    {
        [$audiens, $konsultan] = $this->pasangan();

        $konsultasi = $this->buatKonsultasi($audiens, $konsultan);
        $this->buatPesan($konsultasi, $audiens, 'Pertanyaan audiens');

        $this->actingAs($audiens)
            ->get(route('audiens.konsultasi-saya'))
            ->assertOk()
            ->assertSee('wa-thread');

        $this->actingAs($audiens)
            ->get(route('audiens.konsultasi-saya', ['daftar' => 1]))
            ->assertOk()
            ->assertDontSee('wa-thread');
    }

    public function test_halaman_peserta_menyertakan_polling_aktivitas_konsultasi(): void
    {
        [$audiens, $konsultan] = $this->pasangan();

        $this->actingAs($audiens)
            ->get(route('audiens.konsultasi-saya'))
            ->assertOk()
            ->assertSee('aktivitas-konsultasi/versi', false);

        $this->actingAs($konsultan)
            ->get(route('konsultan.dashboard'))
            ->assertOk()
            ->assertSee('aktivitas-konsultasi/versi', false);
    }

    public function test_terima_konsultasi_membuat_balasan_otomatis_dari_konsultan(): void
    {
        [$audiens, $konsultan] = $this->pasangan();

        $konsultasi = $this->buatKonsultasi($audiens, $konsultan, 'menunggu');

        $this->actingAs($konsultan)
            ->patch(route('konsultasi.terima', $konsultasi))
            ->assertRedirect(route('konsultan.obrolan', $konsultasi->id));

        $this->assertSame('berlangsung', $konsultasi->fresh()->status);

        $balasan = $konsultasi->pesans()->first();

        $this->assertNotNull($balasan);
        $this->assertSame($konsultan->id, $balasan->pengirim_id);
        $this->assertSame(
            'Konsultasi Anda telah disetujui. Silakan sampaikan pertanyaan Anda. Kami siap membantu.',
            $balasan->isi_pesan
        );

        $this->actingAs($audiens)
            ->get(route('audiens.konsultasi-saya', $konsultasi))
            ->assertOk()
            ->assertSee('Konsultasi Anda telah disetujui. Silakan sampaikan pertanyaan Anda. Kami siap membantu.');
    }

    public function test_terima_ulang_tidak_membuat_balasan_otomatis_ganda(): void
    {
        [$audiens, $konsultan] = $this->pasangan();

        $konsultasi = $this->buatKonsultasi($audiens, $konsultan, 'menunggu');

        $this->actingAs($konsultan)->patch(route('konsultasi.terima', $konsultasi))->assertRedirect();
        $this->actingAs($konsultan)->patch(route('konsultasi.terima', $konsultasi))->assertRedirect();

        $this->assertSame(1, $konsultasi->pesans()->count());
    }

    public function test_versi_aktivitas_berubah_ketika_status_konsultasi_diubah(): void
    {
        [$audiens, $konsultan] = $this->pasangan();

        $konsultasi = $this->buatKonsultasi($audiens, $konsultan, 'menunggu');

        $versiAwal = $this->actingAs($audiens)
            ->getJson(route('aktivitas-konsultasi.versi'))
            ->assertOk()
            ->json('versi');

        $konsultasi->update(['status' => 'berlangsung']);

        $versiBaru = $this->actingAs($audiens)
            ->getJson(route('aktivitas-konsultasi.versi'))
            ->assertOk()
            ->json('versi');

        $this->assertNotSame($versiAwal, $versiBaru);
    }

    public function test_pratinjau_obrolan_yang_sudah_dibersihkan_tidak_menampilkan_pesan_lama(): void
    {
        [$audiens, $konsultan] = $this->pasangan();

        $konsultasi = $this->buatKonsultasi($audiens, $konsultan, 'selesai');
        $this->buatPesan($konsultasi, $audiens, 'Pesan yang sudah dibersihkan', now()->subHour()->toDateTimeString());

        $konsultasi->forceFill(['dibersihkan_konsultan_pada' => now()])->save();

        $this->actingAs($konsultan)
            ->get(route('konsultan.obrolan', $konsultasi))
            ->assertOk()
            ->assertDontSee('Pesan yang sudah dibersihkan')
            ->assertSee('Belum ada pesan');
    }
}
