<?php

namespace Gvs\ShortLink;

use \RedBeanPHP\R as R;

class ShortLinkPersister {
    
    public function __construct($driver, $host, $dbName, $user, $password) {
        $connection = "{$driver}:host={$host};dbname={$dbName};";
        R::setup($connection, $user, $password);
    }
    
    public function createShortLink($fullUrl) {
        $shortLink = R::dispense('sllink');
        $shortLink->Url = $fullUrl;
        $shortLink->HitCount = 0;
        $shortLink->CreatedOn = new \DateTime();
        $id = R::store($shortLink);
        
        $coder = new CodeConverter();
        return $coder->encode($id);
    }
    
    public function getFullUrl($shortLink) {
        $coder = new CodeConverter();
        $id = $coder->decode($shortLink);
        
        $shortLink = R::load('sllink', $id);
        ++$shortLink->HitCount;
        R::store($shortLink);
        return $shortLink->Url;
    }
    
}
