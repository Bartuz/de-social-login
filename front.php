<?php
include_once(loginByOpenID_PATH.'loginBySocialID.php');
include_once loginByOpenID_PATH.'twitter/OAuth.php';
//include_once(loginByOpenID_PATH.'google/deOpenIdGoogle.php');
include_once(loginByOpenID_PATH.'google/Client.php');
include_once(loginByOpenID_PATH.'google/Service/Plus.php');
include_once(loginByOpenID_PATH.'google/deLoginByGoogle.php');
include_once(loginByOpenID_PATH.'openid/deOpenIdOpenID.php');
include_once(loginByOpenID_PATH.'openid/deLoginByOpenID.php');
include_once(loginByOpenID_PATH.'facebook/src/facebook.php');
include_once(loginByOpenID_PATH.'facebook/deLoginByFacebook.php');
include_once(loginByOpenID_PATH.'twitter/deLoginByTwitter.php');
include_once(loginByOpenID_PATH.'linkedin/deLoginByLinkedIn.php');
include_once(loginByOpenID_PATH.'yahoo/deLoginByYahoo.php');

add_action( 'login_form', 'wp_de_social_login_form');

function wp_de_social_login_form($is_temp=false){
	$pluginUrl = loginBySocialID::getPluginUrl();
	?>
<style>
	#login{
		padding-top:60px;	
	}
	#wp-submit{display:none;}
	#loginform{
		position:relative !important;
	}
	.forgetmenot{
		position: absolute;
		top: <?php echo (version_compare(get_bloginfo('version'), '3.8', '<')?'165px':'190px');?>
	}
	input.button-openid {
		width: 150px;
		height: 24px;
		font-size: 13px!important;
		line-height: 16px;
		padding: 3px 10px;
		float: right;
		background: #21759B url(<?php echo $pluginUrl;?>/icons/sign_in_with_openid.png) no-repeat left top;
	}
	.subText{
		font-size:80%;
	}
	input#openid_url{
		margin-bottom:2px;
		background-image: url(<?php echo $pluginUrl;?>/icons/openid.gif);
		background-position: 3px 50%;
		background-repeat: no-repeat; 
		padding-left: 21px !important; 
	}
	.clear2{
		margin-bottom:20px;
		border-bottom:1px solid  #cccccc;
		width:100%;
		height:20px;
	}
	
</style>
<?php if(!$is_temp){?>
<p>
<input type="submit" name="wp-submit" class="button-primary" value="<?php esc_attr_e('Log In'); ?>" tabindex="100" />
</p>
<div class="clear  clear2"></div>
<?php }

	$orderSettings 	= loginBySocialID::getBoxOrders();//var_dump($orderSettings);
	$orders			= $orderSettings->orders;
	$boxes			= $orderSettings->boxes;
	foreach($orders as $key){
		if($key=='openid'){
			if(get_option('de_social_login_openid_enable')==1){?>
			<p>
				<label>Login with OpenID</label><span>&nbsp;(<a href="http://www.myopenid.com/" target="_blank" class="link" >Get an OpenID</a>)</span>
				<div>
					<input type="text" id="openid_url" name="openid_url" class="input">
					<span class="subText">(e.g. http://username.myopenid.com)</span>
					<input type="button" name="login" value="" class="button-openid" onclick="if(document.getElementById('openid_url').value==''){alert('Please enter your Open ID');}else{window.location.href = '<?php echo '?SOCIALID=deLoginByOpenID_login&openid_url=';?>'+document.getElementById('openid_url').value;}">
					
				</div>
			</p>
			<div class="clear"></div>
			<br />
			<?php }
		}else{
			if(get_option('de_social_login_'.$key.'_enable')==1){?>
			<p>
				<div align="center">
				 <a href="?SOCIALID=deLoginBy<?php echo ucfirst($key);?>_login"><img src="<?php echo $pluginUrl;?>/icons/sign_in_with_<?php echo $key;?>.png" title="Login with <?php echo ucfirst($key);?>"/></a>
				</div>
			</p>
			<?php }
		}
	}
}

add_action('plugins_loaded', 'wpLoginBySocialID');

function wpLoginBySocialID(){
	if(get_var(loginBySocialID::$loginKey,-99)!=-99){
		$action		= explode('_',get_var(loginBySocialID::$loginKey));
		$action		= $action[0];
		$deLogin	= false;
		switch($action){
			case deLoginByGoogle::$loginBy:
				$deLogin = new deLoginByGoogle();
			break;
			case deLoginByOpenID::$loginBy:
				$deLogin = new deLoginByOpenID();
			break;
			case deLoginByFacebook::$loginBy:
				$deLogin = new deLoginByFacebook();
			break;
			case deLoginByLinkedin::$loginBy:
				$deLogin = new deLoginByLinkedIn();
			break;
			case deLoginByTwitter::$loginBy:
				$deLogin = new deLoginByTwitter();
			break;
			case deLoginByYahoo::$loginBy:
				$deLogin = new deLoginByYahoo();
			break;
			default:
				$deLogin	= false;
			break;
		}
		if($deLogin){
			$deLogin->onLogin();
		}
	}
}

function wp_de_render_login_form(){
	if (is_user_logged_in()){
		global $current_user;
		
		$user_info 	= "<span class='display-name'>{$current_user->data->display_name}</span>&nbsp;";
		$user_info  .= get_avatar( $current_user->ID, 20 );
		?><div class="user-login">Welcome <b><?php echo $user_info;?></b>&nbsp;|&nbsp;<a href="<?php echo wp_logout_url(); ?>" title="Logout">Logout</a></div><?php
	}else{
		wp_de_social_login_form(true);
	}
	
}
function get_var($key,$default=false){
	if(isset($_REQUEST[$key])){
		return $_REQUEST[$key];
	}
	return $default;
}
?>
