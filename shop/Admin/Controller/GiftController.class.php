<?php
/**
 * 后台赠品管理
 *
 * @author tangll
 *
 */
namespace Admin\Controller;
use Think\Controller;
use Page\Page;
use Admin\Model\GiftModel;
include (COMMON_PATH . "/Class/Page.class.php");
class GiftController extends Controller {
    /**
     * 初始化
     */
	public function _initialize() {
		header ( "content-type:text/html;charset=utf8" );
		$uid = session ( "userid" );
		if (empty ( $uid )) {
            echo '<script>top.location.href="/Admin/Login/index";</script>';
            exit();
		}
	}
	/**
	 * 赠品列表
	 */
	public function lists() {
		$get = I ( 'get.' );
		$page = isset ( $get ['p'] ) ? intval ( $get ['p'] ) : 1;
		$pageSize = isset ( $get ['pageSize'] ) ? intval ( $get ['pageSize'] ) : 10;
		$sort = isset ( $post ['sort'] ) ? intval ( $post ['sort'] ) : 1;
		$model = new GiftModel();
	    $lists = $model->getGiftList ($page, $pageSize, '', $sort);
	    
	    $pageModel = new Page ( $lists ['count'] );
	    $pages = $pageModel->show ();
	    $this->assign ('lists', $lists['info']);
	    $this->assign('goods_number', $lists['count']);
	    $this->assign('dayCount', $lists['dayCount']>0?$lists['dayCount']:0);
	    $this->assign('pages', $pages);
	    $this->assign('sort', $sort);
	     
		$this->display();
	}
	
	/**
	 * 赠品搜索列表
	 */
	public function search() {
		$get = I ( 'get.' );
		$page = isset ( $get ['p'] ) ? intval ( $get ['p'] ) : 1;
		$pageSize = isset ( $get ['pageSize'] ) ? intval ( $get ['pageSize'] ) : 10;
		$sort = isset ( $get ['sort'] ) ? intval ( $get ['sort'] ) : 1;
		$key = $get ['key'];
		$keywords = $get ['keywords'];
		$buy_timeb = str_replace('+', ' ', $get ['buy_timeb']) ;
		$buy_timeend = str_replace('+', ' ', $get ['buy_timeend']);
		
		$model = new GiftModel();
		$lists = $model->getGiftList ($page, $pageSize, $get, $sort);
		 
		$pageModel = new Page ( $lists ['count'] );
		$pages = $pageModel->show ();
		$this->assign ('lists', $lists['info']);
		$this->assign('goods_number', $lists['count']);
		$this->assign('dayCount', $lists['dayCount']>0?$lists['dayCount']:0);
		$this->assign('pages', $pages);
		$this->assign('key1', $key);
		$this->assign('keywords', $keywords);
		$this->assign('buy_timeb', $buy_timeb);
		$this->assign('buy_timeend', $buy_timeend);
		$this->assign('sort', $sort);
		
		$this->display('lists');
	}
}