<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Services\AppSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Internal Dev Status page for platform admins.
 *
 * Provides a simple dashboard to track features, tests, and todos
 * for internal development and QA purposes.
 */
class DevStatus extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'Dev & QA';

    protected static ?int $navigationSort = 50;

    protected static string $view = 'filament.pages.dev-status';

    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return 'Dev Status';
    }

    public function getTitle(): string
    {
        return 'Dev Status';
    }

    public static function getSlug(): string
    {
        return 'dev-status';
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && $user->isPlatformAdmin();
    }

    public function mount(): void
    {
        $summary = AppSettings::get('dev_status.summary', '');
        $features = AppSettings::get('dev_status.features', []);
        $tests = AppSettings::get('dev_status.tests', []);
        $todos = AppSettings::get('dev_status.todos', []);

        $this->form->fill([
            'summary' => is_string($summary) ? $summary : '',
            'features' => is_array($features) ? $features : [],
            'tests' => is_array($tests) ? $tests : [],
            'todos' => is_array($todos) ? $todos : [],
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('editFeaturesAsText')
                ->label('Edit Features as Text')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->modalHeading('Edit Features as Text')
                ->modalDescription('Format: [Feature] blocks with Name:, Area:, Status:, Notes: fields. Blocks separated by blank lines.')
                ->modalSubmitActionLabel('Save & Import')
                ->modalWidth('4xl')
                ->form([
                    Textarea::make('text')
                        ->label('Features (Plain Text)')
                        ->rows(20)
                        ->helperText('Each feature starts with [Feature], then Key: Value lines. Status: planned, in_progress, ready_for_tests, tested_ok, blocked')
                        ->extraAttributes(['class' => 'font-mono text-sm']),
                ])
                ->fillForm(fn (): array => [
                    'text' => $this->exportFeaturesToText(),
                ])
                ->action(function (array $data): void {
                    $text = $data['text'] ?? '';

                    try {
                        $parsed = $this->importFeaturesFromText($text);
                        $this->data['features'] = $parsed;

                        AppSettings::set(
                            'dev_status.features',
                            $parsed,
                            'dev_status',
                            'Features & modules status'
                        );

                        Notification::make()
                            ->title('Features imported')
                            ->body('Imported ' . count($parsed) . ' feature(s) from text.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Import failed')
                            ->body('Could not parse input: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->extraModalFooterActions([
                    Action::make('copyToClipboard')
                        ->label('Copy to Clipboard')
                        ->color('gray')
                        ->extraAttributes([
                            'x-on:click' => "
                                const ta = \$el.closest('[role=dialog]').querySelector('textarea');
                                if (ta) {
                                    navigator.clipboard.writeText(ta.value);
                                    \$tooltip('Copied!', { timeout: 1500 });
                                }
                            ",
                        ]),
                ]),

            Action::make('editTestsAsText')
                ->label('Edit Tests as Text')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->modalHeading('Edit Test Suites as Text')
                ->modalDescription('Format: [TestSuite] blocks with Name:, Scope:, LastResult:, LastRunAt:, Check: fields.')
                ->modalSubmitActionLabel('Save & Import')
                ->modalWidth('4xl')
                ->form([
                    Textarea::make('text')
                        ->label('Test Suites (Plain Text)')
                        ->rows(20)
                        ->helperText('Each suite starts with [TestSuite]. LastResult: unknown, pass, fail. Check: lines become nested checks.')
                        ->extraAttributes(['class' => 'font-mono text-sm']),
                ])
                ->fillForm(fn (): array => [
                    'text' => $this->exportTestsToText(),
                ])
                ->action(function (array $data): void {
                    $text = $data['text'] ?? '';

                    try {
                        $parsed = $this->importTestsFromText($text);
                        $this->data['tests'] = $parsed;

                        AppSettings::set(
                            'dev_status.tests',
                            $parsed,
                            'dev_status',
                            'Test suites & checks'
                        );

                        Notification::make()
                            ->title('Test suites imported')
                            ->body('Imported ' . count($parsed) . ' test suite(s) from text.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Import failed')
                            ->body('Could not parse input: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->extraModalFooterActions([
                    Action::make('copyToClipboard')
                        ->label('Copy to Clipboard')
                        ->color('gray')
                        ->extraAttributes([
                            'x-on:click' => "
                                const ta = \$el.closest('[role=dialog]').querySelector('textarea');
                                if (ta) {
                                    navigator.clipboard.writeText(ta.value);
                                    \$tooltip('Copied!', { timeout: 1500 });
                                }
                            ",
                        ]),
                ]),

            Action::make('editTodosAsText')
                ->label('Edit Todos as Text')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->modalHeading('Edit Todos as Text')
                ->modalDescription('Format: [Todo] blocks with Title:, Type:, Status:, Priority:, Notes: fields.')
                ->modalSubmitActionLabel('Save & Import')
                ->modalWidth('4xl')
                ->form([
                    Textarea::make('text')
                        ->label('Todos (Plain Text)')
                        ->rows(20)
                        ->helperText('Each todo starts with [Todo]. Type: feature, bug, refactor, cleanup, other. Status: open, in_progress, done. Priority: low, normal, high.')
                        ->extraAttributes(['class' => 'font-mono text-sm']),
                ])
                ->fillForm(fn (): array => [
                    'text' => $this->exportTodosToText(),
                ])
                ->action(function (array $data): void {
                    $text = $data['text'] ?? '';

                    try {
                        $parsed = $this->importTodosFromText($text);
                        $this->data['todos'] = $parsed;

                        AppSettings::set(
                            'dev_status.todos',
                            $parsed,
                            'dev_status',
                            'Short todo list for dev/QA'
                        );

                        Notification::make()
                            ->title('Todos imported')
                            ->body('Imported ' . count($parsed) . ' todo(s) from text.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Import failed')
                            ->body('Could not parse input: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->extraModalFooterActions([
                    Action::make('copyToClipboard')
                        ->label('Copy to Clipboard')
                        ->color('gray')
                        ->extraAttributes([
                            'x-on:click' => "
                                const ta = \$el.closest('[role=dialog]').querySelector('textarea');
                                if (ta) {
                                    navigator.clipboard.writeText(ta.value);
                                    \$tooltip('Copied!', { timeout: 1500 });
                                }
                            ",
                        ]),
                ]),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Summary')
                    ->description('High-level notes about current development phase and priorities.')
                    ->schema([
                        Textarea::make('summary')
                            ->label('Global notes / context')
                            ->rows(4)
                            ->helperText('Short, high-level notes about current phase, priorities, or important decisions.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Features & Modules')
                    ->description('Track status of features and modules being developed.')
                    ->schema([
                        Repeater::make('features')
                            ->label('Features / Modules')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Name')
                                    ->required(),
                                TextInput::make('area')
                                    ->label('Area / Module')
                                    ->placeholder('e.g. Affiliates, Bookings, Directory'),
                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'planned' => 'Planned',
                                        'in_progress' => 'In Progress',
                                        'ready_for_tests' => 'Ready for Tests',
                                        'tested_ok' => 'Tested OK',
                                        'blocked' => 'Blocked',
                                    ])
                                    ->required()
                                    ->default('in_progress'),
                                Textarea::make('notes')
                                    ->label('Notes')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->collapsed(false)
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                            ->columnSpanFull(),
                    ]),

                Section::make('Test Suites')
                    ->description('Track test suites and their status.')
                    ->collapsed()
                    ->schema([
                        Repeater::make('tests')
                            ->label('Test Suites')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Name')
                                    ->required(),
                                Textarea::make('scope')
                                    ->label('Scope')
                                    ->rows(2),
                                DateTimePicker::make('last_run_at')
                                    ->label('Last run at')
                                    ->seconds(false),
                                Select::make('last_result')
                                    ->label('Last result')
                                    ->options([
                                        'unknown' => 'Unknown',
                                        'pass' => 'Pass',
                                        'fail' => 'Fail',
                                    ])
                                    ->default('unknown'),
                                Repeater::make('checks')
                                    ->label('Checks')
                                    ->schema([
                                        TextInput::make('label')
                                            ->label('Label')
                                            ->required(),
                                        Checkbox::make('done')
                                            ->label('Done'),
                                    ])
                                    ->collapsed()
                                    ->columns(2)
                                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->collapsed()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                            ->columnSpanFull(),
                    ]),

                Section::make('Todos')
                    ->description('Short todo list for development and QA tasks.')
                    ->collapsed()
                    ->schema([
                        Repeater::make('todos')
                            ->label('Todo Items')
                            ->schema([
                                TextInput::make('title')
                                    ->label('Title')
                                    ->required(),
                                Select::make('type')
                                    ->label('Type')
                                    ->options([
                                        'feature' => 'Feature',
                                        'bug' => 'Bug',
                                        'refactor' => 'Refactor',
                                        'cleanup' => 'Cleanup',
                                        'other' => 'Other',
                                    ])
                                    ->default('feature'),
                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'open' => 'Open',
                                        'in_progress' => 'In Progress',
                                        'done' => 'Done',
                                    ])
                                    ->default('open'),
                                Select::make('priority')
                                    ->label('Priority')
                                    ->options([
                                        'low' => 'Low',
                                        'normal' => 'Normal',
                                        'high' => 'High',
                                    ])
                                    ->default('normal'),
                                Textarea::make('notes')
                                    ->label('Notes')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->collapsed()
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        AppSettings::set(
            'dev_status.summary',
            $data['summary'] ?? '',
            'dev_status',
            'Global Dev & QA status summary'
        );

        AppSettings::set(
            'dev_status.features',
            $data['features'] ?? [],
            'dev_status',
            'Features & modules status'
        );

        AppSettings::set(
            'dev_status.tests',
            $data['tests'] ?? [],
            'dev_status',
            'Test suites & checks'
        );

        AppSettings::set(
            'dev_status.todos',
            $data['todos'] ?? [],
            'dev_status',
            'Short todo list for dev/QA'
        );

        Notification::make()
            ->title('Dev status updated')
            ->body('Your development status has been saved.')
            ->success()
            ->send();
    }

    // =========================================================================
    // EXPORT METHODS
    // =========================================================================

    private function exportFeaturesToText(): string
    {
        $features = $this->data['features'] ?? AppSettings::get('dev_status.features', []);

        if (empty($features)) {
            return '';
        }

        $lines = [];

        foreach ($features as $feature) {
            $lines[] = '[Feature]';
            $lines[] = 'Name: ' . ($feature['name'] ?? '');

            if (! empty($feature['area'])) {
                $lines[] = 'Area: ' . $feature['area'];
            }

            $lines[] = 'Status: ' . ($feature['status'] ?? 'in_progress');

            if (! empty($feature['notes'])) {
                foreach (explode("\n", $feature['notes']) as $noteLine) {
                    $lines[] = 'Notes: ' . $noteLine;
                }
            }

            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    private function exportTestsToText(): string
    {
        $tests = $this->data['tests'] ?? AppSettings::get('dev_status.tests', []);

        if (empty($tests)) {
            return '';
        }

        $lines = [];

        foreach ($tests as $test) {
            $lines[] = '[TestSuite]';
            $lines[] = 'Name: ' . ($test['name'] ?? '');

            if (! empty($test['scope'])) {
                $lines[] = 'Scope: ' . $test['scope'];
            }

            $lines[] = 'LastResult: ' . ($test['last_result'] ?? 'unknown');

            if (! empty($test['last_run_at'])) {
                $lines[] = 'LastRunAt: ' . $test['last_run_at'];
            }

            if (! empty($test['checks']) && is_array($test['checks'])) {
                foreach ($test['checks'] as $check) {
                    $done = ! empty($check['done']) ? ' [x]' : '';
                    $lines[] = 'Check: ' . ($check['label'] ?? '') . $done;
                }
            }

            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    private function exportTodosToText(): string
    {
        $todos = $this->data['todos'] ?? AppSettings::get('dev_status.todos', []);

        if (empty($todos)) {
            return '';
        }

        $lines = [];

        foreach ($todos as $todo) {
            $lines[] = '[Todo]';
            $lines[] = 'Title: ' . ($todo['title'] ?? '');
            $lines[] = 'Type: ' . ($todo['type'] ?? 'feature');
            $lines[] = 'Status: ' . ($todo['status'] ?? 'open');
            $lines[] = 'Priority: ' . ($todo['priority'] ?? 'normal');

            if (! empty($todo['notes'])) {
                foreach (explode("\n", $todo['notes']) as $noteLine) {
                    $lines[] = 'Notes: ' . $noteLine;
                }
            }

            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    // =========================================================================
    // IMPORT METHODS
    // =========================================================================

    private function importFeaturesFromText(string $text): array
    {
        $blocks = $this->parseBlocks($text, 'Feature');
        $features = [];

        foreach ($blocks as $block) {
            $feature = [
                'name' => $block['Name'] ?? '',
                'area' => $block['Area'] ?? '',
                'status' => $this->normalizeFeatureStatus($block['Status'] ?? 'in_progress'),
                'notes' => $this->combineMultipleValues($block, 'Notes'),
            ];

            if (! empty($feature['name'])) {
                $features[] = $feature;
            }
        }

        return $features;
    }

    private function importTestsFromText(string $text): array
    {
        $blocks = $this->parseBlocks($text, 'TestSuite');
        $tests = [];

        foreach ($blocks as $block) {
            $checks = [];

            if (isset($block['Check'])) {
                $checkValues = is_array($block['Check']) ? $block['Check'] : [$block['Check']];

                foreach ($checkValues as $checkLine) {
                    $done = false;

                    if (str_contains($checkLine, '[x]')) {
                        $done = true;
                        $checkLine = str_replace('[x]', '', $checkLine);
                    }

                    $checkLine = trim($checkLine);

                    if (! empty($checkLine)) {
                        $checks[] = [
                            'label' => $checkLine,
                            'done' => $done,
                        ];
                    }
                }
            }

            $test = [
                'name' => $block['Name'] ?? '',
                'scope' => $block['Scope'] ?? '',
                'last_result' => $this->normalizeTestResult($block['LastResult'] ?? 'unknown'),
                'last_run_at' => $block['LastRunAt'] ?? null,
                'checks' => $checks,
            ];

            if (! empty($test['name'])) {
                $tests[] = $test;
            }
        }

        return $tests;
    }

    private function importTodosFromText(string $text): array
    {
        $blocks = $this->parseBlocks($text, 'Todo');
        $todos = [];

        foreach ($blocks as $block) {
            $todo = [
                'title' => $block['Title'] ?? '',
                'type' => $this->normalizeTodoType($block['Type'] ?? 'feature'),
                'status' => $this->normalizeTodoStatus($block['Status'] ?? 'open'),
                'priority' => $this->normalizeTodoPriority($block['Priority'] ?? 'normal'),
                'notes' => $this->combineMultipleValues($block, 'Notes'),
            ];

            if (! empty($todo['title'])) {
                $todos[] = $todo;
            }
        }

        return $todos;
    }

    // =========================================================================
    // PARSING HELPERS
    // =========================================================================

    /**
     * Parse text into blocks based on a marker like [Feature], [TestSuite], [Todo].
     *
     * @return array<int, array<string, string|array>>
     */
    private function parseBlocks(string $text, string $blockType): array
    {
        $blocks = [];
        $marker = '[' . $blockType . ']';
        $lines = preg_split('/\r\n|\r|\n/', $text);
        $currentBlock = null;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === $marker) {
                if ($currentBlock !== null) {
                    $blocks[] = $currentBlock;
                }

                $currentBlock = [];

                continue;
            }

            if ($currentBlock === null) {
                continue;
            }

            if (empty($trimmed)) {
                continue;
            }

            if (preg_match('/^([A-Za-z]+):\s*(.*)$/', $trimmed, $matches)) {
                $key = $matches[1];
                $value = $matches[2];

                if (isset($currentBlock[$key])) {
                    if (! is_array($currentBlock[$key])) {
                        $currentBlock[$key] = [$currentBlock[$key]];
                    }

                    $currentBlock[$key][] = $value;
                } else {
                    $currentBlock[$key] = $value;
                }
            }
        }

        if ($currentBlock !== null) {
            $blocks[] = $currentBlock;
        }

        return $blocks;
    }

    /**
     * Combine multiple values for a key (like Notes:) into a single string.
     */
    private function combineMultipleValues(array $block, string $key): string
    {
        if (! isset($block[$key])) {
            return '';
        }

        $values = is_array($block[$key]) ? $block[$key] : [$block[$key]];

        return implode("\n", $values);
    }

    private function normalizeFeatureStatus(string $status): string
    {
        $status = strtolower(trim($status));

        $map = [
            'planned' => 'planned',
            'in_progress' => 'in_progress',
            'inprogress' => 'in_progress',
            'in progress' => 'in_progress',
            'ready_for_tests' => 'ready_for_tests',
            'readyfortests' => 'ready_for_tests',
            'ready for tests' => 'ready_for_tests',
            'tested_ok' => 'tested_ok',
            'testedok' => 'tested_ok',
            'tested ok' => 'tested_ok',
            'tested' => 'tested_ok',
            'blocked' => 'blocked',
        ];

        return $map[$status] ?? 'in_progress';
    }

    private function normalizeTestResult(string $result): string
    {
        $result = strtolower(trim($result));

        $map = [
            'unknown' => 'unknown',
            'pass' => 'pass',
            'passed' => 'pass',
            'fail' => 'fail',
            'failed' => 'fail',
        ];

        return $map[$result] ?? 'unknown';
    }

    private function normalizeTodoType(string $type): string
    {
        $type = strtolower(trim($type));

        $map = [
            'feature' => 'feature',
            'bug' => 'bug',
            'refactor' => 'refactor',
            'cleanup' => 'cleanup',
            'other' => 'other',
            'qa' => 'other',
        ];

        return $map[$type] ?? 'feature';
    }

    private function normalizeTodoStatus(string $status): string
    {
        $status = strtolower(trim($status));

        $map = [
            'open' => 'open',
            'in_progress' => 'in_progress',
            'inprogress' => 'in_progress',
            'in progress' => 'in_progress',
            'done' => 'done',
            'completed' => 'done',
            'planned' => 'open',
            'blocked' => 'open',
        ];

        return $map[$status] ?? 'open';
    }

    private function normalizeTodoPriority(string $priority): string
    {
        $priority = strtolower(trim($priority));

        $map = [
            'low' => 'low',
            'normal' => 'normal',
            'medium' => 'normal',
            'high' => 'high',
            'urgent' => 'high',
        ];

        return $map[$priority] ?? 'normal';
    }
}
