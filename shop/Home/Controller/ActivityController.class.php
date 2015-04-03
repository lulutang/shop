<?php
/**
 * 促销活动
 * @author lijiandong
 *
 */

namespace Home\Controller;
use Think\Controller;
use Home\Model\ActivityModel;

class ActivityController extends Controller {
    /**
     * 促销活动
     * @param int id 活动id
     */
    public function activitydesition(){

        //实例化促销活动模型
        $activity = new ActivityModel();
        
        $getactivityid = $activity -> getActivityId();
        $activityid = $getactivityid["data"]["act_id"];
        $whereone = "Act_id=$activityid";
        //获取单条活动数据
        $getone = $activity -> getFindActivity($whereone);
        if(empty($activityid) || empty($getone)){
            $this->assign ( 'message', '该活动不存在' );
            $this->display('Public/error');
            exit();
        }
        $nowtime = time();
        $flag = array(0,1);
        if($getactivityid["time"]["start"] > $nowtime || $getactivityid["time"]["end"] < $nowtime)
        {
            array_splice($flag,0,1); 
        }
        //获取时间轴状态
        $dataTime = $activity -> getTime($nowtime);
        //获取多条活动数据
        $whereall = "1=1";
        $getMore = $activity -> getAllActivity($whereall);
        $this -> assign("flag",$flag);
        $this -> assign("getone",$getone);
        $this -> assign("datatime",$dataTime);
        $this -> display();  
    }
}

