<?php
    include_once "Field.php";
    //! A special field for getting data data. This field presents the user
    //! with three fields. One for the day, the month and the year. The data
    //! returned by this Field is similar to that required by the mysql date
    //! type (YYYY-MM-DD). It also validates the date to ensure that the date
    //! entered is correct.
    //! \ingroup Form_API
    class DateField extends Field
    {
        //! The field to store the values for the day.
        protected $dayField;

        //! The field to store the values for the month.
        protected $monthField;

        //! The field to store the year.
        protected $yearField;

        public function __construct($label="",$name="",$description="")
        {
            Field::__construct($name);
            Element::__construct($label,$description);

            //Setup the field to contain the days.
            $this->dayField = new SelectionList("Day",$name."_day");
            for($i=1; $i<32; $i++)
            {
                $this->dayField->addOption(strval($i),strval($i));
            }
            $this->dayField->parent = $this;

            //Setup the Month Field
            $this->monthField = new MonthField("Month",$name."_month");
            $this->monthField->parent = $this;

            //Setup the year field
            $this->yearField = new TextField("Year", $name."_year");
            $this->yearField->setAsNumeric(1900,4000);
            $this->yearField->parent = $this;
        }

        public function render()
        {
            $ret = "<div style='float:left'><span class='fapi-description'>Day</span><br />";
            $ret .= $this->dayField->render();
            $ret .= "</div>
                       <div style='float:left'><span class='fapi-description'>Month</span><br />";
            $ret .= $this->monthField->render();
            $ret .= "</div>
                       <div style='float:left'><span class='fapi-description'>Year</span><br />";
            $ret .= $this->yearField->render();
            $ret .= "</div><p style='clear:both'></p>";
            return $ret;
        }

        public function setValue($value)
        {
        	// Blank out dates which are empty to prevent unnecessary
        	// validation errors.

            if($value=="0000-00-00") $value="";
            parent::setValue($value);
            $values = explode("-",$value);

            $this->yearField->setValue($values[0]);
            $this->monthField->setValue($values[1]);
            $this->dayField->setValue($values[2]);
        }

        public function setMethod($method)
        {
            Element::setMethod($method);
            $this->dayField->setMethod($method);
            $this->monthField->setMethod($method);
            $this->yearField->setMethod($method);
        }

        public function getValue()
        {
            if($this->dayField->getValue()=="" || $this->monthField->getValue()=="" || $this->yearField->getValue()=="")
            {
                return "";
            }
            else
            {
                return $this->yearField->getValue()."-".$this->monthField->getValue()."-".$this->dayField->getValue();
            }
        }

        public function getData($storable=false)
        {
            $this->dayField->getData();
            $this->yearField->getData();
            $this->monthField->getData();
            return array($this->getName(false) => $this->getValue());
        }

        public function validate()
        {
            if(parent::validate())
            {
                $ret = true;
                if( $this->dayField->getValue()!="" ||
                    $this->monthField->getValue()!="" ||
                    $this->yearField->getValue()!="")
                {
                    $ret = $this->dayField->validate();
                    $ret = $this->monthField->validate();
                    $ret = $this->yearField->validate();
                }

                if(!$ret)
                {
                    $this->error = true;
                    array_push($this->errors,"Please ensure that the date entered is valid");
                }
                return $ret;
            }
            else
            {
                return false;
            }
        }

        public function getDatabaseTable()
        {

        }
    }
?>
