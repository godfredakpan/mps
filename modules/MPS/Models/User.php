<?php

namespace Modules\MPS\Models;

use Carbon\Carbon;
use App\User as AppUser;
use Spatie\MediaLibrary\HasMedia;
use Modules\MPS\Models\Traits\TableTrait;
use Spatie\MediaLibrary\InteractsWithMedia;
use Modules\MPS\Models\Traits\HasSchemalessAttributes;

class User extends AppUser implements HasMedia
{
    use HasSchemalessAttributes;
    use InteractsWithMedia;
    use TableTrait;

    public static $searchable = [
        'id', 'name', 'email', 'phone', 'active', 'username', 'password',
        'location.name', 'view_all', 'edit_all', 'bulk_actions', 'extra_attributes',
    ];

    protected $casts = ['extra_attributes' => 'array'];

    protected $fillable = [
        'name', 'email', 'phone', 'active', 'username', 'password', 'customer_id', 'vendor_id',
        'location_id', 'view_all', 'edit_all', 'bulk_actions', 'extra_attributes', 'avatar',
    ];

    protected $guard_name = 'web';

    public function addFile($file, $name)
    {
        return $this->addMedia($file)->withCustomProperties(['name' => $name])->preservingOriginal()->toMediaCollection('files', 'local');
    }

    public function addFiles($files)
    {
        $media = [];
        foreach ($files as $file) {
            if (!empty($file['file']) && !empty($file['name'])) {
                // $file['file']->store('users');
                $media[] = $this->addFile($file['file'], $file['name']);
            }
        }
        return !empty($media);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function del()
    {
        if ($this->sales()->exists() || $this->purchases()->exists() || $this->incomes()->exists() || $this->expenses()->exists() || $this->quotations()->exists() || $this->returnOrders()->exists() || $this->timeClocks()->exists()) {
            return false;
        }

        return $this->delete();
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function getAllPermissionsAttribute()
    {
        return $this->getAllPermissions()->pluck('name');
    }

    public function getMorphClass()
    {
        return parent::class;
    }

    public function incomes()
    {
        return $this->hasMany(Income::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class);
    }

    public function meta()
    {
        return $this->hasMany(UserMeta::class)->mps();
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function registerRecords()
    {
        return $this->hasMany(RegisterRecord::class);
    }

    public function returnOrders()
    {
        return $this->hasMany(ReturnOrder::class);
    }

    public function salaries()
    {
        return $this->hasMany(Salary::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function saveSettings($settings, $profile = false)
    {
        if (!empty($settings)) {
            $allowed = allowed_usermeta($profile);
            foreach ($settings as $key => $value) {
                if (in_array($key, $allowed)) {
                    $this->meta()->updateOrCreate(['meta_key' => $key], ['meta_key' => $key, 'meta_value' => $value]);
                }
            }
        }
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function timeClocks()
    {
        return $this->hasMany(TimeClock::class);
    }

    public function workHours($month)
    {
        $month = Carbon::parse($month);
        if (config('database.default') == 'mysql') {
            return optional($this->timeClocks()
                ->selectRaw('ROUND(SUM(TIME_TO_SEC(TIMEDIFF(`in_time`, `out_time`)) / 3600)) AS work_hours')
                ->whereBetween('out_time', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->first())->work_hours;
        } elseif (config('database.default') == 'sqlite') {
            return optional($this->timeClocks()
                ->selectRaw('ROUND(SUM((julianday(out_time) - julianday(in_time)) * 24)) AS work_hours')
                ->whereBetween('out_time', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->first())->work_hours;
        }
    }

    public function workHoursSalary($month)
    {
        $month = Carbon::parse($month);
        if (config('database.default') == 'mysql') {
            return optional($this->timeClocks()
                ->selectRaw('ROUND(ROUND(SUM(TIME_TO_SEC(TIMEDIFF(`in_time`, `out_time`)) / 3600)) * rate) AS salary')
                ->whereBetween('out_time', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->groupBy('rate')->get())->sum('salary');
        } elseif (config('database.default') == 'sqlite') {
            return optional($this->timeClocks()
                ->selectRaw('ROUND(ROUND(SUM((julianday(out_time) - julianday(in_time)) * 24)) * rate) AS salary')
                ->whereBetween('out_time', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->groupBy('rate')->get())->sum('salary');
        }
    }
}
