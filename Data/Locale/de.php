<?php

/**
 * WebmasterTools
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/flexContent
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

if ('á' != "\xc3\xa1") {
    // the language files must be saved as UTF-8 (without BOM)
    throw new \Exception('The language file ' . __FILE__ . ' is damaged, it must be saved UTF-8 encoded!');
}

return array(
    'Check the created sitemaps'
        => 'Überprüfen der erstellten Sitemaps',
    'Configurations'
        => 'Einstellungen',

    'Information about the WebmasterTools'
        => 'Information über die WebmasterTools',

    'Load'
        => 'Laden',

    'Please visit the <a href="%url%" target="_blank">Wiki</a> to get more information about <em>%file%</em>.'
        => 'Bitte besuchen Sie das <a href="%url%" target="_blank">Wiki</a> um Informationen über den Aufbau und die Verwendung der Datei <em>%file%</em> zu erhalten.',
    'Protocols'
        => 'Protokolle',

    'kitFramework configurations'
        => 'kitFramework Einstellungen',
    'kitFramework protocols'
        => 'kitFramework Protokolle',

    'Save configuration'
        => 'Einstellungen sichern',
    'Successfull saved the configuration file %file%.'
        => 'Die Konfigurationsdatei <strong>%file%</strong> wurde erfolgreich gesichert.',

);
