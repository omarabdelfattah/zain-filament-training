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
use App\Rules\SaudiPhone;

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
                            ->maxLength(50)
                        ,
                        Repeater::make('addresses')
                            ->label('Address Book')
                            ->minItems(1)
                            ->default([
                                [
                                    'country' => 'EG',
                                    'city' => 'Cairo',
                                    'address' => '4th building, 15th St., Nasr City, Cairo, Egypt',
                                    'contact_info' => [
                                        ['email' => 'example@test.com', 'phone' => '01011111111'],
                                        ['email' => 'example2@test.com', 'phone' => '01200000000']
                                    ]
                                ]
                            ])
                            ->live()
                            ->relationship('addresses')
                        ->afterStateUpdated(function (callable $get, callable $set, $state, $livewire) {
                                foreach ($state as $addressIndex => $addressItem) {
                                    $contactInfo = $addressItem['contact_info'] ?? [];
                                    
                                    foreach ($contactInfo as $contactIndex => $contactItem) {
                                        $phonePath = "data.addresses.{$addressIndex}.contact_info.{$contactIndex}.phone";
                                        $livewire->validateOnly($phonePath);
                                    }
                                }
                            })
                            ->schema([

                                Grid::make(2)->schema([
                                    Select::make('country')
                                        ->label('Country')
                                        ->options(
                                            collect(json_decode(file_get_contents(public_path('countries.json')), true))
                                                ->mapWithKeys(fn($val, $key) => [$key => $val['name']])
                                        )
                                        ->required()
                                        ->default('SA')
                                        ->live()
                                        // ->afterStateUpdated(function ($state, callable $get, callable $set, $livewire, $component) {
                                        //         $contactInfo = $get('Contact Info') ?? [];
                                        //         foreach ($contactInfo as $index => $contactItem) {
                                        //             $livewire->validateOnly("Contact Info.{$index}.phone");
                                        //         }
                                        //     })
                                        ,

                                    TextInput::make('city')
                                        ->label('City')
                                        ->required()
                                        ->maxLength(255),

                                ]),

                            Textarea::make('address')
                            ->label('Address')
                            ->rows(4)
                            ->minLength(2)
                            ->maxLength(1024),
                            
                            Repeater::make('contact_info')
                            ->label("Contact Info")
                            ->columns(2)
                            ->relationship('contacts')
                            ->required()
                            ->schema([
                                TextInput::make('email')
                                    ->label(
                                        fn() => 'Email'
                                    )
                                    ->email()
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                        $address = $get('../../address') ?? '';
                                        $set('../../address', trim($address . "\n" . strtoupper($state)));
                                    })
                                ,
    
                                TextInput::make('phone')
                                    ->label(
                                        fn() => 'Phone'
                                    )
                                    ->required()
                                    ->rule(function (callable $get) {
                                        $countryCode = $get('../../country');
                                        if($countryCode){
                                            return new \App\Rules\CountryPhonePrefix($countryCode);
                                        }
                                    })
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        // remove "+" from value 
                                        $state = str_replace('+',"",$state);
                                        $countryKey = $get('../../country');
                                        $prefix = static::getPrefixFromCountry($countryKey);
                                        // if prefix is not written add it 
                                        if ($prefix && !str_starts_with($state, $prefix)) {
                                            $set('phone', $prefix . $state);
                                        }
                                    })
                                ,
                            ])
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

    protected static function getPrefixFromCountry(?string $countryCode): ?string
    {
        if (!$countryCode) return null;

        $countries = json_decode(file_get_contents(public_path('countries.json')), true);

        return $countries[$countryCode]['prefix'] ?? null;
    }
}
