<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_class_id',
        'user_id',
        'nisn',
        'name',
        'gender',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'no_telp_ortu',
        'nama_ortu',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
