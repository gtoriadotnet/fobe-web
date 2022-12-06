<?php

namespace Fobe\Moderation {

    use PDO;

    class Filter
    {
        public static function FilteredWordList()
        {
            return array(
                'Afro-engineering',
                'Afroengineering',
                'Afro engineering',
                'African engineering',
                'Africanengineering',
                'African-engineering',
                'nigger rigging',
                'nigger-rigging',
                'niggerrigging',
                'Ashke Nazi',
                'Ashke-Nazi',
                'AshkeNazi',
                'nazi',
                'hitler',
                'gas chambers',
                'gaschambers',
                'gas-chambers',
                'gas chamber',
                'gaschamber',
                'gas-chamber',
                'genocide',
                'Beaner',
                'Beaney',
                'boonie',
                'Coon',
                'Coonass',
                'Cracker',
                'Dothead',
                'Jewboy',
                'Jigaboo',
                'jiggabo',
                'jigarooni',
                'jijjiboo',
                'zigabo',
                'jigger',
                'Niglet',
                'nigglet',
                'Nig-nog',
                'Nignog',
                'Nigger',
                'niger',
                'nigor',
                'niggur ',
                'niggar',
                //'Nigga',
                //'nigga',
                'Porch monkey',
                'Porchmonkey',
                'porch-monkey',
                'Sand nigger',
                'Sandnigger',
                'Sand-nigger',
                'Spearchucker',
                'spick',
                'Tacohead',
                'TarBaby',
                'Tar Baby',
                'Tar-Baby',
                'Towel head',
                'Towelhead',
                'Towel-head',
                'Wetback',
                'Wigger',
                'Whigger',
                'Wigga',
                'White trash',
                'Whitetrash',
                'White-trash',
                'Whitey',
                'Zipperhead',
                'fagot',
                'faggot',
                'fegot',
                'faget',
                'feget',
                'fag',
                'rape',
                'tranny',
                'tarbaby',
                'tar baby',
                'blackface',
                'black face',
                'dogwater',
                'dog water',
                'dog-water',
                'Mirai'
            );
        }

        public static function IsTextFiltered(string $text)
        {
            foreach(Filter::FilteredWordList() as $a) {
                if (stripos($text,$a) !== false) {
                    return true;
                }
            }
            return false;
        }      
        
        public static function FilterText(string $text)
        {
            $badlist = Filter::FilteredWordList();
            $filterCount = sizeof($badlist);
            for ($i = 0; $i < $filterCount; $i++) {
                $text = preg_replace_callback('/(' . $badlist[$i] . ')/i', function($matches){return str_repeat('#', strlen($matches[0]));}, $text);
            }
            return $text;
        }
    }
}
