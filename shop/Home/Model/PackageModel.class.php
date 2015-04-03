<?php
namespace Home\Model;
use Think\Model;
/**
 * 优惠套餐model类
 * @author tangll
 *
 */
class PackageModel extends Model {
	
	protected $fields = array('package_id', 
							'package_code', 
							'short_title', 
							'title', 
							'description', 
							'now_price', 
							'old_price', 
							'starttime', 
							'endtime', 
							'thumb', 
							'coupon', 
							'ask', 
							'case', 
							'addtime', 
							'orderid', 
							'is_index',
							'_pk'=>'package_id'
			
						);
        
        
        /*
         * 获取限购商品
         * 排序 ：首页推广、排序、创建时间desc
         */
        
        public function getPanicBuy(){
            
            $time = time();
            $data['data'] = $this->where(' status=1 and is_index=1 ')->order(' orderid asc, addtime desc ')->limit(3)->select();

            //计算距离天数
            foreach( $data['data'] as $key=>$val){
                
                $timediff = $val['endtime'] - time();
                
                if( $timediff > 0 ){
                    $days = intval( $timediff / 86400 );
                    $remain = $timediff % 86400;
                    $hours = intval( $remain / 3600 );
                    $remain = $remain % 3600;
                    $mins = intval( $remain / 60 );
                    $secs = $remain % 60;
                    
                    $data['data'][$key]['day'] = $days;
                    $data['data'][$key]['hour'] = $hours;
                    $data['data'][$key]['min'] = $mins;
                    $data['data'][$key]['sec'] = $secs; 
                }
                
                if( $val['endtime']> $time ) $data['data'][$key]['zstatus']='1'; 
                else $data['data'][$key]['zstatus']='0'; 
            }
            return $data;
        }
}