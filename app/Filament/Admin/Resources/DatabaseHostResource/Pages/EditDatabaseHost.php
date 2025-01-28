<?php

namespace App\Filament\Admin\Resources\DatabaseHostResource\Pages;

use App\Filament\Admin\Resources\DatabaseHostResource;
use App\Filament\Admin\Resources\DatabaseHostResource\RelationManagers\DatabasesRelationManager;
use App\Models\DatabaseHost;
use App\Services\Databases\Hosts\HostUpdateService;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use PDOException;

class EditDatabaseHost extends EditRecord
{
    protected static string $resource = DatabaseHostResource::class;

    private HostUpdateService $hostUpdateService;

    public function boot(HostUpdateService $hostUpdateService): void
    {
        $this->hostUpdateService = $hostUpdateService;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->columns([
                        'default' => 2,
                        'sm' => 3,
                        'md' => 3,
                        'lg' => 4,
                    ])
                    ->schema([
                        TextInput::make('host')
                            ->columnSpan(2)
                            ->label(trans('admin/databasehost.edit.host'))
                            ->helperText(trans('admin/databasehost.edit.host_help'))
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('name', $state))
                            ->maxLength(255),
                        TextInput::make('port')
                            ->columnSpan(1)
                            ->label(trans('admin/databasehost.edit.port'))
                            ->helperText(trans('admin/databasehost.edit.port_help'))
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(65535),
                        TextInput::make('max_databases')
                            ->label(trans('admin/databasehost.edit.max_database', ['databases' => 'Databases']))
                            ->helpertext(trans('admin/databasehost.edit.max_databases_help'))
                            ->numeric(),
                        TextInput::make('name')
                            ->label(trans('admin/databasehost.edit.display_name'))
                            ->helperText(trans('admin/databasehost.edit.display_name_help'))
                            ->required()
                            ->maxLength(60),
                        TextInput::make('username')
                            ->label(trans('admin/databasehost.edit.username'))
                            ->helperText(trans('admin/databasehost.edit.username_help'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('password')
                            ->label(trans('admin/databasehost.edit.password'))
                            ->helperText(trans('admin/databasehost.edit.password_help'))
                            ->password()
                            ->revealable()
                            ->maxLength(255),
                        Select::make('nodes')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText(trans('admin/databasehost.edit.linked_nodes_help', ['databasehost' => 'Database Host', 'database' => 'Database', 'server' => 'Server', 'node' => 'Node']))
                            ->label(trans('admin/databasehost.edit.linked_nodes', ['nodes' => 'Nodes']))
                            ->relationship('nodes', 'name'),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label(fn (DatabaseHost $databaseHost) => $databaseHost->databases()->count() > 0 ? 'Database Host Has Databases' : trans('filament-actions::delete.single.modal.actions.delete.label'))
                ->disabled(fn (DatabaseHost $databaseHost) => $databaseHost->databases()->count() > 0),
            $this->getSaveFormAction()->formId('form'),
        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }

    public function getRelationManagers(): array
    {
        if (DatabasesRelationManager::canViewForRecord($this->getRecord(), static::class)) {
            return [
                DatabasesRelationManager::class,
            ];
        }

        return [];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if (!$record instanceof DatabaseHost) {
            return $record;
        }

        try {
            return $this->hostUpdateService->handle($record, $data);
        } catch (PDOException $exception) {
            Notification::make()
                ->title(trans('admin/databasehost.edit.connection_error', ['databasehost' => 'Database Host']))
                ->body($exception->getMessage())
                ->color('danger')
                ->icon('tabler-database')
                ->danger()
                ->send();

            throw new Halt();
        }
    }
}
