<?php

namespace App\Livewire;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class RunMigrations extends Component
{
    use AuthorizesRequests;

    public ?string $output = null;

    public function mount(): void
    {
        $this->authorize('runMigrations');
    }

    public function run(): void
    {
        $this->authorize('runMigrations');

        Artisan::call('migrate', ['--force' => true]);

        $this->output = Artisan::output();

        session()->flash('success', 'Migrations ran successfully.');
    }

    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
    {
        return view('livewire.run-migrations');
    }
}
