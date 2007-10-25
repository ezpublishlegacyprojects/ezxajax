<?php
/**
 * File eztemplateautoload.php
 *
 * @package xajax
 * @version //autogentag//
 * @copyright Copyright (C) 2006 SCK-CEN All rights reserved.
 * @license http://www.gnu.org/licenses/lgpl.txt LGPL License
 */
/*! \file eztemplateautoload.php
*/

// Operator autoloading

$eZTemplateOperatorArray = array();

$eZTemplateOperatorArray[] = array( 'script' => 'extension/xajax/autoloads/xajaxoperator.php',
                                    'class' => 'XajaxOperator',
                                    'operator_names' => array( 'xajax_javascript' ) );
                                    


?>
