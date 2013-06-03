<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');  

class Unit_tests_customers_model extends CI_Driver {
    private $CI;
    private $customer_role_id;
    
    /**
     * Class Constructor
     */
    public function __construct() {
        // CodeIgniter initialization.
        $this->CI =& get_instance();
        $this->CI->load->library('Unit_test');
        $this->CI->load->model('Customers_Model');
        
        // Use this when creating test records.
        $this->customer_role_id = $this->CI->db->get_where('ea_roles', array('slug' => DB_SLUG_CUSTOMER))->row()->id;
    }
    
    /**
     * Run all the available tests
     */
    public function run_all() {
        // All the methods whose names start with "test" are going to be 
        // executed. If you want a method to not be executed remove the 
        // "test" keyword from the beginning.
        $class_methods = get_class_methods('Unit_tests_customers_model');
        foreach ($class_methods as $method_name) {
            if (substr($method_name, 0, 5) === 'test_') {
                call_user_func(array($this, $method_name));
            }
        }
    }
    
    /////////////////////////////////////////////////////////////////////////
    // UNIT TESTS
    /////////////////////////////////////////////////////////////////////////   
    
    // TEST ADD() CUSTOMER METHOD
    private function test_add_insert() {
        // Insert new customer record.
        $customer_data = array(
                            'last_name' => 'Doe',
                            'first_name' => 'John',
                            'email' => 'alextselegidis@gmail.com',
                            'phone_number' => '0123456789',
                            'address' => 'Abbey Road 18',
                            'city' => 'London',
                            'zip_code' => '12345',
                            'id_roles' => $this->customer_role_id
                        );
        $customer_data['id'] = $this->CI->Customers_Model->add($customer_data);
        $this->CI->unit->run($customer_data['id'], 'is_int', 'Test add() customer (insert operation) has returned the new row id.');
        
        // Check if the record was successfully added to the database.
        $db_data = $this->CI->db->get_where('ea_users', array('id' => $customer_data['id']))->row_array();
        $are_the_same = TRUE;
        if ($customer_data['last_name'] != $db_data['last_name'] 
                || $customer_data['first_name'] != $db_data['first_name'] 
                || $customer_data['email'] != $db_data['email'] 
                || $customer_data['phone_number'] != $db_data['phone_number'] 
                || $customer_data['address'] != $db_data['address'] 
                || $customer_data['city'] != $db_data['city'] 
                || $customer_data['zip_code'] != $db_data['zip_code'] 
                || $customer_data['id_roles'] != $db_data['id_roles']) {
            $are_the_same = FALSE;
        }
        $this->CI->unit->run($are_the_same, TRUE, 'Test add() customer (insert operation) has successfully been added to the datbase.');
        
        // Delete inserted record.
        $this->CI->db->delete('ea_users', array('id' => $customer_data['id']));
    }
    
    private function test_add_update() {
        // Insert new customer record (will be updated later).
        $customer_data = array(
                            'last_name' => 'Doe',
                            'first_name' => 'John',
                            'email' => 'alextselegidis@gmail.com',
                            'phone_number' => '0123456789',
                            'address' => 'Abbey Road 18',
                            'city' => 'London',
                            'zip_code' => '12345',
                            'id_roles' => $this->customer_role_id
                        );
        $this->CI->db->insert('ea_users', $customer_data);
        $customer_data['id'] = intval($this->CI->db->insert_id());
        
        // Update customer record.
        $new_phone_number = 'THE PHONE NUMBER IS UPDATED';
        $customer_data['phone_number'] = $new_phone_number;
        $update_result = $this->CI->Customers_Model->add($customer_data);
        $this->CI->unit->run($update_result, 'is_int', 'Test add() customer (update operation) has returned the row id.');
        
        // Check if record was successfully updated.
        $db_phone_number = $this->CI->db->get_where('ea_users', array('id' => $customer_data['id']))->row()->phone_number;
        $this->CI->unit->run($customer_data['phone_number'], $db_phone_number, 'Test add() customer (update operation) has successfully updated the phone number field.');
        
        // Delete inserted record.
        $this->CI->db->delete('ea_users', array('id' => $customer_data['id']));
    }
    
