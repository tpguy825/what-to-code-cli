<?php

$GLOBALS["debug"] = false;
$GLOBALS["attempts"] = 0;

// arguments
if(isset($argv[1])) {
    if($argv[1] === "--info") {
        die("Random Idea Generator by tpguy825\nUses the https://what-to-code.com api\nVersion 1.0.0        ");
    } elseif($argv[1] === "--help") {
        $file = explode('/', __FILE__)[count(explode('/', __FILE__))-1];
        echo("Random Idea Generator by tpguy825 v1.0.0\n\nOptions:\n\n--debug     Starts debug mode, which shows timings and steps\n--info      Info about the program\n--multiple  Get multiple ideas - Note: use 'php ".$file." --multiple > ideas.txt' to save the ideas to 'ideas.txt'\n--help      Help menu\n--post      Post an idea");
        die();
    } elseif($argv[1] === "--debug") {
        $GLOBALS["debug"] = true;
        echo "Debug mode enabled\n\n";
        echo getIdea();
    } elseif($argv[1] === "--post") {
        postIdea($argv);
        die();
    } elseif($argv[1] === "--multiple") {
        if(isset($argv[2])) {
            $ideatimes = 0;
            while($ideatimes < $argv[2]) {
                echo getIdea()."\n\n";
                $ideatimes++;
            }
        } else {
            die("Please specify how many you want to get");
        }
        
    } else {
        die("Invalid args, use --help");
    }
} else {
    echo getIdea();
}



function postidea($argv) {
    die("This feature is under construction, try again later");
    // $result = array();
    // $tagscount2 = 0;
    // $complete = false;
    // if(isset($argv[2]) && $argv[2] === "--test") {
    //     $title = "Test";
    //     $description = "Test description";
    //     $tags = array("this ", "is ", "a ", "test");
    // } else {
    //     $title = readline("Title: ");
    //     $description = readline("Description(use \\n for newlines): ");
    //     $tags = explode("#", readline("Tags(eg. #this #is #cool): "));
    // }
    // if(count($tags) > 6) {
    //     die('Too many tags! Maximum 6');
    // }
    // while(!$complete) {
    //     if(!isset($tags[$tagscount2])) {
    //         $complete = true;
    //     } else {
    //         $result[$tagscount2]["value"] = trim($tags[$tagscount2]);
    //         $tagscount2++;
    //     }
    // }
    // $response = array(
    //     "title" => $title,
    //     "description" => $description,
    //     "tags" => $result
    // );
    // $headers = array('Host: what-to-code.com', 'Cookie: token=b9e0916b-77fd-4d9d-81f1-e39b9f6426f7; likes=%7B%7D', 'Content-Length: 174', 'Sec-Ch-Ua: " Not A;Brand";v="99", "Chromium";v="100", "Opera GX";v="86"', 'Accept: application/json, text/plain, */*', 'Content-Type: application/json;charset=UTF-8', 'Sec-Ch-Ua-Mobile: ?0', 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.127 Safari/537.36 OPR/86.0.4363.70', 'Sec-Ch-Ua-Platform: "Windows"', 'Sec-Gpc: 1', 'Origin: https://what-to-code.com', 'Sec-Fetch-Site: same-origin', 'Sec-Fetch-Mode: cors', 'Sec-Fetch-Dest: empty', 'Referer: https://what-to-code.com/submit', 'Accept-Encoding: gzip, deflate', 'Accept-Language: en-GB,en-US;q=0.9,en;q=0.8', 'Connection: close');
    // $post = file_get_contents("http://what-to-code.com/api/ideas",false, stream_context_create(array('http' => array('method'=>"POST", "header" => $headers, "content"=>json_encode($response)))));
    // if($post == false) {
    //     echo "Failed to send idea";
    // } else {
    //     echo "Success!";
    // }
    // echo "\nResponse from server: \n";
    // print_r($post);
    // die();
}

/**
 * @param string $toprint String to print
 * Echoes a string only if --debug flag is set
 */
function debug(string $toprint ):void {
    if($GLOBALS["debug"]) {
        echo $toprint;
    }
}

/**
 * @return string Idea
 */
