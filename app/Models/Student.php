<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'enrollments')->withPivot('status')->withTimestamps();
    }

    public function marks()
    {
        return $this->hasMany(Mark::class);
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function studyPlans()
    {
        return $this->hasMany(StudyPlan::class);
    }

    public function recommendations()
    {
        return $this->hasMany(AiRecommendation::class);
    }

    public function latestRiskScore()
    {
        return $this->hasOne(AcademicRiskScore::class)->latestOfMany('calculated_at');
    }
}
