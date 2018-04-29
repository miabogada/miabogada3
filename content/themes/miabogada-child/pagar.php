<?php 
/*
Template Name: CC Pay Form
*/
// If WPML is used for translation, uncomment and run the page once to register the string translations functionality in wp-admin
/*
if (function_exists('icl_register_string')) {
	icl_register_string('pagar', 'form error message', 'Hay una problema...'); 
	icl_register_string('pagar', 'payment failure message', 'El pago no se realizó correctamente.');
	icl_register_string('pagar', 'form title', 'Pago con tarjeta de crédito');
	icl_register_string('pagar', 'label first name', 'Nombre:');
	icl_register_string('pagar', 'label last name', 'Apellido:');
	icl_register_string('pagar', 'label card type', 'Typo de tarjeta:');
	icl_register_string('pagar', 'label card number', 'Número de tarjeta:');
	icl_register_string('pagar', 'label exp date', 'Fecha de expiración:');
	icl_register_string('pagar', 'label email', 'E-mail para recibir la respuesta:');
	icl_register_string('pagar', 'label cvv', 'Código de seguridad CVV:');
	icl_register_string('pagar', 'help cvv', '¿Qué es el CVV?');
	icl_register_string('pagar', 'label billing address', 'Dirección de facturación:');

	icl_register_string('pagar', 'label city', 'Ciudad:');
	icl_register_string('pagar', 'label state', 'Estado:');
	icl_register_string('pagar', 'label zip', 'Código postal:');
	icl_register_string('pagar', 'label email', 'E-mail:');
	icl_register_string('pagar', 'submit', 'Pagar');
	icl_register_string('pagar', 'payment', 'Pago:');
	icl_register_string('pagar', 'total', 'Total:');

	$lang = ICL_LANGUAGE_CODE;
	echo "registered ". $lang;
} 
*/
// Uncomment and run once to unregister the string translations functionality if needed
/*
if (function_exists('icl_unregister_string')){
	icl_unregister_string('Custom strings', 'Name');
	echo 'unregistered';
}
*/
// ---BEGIN FORM HANDLER---
if(isset($_POST['submit'])) {

		//Check to make sure that the first name field is not empty
		if(trim($_POST['firstName']) === '') {
			$firstNameError = '*'; //icl_t('pagar', 'field missing', 'Se olvidó de poner');
			$hasError = true;
		} else {
			$firstName = trim($_POST['firstName']);
		}
		//Check to make sure that the last name field is not empty
		if(trim($_POST['lastName']) === '') {
			$lastNameError = '*'; //icl_t('pagar', 'field missing', 'Se olvidó de poner');
			$hasError = true;
		} else {
			$lastName = trim($_POST['lastName']);
		}		
		//Check for valid credit card number
		if(trim($_POST['creditCardNumber']) === '')  {
			$creditCardNumberError = '*'; //icl_t('pagar', 'field missing', 'Se olvidó de poner');
			$hasError = true;
		} else {
			$creditCardNumber = preg_replace("/[^0-9]/", "", $_POST['creditCardNumber']);
			$Visa = "/^([4]{1})([0-9]{12,15})$/";
			$MasterCard = "/^5[1-5][0-9]{14}$/";
			$Discover = "/^6(?:011|5[0-9]{2})[0-9]{12}$/";
			$Amex = "/^3[47][0-9]{13}$/";
			if (preg_match($Visa,$creditCardNumber)) { $creditCardType = "Visa";
			} elseif (preg_match($MasterCard,$creditCardNumber)) { $creditCardType = "MasterCard";
			} elseif (preg_match($Discover,$creditCardNumber)) { $creditCardType = "Discover";
			} elseif (preg_match($Amex,$creditCardNumber)) { $creditCardType = "Amex";
			} else {
				$creditCardNumberError = icl_t('pagar', 'field invalid', 'No válida');
				$hasError = true;
			}
		}
		//Check for valid cvv
		if(trim($_POST['cvv2Number']) === '')  {
			$cvv2NumberError = '*'; //icl_t('pagar', 'field missing', 'Se olvidó de poner');
			$hasError = true;
		} else {
			$cvv2Number = trim($_POST['cvv2Number']);
			if (!preg_match("/^([0-9]{3,4})$/",$cvv2Number)) {
				$cvv2NumberError = icl_t('pagar', 'field invalid', 'No válida');
				$hasError = true;
			}
		}
		//Check for valid address1
		if(trim($_POST['address1']) === '')  {
			$address1Error = '*'; //icl_t('pagar', 'field missing', 'Se olvidó de poner');
			$hasError = true;
		} else {
			$address1 = trim($_POST['address1']);
		}
		//Check for valid city
		if(trim($_POST['city']) === '')  {
			$cityError = '*'; //icl_t('pagar', 'field missing', 'Se olvidó de poner');
			$hasError = true;
		} else {
			$city = trim($_POST['city']);
		}
		//Check for valid US zip
		if(trim($_POST['zip']) === '')  {
			$zipError = '*'; //icl_t('pagar', 'field missing', 'Se olvidó de poner');
			$hasError = true;
		} else {
			$zip = trim($_POST['zip']);
			if (!preg_match("/^\d{5}(-\d{4})?$/",$zip)) {
				$zipError = icl_t('pagar', 'field invalid', 'No válida');
				$hasError = true;
			}
		}
		//Check for valid email address submitted
		if(trim($_POST['email']) === '')  {
			$emailError = '*'; //icl_t('contact form', 'email missing', 'Se olvidó de poner');
			$hasError = true;
		} else {
			$email = trim($_POST['email']);
			if (!preg_match("/^[^@]+@[a-zA-Z0-9._-]+\.[a-zA-Z]+$/",$email)) {
				$emailError = icl_t('pagar', 'field invalid', 'No válida');
				$hasError = true;
			}
		}

		//Check for valid amount
		if(trim($_POST['amount']) === '')  {
			$amountError = '*'; 
			$hasError = true;
		} else {
			$amount = trim($_POST['amount']);
//			if (!preg_match("/^\d{5}(-\d{4})?$/",$zip)) {
//				$amountError = icl_t('pagar', 'field invalid', 'No válida');
//				$hasError = true;
//			}
		}
			
		//Check for comments submitted	
//		if(trim($_POST['comments']) === '') {
//			$commentError = icl_t('contact form', 'question missing', 'Se olvidó de poner una pregunta.');
//			$hasError = true;
//		} else {
			$comments = trim($_POST['os1']);
//		}
//		
		if(!isset($hasError)) {
			// process the payment
 			$ppParams = array(
			    'METHOD'		=> 'doDirectPayment',
			    'PAYMENTACTION'	=> 'Sale',
			    'IPADDRESS'		=> $_SERVER['REMOTE_ADDR'],
			    'AMT'		=> $amount,
			    'DESC'		=> $_POST['item_name'],
			    'CUSTOM'		=> $comments,
			    'CREDITCARDTYPE'	=> $creditCardType,
			    'ACCT'		=> $creditCardNumber, //test '4933842059426741',
			    'EXPDATE'		=> $_POST['expDateMonth'].$_POST['expDateYear'],
			    'CVV2'		=> $cvv2Number,
			    'FIRSTNAME'		=> $firstName,
			    'LASTNAME'		=> $lastName,
			    'EMAIL'		=> $email,
			    'STREET'		=> $address1,
			    'STREET2'		=> '',
			    'CITY'		=> $city,
			    'STATE'		=> $_POST['state'],
			    'ZIP'		=> $zip,
			    'COUNTRYCODE'	=> 'US',
			    'INVNUM'		=> '',
			);
			$response = hashCall($ppParams);
			if ($response['ACK'] === 'Success') {
				$pagestate = 'success';
			} else { $pagestate = 'failure';
			}
		} else { $pagestate = 'error';
		}
} else { $pagestate = 'form';
}
// --- END FORM HANDLER --- 
// --- BEGIN PAGE DISPLAY ---
add_filter('yoast-ga-push-after-pageview', 'ga_push');
//echo $pagestate;
get_header(); 
	if (have_posts()) : 
		while (have_posts()) : the_post(); 
			$item_name = $_POST['item_name'];
