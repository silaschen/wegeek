<?php

namespace helper;

include_once realpath(__DIR__ . "/../../autoload.php");

function executeRequest() {
    $q = new HelpHandler();
    $q->run();
}

class HelpHandler {

    private $userinfo;

    public function __construct() {
        $this->userinfo = Auth::getCurrentUser(FALSE);
    }

    public function run() {
        $action = filter_input(INPUT_GET, "action");
        switch ($action) {
            case "files":
            default:
                $this->helpfiles();
        }
    }

    private function helpfiles() {
        $key = "CODATA_HELP_FILES";
        $redis = redisutil::getRedis();
        $v = $redis->get($key);
       if (!$v) {
            $data = $this->loadFileListFromDir(CODATA_HELP_FILELOCATION);
           $redis->put($key, $v, 1800);
       } else {
           $data = json_decode($v, TRUE);
       }
       krsort($data);
       outputjson($data);
    }


    public function loadFileListFromDir($dir) {
        $ret = ["categories" => [], "files" => []];
        $h = opendir($dir);
        while (($file = readdir($h)) !== FALSE) {
            if (($file == ".") || ($file == "..")) {
                continue;
            }

            if (filetype(Utils::joinPath([$dir, $file])) == "dir") {
                // $name = explode("_", $file)[1];
                
                $ret["categories"][$file] = $this->loadFileListFromDir(Utils::joinPath([$dir, $file]));
            } else {
                $pathparts = pathinfo($file);
                $ret["files"][$pathparts["filename"]] = Utils::joinUrl(["/static/help", str_replace(CODATA_HELP_FILELOCATION, "", $dir), $pathparts["basename"]], TRUE);

            }
        }
        closedir($h);
        return $ret;
    }

}
