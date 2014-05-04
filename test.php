<?php require __DIR__.DIRECTORY_SEPARATOR."titanbox/template.class.php" ?>
<!doctype html>
<html>
<head>
<meta charset="utf-8" />
<title>TEMPLATE</title>
</head>
<body>
<?php
// preparing everything for testing
$var = 4;
define("TEST_CONST","SIMPLE CONSTANT");
$array = array(
    "key" => 0,
    "a" => 1,
    "c" => 2,
    1 => 3
);
class Test{
    const TEST_CONST = "CLASS SIMPLE CONST";
    static $test = "static prop of class";
    static function md5($a){
        return md5($a);
    }
}
$obj = new Test;
// start testing template
$n = new titanbox_template(__DIR__.DIRECTORY_SEPARATOR."test".DIRECTORY_SEPARATOR."test.php.tpl",false,false);
include $n->render();
?>
</body>
</html>