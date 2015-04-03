<?php
/**
 * 业务类型管理
 * @author xxq
 */
namespace Admin\Controller;
use Think\Controller;
use Admin\Model\ServiceModel;
use Admin\Model\Server_attrModel;
use Admin\Model\Attr_valueModel;
use Admin\Model\GoodsModel;

class ServerController extends Controller {
    
    public function _initialize() {
        header("content-type:text/html;charset=utf8");
        $this->realm = $_SERVER['HTTP_HOST'];
        $uid = session("userid");
        if(empty($uid))
        {
            echo '<script>top.location.href="/Admin/Login/index";</script>';
        }
    }
     
    /**
     * 业务类型管理
     */
    public function serverList(){
       
        $conn = new ServiceModel();
        $lists = $conn -> getService();

        $this -> assign('data',$lists);
        
        $this->display('Server/serverList');
    }
    
    /**
     * 添加一级业务 或二级
     */
    public function addOne(){
       
        $parent_id = isset($_REQUEST['parent_id']) ? $_REQUEST['parent_id'] : '0'; //默认一级  确认父id
      
        $id = $_REQUEST['id'];
           
        $SS = M("Service"); // 实例化对象
        //验证信息
        $rules = array(
          array('server_name','require','业务类型名称不能为空！')     
        );
        
        if (!$SS->validate($rules)->create()){//错误提示
            
            exit($SS->getError().' [ <A HREF="javascript:history.back()">返 回</A> ]');
            
        }else{
            
            $data['parent_id'] = $parent_id;
            $data['server_name'] = trim($_POST['server_name']);
            $data['description'] = trim($_POST['description']);
            $data['addtime'] = time();
            //执行编辑更新操作
            if( $id ){ 
                $Goods= new GoodsModel();
                $Goods_data["now_servername"] = trim( $_POST['server_name'] );
                $Goods -> where("s_id =".$id) -> save($Goods_data);
                $result = $SS -> where('id='.$id) -> save($data);   
            }else{
                $result = $SS -> data($data) -> add(); 
            }
           
            if($result){   
                
               $this -> redirect('Admin/Server/serverlist');
            }      
        }  
    }
    
    /**
     * 根据ajax传递过来的id 查询整体信息
     */
    public function getOneInfo() {
        
        $id = $_REQUEST['id']; 
        $type = $_REQUEST['type']; 
        
        if( $id && ($type==='showEdit') ){
            // 实例化对象
            $SS = M("Service");  
            $result = $SS -> where('id='.$id) -> find();
            echo json_encode( $result );exit();
        }
        
    }

    /**
     * 删除一级业务
     */
    public function delserver(){
        
        $id = $_REQUEST['id']; //确认有id
         
        if( $id ){

            $server = new ServiceModel();
           
            //判断二级业务 无：删除
            $ids = $server->Getdata($id);
            if( $ids ) { exit('此业务含有子级业务，不能删除！ [ <A HREF="javascript:history.back()">返 回</A> ]');}
            //判断二级下有无商品 无：删除 有：提示
            $re = $this -> checkGoods( $ids );
            
            if( $re ) { exit('此业务下子级业务含有商品，不能删除！ [ <A HREF="javascript:history.back()">返 回</A> ]');}
            
            //删除业务
            $result = $server -> delete( $id );
            
            if( $result ){
                
               $this -> redirect('Admin/Server/serverlist');
               
            }  
        }else{
            header("Content-Type:text/html; charset=utf-8");
            exit( $server -> getError().' [ <A HREF="javascript:history.back()">返 回</A> ]');
        }
    }

    
    /**
     * 判断有无商品 无：删除 有：提示
     * $attr_id：二级业务id
     */
    private function checkGoods($attr_id) {
        $GG = new GoodsModel();
        $res = $GG -> checkIsHaveGood($attr_id);
        
        return $res;
    }
    
    /**
     * 获取业务属性名称列表
     */
    public function getAttrList() {
        
        $id = $_REQUEST['id']; 
        $type = $_REQUEST['type']; 

        if( $id && ($type==='showAttr') ){
           
            $AA = new Server_attrModel();  // 实例化对象
            $result = $AA->getIdAttr($id);
           
            $VV = M('Attr_value');
            
            foreach( $result['data'] as $k=>$val){
                $values= $VV -> where('attr_id='.$val['id']) -> select();  //根据属性类型id取具体的属性值
                $a_v = '';
                foreach ($values as $key => $value) {
                    $a_v .= $value['value'].',';
                }
                $result['data'][$k]['vals'] = trim($a_v,',');
            }
           if($result['data']){ echo json_encode($result); exit();}
        }
        
    }
    
