<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function guardians()
    {
        return $this->hasMany(Guardian::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function scopeVisibleTo(Builder $q, User $user): Builder
    {
        if ($user->role === 'admin') {
            return $q;
        }
        if ($user->role === 'tutor') {
            return $q->whereHas('assignments', fn ($a) => $a->where('user_id', $user->id)->whereNull('ended_at'));
        }

        return $q->whereHas('guardians', fn ($g) => $g->where('email', $user->email));
    }
}
