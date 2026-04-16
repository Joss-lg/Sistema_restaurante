<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | Este controlador maneja la autenticación de usuarios para la web (email)
    | y para el punto de venta (PIN/Código de empleado).
    |
    */

    use AuthenticatesUsers;

    /**
     * Redirección por defecto después del login.
     */
    protected $redirectTo = '/dashboard';

    /**
     * Constructor con middlewares para invitados y autenticados.
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * MÉTODO PRINCIPAL PARA TU TECLADO NUMÉRICO.
     * Este es el que llamamos desde web.php.
     */
    public function loginConPin(Request $request)
    {
        // 1. Validamos que el PIN sea de 4 dígitos
        $this->validateLogin($request);

        // 2. Intentamos el acceso (llama a attemptLogin de abajo)
        if ($this->attemptLogin($request)) {
            // 3. Si es exitoso, redireccionamos
            return $this->authenticated($request, Auth::user());
        }

        // 4. Si falla, regresamos al login con error
        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Valida la entrada dependiendo de si viene PIN o Email.
     */
    protected function validateLogin(Request $request)
    {
        if ($request->has('codigo_empleado')) {
            $request->validate([
                'codigo_empleado' => 'required|string|size:4',
            ]);
        } else {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);
        }
    }

    /**
     * Lógica personalizada para intentar el login.
     */
    protected function attemptLogin(Request $request)
    {
        // Caso A: Intento por PIN (Punto de Venta)
        if ($request->has('codigo_empleado') && !empty($request->codigo_empleado)) {
            $user = User::where('codigo_empleado', $request->codigo_empleado)
                        ->where('esta_activo', true)
                        ->first();
            
            if ($user) {
                // Iniciamos sesión manualmente
                Auth::login($user, $request->filled('remember'));
                
                // Regenerar sesión para seguridad
                $request->session()->regenerate();
                
                // Actualizamos el último acceso
                $user->update(['ultimo_acceso' => now()]);
                
                return true;
            }
            return false;
        }

        // Caso B: Intento estándar (Email y Password)
        return Auth::attempt(
            $this->credentials($request), 
            $request->filled('remember')
        );
    }

    /**
     * Acción después de una autenticación exitosa.
     */
    protected function authenticated(Request $request, $user)
    {
        // Forzamos la redirección a la ruta que definiste en web.php
        return redirect()->route('admin.dashboard');
    }

    /**
     * Credenciales para el login estándar.
     */
    protected function credentials(Request $request)
    {
        return [
            'email'    => $request->email, 
            'password' => $request->password, 
        ];
    }

    /**
     * Define qué columna se usa como "username".
     */
    public function username()
    {
        return 'email';
    }
}