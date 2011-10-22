<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Pancake
 *
 * A simple, fast, self-hosted invoicing application
 *
 * @package		Pancake
 * @author		Pancake Dev Team
 * @copyright           Copyright (c) 2011, Pancake Payments
 * @license		http://pancakeapp.com/license
 * @link		http://pancakeapp.com
 * @since		Version 3.1
 */

/**
 * Checks if a given notification was marked as "Don't show this again" by the currently logged in user.
 * 
 * Returns "hidden" if the notification is hidden, and false if not.
 * You can use if (notification_hidden($notification_id)) if you want to avoid showing the HTML for a notification completely,
 * or class="<?php echo notification_hidden($notification_id);?>" if you want it to come up as a class in your HTML, 
 * so your CSS/JS can deal with the notification appropriately.
 *  
 * @param string $notification_id
 * @return boolean|string 
 */
function notification_hidden($notification_id) {
    $CI = &get_instance();
    $user_id = (int) $CI->template->current_user->id;
    return ($CI->db->where('user_id', $user_id)->where('notification_id', $notification_id)->count_all_results('hidden_notifications') == 0) ? false : 'hidden';
}

/**
 * Marks a notification as "Don't show this again" by the currently logged in user.
 * 
 * @param string $notification_id
 * @return boolean 
 */
function hide_notification($notification_id) {
    if (!notification_hidden($notification_id)) {
	$CI = &get_instance();
	$user_id = (int) $CI->template->current_user->id;
	return $CI->db->insert('hidden_notifications', array('user_id' => $user_id, 'notification_id' => $notification_id));
    }
}

function print_update_notification() {
    $CI = &get_instance();
    $user_id = (int) $CI->template->current_user->id;
    if ($CI->router->fetch_module() == 'settings') {
	# We're in the settings page, no need to show notifications.
	return false;
    }
    $CI->load->model('users/user_m');
    $last_visited_version = LAST_VISITED_VERSION;
    $current_version      = Settings::get('version');
    $latest_version       = Settings::get('latest_version');
    
    if ($last_visited_version != $current_version) {
	# if the user's last visited version is different from the current version, notify him/her that Pancake updated itself.
	$notification_url = site_url('ajax/upgraded/'.$last_visited_version.'/'.$current_version);
	if (notification_hidden("upgraded_".str_ireplace('.', '_', $last_visited_version)."_".str_ireplace('.', '_', $current_version)."")) {
	    return false;
	}
    } elseif ($last_visited_version != $latest_version) {
	# if the user's last visited version is different from the latest  version, notify him/her that a new update is available.
	$notification_url = site_url('ajax/outdated/'.$latest_version);
	if (notification_hidden("outdated_".str_ireplace('.', '_', $latest_version)."")) {
	    return false;
	}
    } else {
	# no notification
	return false;
    }
    
    echo "<script>jQuery.facebox({ ajax: '$notification_url' });</script>";
}