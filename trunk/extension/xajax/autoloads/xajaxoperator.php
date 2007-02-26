<?php

class XajaxOperator
{
    var $Operators;

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
                    include_once( 'extension/xajax/lib/xajax/xajax_core/xajax.inc.php' );
                    include_once( 'lib/ezutils/classes/ezuri.php' );
                    $xajaxModuleView = '/xajax/call';
                    eZURI::transformURI( $xajaxModuleView );
                    $xajax = new xajax( $xajaxModuleView );

                    include_once( 'lib/ezutils/classes/ezextension.php' );
                    include_once( 'lib/ezutils/classes/ezini.php' );

                    $ini =& eZINI::instance( 'xajax.ini' );

                    if ( $ini->variable( 'DebugSettings', 'DebugAlert' ) == 'enabled' )
                    {
                        $xajax->setFlag( 'debug', true );
                    }

                    if ( $ini->variable( 'CompressionSettings', 'UseUncompressedScripts' ) == 'enabled' )
                    {
                        $xajax->setFlag( 'useUncompressedScripts', true );
                    }

                    $functionFiles = $ini->variable( 'ExtensionSettings', 'AvailableFunctions' );
                    $extensionDirectories = array_merge( 'xajax', $ini->variable( 'ExtensionSettings', 'ExtensionDirectories' ) );
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

                    include_once( 'lib/ezutils/classes/ezsys.php' );
                    $sys =& eZSys::instance();
                    $operatorValue = $xajax->getJavascript( $sys->wwwDir() . '/extension/xajax/design/standard/javascript/' );

                    //js stuff that add progress indicator
                    // Since IE6 is not supported we need an ini-flag to handle
                    if ( $ini->variable( 'Compatibility', 'IE6' ) == 'true' )
                    {

                    }
                    else
                    {
                        $operatorValue.='<script type="text/javascript">
                            <!--
                            function xajax_activityIndicatorInit() {
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

                            // Only Mozilla currently supported
                            // For IE support, take a look at http://dean.edwards.name/weblog/2005/09/busted/
                            if (document.addEventListener) {
                                document.addEventListener("DOMContentLoaded", xajax_activityIndicatorInit, false );
                            }

                            xajax.callback.global.onResponseDelay = function(){
                                screenProp = ezjslib_getScreenProperties();
                                screenCenterY = screenProp.ScrollY + screenProp.Height/2;
                                screenCenterX = screenProp.ScrollX + screenProp.Width/2;
                                pImg = xajax.$("spinner");
                                pImg.style.top = (screenCenterY - pImg.height/2 ) + "px";
                                pImg.style.left = ( screenCenterX - pImg.width/2 ) + "px";
                                pImg.style.display = "inline";
                            };

                            xajax.callback.global.beforeResponseProcessing = function(){
                                pImg = xajax.$("spinner");
                                pImg.style.display = "none";
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
        $sys =& eZSys::instance();

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
