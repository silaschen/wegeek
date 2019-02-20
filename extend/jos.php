<?php
/**
* 约瑟夫环问题
*/
ini_set("display_errors", "On");
error_reporting(E_ALL);    //报告所有的错误
$monk = new Arraystack(41);//生产一个约瑟夫环，4个元素
$hand = new Jose($monk);
var_dump($hand->handler(3,2));//每次
class Arraystack
{
  public $stack=array();
  function __construct($num)
  {
    $this->stack = range(1,$num);
  }

  //出队
  public function pop(){
 
   return  array_shift($this->stack);
  }

  //入队
  public function push($key){
    return array_push($this->stack,$key);
  }

  public function now(){

    return $this->stack;
  }
}

/**
* 约瑟夫处理,
*假设有n只猴子做成一圈儿，编号1,2,3,4,5,6,7...n
*从1开始报数，数到m的时候，这只猴子出队。下一只猴子继续从1开始报数，数到m，出队...
*直到剩下一只猴子，请输出最后一只猴子编号
*并输出猴子的出队顺序
*/
class Jose
{
  private $handler;
  public $outlist=array();//出队结构
  function __construct($object)
  { 
    $this->handler = $object;
  }

  public function handler($m,$left=1){
    $i=1;
    while (count($this->handler->now()) > $left) {
  
        $out = $this->handler->pop();//出队
     
        if($i%$m != 0){//不是m的倍数
            //猴子入队尾，构成新队列
          $this->handler->push($out);
                $i+=1;//继续报数
        
        }else{
          //出队的猴子就是数到m的
          array_push($this->outlist,$out);
          $i=1;//重新从1报数

        }
    }

    return ['res'=>$this->handler->now(),'outlist'=>$this->outlist]; 
  }
}

