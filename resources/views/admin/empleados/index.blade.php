@extends('layouts.app') @section('content')
<div class="container-fluid">
    <h2 class="mb-4">Gestión de Staff y Permisos</h2>

    <div class="card shadow">
        <div class="card-body">
            <table class="table table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Rol</th>
                        <th>PIN (Código)</th>
                        
                    </tr>
                </thead>
                <tbody>
                    @foreach($empleados as $empleado)
                    <tr>
                        <td><strong>{{ $empleado->nombre }}</strong></td>
                        <td><span class="badge badge-info">{{ ucfirst($empleado->rol) }}</span></td>
                        <td><code>{{ $empleado->codigo_empleado }}</code></td>
                        <td>
                            <div class="d-flex flex-wrap">
                                @foreach($permisos as $permiso)
                                    <div class="form-check mr-3">
                                        <input class="form-check-input" type="checkbox" 
                                               id="permiso-{{ $empleado->id }}-{{ $permiso->id }}"
                                               {{ $empleado->tienePermiso($permiso->slug) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="permiso-{{ $empleado->id }}-{{ $permiso->id }}">
                                            {{ $permiso->nombre }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop