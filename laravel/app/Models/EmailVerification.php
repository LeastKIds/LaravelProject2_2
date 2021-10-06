<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_verifications',
        'user_id',
        'event_name'
    ];

    public function user() {
        return $this -> hasOne(User::class);
    }
}
