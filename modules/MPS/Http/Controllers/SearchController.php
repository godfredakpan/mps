<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\Item;

class SearchController extends Controller
{
    public $limit = 10;

    public function accounts(Request $request)
    {
        if ($request->q) {
            return \Modules\MPS\Models\Account::search($request->input('q'))
                ->take($this->limit)->get(['id as value', 'name as label']);
        }
        return \Modules\MPS\Models\Account::all(['id as value', 'name as label']);
    }

    public function allCategories(Request $request)
    {
        if ($request->q) {
            return \Modules\MPS\Models\Category::search($request->input('q'))
                ->parents()->with('children')->take($this->limit)->get();
        }
        return \Modules\MPS\Models\Category::parents()->with('children')->get();
    }

    public function brands(Request $request)
    {
        if ($request->q) {
            return \Modules\MPS\Models\Brand::search($request->input('q'))->take($this->limit)->get();
        }
        return \Modules\MPS\Models\Brand::all();
    }

    public function categories(Request $request)
    {
        if ($request->q) {
            return \Modules\MPS\Models\Category::search($request->input('q'))
                ->parents()->take($this->limit)->get(['id as value', 'name as label']);
        }
        return \Modules\MPS\Models\Category::parents()->get(['id as value', 'name as label']);
    }

    public function customerGroups(Request $request)
    {
        if ($request->q) {
            return \Modules\MPS\Models\CustomerGroup::search($request->input('q'))
                ->take($this->limit)->get(['id as value', 'name as label', 'code']);
        }
        return \Modules\MPS\Models\CustomerGroup::all(['id as value', 'name as label', 'code']);
    }

    public function customers(Request $request)
    {
        if ($request->q) {
            $customers = \Modules\MPS\Models\Customer::search($request->input('q'))->take($this->limit)->with('customerGroup')->get();
            return transform_select($customers, ['id' => 'value', 'name' => 'label', 'points', 'state', 'country', 'customer_group']);
        }
        return transform_select(
            \Modules\MPS\Models\Customer::with('customerGroup')->all(),
            ['id' => 'value', 'name' => 'label', 'points', 'state', 'country', 'customer_group']
        );
    }

    public function halls(Request $request)
    {
        if ($request->q) {
            return \Modules\MPS\Models\Hall::search($request->input('q'))->take($this->limit)->get(['id as value', 'title as label', 'code']);
        }
        return \Modules\MPS\Models\Hall::all(['id as value', 'title as label', 'code']);
    }

    public function ingredients(Request $request)
    {
        if ($request->q) {
            return \Modules\MPS\Models\ingredient::search($request->input('q'))
                ->take($this->limit)->get();
        }
        return \Modules\MPS\Models\ingredient::all();
    }

    public function items(Request $request)
    {
        $items = Item::query();
        if ($request->scale) {
            if ($item = $items->search($request->input('q'))->withAll()->first()) {
                return $item;
            }
            $request->replace(['q' => $request->input('barcode')]);
        } elseif ($request->select) {
            $items->search($request->input('q'))
                ->selectRaw("id as value, CONCAT(name, ' (', code, ')') as label");
        } elseif ($request->pos == 1) {
            $items->search($request->input('q'))->pos()->withAll()->orderBy('name');
            if (!$request->q && $request->category) {
                $items->whereHas(
                    'categories',
                    fn ($q) => $q->where('id', $request->input('category'))
                );
            }
        } else {
            $items->search($request->input('q'))
                ->selectRaw("id, name, code, alt_name, CONCAT(name, ' (', code, ')') as label");
            if ($request->cost == 'yes') {
                $items->selectRaw("id, name, code, alt_name, cost, CONCAT(name, ' (', code, ')') as label");
            }
            if ($request->type) {
                $types = explode(',', $request->input('type'));
                $items->ofType($types);
            }
            if ($request->with = 'variations') {
                $items->with('variations');
            }
        }

        return $items->take($this->limit)->get();
    }

    public function itemsWP(Request $request)
    {
        $items = Item::selectRaw("id, name, code, alt_name, CONCAT(name, ' (', code, ')') as label")
            ->search($request->input('q'));
        if ($request->type) {
            $types = explode(',', $request->input('type'));
            $items = $items->ofType($types);
        }
        // return $items->withPurchase()->take($this->limit)->get();
        return $items->take($this->limit)->get();
    }

    public function itemsWT(Request $request)
    {
        return Item::search($request->input('q'))
            ->withTransfer($request->input('location'))->take($this->limit)->get();
    }

    public function locations(Request $request)
    {
        if ($request->q) {
            return \Modules\MPS\Models\Location::search($request->input('q'))
                ->take($this->limit)->get(['id as value', 'name as label', 'state', 'country']);
        }
        return \Modules\MPS\Models\Location::all(['id as value', 'name as label', 'state', 'country']);
    }

    public function modifiers(Request $request)
    {
        if ($request->q) {
            return \Modules\MPS\Models\Modifier::search($request->input('q'))
                ->take($this->limit)->get(['id as value', 'title as label']);
        }
        return \Modules\MPS\Models\Modifier::all(['id as value', 'title as label']);
    }

    public function roles()
    {
        if (user()->hasRole('super')) {
            return \App\Role::all(['id as value', 'name as label']);
        }
        return [];
    }

    public function suppliers(Request $request)
    {
        if ($request->q) {
            return \Modules\MPS\Models\Supplier::search($request->input('q'))
                ->take($this->limit)->get(['id as value', 'name as label', 'state', 'country']);
        }
        return \Modules\MPS\Models\Supplier::all(['id as value', 'name as label', 'state', 'country']);
    }

    public function taxes(Request $request)
    {
        if ($request->q) {
            return \Modules\MPS\Models\Tax::search($request->input('q'))->take($this->limit)->get();
        }
        return \Modules\MPS\Models\Tax::all();
    }

    public function units()
    {
        return \Modules\MPS\Models\Unit::base()->with('subunits')->get();
    }

    public function users(Request $request)
    {
        $users = \Modules\MPS\Models\User::query();
        $users->employee()->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['customer', 'supplier']));

        if ($request->has('roles')) {
            $r     = 0;
            $roles = is_array($request->roles) ? $request->roles : array_map('trim', explode(',', $request->roles));
            foreach ($roles as $role) {
                $users->{$r ? 'orWhereHas' : 'whereDoesntHave'}('roles', fn ($q) => $q->where('name', $role));
                $r++;
            }
        }

        return $users->get(['id as value', 'name as label']);
    }
}
