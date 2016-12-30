<?php
/*
 * 可以所有的站点都调用c站下的本文件，也可以将这个脚本放在每个网站需要登录的页面
 * 各个站点从配置获取
 * 确保能获得用户IP和当前登录的站点域名，两者可能会被用来作为钥匙解密ticket
 */
header("Content-type:text/javascript;charset=utf-8");

$sites = array('http://sitea.com:2222', 'http://siteb.com:2223', 'http://sitec.com:2221');

$refer_info = pathinfo($_SERVER["HTTP_REFERER"]);
$refer = $refer_info['dirname'];

$ticket = '';
if (is_string($_GET['ticket'])) {
    $ticket = $_GET['ticket'];
}
if (is_string($_GET['uname'])) {
    $uname = $_GET['uname'];
}
//irame框架
$iframes = '';
foreach ($sites as $api) {
    if($refer == $api){
        continue;
    }
    $iframes .= '<iframe style="display:none" width=0 height=0 frameborder=0  src="' . $api . '/login_api.php"></iframe>';
}

$js = <<<JSCODE
            window.onload = function(){
                var ticket = "{$ticket}"
                if(!ticket){
                    //从cookie从获取ticket
                    var ticket_arr = document.cookie.split(";").filter(
                        function(e){
                            var cookie = e.split("=")
                            return cookie[0].replace(" ","") === "ticket"
                         }
                    )
                    if(ticket_arr[0]){
                        ticket = ticket_arr[0].split("=")[1]
                    }else{
                        //ticket不存在
                        return
                    }
                }
                //创建隐藏的ifrme，调用认证接口
                div = document.createElement("div")
                div.innerHTML = '{$iframes}'
                var iframes = div.childNodes
                var len = iframes.length
                for (var i= 0;i<len;i++){
                    new_src = iframes[i].getAttribute("src")+'?ticket='+ticket+'&user='+'{$uname}'
                    iframes[i].setAttribute('src',new_src)
                }
                document.body.appendChild(div)
                //销毁ticket,防止被盗反复登录
                var date = new Date();
                date.setTime(date.getTime() - 10000);
                document.cookie = "ticket" + "=a; expires=" + date.toGMTString();
            };
JSCODE;

echo $js;
