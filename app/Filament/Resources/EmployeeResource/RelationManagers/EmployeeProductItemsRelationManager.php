<?php

namespace App\Filament\Resources\EmployeeResource\RelationManagers;

use App\Enums\ProductTypeEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class EmployeeProductItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'employeeProductItems';

    protected static ?string $title = 'Produits Attribués';

    protected static ?string $label = 'Produits Attribués';

    protected static ?string $icon = 'heroicon-o-list-bullet';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produit'),
                Tables\Columns\TextColumn::make('product_type_id')
                    ->label('Type de produit'),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Quantité'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Statut')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->visible(fn () => auth()->user()->hasRole('super_admin'))
                    ->label('Créé par')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->date('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('updatedBy.name')
                    ->label('Modifié par')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->getStateUsing(fn ($record) => $record->updated_by ? $record->updated_at : null)
                    ->label('Modifié le')
                    ->date('d/m/Y H:i')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_type_id')
                    ->label('Type de produit')
                    ->options(ProductTypeEnum::class),
            ])
            ->headerActions([
                /*Tables\Actions\CreateAction::make()
                    ->visible(fn () => $this->getOwnerRecord()->status == EmployeeStatusEnum::WORKING)*/
            ])
            ->actions([
                /*Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),*/
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    protected function canCreate(): bool
    {
        return false;
    }

    protected function canDelete(Model $record): bool
    {
        return false;
    }

    protected function canEdit(Model $record): bool
    {
        return false;
    }
}
