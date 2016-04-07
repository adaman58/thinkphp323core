<?php
/**
 * 异步将远程链接上的内容(图片或内容)写到本地
 * 
 * @param unknown $url
 *            远程地址
 * @param unknown $saveName
 *            保存在服务器上的文件名
 * @param unknown $path
 *            保存路径
 * @return boolean
 */
function put_file_from_url_content($url, $filename) {
    // 设置运行时间为无限制
    set_time_limit ( 0 );
    
    $url = trim ( $url );
    $curl = curl_init ();
    // 设置你需要抓取的URL
    curl_setopt ( $curl, CURLOPT_URL, $url );
    // 设置header
    curl_setopt ( $curl, CURLOPT_HEADER, 0 );
    // 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。
    curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
    // 运行cURL，请求网页
    $file = curl_exec ( $curl );
    // 关闭URL请求
    curl_close ( $curl );
    // 将文件写入获得的数据
    
    if (file_exists($filename)) {$ret = unlink($filename);}
    file_put_contents($filename, $file);

    return true;
}

function autowrap($fontsize, $angle, $fontface, $string, $width) {
// 这几个变量分别是 字体大小, 角度, 字体名称, 字符串, 预设宽度
	$content = "";
	// 将字符串拆分成一个个单字 保存到数组 letter 中
	for ($i=0;$i<mb_strlen($string);$i++) {
		$letter[] = mb_substr($string, $i, 1, 'utf-8');
	}

	foreach ($letter as $l) {
		$teststr = $content." ".$l;
		$testbox = imagettfbbox($fontsize, $angle, $fontface, $teststr);
		// 判断拼接后的字符串是否超过预设的宽度
		if (($testbox[2] > $width) && ($content !== "")) {
			$content .= "\n";
		}
		$content .= $l;
	}

	return $content;
}

/**
 * POST的方式调用API
 * @param string $url       请求的API地址
 * @param array  $data      发送参数 
 */
function curlPost($url,$data=null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0 );

    $header = array ('Accept-Charset: utf-8',
            'Content-Type:application/x-www-form-urlencoded',
            );

    curl_setopt($ch, CURLOPT_HTTPHEADER, $header );
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
    $temp = curl_exec($ch);         // 返回信息
    $status = curl_getinfo($ch);    // 执行结果
    curl_close($ch);
    
    $info = array('receive_info'=>$temp, 'status'=>$status);

    return $info;
}

function curlGet($url,$data=null) {
	$ch = curl_init();

	if (!empty($data)) {
		$params = http_build_query($data);
		if (strstr($url, '?')) {
			$url = $url . '&' . $params;
		} else {
			$url = $url . '?' . $params;
		}
	}


	$header = array ('Accept-Charset: utf-8',
            'Content-Type:application/x-www-form-urlencoded',
            );
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header );
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$temp = curl_exec($ch);
	$status = curl_getinfo($ch);    // 执行结果
	curl_close($ch);

	$info = array('receive_info'=>$temp, 'status'=>$status);

	return $info;
}


function requestWeixinApiGet($url,$getData){

	$baseurl = C('DOMAIN_URL_V1');
	$getData['access_token'] = getToken();
	if($getData['user_id']==null && is_login()>0){//额外增加登录用户的用户openid信息
		$getData['user_id'] = is_loginopen();
	}
	$api = C('UNDM_API');

	$info = curlGet($baseurl.$api[$url],$getData);

	return $info;
}

function requestWeixinApiPost($url, $postData){

	$baseurl = C('DOMAIN_URL_V1');
	$postData['access_token'] = getToken();
	if($postData['user_id']==null && is_login()>0){//额外增加登录用户的用户openid信息
		$postData['user_id'] = is_loginopen();
	}
	$api = C('UNDM_API');

	$info = curlPost($baseurl.$api[$url],$postData);
	return $info;
}

/**
 * 获取 API 授权令牌
 * @return string 授权令牌
 */
