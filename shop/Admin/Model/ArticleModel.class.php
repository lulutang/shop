<?php
/**
 * 后台栏目文章
 * author  author
 */
namespace Admin\Model;
use Think\Model;
class ArticleModel extends Model{
	
        /**
        * 获取栏目列表
        * @return array
        */
	public function getColumn(){
		$data = M('category')->order("orderid")->select();
		foreach ($data as $k => $v) {
			$data[$k]['a_num'] = $this->where("cateid= ".$v['cateid']."")->count();
		}
		if(!empty($data)){
			return $data;
		}
		return FALSE;
	}
        /**
        * 修改栏目序列
        * @param  int $order_id 序列值
        * @param  int $id 栏目id
        * @return string
        */
	public function editOrderid($order_id,$id){
		$data['orderid'] = $order_id;
		$result = M('category')->where("cateid = ".$id."")->save($data);
		if($result > 0){
			return $order_id;
		}
		return FALSE;
	}
        /**
        * 添加栏目序列
        * @param  int $order_id 序列值
        * @param  int $id 栏目id
        * @return string
        */
	public function addOrder($data){
		$result = M('category')->add($data);
		if($result > 0){
			return TRUE;
		}
		return FALSE;
	}
	
        /**
        * 获取栏目序列
        * @param  string $id 栏目id
        * @return array or string
        */
	public function getOneColumn($id){
		$data = M('category')->where("cateid = ".$id."")->find();
		if(!empty($data)){
			return $data;
		}
		return FALSE;
	}
        /**
        * 修改栏目
        * @param  array $data 栏目信息
        * @return string
        */
	public function editColumnInfo($data){
		$datas['catename'] = $data['catename'];
		$datas['catedesc'] = $data['catedesc'];
		$result = M('category')->where("cateid = ".$data['cateid']."")->save($datas);
		if($result > 0){
			return TRUE;
		}
		return FALSE;
	}
        /**
        * 删除栏目信息 
        * @param  int $id 栏目ID
        * @return string
        */
	public function delColumns($id){
		$result = M('category')->where("cateid = ".$id."")->delete();
		if($result > 0){
			return TRUE;
		}
		return FALSE;
	}
        /**
        * 保存文章信息 
        * @param  array $data 文章信息
        * @return string
        */
	public function SaveArticle($data){
		$data['addtime'] = time();
		$arr = M('category')->field("catename")->where("cateid = ".$data['cateid']."")->find();
		$data['author'] = session("truename");
		$data['catename'] = $arr['catename'];
		$result = $this->add($data);
		if($result > 0){
			return TRUE;
		}
		return FALSE;
	}
       /**
        * 获取文章信息
        * @param  int $start [分页开始值]
        * @param  int $end [分页结束值]
        * @param  string $where    [查询条件]
        */
	public function getArticle($start,$end,$where){
		$data = $this->where($where)->order("orderid")->limit($start,$end)->select();
		if(!empty($data)){
			return $data;
		}
		return FALSE;
	}
        /**
        * 获取文章数量
        * @param  string $where    查询条件
        */
	public function getCount($where){
		$data = $this->where($where)->count();
		//var_dump($data);die;
		if(!empty($data)){
			return $data;
		}
		return FALSE;
	}
        /**
        * 修改文章序列
        * @param  int $order_id 序列值
        * @param  int $id 文章id
        * @return string
        */
	public function editArticleOrderid($order_id,$id){
		$data['orderid'] = $order_id;
		$result = $this->where("art_id = ".$id."")->save($data);
                
		if($result > 0){
			return $order_id;
		}
		return FALSE;
	}
        /**
        * 删除文章信息
        * @param  int $id 文章id
        * @return string
        */
	public function delArticle($id){
		$result = $this->where("art_id = ".$id."")->delete();
		if($result > 0){
			return TRUE;
		}
		return FALSE;
	}
        /**
        * 得到一条文章信息
        * @param  int $id 文章id
        * @return array or string
        */
	public function getOneArticle($id){
		$data = $this->where("art_id = ".$id."")->find();
		if(!empty($data)){
			return $data;
		}
		return FALSE;
	}
        /**
        * 修改文章信息
        * @param  array $data 文章信息
        * @return string
        */
	public function editArticleInfo($data){
		$datas['cateid'] = $data['cateid'];
		$datas['short_title'] = $data['short_title'];
		$datas['title'] = $data['title'];
		$datas['author'] = session("username");
		$datas['is_show'] = $data['is_show'];
		$datas['content'] = $data['contents'];
      $arr = M('category')->field("catename")->where("cateid = ".$data['cateid']."")->find();
		$datas['catename'] = $arr['catename'];    
		$result = $this->where("art_id = ".$data['art_id']."")->save($datas);
		if($result > 0){
			return TRUE;
		}
		return FALSE;
	}
}