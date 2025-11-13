<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseType extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'workspace_id',
        'title',
        'description'
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class)->where('expenses.workspace_id', session()->get('workspace_id'));
    }
}
