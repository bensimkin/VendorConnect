<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Crypt;

class ClientCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'title',
        'url',
        'username',
        'password',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'password',
    ];

    protected $appends = [
        'decrypted_password',
    ];

    /**
     * Get the client that owns the credential.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Encrypt the password before saving.
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Crypt::encryptString($value);
    }

    /**
     * Get the decrypted password.
     */
    public function getDecryptedPasswordAttribute()
    {
        try {
            return Crypt::decryptString($this->attributes['password']);
        } catch (\Exception $e) {
            return '***ENCRYPTED***';
        }
    }

    /**
     * Get the decrypted password for display (only when explicitly requested).
     */
    public function getPassword()
    {
        return $this->decrypted_password;
    }
}
