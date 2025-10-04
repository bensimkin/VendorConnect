<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'file_path',
        'file_category',
        'file_name',
        'file_type',
        'file_size',
        'description',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}

