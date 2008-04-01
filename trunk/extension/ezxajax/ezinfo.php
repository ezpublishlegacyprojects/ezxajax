<?php
/**
 * File containing the xajaxInfo class.
 *
 * @package xajax
 * @version //autogentag//
 * @copyright Copyright (C) 2006 SCK-CEN All rights reserved.
 * @license http://www.gnu.org/licenses/lgpl.txt LGPL License
 */
class ezxajaxInfo
{
    static function info()
    {
        return array(
            'Name' => "<a href='http://projects.ez.no/ezxajax'>ezxajax</a> - xajax eZ publish integration",
            'Version' => "2.0.0",
            'Copyright' => "Copyright (C) 2006 SCK-CEN",
            'Author' => "Kristof Coomans",
            'License' => "GNU Lesser General Public License v2.1",
            'Includes the following third-party software' => array( 'Name' => 'xajax',
                                                                    'Version' => '0.5 beta',
                                                                    'License' => 'GNU Lesser General Public License',
                                                                    'More information' => 'http://www.xajaxproject.org/'
                                                                  )
        );
    }
}
?>
