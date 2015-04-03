<?php
/**
 * 后台套餐Model
 * @author 李建栋
 *
 */
namespace Admin\Model;

use Think\Model;

class PackageModel extends Model{

    /**
     * 添加套餐
     * @param array $Package_arr 套餐数据
     */
    public function PackageAdd($Package_arr)
    {
        $string = array();
        foreach($Package_arr as $k=>$v)
        {
            if($k != 'pakeageID')
            {
                $string[$k] = $v;
            }
        }
        return $this ->add($string);
    }

    /**
     * 取出套餐临时表中的商品id
     * @return string
     */
    public function GoodsID()
    {
        $Inter = M("inter");
        $uid = session("userid");
        $str='';
        $goods_id = $Inter -> field("Gid")-> where("u_id=".$uid) -> select();
        foreach($goods_id as $k => $v)
        {
            $str .=$v["Gid"].',';
        }
        return trim($str,',');
    }

    /**
     * 根据输入条件删除临时表的数据
     * @param string $where 删除条件
     */
    public function Del_interAll($where)
    {
        $Inter = M("inter");
        $Inter ->where($where) -> delete();
    }

    /**
     * 在临时表中插入数据
     * @param string $GoodsID 商品Id
     */
    public function InterAdd($GoodsID)
    {
        $Inter = M("inter");
        $Goods_id = explode(",",$GoodsID);
        $string = array();
        $str=array();
        $uid = session("userid");
        $Inter_arr = $Inter -> field("Gid")-> where("u_id =".$uid) -> select();
        foreach($Inter_arr as $k => $v)
        {
            $string[] =$v["Gid"];
        }
        foreach($Goods_id as $k => $v)
        {
            if(!in_array($v,$string))
            {
                $str[] =$v;
            }
        }
        $sql = "insert into shop_inter(Gid , u_id)values";
        for($i=0 ; $i < count($str) ; $i++)
        {
            if($i != 8)
            {
                $sql .="(".$str[$i].",".$uid."),";
            }
        }
        $sql = trim($sql , ',');
        return $Inter -> query($sql);
    }

    /**
     * 获取临时表中所有商品id
     * @return string
     */
    public function GetInterAll()
    {
        $Inter = M("inter");
        $str = ''; 
        $uid = session("userid");
        $InterAll = $Inter -> where("u_id =".$uid) -> select();
        foreach($InterAll as $k => $v)
        {
            $str .=$v['Gid'].',';
        }
        return trim($str,",");
    }

    /**
     * 添加套餐商品关联表数据
     * @param string $Package_str 商品Id
     * @param int $Package_Id     套餐Id
     */
    public function pack_goods($Package_str , $Package_Id)
    {
        $Package_goods = M("package_goods");
        $Goods_arr = explode(",",$Package_str);
        $sql = "insert into shop_package_goods(package_id,goods_id)values";
        for($i=0 ; $i<count($Goods_arr) ; $i++)
        {
            $sql .="(".$Package_Id.",".$Goods_arr[$i]."),";
        }
        $sql=trim($sql , ',');
        return $Package_goods -> query($sql);
    }

    /**
     * 根据条件获取套餐总的记录数
     * @param string $where 查询条件
     */
    public function Getcount($where='1=1')
    {
        return $this -> where($where) -> count();
    }

    /**
     * 
     * @param int $start    开始位置
     * @param int $end      结束位置
     * @param string $where 查询条件
     */
    public function Getdata($start , $end , $where='1=1')
    {
        return $this -> order("package_id desc") -> where($where) ->limit($start , $end) -> select();
    }

    /**
     * 套餐商品关联表数据
     * @param string $where 查询条件
     */
    public function Package_goods($where)
    {
        $PackageGoods = M("package_goods");
        $field="shop_goods.goods_id,s_id,goods_code,short_title,title,description,now_price,old_price,is_limit,limit_num,is_index,is_hot,is_gift,number,thumb,goods_pic,content,adduser,status,addtime,index_order,index_isshow,server_order,server_isshow,give_sale,server_name,attr_name,now_servername,server_pid,cost,shop_package_goods.goods_id,shop_package_goods.package_id";
        return $PackageGoods-> field($field) -> join("left join shop_goods on shop_goods.goods_id = shop_package_goods.goods_id") ->order("shop_goods.addtime desc") ->where($where) -> select();
    }

    /**
     * 删除套餐关联表数据修改套餐组合商品ID
     * @param int $Goods_id   商品Id
     * @param int $Package_id 套餐Id
     */
    public function Package_del($Goods_id , $Package_id)
    {
        $PackageGoods = M("package_goods");
        $PackageGoods ->where("goods_id =".$Goods_id." and package_id=".$Package_id) -> delete();
        return $this -> Upzuhe($Goods_id , $Package_id);
    }

    /**
     * 根据商品查询套餐关联表数据
     * @param string $where 查询条件
     */
    public function Pack_goodsshow($where)
    {
        $PackageGoods = M("package_goods");
        return $PackageGoods  ->where($where) -> select();
    }

    /**
     * 修改组合字段中的商品id
     * @param int $Goods_id   商品Id
     * @param int $Package_id 套餐Id
     */
    public function Upzuhe($Goods_id , $Package_id)
    {
        $string = '';
        $Pzuhe = $this -> field("zuhe") -> where("package_id =".$Package_id) -> find();
        $Goods_zuhe = explode(',',$Pzuhe["zuhe"]);
        for($i=0 ; $i<count($Goods_zuhe) ; $i++)
        {
            if($Goods_zuhe[$i] != $Goods_id)
            {
                $string .=$Goods_zuhe[$i].',';
            }
        }
        $goods_zuhe["zuhe"]= trim(trim($string,","));
        return $this -> where("package_id =".$Package_id) -> save($goods_zuhe);

    }

