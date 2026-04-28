<?php
$data = json_encode(['events'=>[['personID'=>'001','eventDate'=>'2026/01/27','eventTime'=>'18:45:00','deviceID'=>'1','deviceName'=>'Test']]]);
$ch = curl_init('https://diamondworldltd.hrsheba.com/api/biometric/hunduri-sync');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer hunduri-sync-secret-2026'));
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "HTTP response code: $httpcode\n";
echo "Response body: $response\n";
curl_close($ch);
