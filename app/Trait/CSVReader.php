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
}
