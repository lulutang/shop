<?php
/**
 * 优惠订单管理
 * @author tll
 */
namespace Admin\Controller;
use Page\Page;
use Think\Controller;
use Admin\Model\OnSaleModel;

include (COMMON_PATH . "/Class/Page.class.php");

class OnsaleController extends Controller {
   public function _initialize() {
		$uid = session("userid");
		if( empty( $uid ) )
		{
            echo '<script>top.location.href="/Admin/Login/index";</script>';
		}
	} 
    /**
     * 活动列表页
     */
    public function lists() {
        $conn = new OnSaleModel();
        $lists = $conn -> getList();
        $this -> assign('data',$lists);
        $this -> display();
    }
	
	/**
	 * 添加活动
	 */
	public function addOnSale() {
		$info = I ( 'get.' );
		$model = M ( "Onsale" );
		$info ['state'] = trim ( $_POST ['desc'] );
		$info ['startTime'] = strtotime($info ['startTime']);
		$info ['endTime'] = strtotime($info ['endTime']);
		$info ['sale_startTime'] = strtotime($info ['sale_startTime']);
		$info ['sale_endTime'] = strtotime($info ['sale_endTime']);
		$info ['createTime'] = time ();
		$result = $model->add ( $info );
		$this->redirect ( 'Admin/OnSale/lists' );

    }
    
    /**
     * 删除
     */
    public function delOnsale(){
        //确认有id
        $id = intval($_REQUEST['id']); 
         
        if($id){
            $OO = M("Onsale"); 
            $result = $OO ->where('id = '.$id) ->delete();
           if( $result ){          
             echo 1;
             exit();
            }  
        }
        echo 0;
        exit();
        
    }
    
    /**
     * 根据ajax传递过来的id 查询整体信息
     */
    public function getOnsale() {
        
        $id = $_REQUEST['id']; 
        $OO = M("Onsale"); 
        $result = $OO -> where('id='.$id) -> find();
        echo json_encode( $result );
         
    }
    /**
     * 编辑订单
     */
    public function editOnsale() {
        
    	$info = I ( 'get.' );
    	$model = M ( "Onsale" );
    	$id = $info['edit_id'];
    	if($info && $id){
    		unset($info['edit_id']);
    		$info ['startTime'] = strtotime($info ['startTime']);
    		$info ['endTime'] = strtotime($info ['endTime']);
    		$info ['sale_startTime'] = strtotime($info ['sale_startTime']);
    		$info ['sale_endTime'] = strtotime($info ['sale_endTime']);
    		$result = $model -> where('id='.$id)-> save($info);
    	}    
    	$this->redirect ( 'Admin/OnSale/lists' );
    }

    public function userOnsale() {
    	$get = I ( 'get.' );
    	$page = isset ( $get ['p'] ) ? intval ( $get ['p'] ) : 1;
    	$pageSize = isset ( $get ['pageSize'] ) ? intval ( $get ['pageSize'] ) : 10;
    	$m = new OnSaleModel();
    	$where = array();
    	$keywords = $get['keywords'];
    	if($keywords) {
    		$where ['user_name'] = array ('like','%' . $keywords);
    	}
    	
    	$result = $m -> getUserOnsaleList($where, $page, $pageSize);
    	
    	$pageModel = new Page ( $result ['count'] );
    	$pages = $pageModel->show ();
    	$this->assign ( 'pages', $pages );
    	$this->assign ( 'keywords', $keywords );
    	$this->assign ( 'info', $result['info'] );
    	
    	$this->display ( 'userOnsale' );
    	 
    }
   
    public function getUserTop(){
    	$get = I ( 'get.' );
    	$uid = isset ( $get ['uid'] ) ? intval ( $get ['uid'] ) : null;
    	
    	if(!$uid){
    		return false;
    	}
    	$m = new OnSaleModel();
    	$where =array('uid' => $uid) ;
    	$result = $m -> getOnsaleUserInfo($where);
    	
    	echo json_encode($result);
    }
}