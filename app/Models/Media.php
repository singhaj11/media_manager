<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $fillable = [
        'name',
        'alt_text',
        'caption',
        'description',
        'file_path',
        'mime_type',
        'size',
        'disk',
        'collection',
    ];
}
