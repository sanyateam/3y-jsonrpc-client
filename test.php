<?php
class A{
    public static function register(){
        spl_autoload_register([self::class,'_autoload']);
    }

    /**
     * @param $class
     * @throws Exception
     */
    protected static function _autoload($class){
        throw new Exception("{$class} not found",'-0');
    }
}
class B {
    public function bbb(){
        A::register();
        $a = new C();
    }

    public function c(){
        try {
            $this->bbb();
        }catch(Exception $exception){
            echo $exception->getFile().':'.$exception->getLine()."\n";
            echo $exception->getTraceAsString();
            exit;
        }
    }
}

$a = new B();
$a->c();
