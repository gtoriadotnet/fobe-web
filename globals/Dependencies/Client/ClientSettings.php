<?php

/**
 * This class is used to fetch the client settings and client application buckets.
 * It is also used to write the clietn settings and client application buckets.
 * 
 * Written by: Nikita Petko, Jakob Valara (Spfffffx)
 * Date: 10/12/2021
 * 
 * https://series-x.git-stylez.mfdlabs.local/SpfffffX/RobloxWebPlatform/blob/master/Assemblies/Platform/ClientSettings/Roblox.Platform.ClientSettings/Implementation/ClientSettingsHelper.cs
 * 
 * The reason this returns the values as strings is because the client can parse them to their respective types. They can also be converted to their respective types.
 * For now they are all strings.
 * 
 * Open an issue if you want to see the values returned as their respective types and not as strings.
 * 
 * MFDLABS TwoTime (c) 2021-
 */

// Note: This could be shortened slightly.


/*
Usage:

use Alphaland\Client\ClientSettings;
use Alphaland\Client\ClientSettingsApplications;
use Alphaland\Client\ClientSettingsKind;

require_once './globals/Dependencies/Client/ClientSettings.php';

// Doesn't require IP whitelist, doesn't require to be fetched from RCC Service, cannot be fetched from clientsettings service, and has 'Test2' and 'Test3' as dependencies.
ClientSettingsApplications::CreateOrUpdateApplication("Test", false, false, false, ["Test2", "Test3"]);
ClientSettingsApplications::CreateOrUpdateApplication("Test2");
ClientSettingsApplications::CreateOrUpdateApplication("Test3");

// FString
ClientSettings::WriteSetting("Test",  "Test", ClientSettingsKind::FastString, "Test");
ClientSettings::WriteSetting("Test2", "Test2", ClientSettingsKind::FastString, "Test2");
ClientSettings::WriteSetting("Test3", "Test3", ClientSettingsKind::FastString, "Test3");

// DFString
ClientSettings::WriteSetting("Test",  "Test", ClientSettingsKind::DynamicFastString, "Test2");
ClientSettings::WriteSetting("Test2", "Test2", ClientSettingsKind::DynamicFastString, "Test3");
ClientSettings::WriteSetting("Test3", "Test3", ClientSettingsKind::DynamicFastString, "Test");

// No Prefix
ClientSettings::WriteSetting("Test3", "Nikita",  ClientSettingsKind::Unscoped, "Petko");

// SFLog
ClientSettings::WriteSetting("Test", "TestSetting", ClientSettingsKind::SynchronizedFastLog, true);

// The settings that are in the `Test` bucket ONLY, NO DEPENDENCIES
echo "Test Application Bucket Settings: " . json_encode(ClientSettingsApplications::GetApplicationSettings("Test")) . "\n";
echo "Test2 Application Bucket Settings: " . json_encode(ClientSettingsApplications::GetApplicationSettings("Test2")) . "\n";
echo "Test3 Application Bucket Settings: " . json_encode(ClientSettingsApplications::GetApplicationSettings("Test3")) . "\n";

// The settings that are in the `Test` bucket and all of its dependencies
// If it has no dependencies, it will return the same as the GetApplicationSettings("Test")
echo "Get Application Bucket Test's settings, has dependencies '" . implode(', ', ClientSettingsApplications::GetApplicationDependencies("Test")) . "': " . json_encode(ClientSettingsApplications::FetchCombinedApplicationDependencies("Test", true)) . "\n";

*/

namespace Alphaland\Client {

    use Error;
    use PDO;

    /**
     * This class is used to manage ClientSettings Applications.
     * What is a ClientSettings Application?
     * 
     * A ClientSettings Application is a bucket that contains settings how the
     * bucket is displayed. Example: The ClientSettings Application can be configured to be only visible for RCC Service Instances (AccessKey)
     * 
     * The buckets also contain a list of strings called "Dependencies", these dependencies are used to combine the buckets.
     *
     * 
     * TODO: Check if a dependency has a dependency to a dependenant bucket.
     */
    class ClientSettingsApplications
    {