    private function test_add_invalid_email() {
        // Prepare customer's data (email address is invalid).
        $customer_data = array(
                            'last_name' => 'Doe',
                            'first_name' => 'John',
                            'email' => 'THIS IS INVALID',
                            'phone_number' => '0123456789',
                            'address' => 'Abbey Road 18',
                            'city' => 'London',
                            'zip_code' => '12345',
                            'id_roles' => $this->customer_role_id
                        );
        
        $has_thrown_exception = FALSE;
        try {
            $this->CI->Customers_Model->add($customer_data);
        } catch(ValidationException $valExc) {
            $has_thrown_exception = TRUE;
        }
        
        $this->CI->unit->run($has_thrown_exception, TRUE, 'Test add() customer with invalid email address.');
    }
    
    private function test_add_missing_no_last_name() {
        // Prepare customer's data (last name field is missing).
        $customer_data = array(
                            'first_name' => 'John',
                            'email' => 'alextselegidis@gmail.com',
                            'phone_number' => '0123456789',
                            'address' => 'Abbey Road 18',
                            'city' => 'London',
                            'zip_code' => '12345',
                            'id_roles' => $this->customer_role_id
                        );
        
        $has_thrown_exception = FALSE;
        try {
            $this->CI->Customers_Model->add($customer_data);
        } catch(ValidationException $valExc) {
            $has_thrown_exception = TRUE;
        }
        
        $this->CI->unit->run($has_thrown_exception, TRUE, 'Test add() customer with no last name value provided.');
    }
    
    // TEST CUSTOMER EXISTS() METHOD
    private function test_exists() {
        // Insert new customer record (will be updated later).
        $customer_data = array(
                            'last_name' => 'Doe',
                            'first_name' => 'John',
                            'email' => 'alextselegidis@gmail.com',
                            'phone_number' => '0123456789',
                            'address' => 'Abbey Road 18',
                            'city' => 'London',
                            'zip_code' => '12345',
                            'id_roles' => $this->customer_role_id
                        );
        $this->CI->db->insert('ea_users', $customer_data);
        $customer_data['id'] = intval($this->CI->db->insert_id());
        
        // Test that exists returns true.
        $exists_result = $this->CI->Customers_Model->exists($customer_data);
        $this->CI->unit->run($exists_result, TRUE, 'Tests exists() with customer that exists.');
        
        // Delete inserted record.
        $this->CI->db->delete('ea_users', array('id' => $customer_data['id']));
    }
    
    private function test_exists_record_does_not_exist() {
        // Prepare customer's data with email that does not exist.
        $customer_data = array(
                            'last_name' => 'Doe',
                            'first_name' => 'John',
                            'email' => 'THIS DOES NOT EXIST',
                            'phone_number' => '0123456789',
                            'address' => 'Abbey Road 18',
                            'city' => 'London',
                            'zip_code' => '12345',
                            'id_roles' => $this->customer_role_id
                        );
        
        // Test that exists return false on email that doesn't exist.
        $exists_result = $this->CI->Customers_Model->exists($customer_data);
        $this->CI->unit->run($exists_result, FALSE, 'Test exists() method with customer data that does not exist in the database.');
    }
    
    private function test_exists_no_email_provided() {
        // Prepare customer's data with no email value.
        $customer_data = array(
                            'last_name' => 'Doe',
                            'first_name' => 'John',
                            'phone_number' => '0123456789',
                            'address' => 'Abbey Road 18',
                            'city' => 'London',
                            'zip_code' => '12345',
                            'id_roles' => $this->customer_role_id
                        );
        
        // Test that exists return false on email that doesn't exist.
        $has_thrown_exception = FALSE;
        try {
            $this->CI->Customers_Model->exists($customer_data);
        } catch (InvalidArgumentException $ia_exc) {
            $has_thrown_exception = TRUE;
        }

        $this->CI->unit->run($has_thrown_exception, TRUE, 'Test exists() method with email argument missing.');
    }
    
