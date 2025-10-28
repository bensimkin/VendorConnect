<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Chatify\Traits\UUID;

class ChMessage extends Model
{
    use UUID;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'id',
        'task_id',
        'sender_id',
        'message_text',
        'sent_at',
        'seen',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'seen' => 'boolean',
        'sent_at' => 'datetime',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