        /**
         * Lists all ClientSettings Application Names, if the limit is unspecified, or -1, all ClientSettings Applications are returned.
         * 
         * @param int $limit The maximum number of ClientSettings Applications to return. If unspecified, or -1, all ClientSettings Applications are returned.
         * 
         * @return array An array of ClientSettings Application Names.
         */
        public static function GetAllClientSettingsApplicationNames(int $limit = -1)
        {
            if ($limit === -1) {
                $limit = PHP_INT_MAX;
            }

            // get all the applications
            $query = $GLOBALS['pdo']->prepare("SELECT `name` FROM `clientsettings_applications` ORDER BY `name` ASC LIMIT :limit");
            $query->bindParam(':limit', $limit, PDO::PARAM_INT);


            $query->execute();

            // return the applications
            return $query->fetchAll(PDO::FETCH_COLUMN);
        }

        /**
         * Determines if a ClientSettings Application exists.
         * 
         * @param string $applicationName The name of the ClientSettings Application.
         * 
         * @return bool True if the ClientSettings Application exists, false otherwise.
         */
        public static function ApplicationExists(string $applicationName)
        {
            // get the application
            $query = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM `clientsettings_applications` WHERE `name` = :name");
            $query->bindParam(':name', $applicationName, PDO::PARAM_STR);

            $query->execute();

            // return if the application exists
            return $query->fetchColumn() > 0;
        }

        /**
         * Determines if the clientsettings application bucket requires an IP whitelist.
         * 
         * @param string $applicationName The name of the ClientSettings Application.
         * 
         * @return bool True if the ClientSettings Application requires an IP whitelist, false otherwise.
         */
        public static function ApplicationRequiresIpWhitelist(string $applicationName)
        {
            // get the application
            $query = $GLOBALS['pdo']->prepare("SELECT `requires_ip_whitelist` FROM `clientsettings_applications` WHERE `name` = :name");
            $query->bindParam(':name', $applicationName, PDO::PARAM_STR);

            $query->execute();

            // return if the application requires an ip whitelist
            return $query->fetchColumn() == 1;
        }


        /**
         * Determines if the clientsettings application bucket requires RCC Service Authentication.
         * 
         * @param string $applicationName The name of the ClientSettings Application.
         * 
         * @return bool True if the ClientSettings Application requires RCC Service Authentication, false otherwise.
         */
        public static function ApplicationRequiresRccServiceAuthentication(string $applicationName)
        {
            // get the application
            $query = $GLOBALS['pdo']->prepare("SELECT `requires_rcc_service_authentication` FROM `clientsettings_applications` WHERE `name` = :name");
            $query->bindParam(':name', $applicationName, PDO::PARAM_STR);

            $query->execute();

            // return if the application requires an rcc service authentication
            return $query->fetchColumn() == 1;
        }

        /**
         * Get an application by name.
         * 
         * @param string $applicationName The name of the ClientSettings Application.
         * 
         * @return array The ClientSettings Application.
         */
        public static function GetApplication(string $applicationName)
        {
            // get the application
            $query = $GLOBALS['pdo']->prepare("SELECT * FROM `clientsettings_applications` WHERE `name` = :name");
            $query->bindParam(':name', $applicationName, PDO::PARAM_STR);

            $query->execute();

            $data = $query->fetch(PDO::FETCH_ASSOC);

            $deps = [];

            if (!empty($data['dependencies'])) {
                $deps = explode(',', $data['dependencies']);

                if (sizeof($deps) > 0) {
                    if ($deps[0] === '') {
                        array_pop($deps);
                    }

                    $deps = array_map('trim', $deps);
                }
            }
            // return the application
            return array(
                'id' => $data['id'] ?? null,
                'name' => $data['name'] ?? null,
                'requires_ip_whitelist' => $data['requires_ip_whitelist'] ?? false,
                'requires_rcc_service_authentication' => $data['requires_rcc_service_authentication'] ?? false,
                'can_be_fetched_from_clientsettings_service' => $data['can_be_fetched_from_clientsettings_service'] ?? false,
                'dependencies' => $deps,
            );
        }


