<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Directory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'parent_id',
        'created_by',
    ];

    public function parent()
    {
        return $this->belongsTo(Directory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Directory::class, 'parent_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function getFullPathAttribute()
    {
        $path = collect();
        $current = $this;
        
        while ($current) {
            $path->prepend($current->name);
            $current = $current->parent;
        }
        
        return $path->count() > 0 ? '/' . $path->implode('/') : '/';
    }
}