    /**
     * 接收异步传送数据添加属性和属性值
     */
    public function addattrvalue(){
         
       if( $_REQUEST['type'] == 'addDatAattr' ){ 
            $AA = new Server_attrModel(); // 实例化对象
            
            if( $_REQUEST['id'] ) $data['id'] = trim($_REQUEST['id']);
            
            if( $_REQUEST['s_id'] ) $data['s_id'] = trim($_REQUEST['s_id']);
            $data['attr_id'] = trim($_REQUEST['attr_id']);
            $data['attr_name'] = trim($_REQUEST['attr_name']);
            $data['addtime'] = time();
            
            if( $data['id'] ){
                
                $AA->where('id='.$data['id'])->save($data);
                
                $insertId  =$data['id'];
            }else{
                $insertId  = $AA->data($data)->add(); // 写入数据到数据库 
            }
            $VV = M('Attr_value');
            
            //处理属性值
            $values = trim($_REQUEST['values'],',');
            $ids = trim($_REQUEST['aid'],',');
            if( $values ){
                
                $values = explode(',', $values);
                $idarr = explode(',', $ids);
                foreach ( $values as $k=>$v) {
                    $array['attr_id'] = $insertId;
                    $array['value'] = $v;
                    $array['id'] = ( $idarr[$k]!='T_values' ) ? $idarr[$k]: '';
                    //如果有执行更新 无 添加
                    if( $array['id'] ){ 
                        //根据属性值id查找goods_id  
                        $VV -> where('id='.$array['id']) -> save($array);    
                        $goods_attr = M("goods_attr");
                        $Goods = new GoodsModel();
                        $attr_data["attr_name"] = $v;
                        $value_arr = $goods_attr -> field("goods_id") -> where("value_id =".$array['id']) -> find();
                        $Goods -> where("goods_id =".$value_arr["goods_id"]) -> save($attr_data);
                    }else{  
                        $VV->data($array)->add();
                        
                    } 
                } 
            }
            echo $data['s_id'];exit;      
       }
    }

    /**
     * 编辑单条属性传递属性内容
     */
    public function getAttrOne(){
        
        if( $_REQUEST['type'] == 'editAttr' && !empty( $_REQUEST['id'] )){ 
            $id = $_REQUEST['id'];
            
            // 获取属性名称等信息
            $AA = new Server_attrModel();  
            $result = $AA -> getPrIdAttr($id);
            
            //获取属性下的属性值信息
            $res = $this -> getattrValue($id);
            if( $res ) $result['vals'] = $res;
            
            if($result) echo json_encode ($result);
        }
    }
    /**
     * 通过属性id 查询属性值
     * @param type $id  属性id
     * @return array
     */
    private function getattrValue($id){
        
        $VV = new Attr_valueModel();
        $result = $VV -> where('attr_id='.$id) -> select();
        return $result;
    }

   /**
    * 删除二级业务
    */
    public function delTwoserver(){
        //确认有id
        $id = $_REQUEST['id']; 

        if( $id ){
            //判断二级下有无商品 无：删除 有：提示
            $re = $this -> checkGoods( $id );
            
            if( $re ) { exit('此业务下含有商品，不能删除！ [ <A HREF="javascript:history.back()">返 回</A> ]');}
            
                    
            //先删除属性值  业务-》属性类型-》属性值
            //得到二级业务的属性类型
            $types = $this -> getTwotype($id);//server_attr
           
            if($types) $this -> delAttr($types);//attr_value  先删除属性值
             
             //删除二级业务下的属性
            $this -> delTwoAttr($id); //server_attr
       
             $server = new ServiceModel();

            //删除业务
            $result = $server -> delete( $id );
            if($result){
                $this -> redirect('Admin/Server/serverlist');
            }
        }
       
        
    }

    /**
     * 得到二级业务的属性类型
     * @param int $id  二级业务id
     * @return array
     */
    public function getTwotype($id) {
        if($id){
            $SS = new Server_attrModel();
            $res = $SS -> getTwotypes($id);
            return $res;
        }
        
    }
    
    /**
     * 删除二级业务的属性类型
     * @param int $id 业务id
     * @return array 
     */
    private function delTwoAttr($id) {
        if($id){
            $SS = new Server_attrModel();
            $SS -> delTwoAttr($id);   
        }
        
    }
    /**
     * 删除属性值
     * @param int $id  属性值id
     */
    private function delAttr( $id ){
        if($id){
            $SS = new Attr_valueModel();
            $SS -> delAttr($id);  
        }
       
    }
    
    /**
     * 删除类型下的属性
     */
    public function delTypeAttr(){
        if( $_REQUEST['type'] == 'delTypeAttr' && !empty( $_REQUEST['id'] )){ 
            $id = $_REQUEST['id'];
            //判断有无商品 有提示无法删除 
            $isHva = $this -> isHaveGoods($id);
           
            if( $isHva ){
               echo '99';exit;
            }
            //attr_value  先删除属性值
            $this -> delAttr($id);
             
             //删除属性
           $AA= M('Server_attr');
           $result = $AA -> delete( $id ); //server_attr
            
           if($result) echo '1';
        }
    }

   /**
    * 检查属性值下是否有商品
    * @param type $id
    * @return string
    */
    private function isHaveGoods($id) {
        
        if( $id ){
            
            $SS = new Attr_valueModel();
            //获取属性下的属性值
            $vids = $SS -> getvalId( $id );
           //根据属性值获取相应的已上架的数据
            $res = $SS -> getValIdattr($vids);
          
            if($res) return $res;
            else return '0';
        }
        
    }

    /**
     * 查找单条属性下是否有商品
     */
    public function IsattrGoods(){
        
        if( $_REQUEST['type'] == 'IsattrGoods' && !empty( $_REQUEST['id'] )){ 
            $id = $_REQUEST['id'];
            //判断有无商品 有提示无法删除 
            $SS = new Attr_valueModel();
           $res = $SS -> getValIdattr($id);
           
           if( $res ){
               echo '99';exit;
           }else{
               echo "0";exit();
           }
        }
    }
}