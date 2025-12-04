<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Services\AppSettings;
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
}
