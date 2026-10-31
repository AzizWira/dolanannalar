<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class TutorPhotoController extends Controller
{
    public function __invoke(User $tutor)
    {
        abort_unless($tutor->role === 'tutor' && $tutor->active && $tutor->public_profile && $tutor->photo, 404);

        return Storage::disk('public')->response($tutor->photo, null, ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'public, max-age=3600']);
    }
}
