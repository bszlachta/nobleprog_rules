<?php //namespace
namespace GenerateRules;

class Rule {
    var $name;
    var $config;
    var $context;
    var $condition;
    var $action;
    

   //not efficent
    function check($objects) {
        static $cache  = array();

        $result = null;

        $filled = $this->condition;
        $cache_key = $this->name ;
        if (isset($cache[$cache_key])) {

            $filled = $cache[$cache_key];
        } else {
            foreach (array_keys($this->context) as $key) {

                $filled = preg_replace("/\\" . $key . "/", '\$objects[\'' . $key . '\']', $filled);
            }
            $cache[$cache_key] = $filled;
        }
           // echo $filled ."\n";
        eval('$result = ' . $filled . ';');

        return $result;
    }

    function checkAll($invocations) {
        $checked = array();
        foreach ($invocations as $invocation){
            if ($this->check($invocation)){
                array_push($checked, $invocation);
            }
        }

        return $checked;
    }
}