function getToken() {
	$accessToken = S('SUBSITE_ACCESSTOKEN');
	if (empty($accessToken)) {
		$params = C('MP');
		$baseurl = C('WEIXIN_DOMAIN_URL');
		$api = C('WEIXIN_API');

		$params = array('appid'=>$params['APP_ID'],
				'secret'=>$params['APP_SECRET'],
				'grant_type'=>$params['GRANT_TYPE']);

		$url = $baseurl . $api['GETTOKEN'];
		$info = curlGet($url,$params);
		$data = json_decode($info['receive_info'], true);
		$accessToken = $data['access_token'];
		S('SUBSITE_ACCESSTOKEN', $accessToken, (7200 - 60));
	}
	return $accessToken;
}

/**
 * 获取 API 授权令牌
 * @return string 授权令牌
 */
function getJsapiTicket() {
	$ticket = S('SUBSITE_JSAPITICKET');
	if (empty($ticket)) {
		$baseurl = C('WEIXIN_DOMAIN_URL');
		$api = C('WEIXIN_API');

		$params = array('access_token'=>getToken(),
				'type'=>'jsapi');

		$url = $baseurl . $api['GETJSAPI'];
		$info = curlGet($url,$params);
		$data = json_decode($info['receive_info'], true);
		$ticket = $data['ticket'];
		S('SUBSITE_JSAPITICKET', $ticket, (7200 - 60));
	}
	return $ticket;
}

/** 图片圆形处理 **/
class image_cutter {
	private $original_image;
	public $cutted_image;

	private $diameter;
	private $radius;

	public function __construct( $image_path ) {

		/* load the original image ... */
		$image = imagecreatefromjpeg( $image_path );

		/* get image size ... */
		$x = imagesx( $image );
		$y = imagesy( $image );

		/* diameter of the circle is always the smaller side ... */
		$this->diameter = $x > $y ? $y : $x;

		/* radius is half a diameter ... am i must explain this ...? */
		$this->radius = $this->diameter / 2;

		/* save the original image ... */
		$this->original_image = $image;

		/* create new canvas to save our work ... */
		$this->create_blank_image();

		/* PAINTING TIME ... */
		$this->read_the_original_image_and_write();

		/* i'm positively bursting to see what we have done ... */
	}

	public function __destruct() {

		/* hey my dear brower ... it's not a html page comes ... */
		//header("Content-type: image/png");

		/* show our work ... */
		//imagepng( $this->cutted_image );

		/* we have to cleaned up the mass before left ... */
		imagedestroy( $this->original_image );
		imagedestroy( $this->cutted_image );

		/* so ... how do you think about this ...? */
	}

	private function create_blank_image() {

		/* create a true color square whose side length equal to diameter of the circle ... */
		$image = imagecreatetruecolor( $this->diameter,$this->diameter );

		/* we also need a transparent background ... */
		imagesavealpha($image, true);

		/* create a transparent color ... */
		$color = imagecolorallocatealpha($image, 0, 0, 0, 127);

		/* ... then fill the image with it ... */
		imagefill($image, 0, 0, $color);

		/* nothing to do then ... just save the new image ... */
		$this->cutted_image = $image;

		/* go back and see what should we do next ..? */
		return;

	}

	private function read_the_original_image_and_write() {

		/* actually we need a smooth circle ... */
		for ( $x = 0; $x <= $this->radius; $x += 0.01 ) {

			/* standard form for the equation of a circle ... don't tell me you never knew that ... */
			$y = sqrt( $this->diameter * $x - pow( $x , 2 ) ) + $this->radius;

			/* i think i should call this successive scans ... */
			for ( $i = $x; $i < $this->diameter - $x; $i++ ) {

				/* half of the circle ... */
				imagesetpixel (
					$this->cutted_image , $i, $y,
					imagecolorat( $this->original_image, $i, $y )
				);

				/* the other half of course ... */
				imagesetpixel (
					$this->cutted_image , $i, $this->diameter - $y,
					imagecolorat( $this->original_image, $i, $this->diameter - $y )
				);

			}

		}

		/* avoid the white line when the diameter is an even number ... */
		if ( ! is_float( $this->radius ) )
			for ( $i = 0; $i < $this->diameter; $i++ )

				imagesetpixel (
					$this->cutted_image , $i, $this->radius - 1,
					imagecolorat( $this->original_image, $i, $this->radius - 1 )
				);

		/* woo ... not as difficult as you think ... that's all ... */
		return;

	}
}
