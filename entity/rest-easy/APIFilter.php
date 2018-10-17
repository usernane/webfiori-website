<?php

/* 
 * The MIT License
 *
 * * Copyright 2018 Ibrahim BinAlshikh, rest-easy (v1.4.2).
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
/**
 * A class used to filter request parameters.
 * @author Ibrahim Ali <ibinshikh@hotmail.com>
 * @version 1.2.1
 */
class APIFilter{
    /**
     * Supported input types.
     * @var array The values are:
     * <ul>
     * <li>string</li>
     * <li>integer</li>
     * <li>email</li>
     * <li>float</li>
     * <li>url</li>
     * <li>boolean</li>
     * <li>array</li>
     * </ul>
     * @since 1.0
     */
    const TYPES = array(
        'string','integer','email','float','url','boolean','array'
    );
    /**
     * An array that will contains filtered data.
     * @var array
     * @since 1.0 
     */
    private $inputs;
    /**
     * An array that contains non-filtered data (original).
     * @var array
     * @since 1.2 
     */
    private $nonFilteredInputs;
    /**
     * Array that contains filter definitions.
     * @var array
     * @since 1.0 
     */
    private $paramDefs = array();
    /**
     * Adds a new request parameter to the filter.
     * @param RequestParameter $reqParam The request parameter that will be added.
     * @since 1.1
     */
    public function addRequestPaameter($reqParam) {
        if($reqParam instanceof RequestParameter){
            $attribute = array(
                'parameter'=>$reqParam,
                'filters'=>array(),
                'options'=>array('options'=>array())
            );
            if($reqParam->getDefault() !== NULL){
                $attribute['options']['options']['default'] = $reqParam->getDefault();
            }
            if($reqParam->getCustomFilterFunction() != NULL){
                $attribute['options']['filter-func'] = $reqParam->getCustomFilterFunction();
            }
            $paramType = $reqParam->getType();
            if($paramType == 'integer'){
                if($reqParam->getMaxVal() !== NULL){
                    $attribute['options']['options']['max_range'] = $reqParam->getMaxVal();
                }
                if($reqParam->getMinVal() !== NULL){
                    $attribute['options']['options']['min_range'] = $reqParam->getMinVal();
                }
                array_push($attribute['filters'], FILTER_SANITIZE_NUMBER_INT);
                array_push($attribute['filters'], FILTER_VALIDATE_INT);
            }
            else if($paramType == 'float'){
                array_push($attribute['filters'], FILTER_SANITIZE_NUMBER_FLOAT);
            }
            else if($paramType == 'email'){
                array_push($attribute['filters'], FILTER_SANITIZE_EMAIL);
                array_push($attribute['filters'], FILTER_VALIDATE_EMAIL);
            }
            else if($paramType == 'url'){
                array_push($attribute['filters'], FILTER_SANITIZE_URL);
                array_push($attribute['filters'], FILTER_VALIDATE_URL);
            }
            else{
                array_push($attribute['filters'], FILTER_DEFAULT);
            }
            array_push($this->paramDefs, $attribute);
        }
    }
    /**
     * Returns the boolean value of given input.
     * @param type $boolean
     * @return boolean|string
     * @since 1.1
     */
    private function _filterBoolean($boolean) {
        $booleanLwr = strtolower($boolean);
        $boolTypes = array(
            't'=>TRUE,
            'f'=>FALSE,
            'yes'=>TRUE,
            'no'=>FALSE,
            '-1'=>FALSE,
            '1'=>TRUE,
            '0'=>FALSE,
            'true'=>TRUE,
            'false'=>FALSE,
            'on'=>TRUE,
            'off'=>FALSE,
            'y'=>TRUE,
            'n'=>FALSE,
            'ok'=>TRUE);
        if(isset($boolTypes[$booleanLwr])){
            return $boolTypes[$booleanLwr];
        }
        return 'INV';
    }
    /**
     * Converts a string to an array.
     * @param string $array A string in the format '[3,"hello",4.8,"",44,...]'.
     * @return string|array If the string has valid array format, an array 
     * which contains the values is returned. If has invalid syntax, the 
     * function will return the string 'INV'.
     * @since 1.2.1
     */
    private static function _filterArray($array) {
        $len = strlen($array);
        $retVal = 'INV';
        $arrayValues = array();
        if($len >= 2){
            if($array[0] == '[' && $array[$len - 1] == ']'){
                $tmpArrValue = '';
                for($x = 1 ; $x < $len - 1 ; $x++){
                    $char = $array[$x];
                    if($x + 1 == $len - 1){
                        $tmpArrValue .= $char;
                        $number = self::checkIsNumber($tmpArrValue);
                        if($number != 'INV'){
                            $arrayValues[] = $number;
                        }
                        else{
                            return $retVal;
                        }
                    }
                    else{
                        if($char == "\""){
                            $tmpArrValue = strtolower(trim($tmpArrValue));
                            if(strlen($tmpArrValue)){
                                if($tmpArrValue == 'true'){
                                    $arrayValues[] = TRUE;
                                }
                                else if($tmpArrValue == 'false'){
                                    $arrayValues[] = FALSE;
                                }
                                else if($tmpArrValue == 'null'){
                                    $arrayValues[] = NULL;
                                }
                                else{
                                    $number = self::checkIsNumber($tmpArrValue);
                                    if($number != 'INV'){
                                        $arrayValues[] = $number;
                                    }
                                    else{
                                        return $retVal;
                                    }
                                }
                            }
                            else{
                                $result = self::_parseStringFromArray($array, $x + 1, $len - 1);
                                if($result['parsed'] == TRUE){
                                    $x = $result['end'];
                                    $arrayValues[] = filter_var($result['string'], FILTER_SANITIZE_STRING);
                                    $tmpArrValue = '';
                                    continue;
                                }
                                else{
                                    return $retVal;
                                }
                            }
                        }
                        if($char == ','){
                            $tmpArrValue = strtolower(trim($tmpArrValue));
                            if($tmpArrValue == 'true'){
                                $arrayValues[] = TRUE;
                            }
                            else if($tmpArrValue == 'false'){
                                $arrayValues[] = FALSE;
                            }
                            else if($tmpArrValue == 'null'){
                                $arrayValues[] = NULL;
                            }
                            else{
                                $number = self::checkIsNumber($tmpArrValue);
                                if($number != 'INV'){
                                    $arrayValues[] = $number;
                                }
                                else{
                                    return $retVal;
                                }
                            }
                            $tmpArrValue = '';
                        }
                        else if($x + 1 == $len - 1){
                            $arrayValues[] = $tmpArrValue.$char;
                        }
                        else{
                            $tmpArrValue .= $char;
                        }
                    }
                }
                $retVal = $arrayValues;
            }
        }
        return $retVal;
    }
    /**
     * Checks if a given string represents an integer or float value. If yes, 
     * return its numeric value.
     * @param type $str
     * @return string
     */
    private static function checkIsNumber($str){
        $str = trim($str);
        $len = strlen($str);
        $isFloat = FALSE;
        $retVal = 'INV';
        for($y = 0 ; $y < $len ; $y++){
            $char = $str[$y];
            if($char == '.' && !$isFloat){
                $isFloat = TRUE;
            }
            else if($char == '-' && $y == 0){
                
            }
            else if($char == '.' && $isFloat){
                return $retVal;
            }
            else{
                if(!($char <= '9' && $char >= '0')){
                    return $retVal;
                }
            }
        }
        if($isFloat){
            $retVal = floatval($str);
        }
        else{
            $retVal = intval($str);
        }
        return $retVal;
    }
    /**
     * Extract string value from an array that is formed as string.
     * @param type $arr
     * @param type $start
     * @param type $len
     * @return boolean
     * @since 1.2.1
     */
    private static function _parseStringFromArray($arr,$start,$len){
        $retVal = array(
            'end'=>0,
            'string'=>'',
            'parsed'=>false
        );
        $str = "";
        for($x = $start ; $x < $len ; $x++){
            $ch = $arr[$x];
            if($ch == '"'){
                $str .= "";
                $retVal['end'] = $x;
                $retVal['string'] = $str;
                $retVal['parsed'] = TRUE;
                break;
            }
            else if($ch == '\\'){
                $x++;
                $nextCh = $arr[$x];
                if($ch != ' '){
                    $str .= '\\'.$nextCh;
                }
                else{
                    $str .= '\\ ';
                }
            }
            else{
                $str .= $ch;
            }
        }
        for($x = $retVal['end'] + 1 ; $x < $len ; $x++){
            $ch = $arr[$x];
            if($ch == ','){
                $retVal['parsed'] = TRUE;
                $retVal['end'] = $x;
                break;
            }
            else if($ch != ' '){
                $retVal['parsed'] = FALSE;
                break;
            }
        }
        return $retVal;
    }
    /**
     * Returns the array that contains request inputs.
     * @return array|NULL The array that contains request inputs. If no data was 
     * filtered, the function will return <b>NULL</b>.
     * @since 1.0
     */
    public function getInputs(){
        return $this->inputs;
    }
    /**
     * Returns the array that contains request inputs without filters applied.
     * @return array The array that contains request inputs.
     * @since 1.2
     */
    public final function getNonFiltered(){
        return $this->nonFilteredInputs;
    }

