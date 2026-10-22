<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyTask extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'task_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function plan()
    {
        return $this->belongsTo(StudyPlan::class, 'study_plan_id');
    }
}
