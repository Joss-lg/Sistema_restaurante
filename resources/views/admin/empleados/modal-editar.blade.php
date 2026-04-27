<style>
    /* VARIABLES DE CONTRASTE INVERSO PREMIUM */
    .modal-inverso {
        --m-bg: #ffffff;
        --m-text: #09090b;
        --m-muted: #71717a;
        --m-input-bg: #f8fafc;
        --m-border: #e2e8f0;
        --m-btn-bg: #0f172a;
        --m-btn-text: #ffffff;
        --m-drop-bg: #ffffff;
        --m-drop-hover: #f1f5f9;
        --m-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        --m-glow: rgba(0, 0, 0, 0.05);
    }

    body.modo-crema .modal-inverso {
        --m-bg: #09090b;
        --m-text: #fafafa;
        --m-muted: #a1a1aa;
        --m-input-bg: rgba(255, 255, 255, 0.03);
        --m-border: rgba(255, 255, 255, 0.08);
        --m-btn-bg: #ffffff;
        --m-btn-text: #09090b;
        --m-drop-bg: #18181b;
        --m-drop-hover: rgba(255, 255, 255, 0.05);
        --m-shadow: 0 35px 60px -15px rgba(0, 0, 0, 0.8);
        --m-glow: rgba(255, 255, 255, 0.03);
    }
</style>

<div id="editEmpleadoModal" class="fixed inset-0 bg-black/60 backdrop-blur-md z-[100] flex items-center justify-center p-4 hidden opacity-0 transition-all duration-500">
    
    <div class="modal-inverso relative bg-[var(--m-bg)] border border-[var(--m-border)] rounded-[2.5rem] p-10 w-full max-w-[480px] transform scale-95 transition-all duration-500" style="box-shadow: var(--m-shadow);" id="editModalContent">
        
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-[var(--m-glow)] rounded-full blur-3xl pointer-events-none"></div>

        <button type="button" onclick="cerrarEditModal()" class="absolute top-8 right-8 text-[var(--m-muted)] hover:text-[var(--m-text)] hover:rotate-90 transition-all duration-300 outline-none z-10">
            <i class="fas fa-times text-xl"></i>
        </button>

        <div class="mb-10 relative z-10">
            <h2 class="text-3xl font-black text-[var(--m-text)] tracking-tighter">Editar Perfil</h2>
            <p class="text-[11px] font-bold text-[var(--m-muted)] uppercase tracking-[0.2em] mt-2 opacity-80">Actualización de credenciales</p>
        </div>

        <form id="editEmpleadoForm" method="POST" class="space-y-7 relative z-10">
            @csrf
            @method('PUT')

            <div class="group">
                <label for="edit_nombre" class="text-[10px] font-black text-[var(--m-text)] uppercase tracking-[0.2em] mb-3 block opacity-90 group-focus-within:text-[#3B82F6] transition-colors">Nombre Completo</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                        <i class="fas fa-user text-[var(--m-muted)] group-focus-within:text-[#3B82F6] transition-colors text-sm"></i>
                    </div>
                    <input type="text" id="edit_nombre" name="nombre" required 
                        class="w-full h-14 bg-[var(--m-input-bg)] border border-[var(--m-border)] rounded-2xl pl-12 pr-6 text-sm font-bold text-[var(--m-text)] outline-none transition-all focus:border-[#3B82F6] focus:ring-4 focus:ring-[#3B82F6]/10">
                </div>
            </div>
            
            <div class="group">
                <label for="edit_codigo_empleado" class="text-[10px] font-black text-[var(--m-text)] uppercase tracking-[0.2em] mb-3 block opacity-90 group-focus-within:text-[#3B82F6] transition-colors">PIN de Seguridad (4 dígitos)</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-[var(--m-muted)] group-focus-within:text-[#3B82F6] transition-colors text-sm"></i>
                    </div>
                    <input type="text" id="edit_codigo_empleado" name="codigo_empleado" maxlength="4" required 
                        class="w-full h-14 bg-[var(--m-input-bg)] border border-[var(--m-border)] rounded-2xl pl-12 pr-6 text-base font-black tracking-[0.8em] text-[var(--m-text)] outline-none transition-all focus:border-[#3B82F6] focus:ring-4 focus:ring-[#3B82F6]/10">
                </div>
            </div>

            <div class="relative group" id="editDropdownContainer">
                <label class="text-[10px] font-black text-[var(--m-text)] uppercase tracking-[0.2em] mb-3 block opacity-90 group-focus-within:text-[#3B82F6] transition-colors">Rol del Sistema</label>
                
                <input type="hidden" name="rol" id="edit_rol_input">
                
                <button type="button" onclick="toggleEditDropdown(event)" id="editDropdownBtn"
                    class="flex items-center justify-between w-full h-14 bg-[var(--m-input-bg)] border border-[var(--m-border)] rounded-2xl pl-5 pr-6 text-sm font-bold text-[var(--m-text)] outline-none transition-all focus:border-[#3B82F6] focus:ring-4 focus:ring-[#3B82F6]/10">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-shield-halved text-[var(--m-muted)] group-focus-within:text-[#3B82F6] transition-colors text-sm"></i>
                        <span id="editDropdownSelected">Seleccionar...</span>
                    </div>
                    <i class="fas fa-chevron-down text-[var(--m-muted)] transition-transform duration-300" id="editDropdownIcon"></i>
                </button>

                <div id="editDropdownMenu" class="absolute top-[calc(100%+8px)] left-0 w-full bg-[var(--m-drop-bg)] border border-[var(--m-border)] rounded-2xl shadow-2xl z-[110] py-2 hidden opacity-0 translate-y-[-10px] transition-all duration-300 max-h-48 overflow-y-auto backdrop-blur-xl">
                    @php
                        $roles = ['admin' => 'Administrador', 'capitan' => 'Capitán', 'mesero' => 'Mesero', 'cocinero' => 'Cocinero', 'cajero' => 'Cajero'];
                    @endphp
                    @foreach($roles as $val => $label)
                    <button type="button" onclick="selectEditRole('{{ $label }}', '{{ $val }}')"
                        class="flex items-center justify-between w-full px-6 py-3.5 text-sm font-bold text-[var(--m-text)] hover:bg-[var(--m-drop-hover)] hover:text-[#3B82F6] transition-all outline-none">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-4 mt-12 pt-8 border-t border-[var(--m-border)]">
                <button type="button" onclick="cerrarEditModal()" 
                    class="px-8 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest text-[var(--m-muted)] hover:text-[var(--m-text)] hover:bg-[var(--m-input-bg)] transition-all outline-none">
                    Cancelar
                </button>
                <button type="submit" 
                    class="px-10 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest bg-[var(--m-btn-bg)] text-[var(--m-btn-text)] hover:-translate-y-1 active:scale-95 transition-all shadow-xl outline-none">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>