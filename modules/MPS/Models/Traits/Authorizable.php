<?php

namespace Modules\MPS\Models\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Request;

trait Authorizable
{
    private $abilities = [
        'index'             => 'read',
        'show'              => 'read',
        'edit'              => 'update',
        'update'            => 'update',
        'create'            => 'create',
        'store'             => 'create',
        'destroy'           => 'delete',
        'destroy-many'      => 'delete',
        'delete'            => 'delete',
        'void'              => 'delete',
        'email'             => 'email',
        'download'          => 'read',
        'change-password'   => 'update',
        'delete-attachment' => 'delete',
        'account'           => 'transaction',
        'account-table'     => 'transaction',
        'customer'          => 'transaction',
        'customer-table'    => 'transaction',
        'supplier'          => 'transaction',
        'supplier-table'    => 'transaction',
        'payments'          => 'payments', // check the sale/purchase payments
    ];

    private $allowed = [
        'read-customer',
        'read-supplier',
        'read-profile',
        'update-profile',
    ];

    public function callAction($method, $parameters)
    {
        $ability = $this->getAbility($method);
        if ($ability && !Str::contains($ability, 'search')) {
            if (!in_array($ability, $this->allowed)) {
                $this->authorize($ability);
            }
        }

        return parent::callAction($method, $parameters);
    }

    public function getAbility($method)
    {
        $routeName = explode('.', Request::route()->getName());
        if (isset($routeName[1]) && $routeName[1] == 'report') {
            return Str::kebab(Str::camel($routeName[0])) . '-report';
        }
        $action = Arr::get($this->getAbilities(), Str::kebab($method));
        return $action ? Str::kebab($action . '-' . $routeName[0]) : null;
    }

    public function setAbilities($abilities)
    {
        $this->abilities = $abilities;
    }

    private function getAbilities()
    {
        return $this->abilities;
    }
}
