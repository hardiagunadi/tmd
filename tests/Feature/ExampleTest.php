<?php

namespace Tests\Feature;

use App\Models\PendapatanLain;
use App\Models\Tagihan;
use App\Models\TagihanPenarikan;
use App\Models\User;
use Carbon\Carbon;
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
        Carbon::setTestNow(Carbon::create(2025, 3, 15));

        $this->seed();

        $user = User::first();

        $tagihan = Tagihan::create([
            'nama_instansi' => 'SMP Negeri 1',
            'alamat_instansi' => 'Jl. Merdeka',
            'no_invoice' => 'INV-001',
            'no_pelanggan' => 'PLG001',
            'bulan_tagihan' => 3,
            'tahun_tagihan' => 2025,
            'biaya_langganan' => 100000,
            'biaya_admin' => 5000,
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
            'nominal' => 105000,
        ]);
    }

    public function test_store_rejects_tagihan_outside_current_billing_month(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 3, 15));

        $this->seed();
        $user = User::first();

        $previousMonthTagihan = Tagihan::create([
            'nama_instansi' => 'Pelanggan Lama',
            'alamat_instansi' => 'Alamat A',
            'no_invoice' => 'INV-OLD-001',
            'no_pelanggan' => 'PLG-OLD-01',
            'bulan_tagihan' => 2,
            'tahun_tagihan' => 2025,
            'biaya_langganan' => 200000,
            'biaya_admin' => 0,
            'deskripsi_paket' => 'Paket A',
            'printed_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('penarikan.store'), [
            'petugas' => 'Slamet',
            'tagihan_id' => $previousMonthTagihan->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('tagihan_penarikans', [
            'tagihan_id' => $previousMonthTagihan->id,
        ]);
    }

    public function test_assigned_tagihan_hidden_from_database_picker(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 3, 15));

        $this->seed();
        $user = User::first();

        $availableTagihan = Tagihan::create([
            'nama_instansi' => 'Pelanggan Baru',
            'alamat_instansi' => 'Alamat X',
            'no_invoice' => 'INV-NEW-001',
            'no_pelanggan' => 'PLG-NEW-01',
            'bulan_tagihan' => 3,
            'tahun_tagihan' => 2025,
            'biaya_langganan' => 120000,
            'biaya_admin' => 5000,
            'deskripsi_paket' => 'Paket X',
            'printed_at' => now(),
        ]);

        $assignedTagihan = Tagihan::create([
            'nama_instansi' => 'Pelanggan Sudah Ditarik',
            'alamat_instansi' => 'Alamat Y',
            'no_invoice' => 'INV-ASSIGNED-001',
            'no_pelanggan' => 'PLG-ASS-01',
            'bulan_tagihan' => 3,
            'tahun_tagihan' => 2025,
            'biaya_langganan' => 80000,
            'biaya_admin' => 2000,
            'deskripsi_paket' => 'Paket Y',
            'printed_at' => now(),
        ]);

        TagihanPenarikan::create([
            'petugas' => 'Ade',
            'nama_pelanggan' => 'Pelanggan Sudah Ditarik',
            'tagihan_id' => $assignedTagihan->id,
            'nominal' => $assignedTagihan->total_bayar,
        ]);

        $response = $this->actingAs($user)->get(route('penarikan.index'));

        $response->assertOk();
        $response->assertViewHas('printedTagihans', function ($collection) use ($availableTagihan, $assignedTagihan) {
            return $collection->contains('id', $availableTagihan->id)
                && ! $collection->contains('id', $assignedTagihan->id);
        });
    }

    public function test_rekap_page_displays_current_month_totals_and_supports_deletion(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 3, 15));

        $this->seed();
        $user = User::first();

        $deswiTagihan = Tagihan::create([
            'nama_instansi' => 'Pelanggan Deswi',
            'alamat_instansi' => 'Alamat A',
            'no_invoice' => 'INV-DES-001',
            'no_pelanggan' => 'PLG-DES-01',
            'bulan_tagihan' => 3,
            'tahun_tagihan' => 2025,
            'biaya_langganan' => 200000,
            'biaya_admin' => 0,
            'deskripsi_paket' => 'Paket A',
            'printed_at' => now(),
        ]);

        $adeTagihan = Tagihan::create([
            'nama_instansi' => 'Pelanggan Ade',
            'alamat_instansi' => 'Alamat B',
            'no_invoice' => 'INV-ADE-001',
            'no_pelanggan' => 'PLG-ADE-01',
            'bulan_tagihan' => 3,
            'tahun_tagihan' => 2025,
            'biaya_langganan' => 150000,
            'biaya_admin' => 10000,
            'deskripsi_paket' => 'Paket B',
            'printed_at' => now(),
        ]);

        $oldMonthTagihan = Tagihan::create([
            'nama_instansi' => 'Pelanggan Lama',
            'alamat_instansi' => 'Alamat C',
            'no_invoice' => 'INV-OLD-002',
            'no_pelanggan' => 'PLG-OLD-02',
            'bulan_tagihan' => 2,
            'tahun_tagihan' => 2025,
            'biaya_langganan' => 99999,
            'biaya_admin' => 0,
            'deskripsi_paket' => 'Paket C',
            'printed_at' => now(),
        ]);

        $deswiPenarikan = TagihanPenarikan::create([
            'petugas' => 'Deswi',
            'nama_pelanggan' => 'Pelanggan Deswi',
            'tagihan_id' => $deswiTagihan->id,
            'nominal' => $deswiTagihan->total_bayar,
        ]);

        TagihanPenarikan::create([
            'petugas' => 'Ade',
            'nama_pelanggan' => 'Pelanggan Ade',
            'tagihan_id' => $adeTagihan->id,
            'nominal' => $adeTagihan->total_bayar,
        ]);

        TagihanPenarikan::create([
            'petugas' => 'Deswi',
            'nama_pelanggan' => 'Pelanggan Lama',
            'tagihan_id' => $oldMonthTagihan->id,
            'nominal' => $oldMonthTagihan->total_bayar,
        ]);

        $response = $this->actingAs($user)->get(route('penarikan.index'));

        $response->assertOk();
        $response->assertSee('Deswi');
        $response->assertSee('1 Tagihan');
        $response->assertSee('Rp 200.000');
        $response->assertSee('Ade');
        $response->assertSee('Rp 160.000');
        $response->assertDontSee('INV-OLD-002');

        $deleteResponse = $this->actingAs($user)->delete(route('penarikan.destroy', $deswiPenarikan));

        $deleteResponse->assertRedirect(route('penarikan.index'));
        $deleteResponse->assertSessionHas('success');

        $this->assertDatabaseMissing('tagihan_penarikans', [
            'id' => $deswiPenarikan->id,
        ]);
    }

    public function test_store_rejects_duplicate_penarikan_for_same_tagihan(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 3, 15));

        $this->seed();
        $user = User::first();

        $tagihan = Tagihan::create([
            'nama_instansi' => 'Pelanggan Ganda',
            'alamat_instansi' => 'Alamat Z',
            'no_invoice' => 'INV-DUP-001',
            'no_pelanggan' => 'PLG-DUP-01',
            'bulan_tagihan' => 3,
            'tahun_tagihan' => 2025,
            'biaya_langganan' => 140000,
            'biaya_admin' => 5000,
            'deskripsi_paket' => 'Paket Z',
            'printed_at' => now(),
        ]);

        TagihanPenarikan::create([
            'petugas' => 'Deswi',
            'nama_pelanggan' => 'Pelanggan Ganda',
            'tagihan_id' => $tagihan->id,
            'nominal' => $tagihan->total_bayar,
        ]);

        $response = $this->actingAs($user)->post(route('penarikan.store'), [
            'petugas' => 'Slamet',
            'tagihan_id' => $tagihan->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseCount('tagihan_penarikans', 1);
    }

    public function test_penarikan_nominal_can_be_adjusted_inline(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 3, 15));

        $this->seed();
        $user = User::first();

        $tagihan = Tagihan::create([
            'nama_instansi' => 'Pelanggan Parsial',
            'alamat_instansi' => 'Alamat D',
            'no_invoice' => 'INV-PRC-001',
            'no_pelanggan' => 'PLG-PRC-01',
            'bulan_tagihan' => 3,
            'tahun_tagihan' => 2025,
            'biaya_langganan' => 200000,
            'biaya_admin' => 10000,
            'deskripsi_paket' => 'Paket C',
            'printed_at' => now(),
        ]);

        $penarikan = TagihanPenarikan::create([
            'petugas' => 'Slamet',
            'nama_pelanggan' => 'Pelanggan Parsial',
            'tagihan_id' => $tagihan->id,
            'nominal' => $tagihan->total_bayar,
        ]);

        $response = $this->actingAs($user)->patch(route('penarikan.update', $penarikan), [
            'nominal' => 75000,
        ]);

        $response->assertRedirect(route('penarikan.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tagihan_penarikans', [
            'id' => $penarikan->id,
            'nominal' => 75000,
        ]);

        $page = $this->actingAs($user)->get(route('penarikan.index'));

        $page->assertSee('Rp 75.000');
    }

    public function test_pendapatan_lain_menampilkan_total_bersih_per_petugas(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 3, 15));

        $this->seed();
        $user = User::first();

        $response = $this->actingAs($user)->post(route('pendapatan-lain.store'), [
            'petugas' => 'Deswi',
            'keterangan' => 'Pendapatan event',
            'pendapatan' => 200000,
            'pengeluaran' => 50000,
        ]);

        $response->assertRedirect(route('pendapatan-lain.index'));

        PendapatanLain::create([
            'petugas' => 'Ade',
            'keterangan' => 'Pendapatan tambahan',
            'pendapatan' => 100000,
            'pengeluaran' => 25000,
        ]);

        $page = $this->actingAs($user)->get(route('pendapatan-lain.index'));

        $page->assertSee('Pendapatan: Rp 200.000');
        $page->assertSee('Pengeluaran: Rp 50.000');
        $page->assertSee('Bersih: Rp 150.000');
        $page->assertSee('Ade');
        $page->assertSee('Bersih: Rp 75.000');

        $this->assertDatabaseHas('pendapatan_lains', [
            'petugas' => 'Deswi',
            'keterangan' => 'Pendapatan event',
            'pendapatan' => 200000,
            'pengeluaran' => 50000,
        ]);
    }

    public function test_rekap_keuangan_menampilkan_total_bersih_setelah_gaji(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 3, 20));

        $this->seed();
        $user = User::first();

        $tagihanDeswi = Tagihan::create([
            'nama_instansi' => 'Pelanggan Deswi',
            'alamat_instansi' => 'Alamat D',
            'no_invoice' => 'INV-DES-100',
            'no_pelanggan' => 'PLG-DES-100',
            'bulan_tagihan' => 3,
            'tahun_tagihan' => 2025,
            'biaya_langganan' => 120000,
            'biaya_admin' => 5000,
            'deskripsi_paket' => 'Paket D',
            'printed_at' => now(),
        ]);

        $tagihanAde = Tagihan::create([
            'nama_instansi' => 'Pelanggan Ade',
            'alamat_instansi' => 'Alamat E',
            'no_invoice' => 'INV-ADE-200',
            'no_pelanggan' => 'PLG-ADE-200',
            'bulan_tagihan' => 3,
            'tahun_tagihan' => 2025,
            'biaya_langganan' => 80000,
            'biaya_admin' => 20000,
            'deskripsi_paket' => 'Paket E',
            'printed_at' => now(),
        ]);

        TagihanPenarikan::create([
            'petugas' => 'Deswi',
            'nama_pelanggan' => 'Pelanggan Deswi',
            'tagihan_id' => $tagihanDeswi->id,
            'nominal' => $tagihanDeswi->total_bayar,
        ]);

        TagihanPenarikan::create([
            'petugas' => 'Ade',
            'nama_pelanggan' => 'Pelanggan Ade',
            'tagihan_id' => $tagihanAde->id,
            'nominal' => $tagihanAde->total_bayar,
        ]);

        $currentEntry = PendapatanLain::create([
            'petugas' => 'Deswi',
            'keterangan' => 'Pendapatan event',
            'pendapatan' => 40000,
            'pengeluaran' => 10000,
        ]);

        $previousEntry = PendapatanLain::create([
            'petugas' => 'Slamet',
            'keterangan' => 'Pendapatan lama',
            'pendapatan' => 50000,
            'pengeluaran' => 10000,
        ]);
        $previousEntry->forceFill(['created_at' => Carbon::create(2025, 2, 10)])->save();

        $response = $this->actingAs($user)->post(route('rekap-keuangan.gaji'), [
            'gaji' => [
                'Deswi' => 50000,
                'Slamet' => 25000,
                'Ade' => 0,
            ],
        ]);

        $response->assertRedirect(route('rekap-keuangan.index'));

        $this->assertDatabaseHas('petugas_gajis', [
            'petugas' => 'Deswi',
            'bulan' => 3,
            'tahun' => 2025,
            'nominal' => 50000,
        ]);

        $page = $this->actingAs($user)->get(route('rekap-keuangan.index'));

        $page->assertOk();
        $page->assertSee('Rp 225.000');
        $page->assertSee('Rp 30.000');
        $page->assertSee('Rp 75.000');
        $page->assertSee('Rp 180.000');

        $this->assertDatabaseHas('pendapatan_lains', [
            'id' => $currentEntry->id,
        ]);
        $this->assertDatabaseHas('pendapatan_lains', [
            'id' => $previousEntry->id,
        ]);
    }
}