    // TEST DELETE() CUSTOMER METHOD
    private function test_delete() {
        // Insert new customer record.
        $customer_data = array(
                            'last_name' => 'Doe',
                            'first_name' => 'John',
                            'email' => 'alextselegidis@gmail.com',
                            'phone_number' => '0123456789',
                            'address' => 'Abbey Road 18',
                            'city' => 'London',
                            'zip_code' => '12345',
                            'id_roles' => $this->customer_role_id
                        );
        $this->CI->db->insert('ea_users', $customer_data);
        $customer_data['id'] = intval($this->CI->db->insert_id());
        
        // Delete new customer record.
        $delete_result = $this->CI->Customers_Model->delete($customer_data['id']);
        $this->CI->unit->run($delete_result, TRUE, 'Test delete() method returned TRUE.');
        
        // Check that the record is actually deleted (if not delete).
        $num_rows = $this->CI->db->get_where('ea_users', array('id' => $customer_data['id']))->num_rows();
        $this->CI->unit->run($num_rows, 0, 'Test delete() method has actually deleted the record from the db.');
        
        if ($num_rows > 0) {
            $this->CI->db->delete('ea_users', array('id' => $customer_data['id']));
        }
    }
    
    private function test_delete_record_that_does_not_exist() {
        $random_record_id = 879653245;
        
        $delete_result = $this->CI->Customers_Model->delete($random_record_id);
        $this->CI->unit->run($delete_result, FALSE, 'Test delete() method with customer id that does not exist.');
    }
    
    private function test_delete_record_with_invalid_argument() {
        $invalid_argument = 'THIS IS INVALID'; 
        $has_thrown_exception = FALSE;
        try {
            $this->CI->Customers_Model->delete($invalid_argument);
        } catch (InvalidArgumentException $ia_exc) {
            $has_thrown_exception = TRUE;
        }
        $this->CI->unit->run($has_thrown_exception, TRUE, 'Test delete() method with invalid argument (not integer).');
    }
    
    // TEST VALIDATE CUSTOMER DATA METHOD
    private function test_validate_data() {
        // Prepare customer's data.
        $customer_data = array(
                            'last_name' => 'Doe',
                            'first_name' => 'John',
                            'email' => 'alextselegidis@gmail.com',
                            'phone_number' => '0123456789',
                            'address' => 'Abbey Road 18',
                            'city' => 'London',
                            'zip_code' => '12345',
                            'id_roles' => $this->customer_role_id
                        );
        
        // Validate customers data.
        $validation_result = $this->CI->Customers_Model->validate_data($customer_data);
        $this->CI->unit->run($validation_result, TRUE, 'Test validate_data() method.');
    }
    
    private function test_validate_data_no_last_name_provided() {
        // Prepare customer's data (no last_name value provided).
        $customer_data = array(
                            'first_name' => 'John',
                            'email' => 'alextselegidis@gmail.com',
                            'phone_number' => '0123456789',
                            'address' => 'Abbey Road 18',
                            'city' => 'London',
                            'zip_code' => '12345',
                            'id_roles' => $this->customer_role_id
                        );
        
        // Validate customers data.
        $validation_result = $this->CI->Customers_Model->validate_data($customer_data);
        $this->CI->unit->run($validation_result, FALSE, 'Test validate_data() method without a last_name value.');
    }
    
    private function test_validate_data_invalid_email_address() {
        // Prepare customer's data (invalid email address).
        $customer_data = array(
                            'last_name' => 'Doe',
                            'first_name' => 'John',
                            'email' => 'THIS IS INVALID',
                            'phone_number' => '0123456789',
                            'address' => 'Abbey Road 18',
                            'city' => 'London',
                            'zip_code' => '12345',
                            'id_roles' => $this->customer_role_id
                        );
        
        // Validate customers data.
        $validation_result = $this->CI->Customers_Model->validate_data($customer_data);
        $this->CI->unit->run($validation_result, FALSE, 'Test validate_data() method with invalid email address.');
    }
    
    // TEST FIND RECORD ID METHOD
    private function test_find_record_id() {
        // Insert new customer to database.
        $customer_data = array(
                            'last_name' => 'Doe',
                            'first_name' => 'John',
                            'email' => 'alextselegidis@gmail.com',
                            'phone_number' => '0123456789',
                            'address' => 'Abbey Road 18',
                            'city' => 'London',
                            'zip_code' => '12345',
                            'id_roles' => $this->customer_role_id
                        );
        $this->CI->db->insert('ea_users', $customer_data);
        $inserted_id = intval($this->CI->db->insert_id());
        
        // Try to find the db id of the new customer record.
        $method_id = $this->CI->Customers_Model->find_record_id($customer_data);
        $this->CI->unit->run($inserted_id, $method_id, 'Test find_record_id() method.');
        
        // Delete inserted customer record.
        $this->CI->db->delete('ea_users', array('id' => $inserted_id));
    }
    
