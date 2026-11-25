<?php

namespace Tests\Feature;

use App\Models\PembayaranSewa;
use App\Models\Peralatan;
use App\Models\TransaksiSewa;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_homepage_and_catalog_are_separate_pages(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('catalog'))
            ->assertDontSee('Traktor Roda Dua');

        $this->get(route('catalog'))
            ->assertOk()
            ->assertSee('Traktor Roda Dua')
            ->assertSee('images/equipment/traktor.jpg');
    }

    public function test_a_customer_can_update_profile_details_without_changing_their_role(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Pesanan saya')
            ->assertSee('Profil');

        $this->put(route('profile.update'), [
            'name' => 'Petani Sejahtera',
            'email' => 'sejahtera@example.test',
            'phone' => '08123456789',
            'address' => 'Desa Makmur, Kecamatan Tani',
            'role' => 'admin',
        ])->assertRedirect(route('profile.edit'));

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'name' => 'Petani Sejahtera',
            'email' => 'sejahtera@example.test',
            'phone' => '08123456789',
            'address' => 'Desa Makmur, Kecamatan Tani',
            'role' => 'customer',
        ]);
    }

    public function test_a_guest_cannot_open_the_profile_page(): void
    {
        $this->get(route('profile.edit'))
            ->assertRedirect(route('login'));
    }

    public function test_a_customer_cannot_update_their_profile_to_another_users_email(): void
    {
        $customer = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($customer)
            ->put(route('profile.update'), [
                'name' => 'Petani Baru',
                'email' => $otherUser->email,
                'phone' => '08123456789',
                'address' => 'Desa Makmur',
            ])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
        ]);
    }

    public function test_a_guest_can_open_an_equipment_detail_page(): void
    {
        $this->seed(DatabaseSeeder::class);
        $equipment = Peralatan::where('slug', 'traktor-roda-dua')->firstOrFail();

        $this->get(route('equipment.show', $equipment->slug))
            ->assertOk()
            ->assertSee('Masuk untuk menyewa')
            ->assertSee('Rp 275.000');
    }

    public function test_a_customer_can_book_available_units_but_cannot_double_book_them(): void
    {
        $this->seed(DatabaseSeeder::class);
        $customer = User::factory()->create();
        $equipment = Peralatan::where('slug', 'traktor-roda-dua')->firstOrFail();
        $dates = [
            'peralatan_id' => $equipment->id,
            'tanggal_mulai' => now()->addDays(5)->toDateString(),
            'tanggal_selesai' => now()->addDays(6)->toDateString(),
            'jumlah' => 2,
            'alamat_pengiriman' => 'Desa Makmur, Kecamatan Tani',
        ];

        $this->actingAs($customer)
            ->post(route('bookings.store'), $dates)
            ->assertRedirect(route('dashboard'));

        $rental = TransaksiSewa::firstOrFail();
        $this->assertSame(2, $rental->items()->count());
        $this->assertSame('1700000.00', $rental->total);

        $otherCustomer = User::factory()->create();
        $this->actingAs($otherCustomer)
            ->from(route('equipment.show', $equipment->slug))
            ->post(route('bookings.store'), [...$dates, 'jumlah' => 1])
            ->assertSessionHasErrors('jumlah');

        $this->assertDatabaseCount('transaksi_sewa', 1);
    }

    public function test_an_admin_cannot_place_a_rental_order(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::factory()->create(['role' => 'admin']);
        $equipment = Peralatan::where('slug', 'traktor-roda-dua')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('equipment.show', $equipment->slug))
            ->assertOk()
            ->assertSee('Akun admin tidak dapat mengajukan pemesanan alat.')
            ->assertDontSee('Ajukan pemesanan');

        $this->actingAs($admin)
            ->post(route('bookings.store'), [
                'peralatan_id' => $equipment->id,
                'tanggal_mulai' => now()->toDateString(),
                'tanggal_selesai' => now()->addDay()->toDateString(),
                'jumlah' => 1,
                'alamat_pengiriman' => 'Desa Makmur, Kecamatan Tani',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('transaksi_sewa', 0);
    }

    public function test_registration_does_not_allow_a_customer_to_assign_themselves_admin_role(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Petani Baru',
            'email' => 'petani@example.test',
            'phone' => '08123456789',
            'password' => 'sawahkita123',
            'password_confirmation' => 'sawahkita123',
            'role' => 'admin',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'petani@example.test',
            'role' => 'customer',
        ]);
    }

    public function test_customer_cannot_open_admin_dashboard(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_login_always_redirects_to_the_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.test',
            'password' => 'sawahkita123',
            'role' => 'admin',
        ]);

        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));

        $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'sawahkita123',
        ])->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_navigation_shows_catalog_and_hides_customer_orders(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Katalog')
            ->assertSee('Dashboard admin')
            ->assertDontSee('Pesanan saya')
            ->assertDontSee('Profil');
    }

    public function test_an_admin_can_download_all_rentals_as_excel_compatible_csv(): void
    {
        $this->freezeTime();
        $this->seed(DatabaseSeeder::class);
        $customer = User::factory()->create([
            'name' => '=HYPERLINK("https://example.test","Click")',
        ]);
        $equipment = Peralatan::where('slug', 'traktor-roda-dua')->firstOrFail();
        $rental = $this->createRental($customer, $equipment);
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->get(route('admin.rentals.export'))
            ->assertStreamed()
            ->assertDownload('data-penyewaan-'.now()->format('Ymd').'.csv');

        $content = $response->streamedContent();

        $this->assertStringContainsString("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString('"Nomor sewa";"Tanggal pesan";"Nama pelanggan"', $content);
        $this->assertStringContainsString($rental->nomor_sewa, $content);
        $this->assertStringContainsString("'=HYPERLINK", $content);
        $this->assertStringContainsString('Traktor Roda Dua', $content);
        $this->assertStringContainsString('Desa Makmur, Kecamatan Tani', $content);
    }

    public function test_a_customer_cannot_download_rental_export(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->get(route('admin.rentals.export'))
            ->assertForbidden();
    }

    public function test_a_guest_is_redirected_to_login_from_rental_export(): void
    {
        $this->get(route('admin.rentals.export'))
            ->assertRedirect(route('login'));
    }

    public function test_an_admin_can_verify_a_customer_payment(): void
    {
        $this->seed(DatabaseSeeder::class);
        $customer = User::factory()->create();
        $equipment = Peralatan::where('slug', 'traktor-roda-dua')->firstOrFail();
        $rental = $this->createRental($customer, $equipment);

        $this->actingAs($customer)
            ->post(route('payments.store', $rental), [
                'metode' => 'transfer',
                'referensi' => 'BANK-REF-100',
            ])
            ->assertRedirect();

        $payment = PembayaranSewa::firstOrFail();
        $this->assertSame('menunggu_verifikasi', $payment->status);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->patch(route('admin.payments.update', $payment), ['status' => 'diterima'])
            ->assertRedirect();

        $this->assertDatabaseHas('transaksi_sewa', [
            'id' => $rental->id,
            'status_pembayaran' => 'lunas',
        ]);
    }

    public function test_admin_return_inspection_sends_damaged_units_to_service(): void
    {
        $this->seed(DatabaseSeeder::class);
        $customer = User::factory()->create();
        $equipment = Peralatan::where('slug', 'traktor-roda-dua')->firstOrFail();
        $rental = $this->createRental($customer, $equipment);
        $rental->update(['status' => 'sedang_disewa']);

        $this->actingAs($customer)
            ->post(route('returns.store', $rental), ['catatan' => 'Sudah selesai digunakan.'])
            ->assertRedirect();

        $return = $rental->pengembalian()->firstOrFail();
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->patch(route('admin.returns.complete', $return), [
                'kondisi' => 'rusak',
                'denda' => '',
                'catatan' => 'Perlu diperiksa teknisi.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('unit_peralatan', [
            'id' => $rental->items()->value('unit_peralatan_id'),
            'status' => 'servis',
            'kondisi' => 'rusak',
        ]);
        $this->assertDatabaseHas('inspeksi_unit', [
            'transaksi_sewa_id' => $rental->id,
            'kondisi' => 'rusak',
        ]);
        $this->assertDatabaseHas('servis_unit', [
            'unit_peralatan_id' => $rental->items()->value('unit_peralatan_id'),
            'status' => 'dijadwalkan',
        ]);
    }

    public function test_an_admin_can_open_the_management_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Kelola')
            ->assertSee('inventaris')
            ->assertSee('Unduh CSV (Excel)');
    }

    private function createRental(User $customer, Peralatan $equipment): TransaksiSewa
    {
        $this->actingAs($customer)
            ->post(route('bookings.store'), [
                'peralatan_id' => $equipment->id,
                'tanggal_mulai' => now()->toDateString(),
                'tanggal_selesai' => now()->addDay()->toDateString(),
                'jumlah' => 1,
                'alamat_pengiriman' => 'Desa Makmur, Kecamatan Tani',
            ])
            ->assertRedirect(route('dashboard'));

        return TransaksiSewa::latest('id')->firstOrFail();
    }
}
