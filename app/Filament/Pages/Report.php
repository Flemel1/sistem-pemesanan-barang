<?php

namespace App\Filament\Pages;

use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\DB;

class Report extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.report';

    protected static ?string $navigationLabel = 'Laporan Penjualan';

    protected static ?string $title = 'Laporan Penjualan';

    protected static bool $shouldRegisterNavigation = false;

    public array $data = [];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('start_date')
                    ->required(),
                DatePicker::make('end_date')
                    ->required()
            ])
            ->columns(2)
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('generate_report')
                ->label('Cetak Laporan')
                ->submit('generate_report'),
        ];
    }

    public function generateReport()
    {
        $data = $this->form->getState();
        $startDate = $data['start_date'];
        $endDate = $data['end_date'];

        return redirect()->route('generate-report', ['start_date' => $startDate, 'end_date' => $endDate]);
    }
}