    /**
     * 添加商品，修改组合字段
     * @param string $Goods_id 商品Id
     * @param int $Package_id  套餐Id
     */
    public function addpackage($Goods_id , $Package_id)
    {
        $PackageGoods = M("package_goods");
        $Goods_zuhe = explode(',',trim($Goods_id , ','));
        $string = array();
        $str    = array();
        $Pzuhe  = $PackageGoods -> field("goods_id") -> where("package_id =".$Package_id) -> select();
        foreach($Pzuhe as $k => $v)
        {
            $string[] =$v["goods_id"];
        }
        for($i=0 ; $i<count($Goods_zuhe) ; $i++)
        {
            if(!in_array($Goods_zuhe[$i] , $string))
            {
                $str[]=$Goods_zuhe[$i];
            }
        }
        $sql = "insert into shop_package_goods(package_id,goods_id)values";
        for($i=0 ; $i<count($str) ; $i++)
        {
            $sql .="(".$Package_id.",".$str[$i]."),";
        }
        $sql=trim($sql , ',');
        $PackageGoods -> query($sql);
        return $this -> UP_zuhe($str , $Package_id);
    }

    /**
     * 添加商品id在组合字段中
     * @param array $str 商品Id
     * @param int $Package_id 套餐Id
     */
    public function UP_zuhe($str , $Package_id) {
        $string = '';
        $Pzuhe = $this -> field("zuhe") -> where("package_id =".$Package_id) -> find();
        $Goods_zuhe = implode(',',$str);
        $data["zuhe"] = trim($Pzuhe["zuhe"].",".$Goods_zuhe , ',');
        return $this -> where("package_id =".$Package_id) -> save($data);
    }

    /**
     * 获取单条套餐数据并且取出本套餐中所有的商品
     * @param int $PackageId 套餐Id
     * @return array
     */
    public function UPpackAge($PackageId)
    {
        $Package_arr   = $this -> where("package_id =".$PackageId) -> find();
        $Package_goods = $this -> Goodspack($Package_arr["zuhe"]);
        return array("Pack_arr"=>$Package_arr , "Package_goods"=>$Package_goods);
    }

    /**
     * 取出对应套餐中的商品
     * @param string $Goods_zuhe 商品Id
     */
    public function Goodspack($Goods_zuhe)
    {
        $Goods = M("goods");
        //临时表中商品ID
        $package_InterGid = explode(',',$this -> GetInterAll()); 
        //套餐表中商品ID
        $Goods_zuhearr = explode(',',$Goods_zuhe);   
        for($i=0 ; $i < count($package_InterGid) ; $i++)
        {
            if(!in_array($package_InterGid[$i] , $Goods_zuhearr))
            {
                $str .=$package_InterGid[$i].',';
            }
        }
        //将套餐表中的商品ID和临时表中的商品ID拼接到一起
        $Goods_str = trim(implode(',',$Goods_zuhearr).",".trim($str,','),","); 
        return $Goods -> where("goods_id in (".$Goods_str.")") -> select();

    }

    /**
     * 修改套餐信息
     * @param array $Package_arr 套餐数据
     * @param int $Package_id    套餐Id
     */
    public function PackageUP($Package_arr , $Package_id)
    {
        $string = array();
        $str ='';
        foreach($Package_arr as $k=>$v)
        {
            if($k != 'pakeageID' && $k != 'zuhe')
            {
                $string[$k] = $v;
            }
        }
        $Pzuhe = $this -> field("zuhe") -> where("package_id =".$Package_id) -> find();
        $Packstr = explode(',' , $Package_arr["zuhe"]);
        $zuhe = explode(',' , $Pzuhe["zuhe"]);
        for($i=0 ; $i < count($Packstr) ; $i++)
        {
            if(!in_array($Packstr[$i] , $zuhe))
            {
                $str .=$Packstr[$i].',';
            }
        }
        $string["zuhe"] = trim(trim($str , ',').",".$Pzuhe["zuhe"],',');
        return $this -> where("package_id =".$Package_id) -> save($string);
    }

    /**
     * 删除关联表中对应套餐的商品
     * @param string $package_id 套餐Id
     */
    public function Del_Gid($package_id)
    {
        $PackageGoods = M("package_goods"); 
        return $PackageGoods -> where("package_id in(".$package_id.")") ->delete();
    }

    /**
     * 批量修改套餐状态
     * @param string $package_id 套餐Id
     * @param string $syllable   状态名
     * @param int $staus         状态
     */
    public function UPstatus($package_id , $syllable , $staus)
    {
        $data[$syllable] =$staus;
        return $this -> where("package_id in(".$package_id.")") -> save($data);
    }

    /**
     * 删除套餐表中的数据（没有子商品）
     * @param string $packageId 套餐Id
     */
    public function DEl_package($packageId)
    {
        return $this -> where("package_id in(".$packageId.")") -> delete();
    }

    /**
     * 取优先级设置页列表
     */
    public function getPackageList(){
        return $this->field('package_id,package_code,short_title,title,starttime,endtime,orderid,is_index,is_hot,status  ')->where(' status=1 ')->order( ' orderid asc ')->select();
    }

    /**
     * 批量修改商品状态
     * @param string $gid 商品Id
     * @return int
     */
    public function Goods_status($gid){
        $Goods = M("goods");
        $goods_arr =  $Goods -> where("goods_id in (".$gid.")") -> select();
        $str = array();
        foreach($goods_arr as $k => $v)
        {
            if($v["status"] != 1)
            {
                $str[] = $v["goods_id"];
            }
        }
        if(!empty($str))
        {
            return $str;
        }
    }
}