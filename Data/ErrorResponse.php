<?php
    require_once 'Error.php';

    class ErrorResponse
    {
        public $Errors = Array();

        public $valid = true;

        public function AddError($error)
        {
            array_push($this->Errors, $error);
            $this->valid = false;
        }

        public function AddNewError($level, $message)
        {
            $this->AddError(new Error($level, $message));
            $this->valid = false;
        }

        public function __toString()
        {
            $errorText = "";
            foreach ($this->Errors as $error)
            {
                $errorText = $errorText. $error->Message."\r\n";
            }

            return $errorText;
        }
    }

?>
