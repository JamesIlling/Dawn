<?php
    class Common
    {
        private $database;

        /**
         * Short Create the common class which contains most of the validation
         * @param $database mysqli The connection too the database to use.
         * @return Common the new common validation.
         */
        public function __construct($database)
        {
            $this->database = $database;
        }

        /**
         * Short Validate that the length of the string is suitable for the database
         * @param $name   string the name of the variable we are validating
         * @param $string string the string we are validating
         * @param $length int the maximum length of the string
         * @param $error  ErrorResponse The accumulated errors for this call.
         * @return bool true if the item is valid.
         */
        function ValidateLength($name, $string, $length, $error)
        {
            if (strlen($string) > $length)
            {
                $error->AddNewError("Error", "The ($name) value ($string) is too long the maximum length is $length");

                return false;
            }

            return true;
        }

        /**
         * Short recode a string from UrlEncoded to database encoded.
         * @param $urlEncodedString string the text to recode
         * @return string the recoded string.
         */
        public function Recode($urlEncodedString)
        {
            $decoded = urldecode($urlEncodedString);

            return $this->database->real_escape_string($decoded);
        }

        public function ValidateNumber($name, $value, $error)
        {
            if (!is_numeric($value))
            {
                $error->AddNewError("Error", "the value of $name is not a valid number $value");
            }
        }
    }

?>