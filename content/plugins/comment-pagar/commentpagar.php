<?php /*
Plugin Name: Get Paid for Comments
Plugin URI: http://f4rrest.wordpress.com/plugins/
Description: Ask for payment when someone submits a comment on your posts or pages. This is useful for help-related websites.
Author: FC
Version: 0.1
Author URI: http://f4rrest.wordpress.com
License: GPL2
*/
add_action('comment_post', 'comment_pagar'); 
function comment_pagar($comment_ID) {
	$comment = get_comment($comment_ID);
	$status = $comment->comment_approved;
	if($status !== "spam" ) // approved 
	{
		$headers = "From: " . $comment->comment_author ."<".$comment->comment_author_email.">" . "\r\n" . "Reply-To: " . $comment->comment_author_email;
		$subject = "comment_pagar test2";
		$message = $comment->comment_content;
		$to = get_bloginfo('admin_email');
		wp_mail($to , $subject, $message, $headers);
	}
}
?>
