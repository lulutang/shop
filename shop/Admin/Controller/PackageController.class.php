<?php
/**
 * 套餐管理Controller
 * @author 李建栋
 *
 */
namespace Admin\Controller;

use Admin\Model\ServiceModel;
use Admin\Model\PackageModel;
use Admin\Model\GoodsModel;
use Think\Controller;
use Page\Page;

include (COMMON_PATH . "Class/Page.class.php");
class PackageController extends Controller {
    public function _initialize() {
        header ( "content-type:text/html;charset=utf8" );
        $uid = session ( "userid" );
        if (empty ( $uid ))
        {
            echo '<script>top.location.href="/Admin/Login/index";</script>';
        }
    }

    /**
     * 判断是添加还是修改
     * @param int pakeageID 套餐Id
     * @param array $_POST  表单提交数据
     * @param array $_FILES 文件数据
     */
    public function upORadd()
    {
        $Package = new PackageModel();
        if (!$Package->create())
        {       
            //exit($Package->getError());
        }else{  
            $Package_id = trim(I("pakeageID"));
            if(empty($Package_id) )
            {
                    $this -> Package_add($_POST , $_FILES);
            }else{
                    $this -> Package_up($_POST , $_FILES , $Package_id);
            }
        }
    }

    /**
     * 修改套餐整个数据
     * @param array $post     表单提交数据
     * @param array $files    文件数据
     * @param int $Package_id 套餐Id
     */
    public function Package_up($post , $files , $Package_id)
    {
        $Package = new PackageModel(); 
        $Package_str = $Package -> GetInterAll(); 
        $savePath = "./uploads/package/thumb/".date('Ymd',time())."/";
        if(is_dir($savePath)==FALSE){
            mkdir($savePath , 0777);  
        } 
        $thumb_path=$savePath.$files['thumb']['name'];
        if(!empty($files['thumb']['name']))
        {
            $thumbupload = $this -> upload($thumb_path);
            if(empty($thumbupload))
            {
                echo "<script>alert('缩略图片一格式上传错误');location.href='/Admin/Package/Package_list'</script>";die;
            }
        }
        $picPath = "./uploads/package/pic/".date('Ymd',time())."/"; 
        if(is_dir($picPath)==FALSE){
            mkdir($picPath , 0777);  
        } 
        $pic_path=$picPath.$files['pic']['name'];
        if(!empty($files['pic']['name']))
        {
            $picupload = $this -> upload($pic_path);
            if(empty($picupload))
            {
                echo "<script>alert('原图片一格式上传错误');location.href='/Admin/Package/Package_list'</script>";die;
            }
        }
        $cose1_path =  "./uploads/package/cose_pic1/".date('Ymd',time())."/";
        if(is_dir($cose1_path)==FALSE){
            mkdir($cose1_path , 0777);  
        }
        $cose1path = $cose1_path.$files['cose1_pic']['name'];
        if(!empty($files['cose1_pic']['name']))
        {
            $cose1upload = $this -> upload($cose1path);
            if(empty($cose1upload))
            {
                echo "<script>alert('多余图片一格式上传错误');location.href='/Admin/Package/Package_list'</script>";die;
            }
        }
        $cose2_path =  "./uploads/package/cose_pic2/".date('Ymd',time())."/";
        if(is_dir($cose2_path)==FALSE){
          mkdir($cose2_path , 0777);  
        }
        $cose2path = $cose2_path.$files['cose2_pic']['name'];
        if(!empty($files['cose2_pic']['name']))
        {
            $cose2upload = $this -> upload($cose2path);
            if(empty($cose2upload))
            {
                echo "<script>alert('多余图片二格式上传错误');location.href='/Admin/Package/Package_list'</script>";die;
            }
        }
        $cose3_path =  "./uploads/package/cose_pic3/".date('Ymd',time())."/";
        if(is_dir($cose3_path)==FALSE){
            mkdir($cose3_path , 0777);  
        }
        $cose3path = $cose3_path.$files['cose3_pic']['name'];
        if(!empty($files['cose3_pic']['name']))
        {
            $cose3upload = $this -> upload($cose3path);
            if(empty($cose3upload))
            {
                echo "<script>alert('多余图片三格式上传错误');location.href='/Admin/Package/Package_list'</script>";die;
            }
        }
        move_uploaded_file($files['thumb']['tmp_name'] , $thumb_path); 
        move_uploaded_file($files['pic']['tmp_name'] , $pic_path); 
        move_uploaded_file($files['cose1_pic']['tmp_name'] , $cose1path); 
        move_uploaded_file($files['cose2_pic']['tmp_name'] , $cose2path); 
        move_uploaded_file($files['cose3_pic']['tmp_name'] , $cose3path); 
        $post["zuhe"] = $Package -> GoodsID();
        $post["addtime"]   = time();
        $post["starttime"] = strtotime($post["starttime"]);
        $post["endtime"]   = strtotime($post["endtime"]);
        $statstr = '';
        if(!empty($files['thumb']['name']))
        {
            $post["thumb"]     = trim($thumb_path,'.');
        }
        if(!empty($files['pic']['name']))
        {
            $post["pic"]       = trim($pic_path,'.');
        }
        if(!empty($files['cose1_pic']['name'])) {
            $post["cose1_pic"]     = trim($cose1path,'.');
        }
        if(!empty($files['cose2_pic']['name'])) {
            $post["cose2_pic"]     = trim($cose2path,'.');
        }
        if(!empty($files['cose3_pic']['name'])) {
            $post["cose3_pic"]     = trim($cose3path,'.');
        }
        if(empty($post["status"]))
        {
            $post["status"] = 2;
            $post["is_index"] = 0;
            $post["is_hot"] = 0;
        }
        if($post["status"] == 1)
        {
            $Package_str = '';
            $where ="package_id in(".$Package_id.")";
            $Package_data = $Package -> Getdata("","",$where);
            foreach($Package_data as $k => $v)
            {
                $Package_str .= $v["zuhe"].",";
            }
            $PackAge_string = trim($Package_str,',');
            $strring_pack = $Package -> Goods_status($PackAge_string);
            if(!empty($strring_pack)){
                $post["status"] = 2;
                $statstr = 1;
            }
        }
        $Package -> PackageUP($post , $Package_id);
        if(!empty($Package_str))
        {
                $Package -> Del_Gid($Package_id);
                $Package_arr = $Package -> UPpackAge($Package_id);
                $Goods_package_ID = $Package_arr["Pack_arr"]["zuhe"];
                $Package -> pack_goods($Goods_package_ID , $Package_id);	
        }
        $uid = session("userid");
        $where="1=1 and u_id=".$uid;
        $Del_Inter = $Package -> Del_interAll($where);
        if($statstr == 1)
        {
           $this -> success("该套餐中有下架商品不能上架该套餐","/Admin/Package/Package_list");
        }else{
            $this -> redirect("/Admin/Package/Package_list");
        }
    }

