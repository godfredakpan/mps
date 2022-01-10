<?php

namespace Modules\MPS\Http\Controllers;

use App\Media;
use Bnb\Laravel\Attachments\Attachment;

class MediaController extends Controller
{
    public function deleteAttachment(Attachment $attachment)
    {
        $attachment->delete();
        return response(['success' => true]);
    }

    public function destroy(Media $media)
    {
        $media->delete();
        return response(['success' => true]);
    }

    public function show(Media $media)
    {
        return $media;
    }
}
