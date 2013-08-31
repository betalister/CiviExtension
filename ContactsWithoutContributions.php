<?php
/*
+--------------------------------------------------------------------+
| CiviCRM version 4.3                                                |
+--------------------------------------------------------------------+
| Copyright CiviCRM LLC (c) 2004-2013                                |
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
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2013
 * $Id$
 *
 */
class CRM_Contact_Form_Search_Custom_ContactsWithoutContributions extends CRM_Contact_Form_Search_Custom_Base implements CRM_Contact_Form_Search_Interface
{
    function __construct(&$formValues)
    {
        parent::__construct($formValues);
        $this->_columns = array(
            ts('Contact ID') => 'contact_id',
            ts('Name') => 'sort_name',
            ts('Email') => 'email',
            ts('Phone') => 'phone'
        );
    }
    function buildForm(&$form)
    {
        $this->setTitle('Find Contacts without Contributions');
        $groups = array(
            '' => ts('- select group -')
        ) + CRM_Core_PseudoConstant::allGroup();
        $form->addElement('select', 'group_id', ts('Group'), $groups);
        /**
         * if you are using the standard template, this array tells the template what elements
         * are part of the search criteria
         */
        $form->assign('elements', array(
            'group_id'
        ));
    }
    function all($offset = 0, $rowcount = 0, $sort = NULL, $includeContactIDs = FALSE, $justIDs = FALSE)
    {
        if ($justIDs) {
            $selectClause = "contact_a.id as contact_id";
        } else {
            $selectClause = "
              contact_a.id as contact_id,
              contact_a.sort_name as sort_name,
              civicrm_email.email as email,
              civicrm_phone.phone as phone
              ";
        }
        return $this->sql($selectClause, $offset, $rowcount, $sort, $includeContactIDs, NULL);
    }
    function from()
    {
        return "
        FROM civicrm_group_contact, civicrm_contact AS contact_a
             LEFT JOIN civicrm_email 
                    ON contact_a.id = civicrm_email.contact_id 
             LEFT JOIN civicrm_phone 
                    ON contact_a.id = civicrm_phone.contact_id 
             LEFT OUTER JOIN civicrm_contribution 
                          ON contact_a.id = civicrm_contribution.contact_id
        ";
    }
    function where($includeContactIDs = FALSE)
    {
        $params  = array();
        $count   = 1;
        $clause  = array();
        $groupID = CRM_Utils_Array::value('group_id', $this->_formValues);
        if ($groupID) {
            $params[$count] = array(
                $groupID,
                'Integer'
            );
            $clause[] = "civicrm_group_contact.group_id = %{$count}";
        }
        $clause[] = "civicrm_group_contact.status = 'Added'";
        $clause[] = "civicrm_contribution.contact_id IS NULL";
        if (!empty($clause)) {
            $where = implode(' AND ', $clause);
        }
        return $this->whereClause($where, $params);
    }
    function templateFile()
    {
        return 'CRM/Contact/Form/Search/Custom.tpl';
    }
    function setTitle($title)
    {
        if ($title) {
            CRM_Utils_System::setTitle($title);
        } else {
            CRM_Utils_System::setTitle(ts('Search'));
        }
    }
}