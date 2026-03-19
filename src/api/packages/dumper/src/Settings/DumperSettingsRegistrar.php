<?php

namespace ParabellumKoval\Dumper\Settings;

use Backpack\Settings\Contracts\SettingsRegistrarInterface;
use Backpack\Settings\Services\Registry\Field;
use Backpack\Settings\Services\Registry\Registry;
use ParabellumKoval\Dumper\Services\DumpManager;
use ParabellumKoval\Dumper\Support\AutoDumpSchedulePresets;
use Throwable;

class DumperSettingsRegistrar implements SettingsRegistrarInterface
{
    public function register(Registry $registry): void
    {
        $registry->group('dumper', function ($group) {
            $group->title('Резервные копии')->icon('la la-database')
                ->page('Общее', function ($page) {
                    $page->add(Field::make('dumper.operations_link', 'custom_html')
                        ->label('Операции')
                        ->default('<a class="btn btn-outline-primary" href="' . e(backpack_url('dumper')) . '">Открыть страницу дампов</a>')
                        ->tab('Сервис')
                    );

                    $page->add(Field::make('dumper.remote.enabled', 'checkbox')
                        ->label('Включить отправку дампов в облако')
                        ->default((bool) config('dumper.remote.enabled', false))
                        ->cast('bool')
                        ->tab('Облако')
                    );

                    $page->add(Field::make('dumper.remote.enabled_providers', 'select2_from_array')
                        ->label('Активные провайдеры')
                        ->options($this->providerOptions())
                        ->default((array) config('dumper.remote.enabled_providers', []))
                        ->allows_multiple(true)
                        ->cast('array')
                        ->tab('Облако')
                    );

                    $page->add(Field::make('dumper.manual.sync_to_remote', 'checkbox')
                        ->label('Отправлять ручные дампы в облако')
                        ->default((bool) config('dumper.manual.sync_to_remote', false))
                        ->cast('bool')
                        ->tab('Облако')
                    );
                })
                ->page('Автодампы', function ($page) {
                    $page->add(Field::make('dumper.auto.cases_styles', 'custom_html')
                        ->default($this->autoCasesStyles())
                        ->wrapper(['class' => 'hidden'])
                    );

                    $page->add(Field::make('dumper.auto.cases_customized', 'hidden')
                        ->default('1')
                        ->cast('bool')
                    );

                    $page->add(Field::make('dumper.auto.cases', 'repeatable_conditional')
                        ->label('Сценарии автодампов')
                        ->cast('array')
                        ->default($this->autoCaseRows())
                        ->initRows(0)
                        ->newItemLabel('Добавить сценарий')
                        ->wrapper(['class' => 'form-group col-sm-12 dumper-auto-cases'])
                        ->fields([
                            [
                                'name' => 'key',
                                'type' => 'hidden',
                            ],
                            [
                                'name' => 'label',
                                'type' => 'text',
                                'label' => 'Название сценария',
                                'wrapper' => ['class' => 'form-group col-lg-5 col-md-12'],
                            ],
                            [
                                'name' => 'schedule',
                                'type' => 'select_from_array',
                                'label' => 'Периодичность',
                                'options' => $this->scheduleOptions(),
                                'wrapper' => ['class' => 'form-group col-lg-4 col-md-8'],
                            ],
                            [
                                'name' => 'sync_to_remote',
                                'type' => 'checkbox',
                                'label' => 'Отправлять в облако',
                                'wrapper' => ['class' => 'form-group col-lg-3 col-md-4 dumper-auto-cases__toggle'],
                            ],
                            [
                                'name' => 'tables',
                                'type' => 'select2_from_array',
                                'label' => 'Таблицы',
                                'options' => $this->tableOptions(),
                                'allows_multiple' => true,
                                'wrapper' => ['class' => 'form-group col-md-12'],
                                'hint' => 'Выберите "Вся база", чтобы выгружать все таблицы.',
                            ],
                            [
                                'name' => 'retention_keep_last',
                                'type' => 'number',
                                'label' => 'Хранить последних файлов',
                                'attributes' => ['min' => 0, 'step' => 1],
                                'wrapper' => ['class' => 'form-group col-lg-3 col-md-6'],
                                'hint' => '0 = без ограничения.',
                            ],
                            [
                                'name' => 'retention_keep_days',
                                'type' => 'number',
                                'label' => 'Удалять старше N дней',
                                'attributes' => ['min' => 0, 'step' => 1],
                                'wrapper' => ['class' => 'form-group col-lg-3 col-md-6'],
                                'hint' => '0 = не удалять по возрасту.',
                            ],
                            [
                                'name' => 'description',
                                'type' => 'textarea',
                                'label' => 'Описание',
                                'attributes' => ['rows' => 2],
                                'wrapper' => ['class' => 'form-group col-md-12'],
                            ],
                        ])
                    );
                });
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function autoCaseRows(): array
    {
        $rows = [];

        foreach (app(DumpManager::class)->autoCases() as $case) {
            $tables = $case['tables'] ?? '*';
            $tables = $tables === '*' ? ['__all__'] : array_values((array) $tables);

            $rows[] = [
                'key' => $case['key'],
                'label' => (string) ($case['label'] ?? ''),
                'schedule' => $case['schedule'] ?? AutoDumpSchedulePresets::keyForCron($case['cron'] ?? null),
                'sync_to_remote' => !empty($case['sync_to_remote']) ? '1' : '0',
                'tables[]' => $tables,
                'retention_keep_last' => isset($case['retention']['keep_last']) ? (string) $case['retention']['keep_last'] : '0',
                'retention_keep_days' => isset($case['retention']['keep_days']) ? (string) $case['retention']['keep_days'] : '0',
                'description' => (string) ($case['description'] ?? ''),
            ];
        }

        return $rows;
    }

    /**
     * @return array<string, string>
     */
    protected function providerOptions(): array
    {
        return [
            'bunny' => 'BunnyCDN',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function scheduleOptions(): array
    {
        return AutoDumpSchedulePresets::options();
    }

    protected function autoCasesStyles(): string
    {
        return <<<'HTML'
<style>
    .dumper-auto-cases .repeatable-container > label {
        display: block;
        margin-bottom: 0.5rem;
    }

    .dumper-auto-cases .repeatable-element {
        position: relative;
        padding: 1.25rem 1.25rem 1rem 1.25rem;
        margin: 0 0 1rem 0;
        border: 1px solid #d9e2f0;
        border-radius: 14px;
        background: linear-gradient(180deg, #fcfdff 0%, #f7faff 100%);
        box-shadow: 0 8px 24px rgba(31, 57, 104, 0.06);
    }

    .dumper-auto-cases .container-repeatable-elements .controls {
        top: 0.75rem;
        right: 0.75rem;
        left: auto !important;
        flex-direction: row;
        gap: 0.35rem;
    }

    .dumper-auto-cases .container-repeatable-elements .controls .move-element-up,
    .dumper-auto-cases .container-repeatable-elements .controls .move-element-down {
        display: none !important;
    }

    .dumper-auto-cases .container-repeatable-elements .controls .delete-element {
        width: 2rem;
        height: 2rem;
        margin: 0;
        border: 1px solid #f2c9cf;
        background: #fff4f5 !important;
        color: #c03f52;
        box-shadow: none;
    }

    .dumper-auto-cases .repeatable-element .form-group {
        margin-bottom: 0.9rem;
    }

    .dumper-auto-cases .repeatable-element label {
        display: block;
        margin-bottom: 0.35rem;
        color: #405376;
        font-weight: 600;
    }

    .dumper-auto-cases__toggle {
        display: flex;
        align-items: flex-end;
        min-height: 72px;
    }

    .dumper-auto-cases__toggle .checkbox {
        width: 100%;
        margin: 0;
        padding: 0.85rem 0.95rem;
        border: 1px solid #d9e2f0;
        border-radius: 10px;
        background: #fff;
    }

    .dumper-auto-cases__toggle .checkbox label {
        display: inline;
        margin: 0 0 0 0.4rem;
        font-weight: 600;
    }

    .dumper-auto-cases .help-block,
    .dumper-auto-cases .form-text {
        margin-top: 0.3rem;
        font-size: 0.85rem;
        line-height: 1.35;
        color: #7b8ba7;
    }

    .dumper-auto-cases .add-repeatable-element-button {
        margin-left: 0;
        padding: 0.55rem 0.95rem;
        border-radius: 10px;
    }
</style>
HTML;
    }

    /**
     * @return array<string, string>
     */
    protected function tableOptions(): array
    {
        $options = [
            '__all__' => 'Вся база',
        ];

        try {
            foreach (app(DumpManager::class)->tableOptions() as $table) {
                $name = (string) ($table['value'] ?? '');

                if ($name === '') {
                    continue;
                }

                $options[$name] = (string) ($table['text'] ?? $name);
            }
        } catch (Throwable) {
        }

        return $options;
    }
}
