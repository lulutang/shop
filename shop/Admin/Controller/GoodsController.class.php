<?php
/**
 * 商品管理Controller
 * @author 李建栋
 *
 */
namespace Admin\Controller;

use Think\Controller;
use Admin\Model\ServiceModel;
use Admin\Model\Attr_valueModel;
use Admin\Model\Server_attrModel;
use Admin\Model\GoodsModel;
use Admin\Model\Goods_attrModel;
use Admin\Model\PackageModel;
use Page\Page;

include(COMMON_PATH."Class/Page.class.php");
class GoodsController extends Controller {
    public function _initialize() {
        header ( "content-type:text/html;charset=utf8" );
        $uid = session ( "userid" );
        if(empty( $uid )) {
            echo '<script>top.location.href="/Admin/Login/index";</script>';
        }
    }
    
    /**
     * 添加商品
     */
    public function Goods_add(){
        $Server = new ServiceModel();
        $this -> server_arr=$Server -> Server_arr(" 0 ");
        $this -> assign("pid" , 0);
        $this -> display();
    }
    
    /**
     * 获取子级业务信息
     * @param int id 二级业务Id
     */
    public function Next_server()
    {
        $server_pid=trim(I("id"));
        $Server = new ServiceModel();
        $server_arr=$Server -> Server_arr($server_pid);
        if( empty($server_arr) ) {
            $res = 1;
        }
        if($res != 1) {
            $this->assign("server_arr",$server_arr);
            $this->assign("pid",$guo);
            $this->display();
        } else{
            echo $server_pid."@"."#";
        }
    }
    
    /**
     * 根据业务类型ID获取属性
     * *@param int service_id 二级业务id
     */
    public function Ajaxattr_val()
    {
        $server_id = trim( I("service_id") );
        $Server = new Server_attrModel();
        $server_attr = $Server -> Get_attr($server_id);
        $server_attr_val = $Server -> Get_attr_val($server_id);
        $this ->attr_count = count($server_attr);
        if(!empty($server_attr))
        {
            $this ->server_id = $server_id;
            $this ->server_attr = $server_attr;
            $this -> server_attr_val = $server_attr_val;
            $this -> display();
        }	
    }
    
    /**
     * 根据属性值的Id获取商品
     * @param int attrID_val 属性值Id
     * @param int attr_count 属性值数量
     */
    public function Ajaxattr_goods()
    {
        $attrID_val = trim(trim(I("attrID_val"),','));
        $Attr_count = trim(intval(I("attr_count")));
        $Attr_val = new Attr_valueModel();
        $Goods = new GoodsModel();
        $flag = 0;
        $Goods_attr = $Attr_val -> Getattr_goods($attrID_val , $Attr_count);
        if(count($Goods_attr) == 1)
        {
            $Goods_desting = $Goods -> GetgoodsId($Goods_attr);
            if(!empty($Goods_desting))
            {
                $arr = array("code"=>3,"data"=>array("attrID_val" => $attrID_val,"Goods_desting" => $Goods_desting,"Attr_count" =>$Attr_count));
                echo json_encode($arr);
            }
        }else {
            $as = array("code"=>1,"data"=>array("Attr_count" => $Attr_count));
            echo json_encode($as);
        }
    }
    
    /**
     * 判断是添加还是修改
     * @param int goods_id 商品Id
     */
    public function Addorup()
    {
        $Goods = new GoodsModel(); 
        if (!$Goods->create())
        {
            //exit($Goods->getError());
        }else{
            $goods_id = trim(I("goods_id"));
            if(empty($goods_id) )
            {
                    $this -> Goods_addtwo($_POST , $_FILES);
            }else{
                    $this -> Goods_up($_POST , $_FILES , $goods_id);
            }
        }
    }
    
