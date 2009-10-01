<?php
    include_once "Field.php";
    //! A special field for getting data data. This field presents the user
    //! with three fields. One for the day, the month and the year. The data
    //! returned by this Field is similar to that required by the mysql date
    //! type (YYYY-MM-DD). It also validates the date to ensure that the date
    //! entered is correct.
    //! \ingroup Form_API
    class DateField extends TextField
    {
        public function __construct($label="",$name="",$description="")
        {
            parent::__construct($label,$name,$description);
        }

        public function render()
        {
        	$this->addCSSClass("fapi-textfield");
        	$this->addAttribute("class","fapi-sidefield ".$this->getCSSClasses());
        	$this->addAttribute("id",$this->getId());
        	$this->addAttribute("name",$this->getName());
        	$this->addAttribute("value",date("Y/m/d",(int)$this->getValue()));
        	$id = $this->getId();
            return "<input ".$this->getAttributes()." /><input class='fapi-sidebutton' type='button' value='..' onclick=\"$('#date-picker-$id').datepicker({altField:'#$id',changeYear:true,changeDate:true,maxDate:null,minDate:'-100y',maxDate:'+100y',dateFormat:'yy/mm/dd'}).slideToggle()\"><div class='fapi-datepicker' id='date-picker-$id'></div>";
        }

        public function setValue($value)
        {
			//print strtotime($value)."-".date("Y M d",strtotime($value));
			//var_dump($value);
			if(is_numeric($value))
			{
				parent::setValue($value);
			}
			else
			{
				parent::setValue(strtotime($value));
			}
			return $this;
        }

        /*public function setMethod($method)
        {
            Element::setMethod($method);
            $this->dayField->setMethod($method);
            $this->monthField->setMethod($method);
            $this->yearField->setMethod($method);
        }*/

       /* public function getValue()
        {
            /*if($this->dayField->getValue()=="" || $this->monthField->getValue()=="" || $this->yearField->getValue()=="")
            {
                return 0;
            }
            else
            {
                //return strtotime($this->yearField->getValue()."-".$this->monthField->getValue()."-".$this->dayField->getValue());
            }*/
        	/*var_dump($this->value);
        	return strtotime($this->value);
        //}*/

        /*public function getData($storable=false)
        {
            /*$this->dayField->getData();
            $this->yearField->getData();
            $this->monthField->getData();*/
            /*return array($this->getName(false) => $this->getValue());
        }*/

        /*public function validate()
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
        }*/

        /*public function getDatabaseTable()
        {

        }*/
    }
?>