        /**
         * Creates a new ClientSettings Application.
         * 
         * @param string $applicationName The name of the ClientSettings Application.
         * @param bool $requiresIpWhitelist True if the ClientSettings Application requires an IP whitelist, false otherwise.
         * @param bool $requiresRccServiceAuthentication True if the ClientSettings Application requires RCC Service Authentication, false otherwise.
         * @param bool $canBeFetchedFromClientsettingsService True if the ClientSettings Application can be fetched from the Clientsettings Service, false otherwise.
         * @param array $dependencies An array of ClientSettings Application Names that are dependencies of this ClientSettings Application.
         */
        public static function CreateApplication(string $applicationName, bool $requiresIpWhitelist = false, bool $requiresRccServiceAuthentication = false, bool $canBeFetchedFromClientSettingsService = true, array $dependencies = [])
        {
            // check if the application already exists
            if (self::ApplicationExists($applicationName)) {
                throw new Error("The application '$applicationName' already exists.");
            }

            // throw if dependencies includes the application itself
            if (in_array($applicationName, $dependencies)) {
                throw new Error("The application '$applicationName' cannot depend on itself.");
            }

            // merge the dependencies in a csv style
            $dependencies = implode(',', $dependencies);

            // create the application
            $query = $GLOBALS['pdo']->prepare("INSERT INTO `clientsettings_applications` (`name`, `requires_ip_whitelist`, `requires_rcc_service_authentication`, `can_be_fetched_from_clientsettings_service`, `dependencies`) VALUES (:name, :requires_ip_whitelist, :requires_rcc_service_authentication, :can_be_fetched_from_clientsettings_service, :dependencies)");
            $query->bindParam(':name', $applicationName, PDO::PARAM_STR);
            $query->bindParam(':requires_ip_whitelist', $requiresIpWhitelist, PDO::PARAM_BOOL);
            $query->bindParam(':requires_rcc_service_authentication', $requiresRccServiceAuthentication, PDO::PARAM_BOOL);
            $query->bindParam(':can_be_fetched_from_clientsettings_service', $canBeFetchedFromClientSettingsService, PDO::PARAM_BOOL);
            $query->bindParam(':dependencies', $dependencies, PDO::PARAM_STR);

            $query->execute();
        }

        /**
         * Updates an existing ClientSettings Application.
         * 
         * @param string $applicationName The name of the ClientSettings Application.
         * @param bool $requiresIpWhitelist The requiresIpWhitelist flag.
         * @param bool $requiresRccServiceAuthentication The requiresRccServiceAuthentication flag.
         * @param bool $canBeFetchedFromClientSettingsService The canBeFetchedFromClientSettingsService flag.
         * @param array $dependencies The dependencies.
         */
        public static function UpdateApplication(string $applicationName, bool $requiresIpWhitelist = false, bool $requiresRccServiceAuthentication = false, bool $canBeFetchedFromClientSettingsService = true, array $dependencies = [])
        {
            // check if the application exists
            if (!self::ApplicationExists($applicationName)) {
                throw new Error("The application '$applicationName' does not exist.");
            }

            // merge the dependencies in a csv style
            $dependencies = implode(',', $dependencies);

            // update the application
            $query = $GLOBALS['pdo']->prepare("UPDATE `clientsettings_applications` SET `requires_ip_whitelist` = :requires_ip_whitelist, `requires_rcc_service_authentication` = :requires_rcc_service_authentication, `can_be_fetched_from_clientsettings_service` = :can_be_fetched_from_clientsettings_service, `dependencies` = :dependencies WHERE `name` = :name");
            $query->bindParam(':name', $applicationName, PDO::PARAM_STR);
            $query->bindParam(':requires_ip_whitelist', $requiresIpWhitelist, PDO::PARAM_BOOL);
            $query->bindParam(':requires_rcc_service_authentication', $requiresRccServiceAuthentication, PDO::PARAM_BOOL);
            $query->bindParam(':can_be_fetched_from_clientsettings_service', $canBeFetchedFromClientSettingsService, PDO::PARAM_BOOL);
            $query->bindParam(':dependencies', $dependencies, PDO::PARAM_STR);

            $query->execute();
        }


