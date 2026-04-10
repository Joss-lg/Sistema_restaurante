<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * El destino principal ahora es siempre /dashboard.
     */
    protected $redirectTo = '/dashboard';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    protected function validateLogin(Request $request)
    {
        if ($request->has('codigo_empleado')) {
            $request->validate([
                'codigo_empleado' => 'required|string|size:4',
            ]);
        } else {
            $request->validate([
                'correo' => 'required|string|email',
                'contrasena' => 'required|string',
            ]);
        }
    }

    protected function attemptLogin(Request $request)
{
    // Caso 1: Login por PIN (Teclado numérico)
    if ($request->has('codigo_empleado') && !empty($request->codigo_empleado)) {
        $user = User::where('codigo_empleado', $request->codigo_empleado)
                    ->where('esta_activo', true) // Asegúrate de que en la DB sea 'true' o 1
                    ->first();
        
        if ($user) {
            // Iniciamos sesión manualmente
            Auth::login($user, $request->filled('remember'));
            
            // IMPORTANTE: Regenerar la sesión para evitar ataques de fijación
            $request->session()->regenerate();
            
            return true;
        }
        return false;
    }

    // Caso 2: Login tradicional (Correo y Contrasena para el Admin)
    // Usamos las credenciales que mapeamos antes (correo y password)
    return Auth::attempt(
        $this->credentials($request), 
        $request->filled('remember')
    );
}
    /**
     * REVISIÓN CLAVE: Redirección forzada a 'admin.dashboard'.
     * Si Laravel te regresa al login, es porque no encuentra esta ruta.
     */
    protected function authenticated(Request $request, $user)
    {
        // Forzamos que todos vayan a la ruta 'admin.dashboard' que definimos en web.php
        return redirect()->route('admin.dashboard');
    }

    protected function credentials(Request $request)
    {
        return [
            'correo'   => $request->correo, 
            'password' => $request->contrasena, 
        ];
    }

    public function username()
    {
        return 'correo';
    }
}