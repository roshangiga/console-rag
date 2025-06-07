<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'directory_id',
        'file_path',
        'version',
        'type',
        'purpose',
        'metadata',
        'status',
        'created_by',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function directory()
    {
        return $this->belongsTo(Directory::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tags()
    {
        return $this->hasMany(DocumentTag::class);
    }
}
