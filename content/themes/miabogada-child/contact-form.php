<?php
/*
Template Name: Contact Form
*/

// If WPML is used for translation, uncomment and run the page once to register the string translations functionality in wp-admin
/*
if (function_exists('icl_register_string')) {
	icl_register_string('contact form', 'form error message', 'Hay una problema...');
	icl_register_string('contact form', 'question missing', 'Se olvidó de poner una pregunta.');
	icl_register_string('contact form', 'email missing', 'Se olvidó de poner dirección de correo electrónico.');
	icl_register_string('contact form', 'email invalid', 'Dirección de correo electrónico no válida.');
	icl_register_string('contact form', 'legend', 'Envíe su pregunta de inmigracion a la Abogada Linnette');
	icl_register_string('contact form', 'legend thanks', 'Gracias por su pregunta');
	icl_register_string('contact form', 'label comments', 'Ingrese su pregunta:');
	icl_register_string('contact form', 'label email', 'E-mail:');
	icl_register_string('contact form', 'label optin', '¿Deseas recibir mensaje desde Abogada Linnette por correo electrónico?');
	icl_register_string('contact form', 'optin', 'Sí');
	icl_register_string('contact form', 'submit', 'Enviar');
	icl_register_string('contact form', 'important cta', 'Para obtener la respuesta personal a su pregunta el costo es $5');
	icl_register_string('contact form', 'privacy', 'Su pregunta y su información se mantendrá privada. Si prefiere dejar una pregunta o un comentario que aparece en la página web, haga clic aquí');

	$lang = ICL_LANGUAGE_CODE;
	echo "registered ". $lang;
} 
*/

// Uncomment and run once to unregister the string translations functionality if needed
/*
if (function_exists('icl_unregister_string')){
	icl_unregister_string('contact form', 'legend');
	echo 'unregistered';
}
*/

// ---BEGIN FORM HANDLER---
if(isset($_POST['submitted'])) {

	//Check to see if the honeypot captcha field was filled in
/*	if(trim($_POST['checking']) !== '') {
		$captchaError = true;
	} else {
	
		//Check to make sure that the name field is not empty
		if(trim($_POST['contactName']) === '') {
			$nameError = 'You forgot to enter your name.';
			$hasError = true;
		} else {
			$name = trim($_POST['contactName']);
		}
*/		
		//Check for valid email address submitted
		if(trim($_POST['email']) === '')  {
			$emailError = icl_t('contact form', 'email missing', 'Se olvidó de poner dirección de correo electrónico.');
			$hasError = true;
		} else if (!eregi("^[A-Z0-9._%-]+@[A-Z0-9._%-]+\.[A-Z]{2,4}$", trim($_POST['email']))) {
			$emailError = icl_t('contact form', 'email invalid', 'Dirección de correo electrónico no válida');
			$hasError = true;
		} else {
			$email = trim($_POST['email']);
		}
			
		//Check for comments submitted	
		if(trim($_POST['comments']) === '') {
			$commentError = icl_t('contact form', 'question missing', 'Se olvidó de poner una pregunta.');
			$hasError = true;
		} else {
			$comments = trim($_POST['comments']);
		}
		
		if(!isset($hasError)) {
			// open database, find or insert email and insert comments
			include 'insertcomments.php';

			//determine location by ip - previously geolocation.cfc
			include 'geolocation.php';
	
			//prep and send email
			$subject = "Pregunta inmigracion, " . $_POST['state'];
			$body = $comments . "\n\n" . $location;
			$headers = "From: " . $email ."<".$email.">" . "\r\n" . 'Reply-To: ' . $email;
			$touser = get_user_by('login','pregunta');
			if ($touser) {	
				$emailTo = $touser->user_email;
			} else {
				$emailTo = get_bloginfo('admin_email');
			}
			wp_mail($emailTo, $subject, $body, $headers);
			$emailSent = true;
		}
//	}
} 
// --- END FORM HANDLER --- 
?>

<?php 
// --- BEGIN PAGE DISPLAY ---
get_header(); ?>
<?php /*if(isset($emailSent) && $emailSent == true) { ?>

	<div class="thanks">
		<h1>Thanks,</h1>
		<p>Your email was successfully sent. I will be in touch soon.</p>
	</div>

<?php } else { */?>

