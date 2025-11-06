<?php

namespace Notigen\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TemplateNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subject',
        'body',
        'type',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