    /**
     * 添加套装
     * @param array $post 表单提交数据
     * @param array $files文件数据
     */
    public function Package_add($post , $files)
    {
        $Package = new PackageModel(); 
        $Package_str = $Package -> GetInterAll(); 
        $savePath = "./uploads/package/thumb/".date('Ymd',time())."/";
        if(is_dir($savePath)==FALSE){
            mkdir($savePath , 0777);  
        } 
        $thumb_path=$savePath.$files['thumb']['name'];
        $thumbupload = $this -> upload($thumb_path);
        if(!empty($files['thumb']['name']))
        {
            if(empty($thumbupload))
            {
                die("缩略图片格式上传错误");
            }
        }
        $picPath = "./uploads/package/pic/".date('Ymd',time())."/"; 
        if(is_dir($picPath)==FALSE){
           mkdir($picPath , 0777);  
        } 
        $pic_path=$picPath.$files['pic']['name'];
        $picupload = $this -> upload($pic_path);
        if(!empty($files['pic']['name']))
        {
            if(empty($picupload))
            {
                die("原图片格式上传错误");
            }
        }
        $cose1_path =  "./uploads/package/cose_pic1/".date('Ymd',time())."/";
        if(is_dir($cose1_path)==FALSE){
          mkdir($cose1_path , 0777);  
         }
        $cose1path = $cose1_path.$files['cose1_pic']['name'];
        $cose1upload = $this -> upload($cose1path);
        if(!empty($files['cose1_pic']['name']))
        {
            if(empty($cose1upload))
            {
                die("多余图片一格式上传错误");
            }
        }
        $cose2_path =  "./uploads/package/cose_pic2/".date('Ymd',time())."/";
        if(is_dir($cose2_path)==FALSE){
            mkdir($cose2_path , 0777);  
        }
        $cose2path = $cose2_path.$files['cose2_pic']['name'];
        $cose2upload = $this -> upload($cose2path);
        if(!empty($files['cose2_pic']['name']))
        {
            if(empty($cose2upload))
            {
                die("多余图片二格式上传错误");
            }
        }
        $cose3_path =  "./uploads/package/cose_pic3/".date('Ymd',time())."/";
        if(is_dir($cose3_path)==FALSE){
            mkdir($cose3_path , 0777);  
        }
        $cose3path = $cose3_path.$files['cose3_pic']['name'];
        move_uploaded_file($files['thumb']['tmp_name'] , $thumb_path); 
        move_uploaded_file($files['pic']['tmp_name'] , $pic_path); 
        move_uploaded_file($files['cose1_pic']['tmp_name'] , $cose1path); 
        move_uploaded_file($files['cose2_pic']['tmp_name'] , $cose2path); 
        move_uploaded_file($files['cose3_pic']['tmp_name'] , $cose3path); 
        $post["zuhe"] = $Package -> GoodsID();
        $post["addtime"]   = time();
        $post["starttime"] = strtotime($post["starttime"]);
        $post["endtime"]   = strtotime($post["endtime"]);
        if(!empty($files['thumb']['name']))
        {
            $post["thumb"]     = trim($thumb_path,'.');
        }
        if(!empty($files['pic']['name']))
        {
            $post["pic"]       = trim($pic_path,'.');
        }
        if(!empty($files['cose1_pic']['name'])) {
            $post["cose1_pic"] =  trim($cose1path,'.');
        }
        if(!empty($files['cose2_pic']['name'])) {
            $post["cose2_pic"] =  trim($cose2path,'.');
        }
        if(!empty($files['cose3_pic']['name'])) {
            $post["cose3_pic"] =  trim($cose3path,'.');
        }
        $Package_Id = $Package -> PackageAdd($post);
        $where="1=1";
        $uid = session("userid");
        $where="1=1 and u_id=".$uid;
        $Del_Inter = $Package -> Del_interAll($where);
        if(isset($Package_Id))
        {
            $status_packgoods = $Package -> pack_goods($Package_str , $Package_Id); 
            $this -> redirect("/Admin/Package/Package_list");
        }
    }

