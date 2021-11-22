<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'status', "update_by",
    ];

    public function task() {
        return $this->belongsTo('App\Models\Task');
    }
}
