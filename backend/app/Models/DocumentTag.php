<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DocumentTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'tag_name',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
