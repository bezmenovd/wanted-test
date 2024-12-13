<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;

/**
 * @property int $id
 * @property int $ext_id
 * @property string $name
 * @property Carbon $date
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Row extends Model
{
    use HasTimestamps;

    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'date'  => 'date',
    ];
}