<?php if (!(isset($emailSent) && $emailSent == true)) { ?>
	<?php if (have_posts()) : ?>
	
	<?php while (have_posts()) : the_post(); ?>
		
<style type="text/css">
.error {color:red}
ul.forms {list-style-type:none}
</style>
		<!--h1><?php the_title(); ?></h1-->
		<?php 	if(isset($hasError) || isset($captchaError)) { ?>
				<h1><span class="error"><?php echo icl_t('contact form', 'form error message', 'Hay una problema...'); ?></span></h1>
		<?php	} else { ?>
				<h1>
		<?php 		echo icl_t('contact form', 'legend', 'Envía su pregunta de inmigracion en privado por email'); ?>
				<img style="border: 0px;" src="/images/mail.gif" alt="" width="20" height="20" /></h1><p>
		<?php		echo icl_t('contact form', 'privacy', 'Su pregunta y su información se mantendrá privada. Si prefiere dejar una pregunta o un comentario que aparece en la página web, haga clic'); ?>
				<a href="/noticias-inmigracion/"> aquí</a>
				</p>
		<?php	} ?>
		<?php /* the_content(); */ ?>
	
		<form action="<?php the_permalink(); ?>" id="contactForm" method="post">
		<fieldset>	
			<ul class="forms">
				<li class="textarea">
					<label for="commentsText">
						<?php echo icl_t('contact form', 'label comments', 'Ingrese su pregunta:'); ?>
					</label>
					<?php if($commentError != '') { ?>
						<span class="error"><?=$commentError;?></span> 
					<?php } ?>
					<textarea name="comments" id="commentsText" rows="4" cols="30" class="requiredField"><?php if(isset($_POST['comments'])) { if(function_exists('stripslashes')) { echo stripslashes($_POST['comments']); } else { echo $_POST['comments']; } } ?></textarea>
				</li>

				<li class="inline">
					<?php echo icl_t('contact form', 'label optin', '¿Deseas recibir mensaje desde Abogada Linnette por correo electrónico?'); ?>
					<input type="checkbox" name="optin" id="optin" value="true" checked="checked" />
					<label for="sendCopy"><?php echo icl_t('contact form', 'optin', 'Sí'); ?></label>
				</li>


				<li><label for="email">
						<?php echo icl_t('contact form', 'label email', 'E-mail:'); ?>
					</label><br>
					<input type="text" name="email" id="email" value="<?php if(isset($_POST['email']))  echo $_POST['email'];?>" class="requiredField email" />
					<?php if($emailError != '') { ?>
						<span class="error"><?=$emailError;?></span>
					<?php } ?>
				</li>

				<li><label for="state">
						<?php echo icl_t('pagar', 'label state', 'Estado:'); ?>
					</label>
				<br><select name="state">
					<option></option><?php $states_arr = array ('AL'=>"Alabama",'AK'=>"Alaska",'AZ'=>"Arizona",'AR'=>"Arkansas",'CA'=>"California",'CO'=>"Colorado",'CT'=>"Connecticut",'DE'=>"Delaware",'DC'=>"District Of Columbia",'FL'=>"Florida",'GA'=>"Georgia",'HI'=>"Hawaii",'ID'=>"Idaho",'IL'=>"Illinois", 'IN'=>"Indiana", 'IA'=>"Iowa",  'KS'=>"Kansas",'KY'=>"Kentucky",'LA'=>"Louisiana",'ME'=>"Maine",'MD'=>"Maryland", 'MA'=>"Massachusetts",'MI'=>"Michigan",'MN'=>"Minnesota",'MS'=>"Mississippi",'MO'=>"Missouri",'MT'=>"Montana",'NE'=>"Nebraska",'NV'=>"Nevada",'NH'=>"New Hampshire",'NJ'=>"New Jersey",'NM'=>"New Mexico",'NY'=>"New York",'NC'=>"North Carolina",'ND'=>"North Dakota",'OH'=>"Ohio",'OK'=>"Oklahoma", 'OR'=>"Oregon",'PA'=>"Pennsylvania",'RI'=>"Rhode Island",'SC'=>"South Carolina",'SD'=>"South Dakota",'TN'=>"Tennessee",'TX'=>"Texas",'UT'=>"Utah",'VT'=>"Vermont",'VA'=>"Virginia",'WA'=>"Washington",'WV'=>"West Virginia",'WI'=>"Wisconsin",'WY'=>"Wyoming",''=>"--");
			?><?php echo showOptionsDrop($states_arr, $_POST['state'], true); ?>
				    </select>	
				</li>
<br>
				<li class="buttons"><input type="hidden" name="submitted" id="submitted" value="true" /><button type="submit">
					<?php echo icl_t('contact form', 'submit', 'Enviar'); ?> &raquo;</button>
				</li>
			</ul>
		</fieldset>
		</form>
	
		<?php endwhile; ?>
	<?php endif; ?>

<?php } else { //email was sent without error, 
// --- DISPLAY THANKS/PURCHASE VIEW --- ?> 

	<?php if (have_posts()) : ?>
		<?php while (have_posts()) : the_post(); ?>
			<?php the_content(); ?>
		<?php endwhile; ?>
	<?php endif; ?>


<?php } ?>

<?php get_footer(); ?>
	
<?php
function showOptionsDrop($array, $active, $echo=true){
	$string = '';
	foreach($array as $k => $v){
            $s = ($active == $k)? ' selected="selected"' : '';
            $string .= '<option value="'.$k.'"'.$s.'>'.$v.'</option>'."\n";
        }

        if($echo)   echo $string;
        else        return $string;
    }
?>