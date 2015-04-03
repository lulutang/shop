<?php
namespace Admin\Controller;
use Think\Controller;
use Admin\Model\ServiceModel;
use Admin\Model\GoodsModel;
use Admin\Model\PackageModel;
//use Page\Page;

class PriorityController extends Controller {
    
   public function _initialize() {
		header("content-type:text/html;charset=utf8");
		$uid = session("userid");
		if(empty($uid))
		{
                    echo '<script>top.location.href="/Admin/Login/index";</script>';
			//$this -> success('请登录',"/Admin/Login/index");die;
		}
    }
    
    //商品优先级
    public function goods(){
       // print_r($_POST);
       //获取一级业务
       $SS = new ServiceModel();
       
       $cat = $SS->getFirstServer();
       
       //取第一个作为首显示：
       if( !$cat ) exit('请确认是否有业务类型！ [ <A HREF="javascript:history.back()">返 回</A> ]');
       
       if( !empty($_POST['ywlx']) ){ //按查询条件搜索
            $tid = trim( $_POST['ywlx']);
            $NN = $SS->getName($tid);
            $tname = $NN['server_name'];
            $torderid = $NN['orderid'];
        }else{

            $tid = $cat[0]['id'];  
            $tname = $cat[0]['server_name'];
            $torderid = $cat[0]['orderid'];
        }
         
        //查找一级下的二级业务
        $Twocat = $SS->getTwotypes($tid);
        
        if( $_POST['keywords'] ) $keyword = $_POST['keywords'];
            
        $GG = new GoodsModel();
        //查找二级业务下的具体商品
        foreach ($Twocat as $key=>$val) {
            
            $Twocat[$key]['goods'] = $GG->getThreeGoods($val['id'],$keyword);
        }
        
        $this->assign('Twocat',$Twocat);
        $this->assign('keyword',$keyword);
        $this->assign('tid',$tid);
        $this->assign('torderid',$torderid);
        $this->assign('tname',$tname);
        $this->assign('cat',$cat);
        
        $this->display('goodspriority');
    }
    
    //接收ajax传递过来的数据 是否首页推荐
    public function is_index(){
        
        $goods_id = $_REQUEST['goods_id']; 
        if( $_REQUEST['field']=='is_index' ) $data['is_index'] = $_REQUEST['val']; 
        if( $_REQUEST['field']=='is_hot' ) $data['is_hot'] = $_REQUEST['val']; 
       
        if( $goods_id && $_REQUEST['type']==='is_indexorhot' ){
            //更新数据
            $GG = M("Goods");
            
            $result =  $GG->where('goods_id='.$goods_id )->save($data);
            
            if($result){
               echo '1';
            } 
        } 
    }
    
    //套餐服务优先级
    public function package(){
        
        //取套餐列表
        $PP = new PackageModel();
        $data = $PP->getPackageList();
        $time = time();
        
        foreach ($data as $key => $value) {
            if( $value['endtime']> $time ) $data[$key]['zstatus']='1'; 
            else $data[$key]['zstatus']='0'; 
        }
       
        $this->assign('data',$data);
        
        $this->display('packagepriority');
        
    }
    
    //接收ajax传递过来的数据 是否首页推荐
    public function package_Is_index(){
        
        $package_id = $_REQUEST['package_id']; 
        if( $_REQUEST['field']=='is_index' ) $data['is_index'] = $_REQUEST['val']; 
        if( $_REQUEST['field']=='is_hot' ) $data['is_hot'] = $_REQUEST['val']; 
       
        if( $package_id && $_REQUEST['type']==='is_indexorhot' ){
            //更新数据
            $PP = M("Package");
            
            $result =  $PP->where('package_id='.$package_id )->save($data);
            
            if($result){
               echo '1';
            } 
        } 
    }
    
    //套餐排序功能
    
    public function editorderid(){
        $id = $_REQUEST['id']; 
        $orderid = $_REQUEST['orderid']; 
        
        if( $id && $orderid ){
            
            $OO = M("Package"); // 实例化对象
            $data['package_id'] = $id;
            $data['orderid'] = $orderid;
          
            $result = $OO->save($data); // 根据条件保存修改的数据
            if( $result ){
                echo $orderid;
            }else{
                echo '0';
            }
        }
    }
    
   //商品排序功能
    
    public function editGoodorderid(){
        $id = $_REQUEST['id']; 
        $orderid = $_REQUEST['orderid']; 
        
        if( $id && $orderid ){
            
            $OO = M("Goods"); // 实例化对象
            $data['goods_id'] = $id;
            $data['index_order'] = $orderid;
          
            $result = $OO->save($data); // 根据条件保存修改的数据
            if( $result ){
                echo $orderid;
            }else{
                echo '0';
            }
        }
    }
    
    
    public function editServerid(){
        $id = $_REQUEST['id']; 
        $orderid = $_REQUEST['orderid']; 
        
        if( $id && $orderid ){
            
            $OO = M("Service"); // 实例化对象
            $data['id'] = $id;
            $data['orderid'] = $orderid;
          
            $result = $OO->save($data); // 根据条件保存修改的数据
           // echo $OO->getLastSql();
            if( $result ){
                echo $orderid;
            }else{
                echo '0';
            }
        }
    }
    
         
}