        /**
         * Creates or updates a ClientSettings Application.
         * 
         * @param string $applicationName The name of the ClientSettings Application.
         * @param bool $requiresIpWhitelist Whether the application requires an IP Whitelist.
         * @param bool $requiresRccServiceAuthentication Whether the application requires an RCC Service Authentication.
         * @param bool $canBeFetchedFromClientSettingsService Whether the application can be fetched from the ClientSettings Service.
         * @param array $dependencies The dependencies of the application.
         */
        public static function CreateOrUpdateApplication(string $applicationName, bool $requiresIpWhitelist = false, bool $requiresRccServiceAuthentication = false, bool $canBeFetchedFromClientSettingsService = true, array $dependencies = [])
        {
            // check if the application exists
            if (self::ApplicationExists($applicationName)) {
                self::UpdateApplication($applicationName, $requiresIpWhitelist, $requiresRccServiceAuthentication, $canBeFetchedFromClientSettingsService, $dependencies);
            } else {
                self::CreateApplication($applicationName, $requiresIpWhitelist, $requiresRccServiceAuthentication, $canBeFetchedFromClientSettingsService, $dependencies);
            }
        }

        /**
         * Get or create a ClientSettings Application.
         * 
         * @param string $applicationName The name of the ClientSettings Application.
         * @param bool $requiresIpWhitelist Whether the application requires an IP Whitelist.
         * @param bool $requiresRccServiceAuthentication Whether the application requires an RCC Service Authentication.
         * @param bool $canBeFetchedFromClientSettingsService Whether the application can be fetched from the ClientSettings Service.
         * @param array $dependencies The dependencies of the application.
         */
        public static function GetOrCreateApplication(string $applicationName, bool $requiresIpWhitelist = false, bool $requiresRccServiceAuthentication = false, bool $canBeFetchedFromClientSettingsService = true, array $dependencies = [])
        {
            // check if the application exists
            if (!self::ApplicationExists($applicationName)) {
                self::CreateApplication($applicationName, $requiresIpWhitelist, $requiresRccServiceAuthentication, $canBeFetchedFromClientSettingsService, $dependencies);
            }

            // get the application
            return self::GetApplication($applicationName);
        }

        /**
         * List the application bucket's dependencies.
         * 
         * @param string $applicationName The name of the ClientSettings Application.
         * 
         * @return array The dependencies.
         */
        public static function GetApplicationDependencies(string $applicationName): array
        {
            // get the application
            $query = $GLOBALS['pdo']->prepare("SELECT `dependencies` FROM `clientsettings_applications` WHERE `name` = :application");
            $query->bindParam(':application', $applicationName, PDO::PARAM_STR);

            $query->execute();

            // blow up the csv style string into an array
            $dependencies = explode(',', $query->fetchColumn());
            return $dependencies;
        }

        /**
         * Checks if an application bucket has any dependencies.
         * 
         * @param string $applicationName The name of the ClientSettings Application.
         * 
         * @return bool Whether the application has dependencies.
         */
        public static function ApplicationHasAnyDependencies(string $applicationName): bool
        {
            if (!self::ApplicationExists($applicationName)) {
                return false;
            }

            // get the application
            $query = $GLOBALS['pdo']->prepare("SELECT `dependencies` FROM `clientsettings_applications` WHERE `name` = :application");
            $query->bindParam(':application', $applicationName, PDO::PARAM_STR);

            $query->execute();

            // blow up the csv style string into an array
            $dependencies = explode(',', $query->fetchColumn());
            return count($dependencies) > 0;
        }

