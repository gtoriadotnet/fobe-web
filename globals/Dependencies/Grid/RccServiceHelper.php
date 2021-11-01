<?php

/*
    Alphaland 2021
    Nikita TODO: ALPHA-22 (Response Models for things that definitely have a known response (like specific SOAP actions))
                 https://jira.mfdlabs.local/browse/ALPHA-22
*/

namespace Alphaland\Grid {
    class RccServiceHelper
    {
        private string $ServiceIp;

        function __construct(string $ServiceIp)
        {
            $this->ServiceIp = $ServiceIp;
        }

        private function SoapCallService(string $name, array $arguments = []): mixed
        {
            $soapcl = new \SoapClient($GLOBALS['RCCwsdl'], ["location" => "http://" . $this->ServiceIp, "uri" => "http://roblox.com/", "exceptions" => false]);
            return $soapcl->{$name}($arguments); //thanks BrentDaMage didnt know u can do this
        }

        private function VerifyLuaValue(mixed $value): string //mostly due to booleans, but maybe something will come up in the future
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
            if (!empty($arguments)) {
                $luavalue = array("LuaValue" => array());
                foreach ($arguments as $argument) {
                    array_push($luavalue['LuaValue'], array(
                        "type" => $this->GetLuaType($argument),
                        "value" => $this->VerifyLuaValue($argument)
                    ));
                }
                return $luavalue;
            }
        }

        private function ConstructJobTemplate(string $servicename, string $jobid, int $expiration, int $category, int $cores, string $scriptname, string $script, array $arguments = []): mixed
        {
            return $this->SoapCallService(
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
                        "arguments" => $this->ConstructLuaArguments($arguments)
                    )
                )
            );
        }

        public function GetVersion(): mixed
        {
            return $this->SoapCallService("GetVersion");
        }

        public function HelloWorld(): mixed
        {
            return $this->SoapCallService("HelloWorld");
        }

        public function CloseAllJobs(): mixed
        {
            return $this->SoapCallService("CloseAllJobs");
        }

        public function CloseExpiredJobs(): mixed
        {
            return $this->SoapCallService("CloseExpiredJobs");
        }

        public function GetAllJobsEx(): mixed
        {
            return $this->SoapCallService("GetAllJobsEx");
        }

        public function GetStatus(): mixed
        {
            return $this->SoapCallService("GetStatus");
        }

        public function DiagEx(string $type, string $jobid): mixed
        {
            return $this->SoapCallService("DiagEx", array("type" => $type, "jobID" => $jobid));
        }

        // this doesn't return anything
        // https://pastebin.com/raw/pr5NDBwC
        public function CloseJob(string $jobid): mixed
        {
            return $this->SoapCallService("CloseJob", array("jobID" => $jobid));
        }

        public function GetExpiration(string $jobid): mixed
        {
            return $this->SoapCallService("GetExpiration", array("jobID" => $jobid));
        }

        public function ExecuteEx(string $jobid, string $scriptname, string $script, array $arguments = []): mixed
        {
            return $this->SoapCallService(
                "ExecuteEx",
                array(
                    "jobID" => $jobid,
                    "script" => array(
                        "name" => $scriptname,
                        "script" => $script,
                        "arguments" => $this->ConstructLuaArguments($arguments)
                    )
                )
            );
        }

        public function RenewLease(string $jobid, int $expiration): mixed
        {
            return $this->SoapCallService("RenewLease", array("jobID" => $jobid, "expirationInSeconds" => $expiration));
        }

        public function OpenJobEx(string $jobid, int $expiration, string $scriptname, string $script, array $arguments = []): mixed
        {
            return $this->ConstructJobTemplate("OpenJobEx", $jobid, $expiration, 1, 3, $scriptname, $script, $arguments);
        }

        public function BatchJobEx(string $jobid, int $expiration, string $scriptname, string $script, array $arguments = []): mixed
        {
            return $this->ConstructJobTemplate("BatchJobEx", $jobid, $expiration, 1, 3, $scriptname, $script, $arguments);
        }
    }
}
