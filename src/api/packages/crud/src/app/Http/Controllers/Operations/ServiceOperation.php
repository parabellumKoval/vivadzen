<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Backpack\CRUD\app\Library\ServiceOperation\MergeService;
use Backpack\CRUD\app\Library\ServiceOperation\Similar\SimilarSearchService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use InvalidArgumentException;

trait ServiceOperation
{
    /**
     * Register routes for ServiceOperation.
     */
    protected function setupServiceRoutes($segment, $routeName, $controller)
    {
        Route::get($segment.'/{id}/service', [
            'as' => $routeName.'.service',
            'uses' => $controller.'@service',
            'operation' => 'service',
        ]);

        Route::post($segment.'/{id}/service/merge', [
            'as' => $routeName.'.service.merge',
            'uses' => $controller.'@serviceMerge',
            'operation' => 'service',
        ]);

        Route::get($segment.'/service/merge-candidates', [
            'as' => $routeName.'.service.merge-candidates',
            'uses' => $controller.'@serviceMergeCandidates',
            'operation' => 'service',
        ]);

        Route::post($segment.'/{id}/service/similar-search', [
            'as' => $routeName.'.service.similar',
            'uses' => $controller.'@serviceSimilarSearch',
            'operation' => 'service',
        ]);
    }

    protected function setupServiceDefaults()
    {
        $this->crud->allowAccess('service');

        $this->crud->operation('service', function () {
            $this->crud->loadDefaultOperationSettingsFromConfig();
        });

        $this->crud->operation('list', function () {
            $this->crud->addButton('line', 'service', 'view', 'crud::buttons.service', 'beginning');
        });
    }

    public function service($id)
    {
        $this->crud->hasAccessOrFail('service');

        $entry = $this->crud->getEntry($id);
        abort_if(! $entry, 404);

        $mergeService = $this->makeMergeService($entry);

        $this->data['crud'] = $this->crud;
        $this->data['entry'] = $entry;
        $this->data['serviceMerge'] = $mergeService->getDefinition();
        $this->data['serviceMergeFields'] = $mergeService->getFields();
        $this->data['serviceDeleteDefault'] = $mergeService->shouldDeleteSourceByDefault();
        $this->data['serviceCandidatesEndpoint'] = $this->getServiceCandidatesEndpoint();
        $this->data['serviceRelations'] = $mergeService->getRelations();
        $this->data['serviceRelationsDefault'] = $mergeService->getRelationDefaults();
        $this->data['serviceEntryCardView'] = $mergeService->getEntryCardView();
        $this->data['serviceResultCardView'] = $mergeService->getResultCardView();
        $this->data['serviceCards'] = $mergeService->getCardViews();

        $serviceSimilar = $mergeService->getSimilarSearchDefinition();
        $this->data['serviceSimilar'] = $serviceSimilar;
        $this->data['serviceSimilarEnabled'] = (bool) ($serviceSimilar['enabled'] ?? false);
        $this->data['serviceSimilarEndpoint'] = $this->getServiceSimilarEndpoint($entry);
        $this->data['serviceSimilarDefaultStrictness'] = $serviceSimilar['strictness']['default'] ?? null;

        $this->data['title'] = $this->crud->getTitle() ?? __('Режим обслуживания');

        return view($this->crud->getServiceView(), $this->data);
    }

