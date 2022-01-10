<?php

namespace Modules\MPS\Policies;

use Modules\MPS\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RecordOwnerUpdatePolicy
{
    use HandlesAuthorization;

    public function update(User $user, $model)
    {
        return $user->id === $model->user_id || !!$user->edit_all;
    }
}
