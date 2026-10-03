<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Brother, Sister, Deacon... (Church Setup > Member Titles). */
class MemberTitle extends Model
{
    protected $fillable = ['name', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'bool', 'sort_order' => 'int'];

    public function members()
    {
        return $this->hasMany(Member::class, 'title_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Title from an Excel cell: the name, ignoring case and a trailing dot,
     * or a common short form (Bro, Sis, Dcn, Dr...). Null = not found.
     */
    public static function match(?string $value): ?self
    {
        $value = mb_strtolower(rtrim(trim((string) $value), '.'));
        if ($value === '') {
            return null;
        }

        $aliases = ['bro' => 'brother', 'br' => 'brother', 'sis' => 'sister', 'sr' => 'sister', 'dcn' => 'deacon', 'dcns' => 'deaconess', 'pst' => 'pastor', 'ps' => 'pastor', 'evang' => 'evangelist', 'eld' => 'elder'];
        $value = $aliases[$value] ?? $value;

        return static::whereRaw('LOWER(name) = ?', [$value])->first();
    }
}
