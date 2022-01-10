<?php

namespace Modules\MPS\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\MPS\Events\AttachmentEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AttachmentEventListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function failed(AttachmentEvent $event, $exception)
    {
        Log::error('AttachmentEventListener failed!', [
            'Error' => $exception,
            'model' => $event->model,
        ]);
    }

    public function handle(AttachmentEvent $event)
    {
        $event->model->moveAttachments($event->attachments);
    }
}
