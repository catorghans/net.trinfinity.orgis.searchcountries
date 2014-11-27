<?php

/**
 * A custom contact search
 */
class CRM_Searchcountries_Form_Search_CountriesSearch extends CRM_Contact_Form_Search_Custom_Base implements CRM_Contact_Form_Search_Interface {
  function __construct(&$formValues) {
    parent::__construct($formValues);
  }

  /**
   * Prepare a set of search fields
   *
   * @param CRM_Core_Form $form modifiable
   * @return void
   */
  function buildForm(&$form) {
    CRM_Utils_System::setTitle(ts('My Search Title'));

    //@todo FIXME - using the CRM_Core_DAO::VALUE_SEPARATOR creates invalid html - if you can find the form
    // this is loaded onto then replace with something like '__' & test
    $separator = CRM_Core_DAO::VALUE_SEPARATOR;
    $contactTypes = array('' => ts('- any contact type -')) + CRM_Contact_BAO_ContactType::getSelectElements(FALSE, TRUE, $separator);
    $form->addElement('select', 'contact_type', ts('Find...'), $contactTypes, array('class' => 'crm-select2 huge'));

    $countries = array('' => ts('- country -')) + CRM_Core_PseudoConstant::country();
    $form->addElement('select', 'country_ids', ts('Country'), $countries, array('multiple' => 'multiple', 'class' => 'crm-select2 huge'));

    // Optionally define default search values
    $form->setDefaults(array(
      'household_name' => '',
      'state_province_id' => NULL,
      'country_ids' => NULL,
    ));

    /**
     * if you are using the standard template, this array tells the template what elements
     * are part of the search criteria
     */
    $form->assign('elements', array('contact_type', 'household_name', 'state_province_id', 'country_ids'));
  }

  /**
   * Get a list of summary data points
   *
   * @return mixed; NULL or array with keys:
   *  - summary: string
   *  - total: numeric
   */
  function summary() {
    return NULL;
    // return array(
    //   'summary' => 'This is a summary',
    //   'total' => 50.0,
    // );
  }

  /**
   * Get a list of displayable columns
   *
   * @return array, keys are printable column headers and values are SQL column names
   */
  function &columns() {
    // return by reference
    $columns = array(
      ts('Contact Id') => 'contact_id',
      ts('Contact Type') => 'contact_type',
      ts('Name') => 'sort_name',
      ts('Country') => 'country',
    );
    return $columns;
  }

  /**
   * Construct a full SQL query which returns one page worth of results
   *
   * @return string, sql
   */
  function all($offset = 0, $rowcount = 0, $sort = NULL, $includeContactIDs = FALSE, $justIDs = FALSE) {

    // delegate to $this->sql(), $this->select(), $this->from(), $this->where(), etc.
    return $this->sql($this->select(), $offset, $rowcount, $sort, $includeContactIDs, NULL);
  }

  /**
   * Construct a SQL SELECT clause
   *
   * @return string, sql fragment with SELECT arguments
   */
  function select() {
    return "
      contact_a.id           as contact_id  ,
      contact_a.contact_type as contact_type,
      contact_a.sort_name    as sort_name,
      country.name as country
    ";
  }

  /**
   * Construct a SQL FROM clause
   *
   * @return string, sql fragment with FROM and JOIN clauses
   */
  function from() {
    return "
      FROM      civicrm_contact contact_a
      LEFT JOIN civicrm_address address ON ( address.contact_id       = contact_a.id AND
                                             address.is_primary       = 1 )
      LEFT JOIN civicrm_email           ON ( civicrm_email.contact_id = contact_a.id AND
                                             civicrm_email.is_primary = 1 )
      LEFT JOIN civicrm_country country ON country.id = address.country_id
    ";
  }

  /**
   * Construct a SQL WHERE clause
   *
   * @return string, sql fragment with conditional expressions
   */
  function where($includeContactIDs = FALSE) {
    $params = array();


      $where = " contact_a.id = contact_a.id ";



    $count  = 1;
    $clause = array();

    $contact_type = CRM_Utils_Array::value('contact_type',
      $this->_formValues
    );

    if ($contact_type) {
      $params[$count] = array($contact_type, 'String');
      $clause[] = "contact_a.contact_type = %{$count}";
      $count++;
    }


    $countries = CRM_Utils_Array::value('country_ids',
      $this->_formValues
    );

    if ($countries) {
      // $params[$count] = array ($countries, 'Integer');
//       $clause[] = "country.id IN (".implode('',$countries).")";    
       $clause[] = "country.id IN (".implode(',',$countries).")";
//       $count++;
    }
 

    if (!empty($clause)) {
      $where .= ' AND '.implode(' AND ', $clause);
    }

  
    return $this->whereClause($where, $params);
  }

  /**
   * Determine the Smarty template for the search screen
   *
   * @return string, template path (findable through Smarty template path)
   */
  function templateFile() {
    return 'CRM/Contact/Form/Search/Custom.tpl';
  }

  /**
   * Modify the content of each row
   *
   * @param array $row modifiable SQL result row
   * @return void
   */
  function alterRow(&$row) {
//    $row['sort_name'] .= ' ( altered )';
  }
}
