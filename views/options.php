
<?php if ((isset($_REQUEST['error']) && !empty($_REQUEST['error']) && !is_bool($_REQUEST)) || $message): ?>

	<div id="wordy-universal-notice" class="updated">

		<p>
			<strong><?php echo $message ? $message : strip_tags($_REQUEST['error']); ?></strong>
		</p>

	</div>

<?php endif; ?>

<div class="wrap wordy">  
	
	<h2><?php _e('Wordy plugin options', 'wordy'); ?></h2>

	<?php if ($active && !empty($options['email'])): ?>
	
		<p class="wordy-logged-in">
			
			<?php _e('Signed in to Wordy as', 'wordy'); ?> 
			
			<em><?php echo htmlspecialchars($options['email']); ?></em> 
			
			<a href="<?php echo get_option('siteurl'); ?>/wp-admin/options-general.php?page=wordy.php&logout=true" class="button"><?php _e('Sign out', 'wordy') ?></a>

		</p>

		<p>
			You can find <a href="<?php echo $wordy['signin_url']; ?>/settings">additional settings</a> at Wordy.com, or view <a href="#" id="wordy-show-quick-guide">a 2 minute walk-through</a> of what Wordy can do for you.
		</p>

		<div id="wordy-quick-guide">
			
			<object width="480" height="325">
				<param name="movie" value="http://www.youtube.com/v/V3dBPu6AAw4&hl=en_US&fs=1&rel=0"></param>
				<param name="allowFullScreen" value="true"></param>
				<param name="allowscriptaccess" value="always"></param>
				<embed src="http://www.youtube.com/v/V3dBPu6AAw4&hl=en_US&fs=1&rel=0" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="480" height="325"></embed>
			</object>
		
		</div>

	<?php else: ?>

		<div id="wordy-enabled"><?php _e('Sign in to enable.', 'wordy'); ?></div>

	<?php endif; ?>

	<div style="clear:both;"></div>

	<?php do_action('wordy_config_notices'); ?>

	<?php if (empty($options['email'])) : ?>
		
	<!-- TODO: Add in JS Validation -->

	<div class="wordy-not-logged-in">

		<h3><?php _e('Returning customer?', 'wordy'); ?></h3>

		<form action="" method="post">

			<?php wp_nonce_field('wordy-options'); ?>

			<p>	
				<label for="email" <?php echo isset($POST['signin']) && empty($POST['email']) ? 'class="wordy-invalid-label"' : ''; ?>><?php _e('E-mail', 'wordy'); ?></label>
				<input type="text" name="wordy[email]" value="" <?php echo isset($POST['signin']) && empty($POST['email']) ? 'class="wordy-invalid-input"' : ''; ?> />
			</p>

			<p>
				<label for="password" <?php echo isset($POST['signin']) && empty($POST['password']) ? 'class="wordy-invalid-label"' : ''; ?>><?php _e('Password', 'wordy'); ?> (<a href="http://wordy.com/#forgotpassword" class="forgotpassword"><?php _e('Forgot?', 'wordy'); ?></a>)</label>
				<input type="password" name="wordy[password]" value="" <?php echo isset($POST['signin']) && empty($POST['password']) ? 'class="wordy-invalid-input"' : ''; ?> />
			</p>

			<p class="submit">
				<input type="submit" name="wordy[signin]" value="<?php _e('Sign in', 'wordy'); ?>" class="button-primary" />
			</p>

		</form>

		<h3><?php _e('New customer?', 'wordy'); ?></h3>

		<div id="wordy-registration">
				
			<form action="" method="post">
				
				<?php wp_nonce_field('wordy-options'); ?>

				<input type="hidden" name="wordy_action" value="new_account" />

				<p>
					<label for="wordy[email]" <?php echo isset($POST['register']) && empty($POST['email']) ? 'class="wordy-invalid-label"' : ''; ?> ><?php _e('E-mail', 'wordy'); ?> <span class="asterix">*</span></label>
					<input type="text" name="wordy[email]" value="<?php echo $current_user->user_email; ?>" <?php echo isset($POST['register']) && empty($POST['email']) ? 'class="wordy-invalid-input"' : ''; ?> />
				</p>

				<p>
					<table border="0" cellpadding="0" cellspacing="0">
						<tr>
							<td>
								<label for="wordy[password]" <?php echo isset($POST['register']) && empty($POST['password']) ? 'class="wordy-invalid-label"' : ''; ?> ><?php _e('Password', 'wordy'); ?> <span class="asterix">*</span></label>
								<input type="password" name="wordy[password]" value="" <?php echo isset($POST['register']) && empty($POST['password']) ? 'class="wordy-invalid-input"' : ''; ?> />
							</td>
							<td>
								<label for="wordy[re_password]" <?php echo isset($POST['register']) && empty($POST['re_password']) ? 'class="wordy-invalid-label"' : ''; ?> ><?php _e('Repeat password', 'wordy'); ?> <span class="asterix">*</span></label>
								<input type="password" name="wordy[re_password]" value="" <?php echo isset($POST['register']) && empty($POST['re_password']) ? 'class="wordy-invalid-input"' : ''; ?> />
							</td>
						</tr>
					</table>
				</p>

				<p>
					<table border="0" cellpadding="0" cellspacing="0">
						<tr>
							<td>
								<label for="wordy[first_name]" <?php echo isset($POST['register']) && empty($POST['first_name']) ? 'class="wordy-invalid-label"' : ''; ?> ><?php _e('First name', 'wordy'); ?>  <span class="asterix">*</span></label>
								<input type="text" name="wordy[first_name]" value="<?php echo $current_user->user_firstname; ?>" <?php echo isset($POST['register']) && empty($POST['first_name']) ? 'class="wordy-invalid-input"' : ''; ?> />
							</td>
							<td>
								<label for="wordy[last_name]" <?php echo isset($POST['register']) && empty($POST['last_name']) ? 'class="wordy-invalid-label"' : ''; ?> ><?php _e('Last name', 'wordy'); ?>  <span class="asterix">*</span></label>
								<input type="text" name="wordy[last_name]" value="<?php echo $current_user->user_lastname; ?>" <?php echo isset($POST['register']) && empty($POST['last_name']) ? 'class="wordy-invalid-input"' : ''; ?> />
							</td>
						</tr>
					</table>
				</p>

				<p>
					<label for="wordy[country_code]" <?php echo isset($POST['register']) && (empty($POST['country_code']) || $POST['country_code'] == 'Select country') ? 'class="wordy-invalid-label"' : ''; ?> ><?php _e('Country', 'wordy'); ?> <span class="asterix">*</span></label>
					<select name="wordy[country_code]" <?php echo isset($POST['register']) && (empty($POST['country_code']) || $POST['country_code'] == 'Select country') ? 'class="wordy-invalid-input"' : ''; ?> >
						<option>Select country</option>
						<option value="AF">Afghanistan</option>
						<option value="AL">Albania</option>
						<option value="DZ">Algeria</option>
						<option value="AS">American Samoa</option>
						<option value="AD">Andorra</option>
						<option value="AO">Angola</option>
						<option value="AI">Anguilla</option>
						<option value="AQ">Antarctica</option>
						<option value="AG">Antigua and Barbuda</option>
						<option value="AR">Argentina</option>
						<option value="AM">Armenia</option>
						<option value="AW">Aruba</option>
						<option value="AU">Australia</option>
						<option value="AT">Austria</option>
						<option value="AZ">Azerbaijan</option>
						<option value="BS">Bahamas</option>
						<option value="BH">Bahrain</option>
						<option value="BD">Bangladesh</option>
						<option value="BB">Barbados</option>
						<option value="BY">Belarus</option>
						<option value="BE">Belgium</option>
						<option value="BZ">Belize</option>
						<option value="BJ">Benin</option>
						<option value="BM">Bermuda</option>
						<option value="BT">Bhutan</option>
						<option value="BO">Bolivia</option>
						<option value="BA">Bosnia and Herzegovina</option>
						<option value="BW">Botswana</option>
						<option value="BV">Bouvet Island</option>
						<option value="BR">Brazil</option>
						<option value="IO">British Indian Ocean Territory</option>
						<option value="BN">Brunei Darussalam</option>
						<option value="BG">Bulgaria</option>
						<option value="BF">Burkina Faso</option>
						<option value="BI">Burundi</option>
						<option value="KH">Cambodia</option>
						<option value="CM">Cameroon</option>
						<option value="CA">Canada</option>
						<option value="CV">Cape Verde</option>
						<option value="KY">Cayman Islands</option>
						<option value="CF">Central African Republic</option>
						<option value="TD">Chad</option>
						<option value="CL">Chile</option>
						<option value="CN">China</option>
						<option value="CX">Christmas Island</option>
						<option value="CC">Cocos (Keeling) Islands</option>
						<option value="CO">Colombia</option>
						<option value="KM">Comoros</option>
						<option value="CG">Congo</option>
						<option value="CD">Congo, the Democratic Republic of the</option>
						<option value="CK">Cook Islands</option>
						<option value="CR">Costa Rica</option>
						<option value="CI">Cote D'Ivoire</option>
						<option value="HR">Croatia</option>
						<option value="CU">Cuba</option>
						<option value="CY">Cyprus</option>
						<option value="CZ">Czech Republic</option>
						<option value="DK">Denmark</option>
						<option value="DJ">Djibouti</option>
						<option value="DM">Dominica</option>
						<option value="DO">Dominican Republic</option>
						<option value="EC">Ecuador</option>
						<option value="EG">Egypt</option>
						<option value="SV">El Salvador</option>
						<option value="GQ">Equatorial Guinea</option>
						<option value="ER">Eritrea</option>
						<option value="EE">Estonia</option>
						<option value="ET">Ethiopia</option>
						<option value="FK">Falkland Islands (Malvinas)</option>
						<option value="FO">Faroe Islands</option>
						<option value="FJ">Fiji</option>
						<option value="FI">Finland</option>
						<option value="FR">France</option>
						<option value="GF">French Guiana</option>
						<option value="PF">French Polynesia</option>
						<option value="TF">French Southern Territories</option>
						<option value="GA">Gabon</option>
						<option value="GM">Gambia</option>
						<option value="GE">Georgia</option>
						<option value="DE">Germany</option>
						<option value="GH">Ghana</option>
						<option value="GI">Gibraltar</option>
						<option value="GR">Greece</option>
						<option value="GL">Greenland</option>
						<option value="GD">Grenada</option>
						<option value="GP">Guadeloupe</option>
						<option value="GU">Guam</option>
						<option value="GT">Guatemala</option>
						<option value="GN">Guinea</option>
						<option value="GW">Guinea-Bissau</option>
						<option value="GY">Guyana</option>
						<option value="HT">Haiti</option>
						<option value="HM">Heard Island and Mcdonald Islands</option>
						<option value="VA">Holy See (Vatican City State)</option>
						<option value="HN">Honduras</option>
						<option value="HK">Hong Kong</option>
						<option value="HU">Hungary</option>
						<option value="IS">Iceland</option>
						<option value="IN">India</option>
						<option value="ID">Indonesia</option>
						<option value="IR">Iran, Islamic Republic of</option>
						<option value="IQ">Iraq</option>
						<option value="IE">Ireland</option>
						<option value="IL">Israel</option>
						<option value="IT">Italy</option>
						<option value="JM">Jamaica</option>
						<option value="JP">Japan</option>
						<option value="JO">Jordan</option>
						<option value="KZ">Kazakhstan</option>
						<option value="KE">Kenya</option>
						<option value="KI">Kiribati</option>
						<option value="KP">Korea, Democratic People's Republic of</option>
						<option value="KR">Korea, Republic of</option>
						<option value="KW">Kuwait</option>
						<option value="KG">Kyrgyzstan</option>
						<option value="LA">Lao People's Democratic Republic</option>
						<option value="LV">Latvia</option>
						<option value="LB">Lebanon</option>
						<option value="LS">Lesotho</option>
						<option value="LR">Liberia</option>
						<option value="LY">Libyan Arab Jamahiriya</option>
						<option value="LI">Liechtenstein</option>
						<option value="LT">Lithuania</option>
						<option value="LU">Luxembourg</option>
						<option value="MO">Macao</option>
						<option value="MK">Macedonia, the Former Yugoslav Republic of</option>
						<option value="MG">Madagascar</option>
						<option value="MW">Malawi</option>
						<option value="MY">Malaysia</option>
						<option value="MV">Maldives</option>
						<option value="ML">Mali</option>
						<option value="MT">Malta</option>
						<option value="MH">Marshall Islands</option>
						<option value="MQ">Martinique</option>
						<option value="MR">Mauritania</option>
						<option value="MU">Mauritius</option>
						<option value="YT">Mayotte</option>
						<option value="MX">Mexico</option>
						<option value="FM">Micronesia, Federated States of</option>
						<option value="MD">Moldova, Republic of</option>
						<option value="MC">Monaco</option>
						<option value="MN">Mongolia</option>
						<option value="MS">Montserrat</option>
						<option value="MA">Morocco</option>
						<option value="MZ">Mozambique</option>
						<option value="MM">Myanmar</option>
						<option value="NA">Namibia</option>
						<option value="NR">Nauru</option>
						<option value="NP">Nepal</option>
						<option value="NL">Netherlands</option>
						<option value="AN">Netherlands Antilles</option>
						<option value="NC">New Caledonia</option>
						<option value="NZ">New Zealand</option>
						<option value="NI">Nicaragua</option>
						<option value="NE">Niger</option>
						<option value="NG">Nigeria</option>
						<option value="NU">Niue</option>
						<option value="NF">Norfolk Island</option>
						<option value="MP">Northern Mariana Islands</option>
						<option value="NO">Norway</option>
						<option value="OM">Oman</option>
						<option value="PK">Pakistan</option>
						<option value="PW">Palau</option>
						<option value="PS">Palestinian Territory, Occupied</option>
						<option value="PA">Panama</option>
						<option value="PG">Papua New Guinea</option>
						<option value="PY">Paraguay</option>
						<option value="PE">Peru</option>
						<option value="PH">Philippines</option>
						<option value="PN">Pitcairn</option>
						<option value="PL">Poland</option>
						<option value="PT">Portugal</option>
						<option value="PR">Puerto Rico</option>
						<option value="QA">Qatar</option>
						<option value="RE">Reunion</option>
						<option value="RO">Romania</option>
						<option value="RU">Russian Federation</option>
						<option value="RW">Rwanda</option>
						<option value="SH">Saint Helena</option>
						<option value="KN">Saint Kitts and Nevis</option>
						<option value="LC">Saint Lucia</option>
						<option value="PM">Saint Pierre and Miquelon</option>
						<option value="VC">Saint Vincent and the Grenadines</option>
						<option value="WS">Samoa</option>
						<option value="SM">San Marino</option>
						<option value="ST">Sao Tome and Principe</option>
						<option value="SA">Saudi Arabia</option>
						<option value="SN">Senegal</option>
						<option value="CS">Serbia and Montenegro</option>
						<option value="SC">Seychelles</option>
						<option value="SL">Sierra Leone</option>
						<option value="SG">Singapore</option>
						<option value="SK">Slovakia</option>
						<option value="SI">Slovenia</option>
						<option value="SB">Solomon Islands</option>
						<option value="SO">Somalia</option>
						<option value="ZA">South Africa</option>
						<option value="GS">South Georgia & the South Sandwich Islands</option>
						<option value="ES">Spain</option>
						<option value="LK">Sri Lanka</option>
						<option value="SD">Sudan</option>
						<option value="SR">Suriname</option>
						<option value="SJ">Svalbard and Jan Mayen</option>
						<option value="SZ">Swaziland</option>
						<option value="SE">Sweden</option>
						<option value="CH">Switzerland</option>
						<option value="SY">Syrian Arab Republic</option>
						<option value="TW">Taiwan, Province of China</option>
						<option value="TJ">Tajikistan</option>
						<option value="TZ">Tanzania, United Republic of</option>
						<option value="TH">Thailand</option>
						<option value="TL">Timor-Leste</option>
						<option value="TG">Togo</option>
						<option value="TK">Tokelau</option>
						<option value="TO">Tonga</option>
						<option value="TT">Trinidad and Tobago</option>
						<option value="TN">Tunisia</option>
						<option value="TR">Turkey</option>
						<option value="TM">Turkmenistan</option>
						<option value="TC">Turks and Caicos Islands</option>
						<option value="TV">Tuvalu</option>
						<option value="UG">Uganda</option>
						<option value="UA">Ukraine</option>
						<option value="AE">United Arab Emirates</option>
						<option value="GB">United Kingdom</option>
						<option value="UM">United States Minor Outlying Islands</option>
						<option value="US">United States of America</option>
						<option value="UY">Uruguay</option>
						<option value="UZ">Uzbekistan</option>
						<option value="VU">Vanuatu</option>
						<option value="VE">Venezuela</option>
						<option value="VN">Viet Nam</option>
						<option value="VG">Virgin Islands, British</option>
						<option value="VI">Virgin Islands, U.S.</option>
						<option value="WF">Wallis and Futuna</option>
						<option value="EH">Western Sahara</option>
						<option value="YE">Yemen</option>
						<option value="ZM">Zambia</option>
						<option value="ZW">Zimbabwe</option>
					</select>
				</p>
				
				<p>
					<label for="wordy[company_name]"><?php _e('Company name', 'wordy'); ?></label>
					<input type="text" name="wordy[company_name]" value="" />
				</p>
				
				<p>
					<label for="wordy[vat_number]" ><?php _e('VAT number', 'wordy'); ?></label>
					<input type="text" name="wordy[vat_number]" value="" />
				</p>

				<p class="submit">
					<input type="submit" name="wordy[register]" value="<?php _e('Create account', 'wordy'); ?>" class="button-primary" />
				</div>
				
			</form>
	
		</div>
	
	</div>

	<?php else : ?>

		<div id="wordy-signed-in-block">

			<form method="post" action="">

				<h3><?php _e('Language settings', 'wordy'); ?></h3>

				<p>
					<input type="radio" name="wordy[language_code]" value="GB" <?php if ($options['language_code'] == 'GB'): ?> checked="checked"<?php endif; ?> />
					<label for="language_code-GB"><?php _e('UK English', 'wordy'); ?></label>
				</p>

				<p>
					<input type="radio" name="wordy[language_code]" value="US" <?php if ($options['language_code'] == 'US'): ?> checked="checked"<?php endif; ?> />
					<label for="language_code-US"><?php _e('US English', 'wordy'); ?></label>
				</p>


				<h3><?php _e('Publishing settings', 'wordy'); ?></h3>

				<p>
					<input type="radio" id="settings-1" name="wordy[settings]" value="approve" <?php if ($options['publishing_settings'] == 'approve'): ?> checked="checked"<?php endif; ?> />
					<label for="settings-1"><?php _e('Once edited the post should await my approval', 'wordy'); ?></label>
				</p>

				<p>
					<input type="radio" id="settings-2" name="wordy[settings]" value="publish" <?php if ($options['publishing_settings'] == 'publish'): ?> checked="checked"<?php endif; ?> />
					<label for="settings-2"><?php _e('Wordy can publish my post directly once edited', 'wordy'); ?></label>
				</p>

				<p>
					<input type="radio" id="settings-3" name="wordy[settings]" value="only_wordy" <?php if ($options['publishing_settings'] == 'only_wordy'): ?> checked="checked"<?php endif; ?> />
					<label for="settings-3"><?php _e('Do not allow publishing without Wordy', 'wordy'); ?></label>
				</p>

				<input type="submit" name="wordy[save_profile]" value="Save" class="button-primary" />

			</form>

		</div>

	<?php endif; ?>

</div>  

<script type="text/javascript">

	jQuery(document).ready(function() 
	{
		jQuery('#wordy-show-quick-guide').click(function() 
		{
			jQuery('#wordy-quick-guide').slideToggle();
		});
	});
	
</script>