    /**
     * 修改商品数据
     * @param array $post 提交表单数据
     * @param array $files 文件数据
     * @param int $goods_id 商品Id
     */
    public function Goods_up($post , $files , $goods_id)
    {
        $Goods = new GoodsModel(); 
        $Goods_attr = new Goods_attrModel(); 
        $savePath = "./uploads/thumb/".date('Ymd',time())."/"; 
        if(is_dir($savePath)==FALSE){
            mkdir($savePath , 0777);  
        } 
        $thumb_path=$savePath.$files['thumb']['name'];
        if(!empty($files['thumb']['name']))
        {
            $str_thumb = $this -> upload($files['thumb']['name']);
            if(empty($str_thumb))
            {
                echo "<script>alert('缩略图片格式上传错误');location.href='/Admin/Goods/Goods_listUp/goods_id/".$goods_id."'</script>";die;
            }
        }
        $picPath = "./uploads/goods_pic/".date('Ymd',time())."/"; 
        if(is_dir($picPath)==FALSE){
            mkdir($picPath , 0777);  
        } 
        $pic_path=$picPath.$files['goods_pic']['name'];
        if(!empty($files['goods_pic']['name']))
        {
            $str_pic = $this -> upload($files['goods_pic']['name']);
            if(empty($str_pic))
            {
                echo "<script>alert('原图片格式上传错误');location.href='/Admin/Goods/Goods_listUp/goods_id/".$goods_id."'</script>";die;
            }
        }
        move_uploaded_file($files['thumb']['tmp_name'] , $thumb_path);
        move_uploaded_file($files['goods_pic']['tmp_name'] , $pic_path);
        $UP_goods = $this -> pub_parament($post);
        $server_pid = trim($post["serveris_1"]);
        $now_serverid = trim($post["server_id"]);
        $Server = new ServiceModel(); 
        $Goods_attr = new Goods_attrModel(); 
        $server_pname = $Server -> Getserverpname($server_pid); 
        $now_servername = $Server ->Getserverpname($now_serverid); 
        if(!empty($files['thumb']['name']))
        {
            $UP_goods["thumb"] = trim($thumb_path , '.');
        }
        if(!empty($files['goods_pic']['name'])){
            $UP_goods["goods_pic"] = trim($pic_path , '.');
        }
        $Package = new PackageModel(); 
        $where = "goods_id in (".$goods_id.")";
        $Pack_arr = $Package -> Pack_goodsshow($where); 
        $package_arr = $Package -> Getdata('',''); 
        $t_gs = '';
        $statstr = '';
        if($post['is_index'] == '' || $post['is_hot'] == '')
        {
           foreach($package_arr as $key=>$val)
           {
               foreach($Pack_arr as $k => $v)
               {
                   if($val['package_id'] == $v["package_id"])
                   {
                       if($val['status'] == 1)
                       {
                           $t_gs .=$v['goods_id'].',';
                           $statstr = 1;
                       }
                   }
               }
           }
        }
        if(!empty($t_gs))
        {
            $UP_goods['status'] = 1;
        }else{
            if(!empty($post["status"]))
            {
                $UP_goods['status'] = $post["status"];
            }else{
                $UP_goods['status'] = 0;
                $UP_goods["is_hot"] = 0;
                $UP_goods["is_index"] = 0;
            }
        }
        $goodsID=$Goods -> GoodsUp($UP_goods , $goods_id);
        if($statstr == 1  && $post["status"] != 1){
            $this -> success("该商品是套餐商品不能下架","/Admin/Goods/Goods_list");
        }else{
            $this -> redirect("/Admin/Goods/Goods_list");
        }
    }
    
