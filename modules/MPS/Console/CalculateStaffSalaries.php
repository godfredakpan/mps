<?php

namespace Modules\MPS\Console;

use Modules\MPS\Models\User;
use Modules\MPS\Models\Salary;
use Illuminate\Console\Command;
use Modules\MPS\Models\Activity;

class CalculateStaffSalaries extends Command
{
    protected $description = 'Calculate staff salaries';

    protected $signature = 'staff:salaries';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $users  = User::withoutGlobalScopes()->employee()->get();
        $end    = now()->subMonth()->endOfMonth()->format('Y-m-d');
        $start  = now()->subMonth()->startOfMonth()->format('Y-m-d');
        $salary = ['date' => now()->format('Y-m-d'), 'advance' => 0, 'type' => 'salary', 'month' => $start, 'details' => 'Calculating salary with command.'];
        $users->each(function ($user) use ($salary, $start, $end) {
            if ($user->created_at->betweenIncluded($start, $end)) {
                $already = $user->salaries()->withoutGlobalScopes()->salary()->notAdvance()
                ->whereBetween('date', [now()->startOfMonth()->format('Y-m-d'),  now()->endOfMonth()->format('Y-m-d')])->exists();
                if ($already) {
                    activity('Salary')->log("User ({$user->name}) salary is already created.");
                    $this->error("User ({$user->name}) salary is already created.");
                } else {
                    $advance = Salary::withoutGlobalScopes()->orWhere(function ($query) use ($start, $end) {
                        $query->whereDate('date', '>=', $start)->whereDate('date', '<=', $end);
                    })->where('user_id', $user->id)->advance()->get();
                    $salary['user_id'] = $user->id;
                    $salary['staff_id'] = $user->id;
                    $salary['account_id'] = mps_config('default_account');
                    $salary['work_hours'] = $user->workHours($start);
                    $salary_amount = usermeta($user->id, 'salary');
                    $salary['work_hours_salary'] = $user->workHoursSalary($start);
                    if (!$salary_amount) {
                        $salary_amount = $salary['work_hours_salary'];
                    }
                    if ($salary_amount && $advance->isNotEmpty()) {
                        $advance_amount = $advance->sum('amount');
                        $details = "User ({$user->name}) salary after advance created {($salary_amount - $advance_amount)}";
                        $salary['amount'] = $salary_amount - $advance_amount;
                        $salary['details'] = $details;
                        $salary = new Salary($salary);
                        $salary->disableLogging();
                        $salary->save();
                        $advance->each->update(['settled_on' => now()]);
                        activity('Salary')->performedOn($salary)->log($details);
                        $this->info($details);
                    } elseif ($salary_amount) {
                        $details = "User ({$user->name}) salary created {$user->salary}";
                        $salary['amount'] = $salary_amount;
                        $salary['details'] = $details;
                        $salary['account_id'] = mps_config('default_account');
                        $salary = new Salary($salary);
                        $salary->disableLogging();
                        $salary->save();
                        activity('Salary')->performedOn($salary)->log($details);
                        $this->info($details);
                    } else {
                        activity('Salary')->log("User ({$user->name}) do not have salary.");
                        $this->info("User ({$user->name}) do not have salary.");
                    }
                }
            } else {
                $end_date = now()->subMonth()->endOfMonth()->toDateTimeString();
                activity('Salary')->log("User ({$user->name}) is created after {$end_date}.");
                $this->error("User ({$user->name}) is created after {$end_date}.");
            }
        });
    }
}
