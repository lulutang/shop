<?php
/**
 * 前台帮助管理
 * author  author
 */
namespace Home\Controller;
use Think\Controller;
class ArticleController extends Controller{
        /**
        * 前台帮助管理
        */
	public function detialarticle(){
            $art = M('category');
            $art_id = isset($_GET['id']) ? $_GET['id'] : 68;
            if(!is_numeric($art_id)){
                $this->assign ( 'message', '非法参数' );
                $this->display('Public/error');
                exit();
            }
            $article = M('article')->where("art_id = ".$art_id."")->find();
            $data = $art->order("orderid")->select();
            foreach ($data as $key => $val) {
                $str .= $val['cateid'].",";
            }
            $str = substr($str,0,-1);
            $arr= $this->getarticle($str);
            $str = explode(',', $str);
            foreach($arr as $k => $v){
                if(in_array($v['cateid'], $str)){
                    $list[$v['cateid']]['catename'] = $v['catename'];
                    $list[$v['cateid']]['article'][] = $v;
                }
            }
            $this->assign('data',$list);
            $this->assign('article',$article);
            $this->display();
	}
        private function getarticle($str){
            $art = M('article');
            return $arr = $art->where("cateid in(".$str.")")->order("orderid")->select();
        } 
}	