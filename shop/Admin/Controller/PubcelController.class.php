<?php
/**
 * 导出文件Controller
 * @author lijiandong
 * @createtime 2015-3-3
 */
namespace Admin\Controller;

use Think\Controller;

//导入excel类库
import("Common.Org.PHPExcel");
import("Common.Org.PHPExcel.Writer.Excel5");
import("Common.Org.PHPExcel.IOFactory.php");
class PubcelController extends Controller {
    public  $num = 1;
    private $grapheme=array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");

    /**
     * 调用并处理数据
     * @param array $data      要导入到excel中的数据
     * @param array $caption   要导入的标题
     * @param string $filename excel文件名称
     */
    public function out($data , $caption , $filename="test_excel") {   
            $key=array_keys($caption);
            //去除传入数据中多余数据
            $processeddata = $this -> processing($key,$data);
            //将数据写入excel
            $this->getexcel($filename,$caption,$processeddata);    
    } 
     
    /**
     * 生成excel文件
     * @param substring $filename 生成文件名
     * @param array $data 数据中文名
     * @param array $sele 要写入excel的数据
     */
    private function getexcel($filename,$data,$sele){  
            $date=date("YmdHis",time()); 
            $filename.="_{$date}.xls";  
            $letter = $this -> grapheme;
            //创建PHPExcel对象             
            $objphpexcel=new \PHPExcel();             
            $objprops=$objphpexcel->getProperties();            
            $objactsheet=$objphpexcel->getActiveSheet();
            //设置字体
            $objphpexcel->getActiveSheet()->getStyle()->getFont()->setName('微软雅黑');
            //设置默认高度
            $objphpexcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(25);
            //设置列宽
            foreach($letter as $k => $v)
            {
                $objphpexcel->getActiveSheet()->getStyle($v.'1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objphpexcel->getActiveSheet()->getStyle($v.'1')->getFill()->getStartColor()->setARGB('#FFFF00');
                $objphpexcel->getActiveSheet()->getColumnDimension($v)->setWidth('30');
            }
            //设置边框
            $sharedstyle1=new \PHPExcel_Style();
            $sharedstyle1->applyFromArray(array('borders'=>array('allborders'=>array('style'=>\PHPExcel_Style_Border::BORDER_THIN))));

            $s=0;
            foreach($data as $k=>$v)
            {
                    $objphpexcel->setActiveSheetIndex(0)->setCellValue($letter[$s].$this->num,$v);
                    $s++;
            }
            $i=0;
            for($j=2 ; $j<=(count($sele)+1) ; $j++)
            {
                    $s=0;
                    foreach($sele[$i] as $k=>$v)
                    {
                            $objphpexcel->setActiveSheetIndex(0)->setCellValue($letter[$s].$j,$v);
                            $s++;
                    }	
                    $i++;
            }

            $filename = iconv("utf-8", "gb2312", $filename);          
            $objphpexcel->setActiveSheetIndex(0);             
            header('Content-Type: application/vnd.ms-excel');             
            header("Content-Disposition: attachment;filename=\"$filename\"");             
            header('Cache-Control: max-age=0');                
            $objwriter = \PHPExcel_IOFactory::createWriter($objphpexcel, 'Excel5');    
            //文件通过浏览器下载
            $objwriter->save('php://output');              
            exit;     
    }
    
    /**
     * 订单下载标题头
     */
    public function ordertle() {
        return array(
                    "ordertle" => array(
                                    "order_card"=>"订单号",
                                    "bargain"=>"合同编号",
                                    "user_name"=>"客官昵称",
                                    "phone"=>"手机号码",
                                    "pname"=>"业务员姓名",
                                    "short_title"=>"商品名称",
                                    "erji"=>"商品类型",
                                    "goods_number"=>"购买商品数",
                                    "status"=>"订单状态",
                                    "createtime"=>"订单生成时间",
                                    "pay_time"=>"订单支付时间",
                                    "is_invoile"=>"发票状态",
                                    "cost"=>"成本价",
                                    "profit"=>"盈利额",
                                    "onsale_money"=>"优惠券",
                                    "paypaper"=>"红包",
                                    "coil_money"=>"虚拟币",
                                    "pay_money"=>"现金",
                                    "goods_price"=>"商品价格",
                                    "totalprice"=>"订单总价",
                                ),
                    "invoicetitle" => array(
                                    "pay_time"=>"日期",
                                    "order_card"=>"订单号",
                                    "bargain"=>"合同号",
                                    "user_name"=>"用户名",
                                    "bill_type" => "发票类别",
                                    "bill_title"=>"开票抬头",
                                    "taxes_phore"=>"税务登记证号",
                                    "addressphone"=>"地址电话",
                                    "bankbank_number"=>"开户行账号",
                                    "erji" => "商品名称",
                                    "goods_number"=>"数量",
                                    "totalprice"=>"总价",
                                    "bill_status"=>"发票状态",
                                )
        );
    }
    
    /**
     * 
     * @param array $arr  所有要生成的key
     * @param array $sele 要处理的数据
     * @return array 返回处理后的数据
     */
    private function processing($arr,$sele){
            foreach($sele as $k => $v)
            {
                foreach($v as $key=>$val)
                {
                    if(in_array($key , $arr))
                    {
                        foreach($arr as $akey => $aval)
                        {
                            $dataloop[$aval]=$v[$aval];
                        }
                    }
                }
                $datadeal[]=$dataloop;
            }
            return $datadeal;
    }
	
    /**
     *要执行模板 
     * @param string $address 模板地址
     * @param unknown $val
     */
    public function word($addRess='', $val, $fileName) {
        //导出word
        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="">';
        echo '<xml><w:WordDocument><w:View>Print</w:View></xml> ';
        $this->display($addRess,$val);
        echo '</html>';
        ob_start(); //打开缓冲区
        Header("Cache-Control: public");
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        if (strpos($_SERVER["HTTP_USER_AGENT"],'MSIE')) {
            Header("Content-Disposition: attachment; filename=$fileName.doc");
        }else if (strpos($_SERVER["HTTP_USER_AGENT"],'Firefox')) {
            Header("Content-Disposition: attachment; filename=$fileName.doc");
        } else {
            Header("Content-Disposition: attachment; filename=$fileName.doc");
        }
        Header("Pragma:no-cache");
        Header("Expires:0");
        ob_end_flush();//输出全部内容到浏览器
    }
    
    /**
     * 上传文件
     * @param string $filename 文件名称
     * @param string $filetmpname 文件临时目录
     */
    public function upFile($filepath , $filename , $filetmpname) {
        if(is_dir($filepath)==FALSE){
            mkdir($filepath , 0777);  
        } 
        $dateFilepath = $filepath.date('Ymd',time())."/";
        if(is_dir($dateFilepath)==FALSE){
            mkdir($dateFilepath , 0777);  
        }
        $dateFilepath .=$filename;
        move_uploaded_file($filetmpname, $dateFilepath);
        return !empty($filename) ? trim($dateFilepath , '.') : false;
    }
}

