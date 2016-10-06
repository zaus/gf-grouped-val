<?php
/*

Plugin Name: Gravity Forms Grouped Validation
Plugin URI: https://github.com/zaus/gf-grouped-val
Description: Conditional validation for grouped fields
Author: zaus
Version: 0.4.1
Author URI: http://drzaus.com
Changelog:
	0.1	initial
	0.2	bugfixes for old php, custom group name
	0.3	customize error message via upload txt file
	0.4	form-specific messaging or default
*/

class GravityFormsGroupedValidation {

	// TODO: via setting
	const GROUPED_SELECTOR = 'gfv-group-req';
	const GROUPED_INVALID = 'You must provide at least one of the required values for group %s.';

	/**
	 * @var string What to report as the validation failure message
	 */
	var $err_msg;

	/**
	 * Lame 'manual' override of messaging, settings page too complicated
	 */
	const MESSAGE_OVERRIDE_PATH = 'gfv-grouped-val-msg.txt';

	public function __construct() {
		// see https://www.gravityhelp.com/documentation/article/gform_validation/
		// hook super late to apply after other validations
		add_filter('gform_validation', array(&$this, 'grouped'), 30, 1);

		// don't need to call this all the time, unless we've cached it
		//add_action('init', array(&$this, 'setup'));
	}

	const DEFAULT_MSG_INDEX = '-1';

	public function setup() {
		if(isset($this->err_msg)) return;

		$msg_override = trailingslashit( dirname(__FILE__) ) . self::MESSAGE_OVERRIDE_PATH;

		// default message at index 0
		if( !file_exists($msg_override) ) {
			$this->err_msg = array( self::DEFAULT_MSG_INDEX => __( self::GROUPED_INVALID ));
			return;
		}
		$msgs = file_get_contents($msg_override);
		if(empty($msgs)) {
			$this->err_msg = array( self::DEFAULT_MSG_INDEX => __( self::GROUPED_INVALID ));
			return;
		}
		$this->err_msg = JSON_DECODE($msgs, true);
		// translate too; TODO: plugin-specific?
		foreach($this->err_msg as &$m) {
			$m = __($m);
		}
		// if default not already set
		if(!isset($this->err_msg[self::DEFAULT_MSG_INDEX]))
			$this->err_msg[self::DEFAULT_MSG_INDEX] = __( self::GROUPED_INVALID );

		### _log(__CLASS__ . '.' . __FUNCTION__, array($msg_override, $msgs, $this->err_msg));
	}

	public function grouped($validation_result) {
		// partly inspired by http://stackoverflow.com/a/37031985/1037948
		$form = $validation_result['form'];
		$fields = $form['fields'];

		//find all fields with grouping indicator
		$groups = array();
		foreach( $fields as $k => &$field ) {
			// don't ignore anything already invalid, because we need to check against all fields in the group
			// if($fields[$k]->failed_validation) continue;

			// not indicated for group evaluation?
			if (false === ($i = strpos($field->cssClass, self::GROUPED_SELECTOR))) continue;

			// check for multiple arbitrary groups
			$group = explode(' ', substr($field->cssClass, $i + strlen(self::GROUPED_SELECTOR)));
			### _log(__CLASS__, $group);

			// let it be almost anything
			if (empty($group[0])) $group = 1;
			else $group = $group[0];

			if (!isset($groups[$group])) $groups[$group] = array();
			$groups[$group] [$k] = $field->id;
		}

		### _log(__CLASS__, $groups);

		if(!empty($groups)) $this->setup();

		### _log('checking', array($form['id'] => isset($this->err_msg[$form['id']]), $form['title'] => isset($this->err_msg[$form['title']])), $this->err_msg);

		// check each group
		foreach($groups as $i => $group) {
			$group_invalid = true;
			foreach($group as $k => $id) {
				### _log(__CLASS__ . '/field', array('k' => $k, 'id' => $id, 'invalid' => $group_invalid, 'failed' => $fields[$k]->failed_validation, 'input' => rgpost( "input_$id" )));
				// again, ignore anything already invalid (trick here)
				$input = rgpost( "input_$id" );
				$group_invalid = $group_invalid && !($fields[$k]->failed_validation) && empty($input);
				// don't stop after first invalid, because we need to check the rest of the group
				// for 'normal' validation
			}
			### _log(__CLASS__, $group_invalid, $group);
			if($group_invalid) {
				// must set overall validation to false at least once
				$validation_result['is_valid'] = false;
				$groupid = str_replace('_', ' ', trim($i, '-'));

				foreach($group as $k => $id) {
					$fields[$k]->failed_validation = true;

					// check for either id or title, to make it less brittle
					// NOTE: apparently nested ternary will actually evaluate all branches? maybe just a typo...
					if(isset($this->err_msg[$form['id']])) $msg = $this->err_msg[$form['id']];
					elseif(isset($this->err_msg[$form['title']])) $msg = $this->err_msg[$form['title']];
					else $msg = $this->err_msg[self::DEFAULT_MSG_INDEX];

					if(!empty($fields[$k]->validation_message))
						$fields[$k]->validation_message .= '  ' . sprintf($msg, $groupid);
					else
						$fields[$k]->validation_message = sprintf($msg, $groupid);
				}
			}
		}

		//Assign modified $form object back to the validation result
		$validation_result['form'] = $form;
		return $validation_result;
	}
}   //--	class

new GravityFormsGroupedValidation();