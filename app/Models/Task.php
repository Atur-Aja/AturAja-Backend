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
        'title', 'date', 'time', 'description', 'status', 'priority',
    ];

    public function users() {
        return $this->belongsToMany('App\Models\User');
    }

    public function todos() {
        return $this->hasMany('App\Models\todo');
    }
}
