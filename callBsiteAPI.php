<?php
function callBsiteAPI($postData) {
    $url = "http://localhost/last2/Bsite.php";

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded",
            'method'  => 'POST',
            'content' => http_build_query($postData)
        ]
    ];
    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);

    return $response;
}
?>