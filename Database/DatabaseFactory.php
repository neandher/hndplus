<?php

/**
 * Description of DatabaseFactory
 *
 * @author Neandher
 */

    class DatabaseFactory {
        
        public static function factory( $key ) {                                                
                
            if( $key == null ){
            
                $key = DB_TYPE;                               
            }
            
            switch( $key ) {

                case 'db_mysql': 

                  //code

                break;

                case 'db_mysqlPDO': 

                    if( !Registry::getInstance()->isValid($key) )
                            Registry::getInstance()->set($key, new MySqlPDO());

                    return Registry::getInstance()->get($key);

                break;
                
                case 'db_sqlsrvPDO': 

                    if( !Registry::getInstance()->isValid($key) )
                            Registry::getInstance()->set($key, new SqlSrvPDO());

                    return Registry::getInstance()->get($key);

                break;
                
                case 'db_sqlsrvPDO2': 

                    if( !Registry::getInstance()->isValid($key) )
                            Registry::getInstance()->set($key, new SqlSrvPDO2());

                    return Registry::getInstance()->get($key);

                break;
                
                case 'db_mssql': 

                    //code

                break;
            }             
        }
        
    }
