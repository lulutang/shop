<?php
/**
 * 财务对账Controller
 * @author lijiandong
 * @createtime 2015-3-2
 *
 */
namespace Admin\Controller;

use Think\Controller;
use Admin\Model\UorderModel;
use Admin\Model\ServiceModel;
use Page\Page;
//引入分页类
include(COMMON_PATH."Class/Page.class.php");
class UorderController extends Controller {
    private $one = 1;
    private $zero = 0;
    private $ten = 10;
    private $fifty = 50;
    //发票驳回发送短信内容
    private $phonecontent = "亲您好！您的票务信息经过审核，发现填写的信息与相关执照信息不符，请您再次确认票务信息。";
    private $crumbs = array(
                        "1"=>"当天", 
                        "7"=>"一周",
                        "m"=>"一个月"
        );
    /**
        * 初始化
    */
    public function _initialize() {
        header("content-type:text/html;charset=utf8");
        $uid = session("userid");
        if(empty($uid))
        {
            //echo '<script>top.location.href="/Admin/Login/index";</script>';
            //exit;
        }
    }
    
    /**
     * 判断是支付还是未支付
     * @param array $get 查询条件
     */
    public function judge()
    {
        $get = I ( 'get.' );
        if(isset($get["pay"]))
        {
            $get["status"] = $get["pay"];
            $get["ordertime"] = "createtime";
            $this -> orders($get);
        }else{
            $this -> orders($get);
        } 
    }
    
   /**
    * 获取用户订单信息
    * @param array $get 查询条件
    * @param int p 第几页分页
    * @param int pageSize 分页数据多少条
    * @param int sort_pay 按照支付时间排序，默认  desc
    * @param int $ordertime 判断订单根据支付时间还是添加时间查询
    */
    public function orders($get) {
        $order = new UorderModel();
        $wsort=array("asc","desc");
        $page = isset ( $get ['p'] ) ? intval ( $get ['p'] ) : $this -> one;
        $pageSize = !empty ( $get ['size'] ) ? intval ( $get ['size'] ) : $this -> ten;
        $sort_pay = !empty($get ['sort_pay'])  ? trim ( $get ['sort_pay'] ) : "desc";
        $ordertime = !empty($get ['ordertime'])  ? trim ( $get ['ordertime'] ) : "pay_time";
        
        $sort= array($ordertime,$sort_pay);
        $where = $order -> term($get);
        if($sort_pay == "desc")
        {
            $sortorder = $wsort[0];
        }else{
            $sortorder = $wsort[1];
        }
        $cate = "/admin/Uorder/judge/sort_pay/".$sortorder."/";
        unset($get["sort_pay"]);
        $cate = $order -> cateNote($get , $cate);
        //获取订单数量
        $ordercount = $order -> getOrderCount($where);
        $pageModel = new Page ( $ordercount , $pageSize);
        $pages = $pageModel->show();
        //获取订单数据
        $orderall   = $order -> getOrderAll ($pageModel->firstRow , $pageModel->listRows , $sort ,$where);
        $this -> assign("cate" , $cate);
        $this -> assign("startseat",$pageModel->firstRow);
        $this -> assign("endseat",$pageModel->listRows);
        $this -> assign("get",$get);
        $this -> assign ('pages', $pages);
        $this -> assign ('sortorder', $sortorder);
        $this -> assign ('orderall', $orderall);
        if($get["status"] == "0")
        {
            $this -> display ('Unpaid');
            exit;
        }
        $this -> display ('orders');
    }
    
    /**
     * 订单详情页面
     * @param int $orderid 订单Id
     * @param array $get 提交数据
     */
    public function orderdetailed($orderid) {
        $order = new UorderModel();
        $get = I ( 'get.' );
        $where = "order_id = ". $orderid;
        //获取订单单条数据
        $orderfind = $order -> getOrderData($where);
        //根据订单id获取订单商品
        $ordergoods = $order -> getOrderGoods($where);
        //获取发票信息
        $getbill = $order -> getBillFind($where);
        $userwhere  = "user_id=".$orderfind["getOrderFind"]["user_id"];
        //获取订单用户
        $userdata = $order -> vipUser($userwhere);
        $this -> assign("orderfind" , $orderfind["getOrderFind"]);
        $this -> assign("receiver" , $orderfind["receiver"]);
        $this -> assign("ordergoods" , $ordergoods);
        $this -> assign("getbill" , $getbill);
        $this -> assign("userdata" , $userdata);
        if($orderfind["getOrderFind"]["status"] == $this -> one)
        {
            $this -> display();
            exit();
        }
        $this -> display("UnpayDetail");
    }

