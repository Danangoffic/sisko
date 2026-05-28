<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ClassPromotionController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentTypeController;
use App\Http\Controllers\ReportCardController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::get('/kelas', [SchoolClassController::class, 'index'])->name('class.index');
    Route::post('/kelas', [SchoolClassController::class, 'store'])->name('class.store');
    Route::delete('/kelas/{school_class}', [SchoolClassController::class, 'destroy'])->name('class.destroy');

    Route::middleware('role:admin')->group(function () {
        Route::inertia('/users', 'users/index')->name('users.index');
        Route::resource('teachers', TeacherController::class)->except(['create', 'edit', 'show']);
        Route::resource('students', StudentController::class)->except(['create', 'edit', 'show']);
        Route::resource('academic-years', AcademicYearController::class)->except(['create', 'edit', 'show']);
        Route::post('academic-years/{academic_year}/semesters', [AcademicYearController::class, 'storeSemester'])->name('academic-years.semesters.store');
        Route::put('semesters/{semester}', [AcademicYearController::class, 'updateSemester'])->name('semesters.update');
        Route::delete('semesters/{semester}', [AcademicYearController::class, 'destroySemester'])->name('semesters.destroy');
        Route::get('promotions', [ClassPromotionController::class, 'index'])->name('promotions.index');
        Route::post('promotions', [ClassPromotionController::class, 'store'])->name('promotions.store');
        Route::post('promotions/batch', [ClassPromotionController::class, 'promoteBatch'])->name('promotions.batch');
        Route::put('promotions/{promotion}', [ClassPromotionController::class, 'update'])->name('promotions.update');
        Route::delete('promotions/{promotion}', [ClassPromotionController::class, 'destroy'])->name('promotions.destroy');
        Route::resource('subjects', SubjectController::class)->except(['create', 'edit', 'show']);
        Route::resource('schedules', ScheduleController::class)->except(['create', 'edit', 'show']);
        Route::resource('payment-types', PaymentTypeController::class)->except(['create', 'edit', 'show']);
        Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::post('invoices/{invoice}/pay', [InvoiceController::class, 'pay'])->name('invoices.pay');
        Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
    });

    Route::middleware('role:admin,guru')->group(function () {
        Route::get('attendances', [AttendanceController::class, 'index'])->name('attendances.index');
        Route::post('attendances', [AttendanceController::class, 'store'])->name('attendances.store');
        Route::delete('attendances/{attendance}', [AttendanceController::class, 'destroy'])->name('attendances.destroy');
        Route::get('grades', [GradeController::class, 'index'])->name('grades.index');
        Route::post('grades', [GradeController::class, 'store'])->name('grades.store');
        Route::put('grades/{grade}', [GradeController::class, 'update'])->name('grades.update');
        Route::delete('grades/{grade}', [GradeController::class, 'destroy'])->name('grades.destroy');
        Route::get('report-cards', [ReportCardController::class, 'index'])->name('report-cards.index');
        Route::post('report-cards/generate', [ReportCardController::class, 'generate'])->name('report-cards.generate');
        Route::put('report-cards/{report_card}', [ReportCardController::class, 'update'])->name('report-cards.update');
        Route::delete('report-cards/{report_card}', [ReportCardController::class, 'destroy'])->name('report-cards.destroy');
    });
});

require __DIR__.'/settings.php';
