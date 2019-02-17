<?php
$mysqli = new mysqli("localhost", "amorncha_rpca56", "Rpca56", "amorncha_rpca56");
$mysqli->set_charset("utf8");
$access_token = 'B/kcwIj+9Bw6GvXGqo91vRAFTATtZ3ooGjx+OZQ5zE530OlReszGRF55k2E6U42P/wmuR2eKeYy9Rw9BltofaeLewafB7Bae9rrUVqmdxrEmoikDJlmv6U8BabJ7clsq67ZDRsvAWWyWADopA3+WxgdB04t89/1O/w1cDnyilFU=';
	
// Get POST body content
$content = file_get_contents('php://input');
// Parse JSON
$events = json_decode($content, true);
// Validate parsed JSON data

function sendImage($image,$replyToken){
			global $access_token;
			$messages = [
				'type' => 'image',
				'originalContentUrl' => "https://arcane-waters-61888.herokuapp.com/pic/j/".$image,
				'previewImageUrl' => "https://arcane-waters-61888.herokuapp.com/pic/j/".$image
				//'previewImageUrl' => 'https://arcane-waters-61888.herokuapp.com/resize.php?filename='.$image
			];
			// Make a POST Request to Messaging API to reply to sender
			$url = 'https://api.line.me/v2/bot/message/reply';
			$data = [
				'replyToken' => $replyToken,
				'messages' => [$messages],
			];
			$post = json_encode($data);
			$headers = array('Content-Type: application/json', 'Authorization: Bearer ' . $access_token);
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			$result = curl_exec($ch);
			curl_close($ch);
			echo $result . "\r\n";		
			exit;
}
function sendText($text,$replyToken){
				global $access_token;
				$messages = [
					'type' => 'text',
					'text' => $text
				];

				// Make a POST Request to Messaging API to reply to sender
				$url = 'https://api.line.me/v2/bot/message/reply';
				$data = [
					'replyToken' => $replyToken,
					'messages' => [$messages],
				];
				$post = json_encode($data);
				$headers = array('Content-Type: application/json', 'Authorization: Bearer ' . $access_token);

				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
				$result = curl_exec($ch);
				curl_close($ch);
				//echo $result . "\r\n";
				//exit;
			
}
//saveText($event,$date,$groupId,$userId,'file',"https://tcsd.ml/line_keep/hoon/".$name);
function saveText($event,$date,$groupId,$userId,$type,$text){
    global $mysqli;
    $file = $event['source']['groupId'].'/data'.date("Ymd").'.txt';
    $text = $mysqli->real_escape_string($text);
    //$query = "INSERT INTO tcsd.linekeep (create_time,groupid,userid,typex,content) VALUES('".$date."','".$groupId."','".$userId."','".$type."','".$text."')";
    $mysqli->query($query);

    // The new person to add to the file
    //$person = "John Smith\n";
    // Write the contents to the file, 
    // using the FILE_APPEND flag to append the content to the end of the file
    // and the LOCK_EX flag to prevent anyone else writing to the file at the same time
    file_put_contents($file, $text, FILE_APPEND | LOCK_EX);
}

function getMimeType($buffer) {
    //$buffer = file_get_contents($url);
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    return $finfo->buffer($buffer);
}
function saveFile($event,$type){
    $messageId = $event['message']['id'];
    $groupId =$event['source']['groupId'];
    $userId = $event['source']['userId'];
	$replyToken = $event['replyToken'];
	$roomId =$event['source']['roomId'];

    try{
        global $access_token;
        $url = 'https://api.line.me/v2/bot/message/'.$messageId.'/content';
        //$headers = array('Content-Type: application/json','Authorization: Bearer ' . $access_token);
        $opts = [
            "http" => [
                "method" => "GET",
                "header" => 'Authorization: Bearer ' . $access_token
            ]
        ];
        
        $context = stream_context_create($opts);
        
        // Open the file using the HTTP headers set above
        $file = file_get_contents($url, false, $context);
        $name = ''; 
        if($type!=='file'){
            $extension = explode('/',getMimeType($file));
            $name = $groupId.'/'.date("YmdHis").'_'.$userId.'_'.$type.'.'.$extension[1];
            file_put_contents($name,$file);
        }else{
            $name = $groupId.'/'.date("YmdHis").'_'.$userId.'_'.$event['message']['fileName'];
            file_put_contents($name,$file);
        }
        
        $date = date("YmdHis");
        
        saveText($event,$date,$groupId,$userId,'file',"https://rpca56.amornchai.net/pbook/".$name);
        	
    }catch(Exception $e){
        error_log($e->getMessage());
    }
}
function makedirs($dirpath, $mode=0777) {
    return is_dir($dirpath) || mkdir($dirpath, $mode, true);
}

