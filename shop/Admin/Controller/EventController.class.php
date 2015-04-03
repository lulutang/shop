<?php
/**
 * event 事件入口文件
 * 上线后限制成只有本地可以访问的
 * tangll
 */
namespace Admin\Controller;
use Think\Controller;
use Admin\Model\EventModel;
use Admin\Model\EventTypes;
class EventController extends Controller{
	public function event(){
		$type = $_GET['type'];
		$event = new EventModel();
		switch ($type) {
			case EventTypes::EVENT_TYPE_DAY:
				return $event->dayEvent();
			case EventTypes::EVENT_TYPE_30_MIN:
				return $event->min30Event();
			case EventTypes::EVENT_TYPE_5_MIN:
				return $event->min5Event();
		}
	}
}


