<?php

function sayHelloWorld()
{
    $objResponse = new xajaxResponse();
    $objResponse->alert( 'Hello world!' );
    return $objResponse;
}

?>