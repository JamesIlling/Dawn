<?php
    class Error
    {
        public $TimeStamp;
        public $Message;
        public $Level;

        public function  __construct($level, $message)
        {
            $this->TimeStamp = new DateTime("NOW",new DateTimeZone('UTC'));
            $this->Message = $message;
            $this->Level = $level;
        }
    }
?>
