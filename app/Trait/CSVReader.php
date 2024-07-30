<?php

namespace App\Trait;

trait CSVReader
{
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
                    $data[$column] = $split_row[$columnIndex];
                }
                $list->add($data);
            }

        }catch (\Exception $exception){
            return false;
        }
        return $list;
    }
}
