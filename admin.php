<?php
include(loginByOpenID_PATH.'loginBySocialID.php');
//class that reperesent the complete plugin
class deSocialLoginSettings {
	//constructor of class, PHP4 compatible construction for backward compatibility
	function deSocialLoginSettings() {
		add_action('admin_menu', array($this, 'on_admin_menu')); 
	}
	
	function deRegisterSocialLoginSettings(){
		//facebook
		register_setting( 'deSocialLoginOptionGroup', 'de_social_login_facebook_enable');
		register_setting( 'deSocialLoginOptionGroup', 'de_social_login_facebook_id');
		register_setting( 'deSocialLoginOptionGroup', 'de_social_login_facebook_secret');
		//twitter
		register_setting( 'deSocialLoginOptionGroup', 'de_social_login_twitter_enable');
		register_setting( 'deSocialLoginOptionGroup', 'de_social_login_twitter_id');
		register_setting( 'deSocialLoginOptionGroup', 'de_social_login_twitter_secret');
		//google
		register_setting( 'deSocialLoginOptionGroup', 'de_social_login_google_enable');
		//openid
		register_setting( 'deSocialLoginOptionGroup', 'de_social_login_openid_enable');
		//linkedin
		register_setting( 'deSocialLoginOptionGroup', 'de_social_login_linkedin_enable');
		register_setting( 'deSocialLoginOptionGroup', 'de_social_login_linkedin_id');
		register_setting( 'deSocialLoginOptionGroup', 'de_social_login_linkedin_secret');
		//yahoo
		register_setting( 'deSocialLoginOptionGroup', 'de_social_login_yahoo_enable');
		register_setting( 'deSocialLoginOptionGroup', 'de_social_login_yahoo_id');
		register_setting( 'deSocialLoginOptionGroup', 'de_social_login_yahoo_domain');
		register_setting( 'deSocialLoginOptionGroup', 'de_social_login_yahoo_key');
		register_setting( 'deSocialLoginOptionGroup', 'de_social_login_yahoo_secret');
		//Orders
		register_setting( 'deSocialLoginOptionGroup', 'de_social_login_orders');
	}
	//extend the admin menu
	function on_admin_menu() {
		$this->deRegisterSocialLoginSettings();
		$this->pagehook = add_options_page('DE Social Login Settings', 'DE Social Login', 'administrator', 'de-social-login-settings', array($this, 'on_show_page'));
	}
	
	//executed to show the plugins complete admin page
	function on_show_page() {
		$pluginUrl = loginBySocialID::getPluginUrl();
		wp_enqueue_script('common');
		wp_enqueue_script('wp-lists');
		wp_enqueue_script('postbox');
		wp_enqueue_script('thickbox');
		wp_enqueue_style('thickbox');
		wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
		wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false );
		$this->buildSettingMetaboxes();
		
