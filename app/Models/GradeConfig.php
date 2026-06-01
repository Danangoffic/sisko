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

    /**
     * Konversi nilai angka ke huruf berdasarkan skala scale_max.
     * Menggunakan pembagian 5 band: A/B/C/D/E.
     */
    public function letterFor(float $score): string
    {
        $max = (float) $this->scale_max ?: 100;
        $pct = ($score / $max) * 100;

        return match (true) {
            $pct >= 85 => 'A',
            $pct >= 70 => 'B',
            $pct >= 55 => 'C',
            $pct >= 40 => 'D',
            default => 'E',
        };
    }

    /**
     * Apakah nilai ini lulus berdasarkan passing_grade?
     */
    public function isPassing(float $score): bool
    {
        return $score >= (float) $this->passing_grade;
    }
}
