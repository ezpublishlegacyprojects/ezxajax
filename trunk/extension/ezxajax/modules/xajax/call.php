<?php
/**
 * File call.php
 *
 * @package xajax
 * @version //autogentag//
 * @copyright Copyright (C) 2006 SCK-CEN All rights reserved.
 * @license http://www.gnu.org/licenses/lgpl.txt LGPL License
 */
    include_once( 'extension/ezxajax/lib/xajax/xajax_core/xajax.inc.php' );
    include_once( 'lib/ezutils/classes/ezsys.php' );

    $xajax = new xajax( eZSys::indexDir() . '/xajax/call' );
    $xajax->setFlag( 'exitAllowed', false );

    include_once( 'lib/ezutils/classes/ezextension.php' );
    include_once( 'lib/ezutils/classes/ezini.php' );

    $ini = eZINI::instance( 'xajax.ini' );

    if ( $ini->variable( 'DebugSettings', 'DebugAlert' ) == 'enabled' )
    {
        $xajax->setFlag( 'debug', true );
    }

    $functionFiles = $ini->variable( 'ExtensionSettings', 'AvailableFunctions' );
    $extensionDirectories = $ini->variable( 'ExtensionSettings', 'ExtensionDirectories' );
    $directoryList = eZExtension::expandedPathList( $extensionDirectories, 'xajax' );

    if ( count( $functionFiles ) > 0 )
    {
        foreach ( $functionFiles as $function => $functionFile )
        {
            foreach ( $directoryList as $directory )
            {
                $handlerFile = $directory . '/' . strtolower( $functionFile ) . '.php';
                if ( file_exists( $handlerFile ) )
                {
                    $xajax->registerFunction( $function, $handlerFile );
                }
            }
        }
    }

    $xajax->processRequest();

    include_once( 'lib/ezutils/classes/ezexecution.php' );
    eZDB::checkTransactionCounter();
    eZExecution::cleanup();
    eZExecution::setCleanExit();
    exit();
?>
