<?php

namespace Tests\Feature\Dumper;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\DB;
use Mockery;
use ParabellumKoval\Dumper\Data\DumpRecord;
use ParabellumKoval\Dumper\Services\DumpManager;
use ParabellumKoval\Dumper\Services\DumperSettings;
use ParabellumKoval\Dumper\Services\RemoteDumpManager;
use ParabellumKoval\Dumper\Services\TableInspector;
use Tests\TestCase;

class RemoteDumpDispatchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('backpack-settings.cache.enabled', false);
        DB::table('ak_settings')->delete();
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_manual_dump_dispatches_remote_upload_when_enabled(): void
    {
        config()->set('dumper.manual.sync_to_remote', true);

        $remoteManager = Mockery::mock(RemoteDumpManager::class);
        $remoteManager->shouldReceive('dispatchUpload')
            ->once()
            ->with(Mockery::on(fn (DumpRecord $record) => $record->path === 'dumps/manual/manual_test.sql'));

        $manager = $this->makeManager($remoteManager);
        $manager->fakeRecord = $this->makeRecord('manual', 'dumps/manual/manual_test.sql');

        $manager->createManualDump(null, null);

        $this->addToAssertionCount(1);
    }

    public function test_manual_dump_skips_remote_upload_when_disabled(): void
    {
        config()->set('dumper.manual.sync_to_remote', false);

        $remoteManager = Mockery::mock(RemoteDumpManager::class);
        $remoteManager->shouldNotReceive('dispatchUpload');

        $manager = $this->makeManager($remoteManager);
        $manager->fakeRecord = $this->makeRecord('manual', 'dumps/manual/manual_test.sql');

        $manager->createManualDump(null, null);

        $this->addToAssertionCount(1);
    }

    public function test_auto_dump_dispatches_remote_upload_when_case_requires_it(): void
    {
        config()->set('dumper.auto.cases', [
            'nightly' => [
                'label' => 'Nightly',
                'tables' => '*',
                'schedule' => 'daily',
                'sync_to_remote' => true,
            ],
        ]);

        $remoteManager = Mockery::mock(RemoteDumpManager::class);
        $remoteManager->shouldReceive('dispatchUpload')
            ->once()
            ->with(Mockery::on(fn (DumpRecord $record) => $record->caseKey === 'nightly'));

        $manager = $this->makeManager($remoteManager);
        $manager->fakeRecord = $this->makeRecord('auto', 'dumps/auto/nightly/auto_test.sql', 'nightly');

        $manager->createAutoDump('nightly');

        $this->addToAssertionCount(1);
    }

    public function test_manual_delete_dispatches_remote_delete_when_record_has_remote_providers(): void
    {
        config()->set('dumper.manual.sync_to_remote', false);

        $filesystem = Mockery::mock(FilesystemFactory::class);
        $disk = Mockery::mock(Filesystem::class);
        $filesystem->shouldReceive('disk')->once()->with('local')->andReturn($disk);
        $disk->shouldReceive('delete')->once()->with('dumps/manual/manual_test.sql');
        $disk->shouldReceive('delete')->once()->with('dumps/manual/manual_test.sql.meta.json');

        $remoteManager = Mockery::mock(RemoteDumpManager::class);
        $remoteManager->shouldReceive('dispatchDelete')
            ->once()
            ->with(Mockery::on(fn (DumpRecord $record) => $record->remoteProviders === ['bunny']));

        $manager = $this->makeManager($remoteManager, $filesystem);

        $manager->delete($this->makeRecord('manual', 'dumps/manual/manual_test.sql', null, ['bunny']));

        $this->addToAssertionCount(1);
    }

    public function test_manual_delete_dispatches_remote_delete_when_manual_sync_is_enabled(): void
    {
        config()->set('dumper.manual.sync_to_remote', true);

        $filesystem = Mockery::mock(FilesystemFactory::class);
        $disk = Mockery::mock(Filesystem::class);
        $filesystem->shouldReceive('disk')->once()->with('local')->andReturn($disk);
        $disk->shouldReceive('delete')->once()->with('dumps/manual/manual_test.sql');
        $disk->shouldReceive('delete')->once()->with('dumps/manual/manual_test.sql.meta.json');

        $remoteManager = Mockery::mock(RemoteDumpManager::class);
        $remoteManager->shouldReceive('dispatchDelete')
            ->once()
            ->with(Mockery::on(fn (DumpRecord $record) => $record->remoteProviders === []));

        $manager = $this->makeManager($remoteManager, $filesystem);

        $manager->delete($this->makeRecord('manual', 'dumps/manual/manual_test.sql'));

        $this->addToAssertionCount(1);
    }

    protected function makeManager(RemoteDumpManager $remoteManager, ?FilesystemFactory $filesystem = null): TestableDumpManager
    {
        $filesystem ??= Mockery::mock(FilesystemFactory::class);

        $inspector = Mockery::mock(TableInspector::class);
        $inspector->shouldReceive('connectionName')->andReturn('sqlite');

        return new TestableDumpManager(
            $filesystem,
            Mockery::mock(DatabaseManager::class),
            app('config'),
            $inspector,
            new DumperSettings(app('config')),
            $remoteManager
        );
    }

    /**
     * @param array<int, string> $remoteProviders
     */
    protected function makeRecord(string $source, string $path, ?string $caseKey = null, array $remoteProviders = []): DumpRecord
    {
        return new DumpRecord(
            'local',
            $path,
            basename($path),
            CarbonImmutable::now(),
            ['*'],
            $source,
            $caseKey,
            null,
            1024,
            $remoteProviders
        );
    }
}

class TestableDumpManager extends DumpManager
{
    public DumpRecord $fakeRecord;

    protected function createDump(
        string $disk,
        string $directory,
        string $prefix,
        string $source,
        ?string $caseKey,
        ?array $tables,
        ?string $label
    ): DumpRecord {
        return $this->fakeRecord;
    }

    protected function applyRetention(string $caseKey, array $case): void
    {
    }
}
