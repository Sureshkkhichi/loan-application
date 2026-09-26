<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function login()
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $user = Auth::user();

            if (!in_array($user->role, ['sales_executive', 'manager', 'admin'])) {
                Auth::logout();
                $this->addError('email', 'Access restricted to authorized staff only.');
                return;
            }

            session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        $this->addError('email', 'Invalid work email address or password.');
    }

    public function render()
    {
        return view('livewire.auth.login')
            ->layout('layouts.app', ['title' => 'Sign In - LoanDesk Operations']);
    }
}
