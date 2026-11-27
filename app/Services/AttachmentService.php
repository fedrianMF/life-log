<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\Event;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentService
{
    /**
     * @param  array<int, UploadedFile>  $photos
     * @param  array<int, string>  $storedPhotoPaths
     */
    public function storeEventAttachments(
        Event $event,
        array $photos,
        array &$storedPhotoPaths,
    ): void {
        foreach ($photos as $photo) {
            $storedPhotoPath = $photo->store("attachments/{$event->user_id}", 'local');

            if (! is_string($storedPhotoPath)) {
                throw new RuntimeException('The event photo could not be stored.');
            }

            $storedPhotoPaths[] = $storedPhotoPath;
            $event->attachments()->create([
                'path' => $storedPhotoPath,
                'mime' => $photo->getMimeType() ?? 'application/octet-stream',
                'size' => (int) $photo->getSize(),
            ]);
        }
    }

    /** @param array<int, int> $attachmentIds */
    public function deleteEventAttachments(Event $event, array $attachmentIds): void
    {
        if ($attachmentIds === []) {
            return;
        }

        $event->attachments()
            ->whereKey($attachmentIds)
            ->get()
            ->each->delete();
    }

    /** @param array<int, string> $filePaths */
    public function deleteStoredFiles(array $filePaths): void
    {
        Storage::disk('local')->delete($filePaths);
    }

    public function streamAttachment(Attachment $attachment): StreamedResponse
    {
        $privateDisk = Storage::disk('local');

        abort_unless($privateDisk->exists($attachment->path), 404);

        return $privateDisk->response($attachment->path, null, [
            'Content-Type' => $attachment->mime,
            'Content-Disposition' => 'inline',
        ]);
    }
}
