<?php
/**
 * 后台栏目文章
 * author  author
 */
namespace Admin\Controller;
use Admin\Model\ArticleModel;
use Think\Controller;
use Page\Page;
include(COMMON_PATH."Class/Page.class.php");
header("content-type:text/html;charset=utf8");
class ArticleController extends Controller{
	public function _initialize() {
		header("content-type:text/html;charset=utf8");
		$uid = session("userid");
		if(empty($uid))
		{
                    echo '<script>top.location.href="/Admin/Login/index";</script>';
			//$this -> success('请登录',"/Admin/Login/index");die;
		}
	}
        /**
        * 显示栏目列表
        */
	public function columnshow(){
		$_article = new ArticleModel();
		$result = $_article -> getColumn();
		$columninfo = ($result == FALSE) ? FALSE : $result;
		//dump($columninfo);die;
		$this->assign('columninfo',$columninfo);
		$this->display();
	}
        /**
        * ajax动态修改栏目序列
        */
	public function editorderid(){
		$order_id = $_POST['orderid'];
		$id = $_POST['id'];
		$_article = new ArticleModel();
		$result = $_article -> editOrderid($order_id,$id);
		echo ($result == FALSE) ? 0 : $result;
	}
        /**
        * 添加栏目
        */
	public function saveorder(){
		if(IS_POST){
			$data = I('post.');
			$_article = new ArticleModel();
			$result = $_article -> addOrder($data);
			if($result == TRUE){
				header("location:columnshow");
			}else{
				header("location:columnshow");
			}
		}
	}
        /**
        * 获取要修改的数据
        */
	public function editcolumn(){
		$id = $_POST['id'];
		$_article = new ArticleModel();
		$result = $_article -> getOneColumn($id);
		echo json_encode($result);
	}
	/**
	 * 修改栏目信息
	 */
	public function editcolumninfo(){
		if(IS_POST){
			$data = I('post.');
			$_article = new ArticleModel();
			$result = $_article -> editColumnInfo($data);
			if($result == TRUE){
				header("location:columnshow");
			}else{
				header("location:columnshow");
			}
		}
	}
	/**
	 * 删除栏目
	 */
	public function delcolumns(){
		$id = $_POST['id'];
		$_article = new ArticleModel();
		$result = $_article -> delColumns($id);
		echo ($result == FALSE) ? 0 : 1;
	}
	/**
	 * 添加文章
	 */
	public function savearticle(){
		if(IF_POST){
			$data = I('post.');
                        $data['content'] = $_POST['content'];
			$_article = new ArticleModel();
			$result = $_article -> SaveArticle($data);
			if($result == TRUE){
				header("location:articleshow");
			}else{
				header("location:articleshow");
			}
		}
	}
	/**
	 * 显示文章列表
	 */
	public function articleshow(){
		//拼接where
		$where ='1=1';
		if(!empty($_GET['cateid'])){
			$where.=" and cateid=".$_GET['cateid']."";
		}
		if(!empty($_GET['keywords'])){
			$where.=" and title like '%".$_GET['keywords']."%' or content like '%".$_GET['keywords']."%'";
		}
		$_article = new ArticleModel();
		$Count = $_article -> getCount($where);
		$page_count = 10; //每页显示条数
		$Page = new Page($Count, $page_count);// 实例化分页类 传入总记录数
		$map['cateid'] = @$_GET['cateid']; //回扣值
		$map['keywords'] = @$_GET['keywords'];
		foreach($map as $key=>$val) {   
			$p->parameter .= "$key=".urlencode($val)."&";   
		}
		$Pagesize =$Page ->show(); //得到分页模板
		$articleinfo = $_article -> getArticle($Page->firstRow , $Page->listRows , $where);
		$result = $_article -> getColumn();
		$columninfo = ($result == FALSE) ? FALSE : $result;
		$this->assign('map',$map);
		$this->assign('p',trim(I("p")));
		$this->assign('columninfo',$columninfo);
		$this->assign('page' , $Pagesize);
		$this->assign('articleinfo',$articleinfo);
		$this->display();
	}
	/**
	 * 动态修改文章排序id
	 */
	public function editarticleorderid(){
		$order_id = $_POST['orderid'];
		$id = $_POST['id'];
		$_article = new ArticleModel();
		$result = $_article -> editArticleOrderid($order_id,$id);
		echo ($result == FALSE) ? 0 : $result;
	}
	/**
	 * 删除文章
	 */
	public function delarticle(){
		$id = $_POST['id'];
		$_article = new ArticleModel();
		$result = $_article -> delArticle($id);
		echo ($result == FALSE) ? 0 : 1;
	}
	/**
	 * 获取要修改文章的信息
	 */
	public function editarticle(){
		$id = $_POST['id'];
		$_article = new ArticleModel();
		$result = $_article -> getOneArticle($id);
		echo json_encode($result);
	}
	/**
	 * 修改文章完成
	 */
	public function editsavearticle(){
		if(IS_POST){
			$data = I('post.');
                        $data['contents'] = $_POST['contents'];
                        //dump($data);die;
			$_article = new ArticleModel();
			$result = $_article -> editArticleInfo($data);
			if($result == TRUE){
				header("location:articleshow");
			}else{
				header("location:articleshow");
			}
		}
	}
        
        
        
}