    /**
     * 订单商品服务
     * @param int p 第几页分页 默认1
     * @param int pageSize 分页数据多少条 默认10
     */
    public function ordergoods() {
        $get = I ( 'get.' );
        $order   = new UorderModel();
        $service = new ServiceModel();
        $wsort=array("asc","desc");
        $page = isset ( $get ['p'] ) ? intval ( $get ['p'] ) : $this -> one;
        $pageSize = !empty ( $get ['size'] ) ? intval ( $get ['size'] ) : $this -> ten;
        $sort_pay = !empty($get ['sort_pay'])  ? trim ( $get ['sort_pay'] ) : "desc";
        //获取拼接的订单商品搜索条件
        $where = $order -> termOrderGoods($get);
        if($sort_pay == "desc")
        {
            $sortorder   = $wsort[0];
        }else{
            $sortorder   = $wsort[1];
        }
        $cate = "/admin/Uorder/ordergoods/sort_pay/".$sortorder."/";
        unset($get["sort_pay"]);
        $cate = $order -> cateNote($get , $cate);
        $parentservice = $service -> getService();
        $seervicedata  = $order   -> handleService($parentservice);
        //获取订单商品已支付数量
        $ordergoodscount = $order -> orderGoodsCount($where);
        $pageModel = new Page($ordergoodscount , $pageSize);
        //获取订单商品已支付数据
        $ordergoodsdata  = $order -> getOrderGoods($where ,$pageModel->firstRow , $pageModel->listRows , $sort_pay);
        //获取所有的商品
        $goodswhere = $order -> one ."=".$this -> one;
        $goodsdata  = $order -> goodsDataAll($goodswhere);
        $pages = $pageModel -> show();
        $this -> assign("goodsdata" , $goodsdata);
        $this -> assign("cate" ,$cate);
        $this -> assign("get" , $get);
        $this -> assign ("pages" , $pages );
        $this -> assign("ordergoodsdata" , $ordergoodsdata);
        $this -> assign("seervicedata" , $seervicedata);
        $this -> assign("sortorder" , $sortorder);
        $this -> display();
    }
    
    /**
     * 订单商品详细页面
     * @param int $ordergid 订单商品id
     */
    public function ordergoodsdetail($ordergid) {
        $order   = new UorderModel();
        $ordergoodswhere = "id=$ordergid";
        $traderwhere     = "ordergoods_id=$ordergid";
        //获取订单服务流程
        $goodsflow  = $order -> commodityFlow($ordergid);
        //获取订单商品数据
        $orderGoods = $order -> getFindOrderGoods($ordergoodswhere);
        $needWhere = "need_id=".$orderGoods["need_id"];
        $goodsneeddata = $order -> goodsNeed($needWhere);
        $trawhere = "id=".$orderGoods["deal_id"];
        //获取申请人信息
        $traderfind = $order -> getFindtrader($trawhere);
        //获取操作人信息
        $compiledata = $order -> compileData($traderwhere); 
        //获取该商品跑堂信息
        $adminuserwhere = "bwm.kid=".$orderGoods["user_id"];
        $adminuserfind = $order -> adminUserFind($adminuserwhere);
        $traderfind["apply"]    = explode(',', $traderfind["apply"]);
        $traderfind["priority"] = explode(',', $traderfind["priority"]);
        $ordergoodsflow  = $order -> orderGoodsStatus;
        $this -> assign("adminuserfind" , $adminuserfind);
        $this -> assign("goodsneeddata" , $goodsneeddata);
        $this -> assign("traderfind" , $traderfind);
        $this -> assign("compiledata" , $compiledata);
        $this -> assign("goodsflow",$goodsflow);
        $this -> assign("ordergoodsflow" , $ordergoodsflow);
        $this -> assign("orderGoods" , $orderGoods);
        $this -> display();
    }
    