    /**
     * 上传图片
     * @param array $file 文件数据
     * @return boolean
     */
    private function upload($file){
        $GDstr = array('jpg','gif','png');
        $kz = substr(strrchr($file, '.'), 1);
        return true;

    }

    /**
     * 获取已上架商品
     * @param int package_id 套餐Id
     */
    public function Optgoods()
    {
        $Goods = new GoodsModel(); 
        $this -> package_id = trim(I("package_id"));
        $where = 'shop_goods.status = 1'; 
        $Count = $Goods -> Getcount( $where );
        $ye =ceil($Count/10);
        $Goods_arr = $Goods -> Getdata(0 , 10 , $where);
        $str='';
        for ($i=1; $i <= $ye ; $i++) { 
            $str.='<a href="javascript:;" onclick="page('.$i.')">'.$i.'</a>&nbsp;&nbsp';
        }
        $this->str=$str;
        $this -> assign("Goods_arr" , $Goods_arr);
        $this -> display();
    }
	
    /**
     * 分页后获取的数据
     * @param string k      套餐中商品Id
     * @param int str       当前页
     * @param string hidGid 选择后商品Id
     */
    public function Interye()
    {	 
        $Goods = new GoodsModel(); 
        $k = trim(I("k")); 
        $ye=trim(I('str'));
        $goods_ID = trim(I('goods_id'),',');
        $Goods_hidGid = trim(I("hidGid"));
        if(!empty($Goods_hidGid))
        {
            if(!empty($goods_ID))
            {
                $zu_goods = $goods_ID.",".$Goods_hidGid;
            }else{
                $zu_goods = $Goods_hidGid;
            }
        }else{
                $zu_goods = $goods_ID;
        }
        $zu_goodsarr = array_unique(explode(',',$zu_goods));
        $cue_arr = implode(',',$zu_goodsarr);
        $title=trim('where');
        $where = 'shop_goods.status = 1';
        $start=($ye-1)*10;
        $Goods_arr = $Goods -> Getdata($start , 10 , $where);
        $this -> zu_goodsarr = $zu_goodsarr;
        $this -> skey = $where;
        $this -> zu_goods = $cue_arr;
        $this -> Goods_arr = $Goods_arr;
        $this ->display();
    }
	
