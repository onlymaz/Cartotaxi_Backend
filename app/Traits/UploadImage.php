<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait UploadImage
{
    /**
     * Image MIME types we accept on upload. Anything else is rejected
     * outright — this is the last line of defence after the FormRequest's
     * `mimes:` rule. Extensions are NOT trusted; we re-derive from the
     * detected MIME type before writing.
     */
    private static array $allowedImageMimes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];

    /** Maximum upload size: 4 MB. Form requests can apply a tighter cap. */
    private const MAX_UPLOAD_BYTES = 4 * 1024 * 1024;

    /**
     * Store an uploaded file under the `public` disk (resolved at runtime to
     * `storage/app/public/uploads/media/`, exposed to the web via the symlink
     * created by `php artisan storage:link`).
     *
     * Hardening compared to the previous implementation:
     *   - filename is unguessable random hex, so an attacker can't predict /
     *     read another user's upload via URL probing
     *   - extension is derived from the *server-detected* MIME, not from the
     *     client-supplied filename — a `.php` payload renamed to `.png` is
     *     rejected
     *   - never writes into `public/uploads/...` (where a misconfigured web
     *     server might execute PHP from an upload); writes into `storage/`
     *     and returns the public-disk relative path
     *   - explicit size cap as defence-in-depth against PHP/web-server limits
     *     being raised in the future
     *
     * @return string Path relative to the `public` disk (suitable for
     *                Storage::disk('public')->url($path)).
     * @throws \InvalidArgumentException if the upload is not an allowed image.
     */
    public function uploadImage(UploadedFile $file): string
    {
        if (!$file->isValid()) {
            throw new \InvalidArgumentException('Uploaded file is not valid.');
        }
        if ($file->getSize() > self::MAX_UPLOAD_BYTES) {
            throw new \InvalidArgumentException('Uploaded file exceeds the maximum allowed size.');
        }

        $mime = $file->getMimeType();
        if (!isset(self::$allowedImageMimes[$mime])) {
            throw new \InvalidArgumentException('Unsupported image type: ' . $mime);
        }

        $extension = self::$allowedImageMimes[$mime];
        $filename  = Str::random(40) . '.' . $extension;
        $directory = 'uploads/media';

        // storeAs on the `public` disk writes to storage/app/public/uploads/media/
        // and returns the relative path. The web URL is reachable via the
        // `php artisan storage:link` symlink only — the storage directory itself
        // is not publicly executable.
        $path = $file->storeAs($directory, $filename, 'public');

        return $path;
    }

    /**
     * Delete a previously-uploaded asset. Path must be a `public` disk
     * relative path — never a user-controlled absolute path (path-traversal).
     */
    public function deleteOne(?string $imagePath): void
    {
        if (empty($imagePath)) {
            return;
        }
        // Guard against absolute paths, path traversal, and any leading slash.
        $normalised = ltrim($imagePath, '/');
        if (str_contains($normalised, '..')) {
            return;
        }

        $disk = Storage::disk('public');
        if ($disk->exists($normalised)) {
            $disk->delete($normalised);
        }
    }
}
