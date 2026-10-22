<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiRecommendation extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'source_data' => 'array',
            'is_read' => 'boolean',
        ];
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
