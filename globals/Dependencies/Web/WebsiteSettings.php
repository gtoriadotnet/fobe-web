<?php

/**
 * This class is used to fetch the website settings.
 * It is also used to write the website settings.
 * 
 * Written by: Nikita Petko
 * Date: 25/11/2021
 * 
 * Ported from MFDLABS/corp-integral/src/lib/web/settings.fx
 * MFDLABS TwoTime (c) 2021-
 */

namespace Alphaland\Web {

    use PDO;

    class WebsiteSettings
    {
        // default return if no settings are found
        // because there may be a NULL value in the database
        private const DOES_NOT_EXIST = 'does_not_exist';

        private static array $validTypes = [
            'string',
            'integer',
            'float',
            'boolean',
            'array'
        ];

        private static function ValueIsValidType($value, string $type): bool
        {
            if (gettype($value) === 'NULL') return true;
            return gettype($value) === $type && gettype($value) !== 'NULL';
        }

        private static function ConvertValueToString($value): string
        {
            switch (gettype($value)) {
                case 'string':
                    return $value;
                case 'integer':
                case 'float':
                    return (string) $value;
                case 'boolean':
                    return $value ? 'true' : 'false';
                case 'array':
                    return json_encode($value);
                default:
                    return '';
            }
        }

        private static function ConvertStringToValue($value, string $type)
        {
            if (!in_array($type, self::$validTypes)) {
                throw new \Exception("Invalid type");
            }

            switch ($type) {
                case 'string':
                    return $value;
                case 'integer':
                    return intval($value);
                case 'float':
                    return floatval($value);
                case 'boolean':
                    return boolval($value);
                case 'array':
                    return json_decode($value, true);
                default:
                    return $value;
            }
        }

        /**
         * Fetches a website setting.
         * 
         * @param string $name The name of the setting.
         * @param string $default The default value of the setting.
         * 
         * @return mixed The value of the setting.
         */
        public static function GetSetting(string $name, $default = null)
        {
            $query = $GLOBALS['pdo']->prepare("SELECT `value`, `type` FROM `websettings` WHERE `name` = :name");
            $query->bindParam(':name', $name);

            if (!$query->execute()) {
                return $default;
            }
            $result = $query->fetch(PDO::FETCH_ASSOC);

            if ($result === false) {
                return $default;
            }

            return self::ConvertStringToValue($result['value'], $result['type']);
        }

        /**
         * Sets a website setting.
         * 
         * @param string $name The name of the setting.
         * @param mixed $value The value of the setting.
         * @param string $type The type of the setting.
         * 
         * @return bool Whether the setting was set.
         */
        public static function UpdateSetting(string $name, $value, string $type = null): bool
        {
            if ($type === null) {
                $type = gettype($value);
            }

            if ($value === self::DOES_NOT_EXIST) return false;

            if (!in_array($type, self::$validTypes)) {
                return false;
            }

            if (!self::ValueIsValidType($value, $type)) {
                return false;
            }

            $remote = self::GetSetting($name, self::DOES_NOT_EXIST);

            if ($remote === $value) {
                return false;
            }

            $query = null;

            if ($remote === self::DOES_NOT_EXIST) {
                $query = $GLOBALS['pdo']->prepare("INSERT INTO `websettings` (`name`, `value`, `type`) VALUES (:name, :value, :type)");
            } else {
                $query = $GLOBALS['pdo']->prepare("UPDATE `websettings` SET `value` = :value, `type` = :type WHERE `name` = :name");
            }

            if (gettype($value) === 'NULL') {
                $query->bindParam(':value', $value, PDO::PARAM_NULL);
            } else {
                $tmp = self::ConvertValueToString($value);

                $query->bindParam(':value', $tmp, PDO::PARAM_STR);
            }

            $query->bindParam(':name', $name, PDO::PARAM_STR);
            $query->bindParam(':type', $type, PDO::PARAM_STR);

            return $query->execute();
        }

        /**
         * Deletes a website setting.
         * 
         * @param string $name The name of the setting.
         * 
         * @return bool Whether the setting was deleted.
         */
        public static function DeleteSetting(string $name): bool
        {
            $query = $GLOBALS['pdo']->prepare("DELETE FROM `websettings` WHERE `name` = :name");
            $query->bindParam(':name', $name);

            return $query->execute();
        }

        /**
         * Gets all website settings.
         * 
         * @return array The website settings.
         */
        public static function GetAllSettings(): array
        {
            $query = $GLOBALS['pdo']->prepare("SELECT `name`, `value`, `type` FROM `websettings`");

            if (!$query->execute()) {
                return [];
            }

            $result = $query->fetchAll(PDO::FETCH_ASSOC);

            $settings = [];

            foreach ($result as $row) {
                $settings[$row['name']] = self::ConvertStringToValue($row['value'], $row['type']);
            }

            return $settings;
        }

        /**
         * Determines if a website setting exists.
         * 
         * @param string $name The name of the setting.
         * 
         * @return bool Whether the setting exists.
         */
        public static function SettingExists(string $name): bool
        {
            $query = $GLOBALS['pdo']->prepare("SELECT `name` FROM `websettings` WHERE `name` = :name");
            $query->bindParam(':name', $name);

            if (!$query->execute()) {
                return false;
            }

            $result = $query->fetch(PDO::FETCH_ASSOC);

            return $result !== false;
        }

        /**
         * Deletes all website settings.
         * 
         * @return bool Whether the settings were deleted.
         */
        public static function DeleteAllSettings(): bool
        {
            $query = $GLOBALS['pdo']->prepare("DELETE FROM `websettings`");

            return $query->execute();
        }
    }
}
