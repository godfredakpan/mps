<?php

namespace Modules\MPS\Events;

use Modules\MPS\Models\User;
use Illuminate\Queue\SerializesModels;
use Modules\MPS\Models\RegisterRecord;

class RegisterRecordEvent
{
    use SerializesModels;

    public $register_record;

    public $user;

    public function __construct(RegisterRecord $register_record, User $user)
    {
        $this->user            = $user;
        $this->register_record = $register_record;
    }
}
