<?php

function response($message, $status, $data){
    return json_encode(
        array(
            'message'=> $message,
            'status'=> $status,
            'data'=> $data,
        )
    );
}

?>