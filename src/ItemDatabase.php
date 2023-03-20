<?php

namespace MTNewton\IdleClansUtils;

use Illuminate\Support\Collection;

class ItemDatabase {

    protected Collection $data; 

    public static function load(Collection $data) 
    {
        return new static($data);
    }

    public function __construct(Collection $data)
    {

        $this->data = $data->map(function ($item) {

            return [
                'name' => $item[2],
                'skill' => $item[0],
                'quantity' => $item[1],
                'time' => $item[3],
                'valueEach' => $item[4],
                'materials' => $item[5],
            ];
        });
        $this->calculate();
    }

    protected function calculate()
    {
        $d = new Collection($this->data->toArray());

        $this->data = $this->data->map(function ($item) use ($d){
            $calc = $this->fillIn($item, $d);
            $item['materialsTree'] = $calc['tree'] ?? [];
            $item['materialsTreeText'] = $calc['treeText'] ?? "";
            $item['materialsRaw'] = $calc['raw'] ?? [];
            $item['materialsTime'] = $calc['time'] ?? 0;
            return $item;
        })->map(function ($item) {
            $item['value'] = $item['valueEach'] * $item['quantity'];
            $item['totalTime'] = $item['time'] + $item['materialsTime'];
            $item['gps'] = $item['value'] / ($item['totalTime']);
            return $item;
        });
    }

    protected function fillIn(array $item, Collection $data, int $multiplier = 1, int $depth = 0)
    {
        
        $result = [
            'tree' => [],
            'treeText' => "",
            'raw' => [],
            'time' => 0,
        ];
        
        $materialsData = $data->whereIn('name', collect($item['materials'])->pluck(1))->all();
        $i = 0;
        foreach ($materialsData as $materialData) {
            $i++;
            $amountNeeded = collect($item['materials'])->where(1, $materialData['name'])->first()[0] * $multiplier;
            $calc = $this->fillIn($materialData, $data, $amountNeeded, $depth + 1);

            array_push($result['tree'], [$materialData['quantity'] * $amountNeeded, $materialData['name'], $calc['tree']]);
            $line = "&boxur;";
            if ((count($materialsData) > 1) && ($depth > 0) && ($i < count($materialsData))) $line = "&boxvr;";
            $result['treeText'] .= str_repeat("&nbsp;&nbsp;&nbsp;{$line}&nbsp;", $depth) . ($materialData['quantity'] * $amountNeeded) . "x {$materialData['name']}<br />" . $calc['treeText'];

            foreach ($calc['raw'] as $calcRawName => $calcRawCount) {
                if (!array_key_exists($calcRawName, $result['raw'])) $result['raw'][$calcRawName] = 0;
                $result['raw'][$calcRawName] += $calcRawCount;
            }
            if (!$materialData['materials']) {
                if (!array_key_exists($materialData['name'], $result['raw'])) $result['raw'][$materialData['name']] = 0;
                $result['raw'][$materialData['name']] += $materialData['quantity'] * $amountNeeded;
            }

            $result['time'] += $calc['time'] + $materialData['time'] * $amountNeeded;

        }
        
        return $result;
    }

    public function getData()
    {
        return $this->data;
    }
}