    /**
     * 套餐选择商品搜索
     * @param string search 套餐中商品关键字
     */
    public function Goods_search()
    {
        $search["keyword"] = trim(I("search"));
        $Goods = new GoodsModel(); 
        $where = $Goods -> Term($search);
        if(empty($search["keyword"]))
        {
            $where = 'shop_goods.status = 1';
        }
        $Goods_arr = $Goods -> Getdata(0 , 10 , $where);
        $this -> Goods_arr = $Goods_arr;
        $this ->display();
    }

    /**
     * 在临时表中加入商品id,取出要添加商品数据
     * @param string goods_id 商品Id 
     */
    public function Inter()
    {
        $GoodsID = trim(I("goods_id") , ',');
        $this -> package_id = trim(I("packade_id"));
        $Package = new PackageModel(); 
        $Goods = new GoodsModel(); 
        $Package -> InterAdd($GoodsID); 
        $Package_str = $Package -> GetInterAll(); 
        $Goods_arr = $Goods -> moreID($Package_str);
        $this -> assign("Goods_arr" , $Goods_arr);
        $this ->display();
    }

    /**
     * 删除临时表中指定商品Gid
     * @param int Gid_goodsID 商品Id
     */
    public function Inter_Del()
    {
        $inter_Gid = trim(I("Gid_goodsID"));
        $uid = session("userid");
        $where = 'Gid ='.$inter_Gid." and u_id=".$uid;
        $Package = new PackageModel(); 
        $Goods = new GoodsModel(); 
        $Package -> Del_interAll($where);
        $Package_str = $Package -> GetInterAll();
        $Goods_arr = $Goods -> moreID($Package_str); 
        $this -> assign("Goods_arr" , $Goods_arr);
        $this ->display("Inter");
    }

    /**
     * 套餐列表
     * @param array $_GET 搜索条件
     */
    public function Package_list()
    {
        $Package = new PackageModel(); 
        $Goods = new GoodsModel();  
        $where = '1=1';
        if(!empty($_GET["stat"]))
        {
            $arr = explode("@",$_GET["stat"]);
            $where .=" and ".$arr[0]."=".$arr[1];
        }
        if(!empty($_GET["keywords"]))
        {
            $k = $_GET["keywords"];
            $where .= " and (package_code like '%".$k . "%' or short_title like '%".$k ."%')";
        }
        $Count = $Package -> Getcount($where); 
        $page_count = isset($_REQUEST['page_count']) ? $_REQUEST['page_count'] : 10;
        $Page = new Page($Count, $page_count);
        $this -> Pagesize =$Page ->show();
        $Package_arr = $Package -> Getdata($Page->firstRow , $Page->listRows , $where); 
        $Goods_arr   = $Package -> Package_goods();
        $map['stat'] = @$_GET['stat'];
        $map['keyword'] = @$_GET['keywords'];
        foreach($map as $key=>$val) {   
            $p->parameter .= "$key=".urlencode($val)."&";   
        }
        $service = new ServiceModel();
        $this -> Pserver = $service -> getFirstServer();
        $this -> map = $map;
        $this ->assign("Goods_arr" , $Goods_arr);
        $this ->assign("Package_arr" , $Package_arr);
        $this ->display();
    }

    /**
     * 删除套餐关联表数据修改套餐组合商品ID
     * @param int Goods_id   商品Id
     * @param int Package_id 套餐Id
     */
    public function PackageDel()
    {
        $Goods_id   = trim(I("Goods_id"));
        $Package_id = trim(I("Package_id"));
        $Package = new PackageModel(); 
        $del = $Package -> Package_del($Goods_id , $Package_id);
        $this -> redirect("/Admin/Package/Package_list");
    }

    /**
     * 给套餐添加商品
     * @param int Goods_id   商品Id
     * @param int Package_id 套餐Id
     */
    public function UPpack()
    {
        $Goods_id   = trim(I("goods_id"),',');
        $Package_id = trim(I("package_id"));
        $Package = new PackageModel(); 
        $Packadd = $Package -> addpackage($Goods_id , $Package_id);
        echo "0@";
    }

