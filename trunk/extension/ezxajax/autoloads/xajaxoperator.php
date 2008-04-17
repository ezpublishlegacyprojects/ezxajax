<?php
/**
 * File containing the XajaxOperator class.
 *
 * @package xajax
 * @version //autogentag//
 * @copyright Copyright (C) 2006 SCK-CEN All rights reserved.
 * @license http://www.gnu.org/licenses/lgpl.txt LGPL License
 */
class XajaxOperator
{
    function XajaxOperator( )
    {
        $this->Operators = array( 'xajax_javascript' );
    }

    function &operatorList( )
    {
        return $this->Operators;
    }

    /*!
     \return true to tell the template engine that the parameter list exists per operator type.
    */
    function namedParameterPerOperator()
    {
        return true;
    }

        /*!
     See eZTemplateOperator::namedParameterList()
    */
    function namedParameterList()
    {
        return array
        (
            'xajax_javascript' => array()
        );
    }

    /*!
     \reimp
    */
    function modify( &$tpl, &$operatorName, &$operatorParameters, &$rootNamespace, &$currentNamespace, &$operatorValue, &$namedParameters )
    {
        switch ( $operatorName )
        {
            case 'xajax_javascript':
                {
                    include_once( 'extension/ezxajax/lib/xajax/xajax_core/xajax.inc.php' );
                    include_once( 'lib/ezutils/classes/ezuri.php' );
                    $xajaxModuleView = '/xajax/call';
                    eZURI::transformURI( $xajaxModuleView );
                    $xajax = new xajax( $xajaxModuleView );

                    include_once( 'lib/ezutils/classes/ezextension.php' );
                    include_once( 'lib/ezutils/classes/ezini.php' );

                    $ini = eZINI::instance( 'xajax.ini' );

                    // left here for backward compatibility
                    if ( $ini->hasVariable( 'DebugSettings', 'DebugAlert' ) )
                    {
                        $useDebug = ( $ini->variable( 'DebugSettings', 'DebugAlert' ) == 'enabled' );
                        $xajax->setFlag( 'debug', $useDebug );
                    }

                    // left here for backward compatibility
                    if ( $ini->hasVariable( 'CompressionSettings', 'UseUncompressedScripts' ) )
                    {
                        $useUncompressedScripts = ( $ini->variable( 'CompressionSettings', 'UseUncompressedScripts' ) == 'enabled' );
                        $xajax->setFlag( 'useUncompressedScripts', $useUncompressedScripts );
                    }

                    if ( $ini->hasGroup( 'Flags' ) )
                    {
                        $flags =& $ini->group( 'Flags' );
                        foreach ( $flags as $flagName => $flagINIValue )
                        {
                            $normalizedFlagValue = strtolower( trim( $flagINIValue ) );
                            $flagValue = ( $normalizedFlagValue == 'enabled' || $normalizedFlagValue == 'true' );
                            $xajax->setFlag( $flagName, $flagValue );
                        }
                    }

                    $functionFiles = $ini->variable( 'ExtensionSettings', 'AvailableFunctions' );
                    $defaultFunctionDirs = array( 'xajax' );
                    $extensionDirectories = array_merge( $defaultFunctionDirs, $ini->variable( 'ExtensionSettings', 'ExtensionDirectories' ) );
                    $directoryList = eZExtension::expandedPathList( $extensionDirectories, 'xajax' );

                    if ( count( $functionFiles ) > 0 )
                    {
                        $isWindows = strtolower( substr( PHP_OS, 0, 3 ) ) == 'win';
                        foreach ( $functionFiles as $function => $functionFile )
                        {
                            $found = false;
                            $triedPaths = array();
                            foreach ( $directoryList as $directory )
                            {
                                if ( substr( $functionFile, -4 ) != '.php' )
                                {
                                    $functionFile .= '.php';
                                }

                                $handlerFile = $directory . '/' . $functionFile;
                                $realPath = realpath( $handlerFile );

                                $triedPaths[] = $handlerFile;
                                if ( $realPath !== false )
                                {
                                    // Microsoft Windows uses case insensitive path names. However, We want people to write cross platform xajax extensions.
                                    // Therefore we will force them to use the file name as it is on the file system.
                                    if ( $isWindows &&
                                         strcmp( $handlerFile, str_replace( '\\', '/', substr( $realPath, - strlen( $handlerFile ) ) ) ) !== 0 )
                                    {
                                        eZDebug::writeError( 'In order to write cross platform extensions, the function file names specified by AvailableFunctions[] in xajax.ini should exactly match the file names on the file system (case sensitive)!' . "\r\n" .
                                                             'Please correct! Requested: ' . $handlerFile . ', found but not accepted: ' . str_replace( '\\', '/', substr( $realPath, - strlen( $handlerFile ) ) ), 'XajaxOperator::modify()' );
                                        break;
                                    }
                                    // Still need to check if file exists, because on BSD systems realpath() doesn't fail if only the last path component doesn't exist.
                                    // See http://www.php.net/realpath.
                                    else if ( file_exists( $handlerFile ) )
                                    {
                                        $xajax->registerFunction( $function, $handlerFile );
                                        $found = true;
                                        break;
                                    }
                                }
                            }

                            if ( !$found )
                            {
                                eZDebug::writeError( 'Unable to find ezxajax function file, tried paths: ' . implode( ', ', $triedPaths ), 'XajaxOperator::modify()' );
                            }
                        }
                    }

                    include_once( 'lib/ezutils/classes/ezsys.php' );
                    $sys = eZSys::instance();
                    $operatorValue = $xajax->getJavascript( $sys->wwwDir() . '/extension/ezxajax/design/standard/javascript/' );

                    if ( $ini->variable( 'GeneralSettings', 'ActivityIndicator' ) == 'enabled' )
                    {
                        /*
                           Now add some code to insert and control an ajax activity indicator.
                           In browsers where DOMContentLoaded is supported, we load the javaScript on that event,
                           in other browsers, we'll use the load event instead

                           Maybe this can be improved in the future. Interesting resources:
                           - http://dean.edwards.name/weblog/2005/09/busted/
                           - http://dean.edwards.name/weblog/2006/06/again/
                        */
                        $operatorValue.='<script type="text/javascript">
                            <!--
                            // BC with 0.5 beta 1
                            xajax.config.defaultReturnValue = true;

                            function xajax_activityIndicatorInit(e) {
                                if (!e){
                                   var e=window.event;
                                }

                                // return if this function has been flagged
                                if (arguments.callee.done) {
                                    return;
                                }

                                // flag this function so we do not do the same thing twice
                                arguments.callee.done = true;

                                var b=document.getElementsByTagName("body")[0];
                                var pImg=new Image();
                                pImg.src = "' . $this->ezimage( "ajax-activity_indicator.gif" ) . '";
                                b.appendChild( pImg );
                                pImg.setAttribute("id", "spinner");
                                pImg.style.display="none";
                                pImg.style.position="absolute";
                                pImg.style.top="50%";
                                pImg.style.left="50%";
                                pImg.style.backgroundColor="#CCC";
                            }


                            if (document.addEventListener) {
                                document.addEventListener("DOMContentLoaded", xajax_activityIndicatorInit, false );
                                // fallback for browsers supporting addEventListener but not the DOMContentLoaded event
                                window.addEventListener("load", xajax_activityIndicatorInit, false );
                            } else if (window.attachEvent) {
                                // IE
                                window.attachEvent("onload", xajax_activityIndicatorInit);
                            }

                            xajax.callback.global.onResponseDelay = function(){
                                var screenProp = ezjslib_getScreenProperties();
                                var screenCenterY = screenProp.ScrollY + screenProp.Height/2;
                                var screenCenterX = screenProp.ScrollX + screenProp.Width/2;
                                var pImg = xajax.$("spinner");
                                if ( pImg )
                                {
                                    pImg.style.top = (screenCenterY - pImg.height/2 ) + "px";
                                    pImg.style.left = ( screenCenterX - pImg.width/2 ) + "px";
                                    pImg.style.display = "inline";
                                }
                            };

                            xajax.callback.global.beforeResponseProcessing = function(){
                                var pImg = xajax.$("spinner");
                                if ( pImg )
                                {
                                    pImg.style.display = "none";
                                }
                            };
                            -->
                            </script>';
                    }
                }break;
            default:
                {
                    eZDebug::writeError( 'Unknown operator: ' . $operatorName, 'xajaxoperator.php' );
                }
        }
    }

    /*
        some code used by the ezimage operator
        taken from kernel/common/ezurloperator.php
    */
    function ezimage( $path )
    {
        include_once( 'kernel/common/eztemplatedesignresource.php' );
        $bases = eZTemplateDesignResource::allDesignBases();

        include_once( 'lib/ezutils/classes/ezsys.php' );
        $sys = eZSys::instance();

        $imageFound = false;
        foreach ( $bases as $base )
        {
            if ( file_exists( $base . "/images/" . $path ) )
            {
                $path = $sys->wwwDir() . '/' . $base . '/images/'. $path;
                break;
            }
        }

        $path = htmlspecialchars( $path );

        return $path;
    }

    /// \privatesection
    var $Operators;
};

?>
