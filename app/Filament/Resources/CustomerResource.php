<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Closure;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('name')
                            ->label('Full name')
                            ->required()
                            ->minLength(2)
                            ->maxLength(50),

                        // Textarea::make('address')
                        //     ->label('Address')
                        //     ->rows(4)
                        //     ->minLength(2)
                        //     ->maxLength(1024),

                        // TODO: refactor this after finishing
                        TextInput::make('email')
                            ->label(
                                fn() => 'Email'
                            )
                            ->email()
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (callable $get,        callable $set, $state) {
                                $address = $get('address') ?? '';
                                $set('address', trim($address . "\n" . strtoupper($state)));
                            })
                        // ->visible(fn ($get) => $get('show_contact'))
                        ,

                        Toggle::make('show_contact')
                            ->label('Has Contact Information')
                            ->onIcon('heroicon-m-bolt')
                            ->offIcon('heroicon-m-user')
                            ->onColor('success')                        
                            ->live(),

                        // This part will only show if toggle is on
                        Repeater::make('addresses')
                        ->minItems(1)
                        ->schema([
                            Grid::make(3)->schema([
                                Select::make('country')
                                    ->label('Country')
                                    ->options(
                                        collect(json_decode(file_get_contents(public_path('countries.json')), true))
                                            ->mapWithKeys(fn($val, $key) => [$key => $val['name']])
                                    )
                                    ->required(),

                                TextInput::make('city')
                                    ->label('City')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('fax')
                                    ->label('Fax')
                                    ->nullable()
                                    ->maxLength(20),
                           ]),

                            Textarea::make('address')
                                ->label('Full address')
                                ->rows(4)
                                ->minLength(2)
                                ->maxLength(1024),

                            Repeater::make('contact_info')
                            ->label('Contact info')
                            ->schema([
                                TextInput::make('phone')
                                    ->label('Phone')
                                    ->live(onBlur: true)
                                    ->required()
                                    ->rules(['regex:/^050[0-9]{8}$/'])
                                    ->validationMessages([
                                        'regex' => 'Phone must be a valid Saudi number starting with 050 and followed by 8 digits.',
                                    ])
                                    ->afterStateUpdated(function ($livewire, $component) {
                                        $livewire->validateOnly($component->getStatePath());
                                    }),
                                TextInput::make('tel')
                                    ->label('Telephone')
                                    ->required(),

                            ])
                            ->columns(2),
                        ])
                        ->rules([
                            function () {
                                return function (string $attribute, $value, Closure $fail) {
                                    if (empty($value) || count($value) === 0) {
                                        $fail('At least one item is required.');
                                    }
                                };
                            },
                        ])
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                ->label('Name')
                ->searchable()
                ->sortable(),

                TextColumn::make('email')
                ->label('Email')
                ->formatStateUsing(fn($state, $record) => $record->show_contact ? $state : '-'),

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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
