<?php 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");


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


$sql = "CREATE TABLE IF NOT EXISTS guvi_users (
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    mongodbId VARCHAR(255) NOT NULL,
    PRIMARY KEY (email)
)";

$email = $_POST['email'];
$password = $_POST['password'];
$password = password_hash($password, PASSWORD_DEFAULT); //PASSWORD_DEFAULT is used for hashing password with defalut algorithm


if (!mysqli_query($conn, $sql)) 
{
    echo "Error creating table: " . mysqli_error($conn);
}

//check if the user is already exist
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
if (mysqli_num_rows($result) > 0) {
    $response = array(
        "status" => "user_error",
        "message" => "User already exists"
    );
    echo json_encode($response);
    exit();
}
}

// connecting to mongodb localhost
$uri = 'mongodb://localhost:27017/';
$manager = new MongoDB\Driver\Manager($uri);

$database = "guvi";
$collection = "users";

$bulk = new MongoDB\Driver\BulkWrite;

$document = [
    'email' => $email,
    'dob' => '',
    'age' => '',
    'contact'=>'',
];

$bulk = new MongoDB\Driver\BulkWrite;
$_id = $bulk->insert($document);
$writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
$result = $manager->executeBulkWrite("$database.$collection", $bulk, $writeConcern);


$mongoId = (string)$_id;

// SQL query with placeholders
$sql = "INSERT INTO guvi_users (email, password, mongodbId) VALUES (?, ?, ?)";

// Prepare the statement
$stmt = $conn->prepare($sql);

// $sql = "INSERT INTO guvi_users (email, password ,mongodbId) VALUES ('$email', '$password','$mongoId')";
if ($stmt) {
    // Bind parameters
    $stmt->bind_param("sss", $email, $password, $mongoId);
    // "sss" represents three string parameters;

    // Execute the statement
    $stmt->execute();

    // Check if the insertion was successful
    if ($stmt->affected_rows > 0) {
        $response = ['status' => 'success', 'message' => 'Registered successfully'];
    } else {
        $response = ['status' => 'error', 'message' => 'Registration failed'];
    }

    // Close the statement
    $stmt->close();
}
else {
    // Handle the case where the statement preparation fails
    echo "Error preparing statement: " . $conn->error;
}

echo json_encode($response);

?>