//			$amount = $_POST['amount'];
?>
			<style type="text/css">
				ul.forms {list-style-type:none}
				.error {color:red}
			</style>
			<h2><?php  echo $item_name .': $' .$amount;  ?></h2>

			<?php if ($pagestate === 'failure') {
				echo '<span class="error">' . icl_t('pagar', 'payment failure message', 'El pago no se realizó correctamente.') . $response['L_LONGMESSAGE0'] .'</span>';
//				echo '<br><pre>';
//				print_r($response);
//				echo '</pre>';
			} elseif ($pagestate === 'error') {
				echo '<h2><span class="error">' . icl_t('pagar', 'form error message', 'Hay una problema... ') .'</span></h2>';
			}
			
			the_content(); 

			if ($pagestate != 'success') { //show the form

				$email = $_POST['email'];
				$comments = $_POST['os1'];
				$item_name = $_POST['item_name'];
				$amount = $_POST['amount'];
			?>

			<script type="text/javascript" language="JavaScript"><!--
				var submitted = false;
				document.myform.mybutton.value = 'Pagar';
				document.myform.mybutton.disabled = false;
					function SubmitTheForm() {
						if(submitted == true) { return; }
						document.myform.submit();
						document.myform.mybutton.value = 'Un momento...';
						document.myform.mybutton.disabled = true;
						submitted = true;
					}
			//--></script>

			<form ACTION="<?php the_permalink(); ?>" METHOD="post" name="myform" onsubmit="javascript:__utmLinkPost(this)">
				<fieldset>
			  <center>
			    <table class="api">
				<tr>
				  <?php if($amount != '') { ?>
				  	<td class="field "><strong><?php echo icl_t('pagar', 'total', 'Total:'); ?></strong></td>
				  	<td><?php echo '$' .$amount; ?><input type="hidden" name="amount" value="<?php echo $amount; ?>"></td>
				  <?php } else { ?>
				  	<td class="field <?php 	if($amountError != '') { echo 'error'; }?>"><strong><?php echo icl_t('pagar', 'payment', 'Pago:'); ?></strong></td>
					<td><input type="text" size="8" maxlength="8" name="amount" value="" />
				  <?php } ?>
				  </td>
				</tr>
			      <tr>
				<td><?php echo icl_t('pagar', 'form title', 'Pago con tarjeta de crédito'); ?></td><td><img width="173" height="25"src="/images/cards.gif" alt="tarjetas"/></td>
				  </tr>
			<!--      <tr>
				<td class="field"><?php echo icl_t('pagar', 'label card type', 'Typo de tarjeta:'); ?></td>
				<td><select name="creditCardType">
				    <option></option>
				    <option value="Visa" selected="selected">Visa</option>
				    <option value="MasterCard">MasterCard</option>
				    <option value="Discover">Discover</option>
				    <option value="Amex">American Express</option>
				  </select>
				</td>
			      </tr>
			      <tr>
			--->
				<td class="field <?php 	if($creditCardNumberError != '') { echo 'error'; }?>"><?php echo icl_t('pagar', 'label card number', 'Número de tarjeta:'); ?></td>
				<td><input type="text" size="19" maxlength="19" name="creditCardNumber" value="<?php echo $creditCardNumber ?>" />
					<?php if($creditCardNumberError != '') { ?><span class="error"><?php echo $creditCardNumberError;?></span><?php } ?>
					<?php echo $creditCardType ?>
				</td>
			      </tr>
			      <tr>
				<td class="field"><?php echo icl_t('pagar', 'label exp date', 'Fecha de expiración:'); ?></td>
				<td><select name="expDateMonth"><?php $months_arr = array ('01'=>"01",'02'=>"02",'03'=>"03",'04'=>"04",'05'=>"05",'06'=>"06",'07'=>"07",'08'=>"08",'09'=>"09",'10'=>"10",'11'=>"11",'12'=>"12");
			?><?php echo showOptionsDrop($months_arr, $_POST['expDateMonth'], true); ?>
				    </select>	
				    <select name="expDateYear"><?php $years_arr = array ('2011'=>"2011",'2012'=>"2012",'2013'=>"2013",'2014'=>"2014",'2015'=>"2015",'2016'=>"2016",'2017'=>"2017",'2018'=>"2018",'2019'=>"2019",'2020'=>"2020");
			?><?php echo showOptionsDrop($years_arr, $_POST['expDateYear'], true); ?>
				  </select>
				</td>
			      </tr>
			
			      <tr>
				<td class="field <?php 	if($cvv2NumberError != '') { echo 'error'; }?>"><?php echo icl_t('pagar', 'label cvv', 'Código de seguridad CVV:'); ?></td>
				<td><input type="text" size="3" maxlength="4" name="cvv2Number" value="<?php echo $cvv2Number ?>" /> <a style="font-size:10px" href="/images/cvv.jpg" onclick="javascript:urchinTracker('/pagar/cvv');window.open('/images/cvv.jpg','CVV','height=200,width=200,status=yes,toolbar=no,menubar=no,location=no',true); return false;"><?php echo icl_t('pagar', 'help cvv', '¿Qué es el CVV?'); ?></a>
					<?php if($cvv2NumberError != '') { ?><span class="error"><?php echo $cvv2NumberError;?></span><?php } ?>	
				</td>
			      </tr>
			
			      <tr>
				<td class="field <?php 	if($firstNameError != '') { echo 'error'; }?>"><?php echo icl_t('pagar', 'label first name', 'Nombre:'); ?></td>
				<td><input type="text" size="30" maxlength="32" name="firstName" value="<?php echo $firstName ?>" />
					<?php if($firstNameError != '') { ?><span class="error"><?php echo $firstNameError;?></span><?php } ?>	
				</td>
			      </tr>
			      <tr>
				<td class="field <?php 	if($lastNameError != '') { echo 'error'; }?>"><?php echo icl_t('pagar', 'label last name', 'Apellido:'); ?></td>
				<td><input type="text" size="30" maxlength="32" name="lastName" value="<?php echo $lastName ?>" />
					<?php if($lastNameError != '') { ?><span class="error"><?php echo $lastNameError;?></span><?php } ?>	
				</td>
			      </tr>
			      <tr>
				<td class="field <?php 	if($address1Error != '') { echo 'error'; }?>"><?php echo icl_t('pagar', 'label billing address', 'Dirección de facturación:'); ?></td>
				<td><input type="text" size="25" maxlength="100" name="address1" value="<?php echo $address1 ?>" />
					<?php if($address1Error != '') { ?><span class="error"><?php echo $address1Error;?></span><?php } ?>	
				</td>
			      </tr>
			      <!---
				  <tr>
				<td class="field"> Address 2: </td>
				<td><input type="text" size="25" maxlength="100" name="address2" />
				  (optional)</td>
			      </tr>
				  --->
			      <tr>
				<td class="field <?php 	if($cityError != '') { echo 'error'; }?>"><?php echo icl_t('pagar', 'label city', 'Ciudad:'); ?></td>
				<td><input type="text" size="25" maxlength="40" name="city" value="<?php echo $city ?>" />
					<?php if($cityError != '') { ?><span class="error"><?php echo $cityError;?></span><?php } ?>	
				</td>
			      </tr>
			      <tr>
				<td class="field"><?php echo icl_t('pagar', 'label state', 'Estado:'); ?></td>
				<td><select name="state">
					<option></option><?php $states_arr = array ('AL'=>"Alabama",'AK'=>"Alaska",'AZ'=>"Arizona",'AR'=>"Arkansas",'CA'=>"California",'CO'=>"Colorado",'CT'=>"Connecticut",'DE'=>"Delaware",'DC'=>"District Of Columbia",'FL'=>"Florida",'GA'=>"Georgia",'HI'=>"Hawaii",'ID'=>"Idaho",'IL'=>"Illinois", 'IN'=>"Indiana", 'IA'=>"Iowa",  'KS'=>"Kansas",'KY'=>"Kentucky",'LA'=>"Louisiana",'ME'=>"Maine",'MD'=>"Maryland", 'MA'=>"Massachusetts",'MI'=>"Michigan",'MN'=>"Minnesota",'MS'=>"Mississippi",'MO'=>"Missouri",'MT'=>"Montana",'NE'=>"Nebraska",'NV'=>"Nevada",'NH'=>"New Hampshire",'NJ'=>"New Jersey",'NM'=>"New Mexico",'NY'=>"New York",'NC'=>"North Carolina",'ND'=>"North Dakota",'OH'=>"Ohio",'OK'=>"Oklahoma", 'OR'=>"Oregon",'PA'=>"Pennsylvania",'RI'=>"Rhode Island",'SC'=>"South Carolina",'SD'=>"South Dakota",'TN'=>"Tennessee",'TX'=>"Texas",'UT'=>"Utah",'VT'=>"Vermont",'VA'=>"Virginia",'WA'=>"Washington",'WV'=>"West Virginia",'WI'=>"Wisconsin",'WY'=>"Wyoming",''=>"");
			?><?php echo showOptionsDrop($states_arr, $_POST['state'], true); ?>
				    </select>	
				</td>
			      </tr>
			      <tr>
				<td class="field <?php 	if($zipError != '') { echo 'error'; }?>"><?php echo icl_t('pagar', 'label zip', 'Código postal:'); ?></td>
				<td><input type="text" size="10" maxlength="10" name="zip" value="<?php echo $zip ?>" />
					<?php if($zipError != '') { ?><span class="error"><?php echo $zipError;?></span><?php } ?>	
				</td>
			      </tr>
				  <tr>
				<td class="field <?php 	if($emailError != '') { echo 'error'; }?>"><?php echo icl_t('pagar', 'label email', 'E-mail:'); ?></td>
				<td><input type="text" size="30" maxlength="32" name="email" value="<?php echo $email ?>" />
					<?php if($emailError != '') { ?><span class="error"><?php echo $emailError;?></span><?php } ?>	
				</td>
			      </tr>

			      <tr>
				<td class="field" colspan=2 style="text-align:center">

					<input type="hidden" name="submit" id="submit" value="true" /><button type="submit">
						<?php echo icl_t('pagar', 'submit', 'Pagar'); ?> &raquo;</button>
			<? /*
					<script type="text/javascript" language="JavaScript"><!--
			document.write('<input style="margin:6px;width:9em;font-weight:bold" type="button" name="mybutton" value="<?php echo icl_t('pagar', 'submit', 'Pagar'); ?>" onclick="return SubmitTheForm();">');
			//--></script>
					<noscript>
					<input type="image" src="images/boton_comprar.gif" border="0" name="submit" alt="Compralo">
					</noscript>
			*/?>
					</td>
			      </tr>
			    </table>
			  </center>
			 	<!--
					Using this hidden variable the system can identify whether it is Authorization or Sale
					This paymentaction coming from Calls.html
				-->
				<INPUT TYPE="hidden" NAME="PAYMENTACTION" VALUE="Sale">
				<input type="hidden" name="L_NAME0" value="<?php echo $item_name ?>">
				<input type="hidden" name="item_name" value="<?php echo $item_name ?>">
<!--				<input type="hidden" name="amount" value="<?php echo $amount ?>">
-->
				<input type="hidden" name="on1" value="subject">
				<input type="hidden" name="os1" maxlength="200" value="<?php echo $comments ?>">
				<input type="hidden" name="creditCardType" value="<?php echo $creditCardType ?>">
				</fieldset>
			</form>
<?php
			} //if $pagestate
			else { // successful transaction, "thank you" message
				echo "<h1>Gracias por su pago</h1><p>Abogada Linnette responderá ha su pregunta de inmigración dentro de un día Lunes ha Viernes. Si no lo recibe, por favor de llamarnos y te ayudaremos. Gracias!</p><p>Si teines una problema sobre el pago, llamanos ahora y te ayudamos. </p>";
				echo '<pre>';
				print_r($response);
				echo '</pre>';
			}
		endwhile;
	endif;
?>

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


