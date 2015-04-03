<?php
/**
 * 商城首页
 * @author xxq
 */
namespace Home\Controller;

use Home\Model\GoodsModel;
use Home\Model\ServiceModel;
use Home\Model\PackageModel;
use Think\Controller;
use Home\Controller\LoginController;

class IndexController extends Controller {
    
    const  SBNUM = 12908;
    const  ZLNUM = 808;
    const  BQNUM = 7342;
    /**
     * 首页展示
     */
    public function index() {
        // 限时抢购内容
        $panicbuy = $this -> getPanicBuy();
        // 获取一级业务类型
        $onetype = $this -> getFirstType();
       
        // 根据一级业务取出相应的二级业务和首页推荐的产品
        $user = new LoginController();
        $userinfo = $user -> login();
        foreach ( $onetype as $val ) {
            $goodsdata[] = $this -> getIndexTrademark( $val ['id'], $val ['server_name'] ); // 商标服务
        }

        $this -> assign( 'SBnum', self::SBNUM );
        $this -> assign( 'ZLnum', self::ZLNUM );
        $this -> assign( 'BQnum', self::BQNUM );
        $this -> assign( 'panicbuy', $panicbuy );
        $this -> assign( 'list', C('SELF_STYLE'));
        $this -> assign( 'goodsdata', $goodsdata );

        $this -> display ();
    }

    /**
     * 获取限时抢购内容
     * @return array
     */
    private function getPanicBuy() {
        $package = new PackageModel();

        $data = $package -> getPanicBuy();
        return $data;
    }

    /**
     * 获取一级业务类型
     * @return array
     */
    private function getFirstType() {
        $types = new ServiceModel();

        $data = $types -> getFirstType();
        return $data;
    }

    /**
     * 获取首页商标服务
     * @param mediumint $pid  一级业务id
     * @param varchar $pname 业务名称
     * @return array
     */
    private function getIndexTrademark($pid, $pname) {
        $goods = new GoodsModel();

        $data = $goods -> getIndexTrademark( $pid, $pname );
        return $data;
    }

}