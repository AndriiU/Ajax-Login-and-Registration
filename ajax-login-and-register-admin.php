<?php

class Ajax_Login_And_Registration_Admin{
	function __construct() {
		global $user_level;
		$alar = Ajax_Login_And_Registration::$data;
		add_action ( 'admin_menu', array (&$this, 'menus') );
		if( !empty($_REQUEST['alar_dismiss_notice']) && wp_verify_nonce($_REQUEST['_nonce'], 'alar_notice_'.$_REQUEST['alar_dismiss_notice']) && current_user_can('manage_options') ){
			if( key_exists($_REQUEST['alar_dismiss_notice'], $alar['notices']) ){
			    unset($alar['notices'][$_REQUEST['alar_dismiss_notice']]);
			    if( empty($alar['notices']) ) unset($alar['notices']); 
    			update_option('alar_data', $alar);
			}
		}elseif( !empty($alar['notices']) && is_array($alar['notices']) && count($alar['notices']) > 0 && current_user_can('manage_options') ){
			add_action('admin_notices', array(&$this, 'admin_notices'));
		}
	}
	
	function menus(){
		$page = add_options_page('Ajax login and registration', 'Ajax login and registration', 'manage_options', 'ajax-login-and-registration', array(&$this,'options'));
		add_action('admin_head-'.$page, array(&$this,'options_head'));
	}
// проедупреждение по отправки письма
	function admin_notices() {
	    if( !empty(Ajax_Login_And_Registration::$data['notices']['password_link']) ){
    		?>
    		<div class="updated notice notice-success is-dismissible password_link">
                <p>
                    <?php esc_html_e("Поскольку пароли WordPress больше не отправляются пользователям по электронной почте, они заменяются ссылкой для создания нового пароля..", 'ajax-login-and-registration'); ?>
                    <a href="<?php echo admin_url('options-general.php?page=ajax-login-and-registration'); ?>"><?php esc_html_e("Проверьте свой шаблон электронного письма для регистрации.", 'ajax-login-and-registration'); ?></a>
                </p>
                <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html_e('Dismiss','ajax-login-and-registration') ?></span></button>
            </div>
    	    <script type="text/javascript">
    			jQuery('document').ready(function($){
    				$(document).on('click', '.updated.notice.password_link .notice-dismiss', function(event){
    					jQuery.post('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
							'alar_dismiss_notice':'password_link', 
							'_nonce':'<?php echo wp_create_nonce('alar_notice_password_link'); ?>'
        				});
    				});
    			});
    	    </script>
    		<?php
	    }
	}
	
	
	function options_head(){
		?>
		<style type="text/css">
			.alar-plugin table { width:100%; }
			.alar-plugin table .col { width:100px; }
			.alar-plugin table input.wide { width:100%; padding:2px; }
			
		</style>
		<?php
	}
	
	function options() {
		global $Ajax_Login_And_Registration, $wp_version;
		add_option('alar_data');
		$alar_data = array();	
		
		if( !empty($_POST['alarsubmitted']) && current_user_can('list_users') && wp_verify_nonce($_POST['_nonce'], 'ajax-login-and-registration-admin'.get_current_user_id()) ){
			// массив опций
			foreach ($_POST as $postKey => $postValue){
				if( $postValue != '' && preg_match('/alar_role_log(in|out)_/', $postKey) ){
					//перенаправления на основе ролей
					if( preg_match('/alar_role_login/', $postKey) ){
						//Login
						$alar_data['role_login'][str_replace('alar_role_login_', '', $postKey)] = esc_url_raw($postValue);
					}else{
						//Logout
						$alar_data['role_logout'][str_replace('alar_role_logout_', '', $postKey)] = esc_url_raw($postValue);
					}
				}elseif( $postKey === 'alar_notification_message' ){
					if($postValue != ''){
						$alar_data[substr($postKey, 4)] = sanitize_textarea_field($postValue);
					}
				}elseif( substr($postKey, 0, 4) == 'alar_' ){
					// без проверка в админке
					if($postValue != ''){
						$alar_data[substr($postKey, 4)] = sanitize_text_field($postValue);
					}
				}
			}
			update_option('alar_data', $alar_data);
			if( !empty($_POST['alar_notification_override']) ){
				$override_notification = $_POST['alar_notification_override'] ? true:false;
				update_option('alar_notification_override', $override_notification);
			}
			?>
			<div class="updated"><p><strong><?php esc_html_e('Changes saved.'); ?></strong></p></div>
			<?php
		}else{
			$alar_data = get_option('alar_data');	
		}
		?>
		<div class="wrap alar-plugin">
			<h2>Ajax login and registration</h2>
			<div id="poststuff" class="metabox-holder has-right-sidebar">
				<div id="side-info-column" class="inner-sidebar">
					<div id="categorydiv" class="postbox ">
						<div class="handlediv" title="Нажмите, чтобы переключить"></div>
						<h3 class="hndle" style="color:green;">** Support this plugin! **</h3>
						<div class="inside">
							<p>Этот плагин был разработан <a href="https://evan.studio/" target="_blank">Андрей Удод</a></p>
							<!-- <ul>
								<li><a href="http://wordpress.org/support/view/plugin-reviews/ajax-login-and-registration" target="_blank" >Поставьте 5 на WordPress.org</a></li>
								<li><a href="http://wordpress.org/extend/plugins/ajax-login-and-registration/" target="_blank" >Страница плагина</a></li>
							</ul> -->
						</div>
					</div>
					<div id="categorydiv" class="postbox ">
						<div class="handlediv" title="Click to toggle"></div>
						<h3 class="hndle">Помощь</h3>
						<!-- <div class="inside">
						<p> Прежде чем обращаться за помощью, проверьте файлы readme или посетите страницу плагина, чтобы найти ответы на типичные проблемы. </p>
						<p> Если вы все же не нашли ответа, попробуйте на <a href="http://wordpress.org/support/plugin/ajax-login-and-registration/"> форуме сообщества</a>. </p>
						<p> Если у вас есть предложения, заходите на форум и оставляйте комментарии. Заранее вам благодарны! </p>
						</div> -->
					</div>
					
				</div>
				<div id="post-body">
					<div id="post-body-content">
						<form method="post" action="">
						<h3><?php esc_html_e("General Settings", 'ajax-login-and-registration'); ?></h3>
						<table class="form-table">
							<?php if( count(Ajax_Login_And_Registration::$templates) > 1 ) : ?>
							<tr valign="top">
								<th scope="row">
									<label><?php esc_html_e("Default Template", 'ajax-login-and-registration'); ?></label>
								</th>
								<td>
									<select name="alar_template" >
					            		<?php foreach( array_keys(Ajax_Login_And_Registration::$templates) as $template ): ?>
					            		<option <?php echo (!empty($alar_data['template']) && $alar_data['template'] == $template) ? 'selected="selected"':""; ?>><?php echo esc_html($template); ?></option>
					            		<?php endforeach; ?>
					            	</select>
									<br />
									<em><?php esc_html_e("Выберите тему по умолчанию.", 'ajax-login-and-registration'); ?></em>
									<em><?php esc_html_e("Дальнейшая документация по этой функции появится в ближайшее время...", 'ajax-login-and-registration'); ?></em>
								</td>
							</tr>
							<?php endif; ?>
							<tr valign="top">
								<th scope="row">
									<label><?php esc_html_e("Отключить обновление при входе в систему?", 'ajax-login-and-registration'); ?></label>
								</th>
								<td>
									<input style="margin:0px; padding:0px; width:auto;" type="checkbox" name="alar_no_login_refresh" value='1' class='wide' <?php echo ( !empty($alar_data['no_login_refresh']) && $alar_data['no_login_refresh'] == '1' ) ? 'checked="checked"':''; ?> />
									<br />
									<em><?php esc_html_e("Если пользователь входит в систему и вы нажимаете кнопку выше, только виджет входа в систему обновится без обновления страницы. Не рекомендуется, если ваш сайт показывает разный контент пользователям после входа в систему, так как потребуется обновление.", 'ajax-login-and-registration'); ?></em>
								</td>
							</tr>
						</table>
						
						
						<h3><?php esc_html_e("Настройки перенаправления входа", 'ajax-login-and-registration'); ?></h3>
						<p><em><?php echo esc_html(sprintf(__("Если вы хотите отправить пользователя по определенному URL-адресу после %s, введите полный URL-адрес (например, http://wordpress.org/) в поля ниже.. Следующие поля могут использоваться во всех ссылках перенаправления %s", 'ajax-login-and-registration'), __('login','ajax-login-and-registration'), __('login','ajax-login-and-registration'))); ?></em></p>
						<p>
							<ul>
								<li><em><?php esc_html_e("Введите %LASTURL%, чтобы отправить пользователя обратно на страницу, на которой он был ранее.", 'ajax-login-and-registration'); ?></em></li>
								<li><em><?php esc_html_e("Используйте %USERNAME%, и оно будет заменено именем пользователя, вошедшего в систему.", 'ajax-login-and-registration'); ?></em></li>
								<li><em><?php esc_html_e("Используйте %USERNICENAME%, и оно будет заменено на удобное для URL имя пользователя, вошедшего в систему.", 'ajax-login-and-registration'); ?></em></li>
								<?php if( class_exists('SitePress') ): ?>
									<li><em><?php self::ph_esc(esc_html__("Используйте %LANG%, и он будет заменен текущим языком, используемым в многоязычных URL-адресах, например, английский может быть<code>en</code>", 'ajax-login-and-registration')); ?></em></li>
								<?php endif; ?>
							</ul>
						</p>
						<table class="form-table">
							<tr valign="top">
								<th scope="row">
									<label><?php esc_html_e("Перенаправление входа", 'ajax-login-and-registration'); ?></label>
								</th>
								<td>
									<input type="text" name="alar_login_redirect" value='<?php echo (!empty($alar_data['login_redirect'])) ? esc_attr($alar_data['login_redirect']):''; ?>' class='wide' />
									<em><?php esc_html_e("Если вы хотите отправить пользователя по определенному URL-адресу после входа в систему, введите его здесь (e.g. http://wordpress.org/)", 'ajax-login-and-registration'); ?></em>
									<?php
									//(хуки для мультиязычного плагина WPML)
									function alar_icl_inputs( $name, $alar_data ){
										if( function_exists('icl_get_languages') ){
											$langs = icl_get_languages();
											if( count($langs) > 1 ){
												?>
												<table id="alar_<?php echo esc_attr($name); ?>_langs">
												<?php
												foreach($langs as $lang){
													if( substr(get_locale(),0,2) != $lang['language_code'] ){
													?>
													<tr>
														<th style="width:100px;"><?php echo esc_html($lang['translated_name']); ?>: </th>
														<td><input type="text" name="alar_<?php echo esc_attr($name); ?>_<?php echo esc_attr($lang['language_code']); ?>" value='<?php echo ( !empty($alar_data[$name.'_'.$lang['language_code']]) ) ? esc_attr($alar_data[$name.'_'.$lang['language_code']]):''; ?>' class="wide" /></td>
													</tr>
													<?php
													} 
												}
												?>
												</table>
												<em><?php esc_html_e('С включенным WPML вы также можете указать разные направления перенаправления в зависимости от языка..','ajax-login-and-registration'); ?></em>
												<?php
											}
										}
									}
									alar_icl_inputs('login_redirect', $alar_data);
									?> 
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label><?php esc_html_e("Настраиваемое перенаправление входа на основе ролей", 'ajax-login-and-registration'); ?></label>
								</th>
								<td>
									<em><?php esc_html_e("Если вы хотите, чтобы конкретная роль пользователя была перенаправлена на настраиваемый URL-адрес при входе в систему, поместите ее здесь (пустое значение по умолчанию будет глобальным перенаправлением)", 'ajax-login-and-registration'); ?></em>
									<table>
									<?php 
									// wp-admin/include/template
// function wp_dropdown_roles( $selected = '' ) {
// 	$r = '';

// 	$editable_roles = array_reverse( get_editable_roles() );

// 	foreach ( $editable_roles as $role => $details ) {
// 		$name = translate_user_role( $details['name'] );

// 		if ( $selected === $role ) {
// 			$r .= "\n\t<option selected='selected' value='" . esc_attr( $role ) . "'>$name</option>";
// 		} else {
// 			$r .= "\n\t<option value='" . esc_attr( $role ) . "'>$name</option>";
// 		}
// 	}

// 	echo $r;
// }									 

									$editable_roles = get_editable_roles();	
									//Хуки для плагина WMPL	 2
									function alar_icl_inputs_roles( $name, $alar_data, $role ){
										if( function_exists('icl_get_languages') ){
											$langs = icl_get_languages();
											if( count($langs) > 1 ){
												?>
												<table id="alar_<?php echo esc_attr($name); ?>_langs">
												<?php
												foreach($langs as $lang){
													if( substr(get_locale(),0,2) != $lang['language_code'] ){
													?>
													<tr>
														<th style="width:100px;"><?php echo esc_html($lang['translated_name']); ?>: </th>
														<td><input type="text" name="alar_<?php echo esc_attr($name); ?>_<?php echo esc_attr($role); ?>_<?php echo esc_attr($lang['language_code']); ?>" value='<?php echo ( !empty($alar_data[$name][$role.'_'.$lang['language_code']]) ) ? esc_attr($alar_data[$name][$role.'_'.$lang['language_code']]):''; ?>' class="wide" /></td>
													</tr>
													<?php
													} 
												}
												?>
												</table>
												<em><?php esc_html_e('С включенным WPML вы также можете указать разные направления перенаправления в зависимости от языка..','ajax-login-and-registration'); ?></em>
												<?php
											}
										}
									}	
									foreach( $editable_roles as $role => $details ) {
										$role_login = ( !empty($alar_data['role_login']) && is_array($alar_data['role_login']) && array_key_exists($role, $alar_data['role_login']) ) ? $alar_data['role_login'][$role]:''
										?>
										<tr>
											<th class="col"><?php echo translate_user_role($details['name']) ?></th>
											<td>
												<input type='text' class='wide' name='alar_role_login_<?php echo esc_attr($role) ?>' value="<?php echo esc_attr($role_login); ?>" />
												<?php 												
													alar_icl_inputs_roles('role_login', $alar_data, esc_attr($role)); 
												?>
											</td>
										</tr>
										<?php
									}
									?>
									</table>
								</td>
							</tr>
						</table>

<?php // выход по принципу входа ?>
						<h3><?php esc_html_e("Настройки перенаправления выхода", 'ajax-login-and-registration'); ?></h3>
						<p><em><?php echo esc_html(sprintf(__("если вы хотите отправить пользователя по определенному URL-адресу после %s, введите полный URL-адрес (например, http://wordpress.org/) в поля ниже. Следующие заполнители могут использоваться во всех ссылках перенаправления %s", 'ajax-login-and-registration'), __('logout','ajax-login-and-registration'), __('logout','ajax-login-and-registration'))); ?></em></p>
								<ul>
									<li><em><?php esc_html_e("Введите %LASTURL%, чтобы отправить пользователя обратно на страницу, на которой он был ранее..", 'ajax-login-and-registration'); ?></em></li>
									<?php if( class_exists('SitePress') ): ?>
										<li><em><?php self::ph_esc(esc_html__(" Используйте %LANG%, и он будет заменен текущим языком, используемым в многоязычных URL-адресах, например, английский может быть <code>en</code>", 'ajax-login-and-registration')); ?></em></li>
									<?php endif; ?>
								</ul>
						<table class="form-table">
							<tr valign="top">
								<th scope="row">
									<label><?php esc_html_e("Перенаправление выхода", 'ajax-login-and-registration'); ?></label>
								</th>
								<td>
									<input type="text" name="alar_logout_redirect" value='<?php echo (!empty($alar_data['logout_redirect'])) ? esc_attr($alar_data['logout_redirect']):''; ?>' class='wide' />
									<?php
									alar_icl_inputs('logout_redirect', $alar_data);
									?>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label><?php esc_html_e("Пользовательские перенаправления для выхода из системы на основе ролей", 'ajax-login-and-registration'); ?></label>
								</th>
								<td>
									<em><?php esc_html_e("Если вы хотите, чтобы конкретная роль пользователя была перенаправлена на настраиваемый URL-адрес при выходе из системы, поместите ее здесь (пустое значение по умолчанию будет глобальным перенаправлением)", 'ajax-login-and-registration'); ?></em>
									<table>
									<?php 
//Взято из /wp-admin/includes/template.php
// function wp_dropdown_roles( $selected = '' ) {
// 	$r = '';

// 	$editable_roles = array_reverse( get_editable_roles() );

// 	foreach ( $editable_roles as $role => $details ) {
// 		$name = translate_user_role( $details['name'] );

// 		if ( $selected === $role ) {
// 			$r .= "\n\t<option selected='selected' value='" . esc_attr( $role ) . "'>$name</option>";
// 		} else {
// 			$r .= "\n\t<option value='" . esc_attr( $role ) . "'>$name</option>";
// 		}
// 	}

// 	echo $r;
// }	 
									$editable_roles = get_editable_roles();		
									foreach( $editable_roles as $role => $details ) {
										$role_logout = ( !empty($alar_data['role_logout']) && is_array($alar_data['role_logout']) && array_key_exists($role, $alar_data['role_logout']) ) ? $alar_data['role_logout'][$role]:''
										?>
										<tr>
											<th class='col'><?php echo translate_user_role($details['name']) ?></th>
											<td>
												<input type='text' class='wide' name='alar_role_logout_<?php echo esc_attr($role) ?>' value="<?php echo esc_attr($role_logout); ?>" />
												<?php alar_icl_inputs_roles('role_logout', $alar_data, $role); ?>
											</td>
										</tr>
										<?php
									}
									?>
									</table>
								</td>
							</tr>
						</table>
						
						<h3><?php esc_html_e("Настройки уведомлений", 'ajax-login-and-registration'); ?></h3>
						<p>
							<em><?php esc_html_e("Если вы хотите переопределить электронную почту Wordpress по умолчанию, которую пользователи получают после регистрации, убедитесь, что вы установили флажок ниже и введите новую форму электронной почты и сообщение.", 'ajax-login-and-registration'); ?></em><br />
							<em><?php esc_html_e("Если эта функция не работает, убедитесь, что у вас не установлен другой плагин, который также управляет регистрацией пользователей.", 'ajax-login-and-registration'); ?></em>
						</p>
						<table class="form-table">
							<tr valign="top">
								<th>
									<label><?php esc_html_e("Изменить адрес электронной почты по умолчанию?", 'ajax-login-and-registration'); ?></label>
								</th>
								<td>
									<input style="margin:0px; padding:0px; width:auto;" type="checkbox" name="alar_notification_override" value='1' class='wide' <?php echo ( !empty($alar_data['notification_override']) && $alar_data['notification_override'] == '1' ) ? 'checked="checked"':''; ?> />
								</td>
							</tr>
							<tr valign="top">
								<th>
									<label><?php esc_html_e("Тема", 'ajax-login-and-registration'); ?></label>
								</th>
								<td>
									<?php
									if(empty($alar_data['notification_subject'])){
										$alar_data['notification_subject'] = esc_html__('Ваш логин на %BLOGNAME%', 'ajax-login-and-registration');
									}
									?>
									<input type="text" name="alar_notification_subject" value='<?php echo (!empty($alar_data['notification_subject'])) ? esc_attr($alar_data['notification_subject']) : ''; ?>' class='wide' />
									<em><?php self::ph_esc(esc_html__("<code>%USERNAME%</code> будет заменен именем пользователя.", 'ajax-login-and-registration')); ?></em><br />
									<?php if( version_compare($wp_version, '4.3', '>=') ): ?>
									<em><strong><?php echo sprintf(esc_html__("%s будет заменен ссылкой для установки пароля пользователя.", 'ajax-login-and-registration'), '<code>%PASSWORD%</code>'); ?></strong></em><br />
									<?php else: ?>
									<em><?php self::ph_esc(esc_html__("<code>%PASSWORD%</code> будет заменен паролем пользователя.", 'ajax-login-and-registration')); ?></em><br />
									<?php endif; ?>
									<em><?php self::ph_esc(esc_html__("<code>%BLOGNAME%</code> будет заменено названием вашего блога.", 'ajax-login-and-registration')); ?></em>
									<em><?php self::ph_esc(esc_html__("<code>%BLOGURL%</code> будет заменен URL-адресом вашего блога.", 'ajax-login-and-registration')); ?></em>
								</td>
							</tr>
							<tr valign="top">
								<th>
									<label><?php _e("Сообщение", 'ajax-login-and-registration'); ?></label>
								</th>
								<td>
									<?php 
										if( empty($alar_data['notification_message']) ){
										    if( version_compare($wp_version, '4.3', '>=') ){
										        $alar_data['notification_message'] = esc_html__('Спасибо, что подписались на наш блог.
													Вы можете войти со следующими учетными данными, посетив %BLOGURL%
													Имя пользователя: %USERNAME%
													Чтобы установить пароль, посетите следующий адрес: %PASSWORD%
													Мы с нетерпением ждем вашего следующего визита!
													Команда %BLOGNAME% ',' ajax-login-and-registration');
											}else{
												$alar_data['notification_message'] = esc_html__('Спасибо, что подписались на наш блог.
													Вы можете войти со следующими учетными данными, посетив %BLOGURL%

													Имя пользователя: %USERNAME%
													Пароль: %PASSWORD%

													Мы с нетерпением ждем вашего следующего визита!
													Команда %BLOGNAME%', 'ajax-login-and-registration');
										    }
										}
										?>
									<textarea name="alar_notification_message" class='wide' style="width:100%; height:250px;"><?php echo esc_html($alar_data['notification_message']); ?></textarea>
									<em><?php self::ph_esc(esc_html__("<code>%USERNAME%</code> будет заменено именем пользователя.", 'ajax-login-and-registration')); ?></em><br />
									<?php if( version_compare($wp_version, '4.3', '>=') ): ?>
									<em><strong><?php echo sprintf(esc_html__("%s будет заменен ссылкой для установки пароля пользователя.", 'ajax-login-and-registration'), '<code>%PASSWORD%</code>'); ?></strong></em><br />
									<?php else: ?>
									<em><?php self::ph_esc(esc_html__("<code>%PASSWORD%</code> будет заменен паролем пользователя.", 'ajax-login-and-registration')); ?></em><br />
									<?php endif; ?>
									<em><?php self::ph_esc(esc_html__("<code>%BLOGNAME%</code> будет заменено названием вашего блога.", 'ajax-login-and-registration')); ?></em>
									<em><?php self::ph_esc(esc_html__("<code>%BLOGURL%</code> будет заменен URL-адресом вашего блога.", 'ajax-login-and-registration')); ?></em>
								</td>
							</tr>
						</table>
							<div>
								<input type="hidden" name="alarsubmitted" value="1" />
								<input type="hidden" name="_nonce" value="<?php echo wp_create_nonce('ajax-login-and-registration-admin'.get_current_user_id()); ?>" />
								<p class="submit">
									<input type="submit" class="button-primary" value="<?php esc_html_e('Сохранить изменения') ?>" />
								</p>
							</div>		
						</form>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	
	/**
	 * @param string $string
	 */
	public static function ph_esc( $string ){
		echo str_replace(array('&lt;code&gt;','&lt;/code&gt;'), array('<code>','</code>'), $string);
	}
}

function Ajax_Login_And_Registration_AdminInit(){
	global $Ajax_Login_And_Registration_Admin; 
	$Ajax_Login_And_Registration_Admin = new Ajax_Login_And_Registration_Admin();
}

add_action( 'init', 'Ajax_Login_And_Registration_AdminInit' );
?>