<?php

namespace Modules\MPS\Models\Traits;

trait HasManySyncable
{
    public function syncHasMany($items, $relation, $field = 'id', $assoc = true, $children = [], $purchase = false)
    {
        if (!empty($items)) {
            $ndIds   = [];
            $records = [];
            $oItems  = $this->$relation;
            foreach ($items as $item) {
                $item_field = $assoc ? ($item[$field] ?? false) : $item;
                if ($item_field) {
                    $nItem         = $this->$relation()->updateOrCreate([$field => $item_field], $item);
                    $nItem->update = true;
                    $records[]     = $nItem;
                    $this->addChildRelations($this->id, $nItem, $children, $item, $purchase);
                    // if (!empty($children)) {
                    //     foreach ($children as $child) {
                    //         if ($child['relation'] == 'serials' && $purchase) {
                    //             $nItem->createSerials($this->id, $item['serials']);
                    //         } else {
                    //             if ($child['sync']) {
                    //                 $rel = $child['relation'];
                    //                 $nItem->$rel()->sync($item[$rel] ?? []);
                    //             } else {
                    //                 $nItem->syncHasMany($item[$child['relation']], $child['relation'], $child['field'], $child['assoc']);
                    //             }
                    //         }
                    //     }
                    // }
                    $ndIds[] = $item_field;
                } else {
                    $new       = $this->$relation()->create($item);
                    $records[] = $new;
                    $ndIds[]   = $new->$field;
                    $this->addChildRelations($this->id, $new, $children, $item, $purchase);
                    // if ($purchase && $new) {
                    //     $new->createSerials($this->id, $item['serials']);
                    // }
                }
            }

            $oItems->filter(function ($item) use ($ndIds, $field) {
                if (!in_array($item->$field, $ndIds)) {
                    return $item;
                }
            })->map(function ($item) {
                return $item->delete();
            });
            return $records;
        }
    }

    private static function addChildRelations($mId, $model, $children, $item, $purchase)
    {
        if (!empty($children)) {
            foreach ($children as $child) {
                if ($child['relation'] == 'serials' && $purchase) {
                    $model->createSerials($mId, $item['serials'] ?? []);
                } else {
                    if ($child['sync']) {
                        $rel = $child['relation'];
                        $model->$rel()->sync($item[$rel] ?? []);
                    } else {
                        $model->syncHasMany($item[$child['relation']], $child['relation'], $child['field'], $child['assoc']);
                    }
                }
            }
        }
    }
}
