<?php

/**
 * Description of LoggedException
 *
 * @author Neandher
 */
class LoggedExceptionHelper extends Exception {
    
    public function __construct ( Exception $exception ){                                  
        
        $this->log($exception);
        
        set_exception_handler('log');                  
        
        parent::__construct( $exception->getMessage() );        
    }
    
    protected function log( Exception $exception ) {
        
        $file = LOG_PATH;
        
        $strMsg  = $exception->__toString()."\n";
        $strMsg .= "Data: ".date('d/m/Y - H:i')."\n";
        
        if( !strstr( $strMsg,'General error' ) ){
            @file_put_contents($file, $strMsg, FILE_APPEND);        
        }
    }        
}