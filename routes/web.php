<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| CONTROLADORES
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\UserController;
use App\Http\Controllers\SeccionController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ConfCorreoController;
use App\Http\Controllers\CorreoController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ConfiguracionCredencialesController;
use App\Http\Controllers\ArtisanController;
use App\Http\Controllers\UserPersonalizacionController;
use App\Http\Controllers\FormularioController;
use App\Http\Controllers\CamposFormController;
use App\Http\Controllers\RespuestasFormController;
use App\Http\Controllers\ModuloController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\FormLogicController;
use App\Http\Controllers\LogicaController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SeederController;

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS (SIN AUTH)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/clear-cache', function () {
    Artisan::call('optimize:clear');
});

// Autenticación Laravel
Auth::routes();

/*
|--------------------------------------------------------------------------
| DOBLE FACTOR (ANTES DE ENTRAR AL SISTEMA)
|--------------------------------------------------------------------------
*/
Route::get('/2fa/verify', [TwoFactorController::class, 'index'])->name('verify.index');
Route::post('/2fa/verify', [TwoFactorController::class, 'store'])->name('verify.store');
Route::post('/2fa/resend', [TwoFactorController::class, 'resend'])->name('verify.resend');

/*
|--------------------------------------------------------------------------
| RUTAS PROTEGIDAS POR AUTH (SISTEMA)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | HOME
    |--------------------------------------------------------------------------
    */
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');



    Route::get('/email/campos-usuario', [UserController::class, 'camposUsuario']);
    /*
    |--------------------------------------------------------------------------
    | PERSONALIZACIÓN DE USUARIO
    |--------------------------------------------------------------------------
    */
    Route::post('/guardar-color-sidebar', [UserPersonalizacionController::class, 'guardarSidebarColor']);
    Route::post('/user/personalizacion/sidebar-type', [UserPersonalizacionController::class, 'updateSidebarType']);
    Route::post('/user/preferences', [UserPersonalizacionController::class, 'updateDark']);

    /*
    |--------------------------------------------------------------------------
    | PERFIL Y CONTRASEÑA
    |--------------------------------------------------------------------------
    */
    Route::get('/usuario/contraseña', [PasswordController::class, 'ActualizarContraseña'])->name('user.actualizar.contraseña');
    Route::put('password/update', [PasswordController::class, 'update'])->name('password.actualizar');
    Route::get('/usuario/perfil', [UserController::class, 'Perfil'])->name('perfil');

    /*
    |--------------------------------------------------------------------------
    | LOGS DEL SISTEMA
    |--------------------------------------------------------------------------
    */
    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    Route::get('/logs/{filename}', [LogController::class, 'show'])->name('logs.show');

    /*
    |--------------------------------------------------------------------------
    | ARTISAN (PRODUCCIÓN)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['can:ejecutar-artisan'])->group(function () {
        Route::get('/artisan-panel', [ArtisanController::class, 'verificacion'])->name('artisan.admin');
        Route::post('/artisan-panel', [ArtisanController::class, 'index'])->name('artisan.verificar');
        Route::post('/artisan/run', [ArtisanController::class, 'run'])->name('artisan.run');
    });

    /*
    |--------------------------------------------------------------------------
    | ADMINISTRACIÓN DE USUARIOS
    |--------------------------------------------------------------------------
    */
    Route::middleware(['can:Administración de Usuarios'])->group(function () {

        Route::get('/usuarios', [UserController::class, 'index'])->name('users.index')->middleware('can:usuarios.ver');
        Route::get('/usuarios/crear', [UserController::class, 'create'])->name('users.create')->middleware('can:usuarios.crear');
        Route::post('/usuarios', [UserController::class, 'store'])->name('users.store')->middleware('can:usuarios.crear');
        Route::get('/usuarios/{user}', [UserController::class, 'show'])->name('users.show')->middleware('can:usuarios.ver');
        Route::get('/usuarios/edit/{id}', [UserController::class, 'edit'])->name('users.edit')->middleware('can:usuarios.editar');
        Route::put('/usuarios/{id}/{perfil}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/usuarios/{user}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('can:usuarios.eliminar');
        Route::get('/datos/usuario/{id}', [UserController::class, 'GetUsuario'])->name('users.get')->middleware('can:usuarios.ver');

        Route::get('/usuarios/exportar/excel', [UserController::class, 'exportExcel'])->name('usuarios.exportar_excel')->middleware('can:usuarios.exportar_excel');
        Route::get('/usuarios/exportar/pdf', [UserController::class, 'exportPDF'])->name('usuarios.exportar_pdf')->middleware('can:usuarios.exportar_pdf');
    });

    /*
    |--------------------------------------------------------------------------
    | SECCIONES Y MENÚS (ADMIN)
    |--------------------------------------------------------------------------
    */
    Route::resource('secciones', SeccionController::class)->except(['show'])->middleware('role:admin');
    Route::resource('menus', MenuController::class)->except(['show'])->middleware('role:admin');
    Route::post('/api/sugerir-icono', [SeccionController::class, 'SugerirIcono']);
    Route::post('obtener/dato/menu', [SeccionController::class, 'cambiarSeccion'])->middleware('role:admin');
    Route::post('/secciones/ordenar', [SeccionController::class, 'ordenar'])->name('secciones.ordenar');

    /*
    |--------------------------------------------------------------------------
    | CONFIGURACIONES
    |--------------------------------------------------------------------------
    */
    Route::middleware(['can:Configuración'])->group(function () {
        Route::get('/configuracion/correo', [ConfCorreoController::class, 'index'])->name('configuracion.correo.index')->middleware('can:configuracion_correo.ver');
        Route::post('/configuracion/correo/guardar', [ConfCorreoController::class, 'store'])->name('configuracion.correo.store')->middleware('can:configuracion_correo.actualizar');
        Route::put('configuracion_correo', [ConfCorreoController::class, 'update'])->name('configuracion_correo.update')->middleware('can:configuracion_correo.actualizar');
        Route::get('/correo/prueba', [ConfCorreoController::class, 'enviarPrueba'])->name('correo.prueba');
    });

    Route::middleware(['role:admin', 'can:Configuración General'])->group(function () {
        Route::get('/admin/configuracion', [ConfiguracionController::class, 'edit'])->name('admin.configuracion.edit');
        Route::put('/admin/configuracion', [ConfiguracionController::class, 'update'])->name('admin.configuracion.update');
    });

    Route::middleware(['role:admin', 'can:Configuración Credenciales'])->group(function () {
        Route::get('/configuracion/credenciales', [ConfiguracionCredencialesController::class, 'index'])->name('configuracion.credenciales.index');
        Route::post('/configuracion/credenciales/actualizar', [ConfiguracionCredencialesController::class, 'actualizar'])->name('configuracion.credenciales.actualizar');
    });


    Route::get('/plantillas', [CorreoController::class, 'index'])->name('plantillas.index');
    Route::get('/plantillas/crear', [CorreoController::class, 'create'])->name('plantillas.create');
    Route::post('/plantillas', [CorreoController::class, 'store'])->name('plantillas.store');

    Route::get('/plantillas/{plantilla}/editar', [CorreoController::class, 'edit'])->name('plantillas.edit');
    Route::put('/plantillas/{plantilla}', [CorreoController::class, 'update'])->name('plantillas.update');

    Route::delete('/plantillas/{plantilla}', [CorreoController::class, 'destroy'])->name('plantillas.destroy');





    Route::middleware(['role:admin'])->group(function () {

        Route::get('/roles', [RoleController::class, 'index'])
            ->name('roles.index')
            ->middleware('can:roles.inicio');

        Route::get('/roles/create', [RoleController::class, 'create'])
            ->name('roles.create')
            ->middleware('can:roles.crear');

        Route::post('/roles', [RoleController::class, 'store'])
            ->name('roles.store')
            ->middleware('can:roles.guardar');

        Route::get('/roles/edit/{id}', [RoleController::class, 'edit'])
            ->name('roles.edit')
            ->middleware('can:roles.editar');

        Route::put('/roles/{id}', [RoleController::class, 'update'])
            ->name('roles.update')
            ->middleware('can:roles.actualizar');

        Route::delete('/roles/{id}', [RoleController::class, 'destroy'])
            ->name('roles.destroy')
            ->middleware('can:roles.eliminar');

        Route::get('/permissions', [PermissionController::class, 'index'])
            ->name('permissions.index')
            ->middleware('can:permisos.inicio');

        Route::get('/permissions/create', [PermissionController::class, 'create'])
            ->name('permissions.create')
            ->middleware('can:permisos.crear');

        Route::post('/permissions', [PermissionController::class, 'store'])
            ->name('permissions.store')
            ->middleware('can:permisos.guardar');

        Route::get('/permissions/edit/{id}', [PermissionController::class, 'edit'])
            ->name('permissions.edit')
            ->middleware('can:permisos.editar');

        Route::put('/permissions/{id}', [PermissionController::class, 'update'])
            ->name('permissions.update')
            ->middleware('can:permisos.actualizar');

        Route::delete('/permissions/{id}', [PermissionController::class, 'destroy'])
            ->name('permissions.destroy')
            ->middleware('can:permisos.eliminar');

        Route::get('/permissions/cargar/menu/{id}/{rol_id}', [RoleController::class, 'get_permisos_menu'])
            ->name('permissions.menu');

    });

    /*
     |--------------------------------------------------------------------------
     | CATÁLOGOS Y CATEGORÍAS
     |--------------------------------------------------------------------------
     |
     */

    Route::middleware(['auth', 'can:Administración y Parametrización'])->group(function () {

        // Rutas para catalogos
        Route::get('/catalogos', [CatalogoController::class, 'index'])->name('catalogos.index')->middleware('can:catalogo.ver');
        Route::get('/catalogos/create', [CatalogoController::class, 'create'])->name('catalogos.create')->middleware('can:catalogo.crear');
        Route::post('/catalogos', [CatalogoController::class, 'store'])->name('catalogos.store')->middleware('can:catalogo.guardar');
        Route::get('/catalogos/{id}', [CatalogoController::class, 'show'])->name('catalogos.show')->middleware('can:catalogo.ver_detalle');
        Route::get('/catalogos/{id}/edit', [CatalogoController::class, 'edit'])->name('catalogos.edit')->middleware('can:catalogo.editar');
        Route::put('/catalogos/{id}', [CatalogoController::class, 'update'])->name('catalogos.update')->middleware('can:catalogo.actualizar');
        Route::delete('/catalogos/{id}', [CatalogoController::class, 'destroy'])->name('catalogos.destroy')->middleware('can:catalogo.eliminar');

        // Rutas para categorias
        Route::get('/categorias', [CategoriaController::class, 'index'])->name('categorias.index')->middleware('can:categoria.ver');
        Route::get('/categorias/create', [CategoriaController::class, 'create'])->name('categorias.create')->middleware('can:categoria.crear');
        Route::post('/categorias', [CategoriaController::class, 'store'])->name('categorias.store')->middleware('can:categoria.guardar');
        Route::get('/categorias/{id}', [CategoriaController::class, 'show'])->name('categorias.show')->middleware('can:categoria.ver_detalle');
        Route::get('/categorias/{id}/edit', [CategoriaController::class, 'edit'])->name('categorias.edit')->middleware('can:categoria.editar');
        Route::put('/categorias/{id}', [CategoriaController::class, 'update'])->name('categorias.update')->middleware('can:categoria.actualizar');
        Route::delete('/categorias/{id}', [CategoriaController::class, 'destroy'])->name('categorias.destroy')->middleware('can:categoria.eliminar');
    });


    /*
    |--------------------------------------------------------------------------
    | FORMULARIOS, RESPUESTAS, MÓDULOS Y LÓGICA
    |--------------------------------------------------------------------------
    |
    */



    // Formularios
    Route::get('formularios', [FormularioController::class, 'index'])->name('formularios.index');
    Route::get('formularios/create', [FormularioController::class, 'create'])->name('formularios.create');
    Route::post('formularios', [FormularioController::class, 'store'])->name('formularios.store');
    Route::get('formularios/{formulario}/edit', [FormularioController::class, 'edit'])->name('formularios.edit');
    Route::put('formularios/{formulario}', [FormularioController::class, 'update'])->name('formularios.update');
    Route::delete('formularios/{formulario}', [FormularioController::class, 'destroy'])->name('formularios.destroy');







    // Listado y constructor de campos
    Route::get('/formularios/{formulario}/campos', [CamposFormController::class, 'index'])
        ->name('formularios.campos.index');

    // Crear campo
    Route::post('/campos/{formulario}', [CamposFormController::class, 'store'])
        ->name('campos.store');

    // Obtener datos de un campo (para editar)
    Route::get('/campos/{campo}', [CamposFormController::class, 'show'])
        ->name('campos.show');

    // Actualizar campo
    Route::put('/campos/{campo}', [CamposFormController::class, 'update'])
        ->name('campos.update');

    // Eliminar campo
    Route::delete('/campos/{campo}', [CamposFormController::class, 'destroy'])
        ->name('campos.destroy');

    // Reordenar campos
    Route::put('/formularios/{formulario}/campos/reordenar', [CamposFormController::class, 'reordenar'])
        ->name('formularios.campos.reordenar');



    Route::get('/campos/{campo}/cargar-mas', [CamposFormController::class, 'cargarMasOpciones'])
        ->name('campos.cargar_mas');


    Route::get('/campos/{campo}/buscar-opcion', [CamposFormController::class, 'buscarOpcion'])
        ->name('campos.buscar-opcion');

    Route::get('/formulario/{id}/campos', [App\Http\Controllers\FormularioController::class, 'showCampos'])->name('formulario.campos');


    Route::get('/formularios/{form}/create/{modulo}', [RespuestasFormController::class, 'create'])
        ->name('formularios.registrar');


    Route::post('/formularios/{form}/responder/{modulo}', [RespuestasFormController::class, 'store'])
        ->name('formularios.responder');


    Route::get('/formularios/{form}/respuestas', [App\Http\Controllers\RespuestasFormController::class, 'indexPorFormulario'])
        ->name('formularios.respuestas.formulario');


    Route::get('/campos/{campo}/check-respuestas', [CamposFormController::class, 'checkRespuestas'])
        ->name('campos.checkRespuestas');


    Route::get('/respuestas/{respuesta}/edit/{modulo}', [RespuestasFormController::class, 'edit'])
        ->name('respuestas.edit');

    Route::put('/respuestas/{respuesta}/{modulo}', [RespuestasFormController::class, 'update'])
        ->name('respuestas.update');

    Route::delete('/respuestas/{respuesta}', [RespuestasFormController::class, 'destroy'])
        ->name('respuestas.destroy');

    Route::get('/formularios/{form}/export-pdf', [FormularioController::class, 'exportPdf'])
        ->name('formularios.exportPdf');
    Route::get('/formularios/{form}/export/excel', [FormularioController::class, 'exportExcel'])->name('formularios.exportExcel');


    Route::get('/formularios/{form}/carga', [RespuestasFormController::class, 'CargaMasiva'])
        ->name('formularios.carga_masiva');

    Route::post('/formularios/importar/{form}', [RespuestasFormController::class, 'importarDesdeArchivo'])
        ->name('formularios.importar');

    Route::get('/formularios/{form}/descargar-plantilla', [RespuestasFormController::class, 'descargarPlantilla'])
        ->name('formularios.descargar.plantilla');


    Route::post('/import/subir', [RespuestasFormController::class, 'subirArchivo'])->name('import.subir');
    Route::post('/import/procesar', [RespuestasFormController::class, 'procesarChunk'])->name('import.procesar');


    Route::get('/modulos', [ModuloController::class, 'index'])->name('modulos.index');
    Route::get('/modulos/crear', [ModuloController::class, 'create'])->name('modulos.create');
    Route::post('/modulos', [ModuloController::class, 'store'])->name('modulos.store');
    Route::get('/modulos/{modulo}/editar', [ModuloController::class, 'edit'])->name('modulos.edit');
    Route::put('/modulos/{modulo}', [ModuloController::class, 'update'])->name('modulos.update');
    Route::delete('/modulos/{modulo}', [ModuloController::class, 'destroy'])->name('modulos.destroy');
    Route::get('/modulos/{modulo_id}', [ModuloController::class, 'ModulosIndex'])->name('modulo.index');

    Route::get('/modulo/{modulo_id}/administrar', [ModuloController::class, 'ModuloAdmin'])->name('modulo.administrar');

    // Ruta para verificar si el formulario ya está asociado
    Route::get('/modulos/formulario/check/{formulario_id}', [ModuloController::class, 'checkFormulario'])
        ->name('modulos.formulario.check');

    //cambiar visibilidad del formulario asociado al modulo
    Route::post('/modulo/formulario/toggle', [ModuloController::class, 'toggle'])
        ->name('modulo.formulario.toggle');

    //actualizar configuracion
    Route::put('/modulos/{modulo}/configuracion', [ModuloController::class, 'actualizarConfiguracion'])
        ->name('modulos.configuracion.update');


    Route::prefix('admin/form-logic')->middleware(['auth'])->group(function () {
        Route::get('/', [FormLogicController::class, 'index'])->name('form-logic.index');
        Route::get('/create/{modulo}', [FormLogicController::class, 'create'])->name('form-logic.create');
        Route::post('/store/{modulo}', [FormLogicController::class, 'store'])->name('form-logic.store');
        Route::get('/{rule}/edit/{modulo}', [FormLogicController::class, 'edit'])->name('form-logic.edit');
        Route::put('/{rule}/update/{modulo}', [FormLogicController::class, 'update'])->name('form-logic.update');
        Route::delete('/{rule}/delete', [FormLogicController::class, 'destroy'])->name('form-logic.delete');
    });

    Route::get('/formularios/{id}/obtiene/campos', [FormularioController::class, 'obtenerCampos'])
        ->name('formularios.campos');


    Route::get('/formularios/{form_id}/respuestas/{respuesta_id}', [FormularioController::class, 'obtenerFila'])
        ->name('formularios.obtenerFila');


    Route::get('/form-destino/info/{formDestinoId}', [FormularioController::class, 'getInfo'])
        ->name('form-destino.info');


    Route::post('/ejecutar-logica-formulario', [LogicaController::class, 'ejecutar'])
        ->name('logica.ejecutar');

    Route::get('/formulario/logica/detalle/{accion_id}', action: [FormularioController::class, 'detalle'])
        ->name('formulario.auditoria.detalle');


    Route::get('/formulario/logica/detalle', action: [FormularioController::class, 'detalle'])
        ->name('formulario.auditoria.index');

    Route::get('/notification/{notification}/markAsRead', [NotificationController::class, 'markAsRead'])->name('notification.markAsRead');



    Route::get('/seeders/explorador', [SeederController::class, 'index'])
        ->name('seeders.index');

    Route::get('/seeders/ver', [SeederController::class, 'verSeeder'])
        ->name('seeders.ver');
});