		screen_icon();
		?>
        <style>
			input.required{border:1px solid #F00;}
			.de-help-button{
				background:url(<?php echo $pluginUrl;?>/icons/help.png);
				position: absolute;
				width: 20px;
				height: 20px;
				right: 30px;
				top:-32px;
			}
			.de-news{
				width: 100%;
				height: 250px;
				border: 0px solid red;
			}
        </style>
        <h2>WP Social Login Settings</h2>
        <div class="clear"></div>
        <br>
        <div class="wrap">
        	<form method="post" action="options.php" onsubmit="return validateSocialloginSettings();">
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-2">
                        <div id="post-body-content" class="postbox-container">
                         
                        <?php  do_meta_boxes($this->pagehook, 'normal', array()); ?>
                        <input type="hidden" name="de_social_login_orders" value="<?php echo get_option('de_social_login_orders');?>"/>
                        </div>
                        <div id="postbox-container-1" class="postbox-container">
                            <?php  $this->buildStyledBox('Order Settings',array($this, 'buildOrederMetabox')); ?>
                            <?php  $this->buildStyledBox('Help Desk',array($this, 'buildHelpDesk')); ?>
                            <?php  $this->buildStyledBox('News Desk',array($this, 'buildNewsDesk')); ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>
		<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready( function($) {
				// close postboxes that should be closed
				$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
				// postboxes setup
				//console.dir(postboxes);
				postboxes.add_postbox_toggles('<?php echo $this->pagehook;?>');
				
				function update(base) {
					var hidden = $("input[name='de_social_login_orders']");
					var val = [];
					base.find('div').each(function() {
						val.push($(this)[0].id.split('_')[1]);
					})
					hidden.val(val.join(",")).change();
					
				}
				$(".order_list .order_items").sortable({
					update: function() {
						update($(this));
					}
				});
			});
			//]]>; 
			function validateSocialloginSettings(){
				var valid = true;
				jQuery("input[type='checkbox']").each(function(i, e) {
					console.dir(e);
                    if(jQuery(e)[0].checked==true){
						var childs = jQuery('input[type="text"]',jQuery(e).parents('table.form-table'));
						for(var i=0;i<childs.length;i++){
							if(childs[i].value==''){
								valid = false;
								var label = jQuery('th',jQuery(childs[i]).parents('tr'))[0].innerHTML;
								jQuery(childs[i]).addClass('required');
							}else{
								jQuery(childs[i]).removeClass('required');
							}
						}
					}
                });
				return valid;
			}
		</script>
		<?php
	}
	function buildSettingMetaboxes(){
		add_meta_box('de_social_login_metaboxes_fb', 'Facebook', array($this, 'buildSettingMetaboxesFB'), $this->pagehook, 'normal', 'core');
		add_meta_box('de_social_login_metaboxes_tw', 'Twitter', array($this, 'buildSettingMetaboxesTW'), $this->pagehook, 'normal', 'core');
		add_meta_box('de_social_login_metaboxes_google', 'Google', array($this, 'buildSettingMetaboxesGoogle'), $this->pagehook, 'normal', 'core');
		add_meta_box('de_social_login_metaboxes_linkedin', 'LinkedIn', array($this, 'buildSettingMetaboxesLinkedIn'), $this->pagehook, 'normal', 'core');
		add_meta_box('de_social_login_metaboxes_openid', 'OpenId', array($this, 'buildSettingMetaboxesOpenId'), $this->pagehook, 'normal', 'core');
		add_meta_box('de_social_login_metaboxes_yahoo', 'Yahoo', array($this, 'buildSettingMetaboxesYahoo'), $this->pagehook, 'normal', 'core');
	}
	function buildStyledBox($title,$callback,$params=array()){?>
		<div class="order_item postbox">
        	<div class="handlediv" title="Click to toggle"><br></div>
			<h3 class="hndle">
			<?php echo $title;?>
			</h3>
            <div class="inside">
            	<?php call_user_func_array($callback,$params);?>
            </div>
		</div>
        <?php
	}
	function buildHelpDesk(){?>
		<p>
        	<a href="http://developerextensions.com" target="_blank">http://developerextensions.com</a>
        </p>
        <p>
        	<a href="http://tiddu.com" target="_blank">http://tiddu.com</a>
        </p>
		<?php
	}
	function buildNewsDesk(){?>
		<iframe src="http://developerextensions.com" class="de-news" scrolling="auto"></iframe>
		<?php
	}
	