    /**
     * Filter GET parameters.
     * @since 1.0
     */
    public final function filterGET(){
        foreach ($this->paramDefs as $def){
            $name = $def['parameter']->getName();
            if(isset($_GET[$name])){
                $toBeFiltered = $_GET[$name];
                $this->nonFilteredInputs[$name] = $toBeFiltered;
                if(isset($def['options']['filter-func'])){
                    $filteredValue = '';
                    $arr = array(
                        'original-value'=>$toBeFiltered,
                    );
                    if($def['parameter']->applyBasicFilter() === TRUE){
                        if($def['parameter']->getType() == 'boolean'){
                            $filteredValue = $this->_filterBoolean(filter_var($toBeFiltered));
                        }
                        else if($def['parameter']->getType() == 'array'){
                            $filteredValue = $this->_filterArray(filter_var($toBeFiltered));
                        }
                        else{
                            $filteredValue = filter_var($toBeFiltered);
                            foreach ($def['filters'] as $val) {
                                $filteredValue = filter_var($filteredValue, $val, $def['options']);
                            }
                            if($filteredValue == FALSE){
                                $filteredValue = 'INV';
                            }
                        }
                        $arr['basic-filter-result'] = $filteredValue;
                    }
                    else{
                        $filteredValue = 'INV';
                        $arr['basic-filter-result'] = 'NOT APLICABLE';
                    }
                    $r = call_user_func($def['options']['filter-func'],$arr,$def['parameter']);
                    if($r === NULL){
                        $this->inputs[$name] = FALSE;
                    }
                    else{
                        $this->inputs[$name] = $r;
                    }
                    if($this->inputs[$name] == FALSE && $def['parameter']->getType() != 'boolean'){
                        $this->inputs[$name] = 'INV';
                    }
                }
                else{
                    if($def['parameter']->getType() == 'boolean'){
                        $this->inputs[$name] = $this->_filterBoolean(filter_var($toBeFiltered));
                    }
                    else if($def['parameter']->getType() == 'array'){
                        $this->inputs[$name] = $this->_filterArray(filter_var($toBeFiltered));
                    }
                    else{
                        $this->inputs[$name] = filter_var($toBeFiltered);
                        foreach ($def['filters'] as $val) {
                            $this->inputs[$name] = filter_var($this->inputs[$name], $val, $def['options']);
                        }
                        if($this->inputs[$name] === FALSE){
                            $this->inputs[$name] = 'INV';
                        }
                    }
                }
            }
            else{
                if($def['parameter']->isOptional()){
                    $this->inputs[$name] = $def['parameter']->getDefault();
                }
            }
        }
    }
    /**
     * Filter POST parameters.
     * @since 1.0
     */
    public final function filterPOST(){
        foreach ($this->paramDefs as $def){
            $name = $def['parameter']->getName();
            if(isset($_POST[$name])){
                $toBeFiltered = $_POST[$name];
                $this->nonFilteredInputs[$name] = $toBeFiltered;
                if(isset($def['options']['filter-func'])){
                    $filteredValue = '';
                    $arr = array(
                        'original-value'=>$toBeFiltered,
                    );
                    if($def['parameter']->applyBasicFilter() === TRUE){
                        if($def['parameter']->getType() == 'boolean'){
                            $filteredValue = $this->_filterBoolean(filter_var($toBeFiltered));
                        }
                        else if($def['parameter']->getType() == 'array'){
                            $filteredValue = $this->_filterArray(filter_var($toBeFiltered));
                        }
                        else{
                            $filteredValue = filter_var($toBeFiltered);
                            foreach ($def['filters'] as $val) {
                                $filteredValue = filter_var($filteredValue, $val, $def['options']);
                            }
                            if($filteredValue == FALSE){
                                $filteredValue = 'INV';
                            }
                        }
                        $arr['basic-filter-result'] = $filteredValue;
                    }
                    else{
                        $filteredValue = 'INV';
                        $arr['basic-filter-result'] = 'NOT APLICABLE';
                    }
                    $r = call_user_func($def['options']['filter-func'],$arr,$def['parameter']);
                    if($r === NULL){
                        $this->inputs[$name] = FALSE;
                    }
                    else{
                        $this->inputs[$name] = $r;
                    }
                    if($this->inputs[$name] == FALSE && $def['parameter']->getType() != 'boolean'){
                        $this->inputs[$name] = 'INV';
                    }
                }
                else{
                    if($def['parameter']->getType() == 'boolean'){
                        $this->inputs[$name] = $this->_filterBoolean(filter_var($toBeFiltered));
                    }
                    else if($def['parameter']->getType() == 'array'){
                        $this->inputs[$name] = $this->_filterArray(filter_var($toBeFiltered));
                    }
                    else{
                        $this->inputs[$name] = filter_var($toBeFiltered);
                        foreach ($def['filters'] as $val) {
                            $this->inputs[$name] = filter_var($this->inputs[$name], $val, $def['options']);
                        }
                        if($this->inputs[$name] === FALSE){
                            $this->inputs[$name] = 'INV';
                        }
                    }
                }
            }
            else{
                if($def['parameter']->isOptional()){
                    $this->inputs[$name] = $def['parameter']->getDefault();
                }
            }
        }
    }
    /**
     * Clears filter variables (parameters definitions, filtered inputs and non
     * -filtered inputs). 
     * @since 1.1
     */
    public function clear() {
        $this->paramDefs = array();
        $this->inputs = NULL;
        $this->nonFilteredInputs = NULL;
    }
}
