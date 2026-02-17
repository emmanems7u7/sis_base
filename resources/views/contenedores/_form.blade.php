<div class="mb-3">
    <label>Nombre</label>
    <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $contenedor->nombre ?? '') }}">
</div>


<div class="mb-3">
    <label>Rol</label>
    <select name="role_id" class="form-control">
        @foreach(Spatie\Permission\Models\Role::all() as $rol)
            <option value="{{ $rol->id }}" {{ (isset($contenedor) && $contenedor->role_id == $rol->id) ? 'selected' : '' }}>
                {{ $rol->name }}
            </option>
        @endforeach
    </select>
</div>