	function buildOrederMetabox(){
		$orderSettings 	= loginBySocialID::getBoxOrders();
		$orders			= $orderSettings->orders;
		$boxes			= $orderSettings->boxes;
		?>
        <div class="order_list">	
            <div class="order_items">
				<?php foreach($orders as $key){?>
                     <div class="order_item postbox " id="deSocialLoginOrder_<?php echo $key;?>">
                     	<h3 class="hndle">
                        <?php echo $boxes[$key];?>
                        </h3>
                    </div>
               <?php }?>
            </div>
        </div>
        <?php submit_button(); ?>
            <div class="clear"></div>
        <?php
	}
	function buildSettingMetaboxesFB(){
		$rows = array(
			array('label'=>'Enable/Disable','type'=>'checkbox','name'=>'de_social_login_facebook_enable'),
			array('label'=>'App ID/API Key','type'=>'text','name'=>'de_social_login_facebook_id'),
			array('label'=>'App Secret','type'=>'text','name'=>'de_social_login_facebook_secret')
		);
		$this->buildForm($rows, 'facebook', true);
	}
	function buildSettingMetaboxesTW(){
		$rows = array(
			array('label'=>'Enable/Disable','type'=>'checkbox','name'=>'de_social_login_twitter_enable'),
			array('label'=>'Consumer Key','type'=>'text','name'=>'de_social_login_twitter_id'),
			array('label'=>'Consumer Secret','type'=>'text','name'=>'de_social_login_twitter_secret')
		);
		$this->buildForm($rows, 'twitter', true);
	}
	function buildSettingMetaboxesGoogle(){
		$rows = array(
			array('label'=>'Enable/Disable','type'=>'checkbox','name'=>'de_social_login_google_enable')
		);
		$this->buildForm($rows, 'google');
	}
	function buildSettingMetaboxesLinkedIn(){
		$rows = array(
			array('label'=>'Enable/Disable','type'=>'checkbox','name'=>'de_social_login_linkedin_enable'),
			array('label'=>'API Key','type'=>'text','name'=>'de_social_login_linkedin_id'),
			array('label'=>'Secret Key','type'=>'text','name'=>'de_social_login_linkedin_secret')
		);
		$this->buildForm($rows, 'linkedin', true);
	}
	function buildSettingMetaboxesOpenId(){
		$rows = array(
			array('label'=>'Enable/Disable','type'=>'checkbox','name'=>'de_social_login_openid_enable')
		);
		$this->buildForm($rows, 'openid');
	}
	function buildSettingMetaboxesYahoo(){
		$rows = array(
			array('label'=>'Enable/Disable','type'=>'checkbox','name'=>'de_social_login_yahoo_enable'),
			array('label'=>'Application ID','type'=>'text','name'=>'de_social_login_yahoo_id'),
			array('label'=>'App Domain','type'=>'text','name'=>'de_social_login_yahoo_domain'),
			array('label'=>'Consumer Key','type'=>'text','name'=>'de_social_login_yahoo_key'),
			array('label'=>'Consumer Secret','type'=>'text','name'=>'de_social_login_yahoo_secret')
		);
		$this->buildForm($rows, 'yahoo', true);
	}
	
	function buildForm($rows, $key, $showHelpLink=false){
		$help_links = array(
			'facebook'=>'http://softwaredevelopertricks.com/wordpress-social-login-plugin-help-for-facebook/',
			'twitter'=>'http://softwaredevelopertricks.com/wordpress-social-login-plugin-help-for-twitter/',
			'yahoo'=>'http://softwaredevelopertricks.com/wordpress-social-login-plugin-help-for-yahoo/',
			'linkedin'=>'http://softwaredevelopertricks.com/wordpress-social-login-plugin-help-for-linkedin/'
		);
		if($showHelpLink){
		?>
        <a class="de-help-button thickbox" href="<?php echo $help_links[$key];?>" title="Help for <?php echo $key;?>"></a>
		<?php 
		}
		settings_fields( 'deSocialLoginOptionGroup' );?>
		<?php do_settings_fields($this->pagehook, 'deSocialLoginOptionGroup' ); ?>
		<table class="form-table">
		<?php foreach($rows as $row){
			echo $this->buildRow($row['label'], $row['type'], $row['name']);
		};?>
		</table>
		<?php submit_button(); ?>
		<div class="clear"></div>
      	<?php
	}
	function buildRow($label, $type, $name){
		$html = '<tr>
                    <th>'.$label.'</th>
                    <td>'.$this->buildCheckbox($type, $name).'
                    </td>
                </tr>';
		return $html;
	}
	function buildCheckbox($type,$name,$value=1){
		$html = '<input type="'.$type.'" name="'.$name.'" ';
		if(get_option($name)==1){
			$html .=' checked ';
		}
		if($type=='checkbox'){
			$html .= ' value="1"';
		}else{
			$html .= '  style="width:80%" value="'.get_option($name).'"';
		}
		$html .= ' />';
		return $html;
	}
}

add_action( 'init', 'wp_deSocialLoginSettings' );

function wp_deSocialLoginSettings(){
	global $deSocialLoginSettings;
	$deSocialLoginSettings = new deSocialLoginSettings();
}
?>