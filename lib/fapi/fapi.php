<?php
/**
 * @page fapi_page The Form API
 * The Form API is an api in the ntentan framework which makes it possible to
 * define forms in PHP and have the forms rendered in HTML. This API provides server
 * side validation and also an interface which makes it possible to dump the data
 * collected into databases or ntentan data models. The form API is made up of a
 * collection of classes which are all based on a single Element class. This API
 * is very extensible.
 *
 * @section how_fapi_works How the form API Works
 * The form api is orgarnised around the Element class. This class is extended
 * by two other abstract classes; Field and Container. Subclasses of the Container
 * class are used to contain other Elements. Subclasses of the Field class
 * are used to collect the form's data. Some of the subclasses of the container
 * class are the Form, FieldSet and BoxContainer class. Some of the subclasses
 * of the Field class are the TextField, TextArea etc.
 *
 * @section using_fapi Using the form API
 * The following listing shows a simple usage of the form api. This listing
 * creates a simple form intended to get information to be stored in an
 * address book. Embeding this code anywhere within a PHP document should cause the
 * document to render an HTML form definition.
 *
 * \code
 * <?php
 * $form = new Form();
 * $fieldset = new FieldSet("Address Book");
 * $form->add($fieldset);
 *
 * //Create a field. The first parameter of the constructor is the label.
 * //The second parameter is the name and the third is a description.
 * $firstname = new TextField("Firstname","firstname","The firstname of the person.");
 * $fieldset->add($firstname);
 *
 * $lastname = new TextField("Lastname","lastname","The lastname of the person.");
 * $fieldset->add($lastname);
 *
 * $email = new EmailField("Email","email","The e-mail address of the person.");
 * $fieldset->add($email);
 *
 * $address = new TextArea("Address","address","The address of the person");
 * $fieldset->add($address);
 *
 * $form->render();
 * ?>
 * \endcode
 *
 * \subsection intercepting_data Intercepting the Form data.
 * By default the form transmits all its data using the HTTP post method. This
 * can be changed by calling the setMethod method of the Form class.
 * \code
 * $form->setMethod("GET");
 * \endcode
 * Although either GET or POST methods are used to send the form,
 * to intercept the data that was entered into the form, it is
 * advisable to use a call back function within your program. This is
 * because for security reasons, the element names sent are sometimes
 * encrypted to prevent the HTML code from containing any information
 * about the internal database structure. A call back function is just
 * any regular function within your program. You pass the function name
 * as a string to the form class by calling the \c setCallback() method.
 *
 * \code
 * $form->setCallback("my_function")
 * \endcode
 *
 * The function passed should accept one parameter. This parameter would
 * contain a structured array whose keys are the field names, and values
 * are the values passed to the form. Adding a callback method to the
 * above code would give the following.
 *
 * \code
 * <?php
 * function form_callback($data)
 * {
 *     print_r($data);
 * }
 *
 * $form = new Form();
 * $form->setCallback("form_callback");
 * $fieldset = new FieldSet("Address Book");
 * $form->add($fieldset);
 *
 * //Create a field. The first parameter of the constructor is the label.
 * //The second parameter is the name and the third is a description.
 * $firstname = new TextField("Firstname","firstname","The firstname of the person.");
 * $fieldset->add($firstname);
 *
 * $lastname = new TextField("Lastname","lastname","The lastname of the person.");
 * $fieldset->add($lastname);
 *
 * $email = new EmailField("Email","email","The e-mail address of the person.");
 * $fieldset->add($email);
 *
 * $address = new TextArea("Address","address","The address of the person");
 * $fieldset->add($address);
 *
 * $form->render();
 * ?>
 * \endcode
 *
 * After this interception you can do anything you want to do with the data in the callback
 * function.
 *
 * \subsection interacting_with_database Interacting with the Database
 * To automatically store the data in the database, you have to
 * create a database table which has its field names the same as the field names
 * of the form elements. Then you can call the \c setDatabaseTable() method.
 * This would automatically generate a query and store the data into the database,
 * whenever the form is submitted.
 *
 * \code
 * $form->setDatabaseTable("address_table");
 * \endcode
 *
 * To retrieve data which is already in the database for updating or editing,
 * you have to set the database table, the primary key field in the table
 * and the value of the primary key. This could be done as shown below.
 *
 * \code
 * $form->setDatabaseTable("address_table");
 * $form->setPrimaryKeyField("address_id");
 * $form->setPrimaryKeyValue(2);
 * \endcode
 *
 * \subsection Using Database Models
 * The Ntentan framework comes with API's for handling model data. These models
 * are stored in databases. To use models to store your form data you have to
 * create an instance of the model and assign this instance to your form.
 *
 * \code
 * $model = Model::load("clients.addresses");
 * $form = new Form();
 * $form->setModel($model);
 * \endcode
 *
 * To retrieve or modify data which is already in the forms, you still set the
 * primary key field and the primary key value.
 *
 * \code
 * $form->setDatabaseTable("address_table");
 * $form->setPrimaryKeyField("address_id");
 * $form->setPrimaryKeyValue(2);
 * \endcode
 *
 */

/**
 * The form api used for the generation of forms in much of the system.
 * \defgroup Form_API
 * \see \link fapi_page \endlink
 */
include_once ("Forms/Form.php");
include_once ("Forms/TextField.php");
include_once ("Forms/Fieldset.php");
include_once ("Forms/Checkbox.php");
include_once ("Forms/RadioGroup.php");
include_once ("Forms/RadioButton.php");
include_once ("Forms/SelectionList.php");
include_once ("Forms/TextArea.php");
include_once ("Forms/TableLayout.php");
include_once ("Forms/EmailField.php");
include_once ("Forms/DatabaseColumnField.php");
include_once ("Forms/DatabaseColumnField2.php");
include_once ("Forms/MonthField.php");
include_once ("Forms/DateField.php");
include_once ("Forms/Tab.php");
include_once ("Forms/TabLayout.php");
include_once ("Forms/PasswordField.php");
include_once ("Forms/BoxContainer.php");
include_once ("Forms/Label.php");
include_once ("Forms/FileUploadField.php");
include_once ("Forms/Button.php");
include_once ("Forms/SubmitButton.php");
include_once ("Forms/ModelField.php");
include_once ("Forms/ButtonBar.php");
include_once ("Forms/MultiForms.php");
include_once ("Forms/ColumnContainer.php");
include_once ("Forms/ModelSearchField.php");
?>
