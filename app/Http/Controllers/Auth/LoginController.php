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

        // 3. Si no cumple ninguna de las anteriores (como un Mesero), 
        // va directo a su panel operativo de mesas que está libre de permisos modulares
        return redirect()->route('mesero.dashboard');
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