    /**
     * 订单商品专利、版权、除商标注册以外的详细页面
     * @param int $ordergid 订单商品id
     */
    public function patentdetail($ordergid) {
        $order   = new UorderModel();
        $style = C('SELF_STYLE');
        $stylecount = count($style);
        $ordergoodswhere = "id=$ordergid";
        //获取订单商品数据
        $orderGoods = $order -> getFindOrderGoods($ordergoodswhere);
        //获取订单服务流程
        $goodsflow  = $order -> commodityFlowTwo($ordergid);
        $ordergoodsflow  = $order -> orderGoodsStatus;
        $this -> assign("stylecount" , $stylecount);
        $this -> assign("style",$style);
        $this -> assign("ordergoodsflow",$ordergoodsflow);
        $this -> assign("goodsflow",$goodsflow);
        $this -> assign("orderGoods" , $orderGoods);
        $this -> display();
    }
    
    /**
     * 订单商品专利、版权、除商标注册以外的状态修改
     * @param array $get 修改条件
     */
    public function uppatent($ordergoods_id) {
        $order   = new UorderModel();
        $get = I ( 'get.' );
        $user    = session();
        //修改流程状态信息
        $orderid = $order -> addCompile($user , $get);
        $this->redirect("/Admin/Uorder/patentdetail/ordergid/".$ordergoods_id);
    }
    
    /**
     * 订单商品专利、版权、除商标注册以外的修改
     */
    public function upshort(){
        $order   = new UorderModel();
        $get = I ( 'get.' );
        $ordergoodswhere = "id=". $get['ordergid'];
        //获取单条订单商品
        $ordergoodsfind  = $order -> getFindOrderGoods($ordergoodswhere);
        //修改订单商品数据
        $overdata = $order -> overPatentData($ordergoodsfind , $get , $ordergoodswhere);
        echo json_encode(array("code"=>$overdata,"data"=>$get['ordergid']));
    }
    
    /**
     * 修改服务商品流程状态
     * @param array $get 修改条件
     * @param array $user 取出session中的用户信息
     */
    public function upflowstatus() {
        $order   = new UorderModel();
        $user    = session();
        $get = I ( 'get.' );
        $orderid = $order -> addCompile($user , $get);
        $this->redirect("/Admin/Uorder/ordergoodsdetail/ordergid/".$orderid);
    }
    
    /**
     * 商标注册编辑页面
     * @param array $get 查询条件
     */
    public function shopenrollover() {
       $order   = new UorderModel();
        $get =  I ( 'get.' );
        $style = C('SELF_STYLE');
        $ordergoodsflow  = $order -> orderGoodsStatus;
        $ordergoodswhere = "id=".$get['ordergid'];
        $traderwhere     = "ordergoods_id=".$get['ordergid'];
        //获取订单服务流程
        $goodsflow  = $order -> commodityFlow($get['ordergid']);
        //获取订单商品数据
        $orderGoods = $order -> getFindOrderGoods($ordergoodswhere);
        //获取用户申请人
        $traderallwhere  = "trader_belong =".$orderGoods["user_id"];
        //获取申请人信息
        $traderfind = $order -> getFindtrader($traderwhere);
        //申请人多条数据
        $traderall = $order -> getAlltrader($traderallwhere);
        $needWhere = "need_id=".$orderGoods["need_id"];
        $goodsneeddata = $order -> goodsNeed($needWhere);
        $traderfind["apply"]    = explode(',', $traderfind["apply"]);
        $traderfind["priority"] = explode(',', $traderfind["priority"]);
        $this -> assign("goodsneeddata" , $goodsneeddata);
        $this -> assign("traderall" , $traderall);
        $this -> assign("traderfind" , $traderfind);
        $this -> assign("goodsflow",$goodsflow);
        $this -> assign("ordergoodsflow" , $ordergoodsflow);
        $this -> assign("orderGoods" , $orderGoods);
        $this -> assign("style",$style);
        $this ->display();
    }
    
    /**
     * 商标注册编辑
     * @param int $ordergid 订单商品id
     * @param array $post 要修改的数据
     */
    public function trademark($ordergid) {
        $order  = new UorderModel();
        $ordergoodswhere = "id=$ordergid";
        $pubcel = A("Pubcel");
        $post = I("post.");
        $filepath = "./uploads/enroll/";
        $mypath = $pubcel -> upFile($filepath , $_FILES["enroll"]["name"] , $_FILES["enroll"]["tmp_name"]);
        $post["enroll"] = $mypath;
        $ordergoodsfind  = $order -> getFindOrderGoods($ordergoodswhere);
        $savedata = $order -> tardUpData($ordergoodsfind , $post , $ordergid);
        $this->redirect("/Admin/Uorder/ordergoodsdetail/ordergid/".$ordergid);
    }
    
