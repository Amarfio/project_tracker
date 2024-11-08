<?php

  //method to format date
  function formatDate($p_date){
    $long_date_v = strtotime($p_date);
     $newDate = date("d-M-Y", $long_date_v);
     return $newDate;
  }
  
?>