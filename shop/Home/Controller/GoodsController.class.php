<?php
/**
 * 商品处理Controller
 * @author 李建栋
 *
 */
namespace Home\Controller;

use Home\Model\GoodsModel;
use Home\Model\ServiceModel;
use Think\Controller;
use Home\Model\ActivityModel;

class GoodsController extends Controller{
    public function _initialize()
    {
        $this->realm = $_SERVER['HTTP_HOST'];
        header("content-type:text/html;charset=utf8");
    }
    /**
     * 商品详情
     * @param  int id 商品Id
     */
    public function gooddetails(){
    
        $goods_id = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";
        if(!is_numeric($goods_id)){
            $this->assign ( 'message', '非法参数' );
            $this->display('Public/error');
            exit();
        }
        $gs = new GoodsModel();
        //$ob = $gs -> CheckGoodsStatus($goods_id);
        $res = $gs -> getServceType($goods_id);
        $qui = $gs -> findAtrValbygods($goods_id);

        $info = ($res == FALSE) ? FALSE : $res;
        if($info==FALSE){
            $this->assign ( 'message', '该商品不存在' );
            $this->display('Public/error');
            exit();
        }
        if($res[0]['status'] != 1){
            $res = $gs -> getServceType($goods_id);
            $qui = $gs -> findAtrValbygods($goods_id);
            $info = ($res == FALSE) ? FALSE : $res;
            $this->assign('info',$info);
            $this->display('soldout');
            exit();
        }
        $resu=$gs -> findAtrbyServer($info[0]['s_id']);
        if($resu == FALSE || $qui == FALSE){
            $p=1;
            $goodsinfo = $info;
        }else{
            $p=0;
            $resul = $gs -> findAtrValbyAtr($resu);
            $result = $gs -> findGodsbyAtr($resul);
            $goodsinfo = ($result == FALSE) ? $info : $result;
            if(count($goodsinfo['attr'])<=2){
                $p=1;
                $goodsinfo = $info;
            }
        }
        
        $actModel = new ActivityModel();
        $actRes = $actModel ->getActInfoByGoodsId($goods_id);
	    if($actRes){
	    	$this->assign('hasAct',$actRes);
	    }
	   
        $this->assign('info',$info);
        $this->assign('is_attr',$p);
        $this->assign('goodsinfo',$goodsinfo);
        //套餐
        $package=$gs->getPackageByGods($goods_id);
        $this->assign('p_num',count($package));
        $this->assign('package',$package);
        $this->display();
    }

    /**
     * 前台商品列表
     * @param int id 顶级业务ID
     */
    function goods_list(){
        $id = I("id");
        if(!is_numeric($id)){
            $this->assign ( 'message', '非法参数' );
            $this->display('Public/error');
            exit();
        }
        $common      = array("38"=>"商标服务" , "5" => "专利服务" , "6" => "版权服务");
        $service_arr = new ServiceModel();
        $arr_data    = $service_arr->Getdata($id); 
        if(empty($arr_data)){
            $this->assign ( 'message', '该商品列表不存在' );
            $this->display('Public/error');
            exit();
        }
        $arr_id      = $service_arr->Getid(0,'',$id); 
        $goods_arr   = new GoodsModel();
        $goods_list  = $goods_arr->goods_data($arr_data,0); 
        $deploy      = array("lim" => $goods_list['lim'] , "pro_count" => $arr_id['pro_count'] , "server_parentid" => $id);
        $this -> assign("arr_id",$arr_id['goods']);
        $this -> assign("common" , $common);
        $this -> assign($deploy);
        $this -> assign("goods_list" , $goods_list['list']);
        $this -> display("main-trademark");
    }

    /**
     * 更多商品
     * @param int $server_id 二级业务ID
     */
    function ajax_goods() {
        $server_id = trim ( I ( 'server_id' ) );
        if(!is_numeric($server_id)){
            $this->assign ( 'message', '非法参数' );
            $this->display('Public/error');
            exit();
        }
        $goods_arr = new GoodsModel ();
        $goods_list = $goods_arr->goods_ul ( $server_id ); 
        $this->assign ( "goods_list", $goods_list );
        $this->display ( "ajax_goods" );
    }
}