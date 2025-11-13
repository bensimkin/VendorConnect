<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'workspace_id',
        'user_id',
        'title',
        'expense_type_id',
        'amount',
        'note',
        'expense_date',
        'created_by'
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
