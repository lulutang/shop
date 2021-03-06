<?php
/**
 * 查询统计控制类
 *
 * @author tangll
 *
 */
namespace Admin\Controller;
use Think\Controller;
use Admin\Model\CensusModel;
use Page\Page;
include (COMMON_PATH . "/Class/Page.class.php");
class CensusController extends Controller {
    /**
     * 初始化
     */
	public function _initialize() {
		header ( "content-type:text/html;charset=utf8" );
		$uid = session ( "userid" );
		if (empty ( $uid )) {
            echo '<script>top.location.href="/Admin/Login/index";</script>';
		}
	}
	
	/**
	 * 获取点击查询信息
	 * @param int p 第几页分页
	 * @param int pageSize 分页数据多少条
	 * @param int sort 排序
	 */
	public function lists() {
		$get = I ( 'get.' );
		
		$page = isset ( $get ['p'] ) ? intval ( $get ['p'] ) : 1;
		$pageSize = isset ( $get ['size'] ) ? intval ( $get ['size'] ) : 10;
		$buy_timeb = str_replace('+', ' ', $get ['buy_timeb']) ;
		$buy_timeend = str_replace('+', ' ', $get ['buy_timeend']);
		$model = new CensusModel ();
		$where = array();

		if (! empty ( $buy_timeb ) && ! empty ( $buy_timeend )) {
			$where ['add_time'] = array (
					'between',
					strtotime ( $buy_timeb ) . ',' . strtotime ( $buy_timeend )
			);
		} else if (! empty ( $buy_timeb )) {
			$where ['add_time'] = array (
					'egt',
					strtotime ( $buy_timeb )
			);
		} else if (! empty ( $buy_timeend ))
			$where ['add_time'] = array (
					'elt',
					strtotime ( $buy_timeend )
			);
		$info = $model->getList ( $page, $pageSize, $where);
		
		$pageModel = new Page ( $info ['count'], $pageSize );
		$pages = $pageModel->show ();
	
		$this->assign ( 'lists', $info ['lists'] );
		$this->assign ( 'count', $info ['count'] );
		$this->assign ( 'pages', $pages );
		$this->assign ( 'buy_timeb', $buy_timeb );
		$this->assign ( 'buy_timeend', $buy_timeend );
		$this->display ();
	}
	
	/**
	 * 搜索
	 * @param string ywlx 业务类型
	 * @param string key 搜索关键字 用户昵称  用户联系方式 商品编码 商品简称 
	 * @param string keywords 搜索关键词
	 * @param string buy_timeb 开始时间
	 * @param string buy_timeend 结束时间
	 * @param int p 第几页分页
	 * @param int pageSize 分页数据多少条
	 * @param int sort 排序
	 */
	public function search() {
		$post = I ( 'get.' );
		$ywlx = $post ['ywlx'];
		$key = $post ['key'];
		$keywords = $post ['keywords'];
		$buy_timeb = str_replace('+', ' ', $post ['buy_timeb']) ;
		$buy_timeend = str_replace('+', ' ', $post ['buy_timeend']);
		$pageSize = isset ( $post ['pageSize'] ) ? intval ( $post ['pageSize'] ) : 10;
		$sort = isset ( $post ['sort'] ) ? intval ( $post ['sort'] ) : 1;
		$page = isset ( $post ['p'] ) ? intval ( $post ['p'] ) : 1;
		
		$model = new CartModel ();
		$where = array ();
		
		if ($ywlx && $ywlx != '请选择') {
			if($ywlx =='组合商品'){
			
				
				$where ['type'] = 0;
			}else{
				$tijiaoYewu = explode ( '_', $ywlx );
				$oneArr = reset ( $tijiaoYewu );
				$ywlx = end ( $tijiaoYewu );
				$where ['type'] = $oneArr;
			}
		
		}
		if ($key && $key != '关键词'){
			
			if (! empty ( $keywords ) && $keywords != '请输入用户昵称、用户联系方式、商品编码、商品简称') {
				switch ($key) {
					case '用户昵称':
						$where ['userName'] = $keywords;
					break;
					case '用户联系方式':
						$where ['phone'] = $keywords;
					;
					break;
					case '商品编码':
						$where ['code'] = $keywords;
					;
					break;
					case '商品简称':
						$where ['short_title'] = array (
								'like',
								'%' . $keywords . '%'
						);
					;
					break;
				}
				
			}
		}else{
			if (! empty ( $keywords ) && $keywords != '请输入内容进行模糊搜索') {
				
				$where['userName'] = array ('like','%' . $keywords . '%');
				$where['phone'] = array ('like','%' . $keywords . '%');;
				$where['code'] = array ('like','%' . $keywords . '%');;
				$where['short_title'] = array ('like','%' . $keywords . '%');;
				$where['_logic'] = 'OR';
				
			
			}
		}
			
		if (! empty ( $buy_timeb ) && ! empty ( $buy_timeend )) {
			$where ['addtime'] = array (
					'between',
					strtotime ( $buy_timeb ) . ',' . strtotime ( $buy_timeend ) 
			);
		} else if (! empty ( $buy_timeb )) {
			$where ['addtime'] = array (
					'egt',
					strtotime ( $buy_timeb ) 
			);
		} else if (! empty ( $buy_timeend ))
			$where ['addtime'] = array (
					'elt',
					strtotime ( $buy_timeend ) 
			);
		$cartInfo = $model->getCartInfo ( $page, $pageSize, $where, $sort);
	
		$pageModel = new Page ( $cartInfo ['count'] );
		$pages = $pageModel->show ();
		$userModel = new UserOrderModel ();
		$yewu = $userModel->getYewu ( 0 );
		$this->assign ( 'yewu', $yewu );
		$this->assign ( 'key1', $key );
		$this->assign ( 'ywlx', $ywlx );
		$this->assign ( 'keywords', $keywords );
		$this->assign ( 'buy_timeb', $buy_timeb );
		$this->assign ( 'buy_timeend', $buy_timeend );
		$this->assign ( 'dayCount', $cartInfo ['dayCount'] );
		$this->assign ( 'carts', $cartInfo ['carts'] );
		$this->assign ( 'pages', $pages );
		$this->assign ( 'count', $cartInfo ['count'] );
		$this->assign ( 'sort', $sort );
		$this->assign ( 'money', $cartInfo ['money'] );
		$this->display ( 'cart' );
	}

	
}