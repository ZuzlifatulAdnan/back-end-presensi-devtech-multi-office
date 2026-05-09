<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    public const TYPE_NATIONAL = 'national';

    public const TYPE_COMPANY = 'company';

    public const TYPE_WEEKEND = 'weekend';

    public const TYPES = [
        self::TYPE_NATIONAL,
        self::TYPE_COMPANY,
        self::TYPE_WEEKEND,
    ];

    protected $fillable = [
        'date',
        'name',
        'type',
        'is_official',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_official' => 'boolean',
        ];
    }
}
