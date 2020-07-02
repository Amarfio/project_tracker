
    <?php


header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'connect.php';



if ( isset($_GET['sender_id']) && isset($_GET['reciever_id'])) {
    $sender_id = mysqli_escape_string($conn, $_GET['sender_id']); 
    $reciever_id = mysqli_escape_string($conn, $_GET['reciever_id']); 

  
    
    $query = "SELECT ch.is_read, ch.sender_id, ch.reciever_id FROM chat ch  WHERE ch.sender_id = '$sender_id' AND ch.reciever_id = '$reciever_id' AND ch.is_read = 0";


    $result = mysqli_query($conn, $query);

    $num = mysqli_num_rows($result);
    $chat_message = array();


    if ($num > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            read_message ($conn, $row['sender_id'], $row['reciever_id']);
        }

                            
        $message = json_encode(
            array(
                'message' => 'meassage read succefully',
                'status' => 'success'
            )
        );
        exit($message);

    } else { 
       
        $message = json_encode(
            array(
                'message' => 'Nothing to read',
                'status' => 'success'
            ) 
        );
        exit($message); 
    }
}else {
       
        $message = json_encode(
            array(
                'message' => 'Invalid Parameters',
                'status' => 'failed'
            ) 
        );
        exit($message); 
    }

    function read_message ($conn, $sender_id, $reciever_id){
        $query = "UPDATE `chat` SET `is_read` = '1' WHERE `chat`.`sender_id` = '$sender_id' AND  `chat`.`reciever_id` = '$reciever_id' ";
        
        $result = mysqli_query($conn, $query);
        return $result;
    }