    private function test_find_record_id_without_email_address() {
        // Prepare customer's data without an email address.
        $customer_data = array(
                            'last_name' => 'Doe',
                            'first_name' => 'John',
                            'phone_number' => '0123456789',
                            'address' => 'Abbey Road 18',
                            'city' => 'London',
                            'zip_code' => '12345',
                            'id_roles' => $this->customer_role_id
                        );
        $has_thrown_exception = FALSE;
        try {
            $this->CI->Customers_Model->find_record_id($customer_data);
        } catch (InvalidArgumentException $ia_exc) {
            $has_thrown_exception = TRUE;
        }

        $this->CI->unit->run($has_thrown_exception, TRUE, 'Test find_record_id() without providing an email address.');
    }
    
    private function test_find_record_id_record_does_not_exist() {
        // Prepare customer's data with an email address that does not exist in db.
        $customer_data = array(
                            'last_name' => 'Doe',
                            'first_name' => 'John',
                            'email' => 'THIS EMAIL DOES NOT EXIST IN DB',
                            'phone_number' => '0123456789',
                            'address' => 'Abbey Road 18',
                            'city' => 'London',
                            'zip_code' => '12345',
                            'id_roles' => $this->customer_role_id
                        );
        $has_thrown_exception = FALSE;
        try {
            $this->CI->Customers_Model->find_record_id($customer_data);
        } catch (DatabaseException $db_exc) {
            $has_thrown_exception = TRUE;
        }

        $this->CI->unit->run($has_thrown_exception, TRUE, 'Test find_record_id() with email address that does not exist in db.');
    }
    
    // TEST GET BATCH METHOD    ---------------------------------------------------
    private function test_get_batch() {
        // Get all the customer rows without using the model.
        $db_data = $this->CI->db->get_where('ea_users', array('id_roles' => $this->customer_role_id))->result_array();
        // Get all the customer rows by using the model.
        $model_data = $this->CI->Customers_Model->get_batch();
        // Check that the two arrays are the same.
        $this->CI->unit->run($db_data, $model_data, 'Test get_batch() method.');
    }
    
    private function test_get_batch_with_where_clause() {
        // Insert new customer record.
        $customer_data = array(
                            'last_name' => 'Doe',
                            'first_name' => 'John',
                            'email' => 'alextselegidis@gmail.com',
                            'phone_number' => '0123456789',
                            'address' => 'Abbey Road 18',
                            'city' => 'London',
                            'zip_code' => '12345',
                            'id_roles' => $this->customer_role_id
                        );
        $this->CI->db->insert('ea_users', $customer_data);
        $customer_data['id'] = intval($this->CI->db->insert_id());
        
        // Get data without using the model.
        $no_model_data = $this->CI->db->get_where('ea_users', array('id' => $customer_data['id']))->result_array();
        
        // Get data by using the model. 
        $model_data = $this->CI->Customers_Model->get_batch(array('id' => $customer_data['id']));
        
        // Check that the data arrays are the same.
        $this->CI->unit->run($no_model_data, $model_data, 'Test get_batch() with where clause.');
        
        // Delete inserted record from database.
        $this->CI->db->delete('ea_users', array('id' => $customer_data['id']));
    }

    private function unabled_test_get_batch_with_invalid_where_clause() {
        // CodeIgniter auto raises an exception if the where section is invalid.
    }

