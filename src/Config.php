<?php

// namespace
namespace Nettools\ComposerInterface;



/**
 * Config class to ComposerInterface
 *
 * @see ComposerInterface
 */
class Config
{
    /** @var object Litteral object used to store the config data */
    protected $_data = NULL;
    
    
    /** 
     * Magic method to read data
     * 
     * @param string $k Key to fetch
     * @return mixed Value associated to $k
     */
    public function __get($k)
    {
        return $this->_data->{$k};
    }
    
    
     /** 
     * Magic method to set data (allows overriding values)
     * 
     * @param string $k Key to define
     * @param mixed $v Value to associate to key $k
     */
    public function __set($k, $v)
    {
        $this->_data->{$k} = $v;
    }
    
    
   /** 
     * Constructor
     */
    public function __construct(\stdClass $data)
    {
        $this->_data = $data;
    }
    
    
    /** 
     * Read config data from an associative array
     * 
     * @param string[] $data Associative array with config data
     */
    public static function fromArray($data)
    {
        if ( !is_array($data) ) 
            throw new ComposerException('Parameter to Config::fromArray is not an array.');

        return new Config((object) $data);
    }
    
    
    /** 
     * Read config data from a JSON file
     * 
     * @param string $path Filepath to JSON config file
     * @throws ComposerException If JSON config file cannot be decoded, an exception is thrown
     */
    public static function fromJSON($path)
    {
        if ( !file_exists($path) )
            $json = (object)[];
        else
            $json = json_decode(file_get_contents($path));

        // if error when decoding json
        if ( is_null($json) )
            throw new ComposerException('Error when decoding JSON config file : ' . json_last_error_msg());
        
        return new Config($json);
    }
}


?>