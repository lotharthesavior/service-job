<?php

namespace App\Filament\Resources;

use App\Enums\PaymentMethod;
use App\Enums\ServiceJobStatus;
use App\Filament\Resources\ServiceJobResource\Pages;
use App\Filament\Resources\ServiceJobResource\RelationManagers;
use App\Models\ServiceJob;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceJobResource extends Resource
{
    protected static ?string $model = ServiceJob::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options(ServiceJobStatus::getOptions()),
                Forms\Components\Repeater::make('charges')
                    ->relationship()
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('description')
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->required(),
                    ])
                    ->default(null),
                Forms\Components\Repeater::make('payments')
                    ->relationship()
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('amount')
                            ->required(),
                        Forms\Components\Select::make('method')
                            ->options(PaymentMethod::getOptions())
                            ->required(),
                        Forms\Components\DateTimePicker::make('payed_at')
                            ->required(),
                    ])
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable()
                    ->sortable()
                    ->default('-'),
                Tables\Columns\TextColumn::make('payments')
                    ->formatStateUsing(function (ServiceJob $record) {
                        return '$ ' . $record->payments->sum('amount') . ' (of $ ' . $record->charges->sum('amount') . ')';
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(ServiceJobStatus::getOptions())
                    ->default(null),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceJobs::route('/'),
            'create' => Pages\CreateServiceJob::route('/create'),
            'view' => Pages\ViewServiceJob::route('/{record}'),
            'edit' => Pages\EditServiceJob::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