    /**
     * 发票管理
     * @param array $get 搜索条件
     * @param string $where    订单查询条件
     * @param array  $get      订单查询数据
     * @param int    $page     跳转页数
     * @param int    $pageSize 每页显示记录数
     */
    public function orderreceipt() {
        $order  = new UorderModel();
        $get = I ( 'get.' );
        $where = $order -> term($get);
        $page = isset ( $get ['p'] ) ? intval ( $get ['p'] ) : $this -> one;
        $pageSize = !empty ( $get ['size'] ) ? intval ( $get ['size'] ) : $this -> ten;
        $where.=" AND is_invoile=1 AND bill_status=".$get["invoile"];
        $wheretime= $order -> getWhere($get);
        if(!empty($wheretime))
        {
            $where.=" AND ".$wheretime;
        }
       
        $ordercount = $order -> getOrderCount($where);
        $pageModel = new Page ( $ordercount , $pageSize);
        $pages = $pageModel->show();
        //获取订单数据
        $orderall   = $order -> getOrderAll ($pageModel->firstRow , $pageModel->listRows , $sort ,$where);
        $this -> assign("get" , $get);
        $this -> assign("startseat" , $pageModel->firstRow);
        $this -> assign("endseat" , $pageModel->listRows);
        $this -> assign("crumbs",$this -> crumbs);
        $this -> assign("daytime",$get["daytime"]);
        $this -> assign('pages', $pages);
        $this -> assign("num" ,$get["invoile"]);
        $this -> assign ('sortorder', $sortorder);
        $this -> assign ('orderall', $orderall);
        $this -> display ();
    }
    
    /**
     * 发票管理详情
     * @param array $get       查询条件
     * @param int   $receiptid 订单id
     */
    public function receiptdetailed($receiptid) {
        $order  = new UorderModel();
        $get = I ( 'get.' );
        $where = "order_id =". $receiptid;
        //获取单条订单信息
        $receiptdata = $order -> getOrderData($where);
        //根据条件获取订单商品
        $ordergoods   = $order -> getOrderGoods($where , '','','');
        //发票信息
        $billdata   = $order -> getBillFind($where);
        $this -> assign("receiptdata",$receiptdata);
        $this -> assign("orderGoods",$ordergoods);
        $this -> assign("billdata" , $billdata);
        if(!empty($get["downword"])){   //下载excel文档
            
            $pubcel = A("Pubcel");
            $status = "word";
            $filename = "word".time();
            $this -> display("word");
            $pubcel -> word($status, '', $filename);
            exit();
        }
        $this -> display();
    }
    
    /**
     * 开发票
     * @param array $get 条件
     */
    public function upreceipt() {
        $order  = new UorderModel();
        $get = I ( 'get.' );
        if(empty( $get ['bill_id'] ))
        {
            //开票成功
           $invoile= $order -> upSuccess($get["order_id"]);
           if(isset($invoile))
           {
               $this -> redirect("/admin/Uorder/orderreceipt/invoile/1");
           }else{
               return false;
           }
        }else{
            //发票驳回
           $invoile= $order -> defeated($get["bill_id"]);
           if(isset($invoile)) {    //发送短信
               
               send_msgnote($get["phone"], $this -> $phonecontent);
               $this -> redirect("/admin/Uorder/orderreceipt/invoile/0");
           }else{
               return false;
           }
        }
    } 
            
    /**
     * 下载发票管理excel表格
     */
    public function downnovile() {
        $order = new UorderModel();
        $get = I ( 'get.' );
        $where = "od.status=1 AND od.is_invoile=1 AND bl.bill_status=".$get["invoile"];
        $where = $order -> downWhere($get , $where);
        //调用Pubcel下载控制器 
        $Publicel   = A("Pubcel");
        //取出下载标题头
        $ordertitle = $Publicel -> ordertle();
        //下载数据
        $orderdata = $order -> novileDownData($where , $get["startseat"], $get["endseat"]);
        $Publicel -> out($orderdata , $ordertitle["invoicetitle"] ,"财务订单");
    }

