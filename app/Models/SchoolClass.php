<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    protected $fillable = ['name', 'homeroom_teacher'];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
