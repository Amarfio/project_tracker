<?php

    class DateCheck{
        protected $newDate;
        protected $oldDate;

        function __construct($newDate, $oldDate){
            $this->newDate = $newDate;
            $this->oldDate = $oldDate;
        }

        public function setStartDate($newDate){
            $this->newDate = $newDate;
        }

        public function setEndDate($oldDate){
            $this->oldDate = $oldDate;
        }

        public function checkForEarlierDate(){
            $newDateTime = strtotime($this->newDate);
            $oldDateTime = strtotime($this->oldDate);

            return $newDateTime < $oldDateTime;
        }
        public function checkForLaterDate(){
            $newDateTime = strtotime($this->newDate);
            $oldDateTime = strtotime($this->oldDate);
            
            return $newDateTime > $oldDateTime;
        }

        
    }
?>