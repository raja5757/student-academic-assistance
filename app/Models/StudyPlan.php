<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyPlan extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'preferred_times' => 'array',
            'source_data' => 'array',
        ];
    }

    public function tasks()
    {
        return $this->hasMany(StudyTask::class);
    }
}
