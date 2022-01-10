<?php

namespace Modules\MPS\Models;

use Modules\MPS\Models\Traits\HasTaxes;
use Illuminate\Database\Eloquent\Builder;
use Modules\MPS\Models\Traits\OfLocation;
use Modules\MPS\Models\Traits\HasCategories;
use Modules\MPS\Models\Traits\HasAttachments;
use Modules\MPS\Models\Traits\HasSchemalessAttributes;

class Income extends Base
{
    use HasAttachments;
    use HasCategories;
    use HasSchemalessAttributes;
    use HasTaxes;
    use OfLocation;

    public $hasLocation = true;

    public $hasNumber = true;

    public $hasReference = true;

    public static $searchable = ['id', 'date', 'created_at', 'title', 'reference', 'amount', 'details', 'account.name', 'location.name', 'categories.name', 'extra_attributes', 'number'];

    protected $casts = ['date' => 'date', 'extra_attributes' => 'array'];

    protected $fillable = ['date', 'title', 'amount', 'reference', 'details', 'account_id', 'user_id', 'extra_attributes'];

    protected $hidden = ['updated_at'];

    protected $with = ['attachments'];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function del()
    {
        return $this->delete();
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('mine', function (Builder $builder) {
            $user = auth()->user();
            if ($user && !$user->hasRole('super') && !$user->view_all) {
                return $builder->where('user_id', $user->id);
            }
        });
        static::addGlobalScope('of_location', function (Builder $builder) {
            if ($location_id = session('location_id')) {
                return $builder->where('location_id', $location_id);
            }
        });
        static::deleting(function ($income) {
            $income->categories()->detach();
        });
    }
}
