<!DOCTYPE html>
<html>
<head>
    <title>Member Directory</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>
    <style>
        ul { list-style-type: none; }
    </style>
</head>
<body>
    <h1>Member Directory</h1>
    <ul id="memberTree">
        <?php
        // Fetch members and generate the tree structure (modify the database connection details)
        $conn = new PDO("mysql:host=localhost;dbname=gajendra", "root", "root");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        function fetchMembers($conn, $parentId = 0) {
            $stmt = $conn->prepare("SELECT * FROM Members WHERE ParentId = :parentId");
            $stmt->bindParam(':parentId', $parentId, PDO::PARAM_INT);
            $stmt->execute();
            $members = $stmt->fetchAll();
			

            if (count($members) > 0) {
                echo "<ul>";
                foreach ($members as $member) {
                    echo "<li>" . $member['Name'];
                    fetchMembers($conn, $member['Id']); // Recursive call
                    echo "</li>";
                }
                echo "</ul>";
            }
       
	    }
		

        fetchMembers($conn);

        $conn = null;
        ?>
    </ul>

    <!-- Add Member Button -->
    <button id="addMemberButton">Add Member</button>

    <!-- Add Member Popup -->
    <div id="addMemberPopup" style="display: none;">
        <h2>Add New Member</h2>
        <form id="addMemberForm">
            <label for="parentMember">Parent Member:</label>
			<br><br>
            <select id="parentMember" name="parentMember">
                <!-- Populate this dynamically via JavaScript -->
            </select>
			<br><br>
            <label for="memberName">Name:</label>
			<br><br>
            <input type="text" id="memberName" name="memberName">
			<br><br>
			<button type="close" id="closeMemberButton">Close</button>
            <button type="submit" id="saveMemberButton">Save Changes</button>
			
        </form>
    </div>

    <script>
        $(document).ready(function () {
            // Show the Fancybox popup when the "Add Member" button is clicked
            $('#addMemberButton').click(function () {
                // Load the parent dropdown options dynamically
                populateParentDropdown();

                $('#addMemberPopup').css('display', 'block');
                $('#addMemberPopup').fancybox();
            });

            function populateParentDropdown() {
                $.ajax({
                    type: 'GET',
                    url: 'get_parents.php', // Create this file to fetch parent members
                    success: function (data) {
                        var parents = JSON.parse(data);
                        var parentDropdown = $('#parentMember');

                        parentDropdown.empty();

                        // Add a default option for no parent
                        parentDropdown.append($('<option>', {
                            value: '0',
                            text: 'None'
                        }));

                        // Populate the dropdown with parent members
                        parents.forEach(function (parent) {
                            parentDropdown.append($('<option>', {
                                value: parent.Id,
                                text: parent.Name
                            }));
                        });
                    }
                });
            }

            $('#addMemberForm').submit(function (e) {
                e.preventDefault();
                // Validation for the member name input
                var memberName = $('#memberName').val();
                if (!memberName.match(/^[A-Za-z\s]+$/)) {
                    alert('Please enter a valid name with alphabetic characters and spaces only.');
                    return;
                }
                // Collect form data
                var formData = {
                    parentMember: $('#parentMember').val(),
                    memberName: memberName
                };
                // Send the data via an AJAX request to add_member.php
                $.ajax({
                    type: 'POST',
                    url: 'add_member.php', // Create this file for adding a new member
                    data: formData,
                    success: function (data) {
                        // Handle the response and append the new member to the tree structure
                        var newMember = JSON.parse(data);
                        var newMemberHTML = '<li>' + newMember.Name + '</li>';
                        var parentElement = $('#memberTree li:contains("' + newMember.ParentName + '")');
                        if (parentElement.find('ul').length === 0) {
                            parentElement.append('<ul>' + newMemberHTML + '</ul>');
                        } else {
                            parentElement.find('ul').append(newMemberHTML);
                        }
                        // Close the Fancybox popup
                        $.fancybox.close();
                    }
                });
            });
        });
    </script>
</body>
</html>
