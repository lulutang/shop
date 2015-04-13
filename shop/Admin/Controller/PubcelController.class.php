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
import("Common.Org.Tcpdf.tcpdf");
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
    
    /**
     * 下载pdf文件
     */
    public function downpdf($filename , $filehtml){
        //实例化 \PHPExcel
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false); 
        // 设置文档信息 
        $pdf->SetCreator('Helloweba'); 
        $pdf->SetAuthor('yueguangguang'); 
        $pdf->SetTitle('Welcome to helloweba.com!'); 
        $pdf->SetSubject('TCPDF Tutorial'); 
        $pdf->SetKeywords('TCPDF, PDF, PHP'); 

        // 设置页眉和页脚信息 
        $pdf->SetHeaderData('', '', '', '',  
              array(0,64,255), array(0,64,128)); 
        $pdf->setFooterData(array(0,64,0), array(0,64,128)); 

        // 设置页眉和页脚字体 
        $pdf->setHeaderFont(Array('stsongstdlight', '', '10')); 
        $pdf->setFooterFont(Array('helvetica', '', '8')); 

        // 设置默认等宽字体 
        $pdf->SetDefaultMonospacedFont('courier'); 

        // 设置间距 
        $pdf->SetMargins(10, 20, 15); 
        $pdf->SetHeaderMargin(1); 
        $pdf->SetFooterMargin(5); 

        // 设置分页 
        $pdf->SetAutoPageBreak(TRUE, 25); 

        // set image scale factor 
        $pdf->setImageScale(1.25); 

        // set default font subsetting mode 
        $pdf->setFontSubsetting(true); 

        //设置字体 
        $pdf->SetFont('stsongstdlight', '', 14); 
        $pdf->AddPage(); 
        $pdf->writeHTML($filehtml);
        //输出PDF 
        $pdf->Output($filename.'.pdf', 'I'); 
    }
    /**
    *数字金额转换成中文大写金额的函数
    *String Int  $num  要转换的小写数字或小写字符串
    *return 大写字母
    *小数位为两位
    **/
    public function replaceMoney($num){
            $c1 = "零壹贰叁肆伍陆柒捌玖";
            $c2 = "分角元拾佰仟万拾佰仟亿";
            //精确到分后面就不要了，所以只留两个小数位
            $num = round($num, 2); 
            //将数字转化为整数
            $num = $num * 100;
            if (strlen($num) > 10) {
                    return "金额太大，请检查";
            } 
            $i = 0;
            $c = "";
            while (1) {
                    if ($i == 0) {
                            //获取最后一位数字
                            $n = substr($num, strlen($num)-1, 1);
                    } else {
                            $n = $num % 10;
                    }
                    //每次将最后一位数字转化为中文
                    $p1 = substr($c1, 3 * $n, 3);
                    $p2 = substr($c2, 3 * $i, 3);
                    if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
                            $c = $p1 . $p2 . $c;
                    } else {
                            $c = $p1 . $c;
                    }
                    $i = $i + 1;
                    //去掉数字最后一位了
                    $num = $num / 10;
                    $num = (int)$num;
                    //结束循环
                    if ($num == 0) {
                            break;
                    } 
            }
            $j = 0;
            $slen = strlen($c);
            while ($j < $slen) {
                    //utf8一个汉字相当3个字符
                    $m = substr($c, $j, 6);
                    //处理数字中很多0的情况,每次循环去掉一个汉字“零”
                    if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
                            $left = substr($c, 0, $j);
                            $right = substr($c, $j + 3);
                            $c = $left . $right;
                            $j = $j-3;
                            $slen = $slen-3;
                    } 
                    $j = $j + 3;
            } 
            //这个是为了去掉类似23.0中最后一个“零”字
            if (substr($c, strlen($c)-3, 3) == '零') {
                    $c = substr($c, 0, strlen($c)-3);
            }
            //将处理的汉字加上“整”
            if (empty($c)) {
                    return "零元整";
            }else{
                    return $c . "整";
            }
    }
}