    /**
     * 添加商品
     * @param array $post 表单提交数据
     * @param array $files 文件数据
     */
    public function Goods_addtwo($post , $files)
    {
        $Goods = new GoodsModel(); 
        $savePath = "./uploads/thumb/".date('Ymd',time())."/"; 
        if(is_dir($savePath)==FALSE){
            mkdir($savePath , 0777);  
        } 
        $thumb_path=$savePath.$files['thumb']['name'];
        $str_thumb = $this -> upload($files['thumb']['name']);
        if(!empty($files['thumb']['name']))
        {
            if(empty($str_thumb))
            {
                echo "<script>alert('缩略图片格式上传错误');location.href='/Admin/Goods/Goods_add'</script>";die;
            }
        }
        $picPath = "./uploads/goods_pic/".date('Ymd',time())."/"; 
        if(is_dir($picPath)==FALSE){
            mkdir($picPath , 0777);  
        } 
        $pic_path=$picPath.$files['goods_pic']['name'];
        $str_pic = $this -> upload($files['goods_pic']['name']);
        if(!empty($files['goods_pic']['name']))
        {
            if(empty($str_pic))
            {
                echo "<script>alert('原图片格式上传错误');location.href='/Admin/Goods/Goods_add'</script>";die;
            }
        }
        move_uploaded_file($files['thumb']['tmp_name'] , $thumb_path);
        move_uploaded_file($files['goods_pic']['tmp_name'] , $pic_path);
        $server_pid = trim($post["serveris_1"]);
        $now_serverid = trim($post["server_id"]);
        $Server = new ServiceModel(); 
        $Goods_attr = new Goods_attrModel(); 
        $server_pname = $Server -> Getserverpname($server_pid); 
        $now_servername = $Server ->Getserverpname($now_serverid); 
        $attr=$post["attr"];
        $attr_gname = '';
        if(!empty($attr))
        {
            $attr_val=array();
            foreach($attr as $k=>$v)
            {
                $attr_val[$v]=$post["attr".$v];
            }
            $attr_gname = $Goods_attr -> Getattr_gname($attr_val);
        }
        $Add_goods = $this -> pub_parament($post);
        if(!empty($files['thumb']['name']))
        {
            $Add_goods["thumb"]          = trim($thumb_path , '.');
        }
        if(!empty($files['goods_pic']['name']))
        {
            $Add_goods["goods_pic"]      = trim($pic_path , '.');
        }
        $Add_goods["server_name"]    = $server_pname;
        $Add_goods["attr_name"]      = $attr_gname;
        $Add_goods["now_servername"] = $now_servername;
        $Add_goods["server_pid"]     = $server_pid;
        $Add_goods["s_id"]           = trim($post['server_id']);
        $Add_goods["status"]         = trim($post["status"]);
        $goods_id=$Goods -> GoodsAdd($Add_goods);
        if(isset($goods_id) && !empty($attr)) {
            $attr_add=array(
                "goods_id" => $goods_id,
                "s_id"     => trim($post['server_id']),
            );
            $attr_val=array();
            foreach($attr as $k=>$v)
            {
                $attr_val[$v]=$post["attr".$v];
            }
            $attradd= $Goods_attr -> Goods_attradd($attr_add , $attr , $attr_val);
        }
        $this -> redirect("/Admin/Goods/Goods_list");
    }
    
    /**
     * 添加修改公共参数
     * @param array $post 表单数据
     * @return multitype:string number
     */
    private function pub_parament($post)
    {
        $Uid = session('userid')!='' ? session('userid') : 1;
        return array(
            "goods_code"	 => trim($post['goods_code']),
            "short_title"	 => trim($post['short_title']),
            "title"		 => trim($post["titl"]),
            "description"	 => trim($post['description']),
            "now_price"		 => trim($post['now_price']),
            "old_price"		 => trim($post["old_price"]),
            "is_limit"		 => trim($post["is_lim"]),
            "limit_num"		 => trim($post["limit_num"]),
            "number"		 => trim($post["number"]),
            "give_sale"	         => trim($post["give_sale"]),
            "is_index"		 => trim($post["is_index"]),
            "content"		 => trim($post["content"]),
            "answer"		 => trim($post["answer"]),
            "success"		 => trim($post["success"]),
            "is_gift"		 => trim($post["is_gift"]),
            "is_hot"		 => trim($post["is_hot"]),
            "cost"		 => trim($post["cost"]),
            "addtime"            => time(),
            "adduser"            => $Uid,
        );
    }

    /**
     * 上传图片
     * @param array $file 图片名
     * @return boolean
     */
    private function upload($file){
        $GDstr = array('jpg','gif','png');
        $kz = substr(strrchr($file, '.'), 1);
        return true;	
    }
    