function select_option1($chk){
   global $mysqli;
          $sql = "SELECT * from rpca56_db where Fname like '%".$chk."%' or Sname like '%".$chk."%' or Position like '%".$chk."%' or Province like '%".$chk."%' or Region like '%".$chk."%' or Phone like '%".$chk."%' or Nname like '%".$chk."%' order by No limit 20";   
		  $result = $mysqli->query($sql); 
   $res = "ข้อมูลรายชื่อ นรต.56"."\n"."\n";
   $i=0;
	if ($result->num_rows > 0) {		  
		while($row = $result->fetch_assoc()) {
		  $i++;
		  $res .= $i.".".$row['Fname']." ".$row['Sname']." "."(".$row['Nname'].")"." "." : ".$row['Position']." ".$row['Province']." ".$row['Region']." : ".$row['Phone']."\n"."\n";
		  if ($i > 19) { $res .= "=============="."\n".'ดูเพิ่มเติม http://rpca56.amornchai.net/pbook/rpca56_pbook.php'; }
        } 	
    } else { $res = 'ไม่พบข้อมูล'; }
	
    return $res;
}

function select_option2($chk){
 
   global $mysqli;
          $sql = "SELECT * from afaps40 where Fname like '%".$chk."%' or Sname like '%".$chk."%' or Position like '%".$chk."%' or Nname like '%".$chk."%' or Type like '%".$chk."%' or Fname_old like '%".$chk."%' or Sname_old like '%".$chk."%' order by No limit 20";   
		  $result = $mysqli->query($sql); 
   $res = "ข้อมูลรายชื่อ นตท.40"."\n"."\n";
   $i=0;
	if ($result->num_rows > 0) {		  
		while($row = $result->fetch_assoc()) {
		  $i++;
		  $res .= $i.".".$row['Rank']." ".$row['Fname']." ".$row['Sname']." "."(".$row['Nname'].")"." : ".$row['Position']." ".$row['Type']." : ".$row['Mphone']."\n"."\n";
		  if ($i > 19) { $res .= "=============="."\n".'ดูเพิ่มเติม http://rpca56.amornchai.net/afaps40/afaps40.php'; }
        } 	
    } else { $res = 'ไม่พบข้อมูล'; }
	
    return $res;
}





function handleEvent($event){
    makedirs($event['source']['groupId']);
    if ($event['type'] == 'message' && $event['message']['type'] == 'text') {
        // Get text sent
        $text = $event['message']['text'];
        // Get replyToken
        $replyToken = $event['replyToken'];


        $date = date("YmdHis");
        $groupId = $event['source']['groupId'];
        $userId = $event['source']['userId'];
        saveText($event,$date,$groupId,$userId,"text",$text);
    }if ($event['type'] == 'message' && $event['message']['type'] == 'image') {
        // Get text sent
        $replyToken = $event['replyToken'];
        //sendText("ได้รับรูป".$event['message']['id'],$replyToken);
        saveFile($event,'image');
    }
    if ($event['type'] == 'message' && $event['message']['type'] == 'video') {
        
        // Get text sent
        $replyToken = $event['replyToken'];
        saveFile($event,'video');
    }
    if ($event['type'] == 'message' && $event['message']['type'] == 'audio') {
        // Get text sent
        $replyToken = $event['replyToken'];
        saveFile($event,'audio');
    }
    if ($event['type'] == 'message' && $event['message']['type'] == 'file') {
        // Get text sent
        $replyToken = $event['replyToken'];
        saveFile($event,'file');
    }
}
if (!is_null($events['events'])) {
	 
	// $groups = array('Ca6635f433062bdbb296070b1176109d4');

	foreach ($events['events'] as $event) {
        if(isset($event['source']['groupId'])){
            if ($event['type'] == 'message' && $event['message']['type'] == 'text') {
                $text = $event['message']['text'];
                $replyToken = $event['replyToken'];
                if(strpos($text, 'whoamix') !== false){
                    $res = "GroupID:".$event['source']['groupId'].",RoomID".":".$event['source']['roomId'].",UserID:".$event['source']['userId'];
                    sendText($res,$replyToken); 
                }
				
				
				
				
			if(strpos($text, '#m') !== false){
            //sendText("test",$replyToken);
            $q = explode(" ", $text);
            if(!isset($q[1])){
                return;
            }
            $res = select_option1($q[1]);
            sendText($res,$replyToken);
        }  
		
					if(strpos($text, '$') !== false){
            //sendText("test",$replyToken);
            $q = explode("$", $text);
            if(!isset($q[1])){
                return;
            }
            $res = select_option1($q[1]);
            sendText($res,$replyToken);
        }  
		
		
		
		if(strpos($text, '#') !== false){
            //sendText("test",$replyToken);
            $q = explode("#", $text);
            if(!isset($q[1])){
                return;
            }
            $res = select_option2($q[1]);
            sendText($res,$replyToken);
        }  
		
		
		
		
		
		
            }
			
         //   if(in_array($event['source']['groupId'],$groups)){
         //       handleEvent($event);
         //   }
           
        }
        
        // Reply only when message sent is in 'text' format
	}
}
