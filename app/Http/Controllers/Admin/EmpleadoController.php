<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Permiso;
use Illuminate\Http\Request;

class EmpleadoController extends Controller
{
    public function index()
    {
        // Traemos a todos los usuarios (excepto a ti, el admin, para no editarte a ti mismo)
        $empleados = User::where('rol', '!=', 'admin')->get();
        
        // Traemos los permisos que ya existan en la tabla permisos
        $permisos = Permiso::all();

        return view('admin.empleados.index', compact('empleados', 'permisos'));
    }
}