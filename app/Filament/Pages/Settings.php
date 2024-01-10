<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Exception;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\DB;
use MatanYadaev\EloquentSpatial\Objects\Point;

class Settings extends Page
{
    use InteractsWithForms;

    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.settings';


    public ?array $data = [];

    public function mount(): void
    {
        $record = Setting::find(1)->first();
        $filled = [
            'order_deliver_fee' => $record->order_deliver_fee,
            'location' => [
                'lng' => $record->location->longitude,
                'lat' =>  $record->location->latitude
            ]
        ];
        $this->form->fill($filled);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Map::make('location')
                    ->clickable()
                    ->defaultZoom(15)
                    ->required(),
                TextInput::make('order_deliver_fee')
                    ->label('Biaya Ongkir')
                    ->required()
                    ->numeric(),
            ])
            ->statePath('data')
            ->model(Setting::class);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->submit('save'),
        ];
    }

    public function save(Setting $setting): void
    {
        try {
            $data = $this->form->getState();
            $location = new Point($data['location']['lat'], $data['location']['lng']);
            $data['location'] = $location;
            DB::beginTransaction();
            $setting = Setting::find(1);
            $setting->order_deliver_fee = $data['order_deliver_fee'];
            $setting->location = $data['location'];
            $setting->save();
            DB::commit();
        } catch (Halt $exception) {
            DB::rollBack();
            return;
        } catch (Exception $ex) {
            DB::rollBack();
            return;
        }
    }
}
