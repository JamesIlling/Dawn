<?php
    class Common
    {
        private $database;

        /**
         * Short Create the common class which contains most of the validation
         * @param $database PDO The connection too the database to use.
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
         *//*
        public function Recode($urlEncodedString)
        {
            $decoded = urldecode($urlEncodedString);

            return $this->database->quote($decoded);
        }*/

        /**
         * Short EEnsure that the provided string is a valid number
         * @param $name string the name value to use in the error message
         * @param $value string The string to check is a number.
         * @param $error ErrorResponse The error for the current statement.
         * @return array The data if any which was returned
         */
        public function ValidateNumber($name, $value, $error)
        {
            if (!is_numeric($value))
            {
                $error->AddNewError("Error", "the value of $name is not a valid number $value");
            }
        }

        /**
         * Short Execute a command against the database.
         * @param $sql string the SQL statement to execute
         * @param $data array The data required to compose the query
         * @param $error ErrorResponse The error for the current statement.
         * @return array The data if any which was returned
         */
        public function ExecuteCommand($sql,$data,$error)
        {
            try
            {
                $statement = $this->database->prepare($sql);
                $executed = $statement->execute($data);
                if ($executed)
                {
                    if (stristr($sql,'INSERT')!== false || stristr($sql,'UPDATE') !== false || stristr($sql,'DELETE'))
                    {
                        return array();
                    }
                    return $statement->fetchAll(PDO::FETCH_ASSOC);
                }

            }
            catch(PDOException $exception)
            {
                    $error->AddNewError("ERROR",$exception->getMessage());
            }
            return array();
        }
    }
?>