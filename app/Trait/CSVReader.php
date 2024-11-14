<?php

namespace App\Trait;

use Illuminate\Support\Collection;

trait CSVReader
{
    /**
     * @param $string
     * @return false|Collection
     */
    public function csvToCollection($string)
    {
        try{
            $rows = explode("\n", trim($string));
            $columns = explode(',', $rows[0]);
            $list = collect();
            foreach ($rows as $rowIndex => $row){
                if($rowIndex == 0) continue;
                $data = [];
                foreach ($columns as $columnIndex => $column){
                    if(empty($row) && $rowIndex == count($rows)-1){
                        break;
                    }
                    $split_row = explode(',', $row);
                    $data[trim($column)] = trim($split_row[$columnIndex]);
                }
                $list->add($data);
            }

        }catch (\Exception $exception){
            return false;
        }
        return $list;
    }

    /**
     * @param $collection
     * @return false|string
     */
    public function collectionToCSV($collection, $unset = [])
    {
        $result = '';
        try{
            $keys = $collection[0]->toArray();
            if(count($unset) > 0){
                foreach ($unset as $item){
                    unset($keys[$item]);
                }
            }
            $keys = array_keys($keys);
            $result .= join(',', $keys);
            for ($i=0;$i< count($collection);$i++){
                $row = $collection[$i];
                $row_array =  ($row->toArray());
                if(count($unset) > 0){
                    foreach ($unset as $item){
                        unset($row_array[$item]);
                    }
                }
                $tmp = join(',', array_values($row_array));
                $result .= "\n$tmp";
            }

        }catch (\Exception $exception){
            return false;
        }
        return $result;
    }
}
