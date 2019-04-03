<?php

if(strstr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
    echo '<!doctype html>
        <html>
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title></title>
            <style type="text/css">
                body{
                    margin: 0; padding: 0; background: url(/weixin/bg.jpg) repeat;
                }
                #tip{
                    background: url(/weixin/app-open.png) no-repeat center top;
                    -webkit-background-size: contain;
                    background-size: contain;
                    height: 368px;
                }
                #footer{
                    position: absolute;
                    bottom: 20px;
                    width: 100%;
                }
                .logo{
                    width: 192px; height: 20px; margin: 0 auto;
                    background: url(/weixin/logo.png) no-repeat center;
                    background-size: contain;
                    -webkit-background-size: contain;
                }
            </style>
        </head>
        <body>
        <div id="tip"></div>
        <div id="footer">
            <div class="logo"></div>
        </div>
        </body>
        </html>';
}else{
//    header('Location:http://wap.apk.anzhi.com/data5/apk/201812/26/75b7fd487ffded352089d0218c0b95d2_15606500.apk');
    header("Content-type:text/html;charset=utf-8");
    $file_name="mth.apk";
    $file_path="weixin/6c2cf75d85c6717531118407c4ee23db.apk";

    //首先要判断给定的文件存在与否
    if(!file_exists($file_path)){
        echo "没有该文件文件";
        return ;
    }
    $fp=fopen($file_path,"r");
    $file_size=filesize($file_path);

    //下载文件需要用到的头
    Header("Content-type: application/octet-stream");
    Header("Accept-Ranges: bytes");
    Header("Accept-Length:".$file_size);
    Header("Content-Disposition: attachment; filename=".$file_name);
    $buffer=1024;
    $file_count=0;
    //向浏览器返回数据
    while(!feof($fp) && $file_count<$file_size){
        $file_con=fread($fp,$buffer);
        $file_count+=$buffer;
        echo $file_con;
    }
    fclose($fp);
}