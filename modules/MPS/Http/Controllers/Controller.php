<?php

namespace Modules\MPS\Http\Controllers;

use Modules\MPS\Models\Traits\Authorizable;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use Authorizable;
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;
}
