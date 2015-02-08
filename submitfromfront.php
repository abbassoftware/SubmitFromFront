<?php
/*
Plugin Name: SubmitFromFront
Plugin URI: 
Description: This creates a form so that posts can be submitted from the front end
Version: 1.0
Author: a
Author URI: 
*/

class WPSubmitFromFront {

	protected $pluginPath;  
	protected $pluginUrl;  

	public function __construct() {  

    	// Set Plugin Path  
        $this->pluginPath = dirname(__FILE__);  
        // Set Plugin URL  
        $this->pluginUrl = WP_PLUGIN_URL . '/submitfromfront';

         //Add CSS for the form.
        add_action('wp_enqueue_scripts', array($this, 'addStyles'));

        //Add the short code
        add_shortcode('POST_FROM_FRONT', array($this, 'handleFrontEndForm'));  
      
    }
 function handleFrontEndForm() {
    	//Check if the user has permission to publish the post.
    	if ( !current_user_can('publish_posts') ) {
			echo "<h2>Please Login to post links.</h2>";
			return;
		}

		if($this->isFormSubmitted() && $this->isNonceSet()) {
			if($this->isFormValid()) {
				$this->createPost();
			} else {
				$this->displayForm();
			}
		} else {
			$this->displayForm();
		}

    }

    //This function displays the HTML form.
    public function displayForm() {
    	?>
    	<div id ="frontpostform">
	    	<form action="" id="formpost" method="POST" enctype="multipart/form-data">
	 
			    <fieldset>
			        <label for="postTitle">Post Title</label>
			 
			        <input type="text" name="postTitle" id="postTitle" />
			    </fieldset>
			 
			    <fieldset>
			        <label for="postContent">Content</label>
			 
			        <textarea name="postContent" id="postContent" rows="10" cols="35" ></textarea>
			    </fieldset>
			 
			    <fieldset>
			        <button type="submit" name="submitForm" >Create Post</button>
			    </fieldset>

			    <?php wp_nonce_field( 'front_end_new_post' , 'nonce_field_for_front_end_new_post'); ?>
	 
			</form>
		</div>
		<?php
    }

    function addStyles() {
	    // Register the style for the form
	    wp_register_style( 'submitform-style', plugins_url( 'submitfromfront/submitfromfront.css'));
	    wp_enqueue_style( 'submitform-style' );
	}

	function isFormSubmitted() {
    	if( isset( $_POST['submitForm'] ) ) return true;
    	else return false;
    }

    function isNonceSet() {
    	if( isset( $_POST['nonce_field_for_front_end_new_post'] )  &&
    	  wp_verify_nonce( $_POST['nonce_field_for_front_end_new_post'], 'front_end_new_post' ) ) return true;
    	else return false;
    }

    function isFormValid() {
    	//Check all mandatory fields are present.
		if ( trim( $_POST['postTitle'] ) === '' ) {
			$error = 'Please enter a title.';
			$hasError = true;
		} else if ( trim( $_POST['postContent'] ) === '' ) {
			$error = 'Please enter the content.';
			$hasError = true;
		} 

		//Check if any error was detected in validation.
		if($hasError == true) {
			echo $error;
			return false;
		}
		return true;
    }

   function createPost() {
  
  		//Get the ID of currently logged in user to set as post author
		$current_user = wp_get_current_user();
		$currentuserid = $current_user->ID;

		//Get the details from the form which was posted
		$postTitle = $_POST['postTitle'];
		$contentOfPost = $_POST['postContent'] ;
		$postSatus = 'publish'; // 'pending' - in case you want to manually aprove all posts;

		//Create the post in WordPress
		$post_id = wp_insert_post( array(
						'post_title'		=> $postTitle,
						'post_content'		=> $contentOfPost,
						'post_status'		=> $postSatus , 
						'post_author'       => $currentuserid
						
					));

    }
}

$wpSubmitFromFEObj = new WPSubmitFromFront();  