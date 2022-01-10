<?php

namespace Modules\MPS\Services;

use Illuminate\Support\Facades\DB;

class DeliveryService
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

    public function process()
    {
        $this->data = (new DeliveryDataService)($this->form);

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
            // $data['items'] = $this->flatVariations($data['items']);
            $relation_children[] = ['field' => 'id', 'relation' => 'serials', 'sync' => true, 'assoc' => false];
            $relation_children[] = ['field' => 'id', 'relation' => 'variations', 'sync' => true, 'assoc' => false];
            $relation_children[] = ['field' => 'id', 'relation' => 'portions', 'sync' => true, 'assoc' => false];
            $relation_children[] = ['field' => 'id', 'relation' => 'modifierOptions', 'sync' => true, 'assoc' => false];

            $model->syncHasMany($data['items'], 'items', 'id', true, $relation_children);
            return $model;
        });
    }

    private function flatVariations($items)
    {
        foreach ($items as &$item) {
            $variations = [];
            if (!empty($item['variations'])) {
                foreach ($item['variations'] as $variation) {
                    foreach ($variation as $k => $v) {
                        $variations[$k] = $v;
                    }
                }
            }
            $item['variations'] = $variations;
        }
        return $items;
    }
}
