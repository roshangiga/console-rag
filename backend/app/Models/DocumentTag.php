<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
