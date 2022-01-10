<?php

namespace App;

use Ramsey\Uuid\Uuid;
use App\Helpers\Notifiable;
use Modules\MPS\Models\Customer;
use Modules\MPS\Models\Supplier;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Jetstream\HasProfilePhoto;
use Spatie\Permission\Traits\HasRoles;
use Modules\MPS\Models\Traits\ActivityTrait;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements Authorizable, MustVerifyEmail
{
    use ActivityTrait;
    use HasApiTokens;
    use HasProfilePhoto;
    use HasRoles;
    use Notifiable;
    use TwoFactorAuthenticatable;

    public $incrementing = false;

    protected $fillable = ['name', 'email', 'username', 'password', 'avatar', 'phone', 'profile_photo_path', 'active'];

    protected $hidden = ['password', 'remember_token'];

    protected $keyType = 'string';

    public function actingUser()
    {
        if (!session()->has('impersonate')) {
            return null;
        }

        $user = user(session('impersonate'));
        return [
            'name'        => $user->name,
            'avatar'      => $user->avatar,
            'username'    => $user->username,
            'location_id' => $user->location_id,
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function isImpersonating()
    {
        return session()->has('impersonate');
    }

    public function logActivity($msg)
    {
        log_activity(
            $msg,
            [
                'user' => [
                    'name'     => $this->name,
                    'email'    => $this->email,
                    'username' => $this->username,
                    'phone'    => $this->phone,
                    'active'   => $this->active,
                ],
            ],
            $this
        );
    }

    public function scopeEmployee($query)
    {
        return $query->where('employee', 1);
    }

    public function scopeOfCustomer($query, $customer_id)
    {
        return $query->where('customer_id', $customer_id);
    }

    public function scopeOfSupplier($query, $supplier_id)
    {
        return $query->where('supplier_id', $supplier_id);
    }

    public function setImpersonating()
    {
        if ($this->active && $this->can_impersonate) {
            session()->put('impersonate', $this->id);
        }
    }

    public function stopImpersonating()
    {
        session()->forget('impersonate');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = Uuid::uuid4()->toString();
        });
    }
}
