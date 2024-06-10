<?php

namespace App\Models\SysAdmin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskStat extends Model
{
    use SoftDeletes;
    
    protected $guarded = [];

    public function task() : BelongsTo {

        return $this->belongsTo(Task::class);
        
    }

}
