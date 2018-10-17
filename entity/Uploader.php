<?php
if(!defined('ROOT_DIR')){
    header("HTTP/1.1 403 Forbidden");
    die(''
        . '<!DOCTYPE html>'
        . '<html>'
        . '<head>'
        . '<title>Forbidden</title>'
        . '</head>'
        . '<body>'
        . '<h1>403 - Forbidden</h1>'
        . '<hr>'
        . '<p>'
        . 'Direct access not allowed.'
        . '</p>'
        . '</body>'
        . '</html>');
}
/**
 * A helper class that is used to upload files to the server file system.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.2
 */
class Uploader implements JsonI{
    /**
     * An array of supported file types and their MIME types.
     * @var array 
     * @since 1.1
     */
    const ALLOWED_FILE_TYPES = array(
        //audio and video
        'avi'=>array(
            'mime'=>'video/avi',
            'ext'=>'avi'
        ),
        'mp3'=>array(
            'mime'=>'audio/mpeg',
            'ext'=>'mp3'
        ),
        '3gp'=>array(
            'mime'=>'video/3gpp',
            'ext'=>'3gp'
        ),
        'mp4'=>array(
            'mime'=>'video/mp4',
            'ext'=>'mp4'
        ),
        'mov'=>array(
            'mime'=>'video/quicktime',
            'ext'=>'mov'
        ),
        'wmv'=>array(
            'mime'=>'video/x-ms-wmv',
            'ext'=>'wmv'
        ),
        'mov'=>array(
            'mime'=>'video/quicktime',
            'ext'=>'mov'
        ),
        'flv'=>array(
            'mime'=>'video/x-flv',
            'ext'=>'flv'
        ),
        'midi'=>array(
            'mime'=>'audio/midi',
            'ext'=>'midi'
        ),
        //images 
        'jpeg'=>array(
            'mime'=>'image/jpeg',
            'ext'=>'jpeg'
        ),
        'jpg'=>array(
            'mime'=>'image/jpeg',
            'ext'=>'jpg'
        ),
        'png'=>array(
            'mime'=>'image/png',
            'ext'=>'png'
        ),
        'bmp'=>array(
            'mime'=>'image/bmp',
            'ext'=>'bmp'
        ),
        'ico'=>array(
            'mime'=>'image/x-icon',
            'ext'=>'ico'
        ),
        //pdf 
        'pdf'=>array(
            'mime'=>'application/pdf',
            'ext'=>'pdf'
        ),
        //MS office documents
        'doc'=>array(
            'mime'=>'application/msword',
            'ext'=>'doc'
        ),
        'docx'=>array(
            'mime'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'ext'=>'docx'
        ),
        'xls'=>array(
            'mime'=>'application/vnd.ms-excel',
            'ext'=>'xls'
        ),
        'ppt'=>array(
            'mime'=>'application/vnd.ms-powerpoint',
            'ext'=>'ppt'
        ),
        'pptx'=>array(
            'mime'=>'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'ext'=>'pptx'
        ),
        'xlsx'=>array(
            'mime'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ext'=>'xlsx'
        ),
        //other text based files
        'txt'=>array(
            'mime'=>'text/plain',
            'ext'=>'txt'
        ),
        'php'=>array(
            'mime'=>'text/plain',
            'ext'=>'php'
        ),
        'css'=>array(
            'mime'=>'text/css',
            'ext'=>'css'
        ),
        'js'=>array(
            'mime'=>'text/javascribt',
            'ext'=>'js'
        ),
        'asm'=>array(
            'mime'=>'text/x-asm',
            'ext'=>'asm'
        ),
        'java'=>array(
            'mime'=>'text/x-java-source',
            'ext'=>'java'
        ),
        'log'=>array(
            'mime'=>'text/plain',
            'ext'=>'log'
        ),
        'asp'=>array(
            'mime'=>'text/asp',
            'ext'=>'asp'
        ),
        //other files
        'zip'=>array(
            'mime'=>'application/zip',
            'ext'=>'zip'
        ),
        'exe'=>array(
            'mime'=>'application/vnd.microsoft.portable-executable',
            'ext'=>'exe'
        ),
        'psd'=>array(
            'mime'=>'application/octet-stream',
            'ext'=>'psd'
        ),
        'ai'=>array(
            'mime'=>'application/postscript',
            'ext'=>'ai'
        )
    );
    /**
     * An array which contains uploaded files.
     * @var array
     * @since 1.0 
     */
    private $files;
    /**
     * A constant to indicate that a file extension is not allowed to be uploaded.
     * @since 1.1
     */
    const UPLOAD_ERR_EXT_NOT_ALLOWED = -1;
    /**
     * A constant to indicate that a file already exist in upload directory.
     * @since 1.1
     */
    const UPLOAD_ERR_FILE_ALREADY_EXIST = -2;
    /**
     * The name of the index at which the file is stored in the array <b>$_FILES</b>.
     * @var string
     * @since 1.0
     */
    private $asscociatedName;
    /**
     * Upload status message.
     * @var string
     * @since 1.0 
     */
    private $uploadStatusMessage;
    /**
     * Creates new instance of the class.
     * @since 1.0
     */
    public function __construct() {
        Logger::logFuncCall(__METHOD__);
        $this->uploadStatusMessage = 'NO ACTION';
        $this->files = array();
        $this->setUploadDir('\\uploades');
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * The directory at which the file will be uploaded to.
     * @var string A directory. 
     * @since 1.0
     */
    private $uploadDir;
    /**
     * An array that contains all the allowed file types.
     * @var array An array of strings. 
     * @since 1.0
     */
    private $extentions = array();
    /**
     * Sets the directory at which the file will be uploaded to.
     * @param string $dir Upload Directory (such as '/files/uploads'). 
     * @return boolean If upload directory was updated, the function will 
     * return TRUE. If not updated, the function will return FALSE.
     * @since 1.0
     */
    public function setUploadDir($dir){
        Logger::logFuncCall(__METHOD__);
        $retVal = FALSE;
        $len = strlen($dir);
        Logger::log('Checking length...');
        if($len > 0){
            Logger::log('Trimming forward and backward slashes...');
            while($dir[$len] == '/' || $dir[$len] == '\\'){
                $tmpDir = trim($dir,'/');
                $dir = trim($tmpDir,'\\');
                $len = strlen($dir);
            }
            while($dir[0] == '/' || $dir[0] == '\\'){
                $tmpDir = trim($dir,'/');
                $dir = trim($tmpDir,'\\');
            }
            Logger::log('Finished.');
            Logger::log('Validating trimming result...');
            if(strlen($dir) > 0){
                $dir = str_replace('/', '\\', $dir);
                $this->uploadDir = !Util::isDirectory($dir) ? '\\'.$dir : $dir;
                Logger::log('New upload directory = \''.$this->uploadDir.'\'', 'debug');
                $retVal = TRUE;
            }
            else{
                Logger::log('Empty string after trimming.','warning');
            }
        }
        else{
            Logger::log('Empty string is given.', 'warning');
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Returns an array which contains all information about the uploaded 
     * files.
     * @return array
     * @since 1.0
     * 
     */
    public function getFiles() {
        return $this->files;
    }

    /**
     * Adds new extention to the array of allowed files types.
     * @param string $ext File extention. The extention should be 
     * included without suffix.(e.g. jpg, png, pdf)
     * @since 1.0
     */
    public function addExt($ext){
        Logger::logFuncCall(__METHOD__);
        Logger::log('$ext = \''.$ext.'\'','debug');
        Logger::log('Removing the suffix if any.');
        $extFix = str_replace('.', '', strtolower($ext));
        $len = strlen($extFix);
        $retVal = TRUE;
        Logger::log('Checking length...');
        if($len != 0){
            Logger::log('Validating  characters...');
            for($x = 0 ; $x < $len ; $x++){
                $ch = $extFix[$x];
                if($ch == '_' || ($ch >= 'a' && $ch <= 'z') || ($ch >= 'A' && $ch <= 'Z') || ($ch >= '0' && $ch <= '9')){
                    
                }
                else{
                    Logger::log('Invalid character found: \''.$ch.'\'.', 'warning');
                    $retVal = FALSE;
                    break;
                }
            }
            if($retVal === TRUE){
                $this->extentions[] = $extFix;
                Logger::log('Extention added.');
            }
            else{
                Logger::log('Extention not added.','warning');
            }
        }
        else{
            Logger::log('Empty string given.', 'warning');
            $retVal = FALSE;
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * Removes an extention from the array of allowed files types.
     * @param string $ext File extention. The extention should be included 
     * without suffix.(e.g. jpg, png, pdf)
     * @since 1.0
     */
    public function removeExt($ext){
        Logger::logFuncCall(__METHOD__);
        $loweCase = strtolower($ext);
        $count = count($this->extentions);
        $retVal = FALSE;
        for($x = 0 ; $x < $count ; $x++){
            if($this->extentions[$x] == $loweCase){
                unset($this->extentions[$x]);
                $retVal = TRUE;
            }
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * Returns the directory at which the file will be uploaded to.
     * @return string upload directory.
     * @since 1.0
     * 
     */
    public function getUploadDir(){
        return $this->uploadDir;
    }
    /**
     * Sets The name of the index at which the file is stored in the array $_FILES.
     * @param string $name The name of the index at which the file is stored in the array $_FILES.
     * The value of this property is usually equals to the HTML element that is used in 
     * the upload form.
     * @since 1.0
     */
    public function setAssociatedFileName($name){
        Logger::logFuncCall(__METHOD__);
        Logger::log('Passed value = \''.$name.'\'.', 'debug');
        $this->asscociatedName = $name;
        Logger::logFuncCall(__METHOD__);
    }
    /**
     * Returns the array that contains all allowed file types.
     * @return array
     * @since 1.0
     */
    public function getExts(){
        return $this->extentions;
    }
    /**
     * Returns MIME type of a file extension.
     * @param string $ext File extension without the suffix (such as 'jpg').
     * @return string|NULL If the extension MIME type is found, it will be 
     * returned. If not, the function will return NULL.
     * @since 1.0
     */
    public static function getMIMEType($ext){
        Logger::logFuncCall(__METHOD__);
        Logger::log('$ext = \''.$ext.'\'', 'debug');
        $lowerCase = strtolower($ext);
        $retVal = NULL;
        $x = self::ALLOWED_FILE_TYPES[$lowerCase];
        if($x !== NULL){
            Logger::log('MIME found.');
            $retVal = $x['mime'];
        }
        else{
            Logger::log('No MIME type was found for the given value.', 'warning');
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * Checks if uploaded file is allowed or not.
     * @param string $fileName The name of the file (such as 'image.png')
     * @return boolean If file extension is in the array of allowed types, 
     * the function will return TRUE.
     * @since 1.0
     */
    private function isValidExt($fileName){
        Logger::logFuncCall(__METHOD__);
        Logger::log('File name = \''.$fileName.'\'.', 'debug');
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $retVal = in_array($ext, $this->getExts(),TRUE) || in_array(strtolower($ext), $this->getExts(),TRUE);
        Logger::logReturnValue($retVal);
        Logger::logFuncCall(__METHOD__);
        return $retVal;
    }
    /**
     * Checks if PHP upload code is error or not.
     * @param int $code PHP upload code.
     * @return boolean If the given code does not equal to UPLOAD_ERR_OK, the 
     * function will return TRUE.
     * @since 1.0
     */
    private function isError($code){
        switch($code){
            case UPLOAD_ERR_OK:{
                $this->uploadStatusMessage = 'File Uploaded';
                return FALSE;
            }
            case UPLOAD_ERR_INI_SIZE:{
                $this->uploadStatusMessage = 'File Size is Larger Than '. (ini_get('upload_max_filesize')/1000).'KB. Found in php.ini.';
                break;
            }
            case UPLOAD_ERR_FORM_SIZE:{
                $this->uploadStatusMessage = 'File Size is Larger Than '.($this->getLimit()/1000).'KB';
                break;
            }
            case UPLOAD_ERR_PARTIAL:{
                $this->uploadStatusMessage = 'File Uploaded Partially';
                break;
            }
            case UPLOAD_ERR_NO_FILE:{
                $this->uploadStatusMessage = 'No File was Uploaded';
                break;
            }
            case UPLOAD_ERR_NO_TMP_DIR:{
                $this->uploadStatusMessage = 'Temporary Folder is Missing';
                break;
            }
            case UPLOAD_ERR_CANT_WRITE:{
                $this->uploadStatusMessage = 'Faild to Write File to Disk';
                break;
            }
        }
        return TRUE;
    }
    /**
     * Upload the file to the server.
     * @param bolean $replaceIfExist If a file with the given name found 
     * and this attribute is set to true, the file will be replaced.
     * @return array An array which contains uploaded files info. Each index 
     * will contain an associative array which has the following info:
     * <ul>
     * <li><b>file-name</b>: </li>
     * <li><b>size</b>: </li>
     * <li><b>upload-path</b>: </li>
     * <li><b>upload-error</b>: </li>
     * <li><b>is-exist</b>: </li>
     * <li><b>is-replace</b>: </li>
     * <li><b>mime</b>: </li>
     * <li><b>uploaded</b>: </li>
     * </ul>
     */
    public function upload($replaceIfExist = false){
        Logger::logFuncCall(__METHOD__);
        $this->files = array();
        Logger::log('Checking if request method is \'POST\'.');
        $reqMeth = $_SERVER['REQUEST_METHOD'];
        Logger::log('Request method = \''.$reqMeth.'\'.', 'debug');
        if($reqMeth == 'POST'){
            Logger::log('Checking if $_FILES[\''.$this->asscociatedName.'\'] is set...');
            $fileOrFiles = NULL;
            if(isset($_FILES[$this->asscociatedName])){
                $fileOrFiles = $_FILES[$this->asscociatedName];
                Logger::log('It is set.');
            }
            if($fileOrFiles !== null){
                if(gettype($fileOrFiles['name']) == 'array'){
                    Logger::log('Multiple files where found.');
                    //multi-upload
                    $filesCount = count($fileOrFiles['name']);
                    Logger::log('Number of files: \''.$filesCount.'\'.', 'debug');
                    for($x = 0 ; $x < $filesCount ; $x++){
                        $fileInfoArr = array();
                        $fileInfoArr['name'] = $fileOrFiles['name'][$x];
                        $fileInfoArr['size'] = $fileOrFiles['size'][$x];
                        $fileInfoArr['upload-path'] = $this->getUploadDir();
                        $fileInfoArr['upload-error'] = 0;
                        $fileInfoArr['url'] = 'N/A';
                        if(!$this->isError($fileOrFiles['error'][$x])){
                            if($this->isValidExt($fileInfoArr['name'])){
                                if(Util::isDirectory($this->getUploadDir()) == TRUE){
                                    $targetDir = $this->getUploadDir().'\\'.$fileInfoArr['name'];
                                    $targetDir = str_replace('\\', '/', $targetDir);
                                    if(!file_exists($targetDir)){
                                        $fileInfoArr['is-exist'] = 'NO';
                                        $fileInfoArr['is-replace'] = 'NO';
                                            if(move_uploaded_file($fileOrFiles["tmp_name"][$x], $targetDir)){
                                                if(function_exists('mime_content_type')){
                                                $fileInfoArr['mime'] = mime_content_type($fileInfoArr['upload-path']);
                                            }
                                            else{
                                                $ext = pathinfo($fileInfoArr['name'], PATHINFO_EXTENSION);
                                                $fileInfoArr['mime'] = self::getMIMEType($ext);
                                            }
                                            $fileInfoArr['uploaded'] = 'true';
                                        }
                                        else{
                                            $fileInfoArr['uploaded'] = 'NO';
                                        }
                                    }
                                    else{
                                        if(function_exists('mime_content_type')){
                                            $fileInfoArr['mime'] = mime_content_type($fileInfoArr['upload-path']);
                                        }
                                        else{
                                            $ext = pathinfo($fileInfoArr['name'], PATHINFO_EXTENSION);
                                            $fileInfoArr['mime'] = self::getMIMEType($ext);
                                        }
                                        $fileInfoArr['is-exist'] = 'true';
                                        if($replaceIfExist){
                                            $fileInfoArr['is-replace'] = 'true';
                                            
                                            unlink($targetDir);
                                            if(move_uploaded_file($fileOrFiles["tmp_name"][$x], $targetDir)){
                                                $fileInfoArr['uploaded'] = 'true';
                                            }
                                            else{
                                                $fileInfoArr['uploaded'] = 'false';
                                            }
                                        }
                                        else{
                                            $fileInfoArr['is-replace'] = 'false';
                                        }
                                    }
                                }
                                else{
                                    $fileInfoArr['upload-error'] = FileFunctions::NO_SUCH_DIR;
                                    $fileInfoArr['uploaded'] = 'false';
                                }
                            }
                            else{
                                $fileInfoArr['uploaded'] = 'false';
                                $fileInfoArr['upload-error'] = FileFunctions::NOT_ALLOWED;
                            }
                        }
                        else{
                            $fileInfoArr['uploaded'] = 'false';
                            $fileInfoArr['upload-error'] = $fileOrFiles['error'][$x];
                        }
                        array_push($this->files, $fileInfoArr);
                    }
                }
                else{
                    Logger::log('Single file upload.');
                    //single file upload
                    $fileInfoArr = array();
                    $fileInfoArr['name'] = $fileOrFiles['name'];
                    $fileInfoArr['size'] = $fileOrFiles['size'];
                    $fileInfoArr['upload-path'] = $this->getUploadDir();
                    $fileInfoArr['upload-error'] = 0;
                    $fileInfoArr['url'] = 'N/A';
                    $fileInfoArr['mime'] = 'N/A';
                    if(!$this->isError($fileOrFiles['error'])){
                        if($this->isValidExt($fileInfoArr['name'])){
                            if(Util::isDirectory($this->getUploadDir()) == TRUE){
                                $targetDir = $this->getUploadDir().'\\'.$fileInfoArr['name'];
                                $targetDir = str_replace('\\', '/', $targetDir);
                                if(!file_exists($targetDir)){
                                    $fileInfoArr['is-exist'] = 'false';
                                    $fileInfoArr['is-replace'] = 'false';
                                    if(move_uploaded_file($fileOrFiles["tmp_name"], $targetDir)){
                                        $fileInfoArr['uploaded'] = 'true';
                                        if(function_exists('mime_content_type')){
                                            $fileInfoArr['mime'] = mime_content_type($fileInfoArr['upload-path']);
                                        }
                                        else{
                                            $ext = pathinfo($fileInfoArr['name'], PATHINFO_EXTENSION);
                                            $fileInfoArr['mime'] = self::getMIMEType($ext);
                                        }
                                    }
                                    else{
                                        $fileInfoArr['uploaded'] = 'false';
                                    }
                                }
                                else{
                                    $fileInfoArr['is-exist'] = 'true';
                                    if(function_exists('mime_content_type')){
                                        $fileInfoArr['mime'] = mime_content_type($fileInfoArr['upload-path']);
                                    }
                                    else{
                                        $ext = pathinfo($fileInfoArr['name'], PATHINFO_EXTENSION);
                                        $fileInfoArr['mime'] = self::getMIMEType($ext);
                                    }
                                    if($replaceIfExist){
                                        $fileInfoArr['is-replace'] = 'true';
                                        unlink($targetDir);
                                        if(move_uploaded_file($fileOrFiles["tmp_name"], $targetDir)){
                                            $fileInfoArr['uploaded'] = 'true';
                                        }
                                        else{
                                            $fileInfoArr['uploaded'] = 'false';
                                        }
                                    }
                                    else{
                                        $fileInfoArr['is-replace'] = 'false';
                                    }
                                }
                            }
                            else{
                                $fileInfoArr['upload-error'] = FileFunctions::NO_SUCH_DIR;
                                $fileInfoArr['uploaded'] = 'false';
                            }
                        }
                        else{
                            $fileInfoArr['uploaded'] = 'false';
                            $fileInfoArr['upload-error'] = FileFunctions::NOT_ALLOWED;
                        }
                    }
                    else{
                        $fileInfoArr['uploaded'] = 'false';
                        $fileInfoArr['upload-error'] = $fileOrFiles['error'];
                    }
                    array_push($this->files, $fileInfoArr);
                }
            }
            else{
                Logger::log('The variable $_FILES[\''.$this->asscociatedName.'\'] is not set. No files uploaded.', 'warning');
            }
        }
        else{
            Logger::log('Invalid request method. No file(s) were uploaded', 'warning');
        }
        Logger::logFuncReturn(__METHOD__);
        return $this->files;
    }
    public function getAssociatedName(){
        return $this->asscociatedName;
    }
    /**
     * Returns a JSON representation of the object.
     * @return JsonX an object of type <b>JsonX</b>
     * @since 1.0
     */
    public function toJSON(){
        $j = new JsonX();
        $j->add('upload-directory', $this->getUploadDir());
        $j->add('allowed-types', $this->getExts());
        return $j;
    }
    public function __toString() {
        return $this->toJSON().'';
    }
}
