<?php

function classifyComplaint($text){

    $text = strtolower($text);

    $categories = [

        "Theft" => ["stolen","theft","robbery","snatched","bike stolen","phone stolen"],

        "Cyber Crime" => ["hack","hacked","otp","fraud online","cyber","scam","phishing"],

        "Harassment" => ["abuse","threat","harass","stalking","blackmail"],

        "Fraud" => ["fraud","cheated","money taken","fake","scam"],

        "Accident" => ["accident","crash","hit","injury","collision"],

        "Missing Person" => ["missing","not found","kidnap","lost person"]
    ];

    foreach($categories as $category => $keywords){
        foreach($keywords as $word){
            if(strpos($text, $word) !== false){
                return $category;
            }
        }
    }

    return "Other";
}
?>