<?php
/* 
 * 上传图片类 
 * @parameter;
 * @date：2011-8-15 
 * @author：cy 
 * */
class UpimageAction extends AppAction
{
    protected $subdir='./Uploads/images'; //保存目录
    protected $type='jpg';  //上传图片格式
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * 上传图片
     * @param $data 图片数据
     * @param $rand 随机数范围
     * @return [array]
     */
    public function upImg($data,$rand=array(1,33))
    {
        if(!empty($this->subdir)){
            $bak=array();   //返回数据
            $folder = $this->subdir;// folder where to save images
            if( !is_dir($folder) ) mkdir($folder);
            // 图片命名
            $datenow=date('Ymd');
            $timenow=time();
            $image = $datenow.$timenow.mt_rand($rand[0],$rand[1]).'.'.$this->type;
            $check = $folder.'/'.$image;
            if (file_exists($check)) unlink($check);    //如果图片存在则删除
            if($png=base64_decode($data)){
                if($file = fopen($check, "w")){
                    fwrite($file,$png);//写入
                    fclose($file);//关闭
                }else{
                    $bak['suc']=false;
                    $bak['msg']='文件创建失败';
                    return $bak;
                    exit();
                }
            }
            else
            {
                $bak['suc']=false;
                $bak['msg']='没有数据';
                return $bak;
                exit();
            }
            /*$bak['suc']=false;
            $bak['msg']=$data;
            return $bak;
            exit();*/
            /*$bak['suc']=false;
            $bak['msg']=file_exists($check);
            return $bak;
            exit();*/
            if(file_exists($check))
            {
                // if(!$this->getimageInfo($write_image))
                // {
                //     $bak['suc']=false;
                //     $bak['msg']='图片格式有误';
                //     return $bak;
                //     exit();
                // }
                $bak['suc']=true;
                $bak['msg']='上传成功';
                $bak['img']=ltrim($check,'.');
                //$bak['img']=$check;
                return $bak;
                exit();
            }
            else
            {
                $bak['suc']=false;
                $bak['msg']='上传失败';
                return $bak;
                exit();
            }
        }
        else
        {
            $bak['suc']=false;
            $bak['msg']='参数错误';
            return $bak;
            exit();
        }
    }

    /*
     * 检测图片是否合法
     * @parameter; 文件名
     * @date：2011-8-15
     * @author：cy
     * */
    /*private function getimageInfo($imageName = '') {
        $imageInfo = getimagesize ( $imageName );
        if ($imageInfo !== false) {
            $imageType = strtolower ( substr ( image_type_to_extension ( $imageInfo [2] ), 1 ) );
            //            $imageSize = filesize ( $imageInfo );
            return $info = array ('width' => $imageInfo [0], 'height' => $imageInfo [1], 'type' => $imageType, 'mine' => $imageInfo ['mine'] );
        } else {
            //不是合法的图片
            return false;
        }
    } */
}