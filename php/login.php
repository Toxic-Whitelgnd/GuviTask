<?php 

// sql server connected to localhost in development mode
// $servername = "localhost:3307";
// $username = "root";
// $password = "12345678";
// $database = "guvi";

// Connect to the database in production mode
$servername = "sql12.freesqldatabase.com";
$username = "sql12666788";
$password = "sbaeHC6ig1";
$database = "sql12666788";
// port 3306;


$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$redis = new Redis();
// $redis->connect('redis-18192.c281.us-east-1-2.ec2.cloud.redislabs.com', 18192);
// $redis->auth('IzDni3FPBUerad8Q3F6qUdbOvBr42uBL');

$redis->connect('localhost', 6379);


$email = $_POST["email"];
$password = $_POST["password"];

// SQL query with a placeholder
$sql = "SELECT * FROM guvi_users WHERE email = ?";

// Prepare the statement
$stmt = $conn->prepare($sql);

if ($stmt) {
    // Bind the parameter
    $stmt->bind_param("s", $email); // "s" represents a string

    // Execute the statement
    $stmt->execute();

    // Get the result set
    $result = $stmt->get_result();


// $result = mysqli_query($conn, "SELECT * FROM guvi_users WHERE email = '$email'");
if($result == FALSE){
    $response = array(
        "status" => "error",
        "message" => "User not found"
    );
    echo json_encode($response);
}
elseif (mysqli_num_rows($result) == 0) {
    $response = array(
        "status" => "error",
        "message" => "User not found"
    );
    echo json_encode($response);
} else {
    $row = mysqli_fetch_assoc($result);

    if(password_verify($password, $row['password'])){
        $session_id = uniqid();
        $redis->set("session:$session_id", $email);
        $redis->expire("session:$session_id", 10*60);
       

        $payload = array(
            "email" => $row['email'],
        );
      
        $response = array(
            "status" => "success",
            "message" => "Login successful",
            'session_id' => $session_id
        );
        echo json_encode($response);
    } else {
        $response = array(
            "status" => "error",
            "message" => "Incorrect password"
        );
        echo json_encode($response);
    }
}
}

?>