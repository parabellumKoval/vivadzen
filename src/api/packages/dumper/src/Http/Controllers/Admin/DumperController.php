<?php

namespace ParabellumKoval\Dumper\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use ParabellumKoval\Dumper\Http\Requests\CreateDumpRequest;
use ParabellumKoval\Dumper\Http\Requests\RestoreDumpRequest;
use ParabellumKoval\Dumper\Services\DumpManager;
use ParabellumKoval\Dumper\Services\TableInspector;
use Prologue\Alerts\Facades\Alert;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class DumperController extends Controller
{
    public function __construct(
        protected DumpManager $manager,
        protected TableInspector $inspector
    ) {
    }

    public function index(): Response
    {
        return response()->view('dumper::backpack.index', [
            'tables' => $this->inspector->tables(),
            'manualDumps' => $this->manager->manualDumps(),
            'autoCases' => $this->manager->autoCases(),
            'autoDumps' => $this->manager->groupedAutoDumps(),
        ]);
    }

    public function store(CreateDumpRequest $request): RedirectResponse
    {
        try {
            $record = $this->manager->createManualDump(
                $request->input('tables', []),
                $request->input('label')
            );

            Alert::success(trans('dumper::messages.created', ['file' => $record->filename]))->flash();
        } catch (Throwable $exception) {
            report($exception);
            Alert::error(trans('dumper::messages.create_failed', ['message' => $exception->getMessage()]))->flash();
        }

        return redirect()->route('backpack.dumper.index');
    }

    public function restore(RestoreDumpRequest $request): RedirectResponse
    {
        $record = $this->manager->resolveReference($request->input('reference'));

        if (!$record) {
            Alert::warning(trans('dumper::messages.not_found'))->flash();

            return redirect()->back();
        }

        try {
            $this->manager->restore($record);
            Alert::success(trans('dumper::messages.restored'))->flash();
        } catch (Throwable $exception) {
            report($exception);
            Alert::error(trans('dumper::messages.restore_failed', ['message' => $exception->getMessage()]))->flash();
        }

        return redirect()->back();
    }

    public function download(string $reference): StreamedResponse
    {
        $record = $this->manager->resolveReference($reference);

        abort_unless($record, 404);

        $disk = Storage::disk($record->disk);
        $stream = $disk->readStream($record->path);

        if (!$stream) {
            abort(404);
        }

        return response()->streamDownload(function () use ($stream) {
            fpassthru($stream);
            fclose($stream);
        }, $record->filename, [
            'Content-Type' => 'application/sql',
        ]);
    }

    public function runAutoCase(string $case): RedirectResponse
    {
        try {
            $record = $this->manager->createAutoDump($case);
        } catch (Throwable $exception) {
            report($exception);
            Alert::error(trans('dumper::messages.create_failed', ['message' => $exception->getMessage()]))->flash();

            return redirect()->back();
        }

        if (!$record) {
            Alert::warning(trans('dumper::messages.not_found'))->flash();

            return redirect()->back();
        }

        Alert::success(trans('dumper::messages.auto_created', [
            'case' => $case,
            'file' => $record->filename,
        ]))->flash();

        return redirect()->back();
    }
}
