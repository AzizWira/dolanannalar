<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['lesson_date' => 'date', 'published_at' => 'datetime'];
    }

    public function student()
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }

    public function tutor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    public function canRead(User $u): bool
    {
        return Student::visibleTo($u)->whereKey($this->student_id)->exists() && ($u->role !== 'parent' || $this->status === 'published');
    }

    public function canEdit(User $u): bool
    {
        return $u->role === 'admin' || ($u->role === 'tutor' && $u->id === $this->user_id && Student::visibleTo($u)->whereKey($this->student_id)->exists());
    }
}
