<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'student_id',
        'department_id',
        'roll_no',
        'batch',
        'semester',
        'address',
    ];

    protected static function booted(): void
    {
        static::saving(function (Student $student): void {
            $studentId = trim((string) ($student->student_id ?? ''));
            $rollNo = trim((string) ($student->roll_no ?? ''));

            if ($studentId === '' && $rollNo !== '') {
                $studentId = $rollNo;
            }

            if ($rollNo === '' && $studentId !== '') {
                $rollNo = $studentId;
            }

            $student->student_id = $studentId !== '' ? strtoupper($studentId) : null;
            $student->roll_no = $rollNo !== '' ? strtoupper($rollNo) : null;
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function issuedBooks()
    {
        return $this->hasMany(IssuedBook::class);
    }

    public function bookRequests()
    {
        return $this->hasMany(BookRequest::class);
    }

    public function fines()
    {
        return $this->hasMany(Fine::class);
    }

    /**
     * Get the privilege settings for this student.
     */
    public function privilege()
    {
        return $this->hasOne(StudentPrivilege::class);
    }

    public function privileges()
    {
        return $this->hasOne(StudentPrivilege::class);
    }

    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'model');
    }

    public function getDisplayStudentIdAttribute(): ?string
    {
        return $this->student_id ?: $this->roll_no;
    }
}
