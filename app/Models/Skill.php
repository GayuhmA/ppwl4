<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $fillable = ['name', 'level'];

    protected function casts(): array
    {
        return ['level' => 'integer'];
    }
}
