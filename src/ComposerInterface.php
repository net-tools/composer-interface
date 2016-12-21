<?php
/**
 * ComposerInterface
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */



// namespace
namespace Nettools\ComposerInterface;


include_once "ComposerException.php";
include_once "Config.php";



/**
 * Main class providing access to composer
 */
class ComposerInterface
{
    // ------ PROTECTED -------
    
    /** @var Config User config */
    protected $_config = NULL;
 
    /** @var Config Root tech config ; user is not concerned by those parameters */
    protected $_rootConfig = NULL;
    
    /** @var string Filepath for home folder of composer project */
    protected $_libc = NULL;
     
    // ------ /PROTECTED -------

	
	/** 
     * Delete a repository
     *
     * @param string $url Url of repository to remove
     */
	public function repository_remove($url)
	{
		$json = json_decode(file_get_contents($this->_libc . '/composer.json'));

		// if repositories defined
		if ( isset($json->repositories) )
		{
            foreach ( $json->repositories as $k=>$repo )
                if ( $repo->url == $url )
                {
                    // delete value
                    unset($json->repositories[$k]);
                    
                    // use array_merge to reset numeric keys to 0
                    $json->repositories = array_merge($json->repositories);
                    
                    // update composer.json
                    $f = fopen($this->_libc . '/composer.json', 'w');
                    fwrite($f, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                    fclose($f);
                    break;
                }
		}
	}

    
    /**
     * Add a repository
     * 
     * @param string $type Type of repository 
     * @param string $url Url of repository
     */
	public function repository_add($type, $url)
	{
		$json = json_decode(file_get_contents($this->_libc . '/composer.json'));

		if ( !isset($json->repositories) )
			$json->repositories = array();
			
		$json->repositories[] = (object) array(
									'type'	=> $type,
									'url'	=> $url
								);
		
		$f = fopen($this->_libc . '/composer.json', 'w');
		fwrite($f, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
		fclose($f);
	}

	
	/**
     * Composer remove statement
     *
     * @param string $package Package name to remove ("vendor/name")
     * @return string Shell command output
     */
	public function package_remove($package)
	{
		return $this->command("remove --no-progress $package");
	}
    
    
	/**
     * Composer require statement
     * 
     * @param string $package Package name to require ("vendor/name")
     * @return string Shell command output
     */
	public function package_require($package)
	{
		return $this->command("require --no-progress $package");
	}
     
    
	/**
     * Composer show package details statement
     * 
     * @param string $package Package name ("vendor/name")
     * @return string Shell command output
     */
	public function package_show($package)
	{
		return $this->command("show $package");
	}
     
    
	/**
     * Composer show package dependencies
     * 
     * @param string $package Package name ("vendor/name")
     * @return string Shell command output
     */
	public function package_show_dependencies($package)
	{
		return $this->command("show -t $package");
	}
     
    
	/**
     * Composer update package statement
     * 
     * @param string $package Package name ("vendor/name")
     * @return string Shell command output
     */
	public function package_update($package)
	{
		return $this->command("update --no-progress $package");
	}
     
    
	/**
     * Composer depends statement
     * 
     * @param string $package Package name ("vendor/name")
     * @return string Shell command output
     */
	public function package_depends($package)
	{
		return $this->command("depends $package -t");
	}
     
    
    /**
     * Composer archive statement
     *
     * @param string $package Package name to archive
     * @param string $format Format of archive (defaults to 'zip')
     * @param bool $download If true, the file will be downloaded immediately ; otherwise, we return a file path to the archive
     * @param string $downloadFilename Set this parameter to any suggested file name for the download (the filename the browser will suggest in the save as dialog)
     */
    public function package_archive($package, $format = 'zip', $download = true, $downloadFilename = 'archive.zip')
    {
		// execute archive command
		$ret = $this->command("archive $package --format=$format");
		if ( mb_strpos($ret, $this->_rootConfig->composer_archive_ok) === FALSE )
			throw new ComposerException('Archive command failed : ' . $ret);

		// if archived file found
		if ( !preg_match($this->_rootConfig->composer_archive_pattern, $ret, $regs) )
			throw new ComposerException('Archive file cannot be identified : ' . $ret);

		// exclude "./" at line start
		$f = substr($regs[0], 2);

        if ( $download )
        {
            // en-tête pour forcer le téléchargement et suggérer un nom
            header("Content-Type: application/$format; name=\"$downloadFilename\"");
            header("Content-Disposition: attachment; filename=\"$downloadFilename\"");
            header("Expires: 0");
            header("Cache-Control: no-cache, must-revalidate");
            header("Pragma: no-cache"); 

            readfile($this->_libc . "/$f");
            unlink($this->_libc . "/$f");
            die();
        }
        else    
            return $this->_libc . "/$f";        
    }
	
	
	
	/**
     * Composer clear-cache statement
     * 
     * @return string Shell command output
     */
	public function clear_cache()
	{
		return $this->command("clear-cache");
	}

    
    /**
     * Composer install statement
     * 
     * @return string Shell command output
     */
	public function install()
	{
		return $this->command("install --no-progress");
	}
	
	
	/**
     * Composer update statement
     *
     * @param bool $testmode Set this parameter to TRUE to simulate the command 
     * @return string Shell command output
     */
	public function update($testmode = false)
	{
        $testmode = $testmode ? '--dry-run':'';
		return $this->command("update $testmode --no-progress");
	}
	
	
	/**
     * Composer validate statement
     * 
     * @return string Shell command output
     */
	public function validate()
	{
		return $this->command("validate");
	}
	
	
	/**
     * Composer diagnose statement
     * 
     * @return string Shell command output
     */
	public function diagnose()
	{
		return $this->command("diagnose");
	}
   
    
	/**
     * Composer outdated statement
     * 
     * @return string Shell command output
     */
	public function outdated()
	{
		return $this->command("outdated");
	}
   
    
	/**
     * Composer self-update statement
     * 
     * @return string Shell command output
     */
	public function self_update()
	{
		return $this->command("self-update");
	}
   
    
	/**
     * Composer show statement
     * 
     * @return string Shell command output
     */
	public function show()
	{
		return $this->command("show");
	}
   
    
 	/**
     * Composer setup
     *
     * @param string|NULL $json Optionnal composer.json initial content
     */
	public function setup($json = NULL)
	{
		// download install script and run it
		$contextOptions = 
			array(
					 "ssl" => array(
					 "verify_peer"      => false,
					 "verify_peer_name" => false,
					 )
				);
		$script = file_get_contents($this->_rootConfig->composer_setup, false, stream_context_create($contextOptions));
		if ( !$script || !(mb_strpos($script, '<?') === 0) )
			throw new ComposerException('Cannot download PHP installation script');
			
		if ( !file_exists($this->_libc) )
			mkdir($this->_libc);
		
		$f = fopen($this->_libc . '/composer-setup.php', 'w');
		fwrite($f, $script);
		fclose($f);

	
		// execute setup
		$ret = $this->php_shell('composer-setup.php');

		if ( mb_strpos($ret, $this->_rootConfig->composer_install_ok) === FALSE )
			throw new ComposerException('Error during Composer setup : ' . $ret);


		// delete composer-setup.php
		unlink($this->_libc . '/composer-setup.php');
		
		
		// if composer.json does not exist, creating it
		if ( !file_exists($this->_libc . '/composer.json') )
		{	
			// default json 
			if ( !$json && file_exists(rtrim(__DIR__,'/') . '/RootConfig/composer.json.default.json') )
				$json = file_get_contents(rtrim(__DIR__,'/') . '/RootConfig/composer.json.default.json');
			
			$f = fopen($this->_libc . '/composer.json', 'w');
			fwrite($f, $json);
			fclose($f);
		}
	}
    
    
	/**
     * Execute shell command
     * 
     * @param string $script Composer command line
     * @return string Output of shell command
     */
	public function php_shell($script)
	{
		// path to php binary
		$phpbin = $this->_config->composer_phpbin;
		
		// path to composer home ; will be set in the shell environment. We recommand defining a home value
        // in the parent folder of the project, allowing sharing data between projects
		if ( $this->_config->composer_home )
            $home = $this->_config->composer_home;
        else
            $home = $_SERVER['DOCUMENT_ROOT'];
            
		putenv('HOME=' . $home);
		
		// navigate to composer project folder and execute command 
		return shell_exec("cd {$this->_libc} ; $phpbin $script 2>&1");
	}
    
    
    /**
     * Execute a composer command
     * 
     * @param string $cmdline Composer command line to execute
     * @return string Output of shell command
     */
    public function command($cmdline)
    {
        return $this->php_shell("composer.phar $cmdline");
    }
	
	
    
    /**
     * Constructor
     *
     * @param Config $cfg Config instance to use
     * @param string $libc Root folder of composer project
     */
    public function __construct(Config $cfg, $libc)
    {
        $this->_config = $cfg;
        $this->_rootConfig = Config::fromJSON(rtrim(__DIR__,'/') . '/RootConfig/rootcfg.json');
        $this->_libc = rtrim($libc, '/');
    }
}


?>