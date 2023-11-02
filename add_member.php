<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $parentMember = $_POST["parentMember"];
    $memberName = $_POST["memberName"];

    try {
        $conn = new PDO("mysql:host=localhost;dbname=gajendra", "root", "root");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Perform validation (for example, you can check for empty name here)

        // Insert the new member into the database
        $stmt = $conn->prepare("INSERT INTO 'members' ('Name', 'ParentId') VALUES (':name', ':parent')");
        $stmt->bindParam(':name', $memberName);
        $stmt->bindParam(':parent', $parentMember);
        $stmt->execute();
		

        // Retrieve the ID of the newly inserted member
        $newMemberId = $conn->lastInsertId();

        // Fetch the name of the parent member
        $parentName = "None";
        if ($parentMember != 0) {
            $stmt = $conn->prepare("SELECT Name FROM members WHERE Id = :id");
            $stmt->bindParam(':id', $parentMember, PDO::PARAM_INT);
            $stmt->execute();
            $parent = $stmt->fetch();
            $parentName = $parent['Name'];
        }

        // Create an array with the new member's details
        $newMember = [
            "Id" => $newMemberId,
            "Name" => $memberName,
            "ParentName" => $parentName,
        ];

        echo json_encode($newMember);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}
?>
