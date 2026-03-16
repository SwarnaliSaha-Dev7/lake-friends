<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogChange extends Model
{
    protected $fillable = ['model', 'model_id', 'action', 'changes', 'updated_by', 'changed_from', 'description'];
}
