<?php
#View
$VIEW["name"] = $routes[0];
$VIEW["title"] = $VIEW["vars"]["navMain"][$routes[0]];

$notifications = new \App\Http\Controllers\AppNotificationController;
$VIEW["VARS"]["notifications"] = $notifications->getNotifications(NULL, 1);
?>