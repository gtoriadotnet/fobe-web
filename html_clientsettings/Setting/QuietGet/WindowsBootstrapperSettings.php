<?php

use Alphaland\Web\WebsiteSettings;

$gameFileVersion = WebsiteSettings::GetSetting('GameFileVersion');

echo '{"ExeVersion": "' . $gameFileVersion . '", "ValidateInstalledExeVersion": "True", "ShowInstallSuccessPrompt": "True"}';
