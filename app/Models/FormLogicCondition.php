<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class FormLogicCondition extends Model
{
    use HasFactory;

    protected $fillable = [
        'action_id',
        'campo_condicion',
        'operador',
        'valor'
    ];

    public function action()
    {
        return $this->belongsTo(FormLogicAction::class, 'action_id');
    }
    public function campoCondicion()
    {
        return $this->belongsTo(CamposForm::class, 'campo_condicion');
    }

    public function campoValor()
    {
        return $this->belongsTo(CamposForm::class, 'valor');
    }
}