function getIdea():string {

/** @var float $total Total time taken to execute code */
$total = 0.00;

$eta=-hrtime(true);
debug("started\n");

$idea = getIdeaFromWebsite();

$eta+=hrtime(true);
$total = $total + (($eta/1e+6)/10000);
debug("got idea after ".calculatetime($eta/1000)." seconds\n");

$message = "
Title: #{title}
Description: #{description}
Tags(#{tagscount}): #{tags}
ID: #{id}
Likes: #{likes}
";

if(!isset($idea['tags'])) {
    $istheretags = false;
    $message = str_replace("#{tags}", " No tags", $message);
    $tagscount = 0;
} else {
    $istheretags = true;
    $tagscount = count($idea['tags']);
}

$eta+=hrtime(true);
$total = $total + (($eta/1e+6)/10000);
debug("starting replacement after ".calculatetime($total)." seconds\n");
$message = str_replace("#{title}", $idea['title'], $message);
$message = str_replace("#{description}", $idea['description'], $message);
$message = str_replace("#{tagscount}", strval($tagscount), $message);
$tags = replacetags("#{tags}", $idea['tags'], $message, $istheretags, $total, $eta);
$message = $tags["replaced"];
$total = $tags["total"];
$eta = $tags["eta"];
$message = str_replace("#{likes}", $idea['likes'], $message);
$message = str_replace("#{id}", $idea['id'], $message);
$eta+=hrtime(true);
$total = $total + (($eta/1e+6)/1000);
debug("finished replacement after ".calculatetime($total)." seconds\n");
debug("Total time taken: ".calculatetime($total)." seconds\n");
return $message;

}

/**
 * @param string $tofind
 * @param array $decodedtags
 * @param string $replacein
 * @param bool $tagsset
 * @param float $tagstotal
 * @param mixed $eta
 * 
 * @return array
 */
function replacetags($tofind, $decodedtags, $replacein, $tagsset, $tagstotal, $eta) {
    /** @var bool $complete */
    $complete = false;
    $eta+=hrtime(true);
$tagstotal = $tagstotal + (($eta/1e+6)/10000);
debug("replacing tags after ".calculatetime($tagstotal)." seconds\n");
    if(!isset($decodedtags[0]) or !$tagsset) {
        debug("no tags\n");
        
    $eta+=hrtime(true);
    $tagstotal = $tagstotal + (($eta/1e+6)/10000);
    debug("finished replacing tags after ".calculatetime($tagstotal)." seconds\n");
        $replaced = str_replace("#{tags}", "No tags", $replacein);
        return array("replaced" => $replaced, "total" => $tagstotal, "eta" => $eta);
    }
    $tagscount = 0;

while(!$complete) {
    if(!isset($decodedtags[$tagscount])) {
        $complete = true;
    } else {
        $result[$tagscount] = $decodedtags[$tagscount]['value'];
        $tagscount++;
    }
}
$eta+=hrtime(true);
$tagstotal = $tagstotal + (($eta/1e+6)/10000);
debug("finished replacing tags after ".calculatetime($tagstotal)." seconds\n");
if(isset($result)) {
    $replaced = str_replace($tofind, "#".implode(" #", $result), $replacein);
} else {
    $replaced = str_replace($tofind, "No tags", $replacein);
    $eta+=hrtime(true);
    $tagstotal = $tagstotal + (($eta/1e+6)/10000);
    debug("finished replacing tags after ".calculatetime($tagstotal)." seconds\n");
    return array("replaced" => $replaced, "total" => $tagstotal, "eta" => $eta);
}

return array("replaced" => $replaced, "total" => $tagstotal, "eta" => $eta);
}

function readline($prompt = null){
    if($prompt !== null){
        echo $prompt;
    }
    $fp = fopen("php://stdin","r");
    $line = rtrim(fgets($fp, 1024));
    return $line;
}

/**
 * @param float $time
 * 
 * @return float
 */
function calculatetime($time) {
    if(php_uname("s") === "Windows NT") {
        return $time/10000000;
    } else {
        return $time/10000;
    }
}

/**
 * @return array
 */
function getIdeaFromWebsite() {
    $idea = @file_get_contents("https://what-to-code.com/api/ideas/random");
    if($idea === false) {
        echo "**** Too many requests, waiting 2 seconds... ****\n";
        // Uncomment the line below to see the amount of times it attemped to get an idea before receving a 501
        // echo $GLOBALS['attempts'];
        sleep(2);
        return getIdeaFromWebsite();
    } else {
        $GLOBALS['attempts']++;
        return json_decode($idea, true);
    }
}