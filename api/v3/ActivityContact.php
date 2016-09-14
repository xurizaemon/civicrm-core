<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2016                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 */

/**
 * This api exposes the CiviCRM ActivityContact join table.
 *
 * @package CiviCRM_APIv3
 */

/**
 * Add a record relating a contact with an activity.
 *
 * @param array $params
 *
 * @return array
 *   API result array.
 */
function civicrm_api3_activity_contact_create($params) {
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * Adjust Metadata for Create action.
 *
 * The metadata is used for setting defaults, documentation & validation.
 *
 * @param array $params
 *   Array of parameters determined by getfields.
 */
function _civicrm_api3_activity_contact_create_spec(&$params) {
  $params['contact_id']['api.required'] = 1;
  $params['activity_id']['api.required'] = 1;
}

/**
 * Adjust Metadata for Delete action.
 *
 * The metadata is used for setting defaults, documentation & validation.
 *
 * @param array $params
 *   Array of parameters determined by getfields.
 */
function _civicrm_api3_activity_contact_delete_spec(&$params) {
  // Is this required, if we manually check parameters in the actual
  // delete function? If so, how do I specify the accepted parameters?
}

/**
 * Delete an existing ActivityContact record.
 *
 * @param array $params
 *
 * @return array
 *   Api Result
 */
function civicrm_api3_activity_contact_delete($params) {
  // Accept a delete which provides any one (or more) of these parameters.
  $accepted_params = array(
    'id',
    'activity_id',
    'contact_id',
    'record_type_id',
  );

  // Accept delete by ID, activity_id, contact_id, or
  // combination.
  //
  // (You might also want to filter by record_type_id.)
  civicrm_api3_verify_one_mandatory($params, NULL, $accepted_params);

  $dao = new CRM_Activity_BAO_ActivityContact();
  foreach ($accepted_params as $key) {
    if (isset($params[$key])) {
      $dao->$key = $params[$key];
    }
  }
  if ($dao->find()) {
    while ($dao->fetch()) {
      $dao->delete();
    }
    return civicrm_api3_create_success();
  }
  else {
    throw new API_Exception('Could not delete entity id ' . $params['id']);
  }

  throw new API_Exception('no delete method found');
}

/**
 * Get a ActivityContact.
 *
 * @param array $params
 *   An associative array of name/value pairs.
 *
 * @return array
 *   API result array
 */
function civicrm_api3_activity_contact_get($params) {
  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}
