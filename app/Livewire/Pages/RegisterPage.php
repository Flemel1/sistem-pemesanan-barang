<?php

namespace App\Livewire\Pages;

use App\Models\Customer;
use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\SimplePage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterPage extends SimplePage
{
    use InteractsWithFormActions;
    use WithRateLimiting;

    protected static string $view = 'livewire.pages.register-page';

    public ?array $data = [];


    public function mount(): void
    {
        // if (auth()->check()) {
        //     redirect()->intended(Filament::getUrl());
        // }

        // $this->form->fill();
    }

    public function register()
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/register.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->body(array_key_exists('body', __('filament-panels::pages/auth/register.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/register.notifications.throttled.body', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]) : null)
                ->danger()
                ->send();

            return null;
        }

        $data = $this->form->getState();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $customer = new Customer();
        $customer->customer_name = $data['name'];
        $customer->customer_address = $data['customer_address'];
        $customer->postal_code = $data['postal_code'];
        $customer->customer_gender = $data['customer_gender'];
        $customer->user_id = $user->id;
        $customer->save();

        auth()->login($user);

        session()->regenerate();

        return redirect()->intended();
    }

    /**
     * @return array<Action | ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getRegisterFormAction(),
        ];
    }

    public function getRegisterFormAction(): Action
    {
        return Action::make('register')
            ->label('Daftar')
            ->submit('register');
    }

    public function loginAction(): Action
    {
        return Action::make('login')
            ->link()
            ->label('Sudah Memiliki Akun? Masuk Disini')
            ->url(route('login'));
    }

    public function form(Form $form): Form
    {
        return $form;
    }

    /**
     * @return array<int | string, string | Form>
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNameFormComponent(),
                        Textarea::make('customer_address')
                            ->rows(3)
                            ->required(),
                        TextInput::make('postal_code')
                            ->numeric()
                            ->required(),
                        Select::make('customer_gender')
                            ->options([
                                'male' => 'Laki-laki',
                                'female' => 'Perempuan'
                            ])
                            ->required(),
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent()
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('filament-panels::pages/auth/register.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('filament-panels::pages/auth/register.form.email.label'))
            ->email()
            ->required()
            ->maxLength(255)
            ->unique(table: User::class);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('filament-panels::pages/auth/register.form.password.label'))
            ->password()
            ->required()
            ->rule(Password::default())
            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
            ->same('passwordConfirmation')
            ->validationAttribute(__('filament-panels::pages/auth/register.form.password.validation_attribute'));
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label(__('filament-panels::pages/auth/register.form.password_confirmation.label'))
            ->password()
            ->required()
            ->dehydrated(false);
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }
}
