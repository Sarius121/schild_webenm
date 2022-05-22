<?php

/* current version - need exactly 6 digits */
define("VERSION", "010000");

/**
 * code from https://stackoverflow.com/questions/118884/how-to-force-the-browser-to-reload-cached-css-and-javascript-files#answer-118886
 * not written by myself but modified
 * 
 *  Given a file, i.e. /css/base.css, replaces it with a string containing the
 *  file's mtime, i.e. /css/base.1221534296.css.
 *
 *  @param $file  The file to be loaded.  Must be an absolute path (i.e.
 *                starting with slash).
 */
function auto_version($file)
{
    if(strpos($file, '/') !== 0)
        return $file;

    $version = VERSION;

    return substr(preg_replace('{\\.([^./]+)$}', ".$version.\$1", $file), 1);
}

function getConstant(string $name, $defaultValue){
    if(defined($name)){
        return constant($name);
    } else {
        return $defaultValue;
    }
}

?>