        /**
         * Checks if an application bucket has a specific dependency.
         * 
         * @param string $applicationName The name of the ClientSettings Application.
         * @param string $dependency The dependency to check for.
         * 
         * @return bool Whether the application has the dependency.
         */
        public static function ApplicationHasDependency(string $applicationName, string $dependencyName): bool
        {
            // with the dependencies containing $dependencyName, we can check if the application has the dependency
            $query = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM `clientsettings_applications` WHERE `name` = :application AND `dependencies` LIKE :dependency");
            $query->bindParam(':application', $applicationName, PDO::PARAM_STR);
            $query->bindParam(':dependency', '%' . $dependencyName . '%', PDO::PARAM_STR);

            $query->execute();

            // return if the application has the dependency
            return $query->fetchColumn() > 0;
        }

        /**
         * Gets the formatted settings for an application.
         * 
         * @param string $applicationName The name of the ClientSettings Application.
         * 
         * @return array The settings.
         */
        public static function GetApplicationSettings(string $applicationName)
        {
            return ClientSettings::GetSettingsFormatted($applicationName);
        }

        /**
         * Gets the formatted settings for an application, combined with the dependencies.
         * 
         * @param string $applicationName The name of the ClientSettings Application.
         * @param bool $recursive Whether to include the dependencies' dependencies.
         * 
         * @return array The settings.
         */
        public static function FetchCombinedApplicationDependencies(string $applicationName, bool $recursive = true)
        {
            // get the application
            $query = $GLOBALS['pdo']->prepare("SELECT `dependencies` FROM `clientsettings_applications` WHERE `name` = :application");
            $query->bindParam(':application', $applicationName, PDO::PARAM_STR);

            $query->execute();

            // get the current dependencies
            $currentDependencies = explode(',', $query->fetchColumn());

            $combinedDependencies = self::GetApplicationSettings($applicationName);

            foreach ($currentDependencies as $dependency) {
                if ($recursive && self::ApplicationHasAnyDependencies($dependency)) {
                    $combinedDependencies = array_merge($combinedDependencies, self::FetchCombinedApplicationDependencies($dependency, $recursive));
                } else {
                    // get the dependencies of the current dependency
                    $settings = self::GetApplicationSettings($dependency);

                    // add the dependencies to the combined dependencies
                    $combinedDependencies = array_merge($combinedDependencies, $settings);
                }
            }

            return $combinedDependencies;
        }


        /**
         * Deletes an application and all of its settings.
         * 
         * @param string $applicationName The name of the ClientSettings Application.
         */
        public static function DeleteApplicationAndSettings(string $applicationName)
        {
            // delete the application
            $query = $GLOBALS['pdo']->prepare("DELETE FROM `clientsettings_applications` WHERE `name` = :name");
            $query->bindParam(':name', $applicationName, PDO::PARAM_STR);

            $query->execute();

            // delete the settings
            ClientSettings::DeleteSettings($applicationName);
        }

        /**
         * Determine if an application can be fetched from the ClientSettings Service.
         * 
         * @param string $applicationName The name of the ClientSettings Application.
         * 
         * @return bool Whether the application can be fetched from the ClientSettings Service.
         */
        public static function ApplicationCanBeFetchedFromClientSettingsService(string $applicationName)
        {
            // get the application
            $query = $GLOBALS['pdo']->prepare("SELECT `can_be_fetched_from_clientsettings_service` FROM `clientsettings_applications` WHERE `name` = :name");
            $query->bindParam(':name', $applicationName, PDO::PARAM_STR);

            $query->execute();

            // return if the application can be fetched from the client settings service
            return $query->fetchColumn() == 1;
        }
    }

