<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'date', 'time', 'description', 'status',
    ];

    public function users() {
        return $this->belongsToMany('App\Models\User');
    }


}
