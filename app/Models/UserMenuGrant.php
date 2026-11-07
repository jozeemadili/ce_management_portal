<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A menu or sub-menu given to a user (Security & Settings > Access Control). */
class UserMenuGrant extends Model
{
    protected $fillable = ['user_id', 'menu_key', 'granted_by'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