    /**
     * 修改套餐
     * @param int Package_id 套餐Id
     */
    public function UPpackage()
    {
        $PackageId = trim(I("package_id"));
        $Package = new PackageModel(); 
        $Package_Arr = $Package -> UPpackAge($PackageId);
        $this -> Package_Arr = $Package_Arr["Pack_arr"];
        $this -> Goods_arr = $Package_Arr["Package_goods"];
        $this -> PackageId = $PackageId;
        $this -> display();	
    }

    /**
     * 删除“修改套餐中第一步”的商品
     * @param int Gid_goodsID 商品Id
     * @param int Package_id  套餐Id
     * @param int userid      用户Id
     */
    public function Del_Gp_Inter()
    {
        $PackageId = trim(I("PackageId"));
        $GoodsId   = trim(I("Gid_goodsID"));
        $uid = session("userid");
        $Package = new PackageModel(); 
        $del = $Package->Package_del($GoodsId , $PackageId);
        $where = 'Gid ='.$GoodsId." and u_id=".$uid;
        $Package -> Del_interAll($where);
        $Package_Arr = $Package -> UPpackAge($PackageId);
        $this -> Package_Arr = $Package_Arr["Pack_arr"];
        $this -> Goods_arr   = $Package_Arr["Package_goods"];
        $this -> PackageId   = $PackageId;
        $this -> display("Inter");	
    }

    /**
     * 修改套餐状态
     * @param int package_id   套餐Id
     * @param string syllable  状态名
     * @param int staus        状态
     */
    public function syllUP()
    {
        $package_id = trim(I("package_id"),',');
        $syllable   = trim(I("syllable"));
        $staus      = trim(I("staus"));
        $Package = new PackageModel();
        if($syllable == 'del')
        {
            $where ="package_id in(".$package_id.")";
            $Package_data = $Package -> Getdata("","",$where);
            $str ='';
            foreach($Package_data as $k => $v)
            {
                if($v["status"] != 1)
                {
                    $str .=$v["package_id"].',';
                }
            }
            $string = trim($str , ',');
            $Package -> Del_Gid($string);
            $Package ->DEl_package($string);
            if(empty($string)){
                echo json_encode(array("data"=> $string));
            }else{
                echo json_encode(array("data"=> $string));
            }
        }else{
            if($syllable == "status" && $staus == 1)
            {
                $Package_str = '';
                $where ="package_id in(".$package_id.")";
                $Package_data = $Package -> Getdata("","",$where);
                foreach($Package_data as $k => $v)
                {
                   $Package_str .= $v["zuhe"].",";
                }
                $PackAge_string = trim($Package_str,',');
                $strring_pack = $Package -> Goods_status($PackAge_string);
                if(!empty($strring_pack)){
                    echo json_encode(array("code" => 1,"data"=>"请把套餐商品上架后在对套餐进行上架"));
                }else{
                    $Package -> UPstatus($package_id , $syllable , $staus ); 
                    echo json_encode(array("data"=>$staus));
                }
             }else{
                $stat_data["is_index"] = 0;
                $stat_data["is_hot"] = 0;
                $Package -> where("package_id in (".$package_id.")") -> save($stat_data);
                $Package -> UPstatus($package_id , $syllable , $staus );
                echo json_encode(array("data"=>$staus));
            }
        }
    }

    /**
     * 删除套餐临时表的所有数据
     * @param int userid 用户Id
     */
    public function Dell_ALL()
    {
        $Package = new PackageModel(); 
        $uid = session("userid");
        $where = '1=1 and u_id='.$uid;
        $Package -> Del_interAll($where);	
    }

    /**
     * 取出套餐临时表数据
     * @param int userid 用户Id
     */
    public function Getinterall() {
        $inter = M("inter");
        $uid = session ("userid");
        echo json_encode(array(
            "data" => $inter->where ("u_id =".$uid )->count () 
        ) );
    }
    /**
     * 验证套餐编码唯一
     * @param string code 套餐编码
     * @param int pid     套餐Id
     */
    public function onepackcode() {
        $Package = new PackageModel (); 
        $code = trim(I("code"));
        $p = trim(I("pid"));
        if (!empty($p)) {
            $where = "(package_code='".$code."' and package_id<>".$p.") and package_code='".$code."'";
        } else {
            $where = "package_code='".$code."'";
        }
        $count = $Package->Getcount ($where);
        if ($count != 0) {
            echo json_encode(array (
                "data" => "@" 
            ) );
        }
    } 

}