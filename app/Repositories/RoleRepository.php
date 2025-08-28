<?php
namespace App\Repositories;

use App\Interfaces\RoleInterface;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class RoleRepository extends BaseRepository implements RoleInterface
{

    public function __construct()
    {
        parent::__construct();

    }
    public function CrearRol($request)
    {

        $this->validateRole($request);

        $role = Role::create([
            'name' => $this->cleanHtml($request->name),
        ]);


        if ($request->has(key: 'permissions')) {
            $role->givePermissionTo($request->permissions);
        }
    }
    public function EditarRol($request, $id)
    {

        $this->validateRole($request, $id);

        $role = Role::findOrFail($id);
        $role->name = $this->cleanHtml($request->name);
        $role->save();
        $role->syncPermissions($request->permissions);

        return $role;
    }

    private function validateRole(Request $request, $id = null)
    {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($id),
            ],
            'permissions' => 'array',
        ];

        $messages = [
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.string' => 'El nombre del rol debe ser una cadena de texto.',
            'name.max' => 'El nombre del rol no puede exceder los 255 caracteres.',
            'name.unique' => 'Este nombre de rol ya está en uso.',
            'permissions.array' => 'Los permisos deben ser una lista válida.',
        ];

        $request->validate($rules, $messages);
    }

}