    // TEST GET ROW METHOD
    private function test_get_row() {
        // Insert a new customer record. 
        $customer_data = array(
                            'last_name' => 'Doe',
                            'first_name' => 'John',
                            'email' => 'alextselegidis@gmail.com',
                            'phone_number' => '0123456789',
                            'address' => 'Abbey Road 18',
                            'city' => 'London',
                            'zip_code' => '12345',
                            'id_roles' => $this->customer_role_id
                        );
        $this->CI->db->insert('ea_users', $customer_data);
        $customer_data['id'] = intval($this->CI->db->insert_id());
        
        // Get the new customer record from db.
        $no_model_data = $this->CI->db->get_where('ea_users', array('id' => $customer_data['id']))->row_array();
        $model_data = $this->CI->Customers_Model->get_row($customer_data['id']);
        
        // Check that the row is the correct one.
        $this->CI->unit->run($no_model_data, $model_data, 'Test get_row() method');
        
        // Delete inserted customer record.
        $this->CI->db->delete('ea_users', array('id' => $customer_data['id']));
    }
    
    private function test_get_row_that_does_not_exist() {
        $random_record_id = 486868412;
        $row_data = $this->CI->Customers_Model->get_row($random_record_id);
        $this->CI->unit->run($row_data, NULL, 'Test get_row() with record id that does ' 
                . 'not exist in the database.');
    }
    
    private function test_get_row_with_invalid_argument() {
        $invalid_id = 'THIS IS NOT AN INTEGER';
        
        $has_thrown_exception = FALSE;
        try {
            $this->CI->Customers_Model->get_row($invalid_id);        
        } catch (InvalidArgumentException $ia_exc) {
            $has_thrown_exception = TRUE;
        }
        
        $this->CI->unit->run($has_thrown_exception, TRUE, 'Test get_row() with wrong argument.');
    }
    
    // TEST GET VALUE METHOD
    private function test_get_value() {
        // Insert new customer record.
        $customer_data = array(
                            'last_name' => 'Doe',
                            'first_name' => 'John',
                            'email' => 'alextselegidis@gmail.com',
                            'phone_number' => '0123456789',
                            'address' => 'Abbey Road 18',
                            'city' => 'London',
                            'zip_code' => '12345',
                            'id_roles' => $this->customer_role_id
                        );
        $this->CI->db->insert('ea_users', $customer_data);
        $customer_data['id'] = intval($this->CI->db->insert_id());
        
        // Get a specific value from the database.
        $model_value = $this->CI->Customers_Model->get_value('email', $customer_data['id']);
        
        // Check if the value was correctly fetched from the database.
        $this->CI->unit->run($model_value, $customer_data['email'], 'Test get_value() method.');
        
        // Delete inserted appointment record.
        $this->CI->db->delete('ea_users', array('id' => $customer_data['id']));
    }
    
    private function test_get_value_record_does_not_exist() {
        $random_record_id = 843521368768;
        
        $has_thrown_exception = FALSE;
        
        try {
            $this->CI->Customers_Model->get_value('email', $random_record_id);
        } catch (InvalidArgumentException $db_exc) {
            $has_thrown_exception = TRUE;
        }
        
        $this->CI->unit->run($has_thrown_exception, TRUE, 'Test get_value() with record id that does not exist.');
    }
    
    private function test_get_value_field_does_not_exist() {
        // Insert new customer record.
        $customer_data = array(
                            'last_name' => 'Doe',
                            'first_name' => 'John',
                            'email' => 'alextselegidis@gmail.com',
                            'phone_number' => '0123456789',
                            'address' => 'Abbey Road 18',
                            'city' => 'London',
                            'zip_code' => '12345',
                            'id_roles' => $this->customer_role_id
                        );
        $this->CI->db->insert('ea_users', $customer_data);
        $customer_data['id'] = intval($this->CI->db->insert_id());
        
        // Try to get record value with wrong field name.
        $wrong_field_name = 'THIS IS WRONG';
        $has_thrown_exception = FALSE;
        
        try {
            $this->CI->Customers_Model->get_value($wrong_field_name, $customer_data['id']);
        } catch (InvalidArgumentException $db_exc) {
            $has_thrown_exception = TRUE;
        }
        
        $this->CI->unit->run($has_thrown_exception, TRUE, 'Test get_value() with record id that does not exist.');
        
        // Delete inserted customer record.
        $this->CI->db->delete('ea_users', array('id' => $customer_data['id']));
    }
}

/* End of file Unit_tests_customers_model.php */
/* Location: ./application/libraries/Unit_tests/drivers/Unit_tests_customers_model.php */