    /**
     * The client settings kind.
     */
    class ClientSettingsKind
    {
        public const Unscoped = "Unscoped";
        public const FastLog = "FastLog";
        public const DynamicFastLog = "DynamicFastLog";
        public const SynchronizedFastLog =  "SynchronizedFastLog";
        public const FastFlag = "FastFlag";
        public const DynamicFastFlag =  "DynamicFastFlag";
        public const SynchronizedFastFlag = "SynchronizedFastFlag";
        public const FastInteger = "FastInteger";
        public const DynamicFastInteger = "DynamicFastInteger";
        public const SynchronizedFastInteger = "SynchronizedFastInteger";
        public const FastString = "FastString";
        public const DynamicFastString = "DynamicFastString";
        public const SynchronizedFastString = "SynchronizedFastString";

        public static function FormatValueForKind(string $kind, $value)
        {
            switch ($kind) {
                case self::FastLog:
                case self::DynamicFastLog:
                case self::SynchronizedFastLog:
                    return $value;
                case self::FastFlag:
                case self::DynamicFastFlag:
                case self::SynchronizedFastFlag:
                    // Make the first character uppercase
                    return ucfirst($value);
                case self::FastInteger:
                case self::DynamicFastInteger:
                case self::SynchronizedFastInteger:
                    return intval($value);
                case self::FastString:
                case self::DynamicFastString:
                case self::SynchronizedFastString:
                    return strval($value);
                case self::Unscoped:
                    if ($value === 'true' || $value === 'false') {
                        return ucfirst($value);;
                    }
                    return $value;
                default:
                    throw new Error("The kind '$kind' is not supported.");
            }
        }

        public static function ToFormattedKind(string $kind): string
        {
            switch ($kind) {
                case self::Unscoped:
                    return "";
                case self::FastLog:
                    return "FLog";
                case self::DynamicFastLog:
                    return "DFLog";
                case self::SynchronizedFastLog:
                    return "SFLog";
                case self::FastFlag:
                    return "FFlag";
                case self::DynamicFastFlag:
                    return "DFFlag";
                case self::SynchronizedFastFlag:
                    return "SFFlag";
                case self::FastInteger:
                    return "FInt";
                case self::DynamicFastInteger:
                    return "DFInt";
                case self::SynchronizedFastInteger:
                    return "SFInt";
                case self::FastString:
                    return "FString";
                case self::DynamicFastString:
                    return "DFString";
                case self::SynchronizedFastString:
                    return "SFString";
                default:
                    throw new Error("The kind '$kind' is not a valid kind.");
            }
        }

        public static function GetValidTypeForKind(string $kind): string
        {
            switch ($kind) {
                case self::Unscoped:
                    return "mixed";
                case self::FastFlag:
                case self::DynamicFastFlag:
                case self::SynchronizedFastFlag:
                    return  "boolean";
                case self::FastLog:
                case self::DynamicFastLog:
                case self::SynchronizedFastLog:
                case self::FastInteger:
                case self::DynamicFastInteger:
                case self::SynchronizedFastInteger:
                    return "integer";
                case self::FastString:
                case self::DynamicFastString:
                case self::SynchronizedFastString:
                    return "string";
                default:
                    throw new Error("The kind '$kind' is not a valid kind.");
            }
        }

        public static function ValueIsValidForKind($value, string $kind): bool
        {
            // if it is unscoped, it can be anything, so be very careful when writing unscoped values
            switch ($kind) {
                case self::FastFlag:
                case self::DynamicFastFlag:
                case self::SynchronizedFastFlag:
                    return is_bool($value);
                case self::FastLog:
                case self::DynamicFastLog:
                case self::SynchronizedFastLog:
                case self::FastInteger:
                case self::DynamicFastInteger:
                case self::SynchronizedFastInteger:
                    if (is_bool($value)) {
                        return true;
                    }
                    return is_numeric($value);
                case self::Unscoped:
                case self::FastString:
                case self::DynamicFastString:
                case self::SynchronizedFastString:
                    return true;
                default:
                    return false;
            }
        }

