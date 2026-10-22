<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['exam_date' => 'date'];
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
