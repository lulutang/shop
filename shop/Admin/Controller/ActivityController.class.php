<?php
/**
 * 后台促销活动管理
 * author  cjl
 * @var act 活动实例对象
 */
namespace Admin\Controller;
use Think\Controller;
use Admin\Model\ActivityModel;
use Page\Page;
include(COMMON_PATH."Class/Page.class.php");

class ActivityController extends Controller{
    private $act = null;
    public function _initialize() {
        header("content-type:text/html;charset=utf8");
        $uid = session("userid");
        if(empty($uid))
        {
            echo '<script>top.location.href="/Admin/Login/index";</script>';
        }
    }
    /**
     * 促销活动列表
     */
    public function activitylist(){
        
        $where =' act_status = 0  ';

        $ACT = new ActivityModel();
        $Count = $ACT -> getCount($where);
        
        $page_count = 10; //每页显示条数
        $Page = new Page($Count, $page_count);// 实例化分页类 传入总记录数

        $Pagesize =$Page -> show(); //得到分页模板
        $info = $ACT -> getActInfo($Page->firstRow , $Page->listRows , $where);
     
        $time = time();
        
        $this -> assign('nowtime',$time);
        $this -> assign('map',$map);
        $this -> assign('p',trim(I("p")));
        $this -> assign('page' , $Pagesize);
        $this -> assign('data',$info);
        $this -> display('activitylist');
    }
    /**
     * 促销活动添加
     * @var array goodsinfo 得到所有的商品信息
     * @var array giftinfo 得到所有的赠品信息
     */
    public function activitysave(){
        $act = new ActivityModel();
        $goodsinfo = $act -> getGoodsInfo();
        $giftinfo = $act -> getGiftInfo();
        $this -> assign('giftinfo',$giftinfo);
        $this -> assign('goodsinfo',$goodsinfo);
        $this -> display();
    }
    /**
     * 促销活动
     */

    public function activitysavedata(){
        $acts = new ActivityModel();
        $data['act_name'] = I('dan_name');
        $data['act_starttime'] = strtotime(I('dan_starttime'));
        $data['act_endtime'] = strtotime(I('dan_endtime'));
        $data['act_goodsid'] = I('dan_goods');
        $data['act_goodsprice'] = I('dan_price');
        $data['act_quoto'] = I('dan_buy');
        $data['act_number'] = I('dan_number');
        $str = $this -> uploadpic('dan_file');
        $data['act_photo'] = $str;
        $data['act_content'] = $_POST['dan_content'];
        $data['act_type'] = 2;
        $data['act_createuser'] = session('truename');
        $data['act_addtime'] = time();
        $re = $acts -> addActivityInfo($data);
        if($re != FALSE){
            $this -> redirect("/Admin/Activity/activitylist"); 
        }else{
            $this->error('创建活动失败','/Admin/Activity/activitylist');
        }
    }
   
    /**
     * 删除促销活动
     */
    public function activitydel() {
        
        $ACT = new ActivityModel();
        
        $id = $_REQUEST['id'];
        
        if( $id ){
            
            $data['act_id'] = $id;
            $data['act_status'] = 1;
            
            $res = $ACT -> activitydel( $id,$data );
            
            if( $res ) $this -> redirect("/Admin/Activity/activitylist"); 
        }  

    }

    /**
     * 促销活动数据保存-买赠
     * @var array gift 赠品id
     * @var int act 活动id
     * @var int goodsid 商品ID
     * @var array giftnum 赠品数量
     */
    public function activitysavedatas(){
        $acts = new ActivityModel();
        $data['act_name'] = I('mai_name');
        $data['act_starttime'] = strtotime(I('mai_starttime'));
        $data['act_endtime'] = strtotime(I('mai_endtime'));
        $data['act_goodsid'] = I('mai_goods');
        $str = $this -> uploadpic('mai_file');
        $data['act_photo'] = $str;
        $data['act_content'] = $_POST['mai_content'];
        $data['act_type'] = 1;
        $data['act_createuser'] = session('truename');
        $data['act_addtime'] = time();
        $re = $acts -> addActivityInfo($data);
        if($re != FALSE){
            $gift = $_POST['zeng'];
            $act = $re;
            $goodsid = I('mai_goods');
            $giftnum = $_POST['zeng_number'];
            $red = $acts -> addActGiftInfo($gift, $act, $goodsid, $giftnum);
            if($red == TRUE){
                $this -> redirect("/Admin/Activity/activitylist");
            }else{
                $this->error('创建活动失败','/Admin/Activity/activitylist');
            }
        }else{
            $this->error('创建活动失败','/Admin/Activity/activitylist');
        }   
    }