        public static function IsValid(string $kind)
        {
            return in_array($kind, self::GetAll());
        }

        public static function GetAll(): array
        {
            return [
                self::Unscoped,
                self::FastLog,
                self::DynamicFastLog,
                self::SynchronizedFastLog,
                self::FastFlag,
                self::DynamicFastFlag,
                self::SynchronizedFastFlag,
                self::FastInteger,
                self::DynamicFastInteger,
                self::SynchronizedFastInteger,
                self::FastString,
                self::DynamicFastString,
                self::SynchronizedFastString
            ];
        }
    }

    class ClientSettings
    {


        private static function UpdateSetting(string $applicationName, string $settingName, string $value, string $kind)
        {

            $applicationId = ClientSettingsApplications::GetApplication($applicationName)['id'];

            if ($applicationId === null) {
                throw new Error("The application '$applicationName' does not exist.");
            }

            $query = $GLOBALS['pdo']->prepare("UPDATE `clientsettings` SET `value` = :value, `kind` = :kind WHERE `application` = :application AND `name` = :name");
            $query->bindParam(':application', $applicationId, PDO::PARAM_INT);
            $query->bindParam(':name', $settingName, PDO::PARAM_STR);
            $query->bindParam(':value', $value, PDO::PARAM_STR);
            $query->bindParam(':kind', $kind, PDO::PARAM_STR);

            $query->execute();
        }

        private static function InsertSetting(string $applicationName, string $settingName, string $value, string $kind)
        {

            $applicationId = ClientSettingsApplications::GetApplication($applicationName)['id'];

            if ($applicationId === null) {
                throw new Error("The application '$applicationName' does not exist.");
            }

            $query = $GLOBALS['pdo']->prepare("INSERT INTO `clientsettings` (`application`, `name`, `value`, `kind`) VALUES (:application, :name, :value, :kind)");
            $query->bindParam(':application', $applicationId, PDO::PARAM_INT);
            $query->bindParam(':name', $settingName, PDO::PARAM_STR);
            $query->bindParam(':value', $value, PDO::PARAM_STR);
            $query->bindParam(':kind', $kind, PDO::PARAM_STR);

            $query->execute();
        }

        private static function GetFormattedSettingName(string $name, string $kind)
        {
            if ($kind !== 'Unscoped') {
                return ClientSettingsKind::ToFormattedKind($kind) . $name;
            } else {
                return $name;
            }
        }

        /**
         * Gets the settings for the specified application.
         * 
         * @param string $applicationName The name of the application.
         * 
         * @return array An array of settings.
         */
        public static function GetSettings(string $applicationName)
        {

            $applicationId = ClientSettingsApplications::GetApplication($applicationName)['id'];

            if ($applicationId === null) {
                return [];
            }

            // get the settings
            $query = $GLOBALS['pdo']->prepare("SELECT * FROM `clientsettings` WHERE `application` = :application");
            $query->bindParam(':application', $applicationId, PDO::PARAM_INT);

            $query->execute();

            // return the settings
            return $query->fetchAll(PDO::FETCH_ASSOC);
        }

        /**
         * Gets the value of the specified setting.
         * 
         * @param string $applicationName The name of the application.
         * @param string $name The name of the setting.
         * 
         * @return array The value of the setting.
         */
        public static function GetSetting(string $applicationName, string $name)
        {
            $applicationId = ClientSettingsApplications::GetApplication($applicationName)['id'];

            if ($applicationId === null) {
                return null;
            }

            // get the setting
            $query = $GLOBALS['pdo']->prepare("SELECT * FROM `clientsettings` WHERE `application` = :application AND `name` = :name");
            $query->bindParam(':application', $applicationId, PDO::PARAM_INT);
            $query->bindParam(':name', $name, PDO::PARAM_STR);

            $query->execute();

            // return the setting
            return $query->fetch(PDO::FETCH_ASSOC);
        }

