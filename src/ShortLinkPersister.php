<?php

namespace Gvs\ShortLink;

use \RedBeanPHP\R as R;

class ShortLinkPersister {
    
    private $_connectionString;
    private $_user;
    private $_pwd;
    
	/**
	 * Construct and initialize the Persister with database connection details
	 */
    public function __construct($driver, $host, $dbName, $user, $password) {
        $this->_connectionString ="{$driver}:host={$host};dbname={$dbName};";
        $this->_user = $user;
        $this->_pwd = $password;
    }
    
	/**
	 * Creates a shortlink code for the given URL, persists it into the database
	 * identified by the given connection details.
	 */
    public function createShortLink($fullUrl) {
        $this->openConnection();
        $shortLink = R::dispense('sllink');
        $shortLink->Url = $fullUrl;
        $shortLink->HitCount = 0;
        $shortLink->CreatedOn = new \DateTime();
        $id = R::store($shortLink);
        
        $this->closeConnection();
        $coder = new CodeConverter();
        return $coder->encode($id);
    }
    
	/**
	 * Returns the original URL from the given short link code.
	 * Tracks the hit count for accessing the short link
	 */
    public function getFullUrl($shortLink) {
        $coder = new CodeConverter();
        $id = $coder->decode($shortLink);
        
        $this->openConnection();
        $shortLink = R::load('sllink', $id);
        ++$shortLink->HitCount;
        R::store($shortLink);
        $this->closeConnection();

        return $shortLink->Url;
    }
    
	/**
	 * Ensures that the connection to database is setup in RedBean ORM
	 */
    private function openConnection() {
        if (!R::testConnection()) {
            R::setup($this->_connectionString, $this->_user, $this->_pwd);
        }
    }
    
	/**
	 * Closes the database connection in RedBean ORM
	 */
    private function closeConnection() {
        if (R::testConnection()) {
            R::close();
        }
    }
    
}