    /**
    * ajax无刷新上传头�
    */
    private function uploadpic($files){
        $extend = explode(".",$_FILES[$files]["name"]);
        $key = count($extend)-1;
        $ext = ".".$extend[$key];
        $newfile = time().$ext;
        $savePath = "./uploads/avatar/".date('Ymd',time())."/"; 
        if(is_dir($savePath)==FALSE){
              mkdir($savePath,0777);  
        } 
        $thumb_path=$savePath.$newfile;
        move_uploaded_file($_FILES[$files]['tmp_name'] , $thumb_path);
        return substr($thumb_path, 1);

    }

    /**
     * 编辑活动
     */
    public function activityedit() {
        $ACT = new ActivityModel();
        
        $id = $_REQUEST['id'];
        if( $id ){
            
            $goodsinfo = $ACT -> getGoodsInfo();
            $giftinfo = $ACT -> getGiftInfo();
            
            $info = $ACT -> activityEdit( $id );  //获取活动内容
            $giftid = $ACT -> activityGift( $id );  //获取赠品id
            
            $this -> assign('id',$id);
            $this -> assign('giftinfo',$giftinfo);
            $this -> assign('goodsinfo',$goodsinfo);
            $this -> assign('info',$info);
            $this -> assign('giftid',$giftid);
            
            $this -> display();
        }   
    }

    /**
     * 编辑促销活动数据
     */
    public function activityupdatedata(){

        $ACT = new ActivityModel();
        $data['act_id'] = I('hid');
        $data['act_name'] = I('dan_name');
        $data['act_starttime'] = strtotime(I('dan_starttime'));
        $data['act_endtime'] = strtotime(I('dan_endtime'));
        $data['act_goodsid'] = I('dan_goods');
        $data['act_goodsprice'] = I('dan_price');
        $data['act_quoto'] = I('dan_buy');
        $data['act_number'] = I('dan_number'); 
        $data['act_content'] = $_POST['dan_content'];;
        $data['act_type'] = 2;
        $data['act_createuser'] = session('truename');
        $data['act_addtime'] = time();
        
        if( $_FILES['dan_file']['name']) {
            $str = $this -> uploadpic('dan_file');
            $data['act_photo'] = $str;
        }
  
       
        $re = $ACT -> updateActivityInfo($data);

        $this -> redirect("/Admin/Activity/activitylist"); 
        
    }
    /**
     * 编辑买赠商品
     */
    public function activityupdate() {
      
        $ACT = new ActivityModel();
        
        $data['act_id'] = I('hid');
        $data['act_name'] = I('mai_name');
        $data['act_starttime'] = strtotime(I('mai_starttime'));
        $data['act_endtime'] = strtotime(I('mai_endtime'));
        $data['act_goodsid'] = I('mai_goods');
        $data['act_content'] = $_POST['mai_content'];
        $data['act_type'] = 1;
        $data['act_createuser'] = session('truename');
        $data['act_addtime'] = time(); 
        
        if( $_FILES['mai_file']['name']) {
            $str = $this -> uploadpic('mai_file');
            $data['act_photo'] = $str;
        }
        
        $re = $ACT -> updateActivityInfo($data);
        
        if($re != FALSE){
            $gift = $_POST['zeng'];
           
            $goodsid = I('mai_goods');
            $giftnum = $_POST['zeng_number'];
            
            $red = $ACT -> updateActAndGift($gift,$data['act_id'], $goodsid, $giftnum);
            
            if($red == TRUE){
                $this -> redirect("/Admin/Activity/activitylist");
            }else{
                $this->error('编辑活动失败','/Admin/Activity/activitylist');
            }
        }else{
            $this->error('编辑活动失败','/Admin/Activity/activitylist');
        } 
    }
   
}

