<?php
/**
 * 后台统计搜索信息Model
 *
 * @author tangll
 *
 */
namespace Admin\Model;
use Think\Model;

class CensusModel extends Model {
	/**
	 * 获取统计搜索信息
	 * @param int $page
	 * @param int $pageSize
	 * @param string $where
	 * @return array
	 */
	public function getList($page, $pageSize, $where) {
		$result = M ( 'Census' ); 

		if ($where) {
			$info = $result->where ( $where )->order ( 'add_time desc' )->page ( $page, $pageSize )->select (); // 获取购物车商品  // echo $result -> getLastSql();
			$count = $result->where ( $where )->count ();
		} else {
			$info = $result->order ( $sort )->order ( 'add_time desc' )->page ( $page, $pageSize )->select (); // 获取购物车商品
			$count = $result->count ();
		}

		return array (
				'count' => $count,
				'lists' => $info ,
		);
	}
	
	
}