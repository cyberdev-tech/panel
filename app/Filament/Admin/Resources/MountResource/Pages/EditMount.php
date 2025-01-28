<?php

namespace App\Filament\Admin\Resources\MountResource\Pages;

use App\Filament\Admin\Resources\MountResource;
use Filament\Actions;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class EditMount extends EditRecord
{
    protected static string $resource = MountResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    TextInput::make('name')
                        ->label(trans('admin/mount.edit.name'))
                        ->required()
                        ->helperText(trans('admin/mount.edit.name_help', ['mount' => 'mount']))
                        ->maxLength(64),
                    ToggleButtons::make('read_only')
                        ->label(trans('admin/mount.edit.read_only'))
                        ->helperText(trans('admin/mount.edit.read_only_help', ['mount' => 'mount']))
                        ->options([
                            false => trans('admin/mount.edit.toggles.writable'),
                            true => trans('admin/mount.edit.toggles.read_only'),
                        ])
                        ->icons([
                            false => 'tabler-writing',
                            true => 'tabler-writing-off',
                        ])
                        ->colors([
                            false => 'warning',
                            true => 'success',
                        ])
                        ->inline()
                        ->default(false)
                        ->required(),
                    TextInput::make('source')
                        ->label(trans('admin/mount.edit.source'))
                        ->required()
                        ->helperText(trans('admin/mount.edit.source_help', ['mount' => 'mount']))
                        ->maxLength(255),
                    TextInput::make('target')
                        ->label(trans('admin/mount.edit.target'))
                        ->required()
                        ->helperText(trans('admin/mount.edit.target_help', ['mount' => 'mount']))
                        ->maxLength(255),
                    ToggleButtons::make('user_mountable')
                        ->hidden()
                        ->label('User mountable?')
                        ->options([
                            false => 'No',
                            true => 'Yes',
                        ])
                        ->icons([
                            false => 'tabler-user-cancel',
                            true => 'tabler-user-bolt',
                        ])
                        ->colors([
                            false => 'success',
                            true => 'warning',
                        ])
                        ->default(false)
                        ->inline()
                        ->required(),
                    Textarea::make('description')
                        ->label(trans('admin/mount.edit.description'))
                        ->helperText(trans('admin/mount.edit.description_help', ['mount' => 'mount.']))
                        ->columnSpanFull(),
                ])->columnSpan(1)->columns([
                    'default' => 1,
                    'lg' => 2,
                ]),
                Group::make()->schema([
                    Section::make()->schema([
                        Select::make('eggs')->multiple()
                            ->relationship('eggs', 'name')
                            ->preload(),
                        Select::make('nodes')->multiple()
                            ->relationship('nodes', 'name')
                            ->searchable(['name', 'fqdn'])
                            ->preload(),
                    ]),
                ])->columns([
                    'default' => 1,
                    'lg' => 2,
                ]),
            ])->columns([
                'default' => 1,
                'lg' => 2,
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            $this->getSaveFormAction()->formId('form'),
        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }
}
