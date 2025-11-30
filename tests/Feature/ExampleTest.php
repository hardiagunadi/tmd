<?php

namespace Tests\Feature;

use App\Models\Tagihan;
use App\Models\TagihanPenarikan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->followingRedirects()->get('/');

        $response->assertOk();
        $response->assertSee('Masuk');
    }

    public function test_user_can_login_and_access_tagihan(): void
    {
        $this->seed();

        $response = $this->post('/login', [
            'email' => 'admin@admin.com',
            'password' => 'admin',
        ]);

        $response->assertRedirect(route('tagihan.index'));

        $tagihanResponse = $this->get('/tagihan');
        $tagihanResponse->assertOk();
    }

    public function test_user_can_store_rekap_penarikan_from_database_tagihan(): void
    {
        $this->seed();

        $user = User::first();

        $tagihan = Tagihan::create([
            'nama_instansi' => 'SMP Negeri 1',
            'alamat_instansi' => 'Jl. Merdeka',
            'no_invoice' => 'INV-001',
            'no_pelanggan' => 'PLG001',
            'bulan_tagihan' => 1,
            'tahun_tagihan' => 2025,
            'biaya_langganan' => 100000,
            'biaya_admin' => 0,
            'deskripsi_paket' => 'Paket Premium',
            'printed_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('penarikan.store'), [
            'petugas' => 'Deswi',
            'tagihan_id' => $tagihan->id,
        ]);

        $response->assertRedirect(route('penarikan.index'));

        $this->assertDatabaseHas('tagihan_penarikans', [
            'tagihan_id' => $tagihan->id,
            'nama_pelanggan' => 'SMP Negeri 1',
            'petugas' => 'Deswi',
        ]);
    }

    public function test_rekap_page_displays_totals_per_petugas(): void
    {
        $this->seed();
        $user = User::first();

        $deswiTagihan = Tagihan::create([
            'nama_instansi' => 'Pelanggan Deswi',
            'alamat_instansi' => 'Alamat A',
            'no_invoice' => 'INV-DES-001',
            'no_pelanggan' => 'PLG-DES-01',
            'bulan_tagihan' => 2,
            'tahun_tagihan' => 2025,
            'biaya_langganan' => 200000,
            'biaya_admin' => 0,
            'deskripsi_paket' => 'Paket A',
            'printed_at' => now(),
        ]);

        $slametTagihan = Tagihan::create([
            'nama_instansi' => 'Pelanggan Slamet',
            'alamat_instansi' => 'Alamat B',
            'no_invoice' => 'INV-SLM-001',
            'no_pelanggan' => 'PLG-SLM-01',
            'bulan_tagihan' => 3,
            'tahun_tagihan' => 2025,
            'biaya_langganan' => 250000,
            'biaya_admin' => 0,
            'deskripsi_paket' => 'Paket B',
            'printed_at' => now(),
        ]);

        TagihanPenarikan::create([
            'petugas' => 'Deswi',
            'nama_pelanggan' => 'Pelanggan Deswi',
            'tagihan_id' => $deswiTagihan->id,
        ]);

        TagihanPenarikan::create([
            'petugas' => 'Deswi',
            'nama_pelanggan' => 'Pelanggan Deswi 2',
        ]);

        TagihanPenarikan::create([
            'petugas' => 'Slamet',
            'nama_pelanggan' => 'Pelanggan Slamet',
            'tagihan_id' => $slametTagihan->id,
        ]);

        $response = $this->actingAs($user)->get(route('penarikan.index'));

        $response->assertOk();
        $response->assertSee('Deswi');
        $response->assertSee('2 Tagihan');
        $response->assertSee('Slamet');
        $response->assertSee('1 Tagihan');
        $response->assertSee('Ade');
    }
}