    /**
     * 下载订单管理excel表格
     * @param array $get 下载条件
     */

    public function down(){
        $order = new UorderModel();
        $get = I ( 'get.' );
        $startdata = strtotime(date("Y-m-d")."23:59:59");
        $enddata = strtotime(date("Y-m-d")."0:0:0");
        //调用Pubcel下载控制器 
        $Publicel   = A("Pubcel");
        //取出下载标题头
        $ordertitle = $Publicel -> ordertle();
        $start = strtotime(str_replace("+"," ",$get["starttime"]));
        $end = strtotime(str_replace("+"," ",$get["endtime"]));
        $where = isset ( $get ['pay'] ) ? "od.status =". intval ($get["pay"]) : "od.status =1";
        if(is_numeric($start) && !is_numeric($end)){
            $where .= " AND od.pay_time" ." BETWEEN ". $start ." AND ". $startdata;
        }elseif(!is_numeric($start) && is_numeric($end)){
            $where .= " AND od.pay_time" ." BETWEEN ". $enddata ." AND ". $end;
        }elseif(is_numeric($start) && is_numeric($end)){
            $where = isset ( $get ['pay'] ) ? "od.status =". intval ($get["pay"]) : "od.status =1 and od." . $get['ordertime'] ." BETWEEN ". $start ." AND ". $end;
        }
        if(!empty($get["order_card"]) && $get["order_card"] != "ordertime"){
            $where .=" AND od.order_card like '%".$get["order_card"]."%'";
        }
        //取出下载数据
        $orderdata = $order -> orderDownData($where , $get);
        $Publicel -> out($orderdata , $ordertitle["ordertle"] ,"财务订单");
    }
    
    /**
     * 根据传入内容寻找子集
     * @param array $style 类别数组
     * @param array $get   匹配类别条件
     */
    public function findchild() {
        $order = new UorderModel();
        $style = getAllCategory();
        $get   = I ( 'get.' );
        $categorydata = $order -> findCategory($style , $get);
        echo json_encode($categorydata);
    }
    
    /**
     * 搜索二级匹配类别
     * @param array $get 匹配条件
     */
    public function searchtwo() {
        $get = I('get.');
        $order = new UorderModel();
        $style = getAllCategory();
        $twocate = $order -> searchCate($style , $get);
        echo json_encode($twocate);
    }
    
    /**
     * 申请书
     * @param array $get 查询的数据
     */
    public function apply() {
        $get = I("get.");
        $order = new UorderModel();
        $where = "ogd.id=".$get["ordergid"];
        $apply = $order -> applyFind($where);
        $this -> assign("apply" , $apply);
         if($get["down"] == "downpdf"){
            $downpdfhtml = $this ->fetch();
            $Publicel = A("pubcel");
            $Publicel -> downpdf("apply" , $downpdfhtml);   
        }else{
            $this -> display();
        }
    }
    
    /**
     * 委托书
     * @param array $get 查询的数据
     */
    public function deput() {
        $get = I("get.");
        $order = new UorderModel();
        $where = "ogd.id=".$get["ordergid"];
        $deputdata = $order -> deputFind($where);
        $this -> assign("deputdata" , $deputdata);
        if($get["down"] == "downpdf"){
            $downpdfhtml = $this -> fetch();
            $Publicel = A("pubcel");
            $Publicel -> downpdf("deput" , $downpdfhtml);   
        }else{
            $this -> display();
        }
    }
    
    /**
     * 商标注册协议书
     * @param array $get 查询的数据
     */
    public function trademarkbook() {
        $Publicel = A("pubcel");
        $get = I("get.");
        $order = new UorderModel();
        $where = "ogd.id=".$get["ordergid"];
        $trademarkdata = $order -> trademarkbookFind($where);
        $trademarkdata["replacemoney"] = $Publicel -> replaceMoney($trademarkdata["service_price"]); 
        $this -> assign("trademarkdata" , $trademarkdata);
        if($get["down"] == "downpdf"){
            $downpdfhtml = $this -> fetch();
            $Publicel -> downpdf("trademarkbook" ,$downpdfhtml);   
        }else{
            $this -> display();
        }
    }
}