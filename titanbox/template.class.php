<?php
/** 
 * Class to parse a template file and render
 * 
 * @package	: TitanBox Core
 * @author	: HR
 * @version	: 0.5.5.14 beta
 * @license : Apache license 2.0
 * 
 * Copyright 2014 TitanBox Project
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 * http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
class titanbox_template{
    
    /**
     * FLAG_NEWLINES_REMOVAL
     * 
     * this flag enable newline and whitespace characters removal */
    const FLAG_NEWLINES_REMOVAL = 0x2;
    
    /**
     * FLAG_NEWLINES_REMOVAL
     * 
     * this flag enable errored template blocks removal */
    const FLAG_ERRORS_REMOVAL  = 0x4;
    
    /**
     * Store information of current template file
     * 
     * - cachedir : path to directory of caches
     * - filepath : path to current file
     * - cacheprefix : a md5 encoded string of filepath , just to indentify caches
     * - extension : template file real extension ( ex : php , html ...)
     * - maxtime : accept cached time of most recent cache version
     * - flags
     * 
     * @var array */
    protected $_information = array();
    
    /**
     * Create a template object for template file
     * 
     * @param string $filepath full path to template file
     * @param optional string $cachedir full path to cache directory
     * @param optional int $maxtime max cache time in seconds , default is 7 days ( 604800 seconds )
     * @param optional int $flags flags
     */
    public function __construct( $filepath , $cachedir = false , $maxtime = 604800 , $flags = 6 ){
        if( pathinfo( $filepath , PATHINFO_EXTENSION) !=="tpl" ){
            throw new InvalidArgumentException(__CLASS__." accepts only tpl file");
        }
        if( $cachedir === false ){
            $cachedir = __DIR__.DIRECTORY_SEPARATOR."template_caches".DIRECTORY_SEPARATOR;
        }
        if( $maxtime === false ){
            $maxtime = 604800;
        }        
        if( $flags === false ){
            $flags = 6;
        }
        if( is_dir($cachedir) && is_file($filepath) ){
            // Store information
            $this->_information["cachedir"] = $cachedir;
            $this->_information["filepath"] = $filepath;
            $this->_information["cacheprefix"] = md5( $filepath );
            $this->_information["extension"]= pathinfo( strtr( $filepath , array(".tpl"=>"") ) , PATHINFO_EXTENSION );
            $this->_information["maxtime"]  = $maxtime;
            $this->_information["flags"] = $flags;
        }
        else throw new InvalidArgumentException(__CLASS__." can't find cachedir or filepath");
    }
    
    /** Get cache directory full path */
    public function getCacheDir(){
        return $this->_information["cachedir"];
    }
    
    /** Get cache prefix , a md5 string to indentify current template file's caches from cache directory */
    public function getCachePrefix(){
        return $this->_information["cacheprefix"];
    }
    
    /** Get template file's real extension , simply skip .tpl */
    public function getRealExtension(){
        return $this->_information["extension"];
    }
    
    /** Get new generated template file's cache name */
    public function getCacheName(){
        return $this->getCachePrefix()."_".time().".".$this->getRealExtension();
    }
    
    /** Get new generated template file's cache full path */
    public function getCachePath(){
        return $this->getCacheDir().$this->getCacheName();
    }
    
    /**
     * Check if current template file is cached or not
     * 
     * @return boolean */
    public function isCached(){
        return count( $this->listCaches() ) !== 0 ;
    }
    
    /**
     * List all caches of current template file
     * 
     * @return array */
    public function listCaches(){
        return glob( $this->getCacheDir().$this->getCachePrefix()."_*".$this->getRealExtension() );
    }
    
    /**
     * Remove all the caches of current template file */
    public function clearCaches(){
        $caches = $this->listCaches();
        foreach( $caches as $cache ){
            unlink( $cache );
        }
    }
    
    /**
     * Render template and return path to the most recent cache version
     * 
     * You must include or require the returned path
     * 
     * @uses : If your want to use all current variables ( global or current function ..etc.)
     * @param boolean optional $fromCache default is true , if false then force to use a new cache
     * 
     * @return string */
    public function render( $fromCache = true ){
        // retrieve paths of caches
        $caches = $this->listCaches();
        // current time
        $time   = time();
        if( $fromCache === true && count( $caches ) > 0 ){
            // get the most recent cache path
            $recentCache = $caches[ count($caches)-1 ];
            // get timestamp of the most recent cache
            $recentTime = substr( pathinfo( $recentCache , PATHINFO_BASENAME ) , strlen( $this->getCachePrefix() )+1 );
            $recentTime = strpos($recentTime,".") ? (int)strstr( $recentTime , "." , true ) : (int)$recentTime;
            if( time() - $recentTime <= $this->_information["maxtime"] ){
                $new_cache = false;
            }
            else $new_cache = true;
        }
        else $new_cache = true;
        
        // force to generate new cache
        if( $new_cache === true ){
            file_put_contents( $this->getCachePath() , $this->parse( file_get_contents( $this->_information["filepath"] ) ) );
            return $this->getCachePath();
        }
        else return $recentCache;
    }
    
    /**
     * Render template and return path to the most recent cache version with custom variables
     * 
     * This method automatically include the returned path
     * 
     * @uses : If your want to control variables and decide which can be accessed or viewed
     * @param boolean optional $fromCache default is true , if false then force to use a new cache */
    public function renderWithVariables( $arrayOfVariables , $fromCache = true ){
        // retrieve paths of caches
        $caches = $this->listCaches();
        // rencer time
        $time   = time();
        if( $fromCache === true && count( $caches ) > 0 ){
            // get most recent cache path
            $recentCache = $caches[ count($caches)-1 ];
            // get timestamp of most recent cache
            $recentTime = substr( pathinfo( $recentCache , PATHINFO_BASENAME ) , strlen( $this->getCachePrefix() )+1 );
            $recentTime = strpos($recentTime,".") ? (int)strstr( $recentTime , "." , true ) : (int)$recentTime;
            if( time() - $recentTime <= $this->_information["maxtime"] ){
                $new_cache = false;
            }
            else $new_cache = true;
        }
        else $new_cache = true;
        
        extract( $arrayOfVariables );
        // force to generate new cache
        if( $new_cache === true ){
            file_put_contents( $this->getCachePath() , $this->parse( file_get_contents( $this->_information["filepath"] ) ) );
            include $this->getCachePath();return;
        }
        else include $recentCache;return;
    }
    
    /**
     * Protected class for private uses and re-declaration of child class
     * 
     * Only used for parsing all template file contents
     * 
     * @param string $contents */
    protected function parse( $contents ){
        ###################
        # OUTPUT STYLE
        ###################
        // [[ %s ]]
        $contents = preg_replace_callback("/\[\[ ([^\[\]]+) \]\]/",function($m){
                return "<?php echo ".$this->parse_a($m[1])." ?>";
            },$contents
        );
        ###################
        # SYNTAX
        ###################
        // raw php
        $contents = preg_replace("/\[\@raw\ ([^\[\@\]]+)\ \@\]/","<?php $1 ?>",$contents);
        // each %s as %v to %v
        $contents = preg_replace_callback("/\[\@\ each\ ([^\[\@\]]+)\ as\ ([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\ to\ ([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\ \@\]/",function($m){
                return "<?php foreach(".$this->parse_s($m[1])." as $".$m[2]." => $".$m[3]."){ ?>";
            },$contents
        );
        // each %s as %v
        $contents = preg_replace_callback("/\[\@\ each\ ([^\[\@\]]+)\ as\ ([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\ \@\]/",function($m){
                return "<?php foreach(".$this->parse_s($m[1])." as $".$m[2]."){ ?>";
            },$contents
        );
        // in %v
        // @param $ttsv titanbox_template_static_var - a static variable used for template rendering
        $contents = preg_replace_callback("/\[\@\ in\ ([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\ \@\]/",function($m){
                return "<?php for(\$ttsv = 0 ; \$ttsv < $".$m[1]." ; \$ttsv++){ ?>";
            },$contents
        );
        // in %n
        $contents = preg_replace_callback("/\[\@\ in\ ([\d]+)\ \@\]/",function($m){
                return "<?php for(\$ttsv = 0 ; \$ttsv < ".$m[1]." ; \$ttsv++){ ?>";
            },$contents
        );
        // if %a
        $contents = preg_replace_callback("/\[\@\ if\ ([^\[\@\]]+)\ \@\]/",function($m){
                return "<?php if(".$this->parse_a($m[1])."){ ?>";
            },$contents
        );
        // else %a
        $contents = preg_replace_callback("/\[\@\ else\ ([^\[\@\]]+)\ \@\]/",function($m){
                return "<?php }elseif(".$this->parse_a($m[1])."){ ?>";
            },$contents
        );
        // else
        $contents = preg_replace_callback("/\[\@\ else\ \@\]/",function($m){
                return "<?php }else{ ?>";
            },$contents
        );
        // value %s ? %a
        $contents = preg_replace_callback("/\[\@\ value\ ([^\[\@\]]+)\ \?\ ([^\[\@\]]+)\ \@\]/",function($m){
                return "<?php switch(".$this->parse_s($m[1])."){ case ".$this->parse_a($m[2])." : ?>";
            },$contents
        );
        // type %s ? %a
        $contents = preg_replace_callback("/\[\@\ type\ ([^\[\@\]]+)\ \?\ ([^\[\@\]]+)\ \@\]/",function($m){
                return "<?php switch(gettype(".$this->parse_s($m[1]).")){ case ".$this->parse_a($m[2])." : ?>";
            },$contents
        );
        // ? %a
        $contents = preg_replace_callback("/\[\@\ \?\ ([^\[\@\]]+)\ \@\]/",function($m){
                return "<?php break; case ".$this->parse_a($m[2])." : ?>";
            },$contents
        );
        // default
        $contents = preg_replace_callback("/\[\@\ default\ \@\]/",function($m){
                return "<?php break; default : ?>";
            },$contents
        );
        // end
        $contents = preg_replace_callback("/\[\@\ end\ \@\]/",function($m){
                return "<?php } ?>";
            },$contents
        );
        // extends
        // render a template file in same folder of current template with the same configurations such as
        // cache dir , max cache time ...
        $contents = preg_replace_callback("/\[\@\ extends\ ([^\[\@\]]+)\ \@\]/",function($m){
                $file_name = $m[1];
                $file_path = pathinfo( $this->_information["filepath"] , PATHINFO_DIRNAME ).DIRECTORY_SEPARATOR.$file_name;
                return "<?php \$ttsv=new titanbox_template(\"".addslashes((string)$file_path)."\",\"".addslashes((string)$this->_information["cachedir"])."\",".$this->_information["maxtime"].",".(string)$this->_information["flags"].");include \$ttsv->render() ?>";
            },$contents
        );
        #################
        #
        #################
        // [@ %l @]
        $contents = preg_replace_callback("/\[\@\ ([^\[\@\]]+)\ \@\]/",function($m){
                return "<?php ".$this->parse_l($m[1])." ?>";
            },$contents
        );
        #################
        # FOR FLAGS
        #################
        // remove newline and whitespace between html tags
        if( $this->_information["flags"] & self::FLAG_NEWLINES_REMOVAL ){
            $contents = preg_replace(
                array(
                    "/([\r\n]+)\<\?/",
                    "/\?\>([\r\n]+)/",
                    "/\/\>([\r\n]+)/",
                    "/([\r\n]+)\</",
                ),
                array(
                    "<?",
                    "?>",
                    "/>",
                    "<",
                ),
                $contents
            );
        }
        // remove error template blocks
        if( $this->_information["flags"] & self::FLAG_ERRORS_REMOVAL ){
            $contents = preg_replace(
                array(
                    "/\[\[([^\[\]]+)\]\]/",
                    "/\[\@([^\[\]\@]+)\@\]/"
                ),
                array(
                    "",
                    ""
                ),
                $contents
            );
        }
        return $contents;
    }
    
    /**
     * Protected class for private uses and re-declaration of child class
     * 
     * Only used for parsing all %v ( variable ) slot from patterns
     * %v slot contains : pure variables , array elements , object properties , class static properties
     * 
     * @param string $voa */
    protected function parse_v( $contents ){
        return preg_replace(
            array(
                // parse variable name ( %v )
                // @referrence : http://php.net/language.variables.basics
                "/^([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$/",
                // parse array element with integer key
                "/^([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\.([\d]+)$/",
                // parse array element with string key
                "/^([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\.([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$/",
                // parse object property
                "/^([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\:([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$/",
                // parse class static property
                "/^([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\:\:([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$/",
                // parse class constant
                "/^([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\~([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$/"
            ),
            array(
                "$$1", // variable name
                "$$1[$2]",
                "$$1[\"$2\"]",
                "$$1->$2",
                "$1::$$2",
                "$1::$2"
            ),
            $contents
        );
    }
    
    /**
     * Protected class for private uses and re-declaration of child class
     * 
     * Only used for parsing all %s ( single ) slot from patterns
     * %s slot contains : variable name , string , constants , iterator element , class properties ,
     *                    class constants , functions , filters , methods with variables and true/false
     * 
     * @param string $contents */
    protected function parse_s( $contents ){
        // filters - cai nay de lam sau
        // -----------
        // function with variables
        if( preg_match("/^function\:([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\((.+)\)$/",$contents,$matches) ){
            $listvar = explode(",",$matches[2]);
            foreach( $listvar as &$var ){
                // parse each variable
                $var = $this->parse_a( $var );
            }
            $matches[2] = implode(",",$listvar);
            return $matches[1]."(".$matches[2].")";
        }
        // object method with variables
        else if( preg_match("/^([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\:([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\((.+)\)$/",$contents,$matches) ){
            $listvar = explode(",",$matches[3]);
            foreach( $listvar as &$var ){
                // parse each variable
                $var = $this->parse_a( $var );
            }
            $matches[3] = implode(",",$listvar);
            return "$".$matches[1]."->".$matches[2]."(".$matches[3].")";
        }        
        // class static method with variables
        else if( preg_match("/^([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\:\:([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\((.+)\)$/",$contents,$matches) ){
            $listvar = explode(",",$matches[3]);
            foreach( $listvar as &$var ){
                // parse each variable
                $var = $this->parse_a( $var );
            }
            $matches[3] = implode(",",$listvar);
            return $matches[1]."::".$matches[2]."(".$matches[3].")";
        }        
        else return preg_replace( 
            array(
                // parse numbers
                "/^([\d\.]+)$/",
                // parse variable name ( %v )
                // @referrence : http://php.net/language.variables.basics
                "/^([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$/",
                // parse constants
                "/^~([\S]+)$/",
                // parse array element with integer key
                "/^([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\.([\d]+)$/",
                // parse array element with string key
                "/^([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\.([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$/",
                // parse object property
                "/^([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\:([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$/",
                // parse class static property
                "/^([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\:\:([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$/",
                // parse class constant
                "/^([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\~([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$/"
            ),
            array(
                "$1",
                "$$1", // variable name
                "constant(\"$1\")", // constant
                "$$1[$2]",
                "$$1[\"$2\"]",
                "$$1->$2",
                "$1::$$2",
                "$1::$2"
                
            ), $contents
        );
    }
    
    /**
     * Protected class for private uses and re-declaration of child class
     * 
     * Only used for parsing all %l ( statement ) slot from patterns
     * %l slot only have one format : %v %o %a with %v : variable name , %o : operators
     * 
     * @param string $voa */
    protected function parse_l( $voa ){
        $voa = explode(";",$voa);
        $return = array();
        foreach( $voa as $each ){
            // %v ? %a
            if( preg_match("/^([a-zA-Z\.\:]+)\ \?\ (.+)$/",$each,$m) ){
                $m[1] = $this->parse_v($m[1]);
                $return[]= "isset(".$m[1].") && ".$m[1]." ? ".$m[1]." : ".$this->parse_a($m[2]);
            }
            // %v %o %a
            else $return[]= preg_replace_callback("/^([a-zA-Z\.\:]+)\ ([\S]+)\ (.+)$/",
                function($m){
                    $m[1] = $this->parse_v($m[1]);
                    return $m[1].$this->parse_o($m[2]).$this->parse_a($m[3]);
                },
                $each
            );
        }
        
        return implode(";",$return);
    }
    
    /**
     * Protected class for private uses and re-declaration of child class
     * 
     * Only used for parsing all %a ( all types ) slot from patterns
     * %a slot contains : %s , %l , true/false
     * 
     * @param string $contents */
    protected function parse_a( $contents ){
        return $this->parse_s( $this->parse_l( $contents ) );
    }
    
    /**
     * Protected class for private uses and re-declaration of child class
     * 
     * Only used for parsing special operators
     * designed for this template class
     * 
     * @param string $contents */
    protected function parse_o( $contents ){
        return strtr( $contents , array(
            "to" => "=",
            "as" => "==",
            "is" => "===",
            "lt" => "<=",
            "gt" => ">=",
            "slt"=> "<=",
            "sgt"=> ">=",
            "not"=> "!==",
            "||" => "or",
            "&&" => "and",
            "ab" => "+=",
            "sb" => "-=",
            "db" => "%=",
            "mb" => "*="
        ));
    }
}
?>