    /**
     * 商品列表
     * @param array $_GET    搜索时提交数据
     * @param int page_count 每页显示条数
     * @param int server     顶级业务Id
     * @param string status  商品状态
     * @param string keyword 搜索关键字
     */
    public function Goods_list()
    {	
        $Goods = new GoodsModel();  
        $where ='1=1';
        if(!empty($_GET))
        {
            $str =$Goods -> Term($_GET);
            $str =explode("and",$str);
            unset($str[0]);
            for($i=1 ; $i<=count($str) ; $i++)
            {
                $where .=" and ".$str[$i];
            }
        }
        $page_count = isset($_REQUEST['page_count']) ? $_REQUEST['page_count'] : 10;
        $Count = $Goods -> Getcount( $where ); 
        $Page = new Page($Count, $page_count);
        $map['server'] = @$_GET['server'];
        $map['status'] = isset($_REQUEST['status']) ? $_REQUEST['status'] : 10;
        $map['keyword'] = @$_GET['keyword'];
        $map['page_count'] = @$_GET['page_count'];
        foreach($map as $key=>$val) {   
            $p->parameter .= "$key=".urlencode($val)."&";   
        }
        $Pagesize =$Page ->show();
        $Goods_arr = $Goods -> Getdata($Page->firstRow , $Page->listRows , $where);
        $service = new ServiceModel();
        $this -> Pserver = $service -> getFirstServer();
        $this -> map = $map;
        $this -> p = trim(I("p"));
        $this->assign('Goods_arr' , $Goods_arr);
        $this->assign('page' , $Pagesize);
        $this->display(); 
    }
    
    /**
     * 删除商品
     * @param int goods_id 商品Id
     * @param int d        判断值
     */
    public function Goods_del(){
        $goods_id = trim(I("goods_id") , ',');
        $d = trim(I("d"));
        $Goods = new GoodsModel();  
        $Del = $Goods -> Del_Goods($goods_id);
        $Del_attr = $Goods -> Del_Goodsattr($Del); 
        if(!empty($d))
        {
            if(!empty($Del))
            {
                echo json_encode(array("data"=>'')); 
            }
        }else{
            $this -> redirect("/Admin/Goods/Goods_list");
        }

    }

    /**
     * 修改商品状态
     * @param string Bat   要修改商品状态
     * @param int goods_id 商品Id 
     */
    public function Goods_upstatus()
    {
        $Bath = trim(I("Bat"));
        $Str = explode("@" , $Bath);
        $goods_id = trim(I("goods_id") , ',');
        $Package = new PackageModel(); 
        $Goods = new GoodsModel();  
        $where = "goods_id in (".$goods_id.")";
        $Pack_arr = $Package -> Pack_goodsshow($where); 
        $package_arr = $Package -> Getdata('',''); 
        $t_gs = '';
        $strr = array('is_index','is_hot');
        if(!in_array($Str[1],$strr) && ($Str[1] == 'status' && $Str[0] != 1))
        {
            foreach($package_arr as $key=>$val)
            {
                foreach($Pack_arr as $k => $v)
                {
                    if($val['package_id'] == $v["package_id"])
                    {
                        if($val['status'] == 1)
                        {
                            $t_gs .=$v['goods_id'].',';
                        }
                    }
                }
            }
        }
        if(!empty($t_gs))
        {
            echo json_encode(array("data" => $t_gs));
        }else{
            $Up = $Goods -> status_UP($Bath , $goods_id);
            echo json_encode(array("data" => ""));
        }
    }
    
    /**
     * 列表修改商品信息
     * @param int goods_id 商品Id
     */
    public function Goods_listUp() {
        $Goods_ID[]["goods_id"] = trim(I("goods_id"));
        $Goods = new GoodsModel();  
        $Goods_find = $Goods -> GetgoodsId($Goods_ID);
        $this -> Goods_desting = $Goods_find;
        $this -> display();
    }
    
    /**
     * 判断商品编码是否重复
     * @param string code 商品编码
     * @param int pid 商品Id
     */
    public function Codeno() {
        $Goods = new GoodsModel();
        $code = trim (I ("code"));
        $p = trim (I ("pid"));
        if(!empty($p)) {
            $where = "(shop_goods.goods_code='".$code."' and shop_goods.goods_id<>".$p.") and shop_goods.goods_code='".$code."'";
        } else {
            $where = "shop_goods.goods_code='".$code."'";
        }
        $count = $Goods->Getcount ( $where );
        if($count != 0) {
            echo json_encode (array (
                "data" => "@" 
            ));
        }
    }
}
