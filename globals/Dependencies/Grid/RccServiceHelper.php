<?php

/*
    Alphaland 2021
*/

namespace Alphaland\Grid {
    class RccServiceHelper
    {
        private string $ServiceIp;

        function __construct(string $ServiceIp)
        {
            $this->ServiceIp = $ServiceIp;
        }

        private function soapCallService(string $name, array $arguments = []) 
        {
            $soapcl = new \SoapClient($GLOBALS['RCCwsdl'], ["location" => "http://".$this->ServiceIp, "uri" => "http://roblox.com/", "exceptions" => false]);
            return $soapcl->{$name}($arguments); //thanks BrentDaMage didnt know u can do this
        }

        private function verifyLuaValue($value) //mostly due to booleans, but maybe something will come up in the future
        {
            switch ($value)
            {
                case is_bool(json_encode($value)) || $value == 1:
                    return json_encode($value);
                default:
                    return $value;
            }
        }

        private function getLuaType($value): string //currently only supports booleans, integers and strings
        {
            switch ($value)
            {
                case $value == "true" || $value == "false": //this is so gay but php hates me
                    return "LUA_TBOOLEAN";
                case !is_string($value) && !is_bool($value) && filter_var($value, FILTER_VALIDATE_INT):
                    return "LUA_TNUMBER";
                default:
                    return "LUA_TSTRING";
            }
        }

        private function luaArguments(array $arguments=[]) //arguments for a script being executed
        {
            if (!empty($arguments)) {
                $luavalue = array("LuaValue"=>array());
                foreach ($arguments as $argument) { 
                    array_push($luavalue['LuaValue'], array(
                        "type" => $this->getLuaType($argument),
                        "value" => $this->verifyLuaValue($argument)
                    ));
                }
                return $luavalue;
            }
        }

        private function soapJobTemplate(string $servicename, string $jobid, int $expiration, int $category, int $cores, string $scriptname, string $script, array $arguments=[])
        {
            return $this->soapCallService(
                $servicename,
                array(
                    "job" => array(
                        "id" => $jobid,
                        "expirationInSeconds" => $expiration,
                        "category" => $category,
                        "cores" => $cores
                    ),
                    "script" => array(
                        "name" => $scriptname,
                        "script" => $script,
                        "arguments" => $this->luaArguments($arguments)
                    )
                )
            );
        }

        public function soapGetVersion()
        {
            return $this->soapCallService("GetVersion");
        }

        public function soapHelloWorld()
        {
            return $this->soapCallService("HelloWorld");
        }

        public function soapCloseAllJobs()
        {
            return $this->soapCallService("CloseAllJobs");
        }

        public function soapCloseExpiredJobs()
        {
            return $this->soapCallService("CloseExpiredJobs");
        }

        public function soapGetAllJobsEx()
        {
            return $this->soapCallService("GetAllJobsEx");
        }

        public function soapGetStatus()
        {
            return $this->soapCallService("GetStatus");
        }

        public function soapDiagEx(string $type, string $jobid)
        {
            return $this->soapCallService("DiagEx", array("type" => $type, "jobID" => $jobid));
        }

        public function soapCloseJob(string $jobid)
        {
            return $this->soapCallService("CloseJob", array("jobID" => $jobid));
        }

        public function soapGetExpiration(string $jobid)
        {
            return $this->soapCallService("GetExpiration", array("jobID" => $jobid));
        }

        public function soapExecuteEx(string $jobid, string $scriptname, string $script, array $arguments=[])
        {
            return $this->soapCallService("ExecuteEx", array(
                    "jobID" => $jobid, 
                    "script" => array(
                        "name" => $scriptname,
                        "script" => $script,
                        "arguments" => $this->luaArguments($arguments)
                    )
                )
            );
        }

        public function soapRenewLease(string $jobid, int $expiration)
        {
            return $this->soapCallService("RenewLease", array("jobID" => $jobid, "expirationInSeconds" => $expiration));
        }

        public function soapOpenJobEx(string $jobid, int $expiration, string $scriptname, string $script, array $arguments=[])
        {
            return $this->soapJobTemplate("OpenJobEx", $jobid, $expiration, 1, 3, $scriptname, $script, $arguments);
        }

        public function soapBatchJobEx(string $jobid, int $expiration, string $scriptname, string $script, array $arguments=[])
        {
            return $this->soapJobTemplate("BatchJobEx", $jobid, $expiration, 1, 3, $scriptname, $script, $arguments);
        }
    }
}
