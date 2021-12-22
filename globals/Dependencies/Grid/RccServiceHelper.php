<?php

/*
    Alphaland 2021
    This is responsible for communicating to the specified Webservice
    Nikita TODO: ALPHA-22 (Response Models for things that definitely have a known response (like specific SOAP actions))
                 https://jira.mfdlabs.local/browse/ALPHA-22
*/

namespace Alphaland\Grid {

    use stdClass;
    use PDO;

    class RccServiceHelper
    {
        private string $ServiceIp;

        function __construct(string $ServiceIp)
        {
            $this->ServiceIp = $ServiceIp;
        }

        private function LogFault($soap, $description)
        {
            $theFault = print_r($soap, TRUE); //soap object fault to human readable string (ghetto?)
            $fault = $GLOBALS['pdo']->prepare("INSERT INTO soap_faults(description, fault, whenOccurred) VALUES(:jd, :f, UNIX_TIMESTAMP())");
            $fault->bindParam(":jd", $description, PDO::PARAM_STR);
            $fault->bindParam(":f", $theFault, PDO::PARAM_STR);
            $fault->execute();
        }

        private function SoapCallService(string $name, array $arguments = [])
        {
            $soapcl = new \SoapClient($GLOBALS['RCCwsdl'], ["location" => "http://" . $this->ServiceIp, "uri" => "http://roblox.com/", "exceptions" => false]);
            $soapcl = $soapcl->{$name}($arguments); //thanks BrentDaMage didnt know u can do this
            if (is_soap_fault($soapcl)) {
                $this->LogFault($soapcl, $name . " Exception"); //log faults 
            }
            return $soapcl; 
        }

        private function VerifyLuaValue($value) //mostly due to booleans, but maybe something will come up in the future
        {
            switch ($value) {
                case is_bool(json_encode($value)) || $value == 1:
                    return json_encode($value);
                default:
                    return $value;
            }
        }

        private function GetLuaType(string $value): string //currently only supports booleans, integers and strings
        {
            switch ($value) {
                case $value == "true" || $value == "false": //this is so gay but php hates me
                    return "LUA_TBOOLEAN";
                case !is_string($value) && !is_bool($value) && filter_var($value, FILTER_VALIDATE_INT):
                    return "LUA_TNUMBER";
                default:
                    return "LUA_TSTRING";
            }
        }

        private function ConstructLuaArguments(array $arguments = []): array //arguments for a script being executed
        {
            $luavalue = array("LuaValue" => array());
            foreach ($arguments as $argument) {
                array_push($luavalue['LuaValue'], array(
                    "type" => $this->GetLuaType($argument),
                    "value" => $this->VerifyLuaValue($argument)
                ));
            }
            return $luavalue;
        }

        public function ConstructGenericJob(string $jobid, int $expiration, int $category, int $cores, string $scriptname, string $script, array $arguments = []): array
        {
            return array(
                "job" => array(
                    "id" => $jobid,
                    "expirationInSeconds" => $expiration,
                    "category" => $category,
                    "cores" => $cores
                ),
                "script" => array(
                    "name" => $scriptname,
                    "script" => $script,
                    "arguments" => $this->ConstructLuaArguments($arguments)
                )
            );
        }

        public function ConstructGenericScriptExecute(string $jobid, string $scriptname, string $script, array $arguments = []): array
        {
            return array(
                "jobID" => $jobid,
                "script" => array(
                    "name" => $scriptname,
                    "script" => $script,
                    "arguments" => $this->ConstructLuaArguments($arguments)
                )
            );
        }

        public function GetVersion()
        {
            return $this->SoapCallService("GetVersion");
        }

        public function HelloWorld()
        {
            return $this->SoapCallService("HelloWorld");
        }

        public function CloseAllJobs()
        {
            return $this->SoapCallService("CloseAllJobs");
        }

        public function CloseExpiredJobs()
        {
            return $this->SoapCallService("CloseExpiredJobs");
        }

        public function GetAllJobsEx()
        {
            return $this->SoapCallService("GetAllJobsEx");
        }

        public function GetStatus()
        {
            return $this->SoapCallService("GetStatus");
        }

        public function DiagEx(string $type, string $jobid)
        {
            return $this->SoapCallService("DiagEx", array("type" => $type, "jobID" => $jobid));
        }

        // this doesn't return anything
        // austin: i know this doesnt return anything
        // https://pastebin.com/raw/pr5NDBwC
        public function CloseJob(string $jobid)
        {
            return $this->SoapCallService("CloseJob", array("jobID" => $jobid));
        }

        public function GetExpiration(string $jobid)
        {
            return $this->SoapCallService("GetExpiration", array("jobID" => $jobid));
        }

        public function RenewLease(string $jobid, int $expiration)
        {
            return $this->SoapCallService("RenewLease", array("jobID" => $jobid, "expirationInSeconds" => $expiration));
        }
       
        public function ExecuteEx(array $soapargs = [])
        {
            return $this->SoapCallService(
                "ExecuteEx",
                $soapargs
            );
        }

        public function OpenJobEx(array $soapargs = [])
        {
            return $this->SoapCallService(
                "OpenJobEx",
                $soapargs
            );
        }

        public function BatchJobEx(array $soapargs = [])
        {
            return $this->SoapCallService(
                "BatchJobEx",
                $soapargs
            );
        }
    }
}
