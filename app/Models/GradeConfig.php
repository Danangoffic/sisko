<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeConfig extends Model
{
    use HasFactory;

    protected $fillable = ['academic_year_id', 'type', 'passing_grade', 'scale_max'];

    protected $casts = [
        'passing_grade' => 'decimal:2',
        'scale_max' => 'decimal:2',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
