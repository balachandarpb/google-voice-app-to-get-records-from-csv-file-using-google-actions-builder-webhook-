<?php 

// what is the customer id for customer Mark Jensen?
// what is the customer id for company goleads?

// Process only when method is POST

    // $requestBody = file_get_contents('test.json');
    // $json = json_decode($requestBody);

$method = $_SERVER['REQUEST_METHOD'];

    // Process only when method is POST
if($method == 'POST')
{
    $requestBody = file_get_contents('php://input');
    $json = json_decode($requestBody);

    $searchStr = $json->intent->params->customer_phone->resolved;
    if ($searchStr != "")
    {
        
        $final_result = array();
        foreach (glob("./*.csv") as $file) {
            $file = fopen($file, "r");
            if($file) {
                $header = fgetcsv($file, 10000, ",");
                if (in_array("First Name", $header)) {
                    $FirstNames = "First Name";
                }
                if (in_array("Last Name", $header)) {
                    $LastNames = "Last Name";
                }
                if (in_array("Contact name", $header)) {
                    $CntName = "Contact name";
                }
                if (in_array("Name", $header)) {
                    $FullName = "Name";
                }
                while ($data = fgetcsv($file, 10000, ",")) {
                    //$final_result[$data[1]] = $data[0];
                    $final_result[] = array_combine($header, array_values($data));
                    $foundKey = array_search($searchStr, array_column($final_result, "Phone")); 
                    if($FirstNames !="" && $LastNames!=""){
                        $foundFirstNames = $final_result[$foundKey][$FirstNames]; 
                        $foundLastNames = $final_result[$foundKey][$LastNames];
                    }
                    elseif( $CntName !=""){
                        $foundCntName = $final_result[$foundKey][$CntName]; 
                    }
                    elseif( $FullName !=""){
                        $foundFullName = $final_result[$foundKey][$FullName];
                    }
                    
                    
                }
                
                if($foundKey !=""){
                    if($foundFirstNames !="" && $foundLastNames !=""){
                        $response = array ('prompt' => array ('firstSimple' => array ( 'speech' => "Phone Number $searchStr is $foundFirstNames $foundLastNames ", 'text' => "Phone Number $searchStr is $foundFirstNames $foundLastNames ")));
                    }
                    elseif($foundCntName !=""){
                        $response = array ('prompt' => array ('firstSimple' => array ( 'speech' => "Phone Number $searchStr is $foundCntName", 'text' => "Phone Number $searchStr is $foundCntName")));
                    }
                    elseif($foundFullName !=""){
                        $response = array ('prompt' => array ('firstSimple' => array ( 'speech' => "Phone Number $searchStr is $foundFullName", 'text' => "Phone Number $searchStr is $foundFullName")));
                    }
                    
                }
                else
                {
                    $response = array ('prompt' => array ('firstSimple' => array ( 'speech' => "Phone Number $searchStr is not in data ", 'text' => "Phone Number $searchStr is not in data ")));
                }
                
                
            }
        }
        echo json_encode($response);

    }
    else
    {
        if(array_key_exists('customer_name', $json->intent->params)) {
            $customer_name = $json->intent->params->customer_name->resolved;
            $ctypevalue = "cu";
            $csearchtextvalue = $customer_name;
        }
        elseif(array_key_exists('customer_company', $json->intent->params)) 
        {
            $customer_company = $json->intent->params->customer_company->resolved;
            $ctypevalue = "co";
            $csearchtextvalue = $customer_company;
        }
        if($csearchtextvalue !=""){
            $data = array(
                "type" => $ctypevalue,      
                "searchtext" => $csearchtextvalue
                );
        }
        // $data = array(
        //     "type" => $ctypevalue,      
        //     "searchtext" => $csearchtextvalue
        //     );

        $url = "Here you have to give APi url";
        $query_url = sprintf("%s?%s", $url, http_build_query($data));
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $query_url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        $http_code = curl_getinfo($curl , CURLINFO_HTTP_CODE);
        $result = curl_exec($curl);
        
        header('Content-type: application/json');
        
        $someArray = json_decode($result, true); 
        $recordCount = $someArray["RecordCount"];
        $returnvalue = $someArray["ReturnValue"];
        $status = $someArray["Status"];
        $message = $someArray["StatusText"];  
        
        if($status == "suc")
        {
            if($recordCount == 1){
                $response = array ('prompt' => array ('firstSimple' => array ( 'speech' => "Customerid for $csearchtextvalue is $returnvalue ", 'text' => "Customerid for $csearchtextvalue is $returnvalue ")));
            }
            elseif($recordCount > 1){
                $response = array ('prompt' => array ('firstSimple' => array ( 'speech' => "Customerid for $csearchtextvalue has $recordCount records available ", 'text' => "Customerid for $csearchtextvalue has $recordCount records available ")));
            }
            
        }
        elseif($status == "na")
        {
            $response = array ('prompt' => array ('firstSimple' => array ( 'speech' => "Sorry, Not connecting GoLeads. Try again later sometime", 'text' => "Sorry, Not connecting GoLeads. Try again later sometime")));
        }
        elseif($status == "err")
        {
            $response = array ('prompt' => array ('firstSimple' => array ( 'speech' => "Sorry, Not connecting GoLeads. Try again later sometime", 'text' => "Sorry, Not connecting GoLeads. Try again later sometime")));
        }
        echo json_encode($response);
        curl_close($curl);
       
    }

}



?>