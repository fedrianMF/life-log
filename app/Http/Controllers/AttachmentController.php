<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Services\AttachmentService;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function __construct(private readonly AttachmentService $attachmentService) {}

    public function show(Attachment $attachment): StreamedResponse
    {
        Gate::authorize('view', $attachment);

        return $this->attachmentService->streamAttachment($attachment);
    }
}
