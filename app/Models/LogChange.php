<?php

namespace App\Models;

use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Model;

class LogChange extends Model
{
    use LogsModelChanges;
    protected $fillable = ['model', 'model_id', 'action', 'changes', 'updated_by', 'changed_from', 'description'];
}
