<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Mockery;
use ParabellumKoval\Webhooks\Services\WebhookDispatcher;
use Tests\TestCase;

class MigratePickupLocationsSettingsCommandTest extends TestCase
{
    public function test_command_moves_legacy_address_schedule_and_map_into_pickup_locations_array(): void
    {
        $table = config('backpack-settings.table', 'ak_settings');

        DB::table($table)->delete();
        DB::table($table)->insert([
            [
                'key' => 'site.contacts.address',
                'value' => 'Prague Store, Vaclavske namesti 1',
                'cast' => 'string',
                'region' => 'cz',
                'locale' => 'cs',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'site.contacts.schedule',
                'value' => 'Mon-Sun 10:00-20:00',
                'cast' => 'string',
                'region' => 'cz',
                'locale' => 'cs',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'site.contacts.map',
                'value' => '<iframe src="https://maps.example.com/prague"></iframe>',
                'cast' => 'string',
                'region' => 'cz',
                'locale' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $dispatcher = Mockery::mock(WebhookDispatcher::class);
        $dispatcher->shouldReceive('dispatch')->once()->with('refresh_settings', 'settings');
        $this->app->instance(WebhookDispatcher::class, $dispatcher);

        $this->artisan('settings:migrate-pickup-locations')
            ->expectsOutputToContain('перенесено 1')
            ->assertExitCode(0);

        $row = DB::table($table)
            ->where('key', 'site.contacts.pickup_locations')
            ->where('region', 'cz')
            ->where('locale', 'cs')
            ->first();

        $this->assertNotNull($row);
        $this->assertSame('array', $row->cast);
        $this->assertSame([
            [
                'title' => '',
                'address' => 'Prague Store, Vaclavske namesti 1',
                'schedule' => 'Mon-Sun 10:00-20:00',
                'map' => '<iframe src="https://maps.example.com/prague"></iframe>',
            ],
        ], json_decode((string) $row->value, true));
    }

    public function test_command_can_be_re_run_to_fill_schedule_and_map_into_existing_first_pickup_location(): void
    {
        $table = config('backpack-settings.table', 'ak_settings');

        DB::table($table)->delete();
        DB::table($table)->insert([
            [
                'key' => 'site.contacts.address',
                'value' => 'Prague Store, Vaclavske namesti 1',
                'cast' => 'string',
                'region' => 'cz',
                'locale' => 'cs',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'site.contacts.schedule',
                'value' => 'Mon-Sun 10:00-20:00',
                'cast' => 'string',
                'region' => 'cz',
                'locale' => 'cs',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'site.contacts.map',
                'value' => '<iframe src="https://maps.example.com/prague"></iframe>',
                'cast' => 'string',
                'region' => 'cz',
                'locale' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'site.contacts.pickup_locations',
                'value' => json_encode([
                    [
                        'title' => '',
                        'address' => 'Prague Store, Vaclavske namesti 1',
                    ],
                    [
                        'title' => 'Brno Store',
                        'address' => 'Brno, Ceska 10',
                        'schedule' => 'Mon-Fri 09:00-18:00',
                    ],
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'cast' => 'array',
                'region' => 'cz',
                'locale' => 'cs',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $dispatcher = Mockery::mock(WebhookDispatcher::class);
        $dispatcher->shouldReceive('dispatch')->once()->with('refresh_settings', 'settings');
        $this->app->instance(WebhookDispatcher::class, $dispatcher);

        $this->artisan('settings:migrate-pickup-locations')
            ->expectsOutputToContain('перенесено 1')
            ->assertExitCode(0);

        $row = DB::table($table)
            ->where('key', 'site.contacts.pickup_locations')
            ->where('region', 'cz')
            ->where('locale', 'cs')
            ->first();

        $this->assertNotNull($row);
        $this->assertSame([
            [
                'title' => '',
                'address' => 'Prague Store, Vaclavske namesti 1',
                'schedule' => 'Mon-Sun 10:00-20:00',
                'map' => '<iframe src="https://maps.example.com/prague"></iframe>',
            ],
            [
                'title' => 'Brno Store',
                'address' => 'Brno, Ceska 10',
                'schedule' => 'Mon-Fri 09:00-18:00',
            ],
        ], json_decode((string) $row->value, true));
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
