<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Department;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->default(fn()=>'TRX'.mt_rand(10000, 99999)),
                Forms\Components\TextInput::make('payment_status')
                ->readOnly()
                    ->default('pending'),
                Forms\Components\Select::make('user_id')
                    ->required()
                    ->relationship('users','name'),

                Forms\Components\Fieldset::make('Department')
                ->schema([
                    Forms\Components\Select::make('department_id')
                    ->required()
                    ->label('Department Name & Semester')
                    ->options(Department::query()->get()->mapWithKeys(function ($department){
                        return [
                            $department->id => $department->name.' - Semester : '. $department->semester
                        ];
                    })->toArray())
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set){
                        if ($department = Department::find($state)) {
                            $set("department_cost", $department->cost);
                        }else{
                            $set("department_cost", null);
                        }
                    }),
                    Forms\Components\TextInput::make('department_cost')
                    ->label('Cost')
                    ->disabled()
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state){
                        'pending'=>'warning',
                        'success'=>'success',
                        'failed'=>'danger',
                        default=>'secondary'
                    }),
                Tables\Columns\ImageColumn::make('payment_approve')
                    ->label('Bukti Pembayaran')
                    ->width(460)
                    ->height(230),
                Tables\Columns\TextColumn::make('users.name')
                ->label('Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('users.phone')
                ->label('Phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('departments.name')
                    ->label('Department')
                    ->searchable(),
                Tables\Columns\TextColumn::make('departments.semester')
                    ->label('Semester')
                    ->searchable(),
                Tables\Columns\TextColumn::make('departments.cost')
                    ->label('Cost')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