        /**
         * Gets a list of all the settings for the specified application, formatting them for the service.
         * 
         * @param string $applicationName The name of the application.
         * 
         * @return array An array of settings.
         */
        public static function GetSettingsFormatted(string $applicationName)
        {
            // get the settings
            $settings = self::GetSettings($applicationName);

            // format the settings
            $formattedSettings = array();

            foreach ($settings as $setting) {
                $formattedSettings[self::GetFormattedSettingName($setting['name'], $setting['kind'])] = ClientSettingsKind::FormatValueForKind($setting['kind'], $setting['value']);
            }

            return $formattedSettings;
        }

        /**
         * Gets the value of the specified setting.
         * 
         * @param string $applicationName The name of the application.
         * @param string $name The name of the setting.
         * 
         * @return mixed The value of the setting.
         */
        public static function GetSettingValue(string $applicationName, string $name)
        {
            $setting = self::GetSetting($applicationName, $name);

            if ($setting == null) {
                return null;
            } else {
                return $setting['value'];
            }
        }

        /**
         * Writes the specified setting to the database.
         * 
         * @param string $applicationName The name of the application.
         * @param string $name The name of the setting.
         * @param string $kind The kind of the setting.
         * @param string $value The value of the setting.
         */
        public static function WriteSetting(string $applicationName, string $name, string $kind, $value)
        {
            if (!ClientSettingsKind::IsValid($kind)) {
                throw new Error('The kind "' . $kind . '" is not valid.');
            }

            if (!ClientSettingsKind::ValueIsValidForKind($value, $kind)) {
                throw new Error('The value "' . $value . '" is not valid for kind "' . $kind . '" which is of the type "' . gettype($value) . '", but the "' . $kind . '" only supports the type "' . ClientSettingsKind::GetValidTypeForKind($kind) . '".');
            }

            if (!ClientSettingsApplications::ApplicationExists($applicationName)) {
                throw new Error('The application "' . $applicationName . '" does not exist.');
            }

            $setting = self::GetSetting($applicationName, $name);

            // if the setting exists, update it
            if ($setting) {
                self::UpdateSetting($applicationName, $name, $value, $kind);
            } else {
                // if the setting does not exist, insert it
                self::InsertSetting($applicationName, $name, $value, $kind);
            }
        }

        /**
         * Deletes all the settings with the specified application name.
         * 
         * @param string $applicationName The name of the application.
         */
        public static function DeleteSettings(string $applicationName)
        {
            $applicationId = ClientSettingsApplications::GetApplication($applicationName)['id'];

            if ($applicationId === null) {
                throw new Error("The application '$applicationName' does not exist.");
            }

            // delete the settings
            $query = $GLOBALS['pdo']->prepare("DELETE FROM `clientsettings` WHERE `application` = :application");
            $query->bindParam(':application', $applicationId, PDO::PARAM_INT);

            $query->execute();
        }

        /**
         * Deletes the specified setting.
         * 
         * @param string $applicationName The name of the application.
         * @param string $name The name of the setting.
         */
        public static function DeleteSetting(string $applicationName, string $name)
        {

            $applicationId = ClientSettingsApplications::GetApplication($applicationName)['id'];

            if ($applicationId === null) {
                throw new Error("The application '$applicationName' does not exist.");
            }

            // delete the setting
            $query = $GLOBALS['pdo']->prepare("DELETE FROM `clientsettings` WHERE `application` = :application AND `name` = :name");
            $query->bindParam(':application', $applicationId, PDO::PARAM_INT);
            $query->bindParam(':name', $name, PDO::PARAM_STR);

            $query->execute();
        }
    }
}
