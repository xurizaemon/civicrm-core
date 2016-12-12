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
 * File for the CiviCRM APIv3 job functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Job
 *
 * @copyright CiviCRM LLC (c) 2004-2016
 * @version $Id: Job.php 30879 2010-11-22 15:45:55Z shot $
 *
 */

/**
 * Class api_v3_JobTest
 * @group headless
 */
class api_v3_JobSendRemindersTest extends CiviUnitTestCase {
  protected $_apiversion = 3;

  public $DBResetRequired = FALSE;
  public $_entity = 'Job';
  public $_params = array();
  private $_email;
  private $_organization_cid;
  private $_membership_type, $_contact, $_action_schedule;
  private $_unique_string;

  /**
   * @var CiviMailUtils
   */
  private $_mut;

  public function setUp() {
    $this->cleanupMailingTest();
    parent::setUp();
    $this->_mut = new CiviMailUtils($this, TRUE);

    // Domain organisation.
    $this->_organization_cid = $this->organizationCreate();

    // Content for token checks.
    $this->_unique_string = uniqid();
    $this->_string_with_tokens = implode(PHP_EOL, [
      'unique: ' . $this->_unique_string,
      'email_greeting: {contact.email_greeting}',
      'first_name: {contact.first_name}',
    ]);

    // Test subject.
    $contact_params = [
      'contact_type' => 'Individual',
      'first_name' => 'Madge',
      'last_name' => 'Allsop',
      'email' => 'madge.allsop@example.org',
    ];
    // Set up a contact.
    $contact = $this->callAPISuccess('Contact', 'create', $contact_params);
    $this->_contact = $contact['values'][$contact['id']];

    // Membership type.
    $membership_type_params = array(
      'name' => 'Tomato Fancier',
      'description' => 'Elite Lycopersicum Appreciators',
      'financial_type_id' => 1,
      'domain_id' => '1',
      'minimum_fee' => '1',
      'duration_unit' => 'month',
      'duration_interval' => '1',
      'period_type' => 'rolling',
      'visibility' => 'public',
      'member_of_contact_id' => $this->_organization_cid,
    );
    $membership_type = $this->callApiSuccess('MembershipType', 'create', $membership_type_params);
    $this->_membership_type = $membership_type['values'][$membership_type['id']];

    // Set up a scheduled reminder,
    // @TODO There's probably a better way to identify the appropriate mapping?
    // We want the "membership" action to associate it with the action schedule.
    $mapping = CRM_Core_BAO_ActionSchedule::getMappings();
    foreach ($mapping as $entry) {
      if (get_class($entry) === 'CRM_Member_ActionMapping') {
        $mapping_id = $entry->getId();
      }
    }
    // aka action schedule in DB,
    // aka schedule reminder in UI.
    $action_schedule_params = [
      'name' => 'Expiry notice',
      'title' => 'Expiry notice',
      'start_action_offset' => 1,
      'start_action_day' => 1,
      'start_action_condition' => 'after',
      'start_action_date' => 'membership_end_date',
      'body_text' => $this->_string_with_tokens,
      'entity_value' => $this->_membership_type['id'],
      'mapping_id' => $mapping_id,
    ];
    $action_schedule = $this->callApiSuccess('ActionSchedule', 'create', $action_schedule_params);
    $this->_action_schedule = $action_schedule['values'][$action_schedule['id']];
  }

  public function tearDown() {
    $this->_mut->stop();
    CRM_Utils_Hook::singleton()->reset();
    CRM_Mailing_BAO_MailingJob::$mailsProcessed = 0;
    parent::tearDown();
  }

  public function testReminderTokens() {
    // Create a membership of type X.
    // Call scheduled reminders API.
    // Check the resulting emails.
  }

  /**
   * Create contacts in group.
   *
   * @param int $count
   * @param int $groupID
   * @param string $domain
   */
  public function createContactsInGroup($count, $groupID, $domain = 'nul.example.com') {
    for ($i = 1; $i <= $count; $i++) {
      $contactID = $this->individualCreate(array('first_name' => $count, 'email' => 'mail' . $i . '@' . $domain));
      $this->callAPISuccess('group_contact', 'create', array(
        'contact_id' => $contactID,
        'group_id' => $groupID,
        'status' => 'Added',
      ));
    }
  }

  /**
   * Clean up our test data.
   *
   * @throws \Exception
   */
  protected function cleanupMailingTest() {
    $this->quickCleanup(array(
      'civicrm_contact',
      'civicrm_membership',
    ));
  }

}
