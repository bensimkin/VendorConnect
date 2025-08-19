<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Chatify\Traits\UUID;

class ChMessage extends Model
{
    use UUID;

    // protected $guard = [];
    protected $fillable = [
        "task_id",
        "sender_id",
        "message_text"
    ];
}
