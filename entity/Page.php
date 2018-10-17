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
 * A class used to initialize main page components.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.9
 */
class Page{
    /**
     *
     * @var type 
     * @since 1.6
     */
    private $isDynamic;
    /**
     * The document that represents the page.
     * @var HTMLDoc 
     * @since 1.4
     */
    private $document;
    /**
     * The writing direction of the page.
     * @var string
     * @since 1.0 
     */
    private $contentDir;
    /**
     * A variable that is set to <b>TRUE</b> if page has footer.
     * @var boolean A variable that is set to <b>TRUE</b> if page has footer.
     * @since 1.2 
     */
    private $incFooter;
    /**
     * A variable that is set to <b>TRUE</b> if page has aside area.
     * @var boolean A variable that is set to <b>TRUE</b> if page has footer.
     * @since 1.2 
     */
    private $incAside;
    /**
     * A variable that is set to <b>TRUE</b> if page has header.
     * @var boolean A variable that is set to <b>TRUE</b> if page has header.
     * @since 1.2 
     */
    private $incHeader;
    /**
     * The language of the page.
     * @var string
     * @since 1.0 
     */
    private $contentLang;
    /**
     * The title of the page.
     * @var string
     * @since 1.0 
     */
    private $title;
    /**
     * The name of the website that will be appended with the title of 
     * the page.
     * @var string 
     * @since 1.8
     */
    private $websiteName;
    /**
     * The character or string that is used to separate web page title 
     * and website name.
     * @var string
     * @since 1.8 
     */
    private $titleSep;
    /**
     * The description of the page.
     * @var string
     * @since 1.0 
     */
    private $description;
    /**
     * The canonical page URL.
     * @var type 
     * @since 1.2
     */
    private $canonical;
    /**
     * An object of type <b>Theme</b> that contains loaded theme info.
     * @var Theme 
     * @since 1.6
     */
    private $theme;
    /**
     * The name of request page.
     * @var string
     * @since 1.6 
     */
    private $name;
    /**
     * Sets the canonical URL of the page.
     * @since 1.2
     * @param string $url The canonical URL of the page.
     */
    public function setCanonical($url){
        if(strlen($url) != 0){
            $this->canonical = $url;
        }
    }
    /**
     * Returns the canonical URL of the page.
     * @return NULL|string The function will return the  canonical URL of the page 
     * if set. If not, the function will return <b>NULL</b>.
     * @since 1.2
     */
    public function getCanonical() {
        return $this->canonical;
    }
    /**
     * Sets or gets the canonical URL of the page.
     * @param string $new [Optional] The new canonical URL.
     * @return string The canonical URL of the page.
     * @since 1.9
     */
    public static function canonical($new=null){
        $p = &Page::get();
        if($new != NULL && strlen($new) != 0){
            $p->setCanonical($new);
        }
        return $p->getCanonical();
    }
    private function __construct() {
        Logger::logFuncCall(__METHOD__);
        $this->document = new HTMLDoc();
        $this->setTitle('Default X');
        $this->setWebsiteName('My X Website');
        $this->setTitleSep('|');
        $this->contentDir = NULL;
        $this->description = NULL;
        $this->contentLang = NULL;
        $this->incFooter = TRUE;
        $this->incHeader = TRUE;
        $this->isDynamic = TRUE;
        $this->incAside = TRUE;
        $this->setWritingDir();
        WebsiteFunctions::get()->getSession()->initSession(FALSE, TRUE);
        $this->setCanonical(Util::getRequestedURL());
        $this->document->setLanguage($this->getLang());
        $headNode = new HeadNode(
            $this->getTitle().$this->getTitleSep().$this->getWebsiteName(),
            $this->getCanonical(),
            SiteConfig::get()->getBaseURL()
        );
        $this->document->setHeadNode($headNode);
        $headerNode = new HTMLNode();
        $headerNode->setID('page-header');
        $this->document->addChild($headerNode);
        $body = new HTMLNode();
        $body->setID('page-body');
        $asideNode = new HTMLNode();
        $asideNode->setID('side-content-area');
        $body->addChild($asideNode);
        $contentArea = new HTMLNode();
        $contentArea->setID('main-content-area');
        $body->addChild($contentArea);
        $this->document->addChild($body);
        $footerNode = new HTMLNode();
        $footerNode->setID('page-footer');
        $this->document->addChild($footerNode);
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Sets or returns the name of page website.
     * @param string $new [Optional] The new name to set. It must be not-empty 
     * string in order to update.
     * @return string The name of page website. Default is 'My Website'.
     * @since 1.9
     */
    public static function siteName($new=null) {
        $p = &Page::get();
        if($new != NULL && strlen($new) != 0){
            $p->setWebsiteName($new);
        }
        return $p->getWebsiteName();
    }
    /**
     * Returns the name of the website.
     * @return string The name of the website. If the name was not set 
     * using the function <b>Page::setWebsiteName()</b>, the returned value will 
     * be 'My Website'.
     * @since 1.8
     */
    public function getWebsiteName() {
        return $this->websiteName;
    }
    /**
     * Sets the name of the website.
     * @param string $name The name of the website that will be appended with the title of 
     * the page. It will be updated only if the given string is not empty. 
     * Also note that if page document was created, 
     * calling this function will set the value of the &lt;titlt&gt; node. 
     * The format of the title is <b>PAGE_NAME TITLE_SEP WEBSITE_NAME</b>. 
     * for example, if the page name is 'Home' and title separator is 
     * '|' and the name of the website is 'Programming Academia'. The title 
     * of the page will be 'Home | Programming Academia'.
     * @since 1.8
     */
    public function setWebsiteName($name) {
        if(strlen($name) != 0){
            $this->websiteName = $name;
            $this->setTitle($this->getTitle());
        }
    }
    /**
     * Sets or returns the string that is used to separate website name from 
     * the title of the page.
     * @param string $new [Optional] The new title separator.
     * @return string The string that is used to separate website name from 
     * the title of the page.
     * @since 1.9
     */
    public static function separator($new=null){
        $p = &Page::get();
        if($new != NULL && strlen($new) != 0){
            $p->setTitleSep($new);
        }
        return $p->getTitleSep();
    }
    /**
     * Returns the character or string that is used to separate web page title 
     * and website name.
     * @return string The character or string that is used to separate web page title 
     * and website name. If the separator was not set 
     * using the function <b>Page::setTitleSep()</b>, the returned value will 
     * be ' | '.
     * @since 1.8
     */
    public function getTitleSep() {
        return $this->titleSep;
    }
    /**
     * Sets the character or string that is used to separate web page title 
     * @param string $str The new character or string that will be used to 
     * separate page title and website name. It will be set only if it is not 
     * empty string. Also note that if page document was created, 
     * calling this function will set the value of the &lt;titlt&gt; node. 
     * The format of the title is <b>PAGE_NAME TITLE_SEP WEBSITE_NAME</b>. 
     * for example, if the page name is 'Home' and title separator is 
     * '|' and the name of the website is 'Programming Academia'. The title 
     * of the page will be 'Home | Programming Academia'.
     * @since 1.8
     */
    public function setTitleSep($str) {
        $trimmed = trim($str);
        if(strlen($trimmed) != 0){
            $this->titleSep = ' '.$trimmed.' ';
            $this->setTitle($this->getTitle());
        }
    }
    /**
     * Returns the name of requested page.
     * @return string The name of the requested page.
     * @since 1.6
     */
    public function getPageName() {
        return $this->name;
    }
    /**
     * Checks if the type of page will be dynamic or static.
     * @return boolean The function will return <b>TRUE</b> if document 
     * type is dynamic. Otherwise, the function will return <b>FALSE</b>.
     * @since 1.6
     */
    public function isDynamicDoc() {
        return $this->isDynamic;
    }
    /**
     * Adds a child node inside the body of a node given its ID.
     * @param HTMLNode $node The node that will be inserted.
     * @param string $parentNodeId The ID of the node that the given node 
     * will be inserted to.
     * @return boolean The function will return <b>TRUE</b> if the given node 
     * was inserted. If it is not, the function will return <b>FALSE</b>.
     * @since 1.9
     */
    public static function insert($node,$parentNodeId='main-content-area'){
        Logger::logFuncCall(__METHOD__);
        $retVal = Page::get()->insertNode($node, $parentNodeId);
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * Adds a child node inside the body of a node given its ID.
     * @param HTMLNode $node The node that will be inserted.
     * @param string $parentNodeId The ID of the node that the given node 
     * will be inserted to.
     * @return boolean The function will return <b>TRUE</b> if the given node 
     * was inserted. If it is not, the function will return <b>FALSE</b>.
     * @since 1.6
     */
    public function insertNode($node,$parentNodeId='') {
        Logger::logFuncCall(__METHOD__);
        Logger::log('Checking parent node ID...');
        $retVal = FALSE;
        if(strlen($parentNodeId) != 0){
            Logger::log('Checking if the given instance is of type \'HTMLNode\'...');
            if($node instanceof HTMLNode){
                Logger::log('Checking if parent node exist...');
                $parentNode = &$this->document->getChildByID($parentNodeId);
                if($parentNode instanceof HTMLNode){
                    $parentNode->addChild($node);
                    Logger::log('Node added.');
                    $retVal = TRUE;
                }
                else{
                    Logger::log('No node was found which has the ID \''.$parentNodeId.'\'.','warning');
                }
            }
            else{
                Logger::log('Given instance is not of type \'HTMLNode\'. No node was added.','warning');
            }
        }
        else{
            Logger::log('Invalid parent node ID. No node was added.','warning');
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * A single instance of the class.
     * @var Page 
     * @since 1.0
     */
    private static $instance;
    /**
     * Returns a single instance of 'Page'
     * @return Page an instance of 'Page'.
     * @since 1.0
     */
    public static function &get(){
        if(self::$instance != NULL){
            return self::$instance;
        }
        self::$instance = new Page();
        return self::$instance;
    }
    /**
     * Sets or gets the title of the page.
     * @param string $new [Optional] The title of the page. Note that if page document was created, 
     * calling this function will set the value of the &lt;titlt&gt; node. 
     * The format of the title is <b>PAGE_NAME TITLE_SEP WEBSITE_NAME</b>. 
     * for example, if the page name is 'Home' and title separator is 
     * '|' and the name of the website is 'Programming Academia'. The title 
     * of the page will be 'Home | Programming Academia'.
     * @since 1.9
     * @return string The title of the page.
     */
    public static function title($new=null) {
        $p = &Page::get();
        if($new != NULL && strlen($new) != 0){
            $p->setTitle($new);
        }
        return $p->getTitle();
    }
    /**
     * Sets the title of the page.
     * @param string $val The title of the page. If <b>NULL</b> is given, 
     * the title will not updated. Also note that if page document was created, 
     * calling this function will set the value of the &lt;titlt&gt; node. 
     * The format of the title is <b>PAGE_NAME TITLE_SEP WEBSITE_NAME</b>. 
     * for example, if the page name is 'Home' and title separator is 
     * '|' and the name of the website is 'Programming Academia'. The title 
     * of the page will be 'Home | Programming Academia'.
     * @since 1.0
     */
    public function setTitle($val){
        if($val != NULL){
            $this->title = $val;
            $this->document->getHeadNode()->setTitle($this->getTitle().$this->getTitleSep().$this->getWebsiteName());
        }
    }
    /**
     * Saves the page to a stand alone file (self contained).
     * @param string $path The location where the file will be written to 
     * (such as 'pages/system/view-users'. 'view-users' is the page name).
     *  Note that the last part of the 
     * path will be the name of the file. It must be without extention.
     * @param boolean $isDynamic Set to <b>TRUE</b> to save the file as 
     * dynamic PHP web page. If the user wants a plain HTML, set this attribute 
     * to <b>FALSE</b>.
     * @return boolean The function will return <b>TRUE</b> if the file is saved. 
     * If not, the function will return <b>FALSE</b>
     * @since 1.7
     * @deprecated since version 1.8
     */
    public function saveToFile($path,$isDynamic=false){
        if($isDynamic === TRUE){
            $path = str_replace('\\', '/', $path);
            $fArr = explode('/', $path);
            $name = $fArr[count($fArr) - 1];
            $f = fopen($path.'.php', 'w+');
            if($f != FALSE){
                $root = str_replace('\\', '/', ROOT_DIR);
                fwrite($f, "<?php\n");
                fwrite($f, 'require \''.$root.'/root.php\';'."\n");
                fwrite($f, '$page = Page::get();'."\n");
                fwrite($f, '$page->usingTheme(SiteConfig::get()->getBaseThemeName());'."\n");
                fwrite($f, '$page->setLang(WebsiteFunctions::get()->getSession()->getLang());'."\n");
                if($this->getLanguage() != NULL){
                    fwrite($f, '$page->usingLanguage();'."\n");
                    if($this->getLanguage()->get('pages/'.$name) === NULL){
                        fwrite($f, '// Notice: \'pages/'.$name.'\' is not set on loaded language.'."\n");
                        fwrite($f, '// Page title and description will not set because of that.'."\n");
                    }
                }
                else{
                    fwrite($f, '// Notice: Static page title and description will be used.'."\n");
                    fwrite($f, '$page->setTitle(\''.$this->getTitle().'\');'."\n");
                    fwrite($f, '$page->setDescription(\''.$this->getDescription().'\');'."\n");
                }
                fwrite($f, 'echo $page->getDocument();'."\n");
                fclose($f);
                return TRUE;
            }
            return FALSE;
        }
        else{
            return $this->getDocument()->saveToFile($path, TRUE, 'html');
        }
    }
    /**
     * Returns the document that is linked with the page.
     * @return HTMLDoc The document that is linked with the page.
     * @since 1.9
     */
    public static function &document() {
        return Page::get()->getDocument();
    }
    /**
     * Returns the document that is associated with the page.
     * @return HTMLDoc An object of type 'HTMLDoc'.
     * @throws Exception If page theme is not loaded.
     * @since 1.1
     */
    public function &getDocument(){
        return $this->document;
    }
    /**
     * Returns the title of the page.
     * @return string|NULL The title of the page. If the title is not set, 
     * the function will return <b>NULL</b>
     * @since 1.0
     */
    public function getTitle(){
        return $this->title;
    }
    /**
     * Checks if the selected theme is loaded or not.
     * @return boolean <b>TRUE</b> if loaded. <b>FALSE</b> if not loaded.
     * @since 1.1
     */
    public function isThemeLoaded(){
        return $this->theme instanceof Theme;
    }
    /**
     * Sets or gets the description of the page.
     * @param string $new [Optional] The description of the page.
     * @since 1.9
     * @return string The description of the page.
     */
    public static function description($new=null) {
        $p = &Page::get();
        if($new != NULL && strlen($new) != 0){
            $p->setDescription($new);
        }
        return $p->getDescription();
    }
    /**
     * Sets the description of the page.
     * @param string $val The description of the page. If <b>NULL</b> is given, 
     * description will not change.
     * @since 1.0
     */
    public function setDescription($val){
        if($val != NULL){
            $this->description = $val;
            if($this->document != NULL ){
                $headCh = &$this->document->getHeadNode()->children();
                $headChCount = $headCh->size();
                for($x = 0 ; $x < $headChCount ; $x++){
                    $node = &$headCh->get($x);
                    if($node->getAttributeValue('name') == 'description'){
                        $node->setAttribute('content',$val);
                        return;
                    }
                }
                $descNode = new HTMLNode('meta', FALSE);
                $descNode->setAttribute('name', 'description');
                $descNode->setAttribute('content', $val);
                $this->document->getHeadNode()->addChild($descNode);
            }
        }
    }
    /**
     * Returns the description of the page.
     * @return string|NULL The title of the page. If the description is not set, 
     * the function will return <b>NULL</b>
     * @since 1.0
     */
    public function getDescription(){
        return $this->description;
    }
   /**
    * Load the translation file based on the language code. The function uses 
    * two checks to load the translation. If the page language is set using 
    * the function Page::setLang(), then the language that will be loaded 
    * will be based on the value returned by the function Page::getLang(). If 
    * the language is not set, The function will throw an exception.
    * @since 1.0
    */
    public function usingLanguage(){
        if($this->getLang() != NULL){
            if($this->getLanguage() === NULL){
                Language::loadTranslation($this->getLang());
                $pageLang = $this->getLanguage();
                $this->setWritingDir($pageLang->getWritingDir());
            }
        }
        else{
            throw new Exception('Unable to load transulation. Page language is not set.');
        }
    }
    /**
     * Sets or gets language code of the page.
     * @param string $new A two digit language code such as AR or EN. 
     * An exception will be thrown if the given language is not supported.
     * @return string|NULL Two digit language code. In case language is not set, the 
     * function will return NULL
     * @see Page::setLang()
     * @throws Exception
     * @since 1.9
     */
    public static function lang($new=null){
        $p = &Page::get();
        if($new != NULL && strlen($new) == 2){
            $p->setLang($new);
        }
        return $p->getLang();
    }
    /**
     * Returns the language.
     * @return string|NULL Two digit language code. In case language is not set, the 
     * function will return NULL
     * @since 1.0
     */
    public function getLang(){
        return $this->contentLang;
    }
    /**
     * Sets the display language of the page.
     * @param string $lang a two digit language code such as AR or EN.
     * @return boolean True if the language was not set and its the first time to set. 
     * if it was set before, the method will return false.
     * @throws Exception If the language is not supported.
     * @since 1.0
    */
    public function setLang($lang='EN'){
        $langU = strtoupper(trim($lang));
        if(in_array($langU, SessionManager::SUPPORTED_LANGS)){
            $this->contentLang = $langU;
            if($this->document != NULL){
                $this->document->setLanguage($langU);
            }
        }
        else{
            throw new Exception('Unknown language code: '.$lang);
        }
    }
    /**
     * Display the page in the web browser.
     * @since 1.9
     */
    public static function render() {
        echo Page::get()->getDocument()->toHTML();
    }
    /**
     * Loads and returns translation based on page language code.
     * @return Language|NULL An object of type Language is returned 
     * if the language is loaded. Other than that, the function will return 
     * NULL.
     * @since 1.9
     */
    public static function &translation(){
        $p = &Page::get();
        if($p->getLang() != NULL){
            $p->usingLanguage();
            return $p->getLanguage();
        }
        $null = NULL;
        return $null;
    }
    /**
     * Returns the language variables based on loaded translation.
     * @return Language|NULL an object of type 'Language' if language 
     * is loaded. If no language found, 'NULL' is returned. This 
     * function should be called after calling the function 'Page::loadTranslation()' in 
     * order for the function to return non-null value.
     * @since 1.6
     */
    public function &getLanguage() {
        $loadedLangs = &Language::getLoadedLangs();
        if(isset($loadedLangs[$this->getLang()])){
            return $loadedLangs[$this->getLang()];
        }
        $null = NULL;
        return $null;
    }
    /**
     * Loads or returns page theme.
     * @param string [Optional] $name The name of the theme which will be 
     * loaded. If NULL is given, nothing will be loaded.
     * @return Theme|NULL If a theme is already loaded, the function will 
     * return the loaded theme contained in an object of type Theme. If no 
     * theme is loaded, the function will return NULL.
     * @see Page::usingTheme()
     * @since 1.9
     */
    public static function theme($name=null){
        $p = &Page::get();
        if($name != NULL && strlen($name) != 0){
            $p->usingTheme($name);
        }
        return $p->getTheme();
    }
    /**
     * Loads a theme given its name.
     * @param string $themeName [Optional] The name of the theme as specified by the 
     * variable 'name' in theme definition. If the given name is 'NULL', the 
     * function will load the default theme as specified by the function 
     * 'SiteConfig::getBaseThemeName()'. Note that once the theme is updated, 
     * the document content of the page will reset if it was set before calling this 
     * function.
     * @throws Exception The function will throw 
     * an exception if no theme was found which has the given name. Another case is 
     * when the file 'theme.php' of the theme is missing. 
     * Finally, an exception will be thrown if theme component is not found.
     * @since 1.4
     * @see Theme::usingTheme()
     */
    public function usingTheme($themeName=null) {
        Logger::logFuncCall(__METHOD__);
        Logger::log('Checking if given theme name is null...');
        if($themeName === NULL){
            Logger::log('Given value is null. Using theme name from configuration file.');
            $themeName = SiteConfig::get()->getBaseThemeName();
        }
        Logger::log('Theme name = \''.$themeName.'\'.', 'debug');
        Logger::log('Loading theme...');
        $tmpTheme = Theme::usingTheme($themeName);
        $this->theme = $tmpTheme;
        Logger::log('Theme loaded.');
        Logger::log('Constructing page document...');
        $this->document = new HTMLDoc();
        Logger::log('Initializing document head node...');
        $headNode = $this->_getHead(TRUE);
        Logger::log('Initializing document footer node...');
        $footerNode = $this->_getFooter(TRUE);
        Logger::log('Initializing document aside node...');
        $asideNode = $this->_getAside(TRUE);
        Logger::log('Initializing document header node...');
        $headerNode = $this->_getHeader(TRUE);
        $this->document->setLanguage($this->getLang());
        $this->document->setHeadNode($headNode);
        $this->document->addChild($headerNode);
        Logger::log('Creating document body...');
        $body = new HTMLNode();
        $body->setID('page-body');
        $body->addChild($asideNode);
        $mainContentArea = new HTMLNode();
        $mainContentArea->setID('main-content-area');
        $body->addChild($mainContentArea);
        $this->document->addChild($body);
        $this->document->addChild($footerNode);
        Logger::log('Document building finished.');
        Logger::log('Invoking after loaded function...');
        $this->theme->invokeAfterLoaded();
        Logger::log('Theme initialization finished.');
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Returns the directory at which CSS files of loaded theme exists.
     * @return string The directory at which CSS files of the theme exists 
     * (e.g. 'publish/my-theme/css' ). 
     * If the theme is not loaded, the function will return empty string.
     * @since 1.9
     */
    public static function cssDir(){
        return Page::get()->getThemeCSSDir();
    }
    /**
     * Returns the directory at which CSS files of the theme exists.
     * @return string The directory at which CSS files of the theme exists 
     * (e.g. 'publish/my-theme/css' ). 
     * If the theme is not loaded, the function will return empty string.
     * @since 1.6
     */
    public function getThemeCSSDir() {
        if($this->isThemeLoaded()){
            $theme = $this->getTheme();
            return Theme::THEMES_DIR.'/'.$theme->getDirectoryName().'/'.$theme->getCssDirName();
        }
        return '';
    }
    /**
     * Returns the directory at which image files of loaded theme exists.
     * @return string The directory at which image files of the theme exists 
     * (e.g. 'publish/my-theme/images' ). 
     * If the theme is not loaded, the function will return empty string.
     * @since 1.9
     */
    public static function imagesDir(){
        return Page::get()->getThemeImagesDir();
    }
    /**
     * Returns the directory at which image files of the theme exists.
     * @return string The directory at which image files of the theme exists 
     * (e.g. 'publish/my-theme/images' ). 
     * If the theme is not loaded, the function will return empty string.
     * @since 1.6
     */
    public function getThemeImagesDir() {
        if($this->isThemeLoaded()){
            $theme = $this->getTheme();
            return Theme::THEMES_DIR.'/'.$theme->getDirectoryName().'/'.$theme->getImagesDirName();
        }
        return '';
    }
    /**
     * Returns the directory at which JavaScript files of the theme exists.
     * @return string The directory at which JavaScript files of the theme exists 
     * (e.g. 'publish/my-theme/js' ). 
     * If the theme is not loaded, the function will return empty string.
     * @since 1.9
     */
    public static function jsDir(){
        return Page::get()->getThemeJSDir();
    }
    /**
     * Returns the directory at which JavaScript files of the theme exists.
     * @return string The directory at which JavaScript files of the theme exists 
     * (e.g. 'publish/my-theme/js' ). 
     * If the theme is not loaded, the function will return empty string.
     * @since 1.6
     */
    public function getThemeJSDir() {
        if($this->isThemeLoaded()){
            $theme = $this->getTheme();
            return Theme::THEMES_DIR.'/'.$theme->getDirectoryName().'/'.$theme->getJsDirName();
        }
        return '';
    }
    /**
     * Returns an object of type <b>Theme</b> that contains loaded theme information.
     * @return Theme|NULL An object of type <b>Theme</b> that contains theme information. If the theme 
     * is not loaded, the function will return <b>NULL</b>.
     * @since 1.6
     */
    public function getTheme() {
        return $this->theme;
    }
    /**
     * Sets or gets page writing direction.
     * @param string $new 'ltr' or 'rtl'.
     * @return string|NULL If the writing direction was set, 
     * the function will return it. If not, the function will return NULL.
     * @since 1.9
     */
    public static function dir($new=null) {
        $p = &Page::get();
        $lNew = strtolower($new);
        if($lNew == 'ltr' || $lNew == 'rtl'){
            $p->setWritingDir($new);
        }
        return $p->getWritingDir();
    }
    /**
     * Returns the writing direction of the page.
     * @return string|NULL 'ltr' or 'rtl'. If the writing direction is not set, 
     * the function will return <b>NULL</b>
     * @since 1.0
     */
    public function getWritingDir(){
        return $this->contentDir;
    }
    /**
     * Sets the writing direction of the page.
     * @param string $dir <b>Page::DIR_LTR</b> or <b>Page::DIR_RTL</b>.
     * @return boolean True if the direction was not set and its the first time to set. 
     * if it was set before, the method will return false.
     * @throws Exception If the writing direction is not <b>Page::DIR_LTR</b> or <b>Page::DIR_RTL</b>.
     * @since 1.0
     */
    function setWritingDir($dir='ltr'){
        $dirL = strtolower($dir);
        if($dirL == Language::DIR_LTR || $dirL == Language::DIR_RTL){
            $this->contentDir = $dirL;
            return TRUE;
        }
        else{
            throw new Exception('Unknown writing direction: '.$dir);
        }
    }
    /**
     * Sets the property that is used to check if page has a header section or not.
     * @param boolean $bool <b>TRUE</b> to include the header section. <b>FALSE</b> if 
     * not. <b>HAS BUG</b>
     * @since 1.2
     */
    public function setHasHeader($bool){
        if(gettype($bool) == 'boolean'){
            if($this->incHeader == FALSE && $bool == TRUE){
                $children = $this->document->getBody()->children();
                $this->document->getBody()->removeAllChildNodes();
                $this->document->addChild($this->_getHeader());
                for($x = 0 ; $x < $children->size() ; $x++){
                    $this->document->addChild($children->get($x));
                }
            }
            else if($this->incHeader == TRUE && $bool == FALSE){
                $header = $this->document->getChildByID('page-header');
                if($header instanceof HTMLNode){
                    $this->document->removeChild($header);
                }
            }
            $this->incHeader = $bool;
        }
    }
    
    public function setHasAside($bool){
        if(gettype($bool) == 'boolean'){
            if($this->incAside == FALSE && $bool == TRUE){
                
            }
            else if($this->incAside == TRUE && $bool == FALSE){
                $aside = $this->document->getChildByID('side-content-area');
                if($aside instanceof HTMLNode){
                    $this->document->removeChild($aside);
                }
            }
            $this->incAside = $bool;
        }
    }

    /**
     * Sets the property that is used to check if page has a footer section or not.
     * @param boolean $bool <b>TRUE</b> to include the footer section. <b>FALSE</b> if 
     * not. <b>HAS BUG</b>
     * @since 1.2
     */
    public function setHasFooter($bool){
        if(gettype($bool) == 'boolean'){
            if($this->incFooter == FALSE && $bool == TRUE){
                $this->document->addChild($this->_getFooter());
            }
            else if($this->incFooter == TRUE && $bool == FALSE){
                $footer = $this->document->getChildByID('page-footer');
                if($footer instanceof HTMLNode){
                    $this->document->removeChild($footer);
                }
            }
            $this->incFooter = $bool;
        }
    }
    /**
     * Sets or checks if the page will have aside area or not.
     * @param boolean|NULL $bool [Optional] If set to TRUE, the generated page 
     * will have a 'div' element with ID = 'side-content-area'. If set to 
     * FALSE, the generated page will have no such element.
     * @return boolean The function will return TRUE if the page will have 
     * aside area.
     */
    public static function aside($bool=null) {
        $p = &Page::get();
        if($bool !== NULL){
            $p->setHasAside($bool);
        }
        return $p->hasAside();
    }
    /**
     * Sets or checks if the page will have footer area or not.
     * @param boolean|NULL $bool [Optional] If set to TRUE, the generated page 
     * will have a 'div' element with ID = 'page-footer'. If set to 
     * FALSE, the generated page will have no such element.
     * @return boolean The function will return TRUE if the page will have 
     * footer area.
     */
    public static function footer($bool=null) {
        $p = &Page::get();
        if($bool !== NULL){
            $p->setHasFooter($bool);
        }
        return $p->hasFooter();
    }
    /**
     * Sets or checks if the page will have header area or not.
     * @param boolean|NULL $bool [Optional] If set to TRUE, the generated page 
     * will have a 'div' element with ID = 'page-header'. If set to 
     * FALSE, the generated page will have no such element.
     * @return boolean The function will return TRUE if the page will have 
     * header area.
     */
    public static function header($bool=null) {
        $p = &Page::get();
        if($bool !== NULL){
            $p->setHasHeader($bool);
        }
        return $p->hasHeader();
    }
    /**
     * Checks if the page will have a footer section or not.
     * @return boolean <b>TRUE</b> if the page has a footer section.
     * @since 1.2
     */
    public function hasFooter(){
        return $this->incFooter;
    }
    /**
     * Checks if the page will have a header section or not.
     * @return boolean <b>TRUE</b> if the page has a header section.
     * @since 1.2
     */
    public function hasHeader(){
        return $this->incHeader;
    }
    /**
     * Checks if the page will have an aside section or not.
     * @return boolean <b>TRUE</b> if the page has an aside section.
     * @since 1.6
     */
    public function hasAside() {
        return $this->incAside;
    }
    
    private function _getAside($new=false) {
        if($this->hasAside()){
            if($new === TRUE){
                if(function_exists('getAsideNode')){
                    $h = getAsideNode();
                    $h->setID('side-content-area');
                }
                else{
                    $h = new HTMLNode();
                    $h->setID('side-content-area');
                }
                return $h;
            }
            return $this->document->getChildByID('aside-container');
        }
    }
    
    private function _getHeader($new=false){
        if($this->hasHeader()){
            if($new === TRUE){
                if(function_exists('getHeaderNode')){
                    $h = getHeaderNode();
                    $h->setID('page-header');
                }
                else{
                    $h = new HTMLNode();
                    $h->setID('page-header');
                }
                return $h;
            }
            return $this->document->getChildByID('page-header');
        }
    }
    
    private function _getFooter($new=false){
        if($this->hasFooter()){
            if($new === TRUE){
                if(function_exists('getFooterNode')){
                    $f = getFooterNode();
                    $f->setID('page-footer');
                }
                else{
                    $f = new HTMLNode();
                    $f->setID('page-footer');
                }
                return $f;
            }
            return $this->document->getChildByID('page-footer');
        }
    }
    
    private function _getHead($new=false){
        if($new === TRUE){
            $headNode = new HeadNode(
                $this->getTitle().$this->getTitleSep().$this->getWebsiteName(),
                $this->getCanonical(),
                SiteConfig::get()->getBaseURL()
            );
            $metaCharset = new HTMLNode('meta', FALSE);
            $metaCharset->setAttribute('charset', 'UTF-8');
            $headNode->addChild($metaCharset);
            if(function_exists('getHeadNode')){
                $tmpHead = getHeadNode();
                $headNode->setTitle($this->getTitle().$this->getTitleSep().$this->getWebsiteName());
                $headNode->setBase($tmpHead->getBase()->getAttributeValue('href'));
                $headNode->setCanonical($this->getCanonical());
                $descNode = new HTMLNode('meta', FALSE);
                $descNode->setAttribute('name', 'description');
                $descNode->setAttribute('content', $this->getDescription());
                $headNode->addChild($descNode);
                $children = $tmpHead->children();
                $count = $children->size();
                for($x = 0 ; $x < $count ; $x++){
                    $node = $children->get($x);
                    $nodeName = $node->getName();
                    if($nodeName != 'base' && $nodeName != 'title'){
                        if($node->getAttributeValue('name') != 'description'){
                            $headNode->addChild($node);
                        }
                    }
                }
            }
            else {
                $descNode = new HTMLNode('meta', FALSE);
                $descNode->setAttribute('name', 'description');
                $descNode->setAttribute('content', $this->getDescription());
                $headNode->addChild($descNode);
            }
            return $headNode;
        }
        return $this->document->getHeadNode();
    }
}
