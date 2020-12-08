<?php
namespace Pressmind\ORM\Filter\Input;
use Pressmind\ORM\Filter\FilterInterface;
use \DateTime;
use \stdClass;
use \Exception;
/**
 * Class DatetimeFilter
 * @package PressmindBooking\Filter\Input
 */
class DatetimeFilter implements FilterInterface {

    private $_errors = [];

    /**
     * @param string|array|stdClass $pValue
     * @return bool|DateTime|null
     */
    public function filterValue($pValue)
    {
        /**return if given value is empty or already a DateTime object**/
        if(empty($pValue) || is_a($pValue, 'DateTime')) return $pValue;
        try {
            $value = null;
            /**convert simple date string (Y-m-d) to valid datetime string (Y-m-d 00:00:00)**/
            if(is_string($value) && strlen($pValue) == '10' && preg_match('/^(19|20)\d\d[-.](0[1-9]|1[012])[-.](0[1-9]|[12][0-9]|3[01])$/', $pValue) == 1) {
                $pValue .= '00:00:00';
            }
            /**it might happen that we receive a representation of a JSON DateTime Object, so we need to cover this case ...**/
            if((is_array($pValue) && isset($pValue['date'])) || (is_a($pValue, 'stdClass') && isset($pValue->date))) {
                if(is_array($pValue)) {
                    $pValue = $pValue['date'];
                } else {
                    $pValue = $pValue->date;
                }
                $value = DateTime::createFromFormat('Y-m-d H:i:s.000000', $pValue);
            } else {
                $value = DateTime::createFromFormat('Y-m-d H:i:s', $pValue);
            }
            return $value;
        } catch (Exception $e) {
            $this->_errors[] = $e->getMessage();
            return null;
        }
    }

    public function getErrors()
    {
        return $this->_errors;
    }
}
