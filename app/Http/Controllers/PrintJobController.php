<?php

namespace App\Http\Controllers;

use App\Models\PrintJob;
use Illuminate\Http\Request;

class PrintJobController extends Controller
{
    /**
     * El agente local (PowerShell en la PC de Cocina/Barra) llama esto
     * cada pocos segundos preguntando: "¿hay algo nuevo para mi área?"
     *
     * Ejemplo: GET /api/print-jobs/pendientes?area=Cocina
     */
    public function pendientes(Request $request)
    {
        $request->validate([
            'area' => 'required|string|in:Cocina,Barra',
        ]);

        $jobs = PrintJob::pendientes()
            ->deArea($request->area)
            ->orderBy('created_at', 'asc')
            ->get(['id', 'orden_id', 'lote_envio', 'area', 'contenido', 'created_at']);

        return response()->json([
            'success' => true,
            'trabajos' => $jobs,
        ]);
    }

    /**
     * El agente llama esto después de imprimir con éxito, para que
     * ese trabajo no se vuelva a mandar en la siguiente consulta.
     *
     * Ejemplo: POST /api/print-jobs/{id}/marcar-impreso
     */
    public function marcarImpreso($id)
    {
        $job = PrintJob::findOrFail($id);
        $job->update([
            'estado' => 'impreso',
            'impreso_en' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Opcional: si el agente falla al imprimir (impresora sin papel, etc.)
     * puede reportar el error aquí en vez de dejarlo como pendiente para siempre.
     *
     * Ejemplo: POST /api/print-jobs/{id}/marcar-error
     */
    public function marcarError($id)
    {
        $job = PrintJob::findOrFail($id);
        $job->update(['estado' => 'error']);

        return response()->json(['success' => true]);
    }
}