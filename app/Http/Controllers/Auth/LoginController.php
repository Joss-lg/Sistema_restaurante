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
            $codigoEmpleado = trim($request->codigo_empleado);

            $user = User::whereRaw('TRIM(codigo_empleado) = ?', [$codigoEmpleado])
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
     * Ahora usa el slug del rol desde la relación dinámica
     */
    protected function authenticated(Request $request, $user)
    {
        // 1. Si es el Administrador Supremo (ID 1), va directo al Dashboard Administrativo
        if ($user->id === 1) {
            return redirect()->route('admin.dashboard');
        }

        // 2. Evaluamos los permisos del usuario en la base de datos de manera jerárquica:
        
        // ¿Tiene acceso a la administración / métricas?
        if ($user->tienePermiso('Dashboard', 'mostrar')) {
            return redirect()->route('admin.dashboard');
        }

        // ¿Tiene acceso al módulo de Cocina? (Para los cocineros)
        if ($user->tienePermiso('Cocina', 'mostrar')) {
            return redirect()->route('admin.cocina.index');
        }

        // ¿Tiene acceso al módulo de Caja? (Para los cajeros)
        if ($user->tienePermiso('Caja', 'mostrar')) {
            return redirect()->route('admin.caja.index');
        }

        // 3. Meseros y capitanes: su panel operativo de mesas.
        //
        // Esta rama FALTABA. Sin ella, un mesero (que normalmente solo tiene
        // el módulo "Mesas") caía en el return de abajo y era devuelto a la
        // pantalla de login: se autenticaba correctamente, pero volvía al
        // teclado con el NIP vacío, como si no hubiera pasado nada.
        //
        // Se manda a 'admin.mesas.index' (/mesas) y no a 'mesero.dashboard':
        // ambas rutas renderizan EXACTAMENTE la misma vista (MesaController@index),
        // pero /mesas es la que el personal reconoce y la que aparece en el menú.
        //
        // Va al final a propósito: si alguien tiene Caja y Mesas a la vez,
        // gana Caja, que es la vista más completa para ese perfil.
        if ($user->tienePermiso('Mesas', 'mostrar')) {
            return redirect()->route('admin.mesas.index');
        }

        // 4. Sin ningún módulo asignado no hay a dónde mandarlo: se cierra la
        //    sesión para no dejarlo autenticado dando vueltas en el login.
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('error', 'Este usuario no tiene módulos asignados. Pide al administrador que le asigne permisos.');
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