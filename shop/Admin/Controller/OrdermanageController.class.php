<?php
/**
 * 流程订单管理
 * @author xxq
 */
namespace Admin\Controller;

use Think\Controller;
use Admin\Model\OrdermanageModel;

class OrdermanageController extends Controller {

   public function _initialize() {
		header("content-type:text/html;charset=utf8");
		$this -> realm = $_SERVER['HTTP_HOST'];
		$uid = session("userid");
		if( empty( $uid ) )
		{
                    echo '<script>top.location.href="/Admin/Login/index";</script>';
		}
	} 
    /**
     * 流程订单列表页
     */
    public function lists(){
        
        $conn = new OrdermanageModel();
        $lists = $conn -> getList();
        
        $this -> assign('data',$lists);
        
        $this -> display();
    }
    
    /**
     * 添加流程
     */
    public function addOrder(){
        //默认一级  确认父id
        $id = isset($_REQUEST['o_id']) ? $_REQUEST['o_id'] : ''; 

        $OO = M("Ordermanage"); 
        //验证信息
        $rules = array(
            array('orderstatus','require','订单流程名称不能为空！')     
        );
        // 根据表单提交的POST数据创建数据对象
        if ( !$OO -> validate( $rules ) -> create() ){
           
            exit( $OO -> getError().' [ <A HREF="javascript:history.back()">返 回</A> ]');
        }else{
            $data['id'] = $id;
            $data['orderstatus'] = trim( $_POST['orderstatus'] );
            $data['desc'] = trim( $_POST['desc'] );
            $data['addtime'] = time();
            if( $id ){
                //更新
                $result = $OO -> where('id='.$id) -> save($data);   
            }else{
                $result = $OO -> data( $data ) ->add(); 
            }
            if( $result ){        
               $this -> redirect('Admin/Ordermanage/lists');
            }
        } 
    }
    /**
     * 删除订单
     */
    public function delOrder(){
        //确认有id
        $id = $_REQUEST['id']; 
         
        if( $id ){
            $OO = M("Ordermanage"); // 
            $result = $OO -> delete( $id );
           if( $result ){          
               $this -> redirect('Admin/Ordermanage/lists');
            }  
        }else{
            exit( $OO -> getError().' [ <A HREF="javascript:history.back()">返 回</A> ]');
        }
    }
    
    /**
     * 根据ajax传递过来的id 查询整体信息
     */
    public function getOneInfo() {
        
        $id = $_REQUEST['id']; 
        $type = $_REQUEST['type']; 
        
        if( $id && ( $type ==='showEdit') ){
            
            $OO = M("Ordermanage"); 
            $result = $OO -> where('id='.$id) -> find();
            echo json_encode( $result );
        }  
    }
    /**
     * 编辑订单
     */
    public function editorderid() {
        
        $id = $_REQUEST['id']; 
        $orderid = $_REQUEST['orderid']; 
        
        if( $id && $orderid ){
            
            $OO = M("Ordermanage"); 
            $data['id'] = $id;
            $data['orderid'] = $orderid;
          
            $result = $OO -> save($data); 
            if( $result ){
                echo $orderid; exit();
            }else{
                echo '0';exit();
            }
        }
        
    }


}