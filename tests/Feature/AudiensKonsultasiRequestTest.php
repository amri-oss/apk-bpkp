<?php

namespace Tests\Feature;

use App\Models\KategoriKonsultan;
use App\Models\Konsultasi;
use App\Models\KonsultanProfil;
use App\Models\Pesan;
use App\Models\Topik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AudiensKonsultasiRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_consultation_request_requires_a_topic_and_description(): void
    {
        [$audiens, $konsultan] = $this->usersAndTopic();

        $this->actingAs($audiens)
            ->from(route('audiens.pilih-konsultan'))
            ->post(route('audiens.konsultasi.store', $konsultan), [])
            ->assertSessionHasErrors(['topik_id', 'deskripsi']);

        $this->assertDatabaseCount('konsultasis', 0);
    }

    public function test_consultation_request_creates_a_waiting_consultation_with_initial_description(): void
    {
        [$audiens, $konsultan, $topik] = $this->usersAndTopic();

        $response = $this->actingAs($audiens)->post(route('audiens.konsultasi.store', $konsultan), [
            'topik_id' => $topik->id,
            'deskripsi' => 'Mohon penjelasan terkait topik ini.',
        ]);

        $konsultasi = Konsultasi::firstOrFail();

        $response->assertRedirect(route('audiens.konsultasi-saya', $konsultasi));
        $this->assertSame('menunggu', $konsultasi->status);
        $this->assertDatabaseHas('pesans', [
            'konsultasi_id' => $konsultasi->id,
            'pengirim_id' => $audiens->id,
            'isi_pesan' => 'Mohon penjelasan terkait topik ini.',
        ]);
    }

    public function test_waiting_audiens_sees_waiting_notice_without_chat_composer_or_controls(): void
    {
        [$audiens, $konsultan, $topik] = $this->usersAndTopic();
        $konsultasi = Konsultasi::create([
            'audiens_id' => $audiens->id,
            'konsultan_id' => $konsultan->id,
            'topik_id' => $topik->id,
            'status' => 'menunggu',
        ]);
        Pesan::create([
            'konsultasi_id' => $konsultasi->id,
            'pengirim_id' => $audiens->id,
            'isi_pesan' => 'Mohon penjelasan.',
        ]);

        $response = $this->actingAs($audiens)
            ->get(route('audiens.konsultasi-saya', $konsultasi));

        $response->assertOk()
            ->assertSee('Menunggu persetujuan konsultan')
            ->assertDontSee('id="form-pesan"', false)
            ->assertDontSee('Tandai Selesai')
            ->assertDontSee('Hapus Semua');
    }

    public function test_waiting_consultant_can_see_the_composer_to_accept_the_request(): void
    {
        [$audiens, $konsultan, $topik] = $this->usersAndTopic();
        $konsultasi = Konsultasi::create([
            'audiens_id' => $audiens->id,
            'konsultan_id' => $konsultan->id,
            'topik_id' => $topik->id,
            'status' => 'menunggu',
        ]);
        Pesan::create([
            'konsultasi_id' => $konsultasi->id,
            'pengirim_id' => $audiens->id,
            'isi_pesan' => 'Mohon penjelasan.',
        ]);

        $this->actingAs($konsultan)
            ->get(route('konsultan.obrolan', $konsultasi))
            ->assertOk()
            ->assertSee('id="form-pesan"', false)
            ->assertDontSee('id="btn-hapus-semua"', false)
            ->assertDontSee('Tandai Selesai');
    }

    private function usersAndTopic(): array
    {
        $audiens = User::factory()->create(['role' => 'audiens']);
        $konsultan = User::factory()->create(['role' => 'konsultan']);
        $kategori = KategoriKonsultan::create(['nama_kategori' => 'Keuangan']);
        KonsultanProfil::create([
            'user_id' => $konsultan->id,
            'kategori_id' => $kategori->id,
            'aktif' => true,
        ]);
        $topik = Topik::create([
            'kategori_id' => $kategori->id,
            'nama_topik' => 'Pengelolaan anggaran',
        ]);

        return [$audiens, $konsultan, $topik];
    }
}