    public function serviceMerge(Request $request, $id)
    {
        $this->crud->hasAccessOrFail('service');

        $sourceEntry = $this->crud->getEntry($id);
        abort_if(! $sourceEntry, 404);

        $validated = $request->validate([
            'target_entry_id' => ['required', 'integer'],
            'fields' => ['required', 'array', 'min:1'],
            'fields.*' => ['string'],
            'force' => ['array'],
            'force.*' => ['string'],
            'delete_source' => ['nullable', 'boolean'],
            'relations' => ['array'],
            'relations.*' => ['string'],
            'relation_settings' => ['array'],
            'relation_settings.*' => ['array'],
        ]);

        $targetId = (int) $validated['target_entry_id'];

        if ($targetId === $sourceEntry->getKey()) {
            return back()->withErrors(['target_entry_id' => __('Невозможно объединить запись саму с собой')])->withInput();
        }

        $targetEntry = $this->crud->model->newQuery()->find($targetId);

        if (! $targetEntry) {
            return back()->withErrors(['target_entry_id' => __('Выберите запись для объединения')])->withInput();
        }

        $mergeService = $this->makeMergeService($sourceEntry);
        $fields = array_map('strval', $validated['fields']);
        $forced = array_map('strval', $validated['force'] ?? []);
        $relations = array_map('strval', $validated['relations'] ?? []);
        $relationSettings = $validated['relation_settings'] ?? [];
        $deleteSource = (bool) ($validated['delete_source'] ?? false);

        try {
            $result = $mergeService->mergeInto($targetEntry, $fields, $forced, $deleteSource, $relations, $relationSettings);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['service_merge' => $e->getMessage()])->withInput();
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['service_merge' => __('Не удалось выполнить слияние. Повторите попытку позже.')])->withInput();
        }

        \Alert::success(__('Слияние успешно выполнено.'))->flash();

        return redirect()->to(url($this->crud->route));
    }

    public function serviceMergeCandidates(Request $request)
    {
        $this->crud->hasAccessOrFail('service');

        $sourceId = $request->input('source_id');
        $source = null;

        if ($sourceId) {
            $source = $this->crud->model->newQuery()->find($sourceId);
        }

        $mergeService = $this->makeMergeService($source);

        if ($request->filled('selected')) {
            $selectedIds = Arr::wrap($request->input('selected'));
            $results = $mergeService->resolveCandidatesByIds($selectedIds, $source);

            return response()->json(['results' => $results]);
        }

        $results = $mergeService->searchCandidates($request->input('q'), $source);

        return response()->json(['results' => $results]);
    }

    public function serviceSimilarSearch(Request $request, $id)
    {
        $this->crud->hasAccessOrFail('service');

        $entry = $this->crud->getEntry($id);
        abort_if(! $entry, 404);

        $mergeService = $this->makeMergeService($entry);
        $similarDefinition = $mergeService->getSimilarSearchDefinition();

        if (! ($similarDefinition['enabled'] ?? false)) {
            abort(404);
        }

        $validated = $request->validate([
            'strictness' => ['nullable', 'string'],
            'exclude_children' => ['nullable', 'boolean'],
        ]);

        $similarService = $this->makeSimilarSearchService($mergeService, $entry);

        $payload = [
            'strictness' => $validated['strictness'] ?? null,
        ];

        if (array_key_exists('exclude_children', $validated)) {
            $payload['exclude_children'] = (bool) $validated['exclude_children'];
        }

        $results = $similarService->search($payload);
        $cardView = $similarService->getResultCardView();

        $rendered = $results->map(function (array $item) use ($cardView, $entry) {
            /** @var \Illuminate\Database\Eloquent\Model $model */
            $model = $item['model'];
            $html = view($cardView, [
                'entry' => $model,
                'sourceEntry' => $entry,
                'similarScore' => $item['score'],
                'similarMeta' => $item['meta'],
            ])->render();

            return [
                'id' => $model->getKey(),
                'score' => $item['score'],
                'meta' => $item['meta'],
                'html' => $html,
            ];
        })->values();

        return response()->json([
            'results' => $rendered,
            'meta' => [
                'total' => $rendered->count(),
                'strictness' => $similarService->getLastStrictnessKey(),
                'limit' => $similarDefinition['limit'] ?? 20,
            ],
        ]);
    }

    protected function makeMergeService(?Model $sourceEntry = null): MergeService
    {
        return new MergeService($this->crud, $sourceEntry);
    }

    protected function makeSimilarSearchService(MergeService $mergeService, Model $entry): SimilarSearchService
    {
        return new SimilarSearchService($this->crud, $entry, $mergeService->getSimilarSearchDefinition(), $mergeService->getCardViews());
    }

    protected function getServiceCandidatesEndpoint(): string
    {
        return url($this->crud->route.'/service/merge-candidates');
    }

    protected function getServiceSimilarEndpoint(Model $entry): string
    {
        return url($this->crud->route.'/'.$entry->getKey().'/service/similar-search');
    }
}
