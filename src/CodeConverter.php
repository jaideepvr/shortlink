<?php

namespace Gvs\ShortLink;

class CodeConverter
{
    /**
     * @var string
     */
    const ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    
    /**
     * @var string
     */
    private $alphabet;
    /**
     * @param string $alphabet (optional)
     */
    public function __construct($alphabet = null)
    {
        if (strlen($alphabet) === 1) {
            throw new \Exception('Custom alphabet must have at least 2 characters');
        }
        
        $this->alphabet = $alphabet ?: self::ALPHABET;
    }
    
    /**
     * @param int $shortLinkId
     * @return string
     */
    public function encode($shortLinkId)
    {
        if ($shortLinkId < 0 || !is_int(filter_var($shortLinkId, FILTER_VALIDATE_INT))) {
            throw new \Exception('$shortLinkId must be a 0 or positive integer');
        } else if ($shortLinkId === 0) {
            return $this->alphabet[0];
        }
        
        for ($arr = [], $base = strlen($this->alphabet); $shortLinkId > 0;) {
            $rem = $shortLinkId % $base;
            $shortLinkId = floor($shortLinkId / $base);
            // array_unshift($arr, $this->alphabet[$rem]); // Not sure, might be slow
            $arr[] = $this->alphabet[$rem];
        }
        
        // return join('', $arr);
        return join('', array_reverse($arr));
    }
    
    /**
     * @param string $shortLinkCode
     * @return int
     */
    public function decode($shortLinkCode)
    {
        // Cannot check for (!$shortLinkCode) because '0' is falsy valid
        if ($shortLinkCode === '' || $shortLinkCode === null) {
            throw new \Exception('Cannot decode a null or empty string');
        }
        $base = strlen($this->alphabet);
        return array_reduce(str_split($shortLinkCode), function ($carry, $item) use ($base) {
            $strpos = strpos($this->alphabet, $item);
            // Cannot do !$strpos because 0 is falsy and valid
            if ($strpos === false) {
                throw new DecodingException(
                    "String contained characters not present in internal alphabet"
                );
            }
            return ($carry * $base) + $strpos;
        }, 0);
    }
}
