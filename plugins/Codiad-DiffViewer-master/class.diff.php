<?php

    /*
    *  Copyright (c) Codiad & daeks, distributed
    *  as-is and without warranty under the MIT License. See 
    *  [root]/license.txt for more. This information must remain intact.
    */

require_once('../../common.php');

class Diff extends Common {
  
    //////////////////////////////////////////////////////////////////
    // PROPERTIES
    //////////////////////////////////////////////////////////////////
    
    //////////////////////////////////////////////////////////////////
    // METHODS
    //////////////////////////////////////////////////////////////////

    // -----------------------------||----------------------------- //

    //////////////////////////////////////////////////////////////////
    // Construct
    //////////////////////////////////////////////////////////////////

    public function __construct(){
    }
    
    //////////////////////////////////////////////////////////////////
    // Compare Files
    //////////////////////////////////////////////////////////////////
    
    public function compareFiles($file1, $file2){
        return $this->compare(file_get_contents($file1), file_get_contents($file2));
    }
    
    //////////////////////////////////////////////////////////////////
    // Compare Part & Utility methods
    // Created by Stephen Morley - http://stephenmorley.org/ and released under the terms of the CC0 1.0 Universal legal code
    //////////////////////////////////////////////////////////////////

    public function compare($string1, $string2){
        $start = 0;
        $sequence1 = preg_split('/\R/', $string1);
        $sequence2 = preg_split('/\R/', $string2);
        $end1 = count($sequence1) - 1;
        $end2 = count($sequence2) - 1;

        while ($start <= $end1 && $start <= $end2 && $sequence1[$start] == $sequence2[$start]){
          $start ++;
        }

        while ($end1 >= $start && $end2 >= $start && $sequence1[$end1] == $sequence2[$end2]){
          $end1 --;
          $end2 --;
        }

        $table = $this->computeTable($sequence1, $sequence2, $start, $end1, $end2);
        $partialDiff = $this->generatePartialDiff($table, $sequence1, $sequence2, $start);

        $diff = array();
        for ($index = 0; $index < $start; $index ++){
          $diff[] = array($sequence1[$index], 0);
        }
        while (count($partialDiff) > 0) $diff[] = array_pop($partialDiff);
        for ($index = $end1 + 1; $index < count($sequence1); $index ++){
          $diff[] = array($sequence1[$index], 0);
        }
        return $diff;
    }

    private function computeTable($sequence1, $sequence2, $start, $end1, $end2){
        $length1 = $end1 - $start + 1;
        $length2 = $end2 - $start + 1;
        $table = array(array_fill(0, $length2 + 1, 0));

        for ($index1 = 1; $index1 <= $length1; $index1 ++){
          $table[$index1] = array(0);
          for ($index2 = 1; $index2 <= $length2; $index2 ++){
            if ($sequence1[$index1 + $start - 1] == $sequence2[$index2 + $start - 1]){
              $table[$index1][$index2] = $table[$index1 - 1][$index2 - 1] + 1;
            }else{
              $table[$index1][$index2] = max($table[$index1 - 1][$index2], $table[$index1][$index2 - 1]);
            }
          }
        }
        return $table;
    }

    private function generatePartialDiff($table, $sequence1, $sequence2, $start){
        $diff = array();
        $index1 = count($table) - 1;
        $index2 = count($table[0]) - 1;

        while ($index1 > 0 || $index2 > 0){
          if ($index1 > 0 && $index2 > 0 && $sequence1[$index1 + $start - 1] == $sequence2[$index2 + $start - 1]){
            $diff[] = array($sequence1[$index1 + $start - 1], 0);
            $index1 --;
            $index2 --;
          }elseif ($index2 > 0 && $table[$index1][$index2] == $table[$index1][$index2 - 1]){
            $diff[] = array($sequence2[$index2 + $start - 1], 1);
            $index2 --;
          }else{
            $diff[] = array($sequence1[$index1 + $start - 1], -1);
            $index1 --;
          }
        }
        return $diff;
    }
}

?>
