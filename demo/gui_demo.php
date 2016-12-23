<?php

// define a const for the composer project folder name (folder will be created under the webroot folder)
const PROJECT = 'libc-composerinterface';
$root = $_SERVER['DOCUMENT_ROOT'];


// ------------------------------------------------------------------------
// CAUTION : this is a demo GUI, the $_REQUEST array is not sanitized 
//
// If you are going to use this GUI as a base for real work, you MUST
// implement here a way to check the content of $_REQUEST. 
//
// NEVER use user submitted data without proper checking !
// ------------------------------------------------------------------------


// include composer interface api
ini_set('display_errors', 'stdout');
include_once '../src/autoload.php';


use \Nettools\ComposerInterface\ComposerInterface;
use \Nettools\ComposerInterface\Config;



// process requests
try
{
    // create config object and set composer home (here, the parent folder of document root, so that other composer projects may benefit from global caching)
    $config = Config::fromJSON(__DIR__ . '/composer.config.json');
    $config->composer_home = dirname(rtrim($root, '/'));

    // create interface and set the composer project to be in folder PROJECT
    $composer = new ComposerInterface($config, rtrim($root, '/') . '/' . PROJECT);

    
    // global commands (not relative to a package or repository)
    if ( $_REQUEST['composer'] )
        $ret = $composer->{$_REQUEST['composer']}();


    // package commands
    else if ( $_REQUEST['package_cmd'] && $_REQUEST['package'] )
        $ret = $composer->{'package_' . $_REQUEST['package_cmd']}($_REQUEST['package']);

    
    // repositories commands
    else if ( $_REQUEST['repository_cmd'] && $_REQUEST['url'] )
        switch ( $_REQUEST['repository_cmd'] )
        {
            case 'add' : 
                if ( $_REQUEST['type'] )
                    $ret = $composer->repository_add($_REQUEST['type'], $_REQUEST['url']);
                break;
                
            case 'remove' : 
                $ret = $composer->repository_remove($_REQUEST['url']);
                break;
        }
    
    
    // user command (not supported by this library)
    else if ( $_REQUEST['cmd'] )
        $ret = $composer->command($_REQUEST['cmd']);
}
catch(Throwable $e)
{
    $ret = $e->getMessage() . "\n---\nTrace : " . $e->getTraceAsString();
}


?><html>
    <body>
        <?php
        if( $ret )
            echo "<pre style=\"background-color:black; color:white;\">$ret</pre>";
        ?>
        
        <p><strong>Composer.json file (from '<em>webroot</em>/<?php echo PROJECT; ?>/composer.json') : </strong>
        <pre style="background-color: antiquewhite;"><?php

// output composer.json content
if ( file_exists(rtrim($root, '/') . '/' . rtrim(PROJECT, '/') . '/composer.json') )
    echo file_get_contents(rtrim($root, '/') . '/' . rtrim(PROJECT, '/') . '/composer.json');
else 
    echo "<strong style=\"color:firebrick;\">No composer.json file detected ; you MUST install composer by hitting the SETUP link below.</strong>";

?></pre></p>
        
        <hr>
    
        <p><strong>Global commands :</strong>
            <a href="?composer=setup">Setup</a> 
            - <a href="?composer=show">Show</a>
            - <a href="?composer=clear_cache">Clear-cache</a>
            - <a href="?composer=install">Install</a>
            - <a href="?composer=update">Update</a>
            - <a href="?composer=validate">Validate</a>
            - <a href="?composer=diagnose">Diagnose</a>
            - <a href="?composer=archive">Archive</a>
            - <a href="?composer=outdated">Outdated</a>
            - <a href="?composer=self_update">Self-update</a>
        </p>
       
        <hr>
        
        <form method="get" action="gui_demo.php">
            <input type="hidden" value="" name="package_cmd">
            <p><strong>Package commands : </strong>
                <label>Package name: <input type="text" name="package"></label><br>
                    <input type="button" value="require" onclick="this.form.package_cmd.value=this.value; this.form.submit();">
                    <input type="button" value="remove" onclick="this.form.package_cmd.value=this.value; this.form.submit();">
                    <input type="button" value="update" onclick="this.form.package_cmd.value=this.value; this.form.submit();">
                    <input type="button" value="show" onclick="this.form.package_cmd.value=this.value; this.form.submit();">
                    <input type="button" value="archive" onclick="this.form.package_cmd.value=this.value; this.form.submit();">
                    <input type="button" value="prohibits" onclick="this.form.package_cmd.value=this.value; this.form.submit();">
                    <input type="button" value="depends" onclick="this.form.package_cmd.value=this.value; this.form.submit();">
                    <input type="button" value="show_dependencies" onclick="this.form.package_cmd.value=this.value; this.form.submit();">
            </p>
        </form>
        
        <hr>
        
        <form method="get" action="gui_demo.php">
            <input type="hidden" value="" name="repository_cmd">
            <p><strong>Repositories commands</strong> :  
                <label>Repository type : <select name="type"><option></option><option>path</option><option>vcs</option><option>pear</option><option>git</option></select></label> ; 
                <label>Repository url : <input type="text" name="url"></label><br>
                    <input type="button" value="add" onclick="this.form.repository_cmd.value=this.value; this.form.submit();">
                    <input type="button" value="remove" onclick="this.form.repository_cmd.value=this.value; this.form.submit();">
            </p>
        </form>

        <hr>
        
        <form method="get" action="gui_demo.php">
            <p><strong>Other command</strong> :  
                <label>Command line : <input type="text" name="cmd" style="width:50%;"></label><br>
                    <input type="submit" value="send command">
            </p>
        </form>
    </body>
</html>