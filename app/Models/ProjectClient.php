<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectClient extends Model
{
    public $table = 'client_project';

    protected $fillable = [
        'project_id', 'client_id'
    ];
}
