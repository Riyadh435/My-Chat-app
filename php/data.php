<?php
    // Section for connected people (always visible)
    $output .= '<div class="connected-people" style="display: flex; overflow-x: auto; padding: 15px 10px; gap: 15px; border-bottom: none; align-items: center; justify-content: flex-start;">
                    <style>
                        /* Hide the scrollbar */
                        .connected-people::-webkit-scrollbar {
                            display: none; /* Hides the scrollbar */
                        }
                        .connected-people {
                            -ms-overflow-style: none;  /* For Internet Explorer */
                            scrollbar-width: none;   
                            border-bottom: none  /* For Firefox */
                        }
                    </style>';
    mysqli_data_seek($query, 0); // Reset query pointer
    while ($row = mysqli_fetch_assoc($query)) {
        // Check user online status
        $offline = ($row['status'] == "Offline now") ? "offline" : "online";
        $status_color = ($offline == "offline") ? "gray" : "green";

        $output .= '<a href="chat.php?user_id='. $row['unique_id'] .'" class="connected-profile" style="flex-shrink: 0; text-align: center; text-decoration: none; position: relative; display: flex; flex-direction: column; align-items: center; gap: 5px; margin-bottom: 0;">
                        <img src="php/images/'. $row['img'] .'" alt="" class="profile-pic" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;">
                        <span style="font-size: 14px; font-weight: normal; color: #333; line-height: 1.2;">'. $row['fname'] .'</span>
                        <div class="status-dot" style="width: 12px; height: 12px; border-radius: 50%; background-color: '. $status_color .'; position: absolute; bottom: 5px; right: 10px; border: none;"></div>
                    </a>';
    }
    $output .= '</div>';  // End connected people section

    // Section for conversations (with smooth scrolling animation)
    $output .= '<div class="conversation-section" style="min-height: 200px;">';  // Add a wrapper for conversations
    mysqli_data_seek($query, 0); // Reset query pointer again
    $has_conversations = false;  // Track if there are any conversations

    while ($row = mysqli_fetch_assoc($query)) {
        // Check if the logged-in user has chatted with this user
        $sql2 = "SELECT * FROM messages WHERE (incoming_msg_id = {$row['unique_id']} 
                OR outgoing_msg_id = {$row['unique_id']}) AND (outgoing_msg_id = {$outgoing_id} 
                OR incoming_msg_id = {$outgoing_id}) ORDER BY msg_id DESC LIMIT 1";
        $query2 = mysqli_query($conn, $sql2);
        $row2 = mysqli_fetch_assoc($query2);

        // Skip users with no conversations
        if (mysqli_num_rows($query2) == 0) {
            continue;
        }

        $has_conversations = true; // Set to true if a conversation exists

        // Prepare message preview
        $result = $row2['msg'];
        $msg = (strlen($result) > 28) ? substr($result, 0, 28) . '...' : $result;

        // Determine if the message is from the logged-in user
        $you = "";
        if (isset($row2['outgoing_msg_id'])) {
            $you = ($outgoing_id == $row2['outgoing_msg_id']) ? "You: " : "";
        }

        // Check user online status for conversation
        $offline = ($row['status'] == "Offline now") ? "offline" : "online";
        $status_color = ($offline == "offline") ? "gray" : "green";

        // Display conversation with active status
        $output .= '<a href="chat.php?user_id='. $row['unique_id'] .'">
                        <div class="content" style="display: flex; align-items: center; padding: 12px 10px; gap: 10px; border-bottom: none; scroll-behavior: smooth;">
                            <img src="php/images/'. $row['img'] .'" alt="" class="profile-pic" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
                            <div class="details" style="flex-grow: 1;">
                                <span style="font-size: 16px; font-weight: normal; color: #333;">'. $row['fname']. " " . $row['lname'] .'</span>
                                <p style="font-size: 14px; color: #666; line-height: 1.2;">'. $you . $msg .'</p>
                            </div>
                        </div>
                        <!-- Status Dot showing active/inactive status -->
                        <div class="status-dot" style="width: 12px; height: 12px; border-radius: 50%; background-color: '. $status_color .'; margin-left: auto; border: none;"></div>
                    </a>';
    }

    // If no conversations, display the "Start Conversation" message
    
    if (!$has_conversations) {
        $output .= '<div class="no-conversations" style="display: flex; flex-direction: column; justify-content: flex-start; align-items: center; height: 600px; font-size: 16px; color: #888;">
                        <p style="margin-top: 10px;">No conversations yet. Start a conversation</p>
                    </div>';
    }
    

    $output .= '</div>';  // Close the conversation section
?>

