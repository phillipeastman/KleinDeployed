<?php

require 'vendor/autoload.php';

$router = new \Klein\Klein();

$router->respond('/hello-world', function () {
    return 'Hello World!';
});

$router->respond(function ($request, $response, $service, $app) use ($router) {
    // Handle exceptions => flash the message and redirect to the referrer
    $router->onError(function ($router, $err_msg) {
        $router->service()->flash($err_msg);
        $router->service()->back();
    });

    $app->db = new mysqli('klein.eastmanhome.com', 'eastmanf_web', 'w3b4pp', 'eastmanf_kleinuser');
});

$router->respond('POST', '/user/add', function ($request, $response, $service, $app) {
    $service->validateParam('firstName', 'Please enter a valid First Name')->isLen(1, 64)->isChars('a-zA-Z0-9-');
    $service->validateParam('lastName', 'Please enter a valid First Name')->isLen(1, 64)->isChars('a-zA-Z0-9-');
    $service->validateParam('loginName', 'Please enter a valid Login Name')->isLen(5, 64)->isChars('a-zA-Z0-9-');

    $params = $request->params();
    
    $stmt = $app->db->prepare('INSERT INTO user VALUES (NULL, ?, ?, ?)');
    if (!$stmt->bind_param("sss", $params['firstName'], $params['lastName'], $params['loginName'])) {
        echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    
    if (!$stmt->execute()) {
        echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
    }

    return $stmt->insert_id;
});

$router->respond('POST', '/user/[i:id]/address/add', function ($request, $response, $service, $app) {
    $service->validateParam('city', 'Please enter a valid city')->isLen(2, 32)->isChars('a-zA-Z0-9-');
    $service->validateParam('state', 'Please enter a valid state')->isLen(2, 32)->isChars('a-zA-Z0-9-');
    $service->validateParam('zip', 'Please enter a valid zip')->isLen(5, 6)->isChars('0-9-');

    $User_ID = $request->paramsNamed()->get('id');
    $params = $request->params();
    
    $stmt = $app->db->prepare('INSERT INTO address VALUES (NULL, ?, ?, ?, ?, ?, ?)');
    if (!$stmt->bind_param("issssi", $User_ID, $params['address1'], $params['address2'], $params['city'], $params['state'], $params['zip'])) {
        echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    
    if (!$stmt->execute()) {
        echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
    }

    return $stmt->insert_id;
});

$router->respond('GET', '/user/[i:id]/address', function ($request, $response, $service, $app) {
    $User_ID = $request->paramsNamed()->get('id');
    
    $stmt = $app->db->prepare('SELECT * FROM address WHERE User_ID = ?');
    if (!$stmt->bind_param("i", $User_ID)) {
        echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    
    if (!$stmt->execute()) {
        echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    
    $stmt->bind_result($id, $User_ID, $address1, $address2, $city, $state, $zip);
    $allAddresss = array();
    while ($stmt->fetch()) {
        $allAddresss[$id] = array($User_ID, $address1, $address2, $city, $state, $zip);
    }
    
    return json_encode($allAddresss);
});

$router->respond('GET', '/user/[a:fname]/[a:lname]/address', function ($request, $response, $service, $app) {
    $firstName = $request->paramsNamed()->get('fname');
    $lastName = $request->paramsNamed()->get('lname');
    
    $stmt = $app->db->prepare('SELECT address.* FROM address JOIN user ON address.User_ID = user.ID WHERE user.FirstName = ? AND user.LastName = ?');
    if (!$stmt->bind_param("ss", $firstName, $lastName)) {
        echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    
    if (!$stmt->execute()) {
        echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    
    $stmt->bind_result($id, $User_ID, $address1, $address2, $city, $state, $zip);
    $allAddresss = array();
    while ($stmt->fetch()) {
        $allAddresss[$id] = array($User_ID, $address1, $address2, $city, $state, $zip);
    }
    
    return json_encode($allAddresss);
});

$router->respond('GET', '/address/[i:id]', function ($request, $response, $service, $app) {
    $Address_ID = $request->paramsNamed()->get('id');
    
    $stmt = $app->db->prepare('SELECT * FROM address WHERE ID = ?');
    if (!$stmt->bind_param("i", $Address_ID)) {
        echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    
    if (!$stmt->execute()) {
        echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    
    $stmt->bind_result($id, $User_ID, $address1, $address2, $city, $state, $zip);
    $allAddresss = array();
    while ($stmt->fetch()) {
        $allAddresss[$id] = array($User_ID, $address1, $address2, $city, $state, $zip);
    }
    
    return json_encode($allAddresss);
});

$router->respond('GET', '/address/zip/[i]', function ($request, $response, $service, $app) {
    $Zip = $request->paramsNamed()->get(1);
    
    $stmt = $app->db->prepare('SELECT * FROM address WHERE Zip = ?');
    if (!$stmt->bind_param("i", $Zip)) {
        echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    
    if (!$stmt->execute()) {
        echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    
    $stmt->bind_result($id, $User_ID, $address1, $address2, $city, $state, $zip);
    $allAddresss = array();
    while ($stmt->fetch()) {
        $allAddresss[$id] = array($User_ID, $address1, $address2, $city, $state, $zip);
    }
    
    return json_encode($allAddresss);
});

$router->dispatch();
