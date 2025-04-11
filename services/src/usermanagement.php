<?php
//session_start();
require __DIR__ . '/../vendor/autoload.php';  // Include PHPMailer via Composer

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Psr7\UploadedFile;
use Slim\Psr7\Stream;
// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database connection
require_once __DIR__ . '/Database.php';
$db = (new Database())->getConnection();

// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../data');
$dotenv->load();

/** @var App $app */

$group->get('/fetch', function (Request $request, Response $response) use ($db) {
    $query = "SELECT ai.account_id, 
       CONCAT(pi.first_name, ' ', pi.middle_name, ' ', pi.last_name) AS full_name, 
       ai.email,
       ai.password, 
       ai.user_type, 
       ai.barangay_role, 
       ai. status, 
       ai.picture
       FROM accountinformation ai
       JOIN personalinformation pi ON pi.personal_id = ai.personal_id";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/fetchId/{id}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT ai.account_id, 
       CONCAT(pi.first_name, ' ', pi.middle_name, ' ', pi.last_name) AS full_name, 
       ai.email,
       ai.password, 
       ai.user_type, 
       ai.barangay_role, 
       ai. status, 
       ai.picture
       FROM accountinformation ai
       JOIN personalinformation pi ON pi.personal_id = ai.personal_id
       WHERE account_id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $args['id'], PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/search/{searchTerm}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT ai.account_id, 
       CONCAT(pi.first_name, ' ', pi.middle_name, ' ', pi.last_name) AS full_name, 
       ai.email,
       ai.password, 
       ai.user_type, 
       ai.barangay_role, 
       ai. status, 
       ai.picture
       FROM accountinformation ai
       JOIN personalinformation pi ON pi.personal_id = ai.personal_id
       WHERE CONCAT(pi.first_name, ' ', pi.middle_name, ' ', pi.last_name) LIKE :searchTerm";

    $stmt = $db->prepare($query);
    $searchTerm = $args['searchTerm'];
    $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->post('/createUser', function (Request $request, Response $response) use ($db) {
    try {
        // Get the parsed input from the request body
        $input = $request->getParsedBody();
        $files = $request->getUploadedFiles();

        $picture = $files['picture'];
    
        // Get the name value from the form
        $name = isset($input['firstname']) ? $input['firstname'] : '';

        // Sanitize the name to ensure it's a valid folder name (no spaces or special characters)
        $name = preg_replace("/[^a-zA-Z0-9-_]/", "_", $name);

        // Define the upload directory (relative path)
        $uploadDir = '../../users/' . $name . '/';

        // Ensure the directory for the user exists or create it
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create the folder with write permissions if it doesn't exist
        }

        // Allowed file types
        $allowedFileTypes = array(
            'application/msword', // .doc
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
            'image/jpeg', // .jpeg
            'image/png', // .png
            'application/pdf', // .pdf
        );

        // Function to handle file upload and return the file path 
        function handleFileUpload($file, $uploadDir, $label, $allowedFileTypes, $username)
        {
            if ($file) {
                // Get file details
                $fileName = $file->getClientFilename();
                $fileSize = $file->getSize();
                $fileType = $file->getClientMediaType();
                $fileError = $file->getError();

                // Log file error (if any)
                if ($fileError !== UPLOAD_ERR_OK) {
                    error_log("Error uploading file $fileName. Error code: $fileError");
                    return "Error uploading file $fileName. Error code: $fileError.";
                }

                // Validate file type
                if (!in_array($fileType, $allowedFileTypes)) {
                    error_log("Invalid file type for file $fileName. Only .jpeg, .png, and .pdf are allowed.");
                    return "Invalid file type for file $fileName. Only .jpeg, .png, and .pdf are allowed.";
                }

                // Create label directory if it doesn't exist
                $labelDir = $uploadDir . $label . '/';
                if (!is_dir($labelDir)) {
                    if (!mkdir($labelDir, 0777, true)) {
                        error_log("Failed to create directory: $labelDir");
                        return "Failed to create directory for file $fileName.";
                    }
                }

                // Generate a new filename using the username and a random number
                $randomNumber = mt_rand(1000, 9999); // Generate a random number between 1000 and 9999
                $newFileName = $username . "_" . $randomNumber . "." . pathinfo($fileName, PATHINFO_EXTENSION); // Use the extension of the original file

                // Define the destination path (file will be saved in 'users/username/username_1234.jpg')
                $destination = $labelDir . $newFileName;

                // Move the file to the destination folder
                $file->moveTo($destination);

                // Get the file type and create an image resource from the uploaded file
                $srcImage = null;
                $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                
                // Load the image depending on its file type (after moving it to the destination)
                switch(strtolower($extension)) {
                    case 'jpeg':
                    case 'jpg':
                        $srcImage = imagecreatefromjpeg($destination); // Use the file path
                        break;
                    case 'png':
                        $srcImage = imagecreatefrompng($destination); // Use the file path
                        break;
                    case 'gif':
                        $srcImage = imagecreatefromgif($destination); // Use the file path
                        break;
                    default:
                        return "Unsupported file type.";
                }

                // Check if image was loaded successfully
                if (!$srcImage) {
                    return "Failed to create image from file.";
                }

                // Get the original image dimensions
                $width = imagesx($srcImage);
                $height = imagesy($srcImage);

                // Define the new size (500x500)
                $newWidth = 500;
                $newHeight = 500;

                // Create a new blank image with the desired dimensions
                $destinationImage = imagecreatetruecolor($newWidth, $newHeight);

                // Resize the image
                imagecopyresized($destinationImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

                // Save the resized image to the destination
                switch(strtolower($extension)) {
                    case 'jpeg':
                    case 'jpg':
                        imagejpeg($destinationImage, $destination, 100); // Save as JPEG with max quality
                        break;
                    case 'png':
                        imagepng($destinationImage, $destination);
                        break;
                    case 'gif':
                        imagegif($destinationImage, $destination);
                        break;
                }

                // Clean up
                imagedestroy($srcImage);
                imagedestroy($destinationImage);

                // Return the relative path (for example, 'users/john_doe/username_1234.jpg')
                // Remove the leading '../../' from the path
                // Remove the leading '../../' from the path (only if it starts with it)
                if (substr($destination, 0, 6) === "../../") {
                    $destination = substr($destination, 6); // Remove the first 6 characters
                }
                return $destination;
            }

            // Return error if file is not uploaded properly
            return "No file uploaded.";
        }

        // Upload files and collect their paths
        $filePaths = [];
        $filePaths['file1'] = handleFileUpload($picture, $uploadDir, 'profilepicture', $allowedFileTypes, $input['firstname']);

        // Start the transaction
        $db->beginTransaction();

        $query = "INSERT INTO personalinformation (first_name, middle_name, last_name, gender) VALUES (:first_name, :middle_name, :last_name, :gender)";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':first_name', $input['firstname']);
        $stmt->bindValue(':middle_name', $input['middlename']);
        $stmt->bindValue(':last_name', $input['lastname']);
        $stmt->bindValue(':gender', $input['gender']);
        $stmt->execute();

        // Get the last inserted user ID (for linking to the order)
        $personalId = $db->lastInsertId();

        $query = "INSERT INTO accountinformation (personal_id, email, password, user_type, barangay_role, status, picture) VALUES (:personal_id, :email, :password, :user_type, :barangay_role, :status, :picture)";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':personal_id', $personalId);
        $stmt->bindValue(':email', $input['email']);
        $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
        $stmt->bindValue(':password', $hashedPassword);
        $stmt->bindValue(':user_type', $input['usertype']);
        $stmt->bindValue(':barangay_role', $input['brole']);
        $status = "Inactive";
        $stmt->bindValue(':status', $status);
        $picture = $filePaths['file1'];
        $stmt->bindValue(':picture', $picture);
        $stmt->execute();

        // Commit the transaction to make the changes permanent
        $db->commit();
        
        // Optionally, you can return a success message or result after execution
        $response->getBody()->write(json_encode(['message' => 'User created successfully.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
        // If there's a database error (PDOException), handle it here
        $response->getBody()->write(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);  // Internal Server Error
    } catch (Exception $e) {
        // Catch any other general exceptions
        $response->getBody()->write(json_encode(['error' => 'An error occurred: ' . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);  // Internal Server Error
    }
});

// Handle the preflight (OPTIONS) request
$group->options('/updateFullname/{id}', function ($request, $response, $args) {
    return $response->withHeader('Content-Type', 'application/json')
                    ->withHeader('Access-Control-Allow-Origin', '*')
                    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                    ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});

$group->put('/updateFullname/{id}', function (Request $request, Response $response, $args) use ($db) {
    try {
        // Get the parsed input from the request body
        $input = $request->getParsedBody();
        
        // Prepare the SQL query for updating the fullname
        $query = "UPDATE userstable SET fullname = :fullname WHERE id = :id";
        $stmt = $db->prepare($query);
        
        // Bind the parameters to the query
        $stmt->bindParam(':fullname', $input['editFullname']);
        $stmt->bindParam(':id', $args['id'], PDO::PARAM_INT);
        
        // Execute the query
        $stmt->execute();
        
        // Optionally, you can return a success message or result after execution
        $response->getBody()->write(json_encode(['message' => 'User updated successfully.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
        // If there's a database error (PDOException), handle it here
        $response->getBody()->write(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);  // Internal Server Error
    } catch (Exception $e) {
        // Catch any other general exceptions
        $response->getBody()->write(json_encode(['error' => 'An error occurred: ' . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);  // Internal Server Error
    }
    
});

// Handle the preflight (OPTIONS) request
$group->options('/updateEmail/{id}', function ($request, $response, $args) {
    return $response->withHeader('Content-Type', 'application/json')
                    ->withHeader('Access-Control-Allow-Origin', '*')
                    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                    ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});

$group->put('/updateEmail/{id}', function (Request $request, Response $response, $args) use ($db) {
    try {
        // Get the parsed input from the request body
        $input = $request->getParsedBody();
        
        // Prepare the SQL query for updating the fullname
        $query = "UPDATE accountinformation SET email = :email WHERE account_id = :id";
        $stmt = $db->prepare($query);
        
        // Bind the parameters to the query
        $stmt->bindParam(':email', $input['editUsername']);
        $stmt->bindParam(':id', $args['id'], PDO::PARAM_INT);
        
        // Execute the query
        $stmt->execute();
        
        // Optionally, you can return a success message or result after execution
        $response->getBody()->write(json_encode(['message' => 'User updated successfully.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
        // If there's a database error (PDOException), handle it here
        $response->getBody()->write(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);  // Internal Server Error
    } catch (Exception $e) {
        // Catch any other general exceptions
        $response->getBody()->write(json_encode(['error' => 'An error occurred: ' . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);  // Internal Server Error
    }
    
});

// Handle the preflight (OPTIONS) request
$group->options('/updateUsertype/{id}', function ($request, $response, $args) {
    return $response->withHeader('Content-Type', 'application/json')
                    ->withHeader('Access-Control-Allow-Origin', '*')
                    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                    ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});

$group->put('/updateUsertype/{id}', function (Request $request, Response $response, $args) use ($db) {
    try {
        // Get the parsed input from the request body
        $input = $request->getParsedBody();
        
        // Prepare the SQL query for updating the fullname
        $query = "UPDATE accountinformation SET user_type = :user_type WHERE account_id = :account_id";
        $stmt = $db->prepare($query);
        
        // Bind the parameters to the query
        $stmt->bindParam(':user_type', $input['editProfile']);
        $stmt->bindParam(':account_id', $args['id'], PDO::PARAM_INT);
        
        // Execute the query
        $stmt->execute();
        
        // Optionally, you can return a success message or result after execution
        $response->getBody()->write(json_encode(['message' => 'User updated successfully.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
        // If there's a database error (PDOException), handle it here
        $response->getBody()->write(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);  // Internal Server Error
    } catch (Exception $e) {
        // Catch any other general exceptions
        $response->getBody()->write(json_encode(['error' => 'An error occurred: ' . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);  // Internal Server Error
    }
    
});

// Handle the preflight (OPTIONS) request
$group->options('/updateStatus/{id}', function ($request, $response, $args) {
    return $response->withHeader('Content-Type', 'application/json')
                    ->withHeader('Access-Control-Allow-Origin', '*')
                    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                    ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});

$group->put('/updateStatus/{id}', function (Request $request, Response $response, $args) use ($db) {
    try {
        // Get the parsed input from the request body
        $input = $request->getParsedBody();
        
        // Prepare the SQL query for updating the fullname
        $query = "UPDATE accountinformation SET status = :status WHERE account_id = :account_id";
        $stmt = $db->prepare($query);
        
        // Bind the parameters to the query
        $stmt->bindParam(':status', $input['editStatus']);
        $stmt->bindParam(':account_id', $args['id'], PDO::PARAM_INT);
        
        // Execute the query
        $stmt->execute();
        
        // Optionally, you can return a success message or result after execution
        $response->getBody()->write(json_encode(['message' => 'User updated successfully.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
        // If there's a database error (PDOException), handle it here
        $response->getBody()->write(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);  // Internal Server Error
    } catch (Exception $e) {
        // Catch any other general exceptions
        $response->getBody()->write(json_encode(['error' => 'An error occurred: ' . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);  // Internal Server Error
    }
    
});

// Handle the preflight (OPTIONS) request
$group->options('/updatePassword/{id}', function ($request, $response, $args) {
    return $response->withHeader('Content-Type', 'application/json')
                    ->withHeader('Access-Control-Allow-Origin', '*')
                    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                    ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});

$group->put('/updatePassword/{id}', function (Request $request, Response $response, $args) use ($db) {
    try {
        // Get the parsed input from the request body
        $input = $request->getParsedBody();
        $password = $input['editPassword'];
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Prepare the SQL query for updating the fullname
        $query = "UPDATE userstable SET password = :password WHERE id = :id";
        $stmt = $db->prepare($query);
        
        // Bind the parameters to the query
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':id', $args['id'], PDO::PARAM_INT);
        
        // Execute the query
        $stmt->execute();
        
        // Optionally, you can return a success message or result after execution
        $response->getBody()->write(json_encode(['message' => 'User updated successfully.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
        // If there's a database error (PDOException), handle it here
        $response->getBody()->write(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);  // Internal Server Error
    } catch (Exception $e) {
        // Catch any other general exceptions
        $response->getBody()->write(json_encode(['error' => 'An error occurred: ' . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);  // Internal Server Error
    }
    
});

// Handle the preflight (OPTIONS) request
$group->options('/updatePicture/{id}', function ($request, $response, $args) {
    return $response->withHeader('Content-Type', 'application/json')
                    ->withHeader('Access-Control-Allow-Origin', '*')
                    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                    ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});

$group->put('/updatePicture/{id}', function (Request $request, Response $response, $args) use ($db) {
    try {
        // Get the parsed input from the request body
        $files = $request->getUploadedFiles();

        // Check if the file exists and there is no upload error
        //if (!isset($files['pictureInp'])) {
            //$response->getBody()->write(json_encode(["error" => "No file uploaded."]));
            //return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        //}

        // Check if the file exists and there is no upload error
        if (!isset($files['file1']) || $files['file1']->getError() !== UPLOAD_ERR_OK) {
            $response->getBody()->write(json_encode( ["error" => "No file uploaded or there was an upload error."]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $uploadedFile = $files['file1'];

        if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
            $response->getBody()->write(json_encode(["error" => "File upload failed. Error code: " . $uploadedFile->getError()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Set the root upload directory
        $rootUploadDir = '../../users/';

        // Ensure the upload directory exists or create it
        if (!is_dir($rootUploadDir)) {
            mkdir($rootUploadDir, 0777, true); // Create the directory with full permissions if it doesn't exist
        }

        $fullname = 'johndoe';
        // Get the name value from the form
        $name = isset($fullname) ? $fullname : '';

        // Sanitize the name to ensure it's a valid folder name (no spaces or special characters)
        $name = preg_replace("/[^a-zA-Z0-9-_]/", "_", $name);

        // Create the root upload directory based on the name
        $uploadDir = $rootUploadDir . $name . '/';

        // Ensure the name-based directory exists or create it
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create the folder with write permissions
        }

        // Check if there is an existing file in the folder and delete it
        $existingFiles = glob($folder . "/*.jpg");
        if (count($existingFiles) > 0) {
            unlink($existingFiles[0]); // Delete the first file in the folder
        }

        // Move the uploaded file to the user's folder
        $fileName = mt_rand(100, 999) . ".jpg";
        $destination = $folder . "/" . $fileName;

        $uploadedFile->moveTo($destination);

        // Resize the uploaded image to 500x500
        list($width, $height) = getimagesize($destination);
        $srcImage = imagecreatefromjpeg($destination);
        $destinationImage = imagecreatetruecolor(500, 500);
        imagecopyresized($destinationImage, $srcImage, 0, 0, 0, 0, 500, 500, $width, $height);

        // Save the resized image
        imagejpeg($destinationImage, $destination);

        // Free up memory
        imagedestroy($srcImage);
        imagedestroy($destinationImage);

        // Return a success response
        $response->getBody()->write(json_encode(["message" => "File uploaded and resized successfully.", "file" => $destination]));
        return $response->withHeader('Content-Type', 'application/json');


        // Prepare the SQL query for updating the fullname
        $query = "UPDATE userstable SET password = :password WHERE id = :id";
        $stmt = $db->prepare($query);
        
        // Bind the parameters to the query
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':id', $args['id'], PDO::PARAM_INT);
        
        // Execute the query
        $stmt->execute();
        
        // Optionally, you can return a success message or result after execution
        $response->getBody()->write(json_encode(['message' => 'User updated successfully.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
        // If there's a database error (PDOException), handle it here
        $response->getBody()->write(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);  // Internal Server Error
    } catch (Exception $e) {
        // Catch any other general exceptions
        $response->getBody()->write(json_encode(['error' => 'An error occurred: ' . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);  // Internal Server Error
    }
    
});

// Handle the preflight (OPTIONS) request
$group->options('/login', function ($request, $response, $args) {
    return $response->withHeader('Content-Type', 'application/json')
                    ->withHeader('Access-Control-Allow-Origin', '*')
                    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                    ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});

$group->post('/login', function (Request $request, Response $response) use ($db) {

    try {
        // Get the parsed input from the request body
        $input = $request->getParsedBody();

        // Query to check if the username (or email) exists
        $query = "SELECT * FROM userstable WHERE username = :username";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $input['email']);
        $stmt->execute();

        // Fetch the result (Note: we expect a single result here)
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if any user was found
        if ($result === false) {
            // Username/email not found
            echo json_encode(["error" => "Username/email not found."]);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        } else {
            // Username (or email) found, fetch the password
            $password = $input['password'];
            $hashedPassword = $result['password'];

            // Proceed with password verification and other logic
            if (password_verify($password, $hashedPassword)) {

                session_start();
                // Store user data in the session
                $_SESSION["id"] = "22";
                $_SESSION["username"] = $result['username'];
                $_SESSION["profile"] = "Administrator";
                $_SESSION["picture"] = $result["picture"];
                
                // Store IP and user agent in session for session hijacking protection
                $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
                $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

                // Return successful login message
                echo json_encode(["message" => "Login successful!"]);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            } else {
                // Invalid password
                echo json_encode(["error" => "Invalid username or password."]);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
            }
        }
    } catch (Exception $e) {
        // Log the error message for debugging (you can log to a file or error logger)
        error_log("Error: " . $e->getMessage());

        // Return a generic error response
        echo json_encode(["error" => "An unexpected error occurred. Please try again."]);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);  // Internal Server Error
    }

});

// Handle the preflight (OPTIONS) request
$group->options('/verify', function ($request, $response, $args) {
    return $response->withHeader('Content-Type', 'application/json')
                    ->withHeader('Access-Control-Allow-Origin', '*')
                    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                    ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});

$group->post('/verify', function (Request $request, Response $response) use ($db) {
    // Get the parsed input from the request body
    $input = $request->getParsedBody();
    //session_start();
    $user_id = "22";  // You should ensure that the session is started, and the user is logged in

    // Fetch stored OTP and expiration from the database
    $stmt = $db->prepare("SELECT otp, otp_expiration FROM userstable WHERE id = :id");
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    // Proceed with OTP validation only if OTP exists and is not expired
    if ($data['otp'] && strtotime($data['otp_expiration']) > time()) {
        // Verify if the entered OTP matches
        if ($input['otp'] == $data['otp']) {
            // OTP verified, clear OTP from the database (for security)
            $stmt = $db->prepare("UPDATE userstable SET otp = NULL, otp_expiration = NULL WHERE id = :id");
            $stmt->bindParam(':id', $user_id);
            $stmt->execute();

            // Optionally, set a session or cookie to indicate user is logged in
            $_SESSION['logged_in'] = 'ok';

            // Redirect to a logged-in area (dashboard or homepage)
            //header("Location: template.php"); // Use this if you want to redirect after successful login
            //exit();

            // Return successful login message
            $response->getBody()->write(json_encode(['message' => 'User updated successfully.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            $response->getBody()->write(json_encode(['message' => 'Error']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        }
    } else {
        $response->getBody()->write(json_encode(['message' => 'error 1']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

});