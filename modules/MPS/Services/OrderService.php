<?php

namespace Modules\MPS\Services;

use Modules\MPS\Models\Order;
use Modules\MPS\Models\Purchase;
use Illuminate\Support\Facades\DB;
use Modules\MPS\Models\ReturnOrder;
use Modules\MPS\Models\RecurringSale;
use Modules\MPS\Models\StockAdjustment;

class OrderService
{
    protected $data;

    protected $form;

    protected $model;

    protected $updating;

    public function __construct($form, $model, $updating = false)
    {
        $this->form     = $form;
        $this->model    = $model;
        $this->updating = $updating;
    }

    public function getData()
    {
        return $this->data;
    }

    public function process()
    {
        $purchase   = ($this->model instanceof Purchase);
        $adjustment = ($this->model instanceof StockAdjustment);
        if ($this->model instanceof ReturnOrder) {
            $purchase = $this->model->type == 'purchase';
        }
        $this->data = (new OrderDataService())($this->form, $purchase, $adjustment);
        // dd($this->data['items'][0]['portions'][0]['abb6c2d6-8587-4411-89bd-15b5509f3d92'], $this->data['items'][0]['portions'][1]['abb6c2d6-8587-4411-89bd-15b5509f3d92']);
        return $this;
    }

    public function save()
    {
        return $this->transaction($this->data, $this->model, $this->updating);
    }

    public function transaction($data, $model, $updating = null)
    {
        return DB::transaction(function () use ($data, $model, $updating) {
            if ($updating) {
                $model->update($data);
            } else {
                $model->fill($data)->save();
            }
            $relation_children = [];
            $relation_children[] = ['field' => 'id', 'relation' => 'taxes', 'sync' => true, 'assoc' => false];
            $relation_children[] = ['field' => 'id', 'relation' => 'variations', 'sync' => true, 'assoc' => false];
            if (!($model instanceof RecurringSale)) {
                $relation_children[] = ['field' => 'id', 'relation' => 'serials', 'sync' => true, 'assoc' => false];
            }
            if (!($model instanceof Purchase) && !($model instanceof StockAdjustment)) {
                $relation_children[] = ['field' => 'id', 'relation' => 'portions', 'sync' => true, 'assoc' => false];
                $relation_children[] = ['field' => 'id', 'relation' => 'promotions', 'sync' => true, 'assoc' => false];
                $relation_children[] = ['field' => 'id', 'relation' => 'modifierOptions', 'sync' => true, 'assoc' => false];
            }
            $model->syncHasMany($data['items'], 'items', 'id', true, $relation_children, ($model instanceof Purchase));

            if ($data['taxes']) {
                $model->taxes()->sync($data['taxes']->toArray());
            }
            if (isset($data['pos']) && isset($data['oId'])) {
                Order::where('oId', $data['oId'])->delete();
            }
            return $model;
        });
    }
}
